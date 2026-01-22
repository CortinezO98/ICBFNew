<?php
    require_once("config/validaciones_seguridad_raiz.php");
    require_once("config/conexion_db.php");

    // Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo //
    if(isset($_SESSION["usu_id"]) AND $_SESSION['usu_inicio_sesion']!=0){
        header("Location:contenido.php");
        exit;
    } else {

        if(isset($_POST["form_recovery_confirm"])){

            // obtiene variable usuario, token y contraseñas
            $id_usuario     = validar_input(base64_decode($_GET['1'] ?? ''));
            $correo_usuario = validar_input(base64_decode($_GET['2'] ?? ''));
            $contrasena_1   = validar_input($_POST['contrasena_1'] ?? '');
            $contrasena_2   = validar_input($_POST['contrasena_2'] ?? '');
            $codigo_token   = validar_input($_POST['codigo_token'] ?? '');

            if (!isset($_SESSION["password_recovery"])) {

                $consulta_string = "SELECT `tk_usuario`, `tk_token`, `tk_estado`
                                    FROM `tb_administrador_token`
                                    WHERE `tk_usuario`=? AND `tk_token`=? AND TIME_TO_SEC(TIMEDIFF(NOW(), `tk_registro_fecha`))<'180'";

                $consulta_registros = $enlace_db->prepare($consulta_string);
                $consulta_registros->bind_param("ss", $id_usuario, $codigo_token);
                $consulta_registros->execute();
                $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

                if (count($resultado_registros)>0) {

                    if ($contrasena_1==$contrasena_2) {

                        $salt = substr(base64_encode(openssl_random_pseudo_bytes(30)), 0, 22);
                        $salt = strtr($salt, array('+' => '.'));
                        $contrasena_update = crypt($contrasena_1, '$2y$10$' . $salt);

                        // Prepara la sentencia (ya estaba OK)
                        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_administrador_usuario` SET `usu_contrasena`=? WHERE `usu_id`=?");
                        $consulta_actualizar->bind_param('ss', $contrasena_update, $id_usuario);
                        $consulta_actualizar->execute();

                        if (comprobarSentencia($enlace_db->info)) {

                            /* SE CONFIGURAN PARÁMETROS A REGISTRAR EN SISTEMA DE NOTIFICACIÓN */
                            /* SE ESTRUCTURA CONTENIDO DE CORREO */
                            $contenido_correo="<center><table style='width:100%; max-width: 600px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                    <tr>
                                        <td style='padding: 5px 5px 5px 5px;'><img src='cid:logo' style='width: 160px;'></img></td>
                                        <td style='padding: 5px 5px 5px 5px; text-align: right;'><img src='cid:logo_notificacion_correo'></td>
                                    </tr>
                                </table>
                                <table style='width:100%; max-width: 600px; font-family: Lato, Arial, sans-serif;'>
                                    <tr>
                                        <td style='padding: 5px 5px 5px 5px;'>
                                            <p style='font-size: 13px;padding: 0px 5px 0px 5px;color: #666666;'><b>Confirmación Exitosa Cambio de Contraseña</b></p>
                                            <br>
                                            <p style='font-size: 12px;padding: 0px 5px 0px 5px; color: #666666;'>Cordial saludo,<br><br>¡Se ha realizado cambio exitoso de la contraseña de tu cuenta!</p>
                                            <br>
                                            <br>
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
                            /* SE ESTRUCTURA CONTENIDO DE CORREO */

                            $nc_id_set_from="1";
                            $nc_address=$correo_usuario.'|'.$correo_usuario;
                            $nc_cc="";
                            $nc_bcc="";
                            $nc_subject="Confirmación Exitosa Cambio de Contraseña - IQ-ICBF | Gestión Integrada de Servicios";
                            $nc_body=str_replace("'", '"', $contenido_correo);
                            $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png;/var/www/html/images/logo.png;/var/www/html/images/logo_notificacion_correo.png";
                            $nc_embeddedimage_nombre="firma-verde;logo;logo_notificacion_correo";
                            $nc_embeddedimage_tipo="image/png;image/png;image/png";
                            $nc_adjuntos_nombre="";
                            $nc_adjuntos_ruta="";
                            $nc_intentos="";
                            $nc_eliminar="Si";
                            $nc_estado_envio="Pendiente";
                            $nc_fecha_envio="";
                            $nc_usuario_registro=$id_usuario;

                            $verifica_notificacion=0;

                            // ✅ REMEDIACIÓN SQLi: INSERT con sentencia preparada (sin cambiar el flujo)
                            $sql_notif = "INSERT INTO `tb_notificaciones_central`
                                (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                                 `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                                 `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                                VALUES
                                ('1','Alta', ?, ?, ?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                            $stmt_notif = $enlace_db->prepare($sql_notif);

                            for ($i=0; $i < 10; $i++) {

                                // bindea cada intento (mantiene el comportamiento original)
                                $stmt_notif->bind_param(
                                    "sssssssssssssss",
                                    $nc_id_set_from,
                                    $nc_address,
                                    $nc_cc,
                                    $nc_bcc,
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

                                $consulta_notificacion = $stmt_notif->execute();

                                if ($consulta_notificacion) {
                                    $verifica_notificacion=1;
                                    registro_log($enlace_db, 'Login', 'notificacion', $nc_subject, 'NULL', $id_usuario);
                                    $_SESSION["password_recovery"]='Ok';

                                    // Prepara la sentencia (ya estaba OK)
                                    $consulta_actualizar_token = $enlace_db->prepare("UPDATE `tb_administrador_token` SET `tk_estado`='Usado' WHERE `tk_usuario`=? AND `tk_token`=?");
                                    $consulta_actualizar_token->bind_param('ss', $id_usuario, $codigo_token);
                                    $consulta_actualizar_token->execute();

                                    $respuesta_accion = "<p class='alert alert-success p-1 font-size-13'>¡Contraseña actualizada exitosamente!</p>";
                                    break;
                                }
                            }

                            if (isset($stmt_notif) && $stmt_notif instanceof mysqli_stmt) {
                                $stmt_notif->close();
                            }

                        } else {
                            $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Problemas al cambiar la contraseña, por favor verifique e intente nuevamente!</p>";
                        }

                    } else {
                        $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Las contraseñas no coinciden, por favor verifique e intente nuevamente!</p>";
                    }

                } else {
                    $respuesta_accion = "<p class='alert alert-danger p-1 font-size-13'>¡Código no válido, por favor verifique e intente nuevamente!</p>";
                }

            } else {
                $respuesta_accion = "<p class='alert alert-success p-1 font-size-13'>¡Contraseña actualizada exitosamente!</p>";
            }
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
				<div class="row">
					<?php if(!isset($_SESSION["password_recovery"])): ?>
						<div class="col-md-12">
							<h4 class="form-titulo">Cambiar contraseña</h4>
							<h6 class="font-weight-normal">Por favor ingresa una nueva contraseña y el código enviado al correo electrónico:</h6>
							<?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
						</div>
						<div class="col-md-12">
							<input name="contrasena_1" id="contrasena_1" type="password" class="form-control" value="" placeholder="Contraseña nueva" maxlength="50" autofocus autocomplete="off" required>
						</div>
						<div class="col-md-12">
							<input name="contrasena_2" id="contrasena_2" type="password" class="form-control" placeholder="Confirmar contraseña" maxlength="50" autocomplete="off" required>
						</div>
						<div class="col-md-12">
							<input name="codigo_token" id="codigo_token" type="text" class="form-control" placeholder="Código" maxlength="50" autocomplete="off" required>
						</div>
					<?php endif; ?>
					<?php if(isset($_SESSION["password_recovery"])): ?>
						<div class="col-md-12">
							<?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="row pt-1">
					<div class="col-md-12 pt-1">
						<?php if(!isset($_SESSION["password_recovery"])): ?>
							<button class="btn btn-block btn-success" type="submit" name="form_recovery_confirm" id="submit_btn" data-loading-text="Iniciando....">Restablecer</button>
							<a href="index.php" class="btn btn-block btn-danger mt-1">Cancelar</a>
						<?php endif; ?>
						<?php if(isset($_SESSION["password_recovery"])): ?>
							<a href="index.php" class="btn btn-block btn-dark mt-1">Finalizar</a>
						<?php endif; ?>
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
