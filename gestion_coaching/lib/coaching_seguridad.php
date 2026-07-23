<?php
declare(strict_types=1);

/**
 * gestion_coaching/lib/coaching_seguridad.php
 *
 * Autorización POR RECURSO para operaciones de LECTURA (bandeja, detalle,
 * reportes). Para escritura/transiciones de estado, coaching_transiciones.php
 * ya resuelve su propia autorización comparando contra el paquete concreto.
 *
 * Se apoya en $_SESSION['modulos_acceso_permisos'] que YA carga
 * contenido.php para todo el sistema (no se reinventa el mecanismo de
 * sesión, solo se usa lo que ya existe).
 */

/** Perfil que tiene el usuario actual para el módulo 'Coaching', o null si no tiene acceso. */
function coachingPerfilUsuarioActual(): ?string
{
    if (isset($_SESSION['modulos_acceso_permisos']['Coaching']) && $_SESSION['modulos_acceso_permisos']['Coaching'] !== '') {
        return $_SESSION['modulos_acceso_permisos']['Coaching'];
    }
    return null;
}

/**
 * Filtro de alcance para listados (bandeja): construye la condición SQL +
 * parámetros según el perfil del usuario autenticado, para que nadie vea
 * paquetes fuera de su alcance real, independientemente de lo que el
 * frontend muestre u oculte.
 *
 * Perfiles reconocidos (ver tb_configuracion_perfil_usu_mod.per_perfil):
 *  - 'Administrador' / 'Gestor'  -> ve todo (sujeto a filtros de UI, no de seguridad)
 *  - 'Supervisor'                -> solo paquetes donde gcp_supervisor_id = su usu_id
 *  - 'Agente'                    -> solo paquetes donde gcp_agente_id = su usu_id
 *  - Calidad/Coordinación/Gerencia -> ven todo en modo solo-lectura (ampliar aquí si negocio define alcance por campaña)
 *
 * @return array{0:string,1:array} [fragmento SQL a concatenar con AND, parámetros correspondientes]
 */
function coachingFiltroAlcance(string $perfil, string $usu_id): array
{
    switch ($perfil) {
        case 'Agente':
            return ['AND `gcp_agente_id` = ?', [$usu_id]];
        case 'Supervisor':
            return ['AND `gcp_supervisor_id` = ?', [$usu_id]];
        default:
            // Administrador, Gestor, Calidad, Coordinación, Gerencia: sin
            // restricción adicional de propietario. Si negocio confirma un
            // alcance por campaña para Coordinación/Gerencia, se agrega aquí
            // comparando contra tb_administrador_usuario.usu_campania.
            return ['', []];
    }
}

/**
 * Cuenta cuántos paquetes requieren acción del usuario actual EN ESTE
 * MOMENTO (no todos sus paquetes, solo los que están "en su cancha").
 * Usado por la alerta flotante — ver coaching_widget_flotante.php.
 */
function coachingContarPendientesAccion(mysqli $enlace_db, string $usu_id, string $perfil): int
{
    if ($perfil === 'Agente') {
        $consulta = $enlace_db->prepare(
            "SELECT COUNT(*) AS total FROM `tb_gestion_coaching_paquete`
             WHERE `gcp_agente_id` = ? AND `gcp_activo` = 1
               AND `gcp_estado_id` IN (
                   SELECT `gce_id` FROM `tb_gestion_coaching_estado` WHERE `gce_codigo` IN ('PENDIENTE_AGENTE','PENDIENTE_FIRMA_AGENTE')
               )"
        );
        $consulta->bind_param('s', $usu_id);
    } elseif ($perfil === 'Supervisor') {
        $consulta = $enlace_db->prepare(
            "SELECT COUNT(*) AS total FROM `tb_gestion_coaching_paquete`
             WHERE `gcp_supervisor_id` = ? AND `gcp_activo` = 1
               AND `gcp_estado_id` IN (
                   SELECT `gce_id` FROM `tb_gestion_coaching_estado` WHERE `gce_codigo` IN ('ASIGNADO','PENDIENTE_SUPERVISOR','RESPONDIDO_AGENTE','PENDIENTE_CIERRE')
               )"
        );
        $consulta->bind_param('s', $usu_id);
    } else {
        return 0;
    }
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    return (int) ($fila['total'] ?? 0);
}

/**
 * Igual que coachingContarPendientesAccion(), pero además devuelve el
 * gcp_id cuando hay exactamente un paquete pendiente — para que la alerta
 * flotante pueda enlazar directo al paquete en vez de siempre a la
 * bandeja general.
 */
function coachingPendientesDetalle(mysqli $enlace_db, string $usu_id, string $perfil): array
{
    if ($perfil === 'Agente') {
        $consulta = $enlace_db->prepare(
            "SELECT `gcp_id` FROM `tb_gestion_coaching_paquete`
             WHERE `gcp_agente_id` = ? AND `gcp_activo` = 1
               AND `gcp_estado_id` IN (
                   SELECT `gce_id` FROM `tb_gestion_coaching_estado` WHERE `gce_codigo` IN ('PENDIENTE_AGENTE','PENDIENTE_FIRMA_AGENTE')
               )"
        );
        $consulta->bind_param('s', $usu_id);
    } elseif ($perfil === 'Supervisor') {
        $consulta = $enlace_db->prepare(
            "SELECT `gcp_id` FROM `tb_gestion_coaching_paquete`
             WHERE `gcp_supervisor_id` = ? AND `gcp_activo` = 1
               AND `gcp_estado_id` IN (
                   SELECT `gce_id` FROM `tb_gestion_coaching_estado` WHERE `gce_codigo` IN ('ASIGNADO','PENDIENTE_SUPERVISOR','RESPONDIDO_AGENTE','PENDIENTE_CIERRE')
               )"
        );
        $consulta->bind_param('s', $usu_id);
    } else {
        return ['total' => 0, 'unico_gcp_id' => null];
    }
    $consulta->execute();
    $filas = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $total = count($filas);
    return ['total' => $total, 'unico_gcp_id' => $total === 1 ? $filas[0]['gcp_id'] : null];
}


/**
 * Autorización de RECURSO para el detalle de un paquete concreto: ¿puede
 * este usuario ver ESTE gcp_id? Complementa (no reemplaza) el filtro de
 * alcance de la bandeja — se vuelve a validar aquí porque alguien podría
 * intentar acceder directo por URL a un gcp_id que no le corresponde
 * (defensa contra IDOR, ver Entregable 5 sección 2).
 */
function usuarioPuedeVerPaquete(mysqli $enlace_db, string $usu_id, string $perfil, string $gcp_id): bool
{
    if (in_array($perfil, ['Administrador', 'Gestor', 'Calidad', 'Coordinación', 'Gerencia'], true)) {
        return true;
    }

    $consulta = $enlace_db->prepare(
        "SELECT `gcp_agente_id`, `gcp_supervisor_id` FROM `tb_gestion_coaching_paquete` WHERE `gcp_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $gcp_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    if (!$fila) {
        return false;
    }

    if ($perfil === 'Agente') {
        return $fila['gcp_agente_id'] === $usu_id;
    }
    if ($perfil === 'Supervisor') {
        return $fila['gcp_supervisor_id'] === $usu_id;
    }
    return false;
}



