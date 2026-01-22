<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $consulta_string="SELECT `usu_id`, `usu_acceso`, `usu_contrasena`, `usu_nombres_apellidos`, `usu_correo_corporativo`, `usu_fecha_incorporacion`, `usu_campania`, `usu_usuario_red`, `usu_cargo_rol`, `usu_sede`, `usu_ciudad`, `usu_estado`, `usu_supervisor`, `usu_lider_calidad`, `usu_inicio_sesion`, `usu_piloto` FROM `tb_administrador_usuario` WHERE `usu_contrasena`=''";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros); $i++) { 
        $salt = substr(base64_encode(openssl_random_pseudo_bytes('30')), 0, 22);
        $salt = strtr($salt, array('+' => '.'));
        $contrasena = crypt($resultado_registros[$i][0], '$2y$10$' . $salt);

        // Prepra la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_administrador_usuario` SET `usu_contrasena`=? WHERE `usu_id`=?");
        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param("ss", $contrasena, $resultado_registros[$i][0]);
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
                
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion.= "<br>".$resultado_registros[$i][0]." | Actualizado";
        } else {
            $respuesta_accion.= "<br>".$resultado_registros[$i][0]." | No Actualizado";
        }
        
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>