<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Seleccionar Transacción";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    unset($_SESSION["mon_informacion"]["matriz"]);

    // Inicializa variable tipo array
    $data_consulta=array();

    if($perfil_modulo=="Gestor"){
        $filtro_perfil=" AND TMC.`gcm_auditoria_responsable`=?";
        array_push($data_consulta, $_SESSION["usu_id"]);
    }
    
    $consulta_string_transacciones="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TMC.`gcm_nota_general`, TMC.`gcm_nota_enc_estado`, TMC.`gcm_nota_ecn_estado`, TMC.`gcm_nota_ecuf_estado`, TMC.`gcm_aplica_indicador`, TMC.`gcm_fecha_monitoreo`, TMC.`gcm_registro_usuario`, TM.`gcm_observaciones`, TMC.`gcm_auditoria`, TMC.`gcm_auditoria_responsable` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_auditoria_responsable`=TUR.`usu_id` LEFT JOIN `tb_gestion_auditoria_monitoreo` AS TAU ON TMC.`gcm_id`=TAU.`gcm_id` WHERE 1=1 AND `gcm_auditoria`='Si' ".$filtro_perfil." AND TAU.`gcm_id` IS NULL ORDER BY TMC.`gcm_id` ASC";

    $consulta_registros_transacciones = $enlace_db->prepare($consulta_string_transacciones);
    if (count($data_consulta)>0) {
        // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
        $consulta_registros_transacciones->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    $consulta_registros_transacciones->execute();
    $resultado_registros_transacciones = $consulta_registros_transacciones->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode('Mes Actual');

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
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <div class="col-md-5 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                    <p class="alert alert-success p-1">¡Seleccione una transacción para iniciar el monitoreo!</p>
                    </div>
                    <div class="col-md-12">
                        <?php for ($i=0; $i < count($resultado_registros_transacciones); $i++): ?> 
                            <a href="gestion_calidad_monitoreo_evaluacion.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&mon=<?php echo base64_encode($resultado_registros_transacciones[$i][0]); ?>" class="btn btn-corp-menu col-md-12 text-left  mb-1"><span class="fas fa-qrcode"></span> <?php echo $resultado_registros_transacciones[$i][0]; ?> [<?php echo $resultado_registros_transacciones[$i][15]; ?>]</a>
                        <?php endfor; ?>    
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 pt-2">
                        <div class="form-group">
                            <button class="btn btn-danger" type="button" onclick="guardar_cancelar();">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>