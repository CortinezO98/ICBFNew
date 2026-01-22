<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /* =======================
       Helpers XSS (salida segura)
       ======================= */
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

    /* =======================
       Helper upload seguro (PDF)
       ======================= */
    if (!function_exists('safe_pdf_upload')) {
        /**
         * Valida y guarda un PDF de forma segura.
         * Retorna array: [ok(bool), filename(string), final_path(string), ext(string), error(string)]
         */
        function safe_pdf_upload(array $file, string $destDir, string $prefix = 'soporte_refute_') : array
        {
            if (!isset($file['name']) || trim((string)$file['name']) === '') {
                return [true, '', '', '', ''];
            }

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                return [false, '', '', '', 'Error al subir el archivo.'];
            }

            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return [false, '', '', '', 'Archivo inválido.'];
            }

            // Tamaño máximo (ajusta si ya tienes política distinta)
            $maxBytes = 10 * 1024 * 1024; // 10MB
            if (isset($file['size']) && (int)$file['size'] > $maxBytes) {
                return [false, '', '', '', 'El archivo supera el tamaño permitido.'];
            }

            $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                return [false, '', '', '', 'Solo se permiten archivos PDF.'];
            }

            // Verificación MIME real (no confiar en el cliente)
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            if ($mime !== 'application/pdf' && $mime !== 'application/octet-stream') {
                // Algunos servidores reportan octet-stream; por eso se permite.
                return [false, '', '', '', 'El archivo no parece ser un PDF válido.'];
            }

            // Crear directorio destino con permisos seguros
            if (!is_dir($destDir)) {
                if (!mkdir($destDir, 0750, true)) {
                    return [false, '', '', '', 'No fue posible crear el directorio de destino.'];
                }
            }

            // Nombre seguro (no usar nombre original)
            $filename = $prefix . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.pdf';
            $finalPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

            if (!move_uploaded_file($file['tmp_name'], $finalPath)) {
                return [false, '', '', '', 'No fue posible guardar el archivo en el servidor.'];
            }

            return [true, $filename, $finalPath, $ext, ''];
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Refutar";
    $pagina            = validar_input($_GET['pagina'] ?? '');
    $bandeja           = validar_input(base64_decode($_GET['bandeja'] ?? '') ?: '');
    $filtro_permanente = validar_input($_GET['id'] ?? '');
    $id_registro       = validar_input(base64_decode($_GET['reg'] ?? '') ?: '');
    $agente_calidad    = validar_input($_GET['calidad'] ?? '');

    // Evita notices si no existe aún
    if (!isset($_SESSION['registro_creado_cambio_estado'])) {
        $_SESSION['registro_creado_cambio_estado'] = 0;
    }

    if(isset($_POST["guardar_registro"])){

        $observaciones = validar_input($_POST['observaciones'] ?? '');

        if ($agente_calidad!="" AND $agente_calidad=="Rechazar") {
            $tipo_cambio="Refutar-Rechazado";
            $estado="Refutado-Rechazado";
        } elseif ($agente_calidad!="" AND $agente_calidad=="Conciliar") {
            $tipo_cambio="Refutar-Conciliar";
            $estado="Refutado-Conciliación";
        } else {
            $tipo_cambio="Refutar";
            $estado="Refutado";
        }

        $id_usuario = $_SESSION['usu_id'];

        if($_SESSION['registro_creado_cambio_estado'] != 1){

            // INSERT historial (prepared)
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_auditoria_monitoreo_historial`
                (`gcmh_monitoreo`, `gcmh_tipo_cambio`, `gcmh_comentarios`, `gcmh_registro_usuario`)
                VALUES (?,?,?,?)");

            $sentencia_insert->bind_param('ssss', $id_registro, $tipo_cambio, $observaciones, $id_usuario);

            if ($sentencia_insert->execute()) {

                // Directorio seguro por id (evita traversal)
                $id_dir = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$id_registro);
                if ($id_dir === '') { $id_dir = '0'; }

                $ruta_guardar = "storage/".$id_dir;

                // Subida segura PDF (si viene)
                $control_documento = 1;

                if (isset($_FILES['documento']) && ($_FILES['documento']['name'] ?? '') !== '') {

                    $upload = safe_pdf_upload($_FILES['documento'], $ruta_guardar, 'soporte_refute_');

                    if ($upload[0] === true) {
                        $NombreArchivo = $upload[1];
                        $ruta_final    = $upload[2];
                        $archivo_extension = $upload[3];

                        // Insert soporte (prepared)
                        $consulta_registro_soporte_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_auditoria_monitoreo_soportes`
                            (`gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`)
                            VALUES (?,?,?,?,?)");

                        $consulta_registro_soporte_insert->bind_param(
                            'sssss',
                            $id_registro,
                            $NombreArchivo,
                            $ruta_final,
                            $archivo_extension,
                            $_SESSION['usu_id']
                        );

                        if ($consulta_registro_soporte_insert->execute()) {
                            $control_documento = 1;
                        } else {
                            $control_documento = 0;
                        }
                    } else {
                        $control_documento = 0;
                    }
                } else {
                    $control_documento = 1;
                }

                // Actualiza estado monitoreo (prepared)
                $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo` SET `gcm_estado`=? WHERE `gcm_id`=?");
                $consulta_actualizar->bind_param('ss', $estado, $id_registro);
                $consulta_actualizar->execute();

                if (comprobarSentencia($enlace_db->info)) {

                    // Validación (1)
                    $consulta_string_validar="SELECT
                            TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`,
                            TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`,
                            TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`,
                            TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`,
                            TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`,
                            TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`,
                            `gcm_afectacion_1`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos`
                        FROM `tb_gestion_auditoria_monitoreo` AS TMC
                        LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id`
                        LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id`
                        LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id`
                        LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id`
                        LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id`
                        LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id`
                        WHERE TMC.`gcm_id`=?";

                    $consulta_registros_validar = $enlace_db->prepare($consulta_string_validar);
                    $consulta_registros_validar->bind_param("s", $id_registro);
                    $consulta_registros_validar->execute();
                    $resultado_registros_validar = $consulta_registros_validar->get_result()->fetch_all(MYSQLI_NUM);

                    // Mantener lógica original: si estado Refutado y fecha_hora_cierre == '0' entonces afectación 1
                    if ($estado=='Refutado' AND (($resultado_registros_validar[0][4] ?? '')=='0')) {
                        $gcm_afectacion_1='Si';
                        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo` SET `gcm_afectacion_1`=? WHERE `gcm_id`=?");
                        $consulta_actualizar->bind_param('ss', $gcm_afectacion_1, $id_registro);
                        $consulta_actualizar->execute();
                    }

                    // Conciliación -> afectación 2
                    if ($estado=='Refutado-Conciliación') {
                        $gcm_afectacion_2='Si';
                        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_auditoria_monitoreo` SET `gcm_afectacion_2`=? WHERE `gcm_id`=?");
                        $consulta_actualizar->bind_param('ss', $gcm_afectacion_2, $id_registro);
                        $consulta_actualizar->execute();
                    }

                    // insert log eventos
                    $consulta_string_log = "INSERT INTO `tb_administrador_log`
                        (`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`)
                        VALUES (?,?,?,?,?)";

                    $log_modulo=$modulo_plataforma;
                    $log_tipo="editar";
                    $log_accion="Editar registro";
                    $log_detalle=$tipo_cambio." monitoreo auditoría [".$id_registro."]";
                    $log_usuario=$_SESSION["usu_id"];

                    $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                    $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                    $consulta_registros_log->execute();
                    // insert log eventos

                    // Validación (2) (se conserva tal cual el flujo original)
                    $consulta_registros_validar = $enlace_db->prepare($consulta_string_validar);
                    $consulta_registros_validar->bind_param("s", $id_registro);
                    $consulta_registros_validar->execute();
                    $resultado_registros_validar = $consulta_registros_validar->get_result()->fetch_all(MYSQLI_NUM);

                    if (($resultado_registros_validar[0][25] ?? '')!='No-Cliente') {

                        // PROGRAMAR NOTIFICACIÓN CORREO
                        $array_correo_destino['correos']['TO']="camilo.gomez@icbf.gov.co|camilo.gomez@icbf.gov.co;zollange.beltran@icbf.gov.co|zollange.beltran@icbf.gov.co";
                        $array_correo_destino['correos']['CC']="";

                        // Cuerpo correo (se mantiene diseño; solo cuidamos comillas para DB y evitar inyección en SQL)
                        $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>Cordial Saludo,<br><br>Se ha refutado por parte del proveedor, el siguiente monitoreo de auditoría. Por favor verificar el detalle ingresando al siguiente link: <a href='http://20.172.253.76/' target='_blank'>ICBF-IQGIS</a></p><br>
                            <center>
                            <table style='width: 500px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Auditoría</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $id_registro ."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Observaciones</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $observaciones ."</td>
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
                        $nc_subject="Refutado | ".$id_registro;
                        $nc_body=str_replace("'", '"', $contenido_correo);
                        $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png";
                        $nc_embeddedimage_nombre="logo_firma_verde";
                        $nc_embeddedimage_tipo="image/png";
                        $nc_intentos="";
                        $nc_eliminar="Si";
                        $nc_estado_envio="Pendiente";
                        $nc_fecha_envio="";
                        $nc_usuario_registro=$id_usuario;

                        /* =======================
                           Remediación SQLi: INSERT notificación con prepared statement
                           ======================= */
                        $stmt_notif = $enlace_db->prepare("INSERT INTO `tb_notificaciones_central`
                            (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                             `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                             `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                        if ($stmt_notif) {
                            $stmt_notif->bind_param(
                                "sssssssssssssssss",
                                $nc_id_modulo, $nc_prioridad, $nc_id_set_from, $nc_address, $nc_cc, $nc_bcc, $nc_reply_to,
                                $nc_subject, $nc_body, $nc_embeddedimage_ruta, $nc_embeddedimage_nombre, $nc_embeddedimage_tipo,
                                $nc_intentos, $nc_eliminar, $nc_estado_envio, $nc_fecha_envio, $nc_usuario_registro
                            );
                            $stmt_notif->execute();
                            $stmt_notif->close();
                        }
                    }

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

    $consulta_string_monitoreo="SELECT
            TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`,
            TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`,
            TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`,
            TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`,
            TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`,
            `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_afectacion_1`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`,
            TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos`
        FROM `tb_gestion_auditoria_monitoreo` AS TMC
        LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id`
        LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id`
        LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id`
        LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id`
        LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id`
        LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id`
        WHERE TMC.`gcm_id`=?";

    $consulta_registros_monitoreo = $enlace_db->prepare($consulta_string_monitoreo);
    $consulta_registros_monitoreo->bind_param("s", $id_registro);
    $consulta_registros_monitoreo->execute();
    $resultado_registros_monitoreo = $consulta_registros_monitoreo->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_adjuntos="SELECT `gcms_id`, `gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`,
            `gcms_registro_usuario`, `gcms_registro_fecha`
        FROM `tb_gestion_calidad_monitoreo_soportes`
        WHERE `gcms_monitoreo`=?";

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
	<?php include("../config/configuracion_estilos.php"); ?>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <?php if (!empty($respuesta_accion)) { echo $respuesta_accion; } ?>
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <div class="col-md-5 pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Consecutivo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][0] ?? ''); ?></td>
                                <input type="hidden" name="estado_old" value="<?php echo h_attr($resultado_registros_monitoreo[0][15] ?? ''); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11">
                                    <?php echo h($resultado_registros_monitoreo[0][1] ?? ''); ?><br>
                                    [<?php echo h($resultado_registros_monitoreo[0][28] ?? ''); ?>]
                                </td>
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
                            <?php $ecuf_estado = (int)($resultado_registros_monitoreo[0][24] ?? 0); ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECUF</th>
                                <td class="align-middle py-0 font-size-11 <?php echo ($ecuf_estado ? 'aceptado':'rechazado'); ?>">
                                    <?php echo ($ecuf_estado ? "<span class='fas fa-check-circle'></span>":"<span class='fas fa-times-circle'></span>"); ?>
                                </td>
                            </tr>
                            <?php $ecn_estado = (int)($resultado_registros_monitoreo[0][23] ?? 0); ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECN</th>
                                <td class="align-middle py-0 font-size-11 <?php echo ($ecn_estado ? 'aceptado':'rechazado'); ?>">
                                    <?php echo ($ecn_estado ? "<span class='fas fa-check-circle'></span>":"<span class='fas fa-times-circle'></span>"); ?>
                                </td>
                            </tr>
                            <?php $enc_estado = (int)($resultado_registros_monitoreo[0][22] ?? 0); ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ENC</th>
                                <td class="align-middle py-0 font-size-11 <?php echo ($enc_estado ? 'aceptado':'rechazado'); ?>">
                                    <?php echo ($enc_estado ? "<span class='fas fa-check-circle'></span>":"<span class='fas fa-times-circle'></span>"); ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota General</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][21] ?? '0'); ?>%</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][11] ?? ''); ?></td>
                            </tr>
                            <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][16] ?? ''); ?></td>
                            </tr>
                            <?php endif; ?>
                        </thead>
                    </table>
                </div>

                <div class="fondo-corporativo color-blanco p-1 font-size-11"><span class="fas fa-paperclip"></span> Adjuntos monitoreo</div>
                <div class="font-size-11 p-1">
                    <?php if(count($resultado_registros_adjuntos)>0): ?>
                        <?php for ($i=0; $i < count($resultado_registros_adjuntos); $i++): ?>
                            <?php
                                // Evitar rutas peligrosas en href (defensa adicional)
                                $rutaAdj = (string)($resultado_registros_adjuntos[$i][3] ?? '');
                                $rutaAdjSafe = str_replace(['..', "\0"], '', $rutaAdj);
                                $nombreAdj = (string)($resultado_registros_adjuntos[$i][2] ?? '');
                                $nombreShort = (strlen($nombreAdj)>40) ? substr($nombreAdj, 0, 40).'...' : $nombreAdj;
                            ?>
                            <a href="<?php echo h_attr(URL.'gestion_calidad/'.$rutaAdjSafe); ?>" target="_blank" title="<?php echo h_attr($nombreAdj); ?>">
                                <span class="fas fa-external-link-alt"></span>
                            </a>
                            <?php echo h($nombreShort); ?><br>
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

            <div class="col-md-7 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">
                                <?php
                                    if($agente_calidad!="" AND $agente_calidad=="Rechazar") {
                                        echo "Observaciones/comentarios";
                                    } elseif($agente_calidad!="" AND $agente_calidad=="Conciliar") {
                                        echo "Observaciones para conciliación/comentarios";
                                    } else {
                                        echo "Observaciones para refutar/comentarios";
                                    }
                                ?>
                          </label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="10"
                                <?php if($_SESSION['registro_creado_cambio_estado']==1) { echo 'disabled'; } ?>
                                required><?php if(isset($_POST["guardar_registro"])) { echo h($observaciones ?? ''); } ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inputGroupFile01">Adjuntar soportes</label>
                            <div class="custom-file">
                                <input type="file" name="documento" class="custom-file-input" id="inputGroupFile01"
                                       aria-describedby="inputGroupFileAddon01"
                                       <?php if($_SESSION['registro_creado_cambio_estado']==1) { echo 'disabled'; } ?>
                                       accept=".pdf, .PDF">
                                <label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01label">Seleccione un archivo</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado_cambio_estado']==1): ?>
                                <a href="<?php echo h_attr($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
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
            var f = document.getElementById("inputGroupFile01").files;
            if (!f || !f[0]) return;
            var valor_opcion = f[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
            }
        });
    </script>
</body>
</html>
