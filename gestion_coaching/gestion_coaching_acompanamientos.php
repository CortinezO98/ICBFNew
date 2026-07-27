<?php
/**
 * DESCONTINUADO el 27 jul 2026 — el sistema de "Acompañamiento" se
 * consolidó en "Paquetes" (tb_gestion_coaching_paquete). Este archivo ya
 * no debe usarse; se deja como redirección para que nadie que tenga esta
 * URL guardada (o la escriba directo) se tope con un error, o peor, siga
 * guardando datos en tablas que ya no se leen en ningún lado.
 *
 * El código original queda respaldado en el historial de Git, por si
 * alguna vez se necesita consultar.
 */
require_once("../config/validaciones_seguridad.php");
$_SESSION['aviso_redireccion'] = "El módulo de Acompañamiento se unificó con Coaching. Ahora todo vive en \"Mis paquetes\".";
header("Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes");
exit;
