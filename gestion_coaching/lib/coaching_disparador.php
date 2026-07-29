<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_reglas.php';
require_once __DIR__ . '/coaching_datos.php';

/**
 * gestion_coaching/lib/coaching_disparador.php
 *
 * Hook de integración de Modelo 1 (Coaching desde Monitoreo de Calidad).
 * Se llama, con UNA sola línea, desde el final de:
 *   - gestion_calidad/gestion_calidad_monitoreo_guardar.php   (creación)
 *   - gestion_calidad/gestion_calidad_monitoreo_aceptar.php   (Pendiente -> Aceptado)
 *   - gestion_calidad/gestion_calidad_monitoreo_refutar.php   (-> Refutado-Rechazado / Refutado-Aceptado)
 *
 * ⚠️ CAMBIO DE COMPORTAMIENTO (27 jul 2026): ya NO crea el paquete de
 * coaching automáticamente — se decidió que el volumen de monitoreos con
 * nota < 90 sería demasiado alto para generar un paquete sin
 * intervención humana. Ahora esta función SOLO:
 *   1) Concilia paquetes ya creados (los anula si la nota se corrigió a
 *      >= 90 tras una refutación, o los reemplaza si la nota cambió).
 *   2) NUNCA crea un paquete nuevo por sí sola.
 *
 * La creación real ahora es manual: el analista de Calidad ve un botón
 * "Generar Coaching" cuando la nota es < 90 (usar
 * coachingMonitoreoElegibleParaGenerar() para decidir si mostrarlo), y
 * al confirmar, gestion_coaching_generar_desde_monitoreo.php es quien
 * llama a crearPaqueteAutomatico().
 *
 * Se conserva el mismo nombre de función y la misma firma para no tener
 * que volver a tocar los 3 archivos reales de Calidad ya integrados —
 * solo cambió lo que hace por dentro.
 *
 * Alcance confirmado por negocio: SOLO tb_gestion_calidad_monitoreo.
 * Auditoría y Calibración NO disparan coaching.
 *
 * Contrato de no interferencia: esta función NUNCA debe permitir que un
 * error propio interrumpa el flujo del archivo que la llamó.
 */
function evaluarDisparoCoachingAutomatico(
    mysqli $enlace_db,
    string $gcm_id,
    string $gcm_estado,
    string $usu_id_actor
): void {
    try {
        if (!estadoMonitoreoEsTerminal($gcm_estado)) {
            return;
        }

        $snapshot = obtenerSnapshotMonitoreoCalidad($enlace_db, $gcm_id);
        if ($snapshot === null) {
            registrarErrorCoaching($enlace_db, $gcm_id, 'Monitoreo no encontrado al conciliar coaching.');
            return;
        }

        $requiereCoaching = notaRequiereCoaching($snapshot['nota_general']);
        $paqueteVigente    = buscarPaqueteAutomaticoVigente($enlace_db, $gcm_id);

        // Nota ya no amerita coaching pero existe un paquete vigente
        // (ej.: la nota subió tras una refutación) -> se anula.
        if (!$requiereCoaching) {
            if ($paqueteVigente !== null) {
                anularPaquete($enlace_db, $paqueteVigente['gcp_id'], 'Nota corregida a >= 90 tras revisión/refutación, ya no aplica coaching.', 'SISTEMA');
            }
            return;
        }

        // La nota cambió sobre un paquete YA creado (manualmente, por el
        // analista): se reemplaza para que el paquete refleje la nota
        // correcta — esto sigue siendo automático porque es conciliación,
        // no creación nueva desde cero.
        if ($paqueteVigente !== null) {
            $notaGuardada = (float) $paqueteVigente['gcc2_nota_general'];
            if (!notaCambioTrasRefutacion($notaGuardada, $snapshot['nota_general'])) {
                return;
            }
            anularPaquete($enlace_db, $paqueteVigente['gcp_id'], 'Nota modificada por refutación/conciliación, se reemplaza el paquete.', 'SISTEMA');
            crearPaqueteAutomatico($enlace_db, $snapshot, $paqueteVigente['gcp_id']);
            return;
        }

        // Caso normal (nota < 90, sin paquete todavía): YA NO se crea
        // aquí. Queda a la espera de que el analista lo genere manual
        // desde el botón — ver coachingMonitoreoElegibleParaGenerar().
    } catch (Throwable $e) {
        registrarErrorCoaching($enlace_db, $gcm_id, $e->getMessage());
    }
}

