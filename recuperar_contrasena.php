<?php
    require_once("config/validaciones_seguridad_raiz.php");
    require_once("config/conexion_db.php");

    // CSRF (ParagonIE) - ya lo estás usando
    require_once __DIR__ . '/vendor/autoload.php';
    $csrf = new \ParagonIE\AntiCSRF\AntiCSRF();

    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(isset($_SESSION["usu_id"]) AND $_SESSION['usu_inicio_sesion']!=0){
        header("Location:contenido.php");
    } else {

        if(isset($_POST["form_recovery"])){

            // 1) Validación CSRF ANTES de tu lógica original (no afecta flujo, solo bloquea posts externos)
            if (!$csrf->validateRequest()) {
                $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>Solicitud inválida (CSRF). Recarga e intenta de nuevo.</p>";
            } else {

                //obtiene variable de captcha
                $captcha_validacion_recovery = validar_input($_POST['captcha_validacion_recovery']);
                $captcha_original = validar_input($_COOKIE['captcha']);
                //obtiene variable usuario y contraseña
                $doc_identidad = validar_input($_POST['doc_identidad']);
                $correo_usuario = validar_input($_POST['correo_usuario']);

                //valida el captcha correcto
                if ($captcha_original == sha1($captcha_validacion_recovery)) {

                    $consulta_string = "SELECT `usu_id`, `usu_acceso`, `usu_contrasena`, `usu_nombres_apellidos`, `usu_correo_corporativo`, `usu_estado`, `usu_inicio_sesion`, `usu_campania`
                                        FROM `tb_administrador_usuario`
                                        WHERE `usu_id`=? AND `usu_correo_corporativo`=?";

                    $consulta_registros = $enlace_db->prepare($consulta_string);
                    $consulta_registros->bind_param("ss", $doc_identidad, $correo_usuario);
                    $consulta_registros->execute();
                    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

                    if (count($resultado_registros)>0) {

                        if($resultado_registros[0][5]=='Activo'){

                            $token_hex = random_int(0, 9).random_int(0, 9).random_int(0, 9).random_int(0, 9).random_int(0, 9).random_int(0, 9);

                            // Prepara la sentencia
                            $sentencia_insert_token = $enlace_db->prepare("INSERT INTO `tb_administrador_token`(`tk_usuario`, `tk_token`, `tk_estado`) VALUES (?,?,'Activo')");
                            // Agrega variables a sentencia preparada
                            $sentencia_insert_token->bind_param('ss', $resultado_registros[0][0], $token_hex);

                            if ($sentencia_insert_token->execute()) {

                                /*SE CONFIGURAN PARÁMETROS A REGISTRAR EN SISTEMA DE NOTIFICACIÓN*/
                                /*SE ESTRUCTURA COTENIDO DE CORREO*/
                                $contenido_correo="<center><table style='width:100%; max-width: 600px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                        <tr>
                                            <td style='padding: 5px 5px 5px 5px;'><img src='cid:logo' style='width: 160px;'></img></td>
                                            <td style='padding: 5px 5px 5px 5px; text-align: right;'><img src='cid:logo_notificacion_correo'></td>
                                        </tr>
                                    </table>
                                    <table style='width:100%; max-width: 600px; font-family: Lato, Arial, sans-serif;'>
                                        <tr>
                                            <td style='padding: 5px 5px 5px 5px;'>
                                                <p style='font-size: 13px;padding: 0px 5px 0px 5px;color: #666666;'><b>Restablecer Contraseña</b></p>
                                                <br>
                                                <p style='font-size: 12px;padding: 0px 5px 0px 5px; color: #666666;'>Cordial saludo,<br><br>¡Hemos recibido una solicitud para restablecer la contraseña de tu cuenta!<br>Si no es tuya, ignora este correo electrónico.</p>
                                                <br>
                                                <center>
                                                    <p style='font-size: 12px;padding: 0px 5px 0px 5px; color: #666666;'><b>Código: ".$token_hex."</b></p>
                                                </center>
                                                <br>
                                                <p style='font-size: 12px;padding: 0px 5px 0px 5px; color: #666666;'>Ingresa el código anterior para continuar con el proceso de restauración de contraseña.</p>
                                                <center>
                                                    <a href='http://52.188.206.38/' target='_blank' style='border-radius:4px; color:#ffffff; font-size:12px; padding: 5px 5px 5px 5px; text-align:center; text-decoration:none!important; width:50%; display: block; background-color: #72BF44'>Ir a IQGIS-ICBF</a>
                                                </center>
                                                <br>
                                            </td>
                                        </tr>
                                    </table>
                                    <table style='width: 100%; max-width: 600px; background: #666666; font-family: Lato, Arial, sans-serif;'>
                                        <tr>
                                            <td style='font-size: 12px; padding: 5px 10px 5px 10px; color: #FFFFFF'>
                                                <center>IQ-ICBF | Gestión Integrada de Servicios | &copy; Copyright 2020 Todos los derechos reservados.</center>
                                            </td>
                                        </tr>
                                    </table>
                                    <table style='width:100%; max-width: 600px; font-family: Lato, Arial, sans-serif;'>
                                        <tr>
                                            <td style='padding: 5px 5px 5px 5px;'>
                                                <center>
                                                    <p style='font-size: 12px;font-family: Lato, Arial, sans-serif; color: #666666;'>Te recordamos que este correo electrónico es utilizado solamente para el envío de notificaciones automáticas.
                                                        <br>Por favor no respondas con consultas personales ya que no podrán ser respondidas.
                                                    </p>
                                                </center>
                                            </td>
                                        </tr>
                                    </table>
                                    <center>
                                        <table style='max-width: 600px; font-family: Lato, Arial, sans-serif;'>
                                            <tr>
                                                <td style='width: 45px;'><img src='cid:firma-verde' style='margin: 5px; width: 45px;'></td>
                                                <td>
                                                    <p style='font-size: 11px; color: #196F3D; font-family: Lato, Arial, sans-serif;'><br>No imprima este mensaje de no ser necesario;<br>de ésta manera aportamos al cuidado del planeta.</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </center>
                                    <table style='width:100%; max-width: 600px; font-family: Lato, Arial, sans-serif;'>
                                        <tr>
                                            <td style='padding: 5px 5px 5px 5px;'>
                                                <center>
                                                    <i><p style='font-size: 11px;font-family: Lato, Arial, sans-serif; color: #666666;'>AVISO LEGAL (o AVISO DE CONFIDENCIALIDAD): El contenido de este mensaje y sus anexos pueden contener información confidencial o legalmente protegida de propiedad de la ENTIDAD REMITENTE, y no podrá ser utilizada ni divulgada bajo ningún aspecto por personas diferentes a su destinatario. Cualquier uso, divulgación, copia, distribución, impresión o actos originados con ocasión al conocimiento de este mensaje sin autorización de la ENTIDAD REMITENTE será sancionado de acuerdo con las normas legales vigentes y la ENTIDAD REMITENTE no asumirá ninguna responsabilidad. El destinatario del mensaje, o cualquier otra persona que lo reciba, tendrá bajo su custodia la información aquí contenida y deberá velar por mantener su confidencialidad, integridad y privacidad. Las opiniones contenidas en este mensaje electrónico no relacionadas con la actividad de la ENTIDAD REMITENTE, no necesariamente representan la opinión de la ENTIDAD REMITENTE como empresa. Si por error, recibe este mensaje, deberá dar aviso inmediatamente a su remitente y destruir toda copia que tenga del mismo. Cualquier persona que ilícitamente elimine, esconda, cambie, destruya, intercepte, divulgue, controle o impida de otra forma que esta comunicación llegue de manera íntegra y oportuna a su destinatario, estará sujeto a las sanciones penales correspondientes. Aunque la ENTIDAD REMITENTE ha tomado las medidas preventivas para protegerse de virus y otros programas maliciosos, el destinatario debe verificar la presencia de este tipo de programas que tenga el correo o cualquier anexo a él, y por esta razón la ENTIDAD REMITENTE no se hace responsable de los daños causados por virus o cualquier otro programa malicioso transmitido por este medio.
                                                    </p></i>
                                                </center>
                                            </td>
                                        </tr>
                                    </table>
                                </center>";
                                /*SE ESTRUCTURA COTENIDO DE CORREO*/

                                $nc_id_modulo = "1";
                                $nc_prioridad = "Alta";
                                $nc_id_set_from = "1";
                                $nc_address = $correo_usuario.'|'.$correo_usuario;
                                $nc_cc = "";
                                $nc_bcc = "";
                                $nc_reply_to = "";
                                $nc_subject = "Restablecer Contraseña - IQ-ICBF | Gestión Integrada de Servicios";
                                $nc_body = str_replace("'", '"', $contenido_correo);
                                $nc_embeddedimage_ruta = "/var/www/html/images/firma-verde.png;/var/www/html/images/logo.png;/var/www/html/images/logo_notificacion_correo.png";
                                $nc_embeddedimage_nombre = "firma-verde;logo;logo_notificacion_correo";
                                $nc_embeddedimage_tipo = "image/png;image/png;image/png";
                                $nc_intentos = "";
                                $nc_eliminar = "Si";
                                $nc_estado_envio = "Pendiente";
                                $nc_fecha_envio = "";
                                $nc_usuario_registro = $doc_identidad;

                                // ✅ Remediación SQLi: INSERT con prepared statement (manteniendo el mismo flujo)
                                $sql_notif = "INSERT INTO `tb_notificaciones_central`
                                    (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                                     `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                                     `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                                $stmt_notif = $enlace_db->prepare($sql_notif);

                                if (!$stmt_notif) {
                                    $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Problemas al generar la notificación, por favor intente más tarde!</p>";
                                } else {

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

                                    $verifica_notificacion = 0;

                                    for ($i=0; $i < 10; $i++) {
                                        if ($stmt_notif->execute()) {
                                            $verifica_notificacion = 1;
                                            registro_log($enlace_db, 'Login', 'notificacion', $nc_subject, 'NULL', $doc_identidad);
                                            header("Location: recuperar_contrasena_confirmar.php?1=".base64_encode($resultado_registros[0][0])."&2=".base64_encode($resultado_registros[0][4])."");
                                            break;
                                        }
                                    }

                                    // Si quieres, puedes cerrar:
                                    // $stmt_notif->close();
                                }

                            } else {
                                $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Problemas al generar el token, por favor intente más tarde!</p>";
                            }

                        } else {
                            $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Usuario inactivo, por favor comuníquese con el administrador!</p>";
                        }

                    } else {
                        $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Los datos ingresados no coinciden con nuestros registros, inténtalo de nuevo!</p>";
                    }

                } else {
                    $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Problema al validar los datos ingresados, verifique e intente nuevamente!</p>";
                }
            } // fin CSRF
        }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie-edge">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css?v=2">
    <link rel="stylesheet" type="text/css" href="css/login.css?v=2">
    <link rel="stylesheet" type="text/css" href="fonts/css/all.css?v=2">
    <!-- favicon link-->
    <link rel="shortcut icon" type="image/icon" href="images/favicon.ico?v=2"/>
    <title>IQ-ICBF | Gestión Integrada de Servicios</title>
</head>
<body>
    <div class="container-fluid">
        <div class="login-form">
            <div class="form-header">
                <img src="images/header_dian.png" class="img-fluid" style="width: 80%;">
            </div>
            <form id="login-form" method="post" class="form-signin fluid" role="form" action="">
                <?php $csrf->insertToken(); ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="form-titulo">Olvidé mi contraseña</h4>
                        <h6 class="font-weight-normal">Para restablecer la contraseña, registra la siguiente información:</h6>
                        <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                    </div>
                    <div class="col-md-12">
                        <input name="doc_identidad" id="doc_identidad" type="text" class="form-control" value="<?php if(isset($_POST["form_recovery"])){ echo validar_output($doc_identidad); } ?>" placeholder="Doc. identidad" maxlength="50" autofocus autocomplete="off" required>
                    </div>
                    <div class="col-md-12">
                        <input name="correo_usuario" id="correo_usuario" type="email" class="form-control" placeholder="Email" maxlength="50" autocomplete="off" required>
                    </div>
                </div>
                <div class="row pt-1">
                    <div class="col-md-9 ">
                        <input name="captcha_validacion_recovery" id="captcha_validacion_recovery" type="text" class="form-control" placeholder="Escriba los caracteres de la imagen" maxlength="5" autocomplete="off" required>
                    </div>
                    <div class="col-md-3">
                        <center><img src="captcha_imagen.php" title="Código aleatorio"></center>
                    </div>
                    <div class="col-md-12 pt-1">
                        <button class="btn btn-block btn-success" type="submit" name="form_recovery" id="submit_btn" data-loading-text="Iniciando....">Restablecer</button>
                        <a href="index.php" class="btn btn-block btn-danger mt-1">Regresar</a>
                    </div>
                </div>
            </form>
            <div class="form-footer">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <p>IQ-Instituto Colombiano de Bienestar Familiar<br>Gestión Integrada de Servicios | &copy; Copyright 2020</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
<?php
    }
?>
