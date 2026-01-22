<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       Helpers (XSS-safe output)
    ========================== */
    if (!function_exists('h')) {
        function h($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Gestión Alertas | Validación N1";

    $pagina = isset($_GET['pagina']) ? validar_input($_GET['pagina']) : '';
    $filtro_permanente = isset($_GET['id']) ? validar_input($_GET['id']) : '';
    $estado = isset($_GET['est']) ? validar_input($_GET['est']) : '';

    $reg_param = isset($_GET['reg']) ? $_GET['reg'] : '';
    $reg_decoded = base64_decode($reg_param, true);
    $id_registro = validar_input($reg_decoded !== false ? $reg_decoded : '');

    if (!isset($_SESSION['gestion_alerta_valida_n1'])) {
        $_SESSION['gestion_alerta_valida_n1'] = 0;
    }

    if(isset($_POST["guardar_registro"])){

        $categoria     = validar_input($_POST['categoria'] ?? '');
        $categoria_old = validar_input($_POST['categoria_old'] ?? '');
        $afecta_lt     = validar_input($_POST['afecta_lt'] ?? '');
        $contexto      = validar_input($_POST['contexto'] ?? '');
        $observaciones = validar_input($_POST['observaciones'] ?? '');

        // Consulta estado anterior (ya venía con prepared)
        $consulta_string_old="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`
        FROM `tb_gestion_alertas`
        LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id`
        LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id`
        LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id`
        WHERE `ga_id`=?";

        $consulta_registros_old = $enlace_db->prepare($consulta_string_old);
        $consulta_registros_old->bind_param("s", $id_registro);
        $consulta_registros_old->execute();
        $resultado_registros_old = $consulta_registros_old->get_result()->fetch_all(MYSQLI_NUM);

        $contexto_old = isset($resultado_registros_old[0][12]) ? $resultado_registros_old[0][12] : '';

        if ($afecta_lt==="Si") {
            $estado_final="Pendiente RN2";
            $fecha_escala=date('Y-m-d H:i:s');
            $fecha_cierre="";
        } elseif ($afecta_lt==="No") {
            $estado_final="Cerrado";
            $fecha_escala="";
            $fecha_cierre=date('Y-m-d H:i:s');
        } else {
            // Si por alguna razón llega vacío, se conserva la lógica de guardado (no cambia diseño)
            $estado_final = "";
            $fecha_escala = "";
            $fecha_cierre = "";
        }

        if($_SESSION['gestion_alerta_valida_n1']!=1){

            // UPDATE principal (ya venía con prepared)
            $consulta_actualizar = $enlace_db->prepare(
                "UPDATE `tb_gestion_alertas`
                 SET `ga_estado`=?,`ga_afecta_linea_tecnica`=?,`ga_fecha_remite_agente_especializado`=?, `ga_fecha_cierre`=?,
                     `ga_revisa_n1_usuario`=?,`ga_revisa_n1_fecha`=?, `ga_observaciones_n1`=?
                 WHERE `ga_id`=?"
            );

            $revisa_n1_fecha = date('Y-m-d H:i:s');
            $usu_id_session  = $_SESSION['usu_id'];

            $consulta_actualizar->bind_param(
                'ssssssss',
                $estado_final,
                $afecta_lt,
                $fecha_escala,
                $fecha_cierre,
                $usu_id_session,
                $revisa_n1_fecha,
                $observaciones,
                $id_registro
            );

            $consulta_actualizar->execute();

            if (comprobarSentencia($enlace_db->info)) {

                // INSERT avance (ya venía con prepared)
                $observaciones_avance="Validación N1: ".$observaciones;

                $sentencia_insert_avance = $enlace_db->prepare(
                    "INSERT INTO `tb_gestion_alertas_avances`(`gaa_consecutivo`, `gaa_avance`, `gaa_registro_usuario`)
                     VALUES (?,?,?)"
                );
                $sentencia_insert_avance->bind_param('sss', $id_registro, $observaciones_avance, $usu_id_session);
                $sentencia_insert_avance->execute();

                /* ==============================
                   REMEDIACIÓN: SQLi en INSERT avance categoria/contexto
                   (Antes concatenaba dentro del SQL)
                ============================== */
                if ($categoria !== $categoria_old) {

                    $consulta_actualizar_categoria = $enlace_db->prepare(
                        "UPDATE `tb_gestion_alertas` SET `ga_categoria_afectacion`=? WHERE `ga_id`=?"
                    );
                    $consulta_actualizar_categoria->bind_param('ss', $categoria, $id_registro);
                    $consulta_actualizar_categoria->execute();

                    if (comprobarSentencia($enlace_db->info)) {
                        $msg_categoria = "Actualización de categoría a: ".$categoria;

                        $sentencia_insert_avance_categoria = $enlace_db->prepare(
                            "INSERT INTO `tb_gestion_alertas_avances`(`gaa_consecutivo`, `gaa_avance`, `gaa_registro_usuario`)
                             VALUES (?,?,?)"
                        );
                        $sentencia_insert_avance_categoria->bind_param('sss', $id_registro, $msg_categoria, $usu_id_session);
                        $sentencia_insert_avance_categoria->execute();
                    }
                }

                if ($contexto !== $contexto_old) {

                    $consulta_actualizar_contexto = $enlace_db->prepare(
                        "UPDATE `tb_gestion_alertas` SET `ga_descripcion_alerta`=? WHERE `ga_id`=?"
                    );
                    $consulta_actualizar_contexto->bind_param('ss', $contexto, $id_registro);
                    $consulta_actualizar_contexto->execute();

                    if (comprobarSentencia($enlace_db->info)) {
                        $msg_contexto = "Actualización de contexto de: [".$contexto_old."] a: [".$contexto."]";

                        $sentencia_insert_avance_contexto = $enlace_db->prepare(
                            "INSERT INTO `tb_gestion_alertas_avances`(`gaa_consecutivo`, `gaa_avance`, `gaa_registro_usuario`)
                             VALUES (?,?,?)"
                        );
                        $sentencia_insert_avance_contexto->bind_param('sss', $id_registro, $msg_contexto, $usu_id_session);
                        $sentencia_insert_avance_contexto->execute();
                    }
                }

                // Datos para notificación (prepared)
                $consulta_string_validar_notificacion="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`, TU.`usu_correo_corporativo`
                FROM `tb_gestion_alertas`
                LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id`
                LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id`
                LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id`
                WHERE `ga_id`=?";

                $consulta_registros_validar_notificacion = $enlace_db->prepare($consulta_string_validar_notificacion);
                $consulta_registros_validar_notificacion->bind_param("s", $id_registro);
                $consulta_registros_validar_notificacion->execute();
                $resultado_registros_validar_notificacion = $consulta_registros_validar_notificacion->get_result()->fetch_all(MYSQLI_NUM);

                // Helpers para valores en HTML email (evita inyección en contenido del correo)
                $sim          = isset($resultado_registros_validar_notificacion[0][1])  ? h($resultado_registros_validar_notificacion[0][1])  : '';
                $encuesta     = isset($resultado_registros_validar_notificacion[0][2])  ? h($resultado_registros_validar_notificacion[0][2])  : '';
                $fecha_alerta = isset($resultado_registros_validar_notificacion[0][4])  ? h($resultado_registros_validar_notificacion[0][4])  : '';
                $fecha_atc    = isset($resultado_registros_validar_notificacion[0][5])  ? h($resultado_registros_validar_notificacion[0][5])  : '';
                $regional     = isset($resultado_registros_validar_notificacion[0][22]) ? h($resultado_registros_validar_notificacion[0][22]) : '';
                $czonal       = isset($resultado_registros_validar_notificacion[0][23]) ? h($resultado_registros_validar_notificacion[0][23]) : '';
                $doc_pet      = isset($resultado_registros_validar_notificacion[0][8])  ? h($resultado_registros_validar_notificacion[0][8])  : '';
                $nom_pet      = isset($resultado_registros_validar_notificacion[0][9])  ? h($resultado_registros_validar_notificacion[0][9])  : '';
                $tel_pet      = isset($resultado_registros_validar_notificacion[0][10]) ? h($resultado_registros_validar_notificacion[0][10]) : '';
                $agente       = isset($resultado_registros_validar_notificacion[0][24]) ? h($resultado_registros_validar_notificacion[0][24]) : '';
                $cat          = isset($resultado_registros_validar_notificacion[0][11]) ? h($resultado_registros_validar_notificacion[0][11]) : '';
                $correo_agente= isset($resultado_registros_validar_notificacion[0][25]) ? $resultado_registros_validar_notificacion[0][25] : '';

                /* ==============================
                   REMEDIACIÓN: SQLi en INSERT notificación (antes mysqli_query concatenado)
                ============================== */
                if ($afecta_lt==="No") {

                    $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'><b>Cordial saludo,</b><br>Por favor revisar la siguiente retroalimentación sobre la alerta que se relaciona a continuación:</p><br>
                    <center>
                    <table style='width: 500px; font-size: 12px; font-family: Lato, Arial, sans-serif;'>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Radicado SIM</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$sim."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Radicado Encuesta</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$encuesta."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Fecha Alerta</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$fecha_alerta."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Fecha Atención al Ciudadano</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$fecha_atc."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Regional</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$regional."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Centro Zonal</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$czonal."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Doc. Peticionario</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$doc_pet."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Nombre Peticionario</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$nom_pet."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Teléfono</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$tel_pet."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Agente</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$agente."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Categoría</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$cat."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Observaciones/comentarios</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".h($observaciones)."</td>
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

                    $consulta_string_destinatarios="SELECT `gad_id`, `gad_filtro`, `gad_correos_to`, `gad_correos_cc`
                                                    FROM `tb_gestion_alertas_destinatarios`
                                                    WHERE `gad_filtro`='noafectalt_nivel1'";
                    $consulta_registros_destinatarios = $enlace_db->prepare($consulta_string_destinatarios);
                    $consulta_registros_destinatarios->execute();
                    $resultado_registros_destinatarios = $consulta_registros_destinatarios->get_result()->fetch_all(MYSQLI_NUM);

                    $destino_to = isset($resultado_registros_destinatarios[0][2]) ? explode(";", $resultado_registros_destinatarios[0][2]) : [];
                    $nc_address = $correo_agente."|".$correo_agente.";";
                    for ($j=0; $j < count($destino_to); $j++) {
                        if ($destino_to[$j]!="") {
                            $nc_address .= $destino_to[$j].'|'.$destino_to[$j].';';
                        }
                    }

                    $destino_cc = isset($resultado_registros_destinatarios[0][3]) ? explode(";", $resultado_registros_destinatarios[0][3]) : [];
                    $nc_cc = '';
                    for ($j=0; $j < count($destino_cc); $j++) {
                        if ($destino_cc[$j]!="") {
                            $nc_cc .= $destino_cc[$j].'|'.$destino_cc[$j].';';
                        }
                    }

                    $nc_id_set_from="2";
                    $nc_bcc="";
                    $nc_subject="Retroalimentación Alerta de N1";
                    $nc_body=str_replace("'", '"', $contenido_correo);
                    $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png";
                    $nc_embeddedimage_nombre="logo_firma_verde";
                    $nc_embeddedimage_tipo="image/png";
                    $nc_adjuntos_nombre="";
                    $nc_adjuntos_ruta="";
                    $nc_intentos="";
                    $nc_eliminar="Si";
                    $nc_estado_envio="Pendiente";
                    $nc_fecha_envio="";
                    $nc_usuario_registro=$usu_id_session;

                    $stmt_notif = $enlace_db->prepare(
                        "INSERT INTO `tb_gestion_alertas_notificacion`
                        (`gan_consecutivo`, `gan_tipo`, `gan_id_set_from`, `gan_address`, `gan_cc`, `gan_bcc`, `gan_subject`, `gan_body`,
                         `gan_embeddedimage_ruta`, `gan_embeddedimage_nombre`, `gan_embeddedimage_tipo`,
                         `gan_adjunto_nombre`, `gan_adjunto_ruta`, `gan_intentos`, `gan_eliminar`, `gan_estado_envio`, `gan_fecha_envio`, `gan_usuario_registro`)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                    );

                    $gan_tipo = 'retroalimentacion_n1';
                    $stmt_notif->bind_param(
                        'ssssssssssssssssss',
                        $id_registro,
                        $gan_tipo,
                        $nc_id_set_from,
                        $nc_address,
                        $nc_cc,
                        $nc_bcc,
                        $nc_subject,
                        $nc_body,
                        $nc_embeddedimage_ruta,
                        $nc_embeddedimage_nombre,
                        $nc_embeddedimage_tipo,
                        $nc_adjuntos_nombre,
                        $nc_adjuntos_ruta,
                        $nc_intentos,
                        $nc_eliminar,
                        $nc_estado_envio,
                        $nc_fecha_envio,
                        $nc_usuario_registro
                    );

                    $ok_notif = $stmt_notif->execute();

                    if ($ok_notif) {
                        registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject.' ['.$id_registro.']');
                    } else {
                        registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', $nc_subject.' ['.$id_registro.']');
                    }

                } elseif ($afecta_lt==="Si") {

                    $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'><b>Cordial saludo,</b><br>Por favor revisar el siguiente escalamiento sobre la alerta que se relaciona a continuación:</p><br>
                    <center>
                    <table style='width: 500px; font-size: 12px; font-family: Lato, Arial, sans-serif;'>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Radicado SIM</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$sim."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Radicado Encuesta</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$encuesta."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Fecha Alerta</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$fecha_alerta."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Fecha Atención al Ciudadano</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$fecha_atc."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Regional</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$regional."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Centro Zonal</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$czonal."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Doc. Peticionario</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$doc_pet."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Nombre Peticionario</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$nom_pet."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Teléfono</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$tel_pet."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Agente</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$agente."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Categoría</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$cat."</td>
                        </tr>
                        <tr>
                            <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Observaciones/comentarios</td>
                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".h($observaciones)."</td>
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

                    $consulta_string_destinatarios="SELECT `gad_id`, `gad_filtro`, `gad_correos_to`, `gad_correos_cc`
                                                    FROM `tb_gestion_alertas_destinatarios`
                                                    WHERE `gad_filtro`='afectalt_nivel1'";
                    $consulta_registros_destinatarios = $enlace_db->prepare($consulta_string_destinatarios);
                    $consulta_registros_destinatarios->execute();
                    $resultado_registros_destinatarios = $consulta_registros_destinatarios->get_result()->fetch_all(MYSQLI_NUM);

                    $destino_to = isset($resultado_registros_destinatarios[0][2]) ? explode(";", $resultado_registros_destinatarios[0][2]) : [];
                    $nc_address = '';
                    for ($j=0; $j < count($destino_to); $j++) {
                        if ($destino_to[$j]!="") {
                            $nc_address .= $destino_to[$j].'|'.$destino_to[$j].';';
                        }
                    }

                    $destino_cc = isset($resultado_registros_destinatarios[0][3]) ? explode(";", $resultado_registros_destinatarios[0][3]) : [];
                    $nc_cc = '';
                    for ($j=0; $j < count($destino_cc); $j++) {
                        if ($destino_cc[$j]!="") {
                            $nc_cc .= $destino_cc[$j].'|'.$destino_cc[$j].';';
                        }
                    }

                    $nc_id_set_from="2";
                    $nc_bcc="mariostiv@hotmail.com|mariostiv@hotmail.com";
                    $nc_subject="Escalamiento Alerta a N2";
                    $nc_body=str_replace("'", '"', $contenido_correo);
                    $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png";
                    $nc_embeddedimage_nombre="logo_firma_verde";
                    $nc_embeddedimage_tipo="image/png";
                    $nc_adjuntos_nombre="";
                    $nc_adjuntos_ruta="";
                    $nc_intentos="";
                    $nc_eliminar="Si";
                    $nc_estado_envio="Pendiente";
                    $nc_fecha_envio="";
                    $nc_usuario_registro=$usu_id_session;

                    $stmt_notif = $enlace_db->prepare(
                        "INSERT INTO `tb_gestion_alertas_notificacion`
                        (`gan_consecutivo`, `gan_tipo`, `gan_id_set_from`, `gan_address`, `gan_cc`, `gan_bcc`, `gan_subject`, `gan_body`,
                         `gan_embeddedimage_ruta`, `gan_embeddedimage_nombre`, `gan_embeddedimage_tipo`,
                         `gan_adjunto_nombre`, `gan_adjunto_ruta`, `gan_intentos`, `gan_eliminar`, `gan_estado_envio`, `gan_fecha_envio`, `gan_usuario_registro`)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                    );

                    // OJO: tu código original guarda gan_tipo = 'retroalimentacion_n1' también para escalamiento.
                    // Se conserva para NO afectar funcionalidad original.
                    $gan_tipo = 'retroalimentacion_n1';

                    $stmt_notif->bind_param(
                        'ssssssssssssssssss',
                        $id_registro,
                        $gan_tipo,
                        $nc_id_set_from,
                        $nc_address,
                        $nc_cc,
                        $nc_bcc,
                        $nc_subject,
                        $nc_body,
                        $nc_embeddedimage_ruta,
                        $nc_embeddedimage_nombre,
                        $nc_embeddedimage_tipo,
                        $nc_adjuntos_nombre,
                        $nc_adjuntos_ruta,
                        $nc_intentos,
                        $nc_eliminar,
                        $nc_estado_envio,
                        $nc_fecha_envio,
                        $nc_usuario_registro
                    );

                    $ok_notif = $stmt_notif->execute();

                    if ($ok_notif) {
                        registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject.' ['.$id_registro.']');
                    } else {
                        registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', $nc_subject.' ['.$id_registro.']');
                    }
                }

                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                $_SESSION['gestion_alerta_valida_n1']=1;

            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }

        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    // Datos registro (ya venía con prepared)
    $consulta_string="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`
    FROM `tb_gestion_alertas`
    LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id`
    LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id`
    LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id`
    WHERE `ga_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    // Encuesta relacionada
    $radicado_sim = isset($resultado_registros[0][1]) ? $resultado_registros[0][1] : '';

    $consulta_string_encuestas="SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_observaciones`, `ger_registro_usuario`, `ger_registro_fecha`, TU.`usu_nombres_apellidos`, `ger_contactado`, `ger_efectivo`
    FROM `tb_gestion_encuesta_registro`
    LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id`
    WHERE `ger_radicado`=? ORDER BY `ger_registro_fecha` DESC LIMIT 0,1";

    $consulta_registros_encuestas = $enlace_db->prepare($consulta_string_encuestas);
    $consulta_registros_encuestas->bind_param("s", $radicado_sim);
    $consulta_registros_encuestas->execute();
    $resultado_registros_encuestas = $consulta_registros_encuestas->get_result()->fetch_all(MYSQLI_NUM);

    $encuesta_id = isset($resultado_registros_encuestas[0][1]) ? $resultado_registros_encuestas[0][1] : '';

    $consulta_string_pregunta="SELECT `gemp_id`
    FROM `tb_gestion_encuestas_matriz_seccion_pregunta`
    LEFT JOIN `tb_gestion_encuestas_matriz_seccion` AS TSEC ON `tb_gestion_encuestas_matriz_seccion_pregunta`.`gemp_seccion`=TSEC.`gems_id`
    WHERE TSEC.`gems_notificacion`='Si'
      AND (`gemp_tipo`='Desplegable' OR `gemp_tipo`='Casillas' OR `gemp_tipo`='Varias opciones')
      AND `gemp_encuesta`=?";

    $resultado_registros_pregunta = [];
    $resultado_registros_categorias = [];

    if ($encuesta_id !== '') {
        $consulta_registros_pregunta = $enlace_db->prepare($consulta_string_pregunta);
        $consulta_registros_pregunta->bind_param("s", $encuesta_id);
        $consulta_registros_pregunta->execute();
        $resultado_registros_pregunta = $consulta_registros_pregunta->get_result()->fetch_all(MYSQLI_NUM);

        $pregunta_id = isset($resultado_registros_pregunta[0][0]) ? $resultado_registros_pregunta[0][0] : '';

        if ($pregunta_id !== '') {
            $consulta_string_categorias="SELECT `gemo_id`, `gemo_opcion_nombre`
            FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion`
            WHERE `gemo_encuesta`=? AND `gemo_pregunta`=?
            ORDER BY `gemo_orden_mostrar` ASC";

            $consulta_registros_categorias = $enlace_db->prepare($consulta_string_categorias);
            $consulta_registros_categorias->bind_param("ss", $encuesta_id, $pregunta_id);
            $consulta_registros_categorias->execute();
            $resultado_registros_categorias = $consulta_registros_categorias->get_result()->fetch_all(MYSQLI_NUM);
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_alertas.php?pagina=".urlencode($pagina)."&id=".urlencode($filtro_permanente)."&est=".urlencode($estado);

?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
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
                                <td class="align-middle"><?php echo isset($resultado_registros[0][0]) ? h($resultado_registros[0][0]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Radicado SIM</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][1]) ? h($resultado_registros[0][1]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left" style="min-width: 100px;">Fecha Alerta</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][4]) ? h($resultado_registros[0][4]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Fecha Atención al Ciudadano</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][5]) ? h($resultado_registros[0][5]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Regional</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][22]) ? h($resultado_registros[0][22]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Centro Zonal</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][23]) ? h($resultado_registros[0][23]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Doc. Peticionario</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][8]) ? h($resultado_registros[0][8]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Nombre Peticionario</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][9]) ? h($resultado_registros[0][9]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Teléfono</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][10]) ? h($resultado_registros[0][10]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Agente</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][24]) ? h($resultado_registros[0][24]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Radicado SIM Encuesta</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][2]) ? h($resultado_registros[0][2]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Categoría</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][11]) ? h($resultado_registros[0][11]) : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Contexto</th>
                                <td class="align-middle"><?php echo isset($resultado_registros[0][12]) ? h($resultado_registros[0][12]) : ''; ?></td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="col-md-4 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <p class="alert alert-warning p-1 mb-1 font-size-11 <?php if($_SESSION['gestion_alerta_valida_n1']!=1) { echo 'd-block'; } else {echo 'd-none'; } ?>" id="div_alerta_encuesta">¡Por favor ingrese a la encuesta para continuar!</p>
                        <p class="alert alert-success p-1 mb-1 d-none font-size-11" id="div_notificaciones"></p>

                        <?php
                            $encuesta_consecutivo = isset($resultado_registros_encuestas[0][0]) ? $resultado_registros_encuestas[0][0] : '';
                            $encuesta_btn_text    = $encuesta_consecutivo !== '' ? $encuesta_consecutivo : 'Ver Encuesta';
                        ?>
                        <a href="#"
                           onClick="open_modal_encuesta('<?php echo h(base64_encode($encuesta_consecutivo)); ?>');"
                           class="btn btn-success mb-1 font-size-11"
                           title="Ver Encuesta">
                           <span class="fas fa-file-alt"></span> <?php echo h($encuesta_btn_text); ?>
                        </a>
                    </div>

                    <div class="col-md-6 <?php if($_SESSION['gestion_alerta_valida_n1']!=1) { echo 'd-none'; } ?>" id="div_categoria">
                        <div class="form-group">
                            <input type="hidden" name="categoria_old" value="<?php echo isset($resultado_registros[0][11]) ? h($resultado_registros[0][11]) : ''; ?>">
                            <label for="categoria">Categoría</label>
                            <select class="form-control form-control-sm" name="categoria" id="categoria" <?php if($_SESSION['gestion_alerta_valida_n1']==1) { echo 'disabled'; } ?> required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_categorias); $i++): ?>
                                <option value="<?php echo h($resultado_registros_categorias[$i][1]); ?>"
                                    <?php if(isset($resultado_registros[0][11]) && $resultado_registros[0][11]==$resultado_registros_categorias[$i][1]){ echo "selected"; } ?>>
                                    <?php echo h($resultado_registros_categorias[$i][1]); ?>
                                </option>
                              <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 <?php if($_SESSION['gestion_alerta_valida_n1']!=1) { echo 'd-none'; } ?>" id="div_afecta_lt">
                        <div class="form-group">
                            <label for="afecta_lt">Afecta línea técnica?</label>
                            <select class="form-control form-control-sm" name="afecta_lt" id="afecta_lt" onClick="validar_notificacion();" <?php if($_SESSION['gestion_alerta_valida_n1']==1) { echo 'disabled'; } ?> required>
                              <option value="">Seleccione</option>
                              <option value="Si" <?php if(isset($_POST["guardar_registro"]) AND $afecta_lt=="Si"){ echo "selected"; } ?>>Si</option>
                              <option value="No" <?php if(isset($_POST["guardar_registro"]) AND $afecta_lt=="No"){ echo "selected"; } ?>>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 <?php if($_SESSION['gestion_alerta_valida_n1']!=1) { echo 'd-none'; } ?>" id="div_contexto">
                        <div class="form-group">
                          <label for="contexto">Contexto</label>
                          <textarea class="form-control form-control-sm" name="contexto" id="contexto" rows="5" <?php if($_SESSION['gestion_alerta_valida_n1']==1) { echo 'readonly'; } ?> required><?php echo isset($resultado_registros[0][12]) ? h($resultado_registros[0][12]) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-12 <?php if($_SESSION['gestion_alerta_valida_n1']!=1) { echo 'd-none'; } ?>" id="div_observaciones">
                        <div class="form-group">
                          <label for="observaciones">Observaciones/comentarios</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="5" <?php if($_SESSION['gestion_alerta_valida_n1']==1) { echo 'readonly'; } ?> required><?php if(isset($_POST["guardar_registro"])){ echo h($observaciones); } ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['gestion_alerta_valida_n1']==1): ?>
                                <a href="<?php echo h($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
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
                $("#div_categoria").removeClass('d-none').addClass('d-block');
                $("#div_afecta_lt").removeClass('d-none').addClass('d-block');
                $("#div_observaciones").removeClass('d-none').addClass('d-block');
                $("#div_contexto").removeClass('d-none').addClass('d-block');
                $("#div_alerta_encuesta").removeClass('d-block').addClass('d-none');
            });
        }

        function close_modal_encuesta() {
            $('.modal-body-solicitud').html('');
        }

        function validar_notificacion() {
            var afecta_lt_opcion = document.getElementById("afecta_lt");
            var afecta_lt = afecta_lt_opcion.options[afecta_lt_opcion.selectedIndex].value;

            if (afecta_lt!="") {
                $("#div_notificaciones").removeClass('d-none').addClass('d-block');
            } else {
                $("#div_notificaciones").removeClass('d-block').addClass('d-none');
            }

            if (afecta_lt=="Si") {
                document.getElementById("div_notificaciones").innerHTML='¡Se escalará alerta a nivel 2 para revisión!';
            } else if (afecta_lt=="No") {
                document.getElementById("div_notificaciones").innerHTML='¡Se enviará notificación de retroalimentación a encuestador y se procederá a cierre de la alerta!';
            }
        }
    </script>
</body>
</html>
