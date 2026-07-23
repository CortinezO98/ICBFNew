<?php
declare(strict_types=1);

/**
 * gestion_coaching/lib/coaching_datos.php
 *
 * Capa única de acceso a datos de tb_gestion_coaching_*.
 * Ningún archivo de vista debe construir SQL propio contra estas tablas:
 * todo pasa por aquí, para poder auditar/asegurar en un solo lugar.
 *
 * Convención reutilizada del sistema: mysqli + prepared statements,
 * $enlace_db se recibe por parámetro (mismo patrón que conexion_db.php).
 */

/**
 * Genera el siguiente consecutivo de paquete, formato CO0000000001,
 * mismo patrón que MC/EN ya usados en Calidad/Encuestas.
 *
 * A diferencia del patrón heredado (SELECT MAX(...)+1 desde PHP, que es
 * vulnerable a condición de carrera con alta concurrencia), aquí se usa
 * una fila de control con SELECT ... FOR UPDATE dentro de una transacción,
 * lo que hace la generación de consecutivo segura ante peticiones
 * simultáneas sin depender de un UNIQUE de reintento.
 *
 * IMPORTANTE: debe llamarse siempre dentro de una transacción ya abierta
 * por el llamador (begin_transaction), porque el FOR UPDATE solo bloquea
 * durante la transacción activa.
 */
function generarConsecutivoPaquete(mysqli $enlace_db): string
{
    $resultado = $enlace_db->query(
        "SELECT `gcc_ultimo_valor` FROM `tb_gestion_coaching_consecutivo` WHERE `gcc_id` = 1 FOR UPDATE"
    );

    if (!$resultado || $resultado->num_rows === 0) {
        throw new RuntimeException('No fue posible leer el consecutivo de Coaching.');
    }

    $fila = $resultado->fetch_assoc();
    $nuevo_valor = ((int) $fila['gcc_ultimo_valor']) + 1;

    $actualizar = $enlace_db->prepare(
        "UPDATE `tb_gestion_coaching_consecutivo` SET `gcc_ultimo_valor` = ? WHERE `gcc_id` = 1"
    );
    $actualizar->bind_param('i', $nuevo_valor);
    if (!$actualizar->execute()) {
        throw new RuntimeException('No fue posible actualizar el consecutivo de Coaching.');
    }

    return 'CO' . str_pad((string) $nuevo_valor, 10, '0', STR_PAD_LEFT);
}

/**
 * Busca el paquete automático VIGENTE (gcp_activo = 1) para un monitoreo
 * de calidad dado. Devuelve el arreglo del paquete o null si no existe.
 * Se apoya en la misma UNIQUE (gcp_origen_tipo, gcp_monitoreo_id, gcp_activo)
 * que garantiza la idempotencia a nivel de motor.
 */
function buscarPaqueteAutomaticoVigente(mysqli $enlace_db, string $gcm_id): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT P.`gcp_id`, P.`gcp_estado_id`, P.`gcp_version`, C.`gcc2_nota_general`
         FROM `tb_gestion_coaching_paquete` AS P
         LEFT JOIN `tb_gestion_coaching_calidad` AS C ON C.`gcc2_paquete` = P.`gcp_id`
         WHERE P.`gcp_origen_tipo` = 'monitoreo' AND P.`gcp_monitoreo_id` = ? AND P.`gcp_activo` = 1
         LIMIT 1"
    );
    $consulta->bind_param('s', $gcm_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    return $fila ?: null;
}

/**
 * Devuelve el gce_id de un estado a partir de su código (BORRADOR,
 * ASIGNADO, etc.). Se cachea en memoria estática dentro del request
 * porque el catálogo de estados es pequeño y no cambia en caliente.
 */
function obtenerEstadoIdPorCodigo(mysqli $enlace_db, string $codigo): int
{
    static $cache = [];
    if (isset($cache[$codigo])) {
        return $cache[$codigo];
    }

    $consulta = $enlace_db->prepare(
        "SELECT `gce_id` FROM `tb_gestion_coaching_estado` WHERE `gce_codigo` = ? AND `gce_activo` = 1 LIMIT 1"
    );
    $consulta->bind_param('s', $codigo);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    if (!$fila) {
        throw new RuntimeException("Estado de coaching no configurado: {$codigo}");
    }

    $cache[$codigo] = (int) $fila['gce_id'];
    return $cache[$codigo];
}

