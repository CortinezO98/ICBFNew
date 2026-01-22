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

        if($_SESSION['registro_creado_fecha']!=1){
            // Prepara la sentencia
            $sentencia_insert_segmento = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_mensual`(`cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_monitoreos_semanal`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agentes_semana`) VALUES (?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert_segmento->bind_param('ssssssssss', $id_registro, $anio_mes, $cmm_segmento, $cmm_total_mes, $cmm_muestra_calculada, $cmm_monitoreos_semanal, $cmm_muestra_auditoria, $cmm_numero_agentes, $cmm_muestras_agente_mes, $cmm_muestras_agentes_semana);
            $control_insert_segmento=0;
            for ($i=0; $i < count($segmento_id); $i++) { 
                $cmm_segmento=$segmento_id[$i];
                $cmm_total_mes=$total_mes[$i];
                $cmm_muestra_calculada=round($total_muestra_calculada*($segmento_peso[$i]/100));
                $cmm_monitoreos_semanal=round($cmm_muestra_calculada/4);
                $cmm_muestra_auditoria=$cmm_muestra_calculada*0.1;
                $cmm_numero_agentes=0;
                $cmm_muestras_agente_mes=0;
                $cmm_muestras_agentes_semana=0;

                if ($sentencia_insert_segmento->execute()) {
                    $control_insert_segmento++;
                }
            }

            if ($control_insert_segmento==count($segmento_id)) {
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
            <div class="col-md-6 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                          <label for="anio_mes">Año-Mes</label>
                          <input type="month" class="form-control form-control-sm" name="anio_mes" id="anio_mes" value="<?php if(isset($_POST["guardar_registro"])){ echo $anio_mes; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <input type="hidden" class="form-control form-control-sm" name="intervalo_confianza" value="<?php echo $resultado_registros[0][2]; ?>" readonly required>
                        <input type="hidden" class="form-control form-control-sm" name="valor_z" value="<?php echo $resultado_registros[0][3]; ?>" readonly required>
                        <input type="hidden" class="form-control form-control-sm" name="varianza_estimada" value="<?php echo $resultado_registros[0][4]; ?>" readonly required>
                        <input type="hidden" class="form-control form-control-sm" name="error_muestral" value="<?php echo $resultado_registros[0][5]; ?>" readonly required>
                        
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
                              <label for="total_mes">Total mes</label>
                              <input type="number" class="form-control form-control-sm" name="total_mes[]" id="total_mes" min="0" step="1" value="<?php if(isset($_POST["guardar_registro"])){ echo $total_mes[$i]; } ?>" <?php if($_SESSION['registro_creado_fecha']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                    <?php endfor; ?>
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