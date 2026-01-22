<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Evaluación - Editar";

    // Lectura segura de parámetros (evita warnings y valida base64)
    $pagina = validar_input($_GET['pagina'] ?? '');
    $bandeja_decoded = base64_decode($_GET['bandeja'] ?? '', true);
    $bandeja = validar_input(($bandeja_decoded !== false) ? $bandeja_decoded : '');
    $filtro_permanente = validar_input($_GET['id'] ?? '');
    $reg_decoded = base64_decode($_GET['reg'] ?? '', true);
    $id_registro = validar_input(($reg_decoded !== false) ? $reg_decoded : '');

    // Helper de escape para salida HTML (prevención XSS)
    if (!function_exists('e')) {
        function e($value): string {
            return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    // CSRF token (prevención CSRF)
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
if(isset($_POST["actualizar_monitoreo"])){

        // Validación CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Sesión inválida o expirada. Actualiza la página e inténtalo de nuevo.', 0);</script>";
        } else {

            // Sanitiza entradas (sin cambiar lógica de negocio)
            $gcm_observaciones_monitoreo = validar_input($_POST['observaciones'] ?? '');

            $items_matriz = (isset($_POST['id_campos']) && is_array($_POST['id_campos'])) ? array_map('validar_input', $_POST['id_campos']) : array();
            $grupo_peso   = (isset($_POST['grupo_peso']) && is_array($_POST['grupo_peso'])) ? array_map('validar_input', $_POST['grupo_peso']) : array();
            $peso_nota    = (isset($_POST['peso_nota']) && is_array($_POST['peso_nota'])) ? array_map('validar_input', $_POST['peso_nota']) : array();
            $tipo_error   = (isset($_POST['tipo_error']) && is_array($_POST['tipo_error'])) ? array_map('validar_input', $_POST['tipo_error']) : array();

            $peso_sla_ecuf = (float) validar_input($_POST['peso_sla_ecuf'] ?? 0);
            $peso_sla_enc  = (float) validar_input($_POST['peso_sla_enc'] ?? 0);
            $peso_sla_ecn  = (float) validar_input($_POST['peso_sla_ecn'] ?? 0);

            $ecuf_old = (int) validar_input($_POST['ecuf_old'] ?? 0);
            $ecn_old  = (int) validar_input($_POST['ecn_old'] ?? 0);
            $enc_old  = (int) validar_input($_POST['enc_old'] ?? 0);

            $id_analista = validar_input($_POST['id_analista'] ?? '');

            $gcm_fecha_hora_gestion = validar_input($_POST['gcm_fecha_hora_gestion'] ?? '');
            $gcm_tipo_monitoreo     = validar_input($_POST['gcm_tipo_monitoreo'] ?? '');
            $gcm_skill_interaccion  = validar_input($_POST['gcm_skill_interaccion'] ?? '');
            $gcm_tipo_gestion       = validar_input($_POST['gcm_tipo_gestion'] ?? '');
            $gcm_segmento           = validar_input($_POST['gcm_segmento'] ?? '');
            $gcm_id_sim             = validar_input($_POST['gcm_id_sim'] ?? '');
            $gcm_id_ani             = validar_input($_POST['gcm_id_ani'] ?? '');

            $estado_old = validar_input($_POST['estado_old'] ?? '');

            // Inicializa arreglos para evitar warnings
            $grupos_items_nota = array();
            $grupos_peso_id = array();
            $item_respuesta = array();
            $item_id_respuesta = array();
            $item_comentario = array();
            $item_calificable_tipo_error = array();
$grupos_tipo_error=array_values(array_unique($tipo_error));

        for ($i=0; $i < count($items_matriz); $i++) { 
            if ($grupo_peso[$i]!="") {
                $grupos_items_nota['G-'.$grupo_peso[$i]]=100;
                $grupos_peso_id[]='G-'.$grupo_peso[$i];
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
            if ($grupo_peso[$i]=="") {
                if ($item_respuesta[$i]=="No") {
                    $item_calificable_tipo_error[$tipo_error[$i]][$items_matriz[$i]]=0;
                } else {
                    $item_calificable_tipo_error[$tipo_error[$i]][$items_matriz[$i]]=$peso_nota[$i];
                }
            } else {
                $item_calificable_tipo_error[$tipo_error[$i]]['G-'.$grupo_peso[$i]]=$peso_nota[$i];
            }

            if ($grupo_peso[$i]!="" and $item_respuesta[$i]=="No") {
                $grupos_items_nota['G-'.$grupo_peso[$i]]=0;
            }
        }

        for ($i=0; $i < count($grupos_peso_id); $i++) { 
            for ($j=0; $j < count($tipo_error); $j++) { 
                if (isset($item_calificable_tipo_error[$tipo_error[$j]][$grupos_peso_id[$i]])) {
                    if ($grupos_items_nota[$grupos_peso_id[$i]]==0) {
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
            if ($gcm_nota_enc>=$peso_sla_enc) {
                $control_estado_enc=1;
            } else {
                $control_estado_enc=0;
            }
        }

        if ($gcm_nota_ecuf==="NA") {
            $control_estado_ecuf=1;
        } else {
            $nota_general+=$gcm_nota_ecuf;
            if ($gcm_nota_ecuf>=$peso_sla_ecuf) {
                $control_estado_ecuf=1;
            } else {
                $control_estado_ecuf=0;
            }
        }

        if ($gcm_nota_ecn==="NA") {
            $control_estado_ecn=1;
        } else {
            $nota_general+=$gcm_nota_ecn;
            if ($gcm_nota_ecn>=$peso_sla_ecn) {
                $control_estado_ecn=1;
            } else {
                $control_estado_ecn=0;
            }
        }

        if ($estado_old=='Aceptado' AND ($control_estado_enc==0 OR $control_estado_ecuf==0 OR $control_estado_ecn==0)) {
            // Prepara la sentencia
            $consulta_actualizar_estado = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo` SET `gcm_estado`='Pendiente' WHERE `gcm_id`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_estado->bind_param('s', $id_registro);

            // Ejecuta sentencia preparada
            $consulta_actualizar_estado->execute();
        }

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo` SET `gcm_observaciones_monitoreo`=?, `gcm_nota_enc`=?, `gcm_nota_ecn`=?, `gcm_nota_ecuf`=?, `gcm_nota_general`=?, `gcm_nota_enc_estado`=?, `gcm_nota_ecn_estado`=?, `gcm_nota_ecuf_estado`=? WHERE `gcm_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssssss', $gcm_observaciones_monitoreo, $gcm_nota_enc, $gcm_nota_ecn, $gcm_nota_ecuf, $nota_general, $control_estado_enc, $control_estado_ecn, $control_estado_ecuf, $id_registro);

        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        if (comprobarSentencia($enlace_db->info)) {

            $control_insert=0;

            for ($i=0; $i < count($items_matriz); $i++) {
                unset($sentencia_insert_calificaciones);

                $item_matriz_pregunta=$items_matriz[$i];
                $respuesta_item=$item_respuesta[$i];
                $comentarios_insert=$item_comentario[$i];

                // Prepara la sentencia
                $sentencia_insert_calificaciones = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo_calificaciones` SET `gcmc_respuesta`=?,`gcmc_comentarios`=? WHERE `gcmc_pregunta`=? AND `gcmc_monitoreo`=?");

                // Agrega variables a sentencia preparada
                $sentencia_insert_calificaciones->bind_param('ssss', $respuesta_item, $comentarios_insert, $item_matriz_pregunta, $id_registro);

                $sentencia_insert_calificaciones->execute();

                if (comprobarSentencia($enlace_db->info)) {
                    $control_insert++;
                }
            }

            if (count($items_matriz)==$control_insert) {
                //insert log eventos
                    $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";

                    $log_modulo=$modulo_plataforma;
                    $log_tipo="editar";
                    $log_accion="Editar registro";
                    $log_detalle="Actualizó evaluación monitoreo [".$id_registro."]";
                    $log_usuario=$_SESSION["usu_id"];

                    $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                    $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                    $consulta_registros_log->execute();
                //insert log eventos
                $respuesta_accion = "<script type='text/javascript'>alertify.success('Monitoreo actualizado exitosamente!', 0);</script>";

                $control_notificar=0;

                if ($control_estado_ecuf!=$ecuf_old) {
                    $control_notificar=1;
                }

                if ($control_estado_ecn!=$ecn_old) {
                    $control_notificar=1;
                }

                if ($control_estado_enc!=$enc_old) {
                    $control_notificar=1;
                }

                $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_analista`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_afectacion_1`, `gcm_fecha_monitoreo`, TM.`gcm_observaciones` FROM `tb_gestion_auditoria_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` WHERE TMC.`gcm_id`=?";

                $consulta_registros = $enlace_db->prepare($consulta_string);
                $consulta_registros->bind_param("s", $id_registro);
                $consulta_registros->execute();
                $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
                if ($control_notificar AND $resultado_registros[0][26]!='No-Cliente') {

                    $consulta_string_supervisor="SELECT TU.`usu_id`, TU.`usu_nombres_apellidos`, TL.`usu_id`, TL.`usu_nombres_apellidos`, TL.`usu_correo_corporativo`, TU.`usu_correo_corporativo` FROM `tb_administrador_usuario` AS TU LEFT JOIN `tb_administrador_usuario` AS TL ON TU.`usu_supervisor`=TL.`usu_id` WHERE TU.`usu_id`=?";

                    $consulta_registros_supervisor = $enlace_db->prepare($consulta_string_supervisor);
                    $consulta_registros_supervisor->bind_param("s", $id_analista);
                    $consulta_registros_supervisor->execute();
                    $resultado_registros_supervisor = $consulta_registros_supervisor->get_result()->fetch_all(MYSQLI_NUM);

                    // PROGRAMAR NOTIFICACIÓN CORREO
                    /*SE DEFINEN DESTINATARIOS*/
                    $array_correo_destino['correos']['TO']=$resultado_registros_supervisor[0][5]."|".$resultado_registros_supervisor[0][1];
                    $array_correo_destino['correos']['CC']=$resultado_registros_supervisor[0][4]."|".$resultado_registros_supervisor[0][3];

                    if ($resultado_registros[0][23]) {
                        $nota_correo_enc='CUMPLE';
                    } else {
                        $nota_correo_enc='INCUMPLE';
                    }

                    if ($resultado_registros[0][25]) {
                        $nota_correo_ecuf='CUMPLE';
                    } else {
                        $nota_correo_ecuf='INCUMPLE';
                    }

                    if ($resultado_registros[0][24]) {
                        $nota_correo_ecn='CUMPLE';
                    } else {
                        $nota_correo_ecn='INCUMPLE';
                    }

                    /*SE ESTRUCTURA COTENIDO DE CORREO*/
                        $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>Cordial Saludo,<br><br>Se ha monitoreado al agente ".$resultado_registros_supervisor[0][1].", con los siguientes resultados. Por favor verificar el detalle del monitoreo ingresando al siguiente link: <a href='http://52.188.206.38/' target='_blank'>ICBF-IQGIS</a></p><br>
                            <center>
                            <table style='width: 500px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Monitoreo</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][0] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Analista</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros_supervisor[0][0] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nombres y Apellidos</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros_supervisor[0][1] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Fecha gestión/grabación</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][4] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Tipo monitoreo</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][6] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Skill interacción</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][7] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Tipo gestión</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][8] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Segmento</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][9] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id SIM</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][10] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id/ANI</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $resultado_registros[0][11] ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nota ENC</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $nota_correo_enc ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nota ECUF</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $nota_correo_ecuf ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nota ECN</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $nota_correo_ecn ."</td>
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
                    $nc_subject="[Actualizado] Monitoreo Calidad | ".$resultado_registros[0][0];
                    $nc_body=str_replace("'", '"', $contenido_correo);
                    $nc_embeddedimage_ruta="/var/www/iqgis/html/images/firma-verde.png";
                    $nc_embeddedimage_nombre="logo_firma_verde";
                    $nc_embeddedimage_tipo="image/png";
                    $nc_intentos="";
                    $nc_eliminar="Si";
                    $nc_estado_envio="Pendiente";
                    $nc_fecha_envio="";
                    $nc_usuario_registro=$_SESSION['usu_id'];

                    // =========================
                    // REMEDIACIÓN SQLi:
                    // INSERT concatenado -> prepared statement
                    // (misma funcionalidad: 5 intentos + break si inserta)
                    // =========================
                    $sql_insert_notif = "INSERT INTO `tb_notificaciones_central`
                        (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`, `nc_subject`, `nc_body`,
                         `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`, `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                    $stmt_notif = $enlace_db->prepare($sql_insert_notif);

                    if ($stmt_notif) {
                        for ($i=0; $i < 5; $i++) {
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

                            if ($stmt_notif->execute()) {
                                registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject);
                                break;
                            }
                        }
                        $stmt_notif->close();
                    }
}
            }

        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el monitoreo, por favor verifique e intente nuevamente!', 0);</script>";
        }
        }
    }

    $consulta_string_monitoreo="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_afectacion_1`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_auditoria_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

    $consulta_registros_monitoreo = $enlace_db->prepare($consulta_string_monitoreo);
    $consulta_registros_monitoreo->bind_param("s", $id_registro);
    $consulta_registros_monitoreo->execute();
    $resultado_registros_monitoreo = $consulta_registros_monitoreo->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_evaluacion="SELECT `gcmc_id`, `gcmc_monitoreo`, `gcmc_pregunta`, `gcmc_respuesta`, `gcmc_afectaciones`, `gcmc_comentarios`, TIM.`gcmi_matriz`, TIM.`gcmi_item_tipo`, TIM.`gcmi_item_consecutivo`, TIM.`gcmi_item_orden`, TIM.`gcmi_descripcion`, TIM.`gcmi_peso`, TIM.`gcmi_calificable` FROM `tb_gestion_auditoria_monitoreo_calificaciones` LEFT JOIN `tb_gestion_calidad_matriz_item` AS TIM ON `tb_gestion_auditoria_monitoreo_calificaciones`.`gcmc_pregunta`=TIM.`gcmi_id` WHERE `gcmc_monitoreo`=? AND TIM.`gcmi_matriz`=? ORDER BY TIM.`gcmi_item_consecutivo` ASC";

    $consulta_registros_evaluacion = $enlace_db->prepare($consulta_string_evaluacion);
    $consulta_registros_evaluacion->bind_param("ss", $id_registro, $resultado_registros_monitoreo[0][20]);
    $consulta_registros_evaluacion->execute();
    $resultado_registros_evaluacion = $consulta_registros_evaluacion->get_result()->fetch_all(MYSQLI_NUM);

    $array_respuestas = array();
    $array_comentarios = array();

    for ($i=0; $i < count($resultado_registros_evaluacion); $i++) { 
        $array_respuestas[$resultado_registros_evaluacion[$i][2]]=$resultado_registros_evaluacion[$i][3];
        $array_comentarios[$resultado_registros_evaluacion[$i][2]]=$resultado_registros_evaluacion[$i][5];
    }

    $consulta_string_matriz="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id`, `gcmi_nota_maxima`, `gcmi_nota_sla` FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_matriz`=? ORDER BY `gcmi_item_consecutivo` ASC";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $resultado_registros_monitoreo[0][20]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_adjuntos="SELECT `gcms_id`, `gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`, `gcms_registro_fecha` FROM `tb_gestion_calidad_monitoreo_soportes` WHERE `gcms_monitoreo`=?";

    $consulta_registros_adjuntos = $enlace_db->prepare($consulta_string_adjuntos);
    $consulta_registros_adjuntos->bind_param("s", $resultado_registros_monitoreo[0][0]);
    $consulta_registros_adjuntos->execute();
    $resultado_registros_adjuntos = $consulta_registros_adjuntos->get_result()->fetch_all(MYSQLI_NUM);

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
        <form name="actualizar_monitoreo" action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <div class="row pt-2" id="tabla_fixed">
            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Consecutivo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][0]); ?></td>
                                <input type="hidden" name="gcm_fecha_hora_gestion" value="<?php echo e($resultado_registros_monitoreo[0][3]); ?>">
                                <input type="hidden" name="gcm_tipo_monitoreo" value="<?php echo e($resultado_registros_monitoreo[0][5]); ?>">
                                <input type="hidden" name="gcm_skill_interaccion" value="<?php echo e($resultado_registros_monitoreo[0][6]); ?>">
                                <input type="hidden" name="gcm_tipo_gestion" value="<?php echo e($resultado_registros_monitoreo[0][7]); ?>">
                                <input type="hidden" name="gcm_segmento" value="<?php echo e($resultado_registros_monitoreo[0][8]); ?>">
                                <input type="hidden" name="gcm_id_sim" value="<?php echo e($resultado_registros_monitoreo[0][9]); ?>">
                                <input type="hidden" name="gcm_id_ani" value="<?php echo e($resultado_registros_monitoreo[0][10]); ?>">
                                <input type="hidden" name="estado_old" value="<?php echo e($resultado_registros_monitoreo[0][15]); ?>">
                                <input type="hidden" name="id_analista" value="<?php echo e($resultado_registros_monitoreo[0][27]); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][1]); ?><br>[<?php echo e($resultado_registros_monitoreo[0][28]); ?>]</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][5]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Skill Interacción</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][6]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][7]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Segmento</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][8]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id SIM</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][9]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id/ANI</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][10]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][3]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][26]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECUF</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][24]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][24]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                                <input type="hidden" name="ecuf_old" value="<?php echo e($resultado_registros_monitoreo[0][24]); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECN</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][23]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][23]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                                <input type="hidden" name="ecn_old" value="<?php echo e($resultado_registros_monitoreo[0][23]); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ENC</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][22]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][22]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                                <input type="hidden" name="enc_old" value="<?php echo e($resultado_registros_monitoreo[0][22]); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota General</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][21]); ?>%</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][11]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                                <td class="align-middle py-0 font-size-11"><?php echo e($resultado_registros_monitoreo[0][16]); ?></td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="fondo-corporativo color-blanco p-1 font-size-11"><span class="fas fa-paperclip"></span> Adjuntos monitoreo</div>
                <div class="font-size-11 p-1">
                    <?php if(count($resultado_registros_adjuntos)>0): ?>
                        <?php for ($i=0; $i < count($resultado_registros_adjuntos); $i++): ?>
                            <a href="<?php echo e(URL.'gestion_calidad/'.$resultado_registros_adjuntos[$i][3]); ?>" target="_blank" title="<?php echo e($resultado_registros_adjuntos[$i][2]); ?>"><span class="fas fa-external-link-alt"></span></a> <?php echo e((strlen($resultado_registros_adjuntos[$i][2])>40)? substr($resultado_registros_adjuntos[$i][2], 0, 40).'...' : $resultado_registros_adjuntos[$i][2]); ?><br>
                        <?php endfor; ?>
                        <p class="alert alert-warning p-1 d-none mb-0" id="titulo_visor"></p>                
                        <embed id="embed1" src="" width="100%" height="500px" class="d-none mt-0 visor_div" type="application/pdf"/>
                    <?php else: ?>
                        <p class="alert alert-warning p-1">
                            <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-8">
                <?php if (count($resultado_registros_matriz)>0): ?>
                    <div class="table-responsive">
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
                                    for ($i=0; $i < count($resultado_registros_matriz); $i++) { 
                                ?>
                                <tr class="<?php if($resultado_registros_matriz[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros_matriz[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros_matriz[$i][2]=='Item'){echo'matriz-item';}?>">
                                    <td class="align-middle">
                                        <?php if($resultado_registros_matriz[$i][2]=='Grupo' AND $resultado_registros_matriz[$i][10]=='ECU'): ?>
                                            <input type="hidden" name="peso_sla_ecuf" value="<?php echo e($resultado_registros_matriz[$i][16]); ?>">
                                        <?php endif; ?>
                                        <?php if($resultado_registros_matriz[$i][2]=='Grupo' AND $resultado_registros_matriz[$i][10]=='ENC'): ?>
                                            <input type="hidden" name="peso_sla_enc" value="<?php echo e($resultado_registros_matriz[$i][16]); ?>">
                                        <?php endif; ?>
                                        <?php if($resultado_registros_matriz[$i][2]=='Grupo' AND $resultado_registros_matriz[$i][10]=='ECN'): ?>
                                            <input type="hidden" name="peso_sla_ecn" value="<?php echo e($resultado_registros_matriz[$i][16]); ?>">
                                        <?php endif; ?>
                                        <?php if($resultado_registros_matriz[$i][7]=="Si"): ?>
                                            <input type="hidden" name="id_campos[]" value="<?php echo e($resultado_registros_matriz[$i][0]); ?>">
                                            <input type="hidden" name="grupo_peso[]" value="<?php echo e($resultado_registros_matriz[$i][8]); ?>">
                                            <input type="hidden" name="peso_nota[]" value="<?php echo e($resultado_registros_matriz[$i][6]); ?>">
                                            <input type="hidden" name="tipo_error[]" value="<?php echo e($resultado_registros_matriz[$i][10]); ?>">
                                        <?php endif; ?>
                                        <?php echo e($resultado_registros_matriz[$i][3]); ?></td>
                                    <td class="align-middle"><?php echo e($resultado_registros_matriz[$i][5]); ?></td>
                                    <td class="align-middle text-center"><?php echo e($resultado_registros_matriz[$i][6]); ?>% <?php if($resultado_registros_matriz[$i][2]=='Grupo') { echo '/ SLA-'.e($resultado_registros_matriz[$i][16]).'%'; } ?></td>
                                    <td class="align-middle text-center align-middle">
                                        <?php if($resultado_registros_matriz[$i][7]=="Si"): ?>
                                        <div class="form-group m-0 p-0">
                                            <div class="form-group custom-control custom-checkbox m-0">
                                                <input type="radio" class="custom-control-input" id="customCheckreqsi<?php echo e($resultado_registros_matriz[$i][0]); ?>" name="respuesta_<?php echo e($resultado_registros_matriz[$i][0]); ?>" value="Si" <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="Si"){ echo "checked"; } ?> onclick="validar_comentario('Si', '<?php echo e($resultado_registros_matriz[$i][0]); ?>');" required>
                                                <label class="custom-control-label p-0 m-0" for="customCheckreqsi<?php echo e($resultado_registros_matriz[$i][0]); ?>"></label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros_matriz[$i][7]=="Si"): ?>
                                        <div class="form-group m-0 p-0">
                                            <div class="form-group custom-control custom-checkbox m-0">
                                                <input type="radio" class="custom-control-input" id="customCheckreqno<?php echo e($resultado_registros_matriz[$i][0]); ?>" name="respuesta_<?php echo e($resultado_registros_matriz[$i][0]); ?>" value="No" <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="No"){ echo "checked"; } ?> onclick="validar_comentario('No', '<?php echo e($resultado_registros_matriz[$i][0]); ?>');" required>
                                                <label class="custom-control-label p-0 m-0" for="customCheckreqno<?php echo e($resultado_registros_matriz[$i][0]); ?>"></label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros_matriz[$i][7]=="Si"): ?>
                                        <input type="text" class="form-control form-control-sm <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="Si"){ echo "d-none"; } ?>" name="comentario_<?php echo e($resultado_registros_matriz[$i][0]); ?>" id="comentario_<?php echo e($resultado_registros_matriz[$i][0]); ?>" value="<?php echo e($array_comentarios[$resultado_registros_matriz[$i][0]]); ?>" maxlength="2000" required <?php if($array_respuestas[$resultado_registros_matriz[$i][0]]=="Si"){ echo "disabled"; } ?>>
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
                    <p class="alert alert-warning p-1">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                    </p>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones"><?php echo e($resultado_registros_monitoreo[0][11]); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                        <button class="btn btn-success float-right ml-1" type="submit" name="actualizar_monitoreo">Guardar</button>
                    <?php if(!isset($_POST["actualizar_monitoreo"])): ?>
                        <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                    <?php endif; ?>
                    <?php if(isset($_POST["actualizar_monitoreo"])): ?>
                        <a href="<?php echo e($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
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
        function validar_comentario(tipo, id_elemento){
            if (tipo=="Si") {
                $("#comentario_"+id_elemento).removeClass('d-block').addClass('d-none');
                document.getElementById("comentario_"+id_elemento).disabled = true;
            } else {
                $("#comentario_"+id_elemento).removeClass('d-none').addClass('d-block');
                document.getElementById("comentario_"+id_elemento).disabled = false;
            }

        }
    </script>
</body>
</html>
