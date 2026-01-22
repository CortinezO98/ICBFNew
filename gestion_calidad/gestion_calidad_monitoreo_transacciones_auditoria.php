<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Auditoría | Transacciones | Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $bandeja=validar_input(base64_decode($_GET['bandeja']));

    if(isset($_POST["guardar_registro"])){
        $fecha_inicio=validar_input($_POST['fecha_inicio']);
        $fecha_fin=validar_input($_POST['fecha_fin']);
        $fecha_fin_filtro=$fecha_fin.' 23:59:59';
        $telefonico=validar_input($_POST['telefonico']);
        $virtual=validar_input($_POST['virtual']);

        if($_SESSION['gcmta_registro_creado']!=1){
            $consulta_string_auditoria_count="SELECT COUNT(`tb_gestion_calidad_monitoreo`.`gcm_id`) FROM `tb_gestion_calidad_monitoreo` LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON `tb_gestion_calidad_monitoreo`.`gcm_matriz`=TM.`gcm_id` WHERE `tb_gestion_calidad_monitoreo`.`gcm_auditoria`='Si' AND `tb_gestion_calidad_monitoreo`.`gcm_registro_fecha`>=? AND `tb_gestion_calidad_monitoreo`.`gcm_registro_fecha`<=?";

            $consulta_registros_auditoria_count = $enlace_db->prepare($consulta_string_auditoria_count);
            $consulta_registros_auditoria_count->bind_param("ss", $fecha_inicio, $fecha_fin_filtro);
            $consulta_registros_auditoria_count->execute();
            $resultado_registros_auditoria_count = $consulta_registros_auditoria_count->get_result()->fetch_all(MYSQLI_NUM);

            //Validar si existe auditorías y mostrar o no bvotón de crear auditoría, 
            if ($resultado_registros_auditoria_count[0][0]==0) {
                $consulta_string_auditoria_telefonico="SELECT TMON.`gcm_id`, TMON.`gcm_matriz`, TMON.`gcm_analista`, CAST(TMON.`gcm_nota_general` AS UNSIGNED) FROM `tb_gestion_calidad_monitoreo` AS TMON WHERE TMON.`gcm_aplica_indicador`='Si' AND TMON.`gcm_registro_fecha`>=? AND TMON.`gcm_registro_fecha`<=? AND CAST(TMON.`gcm_nota_general` AS UNSIGNED)>=91 AND (TMON.`gcm_segmento`='Línea 141' OR TMON.`gcm_segmento`='Línea Nacional' OR TMON.`gcm_segmento`='Conmutador') AND TMON.`gcm_fecha_hora_cierre`>3 AND TMON.`gcm_fecha_hora_cierre`<=30 ORDER BY CAST(TMON.`gcm_nota_general` AS UNSIGNED) ASC";
                $consulta_registros_auditoria_telefonico = $enlace_db->prepare($consulta_string_auditoria_telefonico);
                $consulta_registros_auditoria_telefonico->bind_param("ss", $fecha_inicio, $fecha_fin_filtro);
                $consulta_registros_auditoria_telefonico->execute();
                $resultado_registros_auditoria_telefonico = $consulta_registros_auditoria_telefonico->get_result()->fetch_all(MYSQLI_NUM);

                $array_agentes=array();
                $array_agentes_monitoreos=array();
                for ($i=0; $i < count($resultado_registros_auditoria_telefonico); $i++) { 
                    $array_agentes[]=$resultado_registros_auditoria_telefonico[$i][2];
                    $array_agentes_monitoreos[$resultado_registros_auditoria_telefonico[$i][2]][]=$resultado_registros_auditoria_telefonico[$i][0];
                    shuffle($array_agentes_monitoreos[$resultado_registros_auditoria_telefonico[$i][2]]);
                }

                $array_agentes=array_values(array_unique($array_agentes));

                shuffle($array_agentes);
                shuffle($array_agentes);

                // Prepara la sentencia
                $consulta_actualizar_auditoria = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo` SET `gcm_auditoria`=? WHERE  `gcm_id`=?");

                // Agrega variables a sentencia preparada
                $consulta_actualizar_auditoria->bind_param('ss', $gcm_auditoria, $gcm_id);
                
                $control_auditoria=0;
                for ($j=0; $j < $telefonico; $j++) {
                    $id_agente=$array_agentes[$j];
                    $gcm_auditoria='Si';
                    $gcm_id=$array_agentes_monitoreos[$id_agente][0];

                    if (isset($array_agentes_monitoreos[$id_agente][0]) AND $array_agentes_monitoreos[$id_agente][0]!='') {
                        // Ejecuta sentencia preparada
                        $consulta_actualizar_auditoria->execute();
                        unset($array_agentes_monitoreos[$id_agente][0]);
                        $array_agentes_monitoreos[$id_agente]=array_values($array_agentes_monitoreos[$id_agente]);
                        $control_auditoria++;
                    }

                    if ($j>=count($array_agentes)) {
                        $j=0;
                    }

                    if ($control_auditoria==$telefonico) {
                        break;
                    }
                }


                $consulta_string_auditoria_virtual="SELECT TMON.`gcm_id`, TMON.`gcm_matriz`, TMON.`gcm_analista`, CAST(TMON.`gcm_nota_general` AS UNSIGNED) FROM `tb_gestion_calidad_monitoreo` AS TMON WHERE TMON.`gcm_aplica_indicador`='Si' AND TMON.`gcm_registro_fecha`>=? AND TMON.`gcm_registro_fecha`<=? AND CAST(TMON.`gcm_nota_general` AS UNSIGNED)>=91 AND (TMON.`gcm_segmento`='Aseguramiento' OR TMON.`gcm_segmento`='Encuestas' OR TMON.`gcm_segmento`='Profesional Es Abogados' OR TMON.`gcm_segmento`='Profesional ES Psicólogos' OR TMON.`gcm_segmento`='WhatsApp' OR TMON.`gcm_segmento`='Video Llamada' OR TMON.`gcm_segmento`='Correos y Portales' OR TMON.`gcm_segmento`='Presencial' OR TMON.`gcm_segmento`='Chat ICBF' OR TMON.`gcm_segmento`='Canal escrito' OR TMON.`gcm_segmento`='Redes Sociales') ORDER BY CAST(TMON.`gcm_nota_general` AS UNSIGNED) ASC";
                $consulta_registros_auditoria_virtual = $enlace_db->prepare($consulta_string_auditoria_virtual);
                $consulta_registros_auditoria_virtual->bind_param("ss", $fecha_inicio, $fecha_fin_filtro);
                $consulta_registros_auditoria_virtual->execute();
                $resultado_registros_auditoria_virtual = $consulta_registros_auditoria_virtual->get_result()->fetch_all(MYSQLI_NUM);

                $array_agentes=array();
                $array_agentes_monitoreos=array();
                for ($i=0; $i < count($resultado_registros_auditoria_virtual); $i++) { 
                    $array_agentes[]=$resultado_registros_auditoria_virtual[$i][2];
                    $array_agentes_monitoreos[$resultado_registros_auditoria_virtual[$i][2]][]=$resultado_registros_auditoria_virtual[$i][0];
                    shuffle($array_agentes_monitoreos[$resultado_registros_auditoria_virtual[$i][2]]);
                }

                $array_agentes=array_values(array_unique($array_agentes));

                shuffle($array_agentes);
                shuffle($array_agentes);

                // Prepara la sentencia
                $consulta_actualizar_auditoria = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo` SET `gcm_auditoria`=? WHERE  `gcm_id`=?");

                // Agrega variables a sentencia preparada
                $consulta_actualizar_auditoria->bind_param('ss', $gcm_auditoria, $gcm_id);
                
                $control_auditoria=0;
                for ($j=0; $j < $virtual; $j++) {
                    $id_agente=$array_agentes[$j];
                    $gcm_auditoria='Si';
                    $gcm_id=$array_agentes_monitoreos[$id_agente][0];

                    if (isset($array_agentes_monitoreos[$id_agente][0]) AND $array_agentes_monitoreos[$id_agente][0]!='') {
                        // Ejecuta sentencia preparada
                        $consulta_actualizar_auditoria->execute();
                        unset($array_agentes_monitoreos[$id_agente][0]);
                        $array_agentes_monitoreos[$id_agente]=array_values($array_agentes_monitoreos[$id_agente]);
                        $control_auditoria++;
                    }

                    if ($j>=count($array_agentes)) {
                        $j=0;
                    }

                    if ($control_auditoria==$virtual) {
                        break;
                    }
                }

                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                $_SESSION['gcmta_registro_creado']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡1Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_matriz="SELECT `gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`, `gcm_registro_fecha` FROM `tb_gestion_calidad_matriz` WHERE `gcm_estado`='Activo' ORDER BY `gcm_nombre_matriz` ASC";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo_transacciones.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode($bandeja);
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
            <div class="col-md-4 pt-2 background-blanco">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="fecha_inicio" id="fecha_inicio">Fecha inicio</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="fecha_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $fecha_inicio; } ?>" <?php if($_SESSION['gcmta_registro_creado']==1) { echo 'readonly'; } ?> required autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="fecha_fin" id="fecha_fin">Fecha fin</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_fin" id="fecha_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $fecha_fin; } ?>" <?php if($_SESSION['gcmta_registro_creado']==1) { echo 'readonly'; } ?> required autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="telefonico">Telefónico</label>
                            <input type="number" class="form-control form-control-sm" name="telefonico" id="telefonico" min="1" max="500" value="<?php if(isset($_POST["guardar_registro"])){ echo $telefonico; } ?>" <?php if($_SESSION['gcmta_registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="virtual">Virtual</label>
                            <input type="number" class="form-control form-control-sm" name="virtual" id="virtual" min="1" max="500" value="<?php if(isset($_POST["guardar_registro"])){ echo $virtual; } ?>" <?php if($_SESSION['gcmta_registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['gcmta_registro_creado']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
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