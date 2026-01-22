<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* ========= Helpers de salida segura (XSS) ========= */
    if (!function_exists('h')) {
        function h($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }
    }
    if (!function_exists('h_attr')) {
        function h_attr($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Evaluación - Editar";
    $pagina = validar_input($_GET['pagina'] ?? '');
    $bandeja = validar_input(base64_decode($_GET['bandeja'] ?? '') ?: '');
    $filtro_permanente = validar_input($_GET['id'] ?? '');
    $id_registro = validar_input(base64_decode($_GET['reg'] ?? '') ?: '');

    if(isset($_POST["actualizar_monitoreo"])){

        // Sanitiza / valida entradas (sin cambiar lógica)
        $gcm_observaciones_monitoreo = validar_input($_POST['observaciones'] ?? '');

        $items_matriz    = (isset($_POST['id_campos']) && is_array($_POST['id_campos'])) ? $_POST['id_campos'] : [];
        $grupo_peso      = (isset($_POST['grupo_peso']) && is_array($_POST['grupo_peso'])) ? $_POST['grupo_peso'] : [];
        $peso_nota       = (isset($_POST['peso_nota']) && is_array($_POST['peso_nota'])) ? $_POST['peso_nota'] : [];
        $tipo_error      = (isset($_POST['tipo_error']) && is_array($_POST['tipo_error'])) ? $_POST['tipo_error'] : [];
        $peso_sla_ecuf   = validar_input($_POST['peso_sla_ecuf'] ?? '');
        $peso_sla_enc    = validar_input($_POST['peso_sla_enc'] ?? '');
        $peso_sla_ecn    = validar_input($_POST['peso_sla_ecn'] ?? '');

        $ecuf_old = validar_input($_POST['ecuf_old'] ?? '');
        $ecn_old  = validar_input($_POST['ecn_old'] ?? '');
        $enc_old  = validar_input($_POST['enc_old'] ?? '');
        $id_analista = validar_input($_POST['id_analista'] ?? '');

        // Mantengo estas variables por compatibilidad (aunque no se usen luego)
        $gcm_fecha_hora_gestion = validar_input($_POST['gcm_fecha_hora_gestion'] ?? '');
        $gcm_tipo_monitoreo     = validar_input($_POST['gcm_tipo_monitoreo'] ?? '');
        $gcm_skill_interaccion  = validar_input($_POST['gcm_skill_interaccion'] ?? '');
        $gcm_tipo_gestion       = validar_input($_POST['gcm_tipo_gestion'] ?? '');
        $gcm_segmento           = validar_input($_POST['gcm_segmento'] ?? '');
        $gcm_id_sim             = validar_input($_POST['gcm_id_sim'] ?? '');
        $gcm_id_ani             = validar_input($_POST['gcm_id_ani'] ?? '');

        $estado_old = validar_input($_POST['estado_old'] ?? '');

        $grupos_tipo_error = array_values(array_unique($tipo_error));

        // Inicializaciones para evitar warnings y asegurar consistencia
        $grupos_items_nota = [];
        $grupos_peso_id = [];
        $item_respuesta = [];
        $item_id_respuesta = [];
        $item_comentario = [];
        $item_calificable_tipo_error = [];

        for ($i=0; $i < count($items_matriz); $i++) {
            $grupo_actual = $grupo_peso[$i] ?? '';
            $tipo_error_actual = $tipo_error[$i] ?? '';
            $peso_nota_actual = $peso_nota[$i] ?? 0;

            if ($grupo_actual !== "") {
                $grupos_items_nota['G-'.$grupo_actual] = 100;
                $grupos_peso_id[] = 'G-'.$grupo_actual;
            }

            $id_item = $items_matriz[$i];

            if (isset($_POST['respuesta_'.$id_item])) {
                $resp = validar_input($_POST['respuesta_'.$id_item]);
                $item_respuesta[] = $resp;
                $item_id_respuesta[$id_item] = $resp;
            } else {
                $item_respuesta[] = "";
                $item_id_respuesta[$id_item] = "";
            }

            if (isset($_POST['comentario_'.$id_item])) {
                // comentario puede ser largo; validar_input mantiene tu estándar
                $item_comentario[] = validar_input($_POST['comentario_'.$id_item]);
            } else {
                $item_comentario[] = "";
            }
        }

        $grupos_peso_id = array_values(array_unique($grupos_peso_id));

        for ($i=0; $i < count($items_matriz); $i++) {
            $grupo_actual = $grupo_peso[$i] ?? '';
            $tipo_error_actual = $tipo_error[$i] ?? '';
            $peso_nota_actual = $peso_nota[$i] ?? 0;

            if ($grupo_actual === "") {
                if (($item_respuesta[$i] ?? "") === "No") {
                    $item_calificable_tipo_error[$tipo_error_actual][$items_matriz[$i]] = 0;
                } else {
                    $item_calificable_tipo_error[$tipo_error_actual][$items_matriz[$i]] = $peso_nota_actual;
                }
            } else {
                $item_calificable_tipo_error[$tipo_error_actual]['G-'.$grupo_actual] = $peso_nota_actual;
            }

            if ($grupo_actual !== "" && ($item_respuesta[$i] ?? "") === "No") {
                $grupos_items_nota['G-'.$grupo_actual] = 0;
            }
        }

        for ($i=0; $i < count($grupos_peso_id); $i++) {
            for ($j=0; $j < count($tipo_error); $j++) {
                $te = $tipo_error[$j] ?? '';
                if (isset($item_calificable_tipo_error[$te][$grupos_peso_id[$i]])) {
                    if (($grupos_items_nota[$grupos_peso_id[$i]] ?? 100) == 0) {
                        $item_calificable_tipo_error[$te][$grupos_peso_id[$i]] = 0;
                    }
                }
            }
        }

        if (isset($item_calificable_tipo_error['ENC'])) {
            if (count($item_calificable_tipo_error['ENC'])>0) {
                $gcm_nota_enc = array_sum($item_calificable_tipo_error['ENC']);
            } else {
                $gcm_nota_enc = "NA";
            }
        } else {
            $gcm_nota_enc = "NA";
        }

        if (isset($item_calificable_tipo_error['ECU'])) {
            if (count($item_calificable_tipo_error['ECU'])>0) {
                $gcm_nota_ecuf = array_sum($item_calificable_tipo_error['ECU']);
            } else {
                $gcm_nota_ecuf = "NA";
            }
        } else {
            $gcm_nota_ecuf = "NA";
        }

        if (isset($item_calificable_tipo_error['ECN'])) {
            if (count($item_calificable_tipo_error['ECN'])>0) {
                $gcm_nota_ecn = array_sum($item_calificable_tipo_error['ECN']);
            } else {
                $gcm_nota_ecn = "NA";
            }
        } else {
            $gcm_nota_ecn = "NA";
        }

        $nota_general = 0;

        if ($gcm_nota_enc === "NA") {
            $control_estado_enc = 1;
        } else {
            $nota_general += (float)$gcm_nota_enc;
            $control_estado_enc = ((float)$gcm_nota_enc >= (float)$peso_sla_enc) ? 1 : 0;
        }

        if ($gcm_nota_ecuf === "NA") {
            $control_estado_ecuf = 1;
        } else {
            $nota_general += (float)$gcm_nota_ecuf;
            $control_estado_ecuf = ((float)$gcm_nota_ecuf >= (float)$peso_sla_ecuf) ? 1 : 0;
        }

        if ($gcm_nota_ecn === "NA") {
            $control_estado_ecn = 1;
        } else {
            $nota_general += (float)$gcm_nota_ecn;
            $control_estado_ecn = ((float)$gcm_nota_ecn >= (float)$peso_sla_ecn) ? 1 : 0;
        }

        if ($estado_old=='Aceptado' AND ($control_estado_enc==0 OR $control_estado_ecuf==0 OR $control_estado_ecn==0)) {
            // Prepara la sentencia
            $consulta_actualizar_estado = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo` SET `gcm_estado`='Pendiente' WHERE `gcm_id`=?");
            $consulta_actualizar_estado->bind_param('s', $id_registro);
            $consulta_actualizar_estado->execute();
        }

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo` SET `gcm_observaciones_monitoreo`=?, `gcm_nota_enc`=?, `gcm_nota_ecn`=?, `gcm_nota_ecuf`=?, `gcm_nota_general`=?, `gcm_nota_enc_estado`=?, `gcm_nota_ecn_estado`=?, `gcm_nota_ecuf_estado`=? WHERE `gcm_id`=?");
        $consulta_actualizar->bind_param('sssssssss', $gcm_observaciones_monitoreo, $gcm_nota_enc, $gcm_nota_ecn, $gcm_nota_ecuf, $nota_general, $control_estado_enc, $control_estado_ecn, $control_estado_ecuf, $id_registro);
        $consulta_actualizar->execute();

        if (comprobarSentencia($enlace_db->info)) {

            $control_insert=0;

            for ($i=0; $i < count($items_matriz); $i++) {
                unset($sentencia_insert_calificaciones);

                $item_matriz_pregunta = $items_matriz[$i];
                $respuesta_item = $item_respuesta[$i] ?? '';
                $comentarios_insert = $item_comentario[$i] ?? '';

                $sentencia_insert_calificaciones = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo_calificaciones` SET `gcmc_respuesta`=?,`gcmc_comentarios`=? WHERE `gcmc_pregunta`=? AND `gcmc_monitoreo`=?");
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

                if ($control_estado_ecuf!=$ecuf_old) { $control_notificar=1; }
                if ($control_estado_ecn!=$ecn_old)  { $control_notificar=1; }
                if ($control_estado_enc!=$enc_old)  { $control_notificar=1; }

                $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_analista`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TM.`gcm_observaciones` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` WHERE TMC.`gcm_id`=?";

                $consulta_registros = $enlace_db->prepare($consulta_string);
                $consulta_registros->bind_param("s", $id_registro);
                $consulta_registros->execute();
                $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

                if ($control_notificar && isset($resultado_registros[0][26]) && $resultado_registros[0][26] != 'No-Cliente') {

                    $consulta_string_supervisor="SELECT TU.`usu_id`, TU.`usu_nombres_apellidos`, TL.`usu_id`, TL.`usu_nombres_apellidos`, TL.`usu_correo_corporativo`, TU.`usu_correo_corporativo` FROM `tb_administrador_usuario` AS TU LEFT JOIN `tb_administrador_usuario` AS TL ON TU.`usu_supervisor`=TL.`usu_id` WHERE TU.`usu_id`=?";

                    $consulta_registros_supervisor = $enlace_db->prepare($consulta_string_supervisor);
                    $consulta_registros_supervisor->bind_param("s", $id_analista);
                    $consulta_registros_supervisor->execute();
                    $resultado_registros_supervisor = $consulta_registros_supervisor->get_result()->fetch_all(MYSQLI_NUM);

                    /*SE DEFINEN DESTINATARIOS*/
                    $array_correo_destino['correos']['TO'] = ($resultado_registros_supervisor[0][5] ?? '')."|".($resultado_registros_supervisor[0][1] ?? '');
                    $array_correo_destino['correos']['CC'] = ($resultado_registros_supervisor[0][4] ?? '')."|".($resultado_registros_supervisor[0][3] ?? '');

                    if (!empty($resultado_registros[0][23])) { $nota_correo_enc='CUMPLE'; } else { $nota_correo_enc='INCUMPLE'; }
                    if (!empty($resultado_registros[0][25])) { $nota_correo_ecuf='CUMPLE'; } else { $nota_correo_ecuf='INCUMPLE'; }
                    if (!empty($resultado_registros[0][24])) { $nota_correo_ecn='CUMPLE'; } else { $nota_correo_ecn='INCUMPLE'; }

                    /*SE ESTRUCTURA CONTENIDO DE CORREO (se mantiene igual)*/
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
                    $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png";
                    $nc_embeddedimage_nombre="logo_firma_verde";
                    $nc_embeddedimage_tipo="image/png";
                    $nc_intentos="";
                    $nc_eliminar="Si";
                    $nc_estado_envio="Pendiente";
                    $nc_fecha_envio="";
                    $nc_usuario_registro=$_SESSION['usu_id'];

                    /* ========= Remediación SQLi: INSERT con prepared statement ========= */
                    $stmt_notif = $enlace_db->prepare("INSERT INTO `tb_notificaciones_central`(
                        `nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                        `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                        `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`
                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                    if ($stmt_notif) {
                        $stmt_notif->bind_param(
                            "sssssssssssssssss",
                            $nc_id_modulo, $nc_prioridad, $nc_id_set_from, $nc_address, $nc_cc, $nc_bcc, $nc_reply_to,
                            $nc_subject, $nc_body, $nc_embeddedimage_ruta, $nc_embeddedimage_nombre, $nc_embeddedimage_tipo,
                            $nc_intentos, $nc_eliminar, $nc_estado_envio, $nc_fecha_envio, $nc_usuario_registro
                        );

                        for ($i=0; $i < 5; $i++) {
                            $ok = $stmt_notif->execute();
                            if ($ok) {
                                registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject);
                                break;
                            }
                        }
                        $stmt_notif->close();
                    } else {
                        // Si por alguna razón no se puede preparar, se conserva el flujo sin romper la pantalla
                        // (No se hace fallback inseguro para evitar SQLi)
                    }
                }
            }

        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el monitoreo, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string_monitoreo="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

    $consulta_registros_monitoreo = $enlace_db->prepare($consulta_string_monitoreo);
    $consulta_registros_monitoreo->bind_param("s", $id_registro);
    $consulta_registros_monitoreo->execute();
    $resultado_registros_monitoreo = $consulta_registros_monitoreo->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_evaluacion="SELECT `gcmc_id`, `gcmc_monitoreo`, `gcmc_pregunta`, `gcmc_respuesta`, `gcmc_afectaciones`, `gcmc_comentarios`, TIM.`gcmi_matriz`, TIM.`gcmi_item_tipo`, TIM.`gcmi_item_consecutivo`, TIM.`gcmi_item_orden`, TIM.`gcmi_descripcion`, TIM.`gcmi_peso`, TIM.`gcmi_calificable` FROM `tb_gestion_calidad_monitoreo_calificaciones` LEFT JOIN `tb_gestion_calidad_matriz_item` AS TIM ON `tb_gestion_calidad_monitoreo_calificaciones`.`gcmc_pregunta`=TIM.`gcmi_id` WHERE `gcmc_monitoreo`=? AND TIM.`gcmi_matriz`=? ORDER BY TIM.`gcmi_item_consecutivo` ASC";

    $consulta_registros_evaluacion = $enlace_db->prepare($consulta_string_evaluacion);
    $consulta_registros_evaluacion->bind_param("ss", $id_registro, $resultado_registros_monitoreo[0][20]);
    $consulta_registros_evaluacion->execute();
    $resultado_registros_evaluacion = $consulta_registros_evaluacion->get_result()->fetch_all(MYSQLI_NUM);

    $array_respuestas = [];
    $array_comentarios = [];

    for ($i=0; $i < count($resultado_registros_evaluacion); $i++) {
        $array_respuestas[$resultado_registros_evaluacion[$i][2]]  = $resultado_registros_evaluacion[$i][3];
        $array_comentarios[$resultado_registros_evaluacion[$i][2]] = $resultado_registros_evaluacion[$i][5];
    }

    $consulta_string_matriz="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id`, `gcmi_nota_maxima`, `gcmi_nota_sla` FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_matriz`=? ORDER BY `gcmi_item_consecutivo` ASC";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $resultado_registros_monitoreo[0][20]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

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
        <div class="row pt-2" id="tabla_fixed">
            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Consecutivo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][0] ?? ''); ?></td>
                                <input type="hidden" name="gcm_fecha_hora_gestion" value="<?php echo h_attr($resultado_registros_monitoreo[0][3] ?? ''); ?>">
                                <input type="hidden" name="gcm_tipo_monitoreo" value="<?php echo h_attr($resultado_registros_monitoreo[0][5] ?? ''); ?>">
                                <input type="hidden" name="gcm_skill_interaccion" value="<?php echo h_attr($resultado_registros_monitoreo[0][6] ?? ''); ?>">
                                <input type="hidden" name="gcm_tipo_gestion" value="<?php echo h_attr($resultado_registros_monitoreo[0][7] ?? ''); ?>">
                                <input type="hidden" name="gcm_segmento" value="<?php echo h_attr($resultado_registros_monitoreo[0][8] ?? ''); ?>">
                                <input type="hidden" name="gcm_id_sim" value="<?php echo h_attr($resultado_registros_monitoreo[0][9] ?? ''); ?>">
                                <input type="hidden" name="gcm_id_ani" value="<?php echo h_attr($resultado_registros_monitoreo[0][10] ?? ''); ?>">
                                <input type="hidden" name="estado_old" value="<?php echo h_attr($resultado_registros_monitoreo[0][15] ?? ''); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][1] ?? ''); ?><br>[<?php echo h($resultado_registros_monitoreo[0][28] ?? ''); ?>]</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Analista</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][2] ?? ''); ?></td>
                                <input type="hidden" name="id_analista" value="<?php echo h_attr($resultado_registros_monitoreo[0][27] ?? ''); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Responsable</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][29] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][5] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Skill Interacción</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][6] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][7] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Segmento</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][8] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id SIM</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][9] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id/ANI</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][10] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][3] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][26] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECUF</th>
                                <td class="align-middle py-0 font-size-11 <?php if(!empty($resultado_registros_monitoreo[0][24])){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if(!empty($resultado_registros_monitoreo[0][24])){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                                <input type="hidden" name="ecuf_old" value="<?php echo h_attr($resultado_registros_monitoreo[0][24] ?? ''); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECN</th>
                                <td class="align-middle py-0 font-size-11 <?php if(!empty($resultado_registros_monitoreo[0][23])){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if(!empty($resultado_registros_monitoreo[0][23])){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                                <input type="hidden" name="ecn_old" value="<?php echo h_attr($resultado_registros_monitoreo[0][23] ?? ''); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ENC</th>
                                <td class="align-middle py-0 font-size-11 <?php if(!empty($resultado_registros_monitoreo[0][22])){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if(!empty($resultado_registros_monitoreo[0][22])){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                                <input type="hidden" name="enc_old" value="<?php echo h_attr($resultado_registros_monitoreo[0][22] ?? ''); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota General</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][21] ?? ''); ?>%</td>
                            </tr>
                            <?php if ($perfil_modulo!='Cliente'): ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Indicador</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][25] ?? ''); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][11] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][16] ?? ''); ?></td>
                            </tr>
                        </thead>
                    </table>
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
                                <?php for ($i=0; $i < count($resultado_registros_matriz); $i++): ?>
                                <?php
                                    $id_item = $resultado_registros_matriz[$i][0];
                                    $tipo_item = $resultado_registros_matriz[$i][2];
                                    $calificable = $resultado_registros_matriz[$i][7];
                                    $tipo_err = $resultado_registros_matriz[$i][10];
                                    $respuesta_actual = $array_respuestas[$id_item] ?? '';
                                    $comentario_actual = $array_comentarios[$id_item] ?? '';
                                ?>
                                <tr class="<?php if($tipo_item=='Grupo'){echo'matriz-grupo';} elseif($tipo_item=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($tipo_item=='Item'){echo'matriz-item';}?>">
                                    <td class="align-middle">
                                        <?php if($tipo_item=='Grupo' AND $tipo_err=='ECU'): ?>
                                            <input type="hidden" name="peso_sla_ecuf" value="<?php echo h_attr($resultado_registros_matriz[$i][16]); ?>">
                                        <?php endif; ?>
                                        <?php if($tipo_item=='Grupo' AND $tipo_err=='ENC'): ?>
                                            <input type="hidden" name="peso_sla_enc" value="<?php echo h_attr($resultado_registros_matriz[$i][16]); ?>">
                                        <?php endif; ?>
                                        <?php if($tipo_item=='Grupo' AND $tipo_err=='ECN'): ?>
                                            <input type="hidden" name="peso_sla_ecn" value="<?php echo h_attr($resultado_registros_matriz[$i][16]); ?>">
                                        <?php endif; ?>

                                        <?php if($calificable=="Si"): ?>
                                            <input type="hidden" name="id_campos[]" value="<?php echo h_attr($id_item); ?>">
                                            <input type="hidden" name="grupo_peso[]" value="<?php echo h_attr($resultado_registros_matriz[$i][8]); ?>">
                                            <input type="hidden" name="peso_nota[]" value="<?php echo h_attr($resultado_registros_matriz[$i][6]); ?>">
                                            <input type="hidden" name="tipo_error[]" value="<?php echo h_attr($tipo_err); ?>">
                                        <?php endif; ?>

                                        <?php echo h($resultado_registros_matriz[$i][3]); ?>
                                    </td>
                                    <td class="align-middle"><?php echo h($resultado_registros_matriz[$i][5]); ?></td>
                                    <td class="align-middle text-center"><?php echo h($resultado_registros_matriz[$i][6]); ?>% <?php if($tipo_item=='Grupo') { echo '/ SLA-'.h($resultado_registros_matriz[$i][16]).'%'; } ?></td>
                                    <td class="align-middle text-center align-middle">
                                        <?php if($calificable=="Si"): ?>
                                        <div class="form-group m-0 p-0">
                                            <div class="form-group custom-control custom-checkbox m-0">
                                                <input type="radio" class="custom-control-input"
                                                       id="customCheckreqsi<?php echo h_attr($id_item); ?>"
                                                       name="respuesta_<?php echo h_attr($id_item); ?>"
                                                       value="Si"
                                                       <?php if($respuesta_actual=="Si"){ echo "checked"; } ?>
                                                       onclick="validar_comentario('Si', <?php echo json_encode((string)$id_item); ?>);"
                                                       required>
                                                <label class="custom-control-label p-0 m-0" for="customCheckreqsi<?php echo h_attr($id_item); ?>"></label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($calificable=="Si"): ?>
                                        <div class="form-group m-0 p-0">
                                            <div class="form-group custom-control custom-checkbox m-0">
                                                <input type="radio" class="custom-control-input"
                                                       id="customCheckreqno<?php echo h_attr($id_item); ?>"
                                                       name="respuesta_<?php echo h_attr($id_item); ?>"
                                                       value="No"
                                                       <?php if($respuesta_actual=="No"){ echo "checked"; } ?>
                                                       onclick="validar_comentario('No', <?php echo json_encode((string)$id_item); ?>);"
                                                       required>
                                                <label class="custom-control-label p-0 m-0" for="customCheckreqno<?php echo h_attr($id_item); ?>"></label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($calificable=="Si"): ?>
                                        <input type="text"
                                               class="form-control form-control-sm <?php if($respuesta_actual=="Si"){ echo "d-none"; } ?>"
                                               name="comentario_<?php echo h_attr($id_item); ?>"
                                               id="comentario_<?php echo h_attr($id_item); ?>"
                                               value="<?php echo h_attr($comentario_actual); ?>"
                                               maxlength="2000"
                                               required
                                               <?php if($respuesta_actual=="Si"){ echo "disabled"; } ?>>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endfor; ?>
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
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones"><?php echo h($resultado_registros_monitoreo[0][11] ?? ''); ?></textarea>
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
                        <a href="<?php echo h_attr($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
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
