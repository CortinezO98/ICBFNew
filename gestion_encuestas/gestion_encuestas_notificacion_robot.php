<?php
    /**
     * ROBOT DE NOTIFICACIONES - ENCUESTAS
     * Remediaciones aplicadas (sin cambiar funcionalidad):
     * - SQLi: consultas dinámicas pasadas a prepared statements
     * - Validación de emails antes de addAddress/addCC/addBCC
     * - Hardening de rutas de embedded images (evita path traversal)
     * - Sanitización/escape de valores insertados en HTML del correo (evita inyección HTML)
     * - Manejo de errores: no exponer errores en pantalla (log interno)
     */

    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/php_error_gestion_encuestas_notificacion_robot.log');

    require_once("/var/www/html/config/conexion_db.php");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require '/var/www/html/PHPMailer-master/src/Exception.php';
    require '/var/www/html/PHPMailer-master/src/PHPMailer.php';
    require '/var/www/html/PHPMailer-master/src/SMTP.php';

    /* =========================
       Helpers de seguridad (local)
       ========================= */
    if (!function_exists('e_mail_html')) {
        function e_mail_html($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    if (!function_exists('strip_crlf')) {
        function strip_crlf(string $v): string {
            return preg_replace("/[\r\n]+/", ' ', $v);
        }
    }

    if (!function_exists('safe_realpath_under')) {
        /**
         * Retorna realpath si existe y está dentro de alguno de los base dirs permitidos.
         */
        function safe_realpath_under(string $path, array $allowedBases): string {
            $path = trim($path);
            if ($path === '') return '';

            $rp = realpath($path);
            if ($rp === false) return '';

            foreach ($allowedBases as $base) {
                $baseRp = realpath($base);
                if ($baseRp === false) continue;

                $baseRp = rtrim($baseRp, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                $rpCheck = rtrim($rp, DIRECTORY_SEPARATOR);

                if (strpos($rpCheck, $baseRp) === 0) {
                    return $rp;
                }
            }
            return '';
        }
    }

    if (!function_exists('add_recipients_from_string')) {
        /**
         * Formato esperado: "correo|nombre;correo2|nombre2;"
         */
        function add_recipients_from_string(PHPMailer $mail, string $raw, string $type = 'to'): void {
            $items = explode(';', (string)$raw);
            for ($i = 0; $i < count($items); $i++) {
                $part = trim($items[$i]);
                if ($part === '') continue;

                $pair = explode('|', $part, 2);
                $email = trim($pair[0] ?? '');
                $name  = trim($pair[1] ?? $email);

                if ($email === '') continue;
                if (!PHPMailer::validateAddress($email)) continue;

                $name = strip_crlf($name);

                try {
                    if ($type === 'cc') {
                        $mail->addCC($email, $name);
                    } elseif ($type === 'bcc') {
                        $mail->addBCC($email, $name);
                    } else {
                        $mail->addAddress($email, $name);
                    }
                } catch (\Throwable $e) {
                    // No detenemos el envío por un destinatario inválido
                    error_log("Destinatario inválido ({$type}): ".$e->getMessage());
                }
            }
        }
    }

    /* =========================
       1) Consultar notificaciones pendientes (REMEDIADO: prepared)
       ========================= */
    $sql_notif = "
        SELECT
            `gern_id`, `gern_encuesta`, `gern_seccion`, `gern_id_set_from`,
            `gern_address`, `gern_cc`, `gern_bcc`, `gern_subject`, `gern_body`,
            `gern_embeddedimage_ruta`, `gern_embeddedimage_nombre`, `gern_embeddedimage_tipo`,
            `gern_intentos`, `gern_eliminar`, `gern_estado_envio`, `gern_fecha_envio`,
            `gern_fecha_registro`, `gern_usuario_registro`,
            TU.`usu_nombres_apellidos`,
            TN.`ncr_host`, TN.`ncr_port`, TN.`ncr_smtpsecure`, TN.`ncr_smtpauth`,
            TN.`ncr_username`, TN.`ncr_password`, TN.`ncr_setfrom`, TN.`ncr_setfrom_name`,
            TE.`ger_encuesta_id`, TE.`ger_radicado`,
            TR.`gera_matriz_id`, TR.`gera_fecha_peticion`, TR.`gera_peticionario_id`,
            TR.`gera_peticionario_nombre`, TR.`gera_tel_fijo`, TR.`gera_tel_movil`,
            TR.`gera_genero`, TR.`gera_edad`, TR.`gera_motivo`, TR.`gera_regional`,
            TR.`gera_centro_zonal`, TR.`gera_auxiliar1`, TR.`gera_auxiliar2`, TR.`gera_auxiliar3`,
            TR.`gera_estado_gestion`, TR.`gera_intentos`, TR.`gera_contactado`, TR.`gera_efectivo`,
            TR.`gera_ultima_observacion`, TR.`gera_fecha_actualizacion`, TR.`gera_usuario_gestion`,
            TR.`gera_registro_fecha`,
            TM.`gem_nombre_encuesta`,
            TREG.`gere_regional`, TCZ.`gercz_centro_zonal`,
            `gern_seccion_complemento`
        FROM `tb_gestion_encuesta_registro_notificacion`
        LEFT JOIN `tb_gestion_encuesta_registro` AS TE
            ON `tb_gestion_encuesta_registro_notificacion`.`gern_encuesta`=TE.`ger_consecutivo`
        LEFT JOIN `tb_gestion_encuesta_radicado` AS TR
            ON TE.`ger_radicado`=TR.`gera_radicado`
        LEFT JOIN `tb_administrador_usuario` AS TU
            ON `tb_gestion_encuesta_registro_notificacion`.`gern_usuario_registro`=TU.`usu_id`
        LEFT JOIN `tb_notificaciones_central_remitente` AS TN
            ON `tb_gestion_encuesta_registro_notificacion`.`gern_id_set_from`=TN.`ncr_id`
        LEFT JOIN `tb_gestion_encuestas_matriz` AS TM
            ON TR.`gera_matriz_id`=TM.`gem_id`
        LEFT JOIN `tb_gestion_encuesta_regional` AS TREG
            ON TR.`gera_regional`=TREG.`gere_id`
        LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ
            ON TR.`gera_centro_zonal`=TCZ.`gercz_id`
        WHERE `gern_estado_envio`=?
        LIMIT 3 OFFSET 0
    ";

    $pendiente = 'Pendiente';
    $stmt_notif = $enlace_db->prepare($sql_notif);
    if (!$stmt_notif) {
        error_log("Prepare notificaciones falló: ".$enlace_db->error);
        exit;
    }
    $stmt_notif->bind_param("s", $pendiente);
    $stmt_notif->execute();
    $resultado_notificaciones = $stmt_notif->get_result()->fetch_all(MYSQLI_NUM);
    $stmt_notif->close();

    if (count($resultado_notificaciones) <= 0) {
        exit;
    }

    /* =========================
       2) Procesar cada notificación
       ========================= */
    for ($i=0; $i < count($resultado_notificaciones); $i++) {

        $marca_temporal = date("Y-m-d H:i:s");
        $id_correo      = (string)($resultado_notificaciones[$i][0] ?? '');

        $gern_encuesta  = (string)($resultado_notificaciones[$i][1] ?? '');
        $gern_seccion   = (string)($resultado_notificaciones[$i][2] ?? '');
        $id_set_from    = (string)($resultado_notificaciones[$i][3] ?? '');

        // Validación mínima de estructura
        if ($gern_encuesta === '' || $gern_seccion === '' || $id_set_from === '') {
            $sql_up_err = "UPDATE `tb_gestion_encuesta_registro_notificacion`
                           SET `gern_estado_envio`=?, `gern_fecha_envio`=?, `gern_intentos`=?
                           WHERE `gern_id`=?";
            $stmt_up_err = $enlace_db->prepare($sql_up_err);
            if ($stmt_up_err) {
                $estado = 'Error-estructura';
                $intentos = '1';
                $stmt_up_err->bind_param("ssss", $estado, $marca_temporal, $intentos, $id_correo);
                $stmt_up_err->execute();
                $stmt_up_err->close();
            }
            continue;
        }

        $gern_seccion_complemento = (string)($resultado_notificaciones[$i][54] ?? '');

        /* =========================
           2.1) Consultar data de sección/preguntas (REMEDIADO: prepared + IN)
           ========================= */
        $secciones = [$gern_seccion];
        if ($gern_seccion_complemento !== '') {
            $secciones[] = $gern_seccion_complemento;
        }

        // Construye placeholders IN (?, ?, ...)
        $placeholders = implode(',', array_fill(0, count($secciones), '?'));

        $sql_seccion = "
            SELECT
                `gerd_encuesta`, `gerd_seccion`, TSEC.`gems_nombre_seccion`, TSEC.`gems_descripcion`,
                `gerd_pregunta`, TPRE.`gemp_pregunta_nombre`, TPRE.`gemp_pregunta_descripcion`,
                TPRE.`gemp_tipo`, `gerd_respuesta`, TOPC.`gemo_opcion_nombre`
            FROM `tb_gestion_encuesta_registro_data`
            LEFT JOIN `tb_gestion_encuestas_matriz_seccion` AS TSEC
                ON `tb_gestion_encuesta_registro_data`.`gerd_seccion`=TSEC.`gems_id`
            LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta` AS TPRE
                ON `tb_gestion_encuesta_registro_data`.`gerd_pregunta`=TPRE.`gemp_id`
            LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` AS TOPC
                ON `tb_gestion_encuesta_registro_data`.`gerd_respuesta`=TOPC.`gemo_id`
            WHERE `gerd_encuesta`=? AND `gerd_seccion` IN ($placeholders)
        ";

        $stmt_seccion = $enlace_db->prepare($sql_seccion);
        if (!$stmt_seccion) {
            error_log("Prepare sección falló: ".$enlace_db->error);

            // Marcar como Error-estructura
            $sql_up_err = "UPDATE `tb_gestion_encuesta_registro_notificacion`
                           SET `gern_estado_envio`=?, `gern_fecha_envio`=?, `gern_intentos`=?
                           WHERE `gern_id`=?";
            $stmt_up_err = $enlace_db->prepare($sql_up_err);
            if ($stmt_up_err) {
                $estado = 'Error-estructura';
                $intentos = '1';
                $stmt_up_err->bind_param("ssss", $estado, $marca_temporal, $intentos, $id_correo);
                $stmt_up_err->execute();
                $stmt_up_err->close();
            }
            continue;
        }

        // Bind dinámico: encuesta + secciones
        $types = 's' . str_repeat('s', count($secciones));
        $params = array_merge([$gern_encuesta], $secciones);

        // bind_param requiere referencias
        $bindParams = [];
        $bindParams[] = $types;
        for ($p=0; $p<count($params); $p++) {
            $bindParams[] = &$params[$p];
        }
        call_user_func_array([$stmt_seccion, 'bind_param'], $bindParams);

        $stmt_seccion->execute();
        $resultado_seccion = $stmt_seccion->get_result()->fetch_all(MYSQLI_NUM);
        $stmt_seccion->close();

        /* =========================
           2.2) Construir contenido encuesta (REMEDIADO: escape HTML)
           ========================= */
        $contenido_encuesta = "";
        $id_sim_generado    = "";
        $descripcion_alerta = "";
        $categoria_alerta   = "";

        for ($k=0; $k < count($resultado_seccion); $k++) {

            $pregunta_nombre = (string)($resultado_seccion[$k][5] ?? '');
            $preg_desc       = (string)($resultado_seccion[$k][6] ?? '');
            $tipo            = (string)($resultado_seccion[$k][7] ?? '');
            $respuesta_raw   = (string)($resultado_seccion[$k][8] ?? '');
            $opcion_nombre   = (string)($resultado_seccion[$k][9] ?? '');

            $texto_descripcion_pregunta = ($preg_desc !== '') ? " [".e_mail_html($preg_desc)."]" : "";

            if ($tipo === "Varias opciones" || $tipo === "Casillas" || $tipo === "Desplegable") {
                $texto_respuesta = $opcion_nombre;
                $categoria_alerta = $opcion_nombre;
            } else {
                $texto_respuesta = $respuesta_raw;
                $descripcion_alerta .= " ".$respuesta_raw;
            }

            $contenido_encuesta .= "<tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>".e_mail_html($pregunta_nombre).$texto_descripcion_pregunta."</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($texto_respuesta)."</td>
                </tr>";

            // Mantengo tu lógica (k==2)
            if ($k == 2) {
                $id_sim_generado = $texto_respuesta;
            }
        }

        /* =========================
           2.3) Estructurar correo (REMEDIADO: escape HTML)
           ========================= */
        $radicado = (string)($resultado_notificaciones[$i][28] ?? '');
        $radicado_clean = strip_crlf($radicado);

        $campania         = (string)($resultado_notificaciones[$i][51] ?? '');
        $fecha_alerta_db  = (string)($resultado_notificaciones[$i][16] ?? '');
        $fecha_atencion   = (string)($resultado_notificaciones[$i][30] ?? '');
        $regional_nombre  = (string)($resultado_notificaciones[$i][52] ?? '');
        $cz_nombre        = (string)($resultado_notificaciones[$i][53] ?? '');
        $pet_id           = (string)($resultado_notificaciones[$i][31] ?? '');
        $pet_nombre       = (string)($resultado_notificaciones[$i][32] ?? '');
        $tel_fijo         = (string)($resultado_notificaciones[$i][33] ?? '');
        $tel_movil        = (string)($resultado_notificaciones[$i][34] ?? '');
        $agente_nombre    = (string)($resultado_notificaciones[$i][18] ?? '');

        $contenido_correo = "<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'><b>¡Alerta!</b> se requiere revisar el siguiente hallazgo asociado al Radicado: <b>".e_mail_html($radicado_clean)."</b></p><br>
            <center>
            <table style='width: 500px; font-size: 12px; font-family: Lato, Arial, sans-serif;'>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Campaña</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($campania)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Fecha Alerta</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($fecha_alerta_db)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Fecha Atención al Ciudadano</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($fecha_atencion)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Regional</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($regional_nombre)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Centro Zonal</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($cz_nombre)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Identificación Peticionario</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($pet_id)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Nombre Peticionario</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($pet_nombre)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Teléfono</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($tel_fijo)." - ".e_mail_html($tel_movil)."</td>
                </tr>
                <tr>
                    <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px;'>Agente</td>
                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".e_mail_html($agente_nombre)."</td>
                </tr>
                ".$contenido_encuesta."
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

        /* =========================
           2.4) Configurar PHPMailer (validaciones)
           ========================= */
        $smtp_host       = (string)($resultado_notificaciones[$i][19] ?? '');
        $smtp_port       = (int)($resultado_notificaciones[$i][20] ?? 0);
        $smtp_secure     = (string)($resultado_notificaciones[$i][21] ?? '');
        $smtp_auth       = (string)($resultado_notificaciones[$i][22] ?? '');
        $smtp_user       = (string)($resultado_notificaciones[$i][23] ?? '');
        $smtp_pass       = (string)($resultado_notificaciones[$i][24] ?? '');
        $smtp_setfrom    = (string)($resultado_notificaciones[$i][25] ?? '');
        $smtp_setfrom_nm = (string)($resultado_notificaciones[$i][26] ?? '');

        $num_intentos = (int)($resultado_notificaciones[$i][12] ?? 0) + 1;
        $estado_error = ($num_intentos >= 2) ? "Error" : "Pendiente";

        // Si faltan parámetros SMTP clave, se marca error estructura
        if ($smtp_host === '' || $smtp_port <= 0 || $smtp_setfrom === '') {
            $sql_up_err = "UPDATE `tb_gestion_encuesta_registro_notificacion`
                           SET `gern_estado_envio`=?, `gern_fecha_envio`=?, `gern_intentos`=?
                           WHERE `gern_id`=?";
            $stmt_up_err = $enlace_db->prepare($sql_up_err);
            if ($stmt_up_err) {
                $estado = 'Error-estructura';
                $stmt_up_err->bind_param("ssis", $estado, $marca_temporal, $num_intentos, $id_correo);
                $stmt_up_err->execute();
                $stmt_up_err->close();
            }
            continue;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->Port = $smtp_port;

            // $mail->SMTPDebug = 2;
            $mail->SMTPSecure = $smtp_secure; // mantiene tu configuración (tls/ssl/'')
            $mail->SMTPAuth   = ($smtp_auth === '1' || strtolower($smtp_auth) === 'true');

            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->setFrom($smtp_setfrom, strip_crlf($smtp_setfrom_nm));

            // Destinatarios (REMEDIADO: validación emails)
            add_recipients_from_string($mail, (string)($resultado_notificaciones[$i][4] ?? ''), 'to');
            add_recipients_from_string($mail, (string)($resultado_notificaciones[$i][5] ?? ''), 'cc');
            add_recipients_from_string($mail, (string)($resultado_notificaciones[$i][6] ?? ''), 'bcc');

            // Embedded images (REMEDIADO: validar ruta)
            $allowedBases = ['/var/www/html/'];
            $image_embedded_ruta   = explode(";", (string)($resultado_notificaciones[$i][9] ?? ''));
            $image_embedded_nombre = explode(";", (string)($resultado_notificaciones[$i][10] ?? ''));
            $image_embedded_tipo   = explode(";", (string)($resultado_notificaciones[$i][11] ?? ''));

            for ($j=0; $j < count($image_embedded_ruta); $j++) {
                $ruta = trim($image_embedded_ruta[$j] ?? '');
                $nom  = trim($image_embedded_nombre[$j] ?? '');
                $tipo = trim($image_embedded_tipo[$j] ?? '');

                if ($ruta === '' || $nom === '' || $tipo === '') continue;

                $ruta_ok = safe_realpath_under($ruta, $allowedBases);
                if ($ruta_ok === '') continue;

                // Mantengo tu firma de AddEmbeddedImage (sin cambiar lógica)
                $mail->AddEmbeddedImage($ruta_ok, $nom, $ruta_ok, 'base64', $tipo);
            }

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $asunto_final = "Notificación de alerta - evento crítico - Canal Presencial RADICADO ".$radicado_clean;
            $mail->Subject = $asunto_final;
            $mail->Body    = $contenido_correo;

            /* =========================
               2.5) Enviar y actualizar estado (REMEDIADO: prepared updates/inserts)
               ========================= */
            if ($mail->send()) {

                // Guardar subject/body como antes (manteniendo reemplazo de comillas simples)
                $contenido_correo_db = str_replace("'", '"', $contenido_correo);

                $sql_up_ok = "UPDATE `tb_gestion_encuesta_registro_notificacion`
                              SET `gern_estado_envio`=?, `gern_fecha_envio`=?, `gern_intentos`=?,
                                  `gern_subject`=?, `gern_body`=?
                              WHERE `gern_id`=?";
                $stmt_up_ok = $enlace_db->prepare($sql_up_ok);
                if ($stmt_up_ok) {
                    $estado_env = 'Enviado';
                    $stmt_up_ok->bind_param("ssisss", $estado_env, $marca_temporal, $num_intentos, $asunto_final, $contenido_correo_db, $id_correo);
                    $stmt_up_ok->execute();
                    $stmt_up_ok->close();
                }

                /* ===== Generar consecutivo alerta (mantiene lógica original) ===== */
                $consulta_consecutivo_alerta = mysqli_query($enlace_db, "SELECT MAX(`ga_id`) FROM `tb_gestion_alertas`");
                $resultado_consecutivo_alerta = mysqli_fetch_all($consulta_consecutivo_alerta);

                $max_alerta = $resultado_consecutivo_alerta[0][0] ?? 'INC000000000000';
                $ultimo_consecutivo_alerta = explode('INC', (string)$max_alerta);
                $nuevo_consecutivo_alerta = (int)($ultimo_consecutivo_alerta[1] ?? 0) + 1;
                $inser_consecutivo_alerta = "INC".str_pad((string)$nuevo_consecutivo_alerta, 12, "0", STR_PAD_LEFT);

                // Mantengo tu limpieza original (quita comillas simples)
                $descripcion_alerta = str_replace("'", "", (string)$descripcion_alerta);

                // Campos para insertar alerta
                $ga_id   = $inser_consecutivo_alerta;
                $ga_sim_asociado = (string)($resultado_notificaciones[$i][28] ?? '');
                $ga_sim_qr_encuesta = (string)$id_sim_generado;

                $ga_estado = 'Pendiente RN2';
                $ga_fecha_alerta = date('Y-m-d H:i:s');
                $ga_fecha_atencion_ciudadano = (string)($resultado_notificaciones[$i][30] ?? '');

                $ga_regional = (string)($resultado_notificaciones[$i][38] ?? '');
                $ga_centro_zonal = (string)($resultado_notificaciones[$i][39] ?? '');

                $ga_peticionario_id = (string)($resultado_notificaciones[$i][31] ?? '');
                $ga_peticionario_nombre = (string)($resultado_notificaciones[$i][32] ?? '');
                $ga_peticionario_telefono = (string)($resultado_notificaciones[$i][33] ?? '')."-".(string)($resultado_notificaciones[$i][34] ?? '');

                $ga_categoria_afectacion = (string)$categoria_alerta;
                $ga_descripcion_alerta = (string)$descripcion_alerta;

                $ga_afecta_linea_tecnica = 'Si';
                $ga_fecha_remite_agente_especializado = date('Y-m-d H:i:s');

                // el usuario en tu insert original viene de TR.`gera_usuario_gestion` (idx 49)
                $ga_registro_usuario = (string)($resultado_notificaciones[$i][49] ?? '');

                $sql_ins_alerta = "
                    INSERT INTO `tb_gestion_alertas`(
                        `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`,
                        `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`,
                        `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`,
                        `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`,
                        `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`,
                        `ga_decision_caso`, `ga_observaciones_n1`, `ga_observaciones_n2`, `ga_snc_ac`,
                        `ga_fecha_cierre`, `ga_accion_mejora`, `ga_revisa_n1_usuario`, `ga_revisa_n1_fecha`,
                        `ga_revisa_n2_usuario`, `ga_revisa_n2_fecha`, `ga_registro_usuario`
                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ";

                $stmt_ins_alerta = $enlace_db->prepare($sql_ins_alerta);
                if ($stmt_ins_alerta) {
                    $v_empty = ""; // para campos vacíos
                    $stmt_ins_alerta->bind_param(
                        "sssssssssssssssssssssssssssss",
                        $ga_id,
                        $ga_sim_asociado,
                        $ga_sim_qr_encuesta,
                        $ga_estado,
                        $ga_fecha_alerta,
                        $ga_fecha_atencion_ciudadano,
                        $ga_regional,
                        $ga_centro_zonal,
                        $ga_peticionario_id,
                        $ga_peticionario_nombre,
                        $ga_peticionario_telefono,
                        $ga_categoria_afectacion,
                        $ga_descripcion_alerta,
                        $ga_afecta_linea_tecnica,
                        $ga_fecha_remite_agente_especializado,
                        $v_empty, // require regional
                        $v_empty, // respuesta regional
                        $v_empty, // concepto tecnico
                        $v_empty, // decision caso
                        $v_empty, // obs n1
                        $v_empty, // obs n2
                        $v_empty, // snc/ac
                        $v_empty, // fecha cierre
                        $v_empty, // accion mejora
                        $v_empty, // revisa n1 usuario
                        $v_empty, // revisa n1 fecha
                        $v_empty, // revisa n2 usuario
                        $v_empty, // revisa n2 fecha
                        $ga_registro_usuario
                    );
                    $stmt_ins_alerta->execute();
                    $stmt_ins_alerta->close();
                } else {
                    error_log("Prepare insert alerta falló: ".$enlace_db->error);
                }

            } else {
                // No enviado => actualizar estado
                $sql_up_fail = "UPDATE `tb_gestion_encuesta_registro_notificacion`
                                SET `gern_estado_envio`=?, `gern_fecha_envio`=?, `gern_intentos`=?
                                WHERE `gern_id`=?";
                $stmt_up_fail = $enlace_db->prepare($sql_up_fail);
                if ($stmt_up_fail) {
                    $stmt_up_fail->bind_param("ssis", $estado_error, $marca_temporal, $num_intentos, $id_correo);
                    $stmt_up_fail->execute();
                    $stmt_up_fail->close();
                }
            }

        } catch (\Throwable $e) {
            // Cualquier excepción => marcar según reintentos
            error_log("Error envío correo (gern_id={$id_correo}): ".$e->getMessage());

            $sql_up_fail = "UPDATE `tb_gestion_encuesta_registro_notificacion`
                            SET `gern_estado_envio`=?, `gern_fecha_envio`=?, `gern_intentos`=?
                            WHERE `gern_id`=?";
            $stmt_up_fail = $enlace_db->prepare($sql_up_fail);
            if ($stmt_up_fail) {
                $stmt_up_fail->bind_param("ssis", $estado_error, $marca_temporal, $num_intentos, $id_correo);
                $stmt_up_fail->execute();
                $stmt_up_fail->close();
            }
        }
    }
?>
