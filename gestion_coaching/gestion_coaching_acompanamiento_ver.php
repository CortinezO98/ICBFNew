<?php
/**
 * DESCONTINUADO el 27 jul 2026 — usar gestion_coaching_ver.php.
 */
require_once("../config/validaciones_seguridad.php");
$_SESSION['aviso_redireccion'] = "El módulo de Acompañamiento se unificó con Coaching. Ahora todo vive en \"Mis paquetes\".";
header("Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes");
exit;
