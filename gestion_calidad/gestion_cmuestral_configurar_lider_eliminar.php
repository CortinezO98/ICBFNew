<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Configurar - Líder Calidad - Eliminar";
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_segmento=validar_input(base64_decode($_GET['seg']));
    $fecha_calculadora=validar_input($_GET['date']);
    
    $consulta_string_fechas="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, TS.`cms_nombre_segmento` FROM `tb_gestion_calidad_cmuestral_mensual` LEFT JOIN `tb_gestion_calidad_cmuestral_segmento` AS TS ON `tb_gestion_calidad_cmuestral_mensual`.`cmm_segmento`=TS.`cms_id` WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros_fechas = $enlace_db->prepare($consulta_string_fechas);
    $consulta_registros_fechas->bind_param("sss", $id_registro, $fecha_calculadora, $id_segmento);
    $consulta_registros_fechas->execute();
    $resultado_registros_fechas = $consulta_registros_fechas->get_result()->fetch_all(MYSQLI_NUM);

    if(isset($_POST["eliminar_registro"])){
        if($_SESSION['calculadora_lider_calidad_eliminado']!=1){
            // Prepara la sentencia
            $sentencia_delete = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_cmuestral_malla_lider` WHERE `ccmm_mes`=? AND `ccmm_segmento`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete->bind_param('ss', $fecha_calculadora, $id_segmento);
            
            // Evalua resultado de ejecución sentencia preparada
            if ($sentencia_delete->execute()) {
                $_SESSION['calculadora_lider_calidad_eliminado']=1;
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente!', 0);</script>";
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_lider="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_malla_lider` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla_lider`.`ccmm_usuario`=TU.`usu_id` WHERE `ccmm_mes`=? AND `ccmm_segmento`=? ORDER BY TU.`usu_nombres_apellidos` ASC";
    $consulta_registros_lider = $enlace_db->prepare($consulta_string_lider);
    $consulta_registros_lider->bind_param("ss", $fecha_calculadora, $id_segmento);
    $consulta_registros_lider->execute();
    $resultado_registros_lider = $consulta_registros_lider->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_lider); $i++) {
        $turno_seg_lider[$resultado_registros_lider[$i][2]][]=$resultado_registros_lider[$i][3];
        $turno_mostrar_datos_lider[$resultado_registros_lider[$i][3]]['id']=$resultado_registros_lider[$i][0];
        $turno_mostrar_datos_lider[$resultado_registros_lider[$i][3]]['nombre']=$resultado_registros_lider[$i][13];
        
        if ($array_convenciones[$resultado_registros_lider[$i][4]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][0]['turno']=$array_convenciones[$resultado_registros_lider[$i][4]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][0]['color']=$array_convenciones_color[$resultado_registros_lider[$i][4]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][0]['turno']=$resultado_registros_lider[$i][4];
        }

        if ($array_convenciones[$resultado_registros_lider[$i][5]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][1]['turno']=$array_convenciones[$resultado_registros_lider[$i][5]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][1]['color']=$array_convenciones_color[$resultado_registros_lider[$i][5]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][1]['turno']=$resultado_registros_lider[$i][5];
        }

        if ($array_convenciones[$resultado_registros_lider[$i][6]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][2]['turno']=$array_convenciones[$resultado_registros_lider[$i][6]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][2]['color']=$array_convenciones_color[$resultado_registros_lider[$i][6]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][2]['turno']=$resultado_registros_lider[$i][6];
        }

        if ($array_convenciones[$resultado_registros_lider[$i][7]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][3]['turno']=$array_convenciones[$resultado_registros_lider[$i][7]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][3]['color']=$array_convenciones_color[$resultado_registros_lider[$i][7]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][3]['turno']=$resultado_registros_lider[$i][7];
        }

        if ($array_convenciones[$resultado_registros_lider[$i][8]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][4]['turno']=$array_convenciones[$resultado_registros_lider[$i][8]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][4]['color']=$array_convenciones_color[$resultado_registros_lider[$i][8]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][4]['turno']=$resultado_registros_lider[$i][8];
        }

        if ($array_convenciones[$resultado_registros_lider[$i][9]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][5]['turno']=$array_convenciones[$resultado_registros_lider[$i][9]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][5]['color']=$array_convenciones_color[$resultado_registros_lider[$i][9]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][5]['turno']=$resultado_registros_lider[$i][9];
        }

        if ($array_convenciones[$resultado_registros_lider[$i][10]]!="") {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][6]['turno']=$array_convenciones[$resultado_registros_lider[$i][10]];
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][6]['color']=$array_convenciones_color[$resultado_registros_lider[$i][10]];
        } else {
            $turno_mostrar_lider[$resultado_registros_lider[$i][2]][$resultado_registros_lider[$i][3]][6]['turno']=$resultado_registros_lider[$i][10];
        }
    }

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
        <div class="row" id="elemento_1">
            <div class="col-md-6 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                        <input type="text" name="id_filtro" value='<?php echo $fecha_calculadora; ?>' class="form-control" readonly>
                        <input type="text" name="id_filtro" value='<?php echo $resultado_registros_fechas[0][14]; ?>' class="form-control" readonly>
                    </div>
                </form>
            </div>
        </div>
        <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row" id="tabla_fixed">
            <div class="col-md-12 pt-2">
                <?php if (count($turno_seg_lider[$id_segmento])>0): ?>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle">Documento</th>
                                    <th class="align-middle">Nombres y Apellidos</th>
                                    <th class="align-middle">Lunes</th>
                                    <th class="align-middle">Martes</th>
                                    <th class="align-middle">Miércoles</th>
                                    <th class="align-middle">Jueves</th>
                                    <th class="align-middle">Viernes</th>
                                    <th class="align-middle">Sábado</th>
                                    <th class="align-middle">Domingo</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($j=0; $j < count($turno_seg_lider[$id_segmento]); $j++) {
                                        $id_usuario_item_lider=$turno_seg_lider[$id_segmento][$j];
                                ?>
                                <tr>
                                    <td class="align-middle"><?php echo $id_usuario_item_lider; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos_lider[$id_usuario_item_lider]['nombre']; ?></td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][0]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][0]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][0]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][1]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][1]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][1]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][2]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][2]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][2]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][3]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][3]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][3]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][4]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][4]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][4]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][5]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][5]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][5]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][6]['color']; ?>; <?php echo ($turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][6]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar_lider[$id_segmento][$id_usuario_item_lider][6]['turno']; ?>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-12">
                <?php if($_SESSION['calculadora_lider_calidad_eliminado']==1): ?>
                    <p class="alert alert-danger p-1 font-size-11">¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!</p>
                <?php else: ?>
                    <p class="alert alert-danger p-1 font-size-11">¡El registro será eliminado de forma permanente y no se podrá recuperar, por favor valide antes de continuar!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php if($_SESSION['calculadora_lider_calidad_eliminado']!=1): ?>
                    <button class="btn btn-warning float-right ml-1" type="submit" name="eliminar_registro">Eliminar Líder Calidad</button>
                    <?php endif; ?>
                    <?php if($_SESSION['calculadora_lider_calidad_eliminado']==1): ?>
                        <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                    <?php else: ?>
                        <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                    <?php endif; ?>
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