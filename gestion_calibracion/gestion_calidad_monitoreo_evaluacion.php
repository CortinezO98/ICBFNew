<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calibración-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Evaluación";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    if(isset($_POST["guardar_informacion"])){
        $_SESSION["mon_informacion"]["matriz"]=validar_input($_POST['id_matriz']);
        $_SESSION["mon_informacion"]["analista"]=validar_input($_POST['analista']);
        $_SESSION["mon_informacion"]["fecha_gestion"]=validar_input($_POST['fecha_gestion']);
        $_SESSION["mon_informacion"]["duracion"]=validar_input($_POST['duracion']);
        $_SESSION["mon_informacion"]["fecha_monitoreo"]=date('Y-m-d');
        $_SESSION["mon_informacion"]["tipo_monitoreo"]=validar_input($_POST['tipo_monitoreo']);
        $_SESSION["mon_informacion"]["skill_interaccion"]=validar_input($_POST['skill_interaccion']);
        $_SESSION["mon_informacion"]["tipo_gestion"]=validar_input($_POST['tipo_gestion']);
        $_SESSION["mon_informacion"]["segmento"]=validar_input($_POST['segmento']);
        $_SESSION["mon_informacion"]["id_sim"]=validar_input($_POST['id_sim']);
        $_SESSION["mon_informacion"]["id_ani"]=validar_input($_POST['id_ani']);
        $_SESSION["mon_informacion"]["indicador"]=validar_input($_POST['indicador']);
        $_SESSION["mon_informacion"]["encuesta"]=validar_input($_POST['encuesta']);
    }

    $consulta_string="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id`, `gcmi_nota_maxima`, `gcmi_nota_sla` FROM `tb_gestion_calibracion_matriz_item` WHERE `gcmi_matriz`=? ORDER BY `gcmi_item_consecutivo` ASC";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $_SESSION["mon_informacion"]["matriz"]);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_matriz="SELECT `gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`, `gcm_registro_fecha` FROM `tb_gestion_calibracion_matriz` WHERE `gcm_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $_SESSION["mon_informacion"]["matriz"]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_supervisor` FROM `tb_administrador_usuario` WHERE `usu_id`=?";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    $consulta_registros_analistas->bind_param("s", $_SESSION["mon_informacion"]["analista"]);
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    $_SESSION["mon_informacion"]["responsable"]=$resultado_registros_analistas[0][2];

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
<body onresize="tabla_fixed();" onload="tabla_fixed();">
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row" id="elemento_1">
            <div class="col-md-3 py-2">
                
            </div>
            <div class="col-md-9 py-2">
                <a href="gestion_calidad_monitoreo_informacion.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-arrow-left"></span></div><div class="pl-2 menu_res float-left">Regresar</div></a>
            </div>
        </div>
        <form name="guardar_monitoreo" action="gestion_calidad_monitoreo_guardar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>" method="POST" enctype="multipart/form-data">
        <div class="row" id="tabla_fixed">
            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11" style="width: 150px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_matriz[0][1]; ?><br>[<?php echo $resultado_registros_matriz[0][3]; ?>]</td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Analista</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_analistas[0][1]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Fecha gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["fecha_gestion"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Fecha monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["fecha_monitoreo"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Tipo monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["tipo_monitoreo"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Skill interacción</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["skill_interaccion"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Tipo gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["tipo_gestion"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Segmento</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["segmento"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Id SIM</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["id_sim"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Id/ANI</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["id_ani"]; ?></td>
                            </tr>
                            <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Formador"): ?>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Indicador</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["indicador"]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left py-0 font-size-11">Encuesta</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $_SESSION["mon_informacion"]["encuesta"]; ?></td>
                            </tr>
                            <?php endif; ?>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="col-md-8">
                <?php if (count($resultado_registros)>0): ?>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Atributos de Evaluación</th>
                                    <th style="width: 100px;">Peso / SLA</th>
                                    <th style="width: 50px;">Si</th>
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 300px;">Comentarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <?php if($resultado_registros[$i][9]=="Si"): ?>
                                <tr class="<?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?>">
                                    <td class="align-middle">
                                        <?php if($resultado_registros[$i][2]=='Grupo' AND $resultado_registros[$i][10]=='ECU'): ?>
                                            <input type="hidden" name="peso_sla_ecuf" value="<?php echo $resultado_registros[$i][16]; ?>">
                                        <?php endif; ?>
                                        <?php if($resultado_registros[$i][2]=='Grupo' AND $resultado_registros[$i][10]=='ENC'): ?>
                                            <input type="hidden" name="peso_sla_enc" value="<?php echo $resultado_registros[$i][16]; ?>">
                                        <?php endif; ?>
                                        <?php if($resultado_registros[$i][2]=='Grupo' AND $resultado_registros[$i][10]=='ECN'): ?>
                                            <input type="hidden" name="peso_sla_ecn" value="<?php echo $resultado_registros[$i][16]; ?>">
                                        <?php endif; ?>
                                        <?php if($resultado_registros[$i][7]=="Si"): ?>
                                            <input type="hidden" name="id_campos[]" value="<?php echo $resultado_registros[$i][0]; ?>">
                                            <input type="hidden" name="grupo_peso[]" value="<?php echo $resultado_registros[$i][8]; ?>">
                                            <input type="hidden" name="peso_nota[]" value="<?php echo $resultado_registros[$i][6]; ?>">
                                            <input type="hidden" name="tipo_error[]" value="<?php echo $resultado_registros[$i][10]; ?>">
                                        <?php endif; ?>
                                        <?php echo $resultado_registros[$i][3]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][5]; ?></td>
                                    <td class="align-middle text-center"><?php echo $resultado_registros[$i][6]; ?>% <?php if($resultado_registros[$i][2]=='Grupo') { echo '/ SLA-'.$resultado_registros[$i][16].'%'; } ?></td>
                                    <td class="align-middle text-center align-middle">
                                        <?php if($resultado_registros[$i][7]=="Si"): ?>
                                        <div class="form-group m-0 p-0">
                                            <div class="form-group custom-control custom-checkbox m-0">
                                                <input type="radio" class="custom-control-input" id="customCheckreqsi<?php echo $resultado_registros[$i][0]; ?>" name="respuesta_<?php echo $resultado_registros[$i][0]; ?>" value="Si" <?php if(isset($_POST["guardar_registro"]) AND $respuesta[$i]==("Si")){ echo "checked"; } ?> checked <?php if($_SESSION['incidencia_guardada']==1) { echo 'disabled'; } ?> onclick="validar_comentario('Si', '<?php echo $resultado_registros[$i][0]; ?>');" required>
                                                <label class="custom-control-label p-0 m-0" for="customCheckreqsi<?php echo $resultado_registros[$i][0]; ?>"></label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][7]=="Si"): ?>
                                        <div class="form-group m-0 p-0">
                                            <div class="form-group custom-control custom-checkbox m-0">
                                                <input type="radio" class="custom-control-input" id="customCheckreqno<?php echo $resultado_registros[$i][0]; ?>" name="respuesta_<?php echo $resultado_registros[$i][0]; ?>" value="No" <?php if(isset($_POST["guardar_registro"]) AND $respuesta[$i]==("No")){ echo "checked"; } ?> <?php if($_SESSION['incidencia_guardada']==1) { echo 'disabled'; } ?> onclick="validar_comentario('No', '<?php echo $resultado_registros[$i][0]; ?>');" required>
                                                <label class="custom-control-label p-0 m-0" for="customCheckreqno<?php echo $resultado_registros[$i][0]; ?>"></label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][7]=="Si"): ?>
                                        <input type="text" class="form-control form-control-sm d-none" name="comentario_<?php echo $resultado_registros[$i][0]; ?>" id="comentario_<?php echo $resultado_registros[$i][0]; ?>" maxlength="2000" required disabled value="">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning">
                        <span class="fas fa-exclamation-triangle p-1"></span> No se encontraron registros
                    </p>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones" class="m-0">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones"><?php if(isset($_SESSION["mon_informacion"]["observaciones"])){ echo $_SESSION["mon_informacion"]["observaciones"]; } ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php if($_SESSION['registro_creado']==1): ?>
                        <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark">Finalizar</a>
                    <?php else: ?>
                        <button class="btn btn-success float-right ml-1" type="submit" name="guardar_monitoreo">Guardar</button>
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
    <script type="text/javascript">
        $("#inputGroupFile01").change(function(){
            var soportes='<b>Documentos seleccionados:</b><br>';
            for (var i = 0; i < document.getElementById("inputGroupFile01").files.length; i++) {
                soportes += '<p class="alert alert-success px-1 py-0 mb-0">'+document.getElementById("inputGroupFile01").files[i].name+'</p>';
            }

            var valor_opcion = document.getElementById("inputGroupFile01").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
                document.getElementById('soportes_seleccionados_inputGroupFile01').innerHTML=soportes;
            } else {
                document.getElementById('soportes_seleccionados_inputGroupFile01').innerHTML="";
            }
        });

        $("#inputGroupFile02").change(function(){
            var soportes='<b>Documentos seleccionados:</b><br>';
            for (var i = 0; i < document.getElementById("inputGroupFile02").files.length; i++) {
                soportes += '<p class="alert alert-success px-1 py-0 mb-0">'+document.getElementById("inputGroupFile02").files[i].name+'</p>';
            }

            var valor_opcion = document.getElementById("inputGroupFile02").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile02label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile02label").addClass("color-verde");
                document.getElementById('soportes_seleccionados_inputGroupFile02').innerHTML=soportes;
            } else {
                document.getElementById('soportes_seleccionados_inputGroupFile02').innerHTML="";
            }
        });

        function reset_adjunto(id_file) {
            document.getElementById(id_file).value = "";
            document.getElementById(id_file+'label').innerHTML='Seleccione un archivo';
            document.getElementById('soportes_seleccionados_'+id_file).innerHTML="";
            $('#'+id_file+'label').removeClass("color-verde");
        }

        function validar_comentario(tipo, id_elemento){
            if (tipo=="Si") {
                $("#comentario_"+id_elemento).removeClass('d-block').addClass('d-none');
                document.getElementById("comentario_"+id_elemento).disabled = true;
            } else {
                $("#comentario_"+id_elemento).removeClass('d-none').addClass('d-block');
                document.getElementById("comentario_"+id_elemento).disabled = false;
            }
            
        }

        function tabla_fixed(){
            var tabla_fixed = document.getElementById("tabla_fixed");
            var elemento = document.getElementById("table-fixed");
            var elemento_1 = document.getElementById("elemento_1");
            alto_ventana=window.outerHeight-elemento_1.clientHeight-100;
            alto_tabla=tabla_fixed.clientHeight+200;

            if (alto_tabla>alto_ventana) {
                alto_elemento=alto_ventana-200;
                elemento.style.height=alto_elemento+"px";
            }
        }
    </script>
</body>
</html>