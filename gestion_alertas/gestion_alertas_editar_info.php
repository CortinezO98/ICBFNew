<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Alertas | Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $estado=validar_input($_GET['est']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $estado_final=validar_input($_POST['estado_final']);
        $concepto_tecnico=validar_input($_POST['concepto_tecnico']);
        $decision_caso=validar_input($_POST['decision_caso']);
        $fecha_respuesta_regional=validar_input($_POST['fecha_respuesta_regional']);
        $fecha_cierre=validar_input($_POST['fecha_cierre']);
        $snc_ac=validar_input($_POST['snc_ac']);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_alertas` SET `ga_estado`=?,`ga_concepto_tecnico_pmejora`=?,`ga_decision_caso`=?,`ga_fecha_respuesta_regional`=?,`ga_fecha_cierre`=?, `ga_snc_ac`=? WHERE `ga_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssss', $estado_final, $concepto_tecnico, $decision_caso, $fecha_respuesta_regional, $fecha_cierre, $snc_ac, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`,`ga_fecha_cierre` FROM `tb_gestion_alertas` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id` WHERE `ga_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_encuestas="SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_observaciones`, `ger_registro_usuario`, `ger_registro_fecha`, TU.`usu_nombres_apellidos`, `ger_contactado`, `ger_efectivo` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` WHERE `ger_radicado`=? ORDER BY `ger_registro_fecha` DESC LIMIT 0,1";

    $consulta_registros_encuestas = $enlace_db->prepare($consulta_string_encuestas);
    $consulta_registros_encuestas->bind_param("s", $resultado_registros[0][1]);
    $consulta_registros_encuestas->execute();
    $resultado_registros_encuestas = $consulta_registros_encuestas->get_result()->fetch_all(MYSQLI_NUM);

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

            <div class="col-md-4 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <a href="#" onClick="open_modal_encuesta('<?php echo base64_encode($resultado_registros_encuestas[0][0]); ?>');" class="btn btn-success mb-1 font-size-11" title="Ver Encuesta"><span class="fas fa-file-alt"></span> <?php echo $resultado_registros_encuestas[0][0]; ?></a>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="afecta_lt" class="m-0">Estado</label>
                            <select class="form-control form-control-sm" name="estado_final" id="estado_final" required>
                              <option value="">Seleccione</option>
                              <option value="Escalado Regional" <?php if($resultado_registros[0][3]=="Escalado Regional"){ echo "selected"; } ?>>Escalado Regional</option>
                              <option value="Cerrado" <?php if($resultado_registros[0][3]=="Cerrado"){ echo "selected"; } ?>>Cerrado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="snc_ac" class="m-0">No. SNC/AC</label>
                          <input type="text" class="form-control form-control-sm" name="snc_ac" id="snc_ac" value="<?php echo $resultado_registros[0][19]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="fecha_respuesta_regional" class="m-0">Fecha respuesta regional</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_respuesta_regional" id="fecha_respuesta_regional" value="<?php echo $resultado_registros[0][16]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="fecha_cierre" class="m-0">Fecha cierre</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_cierre" id="fecha_cierre" value="<?php echo $resultado_registros[0][25]; ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="concepto_tecnico">Concepto técnico</label>
                          <textarea class="form-control form-control-sm" name="concepto_tecnico" id="concepto_tecnico"><?php echo $resultado_registros[0][17]; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="decision_caso">Decisión del caso</label>
                          <textarea class="form-control form-control-sm" name="decision_caso" id="decision_caso"><?php echo $resultado_registros[0][18]; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"]) AND !isset($_POST["reset_contrasena"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"]) OR isset($_POST["reset_contrasena"])): ?>
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
    </script>
</body>
</html>