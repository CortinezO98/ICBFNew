<?php
declare(strict_types=1);

/**
 * gestion_coaching/lib/coaching_notificaciones.php
 *
 * Integra Coaching con la infraestructura de correo YA EXISTENTE del
 * sistema: `tb_notificaciones_central` + `tb_notificaciones_central_remitente`,
 * consumida por el robot real `config_robots/notificaciones_central.php`
 * (que ya corre por cron vía `notificaciones_central.sh`).
 *
 * NO se crea ningún robot nuevo ni ninguna tabla nueva de notificaciones:
 * esta capa solo hace INSERT en la tabla central, con el mismo formato que
 * ya usa el resto del sistema (direcciones "correo|Nombre" separadas por
 * ";", estado 'Pendiente'). El robot existente recoge y envía sin que se
 * le haya tocado ni una línea.
 */

/** Correo y nombre de un usuario, para armar destinatarios. */
function coachingCorreoYNombre(mysqli $enlace_db, string $usu_id): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT `usu_correo_corporativo`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE `usu_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $usu_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    if (!$fila || empty($fila['usu_correo_corporativo'])) {
        return null;
    }
    return ['correo' => $fila['usu_correo_corporativo'], 'nombre' => $fila['usu_nombres_apellidos']];
}

/**
 * Inserta una notificación en la tabla central. Reutiliza el remitente ya
 * configurado en el sistema (toma el primero activo — si en tu ambiente
 * hay varios remitentes para propósitos distintos, dime el `ncr_id` exacto
 * a usar para Coaching y lo fijo aquí en vez de tomar "el primero").
 */
function coachingCrearNotificacionCentral(
    mysqli $enlace_db,
    string $prioridad,
    string $correo_destino,
    string $nombre_destino,
    string $asunto,
    string $cuerpo_html,
    string $usu_id_registro
): bool {
    // Módulo real ya sembrado en tb_configuracion_modulo (migración 007).
    $consulta_modulo = $enlace_db->prepare("SELECT `mod_id` FROM `tb_configuracion_modulo` WHERE `mod_modulo_nombre` = 'Coaching' LIMIT 1");
    $consulta_modulo->execute();
    $modulo = $consulta_modulo->get_result()->fetch_assoc();
    if (!$modulo) {
        return false; // Módulo no sembrado todavía; no se puede asociar nc_id_modulo.
    }

    // Remitente estándar del sistema — confirmado en el código real
    // (gestion_calidad_monitoreo_refutar.php usa literalmente
    // `$nc_id_set_from = "1"`), no una heurística de "el primero que haya".
    $id_set_from = '1';

    $direccion = $correo_destino . '|' . $nombre_destino;

    $insertar = $enlace_db->prepare(
        "INSERT INTO `tb_notificaciones_central`
            (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
             `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
             `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
         VALUES (?, ?, ?, ?, '', '', '', ?, ?, '', '', '', '0', '0', 'Pendiente', '', ?)"
    );
    $insertar->bind_param(
        'issssss',
        $modulo['mod_id'],
        $prioridad,
        $id_set_from,
        $direccion,
        $asunto,
        $cuerpo_html,
        $usu_id_registro
    );
    return (bool) $insertar->execute();
}

/**
 * Punto único que decide QUÉ correo enviar según a qué estado quedó el
 * paquete tras una transición exitosa. Se llama desde
 * coaching_transiciones.php, nunca directamente desde las pantallas —así
 * la notificación queda garantizada sin importar por cuál pantalla se
 * disparó la transición.
 *
 * Nunca lanza excepción hacia el llamador: si la notificación falla (SMTP
 * mal configurado, correo vacío, etc.), el cambio de estado YA ocurrió y
 * NO debe revertirse por un problema de notificaciones — se audita y sigue.
 */
function coachingNotificarPorTransicion(mysqli $enlace_db, string $gcp_id, string $gce_codigo_destino, array $paquete): void
{
    try {
        $enlace_url_base = '(enlace disponible en la plataforma → módulo Coaching)';

        switch ($gce_codigo_destino) {
            case 'PENDIENTE_AGENTE':
                $destino = coachingCorreoYNombre($enlace_db, $paquete['gcp_agente_id']);
                if ($destino) {
                    coachingCrearNotificacionCentral(
                        $enlace_db, 'Media', $destino['correo'], $destino['nombre'],
                        "Coaching {$gcp_id}: tiene una retroalimentación pendiente de respuesta",
                        "Hola {$destino['nombre']},<br><br>Su supervisor registró una retroalimentación de coaching (paquete <b>{$gcp_id}</b>) que requiere su respuesta.<br><br>Ingrese a la plataforma, módulo Coaching, para responderla.<br><br>{$enlace_url_base}",
                        'SISTEMA'
                    );
                }
                break;

            case 'PENDIENTE_FIRMA_AGENTE':
                $destino = coachingCorreoYNombre($enlace_db, $paquete['gcp_agente_id']);
                if ($destino) {
                    coachingCrearNotificacionCentral(
                        $enlace_db, 'Media', $destino['correo'], $destino['nombre'],
                        "Coaching {$gcp_id}: documento listo para su firma",
                        "Hola {$destino['nombre']},<br><br>El documento de su paquete de coaching <b>{$gcp_id}</b> ya está listo para su firma electrónica.<br><br>Ingrese a la plataforma, módulo Coaching, para firmarlo.<br><br>{$enlace_url_base}",
                        'SISTEMA'
                    );
                }
                break;

            case 'PENDIENTE_CIERRE':
                $destino = coachingCorreoYNombre($enlace_db, $paquete['gcp_supervisor_id']);
                if ($destino) {
                    coachingCrearNotificacionCentral(
                        $enlace_db, 'Baja', $destino['correo'], $destino['nombre'],
                        "Coaching {$gcp_id}: agente respondió, listo para cierre",
                        "Hola {$destino['nombre']},<br><br>El agente ya firmó el documento del paquete de coaching <b>{$gcp_id}</b>. Puede revisarlo y cerrarlo cuando lo considere pertinente.<br><br>{$enlace_url_base}",
                        'SISTEMA'
                    );
                }
                break;

            case 'CERRADO':
                $destino = coachingCorreoYNombre($enlace_db, $paquete['gcp_agente_id']);
                if ($destino) {
                    coachingCrearNotificacionCentral(
                        $enlace_db, 'Baja', $destino['correo'], $destino['nombre'],
                        "Coaching {$gcp_id}: paquete cerrado",
                        "Hola {$destino['nombre']},<br><br>Su paquete de coaching <b>{$gcp_id}</b> fue cerrado. Gracias por su participación en el proceso.<br><br>{$enlace_url_base}",
                        'SISTEMA'
                    );
                }
                break;

            case 'RECHAZADO':
                $destino = coachingCorreoYNombre($enlace_db, $paquete['gcp_supervisor_id']);
                if ($destino) {
                    coachingCrearNotificacionCentral(
                        $enlace_db, 'Media', $destino['correo'], $destino['nombre'],
                        "Coaching {$gcp_id}: paquete rechazado",
                        "Hola {$destino['nombre']},<br><br>El paquete de coaching <b>{$gcp_id}</b> fue rechazado. Revise el historial para más detalle.<br><br>{$enlace_url_base}",
                        'SISTEMA'
                    );
                }
                break;

            // ASIGNADO, PENDIENTE_SUPERVISOR, EN_SEGUIMIENTO, ANULADO: sin
            // notificación por correo (el propio actor disparó la
            // transición, no hace falta avisarle a sí mismo).
        }
    } catch (Throwable $e) {
        if (function_exists('registrarErrorCoaching')) {
            registrarErrorCoaching($enlace_db, $gcp_id, 'Fallo al crear notificación por correo: ' . $e->getMessage());
        }
    }
}



