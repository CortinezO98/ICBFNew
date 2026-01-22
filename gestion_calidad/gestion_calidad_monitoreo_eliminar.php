<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Eliminar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $bandeja=validar_input(base64_decode($_GET['bandeja']));
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["eliminar_registro"])){
        if($_SESSION['monitoreo_registro_eliminado']!=1){
            // Prepara la sentencia
            $sentencia_delete_historial = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_monitoreo_historial` WHERE `gcmh_monitoreo`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete_historial->bind_param('s', $id_registro);

            $consulta_string_soportes="SELECT `gcms_id`, `gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`, `gcms_registro_fecha` FROM `tb_gestion_calidad_monitoreo_soportes` WHERE `gcms_monitoreo`=?";

            $consulta_registros_soportes = $enlace_db->prepare($consulta_string_soportes);
            $consulta_registros_soportes->bind_param("s", $id_registro);
            $consulta_registros_soportes->execute();
            $resultado_registros_soportes = $consulta_registros_soportes->get_result()->fetch_all(MYSQLI_NUM);

            if (count($resultado_registros_soportes)>0) {
                for ($i=0; $i < count($resultado_registros_soportes); $i++) { 
                    unlink($resultado_registros_soportes[$i][3]);
                }
            }

            $consulta_string_soportes_his="SELECT `gcmhs_id`, `gcmhs_monitoreo`, `gcmhs_nombre`, `gcmhs_ruta`, `gcmhs_extension`, `gcmhs_registro_usuario`, `gcmhs_registro_fecha` FROM `tb_gestion_calidad_monitoreo_historial_soportes` WHERE `gcmhs_monitoreo`=?";

            $consulta_registros_soportes_his = $enlace_db->prepare($consulta_string_soportes_his);
            $consulta_registros_soportes_his->bind_param("s", $id_registro);
            $consulta_registros_soportes_his->execute();
            $resultado_registros_soportes_his = $consulta_registros_soportes_his->get_result()->fetch_all(MYSQLI_NUM);

            if (count($resultado_registros_soportes_his)>0) {
                for ($i=0; $i < count($resultado_registros_soportes_his); $i++) { 
                    unlink($resultado_registros_soportes_his[$i][3]);
                }
            }

            rmdir('storage/'.$id_registro);

            // Prepara la sentencia
            $sentencia_delete_soportes = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_monitoreo_soportes` WHERE `gcms_monitoreo`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete_soportes->bind_param('s', $id_registro);

            // Prepara la sentencia
            $sentencia_delete_soportes_his = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_monitoreo_historial_soportes` WHERE `gcmhs_monitoreo`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete_soportes_his->bind_param('s', $id_registro);
            
            // Evalua resultado de ejecución sentencia preparada
            if ($sentencia_delete_historial->execute() AND $sentencia_delete_soportes->execute() AND $sentencia_delete_soportes_his->execute()) {
                // Prepara la sentencia
                $sentencia_delete_calificaciones = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_monitoreo_calificaciones` WHERE `gcmc_monitoreo`=?");
                // Agrega variables a sentencia preparada
                $sentencia_delete_calificaciones->bind_param('s', $id_registro);
                
                // Evalua resultado de ejecución sentencia preparada
                if ($sentencia_delete_calificaciones->execute()) {
                    // Prepara la sentencia
                    $sentencia_delete_monitoreo = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_monitoreo` WHERE `gcm_id`=?");
                    // Agrega variables a sentencia preparada
                    $sentencia_delete_monitoreo->bind_param('s', $id_registro);
                    
                    // Evalua resultado de ejecución sentencia preparada
                    if ($sentencia_delete_monitoreo->execute()) {
                        $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";
                        
                        $log_modulo=$modulo_plataforma;
                        $log_tipo="eliminar";
                        $log_accion="Eliminar registro";
                        $log_detalle="Monitoreo [".$id_registro."]";
                        $log_usuario=$_SESSION["usu_id"];
                        
                        $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                        $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                        $consulta_registros_log->execute();

                        $_SESSION['monitoreo_registro_eliminado']=1;
                        $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente!', 0);</script>";
                    } else {
                        $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                    }
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_monitoreo="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

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
        <form name="eliminar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <div class="col-md-6 pt-2 background-blanco">
                <?php if($_SESSION['monitoreo_registro_eliminado']!=1): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Consecutivo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][0]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][1]; ?><br>[<?php echo $resultado_registros_monitoreo[0][27]; ?>]</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Analista</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][2]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Responsable</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][28]; ?></td>
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
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Indicador</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][25]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][11]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                                <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][16]; ?></td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <?php endif; ?>
                <?php if($_SESSION['monitoreo_registro_eliminado']==1): ?>
                    <p class="alert alert-danger p-1 font-size-11">¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!</p>
                <?php else: ?>
                    <p class="alert alert-danger p-1 font-size-11">¡El registro será eliminado de forma permanente y no se podrá recuperar, por favor valide antes de continuar!</p>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['monitoreo_registro_eliminado']!=1): ?>
                            <button class="btn btn-warning float-right ml-1" type="submit" name="eliminar_registro">Si, eliminar</button>
                            <?php endif; ?>
                            <?php if($_SESSION['monitoreo_registro_eliminado']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
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