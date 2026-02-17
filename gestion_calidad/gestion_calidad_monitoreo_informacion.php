<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       FIXES / HELPERS
       ========================= */

    // Asegura variables de sesión para evitar warnings
    if (!isset($_SESSION['registro_creado'])) { $_SESSION['registro_creado'] = 0; }
    if (!isset($_SESSION['mon_informacion']) || !is_array($_SESSION['mon_informacion'])) { $_SESSION['mon_informacion'] = []; }

    // Helper para imprimir seguro (evita XSS / caracteres raros)
    function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

    // Helper selected tolerante a espacios
    function selected_trim($current, $option){
        return (trim((string)$current) === trim((string)$option)) ? 'selected' : '';
    }

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Información";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    // Inicializa variable tipo array
    $data_consulta=array();

    if (isset($_SESSION["mon_informacion"]) AND ($_SESSION["mon_informacion"]["matriz"] ?? '')!="") {
        $id_matriz=$_SESSION["mon_informacion"]["matriz"];
    } else {
        $id_matriz=validar_input(base64_decode($_GET['mat']));
    }

    if ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Formador" OR $perfil_modulo=="Cliente") {
        $filtro_perfil="";
    } elseif($perfil_modulo=="Supervisor"){
        $filtro_perfil=" AND (`usu_supervisor`=?)";
        array_push($data_consulta, $_SESSION["usu_id"]);
    }

    $consulta_string_matriz="SELECT `gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`, `gcm_registro_fecha` FROM `tb_gestion_calidad_matriz` WHERE `gcm_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $id_matriz);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE (`usu_cargo_rol` LIKE '%Agente%' OR `usu_cargo_rol` LIKE '%Supervisor%') ".$filtro_perfil." ORDER BY `usu_nombres_apellidos`";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    if (count($data_consulta)>0) {
        // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta
        $consulta_registros_analistas->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_monitorear="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_martes`, `ccmm_monitoreo_miercoles`, `ccmm_monitoreo_jueves`, `ccmm_monitoreo_viernes`, `ccmm_monitoreo_sabado`, `ccmm_monitoreo_domingo`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_monitor`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TA.`usu_nombres_apellidos`
    FROM `tb_gestion_calidad_cmuestral_malla`
    LEFT JOIN `tb_administrador_usuario` AS TA ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario`=TA.`usu_id`
    WHERE `ccmm_monitor`=? AND `ccmm_mes` LIKE '".date('Y-m')."%'";

    $consulta_registros_monitorear = $enlace_db->prepare($consulta_string_monitorear);
    $consulta_registros_monitorear->bind_param("s", $_SESSION["usu_id"]);
    $consulta_registros_monitorear->execute();
    $resultado_registros_monitorear = $consulta_registros_monitorear->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode('Mes Actual');
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <link rel="stylesheet" href="../js/bootstrap-select/dist/css/bootstrap-select.min.css">
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row">
            <div class="col-md-3 py-2"></div>
            <div class="col-md-9 py-2">
                <a href="gestion_calidad_monitoreo_matriz.php?pagina=<?php echo h($pagina); ?>&id=<?php echo h($filtro_permanente); ?>" class="btn btn-corp menu float-right">
                    <div class="float-left"><span class="fas fa-arrow-left"></span></div>
                    <div class="pl-2 menu_res float-left">Regresar</div>
                </a>
            </div>
        </div>

        <form name="guardar_informacion" action="gestion_calidad_monitoreo_evaluacion.php?pagina=<?php echo h($pagina); ?>&id=<?php echo h($filtro_permanente); ?>" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <?php if(count($resultado_registros_monitorear)>0): ?>
            <div class="col-md-3 pt-2 background-blanco">
                <p class="alert alert-warning font-size-11">
                  Usuarios a monitorear este mes:<br>
                  <?php for ($i=0; $i < count($resultado_registros_monitorear); $i++): ?>
                    <?php echo h($resultado_registros_monitorear[$i][3]).'|'.h($resultado_registros_monitorear[$i][25]).'<br>'; ?>
                  <?php endfor; ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="matriz" class="m-0">Matriz</label>
                          <!-- FIX: antes usabas $i y no existía acá -->
                          <input type="text" class="form-control form-control-sm" name="matriz" id="matriz"
                                 value="<?php echo h($resultado_registros_matriz[0][1]); ?> [<?php echo h($resultado_registros_matriz[0][3]); ?>]" readonly>
                          <input type="hidden" class="form-control form-control-sm" name="id_matriz" id="id_matriz"
                                 value="<?php echo h($resultado_registros_matriz[0][0]); ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="analista" class="m-0">Analista</label>
                            <select class="selectpicker form-control form-control-sm font-size-11 padding-2" data-live-search="true" name="analista" id="analista" required>
                              <option value="" class="font-size-11">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_analistas); $i++): ?>
                                <option value="<?php echo h($resultado_registros_analistas[$i][0]); ?>"
                                        class="font-size-11"
                                        data-tokens="<?php echo h($resultado_registros_analistas[$i][0].' '.$resultado_registros_analistas[$i][1]); ?>"
                                        <?php if(isset($_SESSION["mon_informacion"]["analista"]) AND $_SESSION["mon_informacion"]["analista"]==$resultado_registros_analistas[$i][0]){ echo "selected"; } ?>>
                                    <?php echo h($resultado_registros_analistas[$i][1]); ?>
                                </option>
                              <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_gestion" class="m-0">Fecha gestión</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_gestion" id="fecha_gestion"
                                 max="<?php echo h(date('Y-m-d')); ?>"
                                 value="<?php if(isset($_SESSION["mon_informacion"]["fecha_gestion"])){ echo h($_SESSION["mon_informacion"]["fecha_gestion"]); } ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="duracion" class="m-0">Duración (minutos)</label>
                          <input type="number" class="form-control form-control-sm" name="duracion" id="duracion" step="1" min="0" max="180"
                                 value="<?php if(isset($_SESSION["mon_informacion"]["duracion"])){ echo h($_SESSION["mon_informacion"]["duracion"]); } ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_monitoreo" class="m-0">Tipo monitoreo</label>
                            <select class="form-control form-control-sm" name="tipo_monitoreo" id="tipo_monitoreo" required>
                              <option value="">Seleccione</option>
                              <option value="En línea" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND trim($_SESSION["mon_informacion"]["tipo_monitoreo"])=="En línea"){ echo "selected"; } ?>>En línea</option>
                              <option value="Grabación" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND trim($_SESSION["mon_informacion"]["tipo_monitoreo"])=="Grabación"){ echo "selected"; } ?>>Grabación</option>
                              <option value="Al lado"   <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND trim($_SESSION["mon_informacion"]["tipo_monitoreo"])=="Al lado"){ echo "selected"; } ?>>Al lado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="skill_interaccion" class="m-0">Skill interacción</label>

                            <!-- FIX: skills completos + normalizados + no rompe valores viejos -->
                            <select class="form-control form-control-sm" name="skill_interaccion" id="skill_interaccion" required>
                              <option value="">Seleccione</option>
                              <?php
                                $skill_sel = $_SESSION["mon_informacion"]["skill_interaccion"] ?? '';

                                $op_skills = [
                                  "Click to Call",
                                  "Línea Verde",
                                  "Legal",
                                  "Línea 141",
                                  "Línea Anticorrupción",
                                  "Línea Nacional",
                                  "Mi Familia",
                                  "SNBF",
                                  "Violencia",
                                  "Correos",
                                  "Redes Sociales",
                                  "Escrito",
                                  "Chat",
                                  "WhatsApp",
                                  "Videollamada",
                                  "Apoyo Adopciones",
                                  "N/A"
                                ];

                                $en_lista = false;
                                foreach ($op_skills as $o){ if(trim($skill_sel) === trim($o)) { $en_lista = true; break; } }

                                if ($skill_sel !== '' && !$en_lista){
                                    echo '<option value="'.h($skill_sel).'" selected>'.h($skill_sel).'</option>';
                                }

                                foreach ($op_skills as $opt){
                                    $label = ($opt === "N/A") ? "No Aplica (N/A)" : $opt;
                                    echo '<option value="'.h($opt).'" '.selected_trim($skill_sel, $opt).'>'.h($label).'</option>';
                                }
                              ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_gestion" class="m-0">Tipo gestión</label>
                            <select class="form-control form-control-sm" name="tipo_gestion" id="tipo_gestion" required>
                              <option value="">Seleccione</option>
                              <option value="Acompañamiento familiar" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Acompañamiento familiar"){ echo "selected"; } ?>>Acompañamiento familiar</option>
                              <option value="Actuación ANNACC" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Actuación ANNACC"){ echo "selected"; } ?>>Actuación ANNACC</option>
                              <option value="ACVN" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="ACVN"){ echo "selected"; } ?>>ACVN</option>
                              <option value="Anexo" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Anexo"){ echo "selected"; } ?>>Anexo</option>

                              <!-- MOD: Asesoría en derecho de familia -> Orientación en derecho de familia -->
                              <option value="Orientación en derecho de familia" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Orientación en derecho de familia"){ echo "selected"; } ?>>Orientación en derecho de familia</option>

                              <option value="Atención en Crisis" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Atención en Crisis"){ echo "selected"; } ?>>Atención en Crisis</option>
                              <option value="Búsqueda de Orígenes" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Búsqueda de Orígenes"){ echo "selected"; } ?>>Búsqueda de Orígenes</option>
                              <option value="Comunicación NNA" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Comunicación NNA"){ echo "selected"; } ?>>Comunicación NNA</option>
                              <option value="Consulta" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Consulta"){ echo "selected"; } ?>>Consulta</option>
                              <option value="Correos" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Correos"){ echo "selected"; } ?>>Correos</option>

                              <!-- FIX: sin espacio final -->
                              <option value="Encuestas" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Encuestas"){ echo "selected"; } ?>>Encuestas</option>

                              <option value="Inobservancia" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Inobservancia"){ echo "selected"; } ?>>Inobservancia</option>
                              <option value="IO" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="IO"){ echo "selected"; } ?>>IO</option>
                              <option value="IOT" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="IOT"){ echo "selected"; } ?>>IOT</option>
                              <option value="No Gestionó" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="No Gestionó"){ echo "selected"; } ?>>No Gestionó</option>
                              <option value="No Serio" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="No Serio"){ echo "selected"; } ?>>No Serio</option>
                              <option value="Observación" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Observación"){ echo "selected"; } ?>>Observación</option>
                              <option value="Oficio" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Oficio"){ echo "selected"; } ?>>Oficio</option>
                              <option value="Quejas" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Quejas"){ echo "selected"; } ?>>Quejas</option>
                              <option value="RAVD" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="RAVD"){ echo "selected"; } ?>>RAVD</option>
                              <option value="Reclamo" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Reclamo"){ echo "selected"; } ?>>Reclamo</option>
                              <option value="Reporte a Policía" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Reporte a Policía"){ echo "selected"; } ?>>Reporte a Policía</option>
                              <option value="SRD" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="SRD"){ echo "selected"; } ?>>SRD</option>
                              <option value="Sugerencias" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Sugerencias"){ echo "selected"; } ?>>Sugerencias</option>
                              <option value="TAE" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="TAE"){ echo "selected"; } ?>>TAE</option>
                              <option value="N/A" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="N/A"){ echo "selected"; } ?>>N/A</option>

                              <!-- ADD: Transferencia -->
                              <option value="Transferencia" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND trim($_SESSION["mon_informacion"]["tipo_gestion"])=="Transferencia"){ echo "selected"; } ?>>Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="segmento" class="m-0">Segmento</label>
                            <select class="form-control form-control-sm" name="segmento" id="segmento" required>
                              <option value="">Seleccione</option>
                              <option value="Aseguramiento" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Aseguramiento"){ echo "selected"; } ?>>Aseguramiento</option>
                              <option value="Canal escrito" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Canal escrito"){ echo "selected"; } ?>>Canal escrito</option>
                              <option value="Chat ICBF" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Chat ICBF"){ echo "selected"; } ?>>Chat ICBF</option>
                              <option value="Conmutador" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Conmutador"){ echo "selected"; } ?>>Conmutador</option>
                              <option value="Correos y Portales" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Correos y Portales"){ echo "selected"; } ?>>Correos y Portales</option>
                              <option value="Encuestas" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Encuestas"){ echo "selected"; } ?>>Encuestas</option>
                              <option value="Línea 141" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Línea 141"){ echo "selected"; } ?>>Línea 141</option>
                              <option value="Línea Nacional" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Línea Nacional"){ echo "selected"; } ?>>Línea Nacional</option>
                              <option value="Mi Familia" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Mi Familia"){ echo "selected"; } ?>>Mi Familia</option>
                              <option value="Presencial" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Presencial"){ echo "selected"; } ?>>Presencial</option>
                              <option value="Profesional Es Abogados" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Profesional Es Abogados"){ echo "selected"; } ?>>Profesional Es Abogados</option>
                              <option value="Profesional ES Psicólogos" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Profesional ES Psicólogos"){ echo "selected"; } ?>>Profesional ES Psicólogos</option>
                              <option value="Redes Sociales" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Redes Sociales"){ echo "selected"; } ?>>Redes Sociales</option>
                              <option value="Video Llamada" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="Video Llamada"){ echo "selected"; } ?>>Video Llamada</option>
                              <option value="WhatsApp" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND trim($_SESSION["mon_informacion"]["segmento"])=="WhatsApp"){ echo "selected"; } ?>>WhatsApp</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_sim" class="m-0">Id SIM</label>
                          <input type="text" class="form-control form-control-sm" name="id_sim" id="id_sim" maxlength="100"
                                 value="<?php if(isset($_SESSION["mon_informacion"]["id_sim"])){ echo h($_SESSION["mon_informacion"]["id_sim"]); } ?>"
                                 onkeyup="consultar_idsim();" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_ani" class="m-0">ID/ANI</label>
                          <input type="text" class="form-control form-control-sm" name="id_ani" id="id_ani" maxlength="100"
                                 value="<?php if(isset($_SESSION["mon_informacion"]["id_ani"])){ echo h($_SESSION["mon_informacion"]["id_ani"]); } ?>" required>
                        </div>
                    </div>

                    <?php if($perfil_modulo=="Gestor" OR $perfil_modulo=="Administrador" OR $perfil_modulo=="Formador"): ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="indicador" class="m-0">Indicador</label>
                            <select class="form-control form-control-sm" name="indicador" id="indicador" required>
                                <option value="">Seleccione</option>
                                <?php
                                    $ind_sel = $_SESSION["mon_informacion"]["indicador"] ?? '';

                                    // Requerido
                                    $op_ind = [
                                        "Indicador",
                                        "Encuesta",
                                        "No- Curva",
                                        "No- Supervisor",
                                        "No- Gestor",
                                    ];

                                    // Si venía algo viejo (Si, No-Gestor, No-Formador), lo dejo visible para no romper edición
                                    $en_lista = false;
                                    foreach ($op_ind as $o){ if(trim($ind_sel) === trim($o)) { $en_lista = true; break; } }
                                    if ($ind_sel !== '' && !$en_lista){
                                        echo '<option value="'.h($ind_sel).'" selected>'.h($ind_sel).'</option>';
                                    }

                                    foreach ($op_ind as $opt){
                                        echo '<option value="'.h($opt).'" '.selected_trim($ind_sel, $opt).'>'.h($opt).'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="encuesta" class="m-0">Encuesta</label>
                            <?php $enc_sel = trim((string)($_SESSION["mon_informacion"]["encuesta"] ?? '')); ?>
                            <select class="form-control form-control-sm" name="encuesta" id="encuesta" required>
                                <option value="">Seleccione</option>
                                <option value="Si"        <?php echo ($enc_sel==="Si") ? "selected" : ""; ?>>Si</option>
                                <option value="No"        <?php echo ($enc_sel==="No") ? "selected" : ""; ?>>No</option>
                                <option value="No aplica" <?php echo ($enc_sel==="" || $enc_sel==="No aplica") ? "selected" : ""; ?>>No aplica</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-12" id="coincidencias"></div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado']==1): ?>
                                <a href="<?php echo h($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_informacion">Siguiente</button>
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
    <script src="../js/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script type="text/javascript">
        //funcion ajax consultar_idsim que va a post_busqueda.php, ejecuta el codigo php e imprime los resultados en el div id=coincidencias
        function consultar_idsim(){
            $.ajax({
                success: function(){
                    $("#coincidencias").load(
                        "gestion_calidad_monitoreo_informacion_duplicado.php?id=" + encodeURIComponent($("#id_sim").val())
                    );
                }
            });
        }
    </script>
</body>
</html>
