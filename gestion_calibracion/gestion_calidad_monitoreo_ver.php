<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calibración-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $id_registro=validar_input(base64_decode($_GET['reg']));

    $consulta_string_monitoreo="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos`, TMC.`gcm_encuesta` FROM `tb_gestion_calibracion_monitoreo` AS TMC LEFT JOIN `tb_gestion_calibracion_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

    $consulta_registros_monitoreo = $enlace_db->prepare($consulta_string_monitoreo);
    $consulta_registros_monitoreo->bind_param("s", $id_registro);
    $consulta_registros_monitoreo->execute();
    $resultado_registros_monitoreo = $consulta_registros_monitoreo->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_evaluacion="SELECT `gcmc_id`, `gcmc_monitoreo`, `gcmc_pregunta`, `gcmc_respuesta`, `gcmc_afectaciones`, `gcmc_comentarios`, TIM.`gcmi_matriz`, TIM.`gcmi_item_tipo`, TIM.`gcmi_item_consecutivo`, TIM.`gcmi_item_orden`, TIM.`gcmi_descripcion`, TIM.`gcmi_peso`, TIM.`gcmi_calificable` FROM `tb_gestion_calibracion_monitoreo_calificaciones` LEFT JOIN `tb_gestion_calibracion_matriz_item` AS TIM ON `tb_gestion_calibracion_monitoreo_calificaciones`.`gcmc_pregunta`=TIM.`gcmi_id` WHERE `gcmc_monitoreo`=? AND TIM.`gcmi_matriz`=? ORDER BY TIM.`gcmi_item_consecutivo` ASC";

    $consulta_registros_evaluacion = $enlace_db->prepare($consulta_string_evaluacion);
    $consulta_registros_evaluacion->bind_param("ss", $id_registro, $resultado_registros_monitoreo[0][20]);
    $consulta_registros_evaluacion->execute();
    $resultado_registros_evaluacion = $consulta_registros_evaluacion->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_evaluacion); $i++) { 
        $array_respuestas[$resultado_registros_evaluacion[$i][2]]=$resultado_registros_evaluacion[$i][3];
        $array_comentarios[$resultado_registros_evaluacion[$i][2]]=$resultado_registros_evaluacion[$i][5];
    }

    $consulta_string_matriz="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id` FROM `tb_gestion_calibracion_matriz_item` WHERE `gcmi_matriz`=? ORDER BY `gcmi_item_consecutivo` ASC";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $resultado_registros_monitoreo[0][20]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    // $consulta_string_adjuntos="SELECT `gcms_id`, `gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`, `gcms_registro_fecha` FROM `tb_gestion_calibracion_monitoreo_soportes` WHERE `gcms_monitoreo`=?";

    // $consulta_registros_adjuntos = $enlace_db->prepare($consulta_string_adjuntos);
    // $consulta_registros_adjuntos->bind_param("s", $id_registro);
    // $consulta_registros_adjuntos->execute();
    // $resultado_registros_adjuntos = $consulta_registros_adjuntos->get_result()->fetch_all(MYSQLI_NUM);

    // $consulta_string_adjuntos_his="SELECT `gcmhs_id`, `gcmhs_monitoreo`, `gcmhs_nombre`, `gcmhs_ruta`, `gcmhs_extension`, `gcmhs_registro_usuario`, `gcmhs_registro_fecha` FROM `tb_gestion_calibracion_monitoreo_historial_soportes` WHERE `gcmhs_monitoreo`=?";

    // $consulta_registros_adjuntos_his = $enlace_db->prepare($consulta_string_adjuntos_his);
    // $consulta_registros_adjuntos_his->bind_param("s", $id_registro);
    // $consulta_registros_adjuntos_his->execute();
    // $resultado_registros_adjuntos_his = $consulta_registros_adjuntos_his->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_historial="SELECT `gcmh_id`, `gcmh_monitoreo`, `gcmh_tipo_cambio`, `gcmh_comentarios`, `gcmh_registro_usuario`, `gcmh_registro_fecha`, TUR.`usu_nombres_apellidos` FROM `tb_gestion_calibracion_monitoreo_historial` LEFT JOIN `tb_administrador_usuario` AS TUR ON `tb_gestion_calibracion_monitoreo_historial`.`gcmh_registro_usuario`=TUR.`usu_id` WHERE `gcmh_monitoreo`=? ORDER BY `gcmh_registro_fecha` DESC";

    $consulta_registros_historial = $enlace_db->prepare($consulta_string_historial);
    $consulta_registros_historial->bind_param("s", $id_registro);
    $consulta_registros_historial->execute();
    $resultado_registros_historial = $consulta_registros_historial->get_result()->fetch_all(MYSQLI_NUM);