/**
 * ¿Debe mostrarse el botón "Generar Coaching" para este monitoreo?
 * true si: nota < 90 y no existe ya un paquete vigente (evita duplicados).
 *
 * NOTA (27 jul 2026, decisión de negocio): ya NO se exige que el
 * monitoreo esté en estado terminal (Aceptado/Refutado-*) — el botón se
 * muestra igual aunque siga "Pendiente". Es seguro porque la conciliación
 * automática (evaluarDisparoCoachingAutomatico(), que ya corre en
 * aceptar.php/refutar.php) ajusta o anula el paquete solo si la nota
 * cambia después. La pantalla de confirmación sí avisa claramente si el
 * monitoreo todavía no es terminal, para que quien lo genera lo sepa.
 */
function coachingMonitoreoElegibleParaGenerar(mysqli $enlace_db, string $gcm_id): bool
{
    $snapshot = obtenerSnapshotMonitoreoCalidad($enlace_db, $gcm_id);
    if ($snapshot === null) {
        return false;
    }
    if (!notaRequiereCoaching($snapshot['nota_general'])) {
        return false;
    }
    return buscarPaqueteAutomaticoVigente($enlace_db, $gcm_id) === null;
}

/**
 * Trae y normaliza los campos de tb_gestion_calidad_monitoreo necesarios
 * para crear el paquete, en un único SELECT. Equivalencias confirmadas
 * por negocio: PECUF=gcm_nota_ecuf, PECN=gcm_nota_ecn, PENC=gcm_nota_enc.
 */
function obtenerSnapshotMonitoreoCalidad(mysqli $enlace_db, string $gcm_id): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT `gcm_id`, `gcm_matriz`, `gcm_responsable`, `gcm_analista`,
                `gcm_nota_general`, `gcm_nota_enc`, `gcm_nota_ecn`, `gcm_nota_ecuf`,
                `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`,
                `gcm_estado`, `gcm_tipo_monitoreo`, `gcm_skill_interaccion`, `gcm_tipo_gestion`,
                `gcm_segmento`, `gcm_id_sim`, `gcm_id_ani`, `gcm_observaciones_monitoreo`,
                `gcm_fecha_monitoreo`, `gcm_fecha_hora_cierre`
         FROM `tb_gestion_calidad_monitoreo` WHERE `gcm_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $gcm_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    if (!$fila) {
        return null;
    }

    // NOTA (asunción a confirmar con negocio si no coincide): gcm_responsable
    // es el agente cuya interacción fue evaluada; gcm_analista es el analista
    // de calidad que realizó la evaluación. Se toma así porque es el uso
    // consistente encontrado en las consultas reales del módulo de Calidad.
    return [
        'monitoreo_id'       => $fila['gcm_id'],
        'matriz'             => $fila['gcm_matriz'],
        'agente_id'          => $fila['gcm_responsable'],
        'analista_id'        => $fila['gcm_analista'],
        'nota_general'       => $fila['gcm_nota_general'],
        'nota_enc'           => $fila['gcm_nota_enc'],
        'nota_ecn'           => $fila['gcm_nota_ecn'],
        'nota_ecuf'          => $fila['gcm_nota_ecuf'],
        'nota_enc_estado'    => $fila['gcm_nota_enc_estado'],
        'nota_ecn_estado'    => $fila['gcm_nota_ecn_estado'],
        'nota_ecuf_estado'   => $fila['gcm_nota_ecuf_estado'],
        'gcm_estado'         => $fila['gcm_estado'],
        'tipo_monitoreo'     => $fila['gcm_tipo_monitoreo'],
        'skill_interaccion'  => $fila['gcm_skill_interaccion'],
        'tipo_gestion'       => $fila['gcm_tipo_gestion'],
        'segmento'           => $fila['gcm_segmento'],
        'id_sim'             => $fila['gcm_id_sim'],
        'id_ani'             => $fila['gcm_id_ani'],
        'observaciones'      => $fila['gcm_observaciones_monitoreo'],
        'fecha_monitoreo'    => $fila['gcm_fecha_monitoreo'] ?: null,
        'fecha_cierre'       => $fila['gcm_fecha_hora_cierre'] ?: null,
    ];
}

// Nota: registrarErrorCoaching() ahora vive en coaching_datos.php (capa
// compartida), porque coaching_firma.php también necesita auditar
// incidentes sin depender de este archivo.