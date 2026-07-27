<?php
/**
 * DESCONTINUADO el 27 jul 2026 — usar gestion_coaching_crear.php.
 * Ver gestion_coaching_acompanamientos.php para el detalle de la
 * consolidación.
 */
require_once("../config/validaciones_seguridad.php");
$_SESSION['aviso_redireccion'] = "El módulo de Acompañamiento se unificó con Coaching. Use \"Nuevo paquete\".";
header("Location:gestion_coaching_crear.php");
exit;
