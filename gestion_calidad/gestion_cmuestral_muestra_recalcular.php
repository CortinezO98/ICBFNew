<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    // error_reporting(E_ALL);
// ini_set('display_errors', '1');

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Recalcular Muestra";
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $fecha_calculadora=validar_input($_GET['date']);
    $semana=intval(substr($fecha_calculadora, 9, 2));

    if(isset($_POST["guardar_registro"])){
        $segmento_id=$_POST['segmento_id'];
        $segmento_peso=$_POST['segmento_peso'];
        $total_mes=$_POST['total_mes'];

        if($_SESSION['registro_creado_recalcular']!=1){
            // Prepara la sentencia
            $consulta_actualizar_segmento = $enlace_db->prepare("UPDATE `tb_gestion_calidad_cmuestral_mensual` SET `cmm_muestra_realizada`=? WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_segmento->bind_param('ssss', $muestra_realizada, $id_registro, $cmm_mes, $cmm_segmento);
            
            // Prepara la sentencia
            $consulta_actualizar_recalcular = $enlace_db->prepare("UPDATE `tb_gestion_calidad_cmuestral_mensual` SET `cmm_muestra_recalculada`=? WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_recalcular->bind_param('ssss', $cmm_muestra_recalculada, $id_registro, $cmm_mes, $cmm_segmento);

            $control_recalcular=0;
            for ($i=0; $i < count($segmento_id); $i++) {
                $cmm_segmento=$segmento_id[$i];
                $muestra_realizada=$total_mes[$i];
                $cmm_mes=substr($fecha_calculadora, 0, 7).'-S'.intval($semana-1);
                // Ejecuta sentencia preparada
                $consulta_actualizar_segmento->execute();

                $filtro_mes='%'.substr($fecha_calculadora, 0, -3).'%';
                $consulta_string_parametros="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, `cmm_muestra_realizada`, `cmm_muestra_recalculada` FROM `tb_gestion_calidad_cmuestral_mensual` WHERE `cmm_calculadora`=? AND `cmm_mes` LIKE ? ORDER BY `cmm_mes` ASC";
                $consulta_registros_parametros = $enlace_db->prepare($consulta_string_parametros);
                $consulta_registros_parametros->bind_param("ss", $id_registro, $filtro_mes);
                $consulta_registros_parametros->execute();
                $resultado_registros_parametros = $consulta_registros_parametros->get_result()->fetch_all(MYSQLI_NUM);

                for ($j=0; $j < count($resultado_registros_parametros); $j++) { 
                    $array_semanas[]=$resultado_registros_parametros[$j][2];
                    $id_semana=intval(substr($resultado_registros_parametros[$j][2], 9, 2));
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]]['total_mes']=$resultado_registros_parametros[$j][4];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]]['muestra_calculada']=$resultado_registros_parametros[$j][5];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]]['muestra_auditoria']=$resultado_registros_parametros[$j][6];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['muestra_semanal']=$resultado_registros_parametros[$j][13];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['dias_semanal']=$resultado_registros_parametros[$j][10];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['dias_semanal_cantidad']=$resultado_registros_parametros[$j][11];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['peso_semanal']=$resultado_registros_parametros[$j][12];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['numero_agentes_semanal']=$resultado_registros_parametros[$j][7];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['monitoreos_agentes_semanal']=$resultado_registros_parametros[$j][9];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['monitoreos_realizados']=$resultado_registros_parametros[$j][14];
                    $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['monitoreos_recalculados']=$resultado_registros_parametros[$j][15];
                }

                $array_semanas=array_values(array_unique($array_semanas));

                $total_realizado=0;
                for ($j=1; $j <= $semana; $j++) { 
                    $total_realizado+=floatval($array_segmento_detalle[$cmm_segmento][$j]['monitoreos_realizados']);
                }
                $total_peso_semanas=0;
                for ($j=$semana; $j <= count($array_semanas); $j++) {
                    $total_peso_semanas+=floatval($array_segmento_detalle[$cmm_segmento][$j]['dias_semanal_cantidad']);
                }

                $total_muestra_calculada=$array_segmento_detalle[$cmm_segmento]['muestra_calculada']-$total_realizado;
                for ($j=$semana; $j <= count($array_semanas); $j++) {
                    $cmm_semana_porcentaje=($array_segmento_detalle[$cmm_segmento][$j]['dias_semanal_cantidad']/$total_peso_semanas)*100;
                    $cmm_muestra_recalculada=round(($total_muestra_calculada*$cmm_semana_porcentaje)/100)+1;
                    $cmm_mes=substr($fecha_calculadora, 0, 7).'-S'.$j;
                    // Ejecuta sentencia preparada
                    $consulta_actualizar_recalcular->execute();

                    if (comprobarSentencia($enlace_db->info)) {
                        $control_recalcular++;
                    }
                }
            }
            $total_control_semanas=(count($array_semanas)+1-$semana)*count($segmento_id);

            if ($control_recalcular==$total_control_semanas) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                $_SESSION['registro_creado_recalcular']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_segmento="SELECT `cms_id`, `cms_calculadora`, `cms_nombre_segmento`, `cms_peso` FROM `tb_gestion_calidad_cmuestral_segmento` WHERE `cms_calculadora`=? ORDER BY `cms_nombre_segmento` ASC";

    $consulta_registros_segmento = $enlace_db->prepare($consulta_string_segmento);
    $consulta_registros_segmento->bind_param("s", $id_registro);
    $consulta_registros_segmento->execute();
    $resultado_registros_segmento = $consulta_registros_segmento->get_result()->fetch_all(MYSQLI_NUM);

    $filtro_mes='%'.substr($fecha_calculadora, 0, -3).'%';
    $consulta_string_parametros="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, `cmm_muestra_realizada`, `cmm_muestra_recalculada` FROM `tb_gestion_calidad_cmuestral_mensual` WHERE `cmm_calculadora`=? AND `cmm_mes` LIKE ? ORDER BY `cmm_mes` ASC";
    $consulta_registros_parametros = $enlace_db->prepare($consulta_string_parametros);
    $consulta_registros_parametros->bind_param("ss", $id_registro, $filtro_mes);
    $consulta_registros_parametros->execute();
    $resultado_registros_parametros = $consulta_registros_parametros->get_result()->fetch_all(MYSQLI_NUM);

    for ($j=0; $j < count($resultado_registros_parametros); $j++) { 
        $array_semanas[]=$resultado_registros_parametros[$j][2];
        $id_semana=intval(substr($resultado_registros_parametros[$j][2], 9, 2));
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]]['total_mes']=$resultado_registros_parametros[$j][4];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]]['muestra_calculada']=$resultado_registros_parametros[$j][5];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]]['muestra_auditoria']=$resultado_registros_parametros[$j][6];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['muestra_semanal']=$resultado_registros_parametros[$j][13];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['dias_semanal']=$resultado_registros_parametros[$j][10];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['dias_semanal_cantidad']=$resultado_registros_parametros[$j][11];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['peso_semanal']=$resultado_registros_parametros[$j][12];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['numero_agentes_semanal']=$resultado_registros_parametros[$j][7];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['monitoreos_agentes_semanal']=$resultado_registros_parametros[$j][9];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['monitoreos_realizados']=$resultado_registros_parametros[$j][14];
        $array_segmento_detalle[$resultado_registros_parametros[$j][3]][$id_semana]['monitoreos_recalculados']=$resultado_registros_parametros[$j][15];
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_cmuestral_configurar.php?reg=".base64_encode($id_registro)."&date=".$fecha_calculadora;
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
            <div class="col-md-7 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="semana" class="m-0">Semana</label>
                          <input type="text" class="form-control form-control-sm" name="semana" id="semana" value="<?php echo $fecha_calculadora; ?>" readonly required>
                        </div>
                    </div>
                    <?php for ($i=0; $i < count($resultado_registros_segmento); $i++): ?>
                        <div class="col-md-6">
                            <div class="form-group">
                              <label for="segmento">Segmento</label>
                              <input type="hidden" class="form-control form-control-sm" name="segmento_id[]" value="<?php echo $resultado_registros_segmento[$i][0]; ?>" <?php if($_SESSION['registro_creado_recalcular']==1) { echo 'readonly'; } ?> required>
                              <input type="hidden" class="form-control form-control-sm" name="segmento_peso[]" value="<?php echo $resultado_registros_segmento[$i][3]; ?>" <?php if($_SESSION['registro_creado_recalcular']==1) { echo 'readonly'; } ?> required>
                              <input type="text" class="form-control form-control-sm" name="segmento[]" id="segmento" value="<?php echo $resultado_registros_segmento[$i][2]; ?>" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                              <label for="total_mes">Total muestras realizadas semana anterior</label>
                              <input type="number" class="form-control form-control-sm" name="total_mes[]" id="total_mes" min="0" step="1" value="<?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]][$semana-1]['monitoreos_realizados']; ?>" <?php if($_SESSION['registro_creado_recalcular']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado_recalcular']==1): ?>
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