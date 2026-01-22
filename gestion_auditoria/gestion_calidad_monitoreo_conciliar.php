<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Conciliar";
    $pagina=validar_input($_GET['pagina']);
    $bandeja=validar_input(base64_decode($_GET['bandeja']));
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $agente_calidad=validar_input($_GET['calidad']);

    if(isset($_POST["guardar_registro"])){
        $estado_conciliacion=$_POST['estado_conciliacion'];
        $observaciones=$_POST['observaciones'];
        $tipo_cambio='Conciliación';
        $id_usuario=$_SESSION['usu_id'];

        if($_SESSION['registro_creado_cambio_estado']!=1){
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_auditoria_monitoreo_historial`(`gcmh_monitoreo`, `gcmh_tipo_cambio`, `gcmh_comentarios`, `gcmh_registro_usuario`) VALUES (?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('ssss', $id_registro, $tipo_cambio, $observaciones, $id_usuario);
            
            if ($sentencia_insert->execute()) {
                $ruta_guardar="storage/".$id_registro;
                if (!file_exists($ruta_guardar)) {
                    mkdir($ruta_guardar, 0777, true);
                }

                if ($_FILES['documento']['name']!="") {
                  $archivo_extension = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
                  $NombreArchivo="soporte_conciliacion_".date('YmdHis').".".$archivo_extension;
                  $ruta_final=$ruta_guardar."/".$NombreArchivo;
                  $nombre_soporte_1='Soporte conciliación';
                  if ($_FILES['documento']["error"] > 0) {
                      $control_documento=0;
                  } else {
                    /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                      if (move_uploaded_file($_FILES['documento']['tmp_name'], $ruta_final)) {
                          
                          // Prepara la sentencia 
                            $consulta_registro_soporte_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_auditoria_monitoreo_soportes`(`gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`) VALUES (?,?,?,?,?);");

                            // Agrega variables a sentencia preparada
                            $consulta_registro_soporte_insert->bind_param('sssss', $id_registro,$NombreArchivo,$ruta_final,$archivo_extension, $_SESSION['usu_id']);

                            if ($consulta_registro_soporte_insert->execute()) {
                                $control_documento=1;
                            } else {
                                $control_documento=0;
                            }
                      } else {
                          $control_documento=0;
                      }
                  }
                } else {
                    $control_documento=1;
                }

                // Prepara la sentencia
                $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo` SET `gcm_estado`=? WHERE `gcm_id`=?");

                // Agrega variables a sentencia preparada
                $consulta_actualizar->bind_param('ss', $estado_conciliacion, $id_registro);
                
                // Ejecuta sentencia preparada
                $consulta_actualizar->execute();

                if (comprobarSentencia($enlace_db->info)) {
                    
                    //insert log eventos
                        $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";
                    
                        $log_modulo=$modulo_plataforma;
                        $log_tipo="editar";
                        $log_accion="Editar registro";
                        $log_detalle=$tipo_cambio." monitoreo auditoría [".$id_registro."]";
                        $log_usuario=$_SESSION["usu_id"];
                        
                        $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                        $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                        $consulta_registros_log->execute();
                    //insert log eventos

                    $consulta_string_validar="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_afectacion_1`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_auditoria_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

                    $consulta_registros_validar = $enlace_db->prepare($consulta_string_validar);
                    $consulta_registros_validar->bind_param("s", $id_registro);
                    $consulta_registros_validar->execute();
                    $resultado_registros_validar = $consulta_registros_validar->get_result()->fetch_all(MYSQLI_NUM);

                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                    $_SESSION['registro_creado_cambio_estado']=1;
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }

            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_monitoreo="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_afectacion_1`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_auditoria_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

    $consulta_registros_monitoreo = $enlace_db->prepare($consulta_string_monitoreo);
    $consulta_registros_monitoreo->bind_param("s", $id_registro);
    $consulta_registros_monitoreo->execute();
    $resultado_registros_monitoreo = $consulta_registros_monitoreo->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode($bandeja);
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
            <div class="col-md-5 pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Consecutivo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][0]; ?></td>
                                <input type="hidden" name="estado_old" value="<?php echo $resultado_registros_monitoreo[0][15]; ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][1]; ?><br>[<?php echo $resultado_registros_monitoreo[0][28]; ?>]</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][5]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Skill Interacción</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][6]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][7]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Segmento</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][8]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id SIM</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][9]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id/ANI</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][10]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][3]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][26]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECUF</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][24]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][24]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECN</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][23]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][23]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ENC</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][22]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][22]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota General</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][21]; ?>%</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][11]; ?></td>
                            </tr>
                            <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][16]; ?></td>
                            </tr>
                            <?php endif; ?>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="col-md-7 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="estado_conciliacion">Estado conciliación a favor de</label>
                            <select class="form-control form-control-sm" name="estado_conciliacion" id="estado_conciliacion" <?php if($_SESSION['registro_creado_cambio_estado']==1) { echo 'disabled'; } ?> required>
                                <option value="">Seleccione</option>
                                <option value="Conciliado Proveedor" <?php if(isset($_POST["guardar_registro"]) AND $estado_conciliacion=="Conciliado Proveedor"){ echo "selected"; } ?>>Conciliado Proveedor</option>
                                <option value="Conciliado Cliente" <?php if(isset($_POST["guardar_registro"]) AND $estado_conciliacion=="Conciliado Cliente"){ echo "selected"; } ?>>Conciliado Cliente</option>
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">
                                Observaciones para conciliación/cierre
                          </label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="10" <?php if($_SESSION['registro_creado_cambio_estado']==1) { echo 'disabled'; } ?> required><?php if(isset($_POST["guardar_registro"])){ echo $observaciones; } ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inputGroupFile01">Adjuntar soportes</label>
                            <div class="custom-file">
                                <input type="file" name="documento" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01" <?php if($_SESSION['registro_creado_cambio_estado']==1) { echo 'disabled'; } ?> accept=".pdf, .PDF">
                                <label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01label">Seleccione un archivo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado_cambio_estado']==1): ?>
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
    <script type="text/javascript">
        $("#inputGroupFile01").change(function(){
            var valor_opcion = document.getElementById("inputGroupFile01").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
            }
        });
    </script>
</body>
</html>