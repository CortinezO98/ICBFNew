<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_datos.php';
require_once __DIR__ . '/coaching_notificaciones.php';

/**
 * gestion_coaching/lib/coaching_transiciones.php
 *
 * Único punto de entrada para cambiar tb_gestion_coaching_paquete.gcp_estado_id.
 * Ningún archivo de vista debe hacer UPDATE directo sobre ese campo.
 *
 * Autorización por RECURSO (no por rol global): el "perfil autorizado" de
 * la transición se resuelve comparando el usu_id actuante contra
 * gcp_agente_id / gcp_supervisor_id del paquete concreto, no contra un
 * catálogo genérico de roles — así se cierra la puerta a que un supervisor
 * de OTRO equipo actúe sobre un paquete que no le pertenece (IDOR).
 * 'Sistema' es el único perfil que no se ata a un usu_id real (lo usan
 * coaching_disparador.php y procesos internos).
 */
function ejecutarTransicion(
    mysqli $enlace_db,
    string $gcp_id,
    string $codigo_accion,
    string $usu_id_actor,
    ?string $ip,
    ?string $comentario
): void {
    $enlace_db->begin_transaction();
    try {
        $consulta = $enlace_db->prepare(
            "SELECT `gcp_estado_id`, `gcp_agente_id`, `gcp_supervisor_id`, `gcp_version`, `gcp_activo`
             FROM `tb_gestion_coaching_paquete` WHERE `gcp_id` = ? FOR UPDATE"
        );
        $consulta->bind_param('s', $gcp_id);
        $consulta->execute();
        $paquete = $consulta->get_result()->fetch_assoc();

        if (!$paquete) {
            throw new RuntimeException('El paquete de coaching indicado no existe.');
        }
        if ((int) $paquete['gcp_activo'] !== 1) {
            throw new RuntimeException('El paquete está anulado y no admite más transiciones.');
        }

        $perfil = resolverPerfilActuante($usu_id_actor, $paquete);

        $consulta_transicion = $enlace_db->prepare(
            "SELECT T.`gcet_id`, T.`gcet_estado_destino`, T.`gcet_requiere_motivo`, E2.`gce_codigo` AS estado_destino_codigo
             FROM `tb_gestion_coaching_estado_transicion` AS T
             LEFT JOIN `tb_gestion_coaching_estado` AS E2 ON T.`gcet_estado_destino` = E2.`gce_id`
             WHERE T.`gcet_estado_origen` = ? AND T.`gcet_accion` = ? AND T.`gcet_perfil_autorizado` = ? AND T.`gcet_activo` = 1
             LIMIT 1"
        );
        $consulta_transicion->bind_param('iss', $paquete['gcp_estado_id'], $codigo_accion, $perfil);
        $consulta_transicion->execute();
        $transicion = $consulta_transicion->get_result()->fetch_assoc();

        if (!$transicion) {
            throw new RuntimeException(
                'No tiene autorización para realizar esta acción sobre el paquete en su estado actual, ' .
                'o la transición no está configurada.'
            );
        }

        if ((int) $transicion['gcet_requiere_motivo'] === 1 && (trim((string) $comentario) === '')) {
            throw new RuntimeException('Esta acción requiere indicar un motivo/comentario obligatorio.');
        }

        $actualizar = $enlace_db->prepare(
            "UPDATE `tb_gestion_coaching_paquete`
             SET `gcp_estado_id` = ?, `gcp_version` = `gcp_version` + 1,
                 `gcp_fecha_cierre` = IF(? = 'CERRADO', NOW(), `gcp_fecha_cierre`)
             WHERE `gcp_id` = ? AND `gcp_version` = ?"
        );
        $estado_destino = (int) $transicion['gcet_estado_destino'];
        $codigo_destino = (string) $transicion['estado_destino_codigo'];
        $version_esperada = (int) $paquete['gcp_version'];
        $actualizar->bind_param('issi', $estado_destino, $codigo_destino, $gcp_id, $version_esperada);
        $actualizar->execute();

        if ($actualizar->affected_rows === 0) {
            // Otro proceso modificó el paquete entre el SELECT FOR UPDATE y
            // este UPDATE (no debería pasar gracias al FOR UPDATE, pero se
            // deja como cinturón de seguridad ante configuraciones de
            // aislamiento distintas).
            throw new RuntimeException('El paquete fue actualizado por otro usuario, por favor recargue e intente de nuevo.');
        }

        insertarHistorial(
            $enlace_db,
            $gcp_id,
            (int) $paquete['gcp_estado_id'],
            $estado_destino,
            $codigo_accion,
            $comentario,
            $usu_id_actor,
            $ip
        );

        $enlace_db->commit();

        // Notificación por correo: DESPUÉS del commit, nunca dentro de la
        // transacción — un fallo al notificar (SMTP mal configurado, etc.)
        // jamás debe revertir un cambio de estado que ya ocurrió.
        coachingNotificarPorTransicion($enlace_db, $gcp_id, $transicion['estado_destino_codigo'], [
            'gcp_agente_id'     => $paquete['gcp_agente_id'],
            'gcp_supervisor_id' => $paquete['gcp_supervisor_id'],
        ]);
    } catch (Throwable $e) {
        $enlace_db->rollback();
        throw $e;
    }
}

/**
 * Resuelve el "perfil autorizado" para efectos de la transición, atado al
 * recurso concreto (no a un catálogo genérico de roles). Ver nota de
 * autorización por recurso al inicio del archivo.
 */
function resolverPerfilActuante(string $usu_id_actor, array $paquete): string
{
    if ($usu_id_actor === 'SISTEMA') {
        return 'Sistema';
    }
    if ($usu_id_actor === $paquete['gcp_agente_id']) {
        return 'Agente';
    }
    if ($usu_id_actor === $paquete['gcp_supervisor_id']) {
        return 'Supervisor';
    }
    // Ningún match directo: se retorna un valor que nunca existirá en
    // tb_gestion_coaching_estado_transicion, de forma que la búsqueda de
    // transición simplemente no encuentre fila y se rechace la acción.
    // (Nota: Calidad/Coordinación/Gerencia/Administrador con permisos
    // ampliados de consulta o de configuración se resuelven en
    // coaching_seguridad.php para lectura, no participan del motor de
    // transiciones salvo que negocio confirme una acción específica para
    // esos perfiles).
    return '__SIN_RELACION__';
}



