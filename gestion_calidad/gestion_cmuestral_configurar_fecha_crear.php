<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Fecha - Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    // Inicializa variable tipo array
    $data_consulta_segmento=array();

    if(isset($_POST["guardar_registro"])){
        $anio_mes=validar_input($_POST['anio_mes']);
        $segmento_id=$_POST['segmento_id'];
        $segmento_peso=$_POST['segmento_peso'];
        $total_mes=$_POST['total_mes'];

        $intervalo_confianza=validar_input($_POST['intervalo_confianza'])/100;
        $valor_z=validar_input($_POST['valor_z']);
        $varianza_estimada=validar_input($_POST['varianza_estimada']);
        $error_muestral=validar_input($_POST['error_muestral'])/100;

        $total_muestra_mes=array_sum($total_mes);
        $total_muestra_calculada=round((pow($valor_z, 2)*$varianza_estimada)/(pow($error_muestral, 2)+((pow($valor_z, 2)*$varianza_estimada)/$total_muestra_mes)));
        $control_insert_semanas=0;
        $semana_1=$_POST['semana_1'];
        $semana_1_inicio=validar_input($_POST['semana_1_inicio']);
        $semana_1_fin=validar_input($_POST['semana_1_fin']);
        $semana_2_inicio=validar_input($_POST['semana_2_inicio']);
        $semana_2_fin=validar_input($_POST['semana_2_fin']);
        $semana_3_inicio=validar_input($_POST['semana_3_inicio']);
        $semana_3_fin=validar_input($_POST['semana_3_fin']);
        $semana_4_inicio=validar_input($_POST['semana_4_inicio']);
        $semana_4_fin=validar_input($_POST['semana_4_fin']);
        $semana_5_inicio=validar_input($_POST['semana_5_inicio']);
        $semana_5_fin=validar_input($_POST['semana_5_fin']);
        if (!isset($semana_1)) {
            $semana_1=array();
        }

        if (count($semana_1)>0) {
            $control_insert_semanas++;
            $semanas['peso'][]=count($semana_1);
            $semanas['dias'][]=implode(',', $semana_1);
            $semanas['inicio'][]=validar_input($_POST['semana_1_inicio']);
            $semanas['fin'][]=validar_input($_POST['semana_1_fin']);
            
        }

        $semana_2=$_POST['semana_2'];
        if (!isset($semana_2)) {
            $semana_2=array();
        }

        if (count($semana_2)>0) {
            $control_insert_semanas++;
            $semanas['peso'][]=count($semana_2);
            $semanas['dias'][]=implode(',', $semana_2);
            $semanas['inicio'][]=validar_input($_POST['semana_2_inicio']);
            $semanas['fin'][]=validar_input($_POST['semana_2_fin']);
        }

        $semana_3=$_POST['semana_3'];
        if (!isset($semana_3)) {
            $semana_3=array();
        }

        if (count($semana_3)>0) {
            $control_insert_semanas++;
            $semanas['peso'][]=count($semana_3);
            $semanas['dias'][]=implode(',', $semana_3);
            $semanas['inicio'][]=validar_input($_POST['semana_3_inicio']);
            $semanas['fin'][]=validar_input($_POST['semana_3_fin']);
        }

        $semana_4=$_POST['semana_4'];
        if (!isset($semana_4)) {
            $semana_4=array();
        }

        if (count($semana_4)>0) {
            $control_insert_semanas++;
            $semanas['peso'][]=count($semana_4);
            $semanas['dias'][]=implode(',', $semana_4);
            $semanas['inicio'][]=validar_input($_POST['semana_4_inicio']);
            $semanas['fin'][]=validar_input($_POST['semana_4_fin']);
        }

        $semana_5=$_POST['semana_5'];
        if (!isset($semana_5)) {
            $semana_5=array();
        }

        if (count($semana_5)>0) {
            $control_insert_semanas++;
            $semanas['peso'][]=count($semana_5);
            $semanas['dias'][]=implode(',', $semana_5);
            $semanas['inicio'][]=validar_input($_POST['semana_5_inicio']);
            $semanas['fin'][]=validar_input($_POST['semana_5_fin']);
        }

        if($_SESSION['registro_creado_fecha']!=1){
            // Prepara la sentencia
            $sentencia_insert_segmento = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_mensual`(`cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, `cmm_semana_inicio`, `cmm_semana_fin`, `cmm_muestra_realizada`, `cmm_muestra_recalculada`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert_segmento->bind_param('sssssssssssssssss', $id_registro, $anio_mes_insert, $cmm_segmento, $cmm_total_mes, $cmm_muestra_calculada, $cmm_muestra_auditoria, $cmm_numero_agentes, $cmm_muestras_agente_mes, $cmm_muestras_agente_semana, $cmm_semana_dias, $cmm_semana_peso, $cmm_semana_porcentaje, $cmm_semana_muestras, $cmm_semana_inicio, $cmm_semana_fin, $cmm_muestra_realizada, $cmm_semana_muestras);

            $control_insert_segmento=0;
            $total_peso_semanas=array_sum($semanas['peso']);
            for ($i=0; $i < count($segmento_id); $i++) { 
                $cmm_segmento=$segmento_id[$i];
                $cmm_total_mes=$total_mes[$i];
                $cmm_muestra_calculada=round($total_muestra_calculada*($segmento_peso[$i]/100));
                $cmm_muestra_auditoria=round($cmm_muestra_calculada*0.1);
                $cmm_numero_agentes=0;
                $cmm_muestras_agente_mes=0;
                $cmm_muestras_agente_semana=0;
                $cmm_muestra_realizada='';

                for ($j=0; $j < count($semanas['peso']); $j++) {
                    if ($semanas['peso'][$j]>0) {
                        $temp_semana=$j+1;
                        $anio_mes_insert=$anio_mes.'-S'.$temp_semana;
                        $cmm_semana_dias=$semanas['dias'][$j];
                        $cmm_semana_peso=$semanas['peso'][$j];
                        $cmm_semana_porcentaje=($cmm_semana_peso/$total_peso_semanas)*100;
                        $cmm_semana_muestras=round(($cmm_muestra_calculada*$cmm_semana_porcentaje)/100)+1;
                        $cmm_semana_inicio=$semanas['inicio'][$j];
                        $cmm_semana_fin=$semanas['fin'][$j];

                        if ($sentencia_insert_segmento->execute()) {
                            $control_insert_segmento++;
                        }
                    }
                }
            }
            $total_control_seg=count($segmento_id)*$control_insert_semanas;

            if ($control_insert_segmento==$total_control_seg) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                $_SESSION['registro_creado_fecha']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string="SELECT `cm_id`, `cm_nombre`, `cm_intervalo_confianza`, `cm_valor_z`, `cm_varianza_estimada`, `cm_error_muestral`, `cm_registro_usuario`, `cm_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral`.`cm_registro_usuario`=TU.`usu_id` WHERE `cm_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_segmento="SELECT `cms_id`, `cms_calculadora`, `cms_nombre_segmento`, `cms_peso` FROM `tb_gestion_calidad_cmuestral_segmento` WHERE `cms_calculadora`=? ORDER BY `cms_nombre_segmento` ASC";

    $consulta_registros_segmento = $enlace_db->prepare($consulta_string_segmento);
    $consulta_registros_segmento->bind_param("s", $id_registro);
    $consulta_registros_segmento->execute();
    $resultado_registros_segmento = $consulta_registros_segmento->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_cmuestral_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro);
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
                        <input type="hidden" class="form-control form-control-sm" name="intervalo_confianza" value="<?php echo $resultado_registros[0][2]; ?>" readonly required>
                        <input type="hidden" class="form-control form-control-sm" name="valor_z" value="<?php echo $resultado_registros[0][3]; ?>" readonly required>
                        <input type="hidden" class="form-control form-control-sm" name="varianza_estimada" value="<?php echo $resultado_registros[0][4]; ?>" readonly required>
                        <input type="hidden" class="form-control form-control-sm" name="error_muestral" value="<?php echo $resultado_registros[0][5]; ?>" readonly required>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="anio_mes">Año-Mes</label>
                          <input type="month" class="form-control form-control-sm" name="anio_mes" id="anio_mes" value="<?php if(isset($_POST["guardar_registro"])){ echo $anio_mes; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <?php for ($i=0; $i < count($resultado_registros_segmento); $i++): ?>
                        <div class="col-md-8">
                            <div class="form-group">
                              <label for="segmento">Segmento</label>
                              <input type="hidden" class="form-control form-control-sm" name="segmento_id[]" value="<?php echo $resultado_registros_segmento[$i][0]; ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                              <input type="hidden" class="form-control form-control-sm" name="segmento_peso[]" value="<?php echo $resultado_registros_segmento[$i][3]; ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                              <input type="text" class="form-control form-control-sm" name="segmento[]" id="segmento" value="<?php echo $resultado_registros_segmento[$i][2]; ?>" readonly required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                              <label for="total_mes">Total interacciones</label>
                              <input type="number" class="form-control form-control-sm" name="total_mes[]" id="total_mes" min="0" step="1" value="<?php if(isset($_POST["guardar_registro"])){ echo $total_mes[$i]; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                    <?php endfor; ?>
                    <div class="col-md-2">
                        <div class="form-group my-1">
                            <label for="semana_1" class="font-weight-bold my-0">Semana 1</label>
                            <div class="form-group">
                              <label for="semana_1_inicio" class="my-0 font-weight-bold font-size-11">Fecha inicio</label>
                              <input type="date" class="form-control form-control-sm" name="semana_1_inicio" id="semana_1_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_1_inicio; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                              <label for="semana_1_inicio" class="my-0 font-weight-bold font-size-11">Fecha fin</label>
                              <input type="date" class="form-control form-control-sm" name="semana_1_fin" id="semana_1_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_1_fin; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1L" name="semana_1[]" value="L" <?php if(isset($_POST["guardar_registro"]) AND in_array('L', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1L">Lunes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1M" name="semana_1[]" value="M" <?php if(isset($_POST["guardar_registro"]) AND in_array('M', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1M">Martes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1X" name="semana_1[]" value="X" <?php if(isset($_POST["guardar_registro"]) AND in_array('X', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1X">Miércoles</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1J" name="semana_1[]" value="J" <?php if(isset($_POST["guardar_registro"]) AND in_array('J', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1J">Jueves</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1V" name="semana_1[]" value="V" <?php if(isset($_POST["guardar_registro"]) AND in_array('V', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1V">Viernes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1S" name="semana_1[]" value="S" <?php if(isset($_POST["guardar_registro"]) AND in_array('S', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1S">Sábado</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s1D" name="semana_1[]" value="D" <?php if(isset($_POST["guardar_registro"]) AND in_array('D', $semana_1)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s1D">Domingo</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-1">
                            <label for="semana_2" class="font-weight-bold my-0">Semana 2</label>
                            <div class="form-group">
                              <label for="semana_2_inicio" class="my-0 font-weight-bold font-size-11">Fecha inicio</label>
                              <input type="date" class="form-control form-control-sm" name="semana_2_inicio" id="semana_2_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_2_inicio; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                              <label for="semana_2_inicio" class="my-0 font-weight-bold font-size-11">Fecha fin</label>
                              <input type="date" class="form-control form-control-sm" name="semana_2_fin" id="semana_2_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_2_fin; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2L" name="semana_2[]" value="L" <?php if(isset($_POST["guardar_registro"]) AND in_array('L', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2L">Lunes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2M" name="semana_2[]" value="M" <?php if(isset($_POST["guardar_registro"]) AND in_array('M', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2M">Martes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2X" name="semana_2[]" value="X" <?php if(isset($_POST["guardar_registro"]) AND in_array('X', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2X">Miércoles</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2J" name="semana_2[]" value="J" <?php if(isset($_POST["guardar_registro"]) AND in_array('J', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2J">Jueves</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2V" name="semana_2[]" value="V" <?php if(isset($_POST["guardar_registro"]) AND in_array('V', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2V">Viernes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2S" name="semana_2[]" value="S" <?php if(isset($_POST["guardar_registro"]) AND in_array('S', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2S">Sábado</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s2D" name="semana_2[]" value="D" <?php if(isset($_POST["guardar_registro"]) AND in_array('D', $semana_2)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s2D">Domingo</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-1">
                            <label for="semana_3" class="font-weight-bold my-0">Semana 3</label>
                            <div class="form-group">
                              <label for="semana_3_inicio" class="my-0 font-weight-bold font-size-11">Fecha inicio</label>
                              <input type="date" class="form-control form-control-sm" name="semana_3_inicio" id="semana_3_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_3_inicio; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                              <label for="semana_3_inicio" class="my-0 font-weight-bold font-size-11">Fecha fin</label>
                              <input type="date" class="form-control form-control-sm" name="semana_3_fin" id="semana_3_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_3_fin; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3L" name="semana_3[]" value="L" <?php if(isset($_POST["guardar_registro"]) AND in_array('L', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3L">Lunes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3M" name="semana_3[]" value="M" <?php if(isset($_POST["guardar_registro"]) AND in_array('M', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3M">Martes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3X" name="semana_3[]" value="X" <?php if(isset($_POST["guardar_registro"]) AND in_array('X', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3X">Miércoles</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3J" name="semana_3[]" value="J" <?php if(isset($_POST["guardar_registro"]) AND in_array('J', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3J">Jueves</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3V" name="semana_3[]" value="V" <?php if(isset($_POST["guardar_registro"]) AND in_array('V', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3V">Viernes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3S" name="semana_3[]" value="S" <?php if(isset($_POST["guardar_registro"]) AND in_array('S', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3S">Sábado</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s3D" name="semana_3[]" value="D" <?php if(isset($_POST["guardar_registro"]) AND in_array('D', $semana_3)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s3D">Domingo</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-1">
                            <label for="semana_4" class="font-weight-bold my-0">Semana 4</label>
                            <div class="form-group">
                              <label for="semana_4_inicio" class="my-0 font-weight-bold font-size-11">Fecha inicio</label>
                              <input type="date" class="form-control form-control-sm" name="semana_4_inicio" id="semana_4_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_4_inicio; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                              <label for="semana_4_inicio" class="my-0 font-weight-bold font-size-11">Fecha fin</label>
                              <input type="date" class="form-control form-control-sm" name="semana_4_fin" id="semana_4_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_4_fin; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4L" name="semana_4[]" value="L" <?php if(isset($_POST["guardar_registro"]) AND in_array('L', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4L">Lunes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4M" name="semana_4[]" value="M" <?php if(isset($_POST["guardar_registro"]) AND in_array('M', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4M">Martes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4X" name="semana_4[]" value="X" <?php if(isset($_POST["guardar_registro"]) AND in_array('X', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4X">Miércoles</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4J" name="semana_4[]" value="J" <?php if(isset($_POST["guardar_registro"]) AND in_array('J', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4J">Jueves</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4V" name="semana_4[]" value="V" <?php if(isset($_POST["guardar_registro"]) AND in_array('V', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4V">Viernes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4S" name="semana_4[]" value="S" <?php if(isset($_POST["guardar_registro"]) AND in_array('S', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4S">Sábado</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s4D" name="semana_4[]" value="D" <?php if(isset($_POST["guardar_registro"]) AND in_array('D', $semana_4)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s4D">Domingo</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group my-1">
                            <label for="semana_5" class="font-weight-bold my-0">Semana 5</label>
                            <div class="form-group">
                              <label for="semana_5_inicio" class="my-0 font-weight-bold font-size-11">Fecha inicio</label>
                              <input type="date" class="form-control form-control-sm" name="semana_5_inicio" id="semana_5_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_5_inicio; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?>>
                              <label for="semana_5_inicio" class="my-0 font-weight-bold font-size-11">Fecha fin</label>
                              <input type="date" class="form-control form-control-sm" name="semana_5_fin" id="semana_5_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $semana_5_fin; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?>>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5L" name="semana_5[]" value="L" <?php if(isset($_POST["guardar_registro"]) AND in_array('L', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5L">Lunes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5M" name="semana_5[]" value="M" <?php if(isset($_POST["guardar_registro"]) AND in_array('M', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5M">Martes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5X" name="semana_5[]" value="X" <?php if(isset($_POST["guardar_registro"]) AND in_array('X', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5X">Miércoles</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5J" name="semana_5[]" value="J" <?php if(isset($_POST["guardar_registro"]) AND in_array('J', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5J">Jueves</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5V" name="semana_5[]" value="V" <?php if(isset($_POST["guardar_registro"]) AND in_array('V', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5V">Viernes</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5S" name="semana_5[]" value="S" <?php if(isset($_POST["guardar_registro"]) AND in_array('S', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5S">Sábado</label>
                            </div>
                            <div class="form-group custom-control custom-checkbox my-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_s5D" name="semana_5[]" value="D" <?php if(isset($_POST["guardar_registro"]) AND in_array('D', $semana_5)){ echo "checked"; } ?> <?php if($_SESSION['registro_creado_fecha']==1) { echo 'disabled'; } ?>>
                                <label class="custom-control-label" for="customCheck_s5D">Domingo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado_fecha']==1): ?>
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