?>
<div class="row">
    <div class="col-md-3">
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
                        <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Duración (minutos)</th>
                        <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][4]; ?></td>
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
                    <tr>
                        <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                        <td class="align-middle py-0 font-size-11"><?php echo $resultado_registros_monitoreo[0][16]; ?></td>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="col-md-9">
        <div class="fondo-corporativo color-blanco p-1 font-size-11"><span class="fas fa-history"></span> Historial de gestión</div>
        <div class="avances-seguimiento p-1" style="max-height: 450px;">
            <?php if(count($resultado_registros_historial)>0): ?>
                <?php for ($i=0; $i < count($resultado_registros_historial); $i++): ?>
                    <div class="alert alert-warning p-1 font-size-11 mb-1">
                        <span class="fas fa-layer-group"></span> <?php echo $resultado_registros_historial[$i][2]; ?> | <span class="fas fa-calendar-alt"></span> <?php echo $resultado_registros_historial[$i][5]; ?> | <span class="fas fa-user"></span> <?php echo $resultado_registros_historial[$i][6]; ?>
                        <br><span class="fas fa-file-alt"></span> <?php echo nl2br($resultado_registros_historial[$i][3]); ?>
                    </div>
                <?php endfor; ?>
            <?php else: ?>
                <p class="alert alert-warning p-1 font-size-11">
                    <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                </p>
            <?php endif; ?>
        </div>
        <?php if (count($resultado_registros_matriz)>0): ?>
            <div id="table-fixed" class="table-responsive table-fixed">
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Atributos de Evaluación</th>
                            <th style="width: 100px;">Peso</th>
                            <th style="width: 50px;">Si</th>
                            <th style="width: 50px;">No</th>
                            <th style="width: 300px;">Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            for ($i=0; $i < count($resultado_registros_matriz); $i++) { 
                        ?>
                        <?php if($resultado_registros_matriz[$i][9]=="Si"): ?>
                        <tr class="<?php if($resultado_registros_matriz[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros_matriz[$i][2]=='Sub-Grupo'){echo 'matriz-grupo-sub';} elseif($resultado_registros_matriz[$i][2]=='Item'){echo 'matriz-item';}?>">
                            <td class="align-middle"><?php echo $resultado_registros_matriz[$i][3]; ?></td>
                            <td class="align-middle"><?php echo $resultado_registros_matriz[$i][5]; ?></td>
                            <td class="align-middle text-center"><?php echo $resultado_registros_matriz[$i][6]; ?>%</td>
                            <td class="align-middle text-center align-middle">
                                <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="Si"): ?>
                                <div class="form-group m-0 p-0">
                                    <div class="form-group custom-control custom-checkbox m-0">
                                        <input type="radio" class="custom-control-input" id="customCheckreqsi<?php echo $i; ?>" name="respuesta_<?php echo $i; ?>" value="Si" <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="Si"){ echo "checked"; } ?> disabled>
                                        <label class="custom-control-label p-0 m-0" for="customCheckreqsi<?php echo $i; ?>"></label>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="No"): ?>
                                <div class="form-group m-0 p-0">
                                    <div class="form-group custom-control custom-checkbox m-0">
                                        <input type="radio" class="custom-control-input" id="customCheckreqno<?php echo $i; ?>" name="respuesta_<?php echo $i; ?>" value="No" <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="No"){ echo "checked"; } ?> disabled>
                                        <label class="custom-control-label p-0 m-0" for="customCheckreqno<?php echo $i; ?>"></label>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle rechazado">
                                <?php
                                    if($array_respuestas[$resultado_registros_matriz[$i][0]]=="No") {
                                        echo $array_comentarios[$resultado_registros_matriz[$i][0]];
                                    }
                                ?>
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
            <p class="alert alert-warning p-1">
                <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
            </p>
        <?php endif; ?>
    </div>
</div>