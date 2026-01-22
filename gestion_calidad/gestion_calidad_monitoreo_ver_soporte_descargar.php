<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";
    
	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $id_registro=validar_input($_GET['reg']);
    $tipo=validar_input($_GET['tipo']);

    if ($tipo=="mon") {
        $consulta_string_adjuntos="SELECT `gcms_id`, `gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`, `gcms_registro_fecha` FROM `tb_gestion_calidad_monitoreo_soportes` WHERE `gcms_id`=?";
    } elseif ($tipo=="his") {
        $consulta_string_adjuntos="SELECT `gcmhs_id`, `gcmhs_monitoreo`, `gcmhs_nombre`, `gcmhs_ruta`, `gcmhs_extension`, `gcmhs_registro_usuario`, `gcmhs_registro_fecha` FROM `tb_gestion_calidad_monitoreo_historial_soportes` WHERE `gcmhs_id`=?";
    }

    $consulta_registros_adjuntos = $enlace_db->prepare($consulta_string_adjuntos);
    $consulta_registros_adjuntos->bind_param("s", $id_registro);
    $consulta_registros_adjuntos->execute();
    $resultado_registros_adjuntos = $consulta_registros_adjuntos->get_result()->fetch_all(MYSQLI_NUM);

    $Url=$resultado_registros_adjuntos[0][3];
    $NombreDoc=$resultado_registros_adjuntos[0][2];
    header("Content-disposition: attachment; filename=".$NombreDoc);
    header("Content-type: MIME");
    header('Cache-Control: max-age=0');
    readfile($Url);
?>
