<?php
    $modulo_plataforma = 'Coaching';
    require_once('../config/validaciones_seguridad.php');
    require_once('../config/conexion_db.php');
    require_once('lib/coaching_seguridad.php');
    require_once('lib/coaching_complementos.php');

    $perfil = coachingPerfilUsuarioActual();
    $sid = (int) ($_GET['id'] ?? 0);
    $s = obtenerSoporteCoaching($enlace_db, $sid);

    // gcsp_paquete es el nombre REAL de la columna (antes decía gcs_paquete,
    // que no existe — ver ESTADO_REAL_MODULO_COACHING_v2.md).
    if (!$s || !$perfil || !usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil, $s['gcsp_paquete'])) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    descargarSoporteCoaching($s);
