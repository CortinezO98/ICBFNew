<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Alertas | Seguimiento";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $estado=validar_input($_GET['est']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $observaciones=$_POST['observaciones'];
        $observaciones_avance="Seguimiento: ".$observaciones;
        
        $ruta_actual="storage/";

        if ($_FILES['documento1']['name']!="") {
            $codigo_documento1=generar_codigo(10);
            $archivo_extension1 = strtolower(pathinfo($_FILES['documento1']['name'], PATHINFO_EXTENSION));
            $NombreArchivo1=$codigo_documento1.".".$archivo_extension1;
            $ruta_final1=$ruta_actual.$NombreArchivo1;
            if ($_FILES['documento1']["error"] > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                $control_documento1=0;
            } else {
              /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                if (move_uploaded_file($_FILES['documento1']['tmp_name'], $ruta_final1)) {
                    $control_documento1=1;
                } else {
                    $control_documento1=0;
                }
            }
        } else {
            $control_documento1=1;
        }

        if ($_FILES['documento2']['name']!="") {
            $codigo_documento2=generar_codigo(10);
            $archivo_extension2 = strtolower(pathinfo($_FILES['documento2']['name'], PATHINFO_EXTENSION));
            $NombreArchivo2=$codigo_documento2.".".$archivo_extension2;
            $ruta_final2=$ruta_actual.$NombreArchivo2;
            if ($_FILES['documento2']["error"] > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                $control_documento2=0;
            } else {
              /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                if (move_uploaded_file($_FILES['documento2']['tmp_name'], $ruta_final2)) {
                    $control_documento2=1;
                } else {
                    $control_documento2=0;
                }
            }
        } else {
            $control_documento2=1;
        }

        if ($_FILES['documento3']['name']!="") {
            $codigo_documento3=generar_codigo(10);
            $archivo_extension3 = strtolower(pathinfo($_FILES['documento3']['name'], PATHINFO_EXTENSION));
            $NombreArchivo3=$codigo_documento3.".".$archivo_extension3;
            $ruta_final3=$ruta_actual.$NombreArchivo3;
            if ($_FILES['documento3']["error"] > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                $control_documento3=0;
            } else {
              /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                if (move_uploaded_file($_FILES['documento3']['tmp_name'], $ruta_final3)) {
                    $control_documento3=1;
                } else {
                    $control_documento3=0;
                }
            }
        } else {
            $control_documento3=1;
        }

        if ($control_documento1==1 AND $control_documento2==1 AND $control_documento3==1) {
            // Prepara la sentencia
            $sentencia_insert_avance = $enlace_db->prepare("INSERT INTO `tb_gestion_alertas_avances`(`gaa_consecutivo`, `gaa_avance`, `gaa_sop1_nombre`, `gaa_sop1_ruta`, `gaa_sop1_extension`, `gaa_sop2_nombre`, `gaa_sop2_ruta`, `gaa_sop2_extension`, `gaa_sop3_nombre`, `gaa_sop3_ruta`, `gaa_sop3_extension`, `gaa_registro_usuario`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert_avance->bind_param('ssssssssssss', $id_registro, $observaciones_avance, $_FILES['documento1']['name'], $ruta_final1, $archivo_extension1, $_FILES['documento2']['name'], $ruta_final2, $archivo_extension2, $_FILES['documento3']['name'], $ruta_final3, $archivo_extension3, $_SESSION['usu_id']);
            
            if ($sentencia_insert_avance->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                header('Location:gestion_alertas_seguimiento.php?pagina='.$pagina.'&id='.$filtro_permanente.'&est='.$estado.'&reg='.base64_encode($id_registro));
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos` FROM `tb_gestion_alertas` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id` WHERE `ga_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_encuestas="SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_observaciones`, `ger_registro_usuario`, `ger_registro_fecha`, TU.`usu_nombres_apellidos`, `ger_contactado`, `ger_efectivo` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` WHERE `ger_radicado`=? ORDER BY `ger_registro_fecha` DESC LIMIT 0,1";

    $consulta_registros_encuestas = $enlace_db->prepare($consulta_string_encuestas);
    $consulta_registros_encuestas->bind_param("s", $resultado_registros[0][1]);
    $consulta_registros_encuestas->execute();
    $resultado_registros_encuestas = $consulta_registros_encuestas->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_avances="SELECT `gaa_id`, `gaa_consecutivo`, `gaa_avance`, `gaa_registro_usuario`, TU.`usu_nombres_apellidos`, `gaa_registro_fecha`, `gaa_sop1_nombre`, `gaa_sop1_ruta`, `gaa_sop1_extension`, `gaa_sop2_nombre`, `gaa_sop2_ruta`, `gaa_sop2_extension`, `gaa_sop3_nombre`, `gaa_sop3_ruta`, `gaa_sop3_extension` FROM `tb_gestion_alertas_avances` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas_avances`.`gaa_registro_usuario`=TU.`usu_id` WHERE `gaa_consecutivo`=? ORDER BY `gaa_registro_fecha` DESC";

    $consulta_registros_avances = $enlace_db->prepare($consulta_string_avances);
    $consulta_registros_avances->bind_param("s", $id_registro);
    $consulta_registros_avances->execute();
    $resultado_registros_avances = $consulta_registros_avances->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_alertas.php?pagina=".$pagina."&id=".$filtro_permanente."&est=".$estado;
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
            <div class="col-md-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle text-left">Consecutivo</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][0]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Radicado SIM</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][1]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left" style="min-width: 100px;">Fecha Alerta</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][4]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Fecha Atención al Ciudadano</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][5]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Regional</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][22]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Centro Zonal</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][23]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Doc. Peticionario</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][8]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Nombre Peticionario</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][9]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Teléfono</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][10]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Agente</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][24]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Radicado SIM Encuesta</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][2]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Categoría</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][11]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Contexto</th>
                                <td class="align-middle"><?php echo $resultado_registros[0][12]; ?></td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="col-md-4 pt-2">
                <div><span class="fas fa-history"></span> Historial de gestión</div>
                <div class="avances-seguimiento" style="max-height: 450px;">
                    
                <?php for ($i=0; $i < count($resultado_registros_avances); $i++): ?>
                    <div class="alert alert-warning p-1 font-size-11 mb-1">
                        <span class="fas fa-calendar-alt"></span> <?php echo $resultado_registros_avances[$i][5]; ?> 
                         | <span class="fas fa-user"></span> <?php echo $resultado_registros_avances[$i][4]; ?>
                        <?php if($resultado_registros_avances[$i][6]!=""): ?>
                        <br><a href="#" onClick="open_modal_soportes('<?php echo base64_encode($resultado_registros_avances[$i][0]); ?>', 's1');" class="" title="Ver Documento"><?php echo validar_extension_icono($resultado_registros_avances[$i][8])." ".substr($resultado_registros_avances[$i][6], 0,40)."..."; ?></a>
                        <?php endif; ?>
                        <?php if($resultado_registros_avances[$i][9]!=""): ?>
                        <br><a href="#" onClick="open_modal_soportes('<?php echo base64_encode($resultado_registros_avances[$i][0]); ?>', 's2');" class="" title="Ver Documento"><?php echo validar_extension_icono($resultado_registros_avances[$i][11])." ".substr($resultado_registros_avances[$i][9], 0,40)."..."; ?></a>
                        <?php endif; ?>
                        <?php if($resultado_registros_avances[$i][12]!=""): ?>
                        <br><a href="#" onClick="open_modal_soportes('<?php echo base64_encode($resultado_registros_avances[$i][0]); ?>', 's3');" class="" title="Ver Documento"><?php echo validar_extension_icono($resultado_registros_avances[$i][14])." ".substr($resultado_registros_avances[$i][12], 0,40)."..."; ?></a>
                        <?php endif; ?>
                        <br><span class="fas fa-file-alt"></span> <?php echo nl2br($resultado_registros_avances[$i][2]); ?>
                    </div>
                <?php endfor; ?>
                </div>
            </div>
            <div class="col-md-4 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <p class="alert alert-success p-1 mb-1 d-none" id="div_notificaciones"></p>
                        <a href="#" onClick="open_modal_encuesta('<?php echo base64_encode($resultado_registros_encuestas[0][0]); ?>');" class="btn btn-success mb-1 font-size-11" title="Ver Encuesta"><span class="fas fa-file-alt"></span> <?php echo $resultado_registros_encuestas[0][0]; ?></a>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">Observaciones/seguimiento</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="10" required autofocus></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inputGroupFile01_label" class="m-0">Adjuntar soportes</label>
                            <div class="custom-file">
                                <input type="file" name="documento1" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01">
                                <label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01label">Seleccione un archivo</label>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="documento2" class="custom-file-input" id="inputGroupFile02" aria-describedby="inputGroupFileAddon02">
                                <label class="custom-file-label" for="inputGroupFile02" id="inputGroupFile02label">Seleccione un archivo</label>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="documento3" class="custom-file-input" id="inputGroupFile03" aria-describedby="inputGroupFileAddon03">
                                <label class="custom-file-label" for="inputGroupFile03" id="inputGroupFile03label">Seleccione un archivo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"])): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        </form>
        <!-- Modal ENCUESTA -->
        <div class="modal fade" id="modal_ver_encuesta" role="dialog">
            <div class="modal-dialog modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detalle encuesta</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_encuesta();">×</button>
                    </div>
                    <div class="modal-body modal-body-solicitud">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_encuesta();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal SOPORTES -->
        <div class="modal fade" id="modal_ver_soportes" role="dialog">
            <div class="modal-dialog modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Soporte</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_soportes();">×</button>
                    </div>
                    <div class="modal-body modal-body-solicitud p-1">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_soportes();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
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
            $("#modal_ver_encuesta").on('hidden.bs.modal', function () {
                close_modal_encuesta();
            });
        });
        
        function open_modal_encuesta(id_registro) {
            $('.modal-body-solicitud').load('gestion_alertas_encuesta_ver.php?reg='+id_registro,function(){
                $('#modal_ver_encuesta').modal({show:true});
            });
        }

        function close_modal_encuesta() {
            $('.modal-body-solicitud').html('');
        }

        $(document).ready(function(){
            $("#modal_ver_soportes").on('hidden.bs.modal', function () {
                close_modal_soportes();
            });
        });
        
        function open_modal_soportes(id_registro, id_soporte) {
            $('.modal-body-solicitud').load('gestion_alertas_seguimiento_soportes_ver.php?reg='+id_registro+'&sop='+id_soporte,function(){
                $('#modal_ver_soportes').modal({show:true});
            });
        }

        function close_modal_soportes() {
            $('.modal-body-solicitud').html('');
        }

        $("#inputGroupFile01").change(function(){
            var valor_opcion = document.getElementById("inputGroupFile01").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 30)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
            }
        });

        $("#inputGroupFile02").change(function(){
            var valor_opcion = document.getElementById("inputGroupFile02").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile02label').innerHTML=valor_opcion.substring(0, 30)+"...";
                $("#inputGroupFile02label").addClass("color-verde");
            }
        });

        $("#inputGroupFile03").change(function(){
            var valor_opcion = document.getElementById("inputGroupFile03").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile03label').innerHTML=valor_opcion.substring(0, 30)+"...";
                $("#inputGroupFile03label").addClass("color-verde");
            }
        });


    </script>
</body>
</html>