/** Igual que obtenerEstadoIdPorCodigo pero para tb_gestion_coaching_tipo. */
function obtenerTipoIdPorCodigo(mysqli $enlace_db, string $codigo): int
{
    static $cache = [];
    if (isset($cache[$codigo])) {
        return $cache[$codigo];
    }

    $consulta = $enlace_db->prepare(
        "SELECT `gct_id` FROM `tb_gestion_coaching_tipo` WHERE `gct_codigo` = ? AND `gct_activo` = 1 LIMIT 1"
    );
    $consulta->bind_param('s', $codigo);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    if (!$fila) {
        throw new RuntimeException("Tipo de coaching no configurado: {$codigo}");
    }

    $cache[$codigo] = (int) $fila['gct_id'];
    return $cache[$codigo];
}

/**
 * Trae del sistema de usuarios (tabla heredada tb_administrador_usuario)
 * el supervisor y líder de calidad ACTUALES de un agente, para tomar la
 * fotografía histórica en el momento exacto de crear el paquete.
 */
function obtenerSupervisionActualAgente(mysqli $enlace_db, string $usu_id_agente): array
{
    $consulta = $enlace_db->prepare(
        "SELECT `usu_supervisor`, `usu_lider_calidad`, `usu_campania`
         FROM `tb_administrador_usuario` WHERE `usu_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $usu_id_agente);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    return [
        'supervisor'     => $fila['usu_supervisor'] ?? null,
        'lider_calidad'  => $fila['usu_lider_calidad'] ?? null,
        'campania'       => $fila['usu_campania'] ?? null,
    ];
}

/**
 * Crea el paquete automático (Modelo 1) + su fotografía de calidad,
 * en una única transacción. No debe llamarse directamente desde vistas:
 * es usado exclusivamente por coaching_disparador.php.
 *
 * $snapshot debe traer las columnas ya validadas de
 * tb_gestion_calidad_monitoreo (ver coaching_disparador.php).
 *
 * @return string gcp_id del paquete creado
 */
function crearPaqueteAutomatico(mysqli $enlace_db, array $snapshot, ?string $paquete_anterior = null): string
{
    $enlace_db->begin_transaction();
    try {
        $gcp_id = generarConsecutivoPaquete($enlace_db);

        $supervision = obtenerSupervisionActualAgente($enlace_db, $snapshot['agente_id']);
        if (empty($supervision['supervisor'])) {
            // No se puede asignar un paquete sin supervisor: se registra el
            // problema y se aborta de forma controlada, sin tumbar la
            // transacción del monitoreo que disparó esto (ver coaching_disparador.php).
            throw new RuntimeException(
                "El agente {$snapshot['agente_id']} no tiene supervisor asignado en tb_administrador_usuario; " .
                "no fue posible crear el paquete de coaching automático."
            );
        }

        $estado_id = obtenerEstadoIdPorCodigo($enlace_db, 'ASIGNADO');
        $tipo_id   = obtenerTipoIdPorCodigo($enlace_db, 'RETROALIMENTACION');

        // Campaña actual del agente (sectorización por empresa) — el
        // segmento en origen automático viene directo del monitoreo que
        // disparó el paquete, no hace falta buscar el "más reciente".
        $consulta_campania_agente = $enlace_db->prepare("SELECT `usu_campania` FROM `tb_administrador_usuario` WHERE `usu_id` = ? LIMIT 1");
        $consulta_campania_agente->bind_param('s', $snapshot['agente_id']);
        $consulta_campania_agente->execute();
        $fila_campania_agente = $consulta_campania_agente->get_result()->fetch_assoc();
        $campania_id = $fila_campania_agente && $fila_campania_agente['usu_campania'] !== '' ? (int) $fila_campania_agente['usu_campania'] : null;

        $insertar_paquete = $enlace_db->prepare(
            "INSERT INTO `tb_gestion_coaching_paquete`
                (`gcp_id`, `gcp_origen_tipo`, `gcp_monitoreo_id`, `gcp_paquete_anterior`, `gcp_tipo_id`,
                 `gcp_agente_id`, `gcp_supervisor_id`, `gcp_lider_calidad_id`, `gcp_analista_calidad_id`,
                 `gcp_estado_id`, `gcp_campania_id`, `gcp_segmento`, `gcp_fecha_asignacion`, `gcp_creado_por`)
             VALUES (?, 'monitoreo', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'SISTEMA')"
        );
        $insertar_paquete->bind_param(
            'sssissssiis',
            $gcp_id,
            $snapshot['monitoreo_id'],
            $paquete_anterior,
            $tipo_id,
            $snapshot['agente_id'],
            $supervision['supervisor'],
            $supervision['lider_calidad'],
            $snapshot['analista_id'],
            $estado_id,
            $campania_id,
            $snapshot['segmento']
        );
        if (!$insertar_paquete->execute()) {
            // La UNIQUE (gcp_origen_tipo, gcp_monitoreo_id, gcp_activo) es
            // quien realmente garantiza la idempotencia ante condición de
            // carrera; si otro proceso ganó la carrera, esto falla aquí.
            throw new RuntimeException('No fue posible crear el paquete (posible duplicado concurrente): ' . $enlace_db->error);
        }

        $insertar_calidad = $enlace_db->prepare(
            "INSERT INTO `tb_gestion_coaching_calidad`
                (`gcc2_paquete`, `gcc2_monitoreo_id`, `gcc2_matriz`, `gcc2_nota_general`,
                 `gcc2_nota_enc`, `gcc2_nota_ecn`, `gcc2_nota_ecuf`,
                 `gcc2_nota_enc_estado`, `gcc2_nota_ecn_estado`, `gcc2_nota_ecuf_estado`,
                 `gcc2_estado_monitoreo_al_crear`, `gcc2_tipo_monitoreo`, `gcc2_skill_interaccion`,
                 `gcc2_tipo_gestion`, `gcc2_segmento`, `gcc2_id_sim`, `gcc2_id_ani`,
                 `gcc2_observaciones`, `gcc2_fecha_monitoreo`, `gcc2_fecha_cierre`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insertar_calidad->bind_param(
            'sssdssssssssssssssss',
            $gcp_id,
            $snapshot['monitoreo_id'],
            $snapshot['matriz'],
            $snapshot['nota_general'],
            $snapshot['nota_enc'],
            $snapshot['nota_ecn'],
            $snapshot['nota_ecuf'],
            $snapshot['nota_enc_estado'],
            $snapshot['nota_ecn_estado'],
            $snapshot['nota_ecuf_estado'],
            $snapshot['gcm_estado'],
            $snapshot['tipo_monitoreo'],
            $snapshot['skill_interaccion'],
            $snapshot['tipo_gestion'],
            $snapshot['segmento'],
            $snapshot['id_sim'],
            $snapshot['id_ani'],
            $snapshot['observaciones'],
            $snapshot['fecha_monitoreo'],
            $snapshot['fecha_cierre']
        );
        if (!$insertar_calidad->execute()) {
            throw new RuntimeException('No fue posible guardar la fotografía de calidad: ' . $enlace_db->error);
        }

        insertarHistorial($enlace_db, $gcp_id, null, $estado_id, 'CREAR_AUTOMATICO',
            'Paquete creado automáticamente por nota general < 90 en monitoreo ' . $snapshot['monitoreo_id'],
            'SISTEMA', null);

        $enlace_db->commit();
        return $gcp_id;
    } catch (Throwable $e) {
        $enlace_db->rollback();
        throw $e;
    }
}

/**
 * Anula (no borra) un paquete vigente, con motivo obligatorio.
 * `gcp_activo = 0` libera el slot de la UNIQUE para que, si aplica,
 * coaching_disparador.php pueda crear uno nuevo encadenado.
 */
function anularPaquete(mysqli $enlace_db, string $gcp_id, string $motivo, string $usu_id): bool
{
    $enlace_db->begin_transaction();
    try {
        $estado_anulado = obtenerEstadoIdPorCodigo($enlace_db, 'ANULADO');

        $consulta_actual = $enlace_db->prepare(
            "SELECT `gcp_estado_id` FROM `tb_gestion_coaching_paquete` WHERE `gcp_id` = ? AND `gcp_activo` = 1 FOR UPDATE"
        );
        $consulta_actual->bind_param('s', $gcp_id);
        $consulta_actual->execute();
        $actual = $consulta_actual->get_result()->fetch_assoc();

        if (!$actual) {
            // Ya estaba anulado o no existe: no es un error duro, es un no-op.
            $enlace_db->rollback();
            return false;
        }

        $actualizar = $enlace_db->prepare(
            "UPDATE `tb_gestion_coaching_paquete`
             SET `gcp_activo` = 0, `gcp_estado_id` = ?, `gcp_motivo_anulacion` = ?, `gcp_version` = `gcp_version` + 1
             WHERE `gcp_id` = ?"
        );
        $actualizar->bind_param('iss', $estado_anulado, $motivo, $gcp_id);
        if (!$actualizar->execute()) {
            throw new RuntimeException('No fue posible anular el paquete: ' . $enlace_db->error);
        }

        insertarHistorial($enlace_db, $gcp_id, (int) $actual['gcp_estado_id'], $estado_anulado, 'ANULAR', $motivo, $usu_id, null);

        $enlace_db->commit();
        return true;
    } catch (Throwable $e) {
        $enlace_db->rollback();
        throw $e;
    }
}

/**
 * Inserta un registro inmutable en el historial. Nunca se hace UPDATE ni
 * DELETE sobre tb_gestion_coaching_historial desde ningún punto del código.
 */
function insertarHistorial(
    mysqli $enlace_db,
    string $gcp_id,
    ?int $estado_anterior,
    int $estado_nuevo,
    string $accion,
    ?string $comentario,
    string $usu_id,
    ?string $ip
): void {
    $insertar = $enlace_db->prepare(
        "INSERT INTO `tb_gestion_coaching_historial`
            (`gch2_paquete`, `gch2_estado_anterior`, `gch2_estado_nuevo`, `gch2_accion`, `gch2_comentario`, `gch2_usuario`, `gch2_ip`)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $insertar->bind_param('sisssss', $gcp_id, $estado_anterior, $estado_nuevo, $accion, $comentario, $usu_id, $ip);
    if (!$insertar->execute()) {
        throw new RuntimeException('No fue posible registrar el historial de coaching: ' . $enlace_db->error);
    }
}

/**
 * Registra una nueva versión de documento generado (nunca se sobrescribe
 * una versión existente: si ya había una vigente, esta la marca reemplazada
 * dentro de la misma transacción).
 *
 * @return int gcd_id de la nueva versión
 */
function insertarDocumento(
    mysqli $enlace_db,
    string $gcp_id,
    string $tipo_documento,
    string $ruta,
    string $hash_sha256,
    string $generado_por
): int {
    $enlace_db->begin_transaction();
    try {
        $consulta_version = $enlace_db->prepare(
            "SELECT `gcd_id`, `gcd_version` FROM `tb_gestion_coaching_documento`
             WHERE `gcd_paquete` = ? AND `gcd_estado` = 'Vigente' FOR UPDATE"
        );
        $consulta_version->bind_param('s', $gcp_id);
        $consulta_version->execute();
        $vigente = $consulta_version->get_result()->fetch_assoc();

        $nueva_version = 1;
        if ($vigente) {
            $nueva_version = ((int) $vigente['gcd_version']) + 1;

            $marcar_reemplazado = $enlace_db->prepare(
                "UPDATE `tb_gestion_coaching_documento` SET `gcd_estado` = 'Reemplazado' WHERE `gcd_id` = ?"
            );
            $marcar_reemplazado->bind_param('i', $vigente['gcd_id']);
            $marcar_reemplazado->execute();
        }

        $insertar = $enlace_db->prepare(
            "INSERT INTO `tb_gestion_coaching_documento`
                (`gcd_paquete`, `gcd_tipo_documento`, `gcd_version`, `gcd_ruta`, `gcd_hash_sha256`, `gcd_generado_por`, `gcd_estado`)
             VALUES (?, ?, ?, ?, ?, ?, 'Vigente')"
        );
        $insertar->bind_param('ssisss', $gcp_id, $tipo_documento, $nueva_version, $ruta, $hash_sha256, $generado_por);
        if (!$insertar->execute()) {
            throw new RuntimeException('No fue posible registrar el documento: ' . $enlace_db->error);
        }
        $gcd_id = $insertar->insert_id;

        $enlace_db->commit();
        return (int) $gcd_id;
    } catch (Throwable $e) {
        $enlace_db->rollback();
        throw $e;
    }
}

/** Devuelve el documento VIGENTE (no reemplazado) del paquete, o null. */
function obtenerDocumentoVigente(mysqli $enlace_db, string $gcp_id, string $tipo_documento): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT * FROM `tb_gestion_coaching_documento`
         WHERE `gcd_paquete` = ? AND `gcd_tipo_documento` = ? AND `gcd_estado` = 'Vigente'
         ORDER BY `gcd_version` DESC LIMIT 1"
    );
    $consulta->bind_param('ss', $gcp_id, $tipo_documento);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    return $fila ?: null;
}

/** Devuelve la firma registrada para un gcd_id concreto, o null si no existe. */
function obtenerFirmaPorDocumento(mysqli $enlace_db, int $gcd_id): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT * FROM `tb_gestion_coaching_firma` WHERE `gcf_documento` = ? LIMIT 1"
    );
    $consulta->bind_param('i', $gcd_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    return $fila ?: null;
}

/**
 * Inserta la firma/aceptación electrónica. El llamador (coaching_firma.php)
 * ya debe haber validado con puedeFirmar() y recalculado el hash del PDF
 * en disco contra gcd_hash_sha256 antes de invocar esta función.
 */
function insertarFirma(
    mysqli $enlace_db,
    string $gcp_id,
    int $gcd_id,
    string $usu_id,
    string $rol,
    string $hash_documento,
    string $ip,
    ?string $user_agent,
    string $texto_consentimiento
): int {
    $tipo_firma = 'Aceptacion_Electronica';
    $insertar = $enlace_db->prepare(
        "INSERT INTO `tb_gestion_coaching_firma`
            (`gcf_paquete`, `gcf_documento`, `gcf_firmante_usuario`, `gcf_firmante_rol`, `gcf_tipo_firma`,
             `gcf_hash_documento_firmado`, `gcf_ip`, `gcf_user_agent`, `gcf_consentimiento_texto`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $insertar->bind_param(
        'sisssssss',
        $gcp_id,
        $gcd_id,
        $usu_id,
        $rol,
        $tipo_firma,
        $hash_documento,
        $ip,
        $user_agent,
        $texto_consentimiento
    );
    if (!$insertar->execute()) {
        throw new RuntimeException('No fue posible registrar la firma: ' . $enlace_db->error);
    }
    return (int) $insertar->insert_id;
}

/**
 * Trae el paquete con sus joins habituales (tipo, estado, agente,
 * supervisor) — helper reutilizable para las pantallas del flujo
 * (retroalimentación, responder, aprobar), evita repetir el mismo SELECT
 * en cada archivo.
 */
function obtenerPaqueteConDetalle(mysqli $enlace_db, string $gcp_id): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT P.*, T.`gct_nombre`, T.`gct_codigo`, T.`gct_requiere_respuesta_agente`, T.`gct_requiere_firma_agente`,
                E.`gce_nombre`, E.`gce_codigo`,
                TA.`usu_nombres_apellidos` AS agente_nombre,
                TS.`usu_nombres_apellidos` AS supervisor_nombre
         FROM `tb_gestion_coaching_paquete` AS P
         LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
         LEFT JOIN `tb_gestion_coaching_estado` AS E ON P.`gcp_estado_id` = E.`gce_id`
         LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
         LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
         WHERE P.`gcp_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $gcp_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    return $fila ?: null;
}

/** Inserta (o actualiza si ya existía) la retroalimentación del supervisor. */
function guardarRetroalimentacion(mysqli $enlace_db, string $gcp_id, array $datos, string $usu_id): void
{
    $insertar = $enlace_db->prepare(
        "INSERT INTO `tb_gestion_coaching_retroalimentacion`
            (`gcr_paquete`, `gcr_causa_raiz`, `gcr_metodologia`, `gcr_estrategia_correctiva`,
             `gcr_fortalezas`, `gcr_oportunidades_mejora`, `gcr_observaciones`, `gcr_registro_usuario`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            `gcr_causa_raiz` = VALUES(`gcr_causa_raiz`),
            `gcr_metodologia` = VALUES(`gcr_metodologia`),
            `gcr_estrategia_correctiva` = VALUES(`gcr_estrategia_correctiva`),
            `gcr_fortalezas` = VALUES(`gcr_fortalezas`),
            `gcr_oportunidades_mejora` = VALUES(`gcr_oportunidades_mejora`),
            `gcr_observaciones` = VALUES(`gcr_observaciones`)"
    );
    $insertar->bind_param(
        'ssssssss',
        $gcp_id,
        $datos['causa_raiz'],
        $datos['metodologia'],
        $datos['estrategia_correctiva'],
        $datos['fortalezas'],
        $datos['oportunidades_mejora'],
        $datos['observaciones'],
        $usu_id
    );
    if (!$insertar->execute()) {
        throw new RuntimeException('No fue posible guardar la retroalimentación: ' . $enlace_db->error);
    }
}

function obtenerRetroalimentacion(mysqli $enlace_db, string $gcp_id): ?array
{
    $consulta = $enlace_db->prepare("SELECT * FROM `tb_gestion_coaching_retroalimentacion` WHERE `gcr_paquete` = ? LIMIT 1");
    $consulta->bind_param('s', $gcp_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    return $fila ?: null;
}

/** Reemplaza los compromisos del paquete (borra los previos y registra los nuevos, en una transacción). */
function guardarCompromisos(mysqli $enlace_db, string $gcp_id, array $compromisos, string $usu_id): void
{
    $enlace_db->begin_transaction();
    try {
        $borrar = $enlace_db->prepare("DELETE FROM `tb_gestion_coaching_compromiso` WHERE `gccm_paquete` = ?");
        $borrar->bind_param('s', $gcp_id);
        $borrar->execute();

        $insertar = $enlace_db->prepare(
            "INSERT INTO `tb_gestion_coaching_compromiso`
                (`gccm_paquete`, `gccm_descripcion`, `gccm_que`, `gccm_como`, `gccm_fecha_limite`, `gccm_responsable`, `gccm_registro_usuario`)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        foreach ($compromisos as $c) {
            $fecha_limite = $c['fecha_limite'] !== '' ? $c['fecha_limite'] : null;
            $insertar->bind_param('sssssss', $gcp_id, $c['descripcion'], $c['que'], $c['como'], $fecha_limite, $c['responsable'], $usu_id);
            if (!$insertar->execute()) {
                throw new RuntimeException('No fue posible registrar un compromiso: ' . $enlace_db->error);
            }
        }
        $enlace_db->commit();
    } catch (Throwable $e) {
        $enlace_db->rollback();
        throw $e;
    }
}

function listarCompromisosPorPaquete(mysqli $enlace_db, string $gcp_id): array
{
    $consulta = $enlace_db->prepare("SELECT * FROM `tb_gestion_coaching_compromiso` WHERE `gccm_paquete` = ? ORDER BY `gccm_id` ASC");
    $consulta->bind_param('s', $gcp_id);
    $consulta->execute();
    return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Marca un compromiso individual como cumplido/no cumplido. Valida que el
 * compromiso pertenezca realmente al paquete indicado (defensa contra
 * IDOR: no basta con conocer un gccm_id, tiene que corresponder al gcp_id
 * sobre el que el usuario ya está autorizado).
 */
function actualizarEstadoCompromiso(mysqli $enlace_db, int $gccm_id, string $gcp_id, string $nuevo_estado, ?string $resultado, string $usu_id): bool
{
    $estados_validos = ['Pendiente', 'Cumplido', 'No cumplido'];
    if (!in_array($nuevo_estado, $estados_validos, true)) {
        throw new RuntimeException('Estado de compromiso no válido.');
    }

    $fecha_cumplimiento = $nuevo_estado === 'Cumplido' ? date('Y-m-d') : null;

    $actualizar = $enlace_db->prepare(
        "UPDATE `tb_gestion_coaching_compromiso`
         SET `gccm_estado` = ?, `gccm_resultado` = ?, `gccm_fecha_cumplimiento` = ?
         WHERE `gccm_id` = ? AND `gccm_paquete` = ?"
    );
    $actualizar->bind_param('sssis', $nuevo_estado, $resultado, $fecha_cumplimiento, $gccm_id, $gcp_id);
    $actualizar->execute();

    if ($actualizar->affected_rows > 0) {
        $consulta_estado = $enlace_db->prepare("SELECT `gcp_estado_id` FROM `tb_gestion_coaching_paquete` WHERE `gcp_id` = ? LIMIT 1");
        $consulta_estado->bind_param('s', $gcp_id);
        $consulta_estado->execute();
        $estado_actual = (int) ($consulta_estado->get_result()->fetch_assoc()['gcp_estado_id'] ?? 0);

        insertarHistorial($enlace_db, $gcp_id, $estado_actual, $estado_actual, 'ACTUALIZAR_COMPROMISO',
            "Compromiso #{$gccm_id} marcado como '{$nuevo_estado}'" . ($resultado ? ": {$resultado}" : ''),
            $usu_id, null);
    }

    return $actualizar->affected_rows > 0;
}

/** Inserta (o actualiza) la respuesta del agente. */
function guardarRespuestaAgente(mysqli $enlace_db, string $gcp_id, array $datos, string $usu_id): void
{
    $sql="INSERT INTO tb_gestion_coaching_respuesta_agente(gcra_paquete,gcra_compromiso_general,gcra_acciones_no_reincidencia,gcra_que,gcra_como,gcra_cuando,gcra_aspectos_relevantes,gcra_confirma_claridad,gcra_observaciones,gcra_usuario) VALUES(?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE gcra_compromiso_general=VALUES(gcra_compromiso_general),gcra_acciones_no_reincidencia=VALUES(gcra_acciones_no_reincidencia),gcra_que=VALUES(gcra_que),gcra_como=VALUES(gcra_como),gcra_cuando=VALUES(gcra_cuando),gcra_aspectos_relevantes=VALUES(gcra_aspectos_relevantes),gcra_confirma_claridad=VALUES(gcra_confirma_claridad),gcra_observaciones=VALUES(gcra_observaciones),gcra_usuario=VALUES(gcra_usuario),gcra_registro_fecha=NOW()";
    $s=$enlace_db->prepare($sql);$claridad=!empty($datos['confirma_claridad'])?1:0;
    $s->bind_param('sssssssiss',$gcp_id,$datos['compromiso_general'],$datos['acciones_no_reincidencia'],$datos['que'],$datos['como'],$datos['cuando'],$datos['aspectos_relevantes'],$claridad,$datos['observaciones'],$usu_id);
    if(!$s->execute()) throw new RuntimeException('No fue posible guardar la respuesta del agente.');
}

function obtenerRespuestaAgente(mysqli $enlace_db, string $gcp_id): ?array
{
    $consulta = $enlace_db->prepare("SELECT * FROM `tb_gestion_coaching_respuesta_agente` WHERE `gcra_paquete` = ? LIMIT 1");
    $consulta->bind_param('s', $gcp_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    return $fila ?: null;
}

/**
 * Trae la campaña ACTUAL del agente (usu_campania, ya reutilizada como
 * sectorización por empresa: ASD/IQ/Comware/Happy/ICBF genérico) y el
 * segmento de su monitoreo de calidad MÁS RECIENTE (no existe un
 * "segmento fijo" de agente en el sistema heredado — el segmento es una
 * propiedad de cada interacción/monitoreo, así que se toma el último
 * conocido como sugerencia editable).
 */
function obtenerCampaniaYSegmentoRecienteAgente(mysqli $enlace_db, string $usu_id_agente): array
{
    $consulta_campania = $enlace_db->prepare("SELECT `usu_campania` FROM `tb_administrador_usuario` WHERE `usu_id` = ? LIMIT 1");
    $consulta_campania->bind_param('s', $usu_id_agente);
    $consulta_campania->execute();
    $fila_campania = $consulta_campania->get_result()->fetch_assoc();
    $campania_id = $fila_campania && $fila_campania['usu_campania'] !== '' ? (int) $fila_campania['usu_campania'] : null;

    $consulta_segmento = $enlace_db->prepare(
        "SELECT `gcm_segmento` FROM `tb_gestion_calidad_monitoreo`
         WHERE `gcm_responsable` = ? ORDER BY `gcm_registro_fecha` DESC LIMIT 1"
    );
    $consulta_segmento->bind_param('s', $usu_id_agente);
    $consulta_segmento->execute();
    $fila_segmento = $consulta_segmento->get_result()->fetch_assoc();
    $segmento = $fila_segmento['gcm_segmento'] ?? null;

    return ['campania_id' => $campania_id, 'segmento' => $segmento];
}

/**
 * Crea un paquete GLOBAL (Modelo 2): manual, creado por un supervisor,
 * sin depender de un monitoreo. A diferencia del automático, aquí el
 * supervisor SÍ es quien está creando el paquete (no se busca en
 * tb_administrador_usuario), pero igual se valida que el agente elegido
 * realmente le pertenezca a ese supervisor (autorización por recurso,
 * evita que un supervisor cree paquetes para agentes ajenos).
 *
 * @param array $datos ['agente_id','tipo_codigo','indicador_id'(nullable),
 *                       'prioridad','fecha_limite'(nullable Y-m-d),'contexto'(nullable)]
 * @return string gcp_id
 */
function crearPaqueteGlobal(mysqli $enlace_db, array $datos, string $supervisor_id): string
{
    $enlace_db->begin_transaction();
    try {
        // Autorización por recurso: el agente elegido debe reportar
        // realmente a este supervisor. Nunca se confía en el <select> del
        // formulario sin revalidar en servidor (defensa contra IDOR /
        // manipulación de parámetros).
        $consulta_agente = $enlace_db->prepare(
            "SELECT `usu_supervisor` FROM `tb_administrador_usuario` WHERE `usu_id` = ? LIMIT 1"
        );
        $consulta_agente->bind_param('s', $datos['agente_id']);
        $consulta_agente->execute();
        $agente = $consulta_agente->get_result()->fetch_assoc();

        if (!$agente || $agente['usu_supervisor'] !== $supervisor_id) {
            throw new RuntimeException('El agente seleccionado no pertenece a su equipo.');
        }

        $gcp_id = generarConsecutivoPaquete($enlace_db);
        $estado_id = obtenerEstadoIdPorCodigo($enlace_db, 'ASIGNADO');
        $tipo_id = obtenerTipoIdPorCodigo($enlace_db, $datos['tipo_codigo']);

        // Campaña SIEMPRE se toma del agente en el servidor (nunca del
        // formulario): es la sectorización por empresa (ASD/IQ/Comware/
        // Happy), no algo que el supervisor deba elegir a mano.
        $campania_segmento = obtenerCampaniaYSegmentoRecienteAgente($enlace_db, $datos['agente_id']);

        $insertar = $enlace_db->prepare(
            "INSERT INTO `tb_gestion_coaching_paquete`
                (`gcp_id`, `gcp_origen_tipo`, `gcp_tipo_id`, `gcp_indicador_id`,
                 `gcp_agente_id`, `gcp_supervisor_id`, `gcp_estado_id`, `gcp_campania_id`, `gcp_segmento`,
                 `gcp_prioridad`, `gcp_fecha_asignacion`, `gcp_fecha_limite`, `gcp_creado_por`)
             VALUES (?, 'global', ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)"
        );
        $indicador_id = $datos['indicador_id'] ?? null;
        $fecha_limite = $datos['fecha_limite'] ?? null;
        // El segmento sugerido (del monitoreo más reciente) se usa como
        // valor por defecto, pero el supervisor pudo haberlo editado en el
        // formulario — se respeta lo que llegó en $datos si viene presente.
        $segmento_final = ($datos['segmento'] ?? '') !== '' ? $datos['segmento'] : $campania_segmento['segmento'];
        $insertar->bind_param(
            'siissiissss',
            $gcp_id,
            $tipo_id,
            $indicador_id,
            $datos['agente_id'],
            $supervisor_id,
            $estado_id,
            $campania_segmento['campania_id'],
            $segmento_final,
            $datos['prioridad'],
            $fecha_limite,
            $supervisor_id
        );
        if (!$insertar->execute()) {
            throw new RuntimeException('No fue posible crear el paquete: ' . $enlace_db->error);
        }

        insertarHistorial($enlace_db, $gcp_id, null, $estado_id, 'CREAR_MANUAL',
            $datos['contexto'] ?? null, $supervisor_id, null);

        $enlace_db->commit();
        return $gcp_id;
    } catch (Throwable $e) {
        $enlace_db->rollback();
        throw $e;
    }
}

/** Lista los agentes cuyo supervisor actual es $supervisor_id (para poblar el <select> del alta manual). */
function listarAgentesDeSupervisor(mysqli $enlace_db, string $supervisor_id): array
{
    $consulta = $enlace_db->prepare(
        "SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario`
         WHERE `usu_supervisor` = ? AND `usu_estado` = 'Activo' ORDER BY `usu_nombres_apellidos`"
    );
    $consulta->bind_param('s', $supervisor_id);
    $consulta->execute();
    return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** Lista los tipos e indicadores activos (catálogos), para poblar los <select> del formulario. */
function listarTiposActivos(mysqli $enlace_db): array
{
    $resultado = $enlace_db->query("SELECT `gct_id`, `gct_codigo`, `gct_nombre` FROM `tb_gestion_coaching_tipo` WHERE `gct_activo` = 1 ORDER BY `gct_nombre`");
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

function listarIndicadoresActivos(mysqli $enlace_db): array
{
    $resultado = $enlace_db->query("SELECT `gci_id`, `gci_nombre` FROM `tb_gestion_coaching_indicador` WHERE `gci_activo` = 1 ORDER BY `gci_nombre`");
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
/**
 * Audita cualquier fallo del módulo de Coaching en el log general ya
 * existente del sistema (tb_administrador_log), mismo patrón que usa el
 * resto del sistema para log_tipo="error". Compartida por
 * coaching_disparador.php y coaching_firma.php. No se le muestra nada
 * técnico al usuario final, solo un mensaje funcional genérico.
 */
function registrarErrorCoaching(mysqli $enlace_db, string $referencia, string $detalle): void
{
    $modulo   = 'Coaching';
    $tipo     = 'error';
    $accion   = 'Incidente de Coaching';
    $detalle_completo = "Referencia {$referencia}: {$detalle}";
    $usuario  = 'SISTEMA';

    $log = $enlace_db->prepare(
        "INSERT INTO `tb_administrador_log`
            (`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`)
         VALUES (?, ?, ?, ?, ?)"
    );
    $log->bind_param('sssss', $modulo, $tipo, $accion, $detalle_completo, $usuario);
    $log->execute();
}



