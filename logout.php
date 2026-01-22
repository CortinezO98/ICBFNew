<?php
  session_start();
  unset($_SESSION['usu_id']);
  unset($_SESSION['usu_acceso']);
  unset($_SESSION['usu_nombre_completo']);
  unset($_SESSION['usu_estado_usuario']);
  unset($_SESSION['usu_inicio_sesion']);
  unset($_SESSION['ope_nombre_area']);
  unset($_SESSION['modulos_acceso']);
  unset($_SESSION['modulos_acceso_permisos']);

  session_destroy();
  header("Location: index.php");
  exit;
?>



