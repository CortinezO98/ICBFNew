<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_soporte=validar_input($_GET['sop']);
    
    $consulta_string="SELECT `gaa_id`, `gaa_consecutivo`, `gaa_avance`, `gaa_registro_usuario`, TU.`usu_nombres_apellidos`, `gaa_registro_fecha`, `gaa_sop1_nombre`, `gaa_sop1_ruta`, `gaa_sop1_extension`, `gaa_sop2_nombre`, `gaa_sop2_ruta`, `gaa_sop2_extension`, `gaa_sop3_nombre`, `gaa_sop3_ruta`, `gaa_sop3_extension` FROM `tb_gestion_alertas_avances` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas_avances`.`gaa_registro_usuario`=TU.`usu_id` WHERE `gaa_id`=?";
    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
    
    if ($id_soporte=="s1") {
        $ruta_documento=$resultado_registros[0][7];
        $extension_documento=strtolower($resultado_registros[0][8]);
    } elseif ($id_soporte=="s2") {
        $ruta_documento=$resultado_registros[0][10];
        $extension_documento=strtolower($resultado_registros[0][11]);
    } elseif ($id_soporte=="s3") {
        $ruta_documento=$resultado_registros[0][13];
        $extension_documento=strtolower($resultado_registros[0][14]);
    }


    
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <embed src="<?php echo $ruta_documento; ?>?ran=<?php echo generar_codigo(5); ?>" id="visor" style="width: 100%; min-height: 410px;">
        
    </div>
</div>