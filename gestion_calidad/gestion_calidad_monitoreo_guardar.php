<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       Helpers de seguridad
       ========================= */
    if (!function_exists('h')) {
        function h($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }
    if (!function_exists('hbr')) {
        function hbr($value) {
            return nl2br(h($value));
        }
    }

    // Inicializaciones para evitar warnings (no afecta funcionalidad)
    if (!isset($_SESSION['monitoreo_creado'])) { $_SESSION['monitoreo_creado'] = 0; }
    if (!isset($_SESSION['id_monitoreo'])) { $_SESSION['id_monitoreo'] = ''; }

    // CSRF token (este endpoint recibe POST desde el formulario anterior)
    if (empty($_SESSION['csrf_token_monitoreo'])) {
        $_SESSION['csrf_token_monitoreo'] = bin2hex(random_bytes(32));
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Guardar";
    $pagina = isset($_GET['pagina']) ? validar_input($_GET['pagina']) : '';
    $filtro_permanente = isset($_GET['id']) ? validar_input($_GET['id']) : '';

    // Enlace para botón finalizar y cancelar
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode('Mes Actual');

    // Si no existe la información del monitoreo en sesión, regresamos (flujo seguro)
    if (empty($_SESSION["mon_informacion"]) || empty($_SESSION["mon_informacion"]["analista"]) || empty($_SESSION["mon_informacion"]["matriz"])) {
        header("Location: ".$ruta_cancelar_finalizar);
        exit;
    }

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_fecha_incorporacion`, `usu_piloto` FROM `tb_administrador_usuario` WHERE `usu_id`=?";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    $consulta_registros_analistas->bind_param("s", $_SESSION["mon_informacion"]["analista"]);
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    // Defaults para mensajes de UI
    $respuesta_accion = '';
    $documento_cantidad = 0;
    $documento_registrados = 0;

    if(isset($_POST["guardar_monitoreo"])){

        // ✅ CSRF check (requiere que el formulario anterior envíe csrf_token_monitoreo)
        $token_post = isset($_POST['csrf_token_monitoreo']) ? (string)$_POST['csrf_token_monitoreo'] : '';
        if (empty($token_post) || empty($_SESSION['csrf_token_monitoreo']) || !hash_equals($_SESSION['csrf_token_monitoreo'], $token_post)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Sesión inválida o expirada. Por favor recargue e intente nuevamente.', 0);</script>";
        } else {

            $gcm_matriz=$_SESSION["mon_informacion"]["matriz"];
            $gcm_analista=$_SESSION["mon_informacion"]["analista"];
            $gcm_responsable=$_SESSION["mon_informacion"]["responsable"];
            $gcm_fecha_hora_gestion=$_SESSION["mon_informacion"]["fecha_gestion"];
            $duracion=$_SESSION["mon_informacion"]["duracion"];
            $gcm_fecha_monitoreo=$_SESSION["mon_informacion"]["fecha_monitoreo"];
            $gcm_tipo_monitoreo=$_SESSION["mon_informacion"]["tipo_monitoreo"];
            $gcm_skill_interaccion=$_SESSION["mon_informacion"]["skill_interaccion"];
            $gcm_tipo_gestion=$_SESSION["mon_informacion"]["tipo_gestion"];
            $gcm_segmento=$_SESSION["mon_informacion"]["segmento"];
            $gcm_id_sim=$_SESSION["mon_informacion"]["id_sim"];
            $gcm_id_ani=$_SESSION["mon_informacion"]["id_ani"];
            $gcm_aplica_indicador=$_SESSION["mon_informacion"]["indicador"];
            $gcm_encuesta=$_SESSION["mon_informacion"]["encuesta"];

            $_SESSION["mon_informacion"]["observaciones"]=validar_input($_POST['observaciones'] ?? '');
            $gcm_observaciones_monitoreo=$_SESSION["mon_informacion"]["observaciones"];

            $items_matriz = isset($_POST['id_campos']) ? (array)$_POST['id_campos'] : [];
            $grupo_peso   = isset($_POST['grupo_peso']) ? (array)$_POST['grupo_peso'] : [];
            $peso_nota    = isset($_POST['peso_nota']) ? (array)$_POST['peso_nota'] : [];
            $tipo_error   = isset($_POST['tipo_error']) ? (array)$_POST['tipo_error'] : [];

            $peso_sla_ecuf = $_POST['peso_sla_ecuf'] ?? 0;
            $peso_sla_enc  = $_POST['peso_sla_enc'] ?? 0;
            $peso_sla_ecn  = $_POST['peso_sla_ecn'] ?? 0;

            // Inicializaciones para evitar notices
            $grupos_items_nota = [];
            $grupos_peso_id = [];
            $item_respuesta = [];
            $item_id_respuesta = [];
            $item_comentario = [];
            $item_calificable_tipo_error = [];

            $grupos_tipo_error=array_values(array_unique($tipo_error));

            for ($i=0; $i < count($items_matriz); $i++) {

                $gp = $grupo_peso[$i] ?? '';
                $pe = $peso_nota[$i] ?? 0;
                $te = $tipo_error[$i] ?? '';

                if ($gp!="") {
                    $grupos_items_nota['G-'.$gp]=100;
                    $grupos_peso_id[]='G-'.$gp;
                }

                if (isset($_POST['respuesta_'.$items_matriz[$i]])) {
                    $item_respuesta[]=$_POST['respuesta_'.$items_matriz[$i]];
                    $item_id_respuesta[$items_matriz[$i]]=$_POST['respuesta_'.$items_matriz[$i]];
                } else {
                    $item_respuesta[]="";
                    $item_id_respuesta[$items_matriz[$i]]="";
                }

                if (isset($_POST['comentario_'.$items_matriz[$i]])) {
                    $item_comentario[]=$_POST['comentario_'.$items_matriz[$i]];
                } else {
                    $item_comentario[]="";
                }
            }

            $grupos_peso_id=array_values(array_unique($grupos_peso_id));

            for ($i=0; $i < count($items_matriz); $i++) {

                $gp = $grupo_peso[$i] ?? '';
                $pe = $peso_nota[$i] ?? 0;
                $te = $tipo_error[$i] ?? '';

                if ($gp=="") {
                    if ($item_respuesta[$i]=="No") {
                        $item_calificable_tipo_error[$te][$items_matriz[$i]]=0;
                    } else {
                        $item_calificable_tipo_error[$te][$items_matriz[$i]]=$pe;
                    }
                } else {
                    $item_calificable_tipo_error[$te]['G-'.$gp]=$pe;
                }

                if ($gp!="" and $item_respuesta[$i]=="No") {
                    $grupos_items_nota['G-'.$gp]=0;
                }
            }

            for ($i=0; $i < count($grupos_peso_id); $i++) {
                for ($j=0; $j < count($tipo_error); $j++) {
                    if (isset($item_calificable_tipo_error[$tipo_error[$j]][$grupos_peso_id[$i]])) {
                        if (($grupos_items_nota[$grupos_peso_id[$i]] ?? 100)==0) {
                            $item_calificable_tipo_error[$tipo_error[$j]][$grupos_peso_id[$i]]=0;
                        }
                    }
                }
            }

            if (isset($item_calificable_tipo_error['ENC'])) {
                if (count($item_calificable_tipo_error['ENC'])>0) {
                    $gcm_nota_enc=array_sum($item_calificable_tipo_error['ENC']);
                } else {
                    $gcm_nota_enc="NA";
                }
            } else {
                $gcm_nota_enc="NA";
            }

            if (isset($item_calificable_tipo_error['ECU'])) {
                if (count($item_calificable_tipo_error['ECU'])>0) {
                    $gcm_nota_ecuf=array_sum($item_calificable_tipo_error['ECU']);
                } else {
                    $gcm_nota_ecuf="NA";
                }
            } else {
                $gcm_nota_ecuf="NA";
            }

            if (isset($item_calificable_tipo_error['ECN'])) {
                if (count($item_calificable_tipo_error['ECN'])>0) {
                    $gcm_nota_ecn=array_sum($item_calificable_tipo_error['ECN']);
                } else {
                    $gcm_nota_ecn="NA";
                }
            } else {
                $gcm_nota_ecn="NA";
            }

            $nota_general=0;

            if ($gcm_nota_enc==="NA") {
                $control_estado_enc=1;
            } else {
                $nota_general+=$gcm_nota_enc;
                $control_estado_enc = ($gcm_nota_enc>=$peso_sla_enc) ? 1 : 0;
            }

            if ($gcm_nota_ecuf==="NA") {
                $control_estado_ecuf=1;
            } else {
                $nota_general+=$gcm_nota_ecuf;
                $control_estado_ecuf = ($gcm_nota_ecuf>=$peso_sla_ecuf) ? 1 : 0;
            }

            if ($gcm_nota_ecn==="NA") {
                $control_estado_ecn=1;
            } else {
                $nota_general+=$gcm_nota_ecn;
                $control_estado_ecn = ($gcm_nota_ecn>=$peso_sla_ecn) ? 1 : 0;
            }

            if ($control_estado_enc==1 AND $control_estado_ecuf==1 AND $control_estado_ecn==1) {
                $gcm_estado="Aceptado";
            } else {
                $gcm_estado="Pendiente";
            }

            if (!empty($resultado_registros_analistas) && ($resultado_registros_analistas[0][3] ?? '')=='Si') {
                $fecha_aplica_indicador=date('Y-m-d', strtotime('+3 month', strtotime($resultado_registros_analistas[0][2])));
            } else {
                $fecha_aplica_indicador=date('Y-m-d', strtotime('+2 month', strtotime($resultado_registros_analistas[0][2] ?? date('Y-m-d'))));
            }

            if ($perfil_modulo=="Cliente") {
                $gcm_aplica_indicador='No-Cliente';
            } elseif (date('Y-m-d', strtotime($gcm_fecha_hora_gestion))<=$fecha_aplica_indicador) {
                $gcm_aplica_indicador='No-Curva Aprendizaje';
            } elseif ($perfil_modulo=="Supervisor") {
                $gcm_aplica_indicador='No-Supervisor';
            } elseif ($perfil_modulo=="Formador") {
                $gcm_aplica_indicador='No-Formador';
            } elseif (date('Y-m-d', strtotime($gcm_fecha_hora_gestion))>$fecha_aplica_indicador) {
                if ($gcm_aplica_indicador=='No-Gestor') {
                    $gcm_aplica_indicador=$gcm_aplica_indicador;
                } else {
                    $gcm_aplica_indicador='Si';
                }
            }

            $gcm_auditoria='';
            $gcm_auditoria_responsable='';

            if($_SESSION['monitoreo_creado']!=1){

                // Consecutivo (mantiene lógica original)
                $consulta_consecutivo = mysqli_query($enlace_db, "SELECT MAX(`gcm_id`) FROM `tb_gestion_calidad_monitoreo`");
                $resultado_consecutivo = mysqli_fetch_all($consulta_consecutivo);
                $ultimo_consecutivo=explode('MC', $resultado_consecutivo[0][0] ?? 'MC0000000000');
                $nuevo_consecutivo=((int)($ultimo_consecutivo[1] ?? 0))+1;
                $inser_consecutivo="MC".str_pad($nuevo_consecutivo, 10, 0, STR_PAD_LEFT);

                // Prepara la sentencia
                $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_monitoreo`(`gcm_id`, `gcm_matriz`, `gcm_analista`, `gcm_responsable`, `gcm_fecha_hora_gestion`, `gcm_fecha_hora_cierre`, `gcm_tipo_monitoreo`, `gcm_skill_interaccion`, `gcm_tipo_gestion`, `gcm_segmento`, `gcm_id_sim`, `gcm_id_ani`, `gcm_observaciones_monitoreo`, `gcm_nota_enc`, `gcm_nota_ecn`, `gcm_nota_ecuf`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_estado`, `gcm_aplica_indicador`, `gcm_encuesta`, `gcm_fecha_monitoreo`, `gcm_auditoria`, `gcm_auditoria_responsable`, `gcm_registro_usuario`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                // Agrega variables a sentencia preparada
                $sentencia_insert->bind_param(
                    'sssssssssssssssssssssssssss',
                    $inser_consecutivo, $gcm_matriz, $gcm_analista, $gcm_responsable, $gcm_fecha_hora_gestion, $duracion,
                    $gcm_tipo_monitoreo, $gcm_skill_interaccion, $gcm_tipo_gestion, $gcm_segmento, $gcm_id_sim, $gcm_id_ani,
                    $gcm_observaciones_monitoreo, $gcm_nota_enc, $gcm_nota_ecn, $gcm_nota_ecuf, $nota_general,
                    $control_estado_enc, $control_estado_ecn, $control_estado_ecuf,
                    $gcm_estado, $gcm_aplica_indicador, $gcm_encuesta, $gcm_fecha_monitoreo, $gcm_auditoria, $gcm_auditoria_responsable,
                    $_SESSION['usu_id']
                );

                if ($sentencia_insert->execute()) {

                    $_SESSION['id_monitoreo']=$inser_consecutivo;
                    $control_insert=0;

                    // Prepara la sentencia
                    $sentencia_insert_calificaciones = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_monitoreo_calificaciones`(`gcmc_monitoreo`, `gcmc_pregunta`, `gcmc_respuesta`, `gcmc_afectaciones`, `gcmc_comentarios`) VALUES (?,?,?,?,?)");

                    for ($i=0; $i < count($items_matriz); $i++) {
                        $item_matriz_pregunta=$items_matriz[$i];
                        $afectaciones="";
                        $respuesta_item=$item_respuesta[$i];
                        $comentarios_insert=$item_comentario[$i];

                        $sentencia_insert_calificaciones->bind_param('sssss', $inser_consecutivo, $item_matriz_pregunta, $respuesta_item, $afectaciones, $comentarios_insert);

                        if ($sentencia_insert_calificaciones->execute()) {
                            $control_insert++;
                        }
                    }

                    if (count($items_matriz)==$control_insert) {
                        $respuesta_accion = "<script type='text/javascript'>alertify.success('Monitoreo creado exitosamente!', 0);</script>";
                        $_SESSION['monitoreo_creado']=1;

                        // ========= Upload seguro (sin cambiar funcionalidad) =========
                        $dangerous_ext = ['php','phtml','phar','shtml','html','htm','js','jsp','asp','aspx','cgi','pl','sh','bat','cmd','exe','dll'];

                        $guardar_archivo = function($file_array, $key, $prefijo) use ($enlace_db, $inser_consecutivo, $dangerous_ext, &$documento_cantidad, &$documento_registrados) {
                            if (empty($file_array["name"][$key])) {
                                return 1;
                            }

                            $documento_cantidad++;

                            $ruta_guardar="storage/".$inser_consecutivo;
                            if (!file_exists($ruta_guardar)) {
                                mkdir($ruta_guardar, 0755, true);
                            }

                            $original = (string)$file_array['name'][$key];
                            $base = basename($original);
                            $base = preg_replace('/[^A-Za-z0-9._-]/', '_', $base);
                            $base = substr($base, 0, 180);

                            $archivo_extension = strtolower(pathinfo($base, PATHINFO_EXTENSION));

                            // Bloqueo de extensiones peligrosas (no debería afectar soportes reales)
                            if ($archivo_extension !== '' && in_array($archivo_extension, $dangerous_ext, true)) {
                                return 0;
                            }

                            $nombre_soporte_1 = $inser_consecutivo."-".$prefijo."-".$base;
                            $ruta_final = $ruta_guardar."/".$nombre_soporte_1;

                            if (($file_array["error"][$key] ?? 0) > 0) {
                                return 0;
                            }

                            if (!is_uploaded_file($file_array['tmp_name'][$key])) {
                                return 0;
                            }

                            if (move_uploaded_file($file_array['tmp_name'][$key], $ruta_final)) {

                                $consulta_registro_soporte_insert = $enlace_db->prepare(
                                    "INSERT INTO `tb_gestion_calidad_monitoreo_soportes`(`gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`) VALUES (?,?,?,?,?);"
                                );

                                $consulta_registro_soporte_insert->bind_param('sssss', $inser_consecutivo, $nombre_soporte_1, $ruta_final, $archivo_extension, $_SESSION['usu_id']);

                                if ($consulta_registro_soporte_insert->execute()) {
                                    $documento_registrados++;
                                    return 1;
                                }
                                return 0;
                            }
                            return 0;
                        };

                        if (isset($_FILES["documento"]) && isset($_FILES["documento"]['tmp_name']) && is_array($_FILES["documento"]['tmp_name'])) {
                            foreach($_FILES["documento"]['tmp_name'] as $key => $tmp_name) {
                                $ok = $guardar_archivo($_FILES["documento"], $key, "documento");
                                if (!$ok) {
                                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                                }
                            }
                        }

                        if (isset($_FILES["documento_llamada"]) && isset($_FILES["documento_llamada"]['tmp_name']) && is_array($_FILES["documento_llamada"]['tmp_name'])) {
                            foreach($_FILES["documento_llamada"]['tmp_name'] as $key => $tmp_name) {
                                $ok = $guardar_archivo($_FILES["documento_llamada"], $key, "llamada");
                                if (!$ok) {
                                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                                }
                            }
                        }

                        //insert log eventos
                        $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";

                        $log_modulo=$modulo_plataforma;
                        $log_tipo="crear";
                        $log_accion="Crear registro";
                        $log_detalle="Monitoreo Calidad | ".$_SESSION['id_monitoreo'];
                        $log_usuario=$_SESSION["usu_id"];

                        $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                        $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                        $consulta_registros_log->execute();
                        //insert log eventos

                        if ($gcm_estado=='Pendiente' AND $perfil_modulo!="Cliente") {

                            $consulta_string_supervisor="SELECT TU.`usu_id`, TU.`usu_nombres_apellidos`, TL.`usu_id`, TL.`usu_nombres_apellidos`, TL.`usu_correo_corporativo`, TU.`usu_correo_corporativo` FROM `tb_administrador_usuario` AS TU LEFT JOIN `tb_administrador_usuario` AS TL ON TU.`usu_supervisor`=TL.`usu_id` WHERE TU.`usu_id`=?";

                            $consulta_registros_supervisor = $enlace_db->prepare($consulta_string_supervisor);
                            $consulta_registros_supervisor->bind_param("s", $gcm_analista);
                            $consulta_registros_supervisor->execute();
                            $resultado_registros_supervisor = $consulta_registros_supervisor->get_result()->fetch_all(MYSQLI_NUM);

                            if (!empty($resultado_registros_supervisor)) {

                                $array_correo_destino['correos']['TO']=$resultado_registros_supervisor[0][5]."|".$resultado_registros_supervisor[0][1];
                                $array_correo_destino['correos']['CC']=$resultado_registros_supervisor[0][4]."|".$resultado_registros_supervisor[0][3];

                                $nota_correo_enc  = $control_estado_enc  ? 'CUMPLE' : 'INCUMPLE';
                                $nota_correo_ecuf = $control_estado_ecuf ? 'CUMPLE' : 'INCUMPLE';
                                $nota_correo_ecn  = $control_estado_ecn  ? 'CUMPLE' : 'INCUMPLE';

                                // Escapes seguros para HTML de correo
                                $agente_nombre = h($resultado_registros_supervisor[0][1]);
                                $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>Cordial Saludo,<br><br>Se ha monitoreado al agente ".$agente_nombre.", con los siguientes resultados. Por favor verificar el detalle del monitoreo ingresando al siguiente link: <a href='http://52.188.206.38/' target='_blank'>ICBF-IQGIS</a></p><br>
                                <center>
                                <table style='width: 500px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Monitoreo</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($inser_consecutivo) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Analista</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($resultado_registros_supervisor[0][0]) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nombres y Apellidos</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($resultado_registros_supervisor[0][1]) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Fecha gestión/grabación</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_fecha_hora_gestion) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Tipo monitoreo</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_tipo_monitoreo) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Skill interacción</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_skill_interaccion) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Tipo gestión</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_tipo_gestion) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Segmento</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_segmento) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id SIM</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_id_sim) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id/ANI</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($gcm_id_ani) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nota ENC</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($nota_correo_enc) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nota ECUF</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($nota_correo_ecuf) ."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nota ECN</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". h($nota_correo_ecn) ."</td>
                                    </tr>
                                </table>
                                <br>
                                <table style='width: 100%; background: #2E2E2E; font-family: Lato, Arial, sans-serif;'>
                                    <tr>
                                        <td style='font-size: 12px; padding: 5px 10px 5px 10px; color: #FFFFFF'>
                                            IQ-ICBF | Gestión Integrada de Servicios | &copy; Copyright 2020 Todos los derechos reservados.
                                        </td>
                                    </tr>
                                </table>
                                <p style='font-family: Lato, Arial, sans-serif; color: #2E2E2E;'>
                                Esta es una notificación automática, por favor no responda este mensaje.
                                </p>
                                <table>
                                    <tr>
                                        <td style='width: 50px; height: 73px;'><img src='cid:logo_firma_verde' style='margin: 5px; width: 50px; height: 73px;'></td>
                                        <td>
                                            <p style='font-size: 11px; color: #006400; font-family: Lato, Arial, sans-serif;'><br><b>No imprima este mensaje de no ser necesario; de ésta manera aportamos al cuidado del planeta.</b></p>
                                        </td>
                                    </tr>
                                </table>
                                </center>";

                                /*SE CONFIGURAN PARÁMETROS A REGISTRAR EN SISTEMA DE NOTIFICACIÓN*/
                                $nc_id_modulo="2";
                                $nc_prioridad="Alta";
                                $nc_id_set_from="1";
                                $nc_address=$array_correo_destino['correos']['TO'];
                                $nc_cc=$array_correo_destino['correos']['CC'];
                                $nc_bcc="";
                                $nc_reply_to="";
                                $nc_subject="Monitoreo Calidad | ".$inser_consecutivo;

                                // Mantengo el comportamiento original del body (solo ya no se concatena en SQL)
                                $nc_body=str_replace("'", '"', $contenido_correo);

                                $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png";
                                $nc_embeddedimage_nombre="logo_firma_verde";
                                $nc_embeddedimage_tipo="image/png";
                                $nc_intentos="";
                                $nc_eliminar="Si";
                                $nc_estado_envio="Pendiente";
                                $nc_fecha_envio="";
                                $nc_usuario_registro=$_SESSION['usu_id'];

                                // ✅ Remediación SQLi: INSERT parametrizado (con reintento como tu lógica)
                                $sql_notif = "INSERT INTO `tb_notificaciones_central`
                                    (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                                     `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                                     `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                                $stmt_notif = $enlace_db->prepare($sql_notif);

                                for ($i=0; $i < 5; $i++) {
                                    $ok_notif = false;

                                    if ($stmt_notif) {
                                        $stmt_notif->bind_param(
                                            "sssssssssssssssss",
                                            $nc_id_modulo,
                                            $nc_prioridad,
                                            $nc_id_set_from,
                                            $nc_address,
                                            $nc_cc,
                                            $nc_bcc,
                                            $nc_reply_to,
                                            $nc_subject,
                                            $nc_body,
                                            $nc_embeddedimage_ruta,
                                            $nc_embeddedimage_nombre,
                                            $nc_embeddedimage_tipo,
                                            $nc_intentos,
                                            $nc_eliminar,
                                            $nc_estado_envio,
                                            $nc_fecha_envio,
                                            $nc_usuario_registro
                                        );

                                        $ok_notif = $stmt_notif->execute();
                                    }

                                    if ($ok_notif) {
                                        if (function_exists('registro_log')) {
                                            registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject);
                                        }
                                        break;
                                    }
                                }
                            }
                        }

                        // Rotación de token después de acción exitosa
                        $_SESSION['csrf_token_monitoreo'] = bin2hex(random_bytes(32));
                    }

                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el monitoreo, por favor verifique e intente nuevamente!', 0);</script>";
                }

            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('Monitoreo creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
            }
        }
    }

    $consulta_string_matriz="SELECT `gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`, `gcm_registro_fecha` FROM `tb_gestion_calidad_matriz` WHERE `gcm_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $_SESSION["mon_informacion"]["matriz"]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

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
        <div class="row justify-content-center">
            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <p class="alert alert-success p-1">¡Se ha generado el monitoreo <?php echo h($_SESSION['id_monitoreo']); ?>!</p>
                        <?php if ($documento_cantidad!=$documento_registrados): ?>
                            <p class="alert alert-danger p-1">¡Problemas al cargar algunos soportes, por favor verifique en la bandeja principal!</p>
                        <?php endif; ?>
                        <div class="form-group">
                          <label for="matriz" class="m-0">Matriz</label>
                          <input type="text" class="form-control form-control-sm" name="matriz" id="matriz" maxlength="50"
                            value="<?php echo h(($resultado_registros_matriz[0][1] ?? '')); ?> [<?php echo h(($resultado_registros_matriz[0][3] ?? '')); ?>]" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="analista" class="m-0">Analista</label>
                            <input type="text" class="form-control form-control-sm" name="analista" id="analista" maxlength="50" value="<?php echo h(($resultado_registros_analistas[0][1] ?? '')); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_gestion" class="m-0">Fecha gestión</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_gestion" id="fecha_gestion" maxlength="20" value="<?php if(isset($_SESSION["mon_informacion"]["fecha_gestion"])){ echo h($_SESSION["mon_informacion"]["fecha_gestion"]); } ?>" required readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_monitoreo" class="m-0">Tipo monitoreo</label>
                            <select class="form-control form-control-sm" name="tipo_monitoreo" id="tipo_monitoreo" required disabled>
                              <option value="">Seleccione</option>
                              <option value="En línea" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND $_SESSION["mon_informacion"]["tipo_monitoreo"]=="En línea"){ echo "selected"; } ?>>En línea</option>
                              <option value="Grabación" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND $_SESSION["mon_informacion"]["tipo_monitoreo"]=="Grabación"){ echo "selected"; } ?>>Grabación</option>
                              <option value="Calibración" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND $_SESSION["mon_informacion"]["tipo_monitoreo"]=="Calibración"){ echo "selected"; } ?>>Calibración</option>
                            </select>
                        </div>
                    </div>

                    <!-- (El resto del HTML queda igual; solo se escaparon salidas vulnerables) -->

                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" readonly><?php if(isset($_SESSION["mon_informacion"]["observaciones"])){ echo h($_SESSION["mon_informacion"]["observaciones"]); } ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['monitoreo_creado']==1): ?>
                                <a href="<?php echo h($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>
