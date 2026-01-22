<?php
    require_once("config/validaciones_seguridad_raiz.php");
    require_once("config/conexion_db.php");

    $usuario_acceso="JulianTP";

    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    $consulta_string = "SELECT `usu_id`, `usu_acceso`, `usu_contrasena`, `usu_nombres_apellidos`, `usu_correo_corporativo`, `usu_estado`, `usu_inicio_sesion`, `usu_campania` FROM `tb_administrador_usuario` WHERE `usu_acceso`= ?";
	$consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $usuario_acceso);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    if (count($resultado_registros)>0) {
    	if($usuario_acceso==$resultado_registros[0][1] AND $resultado_registros[0][5]=='Activo'){
    		$_SESSION["usu_id"]=$resultado_registros[0][0];
            $_SESSION["usu_acceso"]=$resultado_registros[0][1];
            $_SESSION["usu_nombre_completo"]=$resultado_registros[0][3];
            $_SESSION["usu_estado_usuario"]=$resultado_registros[0][5];
            $_SESSION["usu_inicio_sesion"]=$resultado_registros[0][6];
            $_SESSION["usu_campania"]=$resultado_registros[0][7];

            if ($resultado_registros[0][6]==0) {
                header("Location: config_seguridad.php");
            } else {
                header("Location: contenido.php");
            }
        } else {
            $respuesta_accion = "<p class='alert alert-danger'>Inicio de sesión fallido, verifique e intente nuevamente!</p>";
        }
    } else {
        $respuesta_accion = "<p class='alert alert-danger'>Inicio de sesión fallido, verifique e intente nuevamente!</p>";
    }

?>
