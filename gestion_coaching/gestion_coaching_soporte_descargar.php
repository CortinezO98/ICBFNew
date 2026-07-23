<?php
$modulo_plataforma='Coaching';require_once('../config/validaciones_seguridad.php');require_once('../config/conexion_db.php');require_once('lib/coaching_seguridad.php');require_once('lib/coaching_complementos.php');
$perfil=coachingPerfilUsuarioActual();$sid=(int)($_GET['id']??0);$s=obtenerSoporteCoaching($enlace_db,$sid);if(!$s||!$perfil||!usuarioPuedeVerPaquete($enlace_db,$_SESSION['usu_id'],$perfil,$s['gcs_paquete'])){http_response_code(403);exit('Acceso denegado.');}descargarSoporteCoaching($s);
