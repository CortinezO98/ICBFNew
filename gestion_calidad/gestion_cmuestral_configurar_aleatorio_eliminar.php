    <?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    $titulo_header = "Calculadora Muestral | Aleatorio - Eliminar";
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_segmento=validar_input(base64_decode($_GET['seg']));
    $fecha_calculadora=validar_input($_GET['date']);

    $consulta_string_fechas="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, TS.`cms_nombre_segmento` FROM `tb_gestion_calidad_cmuestral_mensual` LEFT JOIN `tb_gestion_calidad_cmuestral_segmento` AS TS ON `tb_gestion_calidad_cmuestral_mensual`.`cmm_segmento`=TS.`cms_id` WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros_fechas = $enlace_db->prepare($consulta_string_fechas);
    $consulta_registros_fechas->bind_param("sss", $id_registro, $fecha_calculadora, $id_segmento);
    $consulta_registros_fechas->execute();
    $resultado_registros_fechas = $consulta_registros_fechas->get_result()->fetch_all(MYSQLI_NUM);

    if(isset($_POST["eliminar_registro"])){
        if($_SESSION['registro_creado_random_eliminado']!=1){
            // Prepara la sentencia
            $sentencia_delete = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_cmuestral_muestras` WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete->bind_param('sss', $id_registro, $fecha_calculadora, $id_segmento);
            
            // Evalua resultado de ejecución sentencia preparada
            if ($sentencia_delete->execute()) {
                $_SESSION['registro_creado_random_eliminado']=1;
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente!', 0);</script>";
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('Random eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_malla="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_martes`, `ccmm_monitoreo_miercoles`, `ccmm_monitoreo_jueves`, `ccmm_monitoreo_viernes`, `ccmm_monitoreo_sabado`, `ccmm_monitoreo_domingo`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos`, TC.`usu_nombres_apellidos`, TCAM.`ac_nombre_campania`, `ccmm_monitor`, TMONITOR.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TC ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_coordinador`=TC.`usu_id` LEFT JOIN `tb_administrador_campania` AS TCAM ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario_segmento`=TCAM.`ac_id` LEFT JOIN `tb_administrador_usuario` AS TMONITOR ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_monitor`=TMONITOR.`usu_id` WHERE `ccmm_mes`=? AND `ccmm_segmento`=? ORDER BY TU.`usu_nombres_apellidos` ASC";
    $consulta_registros_malla = $enlace_db->prepare($consulta_string_malla);
    $consulta_registros_malla->bind_param("ss", $fecha_calculadora, $id_segmento);
    $consulta_registros_malla->execute();
    $resultado_registros_malla = $consulta_registros_malla->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_malla); $i++) {
        $turno_seg_usuario[$resultado_registros_malla[$i][2]][]=$resultado_registros_malla[$i][3];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['id']=$resultado_registros_malla[$i][0];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['nombre']=$resultado_registros_malla[$i][24];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['coordinador']=$resultado_registros_malla[$i][25];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['monitor']=$resultado_registros_malla[$i][28];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['segmento']=$resultado_registros_malla[$i][26];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['muestra']=$resultado_registros_malla[$i][13].$resultado_registros_malla[$i][14].$resultado_registros_malla[$i][15].$resultado_registros_malla[$i][16].$resultado_registros_malla[$i][17].$resultado_registros_malla[$i][18].$resultado_registros_malla[$i][19];
        
        if ($array_convenciones[$resultado_registros_malla[$i][6]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['turno']=$array_convenciones[$resultado_registros_malla[$i][6]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['color']=$array_convenciones_color[$resultado_registros_malla[$i][6]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['turno']=$resultado_registros_malla[$i][6];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][7]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['turno']=$array_convenciones[$resultado_registros_malla[$i][7]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['color']=$array_convenciones_color[$resultado_registros_malla[$i][7]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['turno']=$resultado_registros_malla[$i][7];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][8]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['turno']=$array_convenciones[$resultado_registros_malla[$i][8]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['color']=$array_convenciones_color[$resultado_registros_malla[$i][8]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['turno']=$resultado_registros_malla[$i][8];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][9]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['turno']=$array_convenciones[$resultado_registros_malla[$i][9]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['color']=$array_convenciones_color[$resultado_registros_malla[$i][9]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['turno']=$resultado_registros_malla[$i][9];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][10]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['turno']=$array_convenciones[$resultado_registros_malla[$i][10]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['color']=$array_convenciones_color[$resultado_registros_malla[$i][10]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['turno']=$resultado_registros_malla[$i][10];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][11]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['turno']=$array_convenciones[$resultado_registros_malla[$i][11]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['color']=$array_convenciones_color[$resultado_registros_malla[$i][11]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['turno']=$resultado_registros_malla[$i][11];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][12]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['turno']=$array_convenciones[$resultado_registros_malla[$i][12]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['color']=$array_convenciones_color[$resultado_registros_malla[$i][12]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['turno']=$resultado_registros_malla[$i][12];
        }
    }

    $consulta_string_muestra="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_muestras` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_monitor`=TU.`usu_id` WHERE `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros_muestra = $enlace_db->prepare($consulta_string_muestra);
    $consulta_registros_muestra->bind_param("ss", $fecha_calculadora, $id_segmento);
    $consulta_registros_muestra->execute();
    $resultado_registros_muestra = $consulta_registros_muestra->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_muestra); $i++) { 
        $array_muestra[$resultado_registros_muestra[$i][4]][]=$resultado_registros_muestra[$i][6].' | '.$resultado_registros_muestra[$i][7];
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
<body onresize="tabla_fixed();" onload="tabla_fixed();">
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
                <?php if (count($turno_seg_usuario[$id_segmento])>0): ?>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Doc. Identidad</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Segmento</th>
                                    <th>Coordinador</th>
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miércoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                    <th>Sábado</th>
                                    <th>Domingo</th>
                                    <th>Muestra Aleatoria</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($j=0; $j < count($turno_seg_usuario[$id_segmento]); $j++) {
                                        $id_usuario_item=$turno_seg_usuario[$id_segmento][$j];
                                ?>
                                <tr>
                                    <td class="align-middle"><?php echo $id_usuario_item; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['nombre']; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['segmento']; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['coordinador']; ?></td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][0]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][0]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][0]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][1]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][1]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][1]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][2]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][2]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][2]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][3]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][3]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][3]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][4]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][4]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][4]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][5]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][5]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][5]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][6]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][6]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][6]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center py-0">
                                        <?php if(isset($array_muestra[$id_usuario_item])): ?>
                                            <a href="#" onClick="open_modal_detalle('<?php echo base64_encode($turno_mostrar_datos[$id_usuario_item]['id']); ?>');" class="btn btn-secondary btn-sm btn-width" title="Detalle Muestra"><span class="fas fa-eye"></span></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 pt-2">
                <div class="form-group">
                    <?php if($_SESSION['registro_creado_random_eliminado']!=1): ?>
                    <button class="btn btn-warning float-right ml-1" type="submit" name="eliminar_registro">Eliminar Aleatorio</button>
                    <?php endif; ?>
                    <?php if($_SESSION['registro_creado_random_eliminado']==1): ?>
                        <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                    <?php else: ?>
                        <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </form>
        <!-- Modal DETALLE -->
        <div class="modal fade" id="modal_ver_detalle" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detalle muestra aleatoria</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_detalle();">×</button>
                    </div>
                    <div class="modal-body modal-body-detalle p-1">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_detalle();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#modal_ver_detalle").on('hidden.bs.modal', function () {
                close_modal_detalle();
            });
        });
        
        function open_modal_detalle(id_registro) {
            $('.modal-body-detalle').load('gestion_cmuestral_configurar_malla_editar_calcular_ver.php?reg='+id_registro,function(){
                $('#modal_ver_detalle').modal({show:true});
            });
        }

        function close_modal_detalle() {
            $('.modal-body-detalle').html('');
        }
        function tabla_fixed(){
            var tabla_fixed = document.getElementById("tabla_fixed");
            var elemento = document.getElementById("table-fixed");
            var elemento_1 = document.getElementById("elemento_1");
            alto_ventana=window.outerHeight-elemento_1.clientHeight-100;
            alto_tabla=tabla_fixed.clientHeight+200;

            if (alto_tabla>alto_ventana) {
                alto_elemento=alto_ventana-180;
                elemento.style.height=alto_elemento+"px";
            }
        }
    </script>
</body>
</html>