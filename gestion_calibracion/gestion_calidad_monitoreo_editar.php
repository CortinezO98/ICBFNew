<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calibración-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Información General - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $bandeja=validar_input(base64_decode($_GET['bandeja']));
    $id_registro=validar_input(base64_decode($_GET['reg']));

    // Inicializa variable tipo array
    $data_consulta=array();

    if(isset($_POST["guardar_registro"])){
        $fecha_gestion=validar_input($_POST['fecha_gestion']);
        $fecha_monitoreo=validar_input($_POST['fecha_monitoreo']);
        $duracion=validar_input($_POST['duracion']);
        $tipo_monitoreo=validar_input($_POST['tipo_monitoreo']);
        $skill_interaccion=validar_input($_POST['skill_interaccion']);
        $tipo_gestion=validar_input($_POST['tipo_gestion']);
        $segmento=validar_input($_POST['segmento']);
        $id_sim=validar_input($_POST['id_sim']);
        $id_ani=validar_input($_POST['id_ani']);
        $indicador=validar_input($_POST['indicador']);
        $analista=validar_input($_POST['analista']);
        $analista_old=validar_input($_POST['analista_old']);
        $gcm_encuesta=validar_input($_POST['gcm_encuesta']);

        $consulta_string_supervisor_nuevo="SELECT `usu_supervisor` FROM `tb_administrador_usuario` WHERE `usu_id`=?";

        $consulta_registros_supervisor_nuevo = $enlace_db->prepare($consulta_string_supervisor_nuevo);
        $consulta_registros_supervisor_nuevo->bind_param("s", $analista);
        $consulta_registros_supervisor_nuevo->execute();
        $resultado_registros_supervisor_nuevo = $consulta_registros_supervisor_nuevo->get_result()->fetch_all(MYSQLI_NUM);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calibracion_monitoreo` SET `gcm_fecha_hora_gestion`=?, `gcm_fecha_hora_cierre`=?,`gcm_tipo_monitoreo`=?,`gcm_skill_interaccion`=?,`gcm_tipo_gestion`=?,`gcm_segmento`=?,`gcm_id_sim`=?,`gcm_id_ani`=?, `gcm_analista`=?, `gcm_responsable`=?, `gcm_encuesta`=? WHERE  `gcm_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('ssssssssssss', $fecha_gestion, $duracion, $tipo_monitoreo, $skill_interaccion, $tipo_gestion, $segmento, $id_sim, $id_ani, $analista, $resultado_registros_supervisor_nuevo[0][0], $gcm_encuesta, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();

        if ($perfil_modulo=="Administrador") {
            // Prepara la sentencia
            $consulta_actualizar_indicador = $enlace_db->prepare("UPDATE `tb_gestion_calibracion_monitoreo` SET `gcm_aplica_indicador`=?, `gcm_fecha_monitoreo`=? WHERE  `gcm_id`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_indicador->bind_param('sss', $indicador, $fecha_monitoreo, $id_registro);
            
            // Ejecuta sentencia preparada
            $consulta_actualizar_indicador->execute();
        }   
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";

            $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_analista`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TM.`gcm_observaciones` FROM `tb_gestion_calibracion_monitoreo` AS TMC LEFT JOIN `tb_gestion_calibracion_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` WHERE TMC.`gcm_id`=?";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            $consulta_registros->bind_param("s", $id_registro);
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_analista`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TM.`gcm_observaciones`, TMC.`gcm_encuesta` FROM `tb_gestion_calibracion_monitoreo` AS TMC LEFT JOIN `tb_gestion_calibracion_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` WHERE TMC.`gcm_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    if ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Formador" OR $perfil_modulo=="Cliente") {
        $filtro_perfil="";
    } elseif($perfil_modulo=="Supervisor"){
        $filtro_perfil=" AND (`usu_supervisor`=?)";
        array_push($data_consulta, $_SESSION["usu_id"]);
        
    }

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE (`usu_cargo_rol` LIKE '%Agente%' OR `usu_cargo_rol` LIKE '%Supervisor%') ".$filtro_perfil." ORDER BY `usu_nombres_apellidos`";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    if (count($data_consulta)>0) {
        // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
        $consulta_registros_analistas->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        
    }
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode($bandeja);

?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
    <link rel="stylesheet" href="../js/bootstrap-select/dist/css/bootstrap-select.min.css">
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
            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="matriz" class="m-0">Matriz</label>
                          <input type="text" class="form-control form-control-sm" name="matriz" id="matriz" value="<?php echo $resultado_registros[0][1]; ?> [<?php echo $resultado_registros[0][28]; ?>]" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="analista" class="m-0">Analista</label>
                            <select class="selectpicker form-control form-control-sm font-size-11 padding-2" data-live-search="true" name="analista" id="analista" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_analistas); $i++): ?> 
                                <option value="<?php echo $resultado_registros_analistas[$i][0]; ?>" class="font-size-11" data-tokens="<?php echo $resultado_registros_analistas[$i][0].' '.$resultado_registros_analistas[$i][1]; ?>" <?php if($resultado_registros[0][2]==$resultado_registros_analistas[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_analistas[$i][1]; ?></option>
                              <?php endfor; ?>
                            </select>
                            <input type="hidden" name="analista_old" id="analista_old" value="<?php echo $resultado_registros[0][2]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_gestion" class="m-0">Fecha gestión</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_gestion" id="fecha_gestion" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $resultado_registros[0][4]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="duracion" class="m-0">Duración</label>
                          <input type="number" class="form-control form-control-sm" name="duracion" id="duracion" step="1" min="0" max="180" value="<?php echo $resultado_registros[0][5]; ?>" required>
                        </div>
                    </div>
                    <?php if($perfil_modulo=="Administrador"): ?>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_monitoreo" class="m-0">Fecha monitoreo</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_monitoreo" id="fecha_monitoreo" value="<?php echo $resultado_registros[0][27]; ?>" required>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_monitoreo" class="m-0">Tipo monitoreo</label>
                            <select class="form-control form-control-sm" name="tipo_monitoreo" id="tipo_monitoreo" required>
                              <option value="">Seleccione</option>
                              <option value="Calibración Interna" <?php if($resultado_registros[0][6]=="Calibración Interna"){ echo "selected"; } ?>>Calibración Interna</option>
                              <option value="Calibración Externa" <?php if($resultado_registros[0][6]=="Calibración Externa"){ echo "selected"; } ?>>Calibración Externa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="skill_interaccion" class="m-0">Skill interacción</label>
                            <select class="form-control form-control-sm" name="skill_interaccion" id="skill_interaccion" required>
                              <option value="">Seleccione</option>
                              <option value="Click to Call" <?php if($resultado_registros[0][7]=="Click to Call"){ echo "selected"; } ?>>Click to Call</option>
                              <option value="Legal" <?php if($resultado_registros[0][7]=="Legal"){ echo "selected"; } ?>>Legal</option>
                              <option value="Línea 141" <?php if($resultado_registros[0][7]=="Línea 141"){ echo "selected"; } ?>>Línea 141</option>
                              <option value="Línea Anticorrupción" <?php if($resultado_registros[0][7]=="Línea Anticorrupción"){ echo "selected"; } ?>>Línea Anticorrupción</option>
                              <option value="Línea Nacional" <?php if($resultado_registros[0][7]=="Línea Nacional"){ echo "selected"; } ?>>Línea Nacional</option>
                              <option value="Línea Verde" <?php if($resultado_registros[0][7]=="Línea Verde"){ echo "selected"; } ?>>Línea Verde</option>
                              <option value="SNBF" <?php if($resultado_registros[0][7]=="SNBF"){ echo "selected"; } ?>>SNBF</option>
                              <option value="Violencia " <?php if($resultado_registros[0][7]=="Violencia "){ echo "selected"; } ?>>Violencia </option>
                              <option value="N/A" <?php if($resultado_registros[0][7]=="N/A"){ echo "selected"; } ?>>N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_gestion" class="m-0">Tipo gestión</label>
                            <select class="form-control form-control-sm" name="tipo_gestion" id="tipo_gestion" required>
                              <option value="">Seleccione</option>
                              <option value="Actuación ANNACC" <?php if($resultado_registros[0][8]=="Actuación ANNACC"){ echo "selected"; } ?>>Actuación ANNACC</option>
                              <option value="ACVN" <?php if($resultado_registros[0][8]=="ACVN"){ echo "selected"; } ?>>ACVN</option>
                              <option value="Anexo" <?php if($resultado_registros[0][8]=="Anexo"){ echo "selected"; } ?>>Anexo</option>
                              <option value="Asesoría en Derecho de Familia" <?php if($resultado_registros[0][8]=="Asesoría en Derecho de Familia"){ echo "selected"; } ?>>Asesoría en Derecho de Familia</option>
                              <option value="Atención en Crisis" <?php if($resultado_registros[0][8]=="Atención en Crisis"){ echo "selected"; } ?>>Atención en Crisis</option>
                              <option value="Búsqueda de Orígenes" <?php if($resultado_registros[0][8]=="Búsqueda de Orígenes"){ echo "selected"; } ?>>Búsqueda de Orígenes</option>
                              <option value="Comunicación NNA" <?php if($resultado_registros[0][8]=="Comunicación NNA"){ echo "selected"; } ?>>Comunicación NNA</option>
                              <option value="Consulta" <?php if($resultado_registros[0][8]=="Consulta"){ echo "selected"; } ?>>Consulta</option>
                              <option value="Correos" <?php if($resultado_registros[0][8]=="Correos"){ echo "selected"; } ?>>Correos</option>
                              <option value="Encuestas " <?php if($resultado_registros[0][8]=="Encuestas "){ echo "selected"; } ?>>Encuestas </option>
                              <option value="Inobservancia" <?php if($resultado_registros[0][8]=="Inobservancia"){ echo "selected"; } ?>>Inobservancia</option>
                              <option value="IO" <?php if($resultado_registros[0][8]=="IO"){ echo "selected"; } ?>>IO</option>
                              <option value="IOT" <?php if($resultado_registros[0][8]=="IOT"){ echo "selected"; } ?>>IOT</option>
                              <option value="No Gestionó" <?php if($resultado_registros[0][8]=="No Gestionó"){ echo "selected"; } ?>>No Gestionó</option>
                              <option value="No Serio" <?php if($resultado_registros[0][8]=="No Serio"){ echo "selected"; } ?>>No Serio</option>
                              <option value="Observación" <?php if($resultado_registros[0][8]=="Observación"){ echo "selected"; } ?>>Observación</option>
                              <option value="Oficio" <?php if($resultado_registros[0][8]=="Oficio"){ echo "selected"; } ?>>Oficio</option>
                              <option value="Quejas" <?php if($resultado_registros[0][8]=="Quejas"){ echo "selected"; } ?>>Quejas</option>
                              <option value="RAVD" <?php if($resultado_registros[0][8]=="RAVD"){ echo "selected"; } ?>>RAVD</option>
                              <option value="Reclamo" <?php if($resultado_registros[0][8]=="Reclamo"){ echo "selected"; } ?>>Reclamo</option>
                              <option value="Reporte a Policía" <?php if($resultado_registros[0][8]=="Reporte a Policía"){ echo "selected"; } ?>>Reporte a Policía</option>
                              <option value="SRD" <?php if($resultado_registros[0][8]=="SRD"){ echo "selected"; } ?>>SRD</option>
                              <option value="Sugerencias" <?php if($resultado_registros[0][8]=="Sugerencias"){ echo "selected"; } ?>>Sugerencias</option>
                              <option value="TAE" <?php if($resultado_registros[0][8]=="TAE"){ echo "selected"; } ?>>TAE</option>
                              <option value="N/A" <?php if($resultado_registros[0][8]=="N/A"){ echo "selected"; } ?>>N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="segmento" class="m-0">Segmento</label>
                            <select class="form-control form-control-sm" name="segmento" id="segmento" required>
                              <option value="">Seleccione</option>
                              <option value="Aseguramiento" <?php if($resultado_registros[0][9]=="Aseguramiento"){ echo "selected"; } ?>>Aseguramiento</option>
                              <option value="Canal escrito" <?php if($resultado_registros[0][9]=="Canal escrito"){ echo "selected"; } ?>>Canal escrito</option>
                              <option value="Chat ICBF" <?php if($resultado_registros[0][9]=="Chat ICBF"){ echo "selected"; } ?>>Chat ICBF</option>
                              <option value="Conmutador" <?php if($resultado_registros[0][9]=="Conmutador"){ echo "selected"; } ?>>Conmutador</option>
                              <option value="Correos y Portales" <?php if($resultado_registros[0][9]=="Correos y Portales"){ echo "selected"; } ?>>Correos y Portales</option>
                              <option value="Encuestas" <?php if($resultado_registros[0][9]=="Encuestas"){ echo "selected"; } ?>>Encuestas</option>
                              <option value="Línea 141" <?php if($resultado_registros[0][9]=="Línea 141"){ echo "selected"; } ?>>Línea 141</option>
                              <option value="Línea Nacional" <?php if($resultado_registros[0][9]=="Línea Nacional"){ echo "selected"; } ?>>Línea Nacional</option>
                              <option value="Línea Verde" <?php if($resultado_registros[0][9]=="Línea Verde"){ echo "selected"; } ?>>Línea Verde</option>
                              <option value="Presencial" <?php if($resultado_registros[0][9]=="Presencial"){ echo "selected"; } ?>>Presencial</option>
                              <option value="Profesional Es Abogados" <?php if($resultado_registros[0][9]=="Profesional Es Abogados"){ echo "selected"; } ?>>Profesional Es Abogados</option>
                              <option value="Profesional Es Psicólogos" <?php if($resultado_registros[0][9]=="Profesional Es Psicólogos"){ echo "selected"; } ?>>Profesional Es Psicólogos</option>
                              <option value="Profesional Es Violencia Sexual" <?php if($resultado_registros[0][9]=="Profesional Es Violencia Sexual"){ echo "selected"; } ?>>Profesional Es Violencia Sexual</option>
                              <option value="Redes Sociales" <?php if($resultado_registros[0][9]=="Redes Sociales"){ echo "selected"; } ?>>Redes Sociales</option>
                              <option value="Video Llamada" <?php if($resultado_registros[0][9]=="Video Llamada"){ echo "selected"; } ?>>Video Llamada</option>
                              <option value="WhatsApp" <?php if($resultado_registros[0][9]=="WhatsApp"){ echo "selected"; } ?>>WhatsApp</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_sim" class="m-0">Id SIM</label>
                          <input type="text" class="form-control form-control-sm" name="id_sim" id="id_sim" maxlength="100" value="<?php echo $resultado_registros[0][10]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_ani" class="m-0">ID/ANI</label>
                          <input type="text" class="form-control form-control-sm" name="id_ani" id="id_ani" maxlength="100" value="<?php echo $resultado_registros[0][11]; ?>" required>
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
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script src="../js/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
</body>
</html>