<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       CSRF (one-time token)
       - No cambia el diseño
       - Solo bloquea POST sin token válido
    ========================= */
    if (!function_exists('icbf_csrf_issue')) {
        function icbf_csrf_issue(string $scope = 'CONTROL_TURNOS_CAMBIAR'): array {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            if (!isset($_SESSION['_csrf_tokens']) || !is_array($_SESSION['_csrf_tokens'])) {
                $_SESSION['_csrf_tokens'] = [];
            }

            $idx = rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
            $tok = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

            $_SESSION['_csrf_tokens'][$scope][$idx] = [
                't'  => $tok,
                'ts' => time()
            ];

            return [$idx, $tok];
        }
    }

    if (!function_exists('icbf_csrf_verify')) {
        function icbf_csrf_verify(string $idx, string $tok, string $scope = 'CONTROL_TURNOS_CAMBIAR', int $ttlSeconds = 1800): bool {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            if ($idx === '' || $tok === '') return false;
            if (!isset($_SESSION['_csrf_tokens'][$scope][$idx])) return false;

            $row = $_SESSION['_csrf_tokens'][$scope][$idx];
            $expected = (string)($row['t'] ?? '');
            $ts = (int)($row['ts'] ?? 0);

            if ($expected === '' || $ts <= 0) return false;
            if ((time() - $ts) > $ttlSeconds) {
                unset($_SESSION['_csrf_tokens'][$scope][$idx]);
                return false;
            }

            $ok = hash_equals($expected, $tok);

            // one-time use
            unset($_SESSION['_csrf_tokens'][$scope][$idx]);

            return $ok;
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Gestión Turnos | Solicitar Cambio";

    if (!isset($_SESSION['mturno_cambioturno_crear'])) {
        $_SESSION['mturno_cambioturno_crear'] = 0;
    }

    $csrf_error = false;

    if(isset($_POST["guardar_registro"])){

        // Verifica CSRF antes de procesar (sin afectar UI)
        $csrf_idx = $_POST['_CSRF_INDEX'] ?? '';
        $csrf_tok = $_POST['_CSRF_TOKEN'] ?? '';

        if (!icbf_csrf_verify($csrf_idx, $csrf_tok, 'CONTROL_TURNOS_CAMBIAR')) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recarga e intenta de nuevo.', 0);</script>";
            $csrf_error = true;
        }

        $usuario_origen   = $_SESSION["usu_id"];
        $mi_turno         = validar_input($_POST['mi_turno']);
        $usuario_destino  = validar_input($_POST['usuario_destino']);
        $turno_disponible = validar_input($_POST['turno_disponible']);
        $observaciones    = validar_input($_POST['observaciones']);

        if (!$csrf_error) {

            $consulta_string_tprogramado_origen="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TU.`usu_supervisor`, TU.`usu_correo_corporativo`, TR.`usu_correo_corporativo`, TR.`usu_nombres_apellidos` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TU.`usu_supervisor`=TR.`usu_id` WHERE `cotm_id`=? ORDER BY `cotm_usuario`, `cotm_inicio`";
            $consulta_registros_tprogramado_origen = $enlace_db->prepare($consulta_string_tprogramado_origen);
            // Agrega variables a sentencia preparada
            $consulta_registros_tprogramado_origen->bind_param('s', $mi_turno);
            $consulta_registros_tprogramado_origen->execute();
            $resultado_registros_tprogramado_origen = $consulta_registros_tprogramado_origen->get_result()->fetch_all(MYSQLI_NUM);

            $consulta_string_tprogramado_destino="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TU.`usu_correo_corporativo` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` WHERE `cotm_id`=? ORDER BY `cotm_usuario`, `cotm_inicio`";
            $consulta_registros_tprogramado_destino = $enlace_db->prepare($consulta_string_tprogramado_destino);
            // Agrega variables a sentencia preparada
            $consulta_registros_tprogramado_destino->bind_param('s', $turno_disponible);
            $consulta_registros_tprogramado_destino->execute();
            $resultado_registros_tprogramado_destino = $consulta_registros_tprogramado_destino->get_result()->fetch_all(MYSQLI_NUM);

            // Guardas de seguridad (evita warnings y mantiene flujo con mensaje)
            if (count($resultado_registros_tprogramado_origen) == 0 || count($resultado_registros_tprogramado_destino) == 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡No fue posible cargar la información de los turnos seleccionados, verifique e intente nuevamente!', 0);</script>";
            } else {

                if($_SESSION['mturno_cambioturno_crear']!=1){
                    // Prepara la sentencia
                    $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_control_turno_malla_cambio_turno`(`cmtct_usuario_origen`, `cmtct_uo_turno_anterior_inicio`, `cmtct_uo_turno_anterior_fin`, `cmtct_uo_turno_nuevo_inicio`, `cmtct_uo_turno_nuevo_fin`, `cmtct_usuario_destino`, `cmtct_ud_turno_anterior_inicio`, `cmtct_ud_turno_anterior_fin`, `cmtct_ud_turno_nuevo_inicio`, `cmtct_ud_turno_nuevo_fin`, `cmtct_responsable`, `cmtct_estado`, `cmtct_observaciones`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");

                    // Agrega variables a sentencia preparada
                    $sentencia_insert->bind_param('sssssssssssss', $usuario_origen, $uo_turno_anterior_inicio, $uo_turno_anterior_fin, $uo_turno_nuevo_inicio, $uo_turno_nuevo_fin, $usuario_destino, $ud_turno_anterior_inicio, $ud_turno_anterior_fin, $ud_turno_nuevo_inicio, $ud_turno_anterior_fin, $responsable, $estado_solicitud, $observaciones);

                    $uo_turno_anterior_inicio=$resultado_registros_tprogramado_origen[0][3];
                    $uo_turno_anterior_fin=$resultado_registros_tprogramado_origen[0][4];
                    $uo_turno_nuevo_inicio=$resultado_registros_tprogramado_destino[0][3];
                    $uo_turno_nuevo_fin=$resultado_registros_tprogramado_destino[0][4];

                    $ud_turno_anterior_inicio=$resultado_registros_tprogramado_destino[0][3];
                    $ud_turno_anterior_fin=$resultado_registros_tprogramado_destino[0][4];
                    $ud_turno_nuevo_inicio=$resultado_registros_tprogramado_origen[0][3];
                    $ud_turno_nuevo_fin=$resultado_registros_tprogramado_origen[0][4];

                    $responsable=$resultado_registros_tprogramado_origen[0][11];
                    $estado_solicitud='Pendiente';

                    if ($sentencia_insert->execute()) {
                        $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                        $_SESSION['mturno_cambioturno_crear']=1;

                        // PROGRAMAR NOTIFICACIÓN CORREO
                        /*SE DEFINEN DESTINATARIOS*/
                        $array_correo_destino['correos']['TO']=$resultado_registros_tprogramado_origen[0][12]."|".$resultado_registros_tprogramado_origen[0][12].";".$resultado_registros_tprogramado_destino[0][11]."|".$resultado_registros_tprogramado_destino[0][11];
                        $array_correo_destino['correos']['CC']=$resultado_registros_tprogramado_origen[0][13]."|".$resultado_registros_tprogramado_origen[0][13];

                        /*SE ESTRUCTURA COTENIDO DE CORREO*/
                        $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>Cordial Saludo,<br><br>Se notifica solicitud de cambio de turno relacionada a continuación. Mayor información ingresando al siguiente link: <a href='http://52.188.206.38/' target='_blank'>ICBF-IQGIS</a></p><br>
                            <center>
                            <table style='width: 500px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Estado</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>Pendiente</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Fecha</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('Y-m-d', strtotime($uo_turno_anterior_inicio))."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Doc. Solicitante</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros_tprogramado_origen[0][1]."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Solicitante</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros_tprogramado_origen[0][10]."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Anterior</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($uo_turno_anterior_inicio)).'-'.date('H:i', strtotime($uo_turno_anterior_fin))."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Nuevo</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($uo_turno_nuevo_inicio)).'-'.date('H:i', strtotime($uo_turno_nuevo_fin))."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Doc. Solicitado</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros_tprogramado_destino[0][1]."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Solicitado</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros_tprogramado_destino[0][10]."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Anterior</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($ud_turno_anterior_inicio))."-".date('H:i', strtotime($ud_turno_anterior_fin))."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Nuevo</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($ud_turno_nuevo_inicio))."-".date('H:i', strtotime($ud_turno_nuevo_fin))."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Responsable</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros_tprogramado_origen[0][14]."</td>
                                </tr>
                                <tr>
                                    <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Observaciones</td>
                                    <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$observaciones."</td>
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
                        $nc_subject="[Pendiente] Cambio de Turno";
                        $nc_body=str_replace("'", '"', $contenido_correo);
                        $nc_embeddedimage_ruta="/var/www/iqgis/html/images/firma-verde.png";
                        $nc_embeddedimage_nombre="logo_firma_verde";
                        $nc_embeddedimage_tipo="image/png";
                        $nc_intentos="";
                        $nc_eliminar="Si";
                        $nc_estado_envio="Pendiente";
                        $nc_fecha_envio="";
                        $nc_usuario_registro=$_SESSION['usu_id'];

                        // ✅ REMEDIACIÓN SQLi: INSERT notificación con sentencia preparada
                        $sql_notif = "INSERT INTO `tb_notificaciones_central`
                            (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                             `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                             `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                        $stmt_notif = $enlace_db->prepare($sql_notif);

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

                            $consulta_notificacion = $stmt_notif->execute();

                            if ($consulta_notificacion) {
                                registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject);
                                break;
                            }
                        }

                        if (isset($stmt_notif) && $stmt_notif instanceof mysqli_stmt) {
                            $stmt_notif->close();
                        }

                    } else {
                        $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                    }
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
                }
            }
        }
    }

    //CONSULTA MALLA DE TURNOS
    $fecha_minimo=date("Y-m-d", strtotime("+1 day"));
    $fecha_minimo=$fecha_minimo.' 00:00:00';

    // ✅ REMEDIACIÓN SQLi: consulta turnos programados con sentencia preparada
    $sql_turnos_programados = "SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`
        FROM `tb_control_turno_malla`
        LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id`
        WHERE `cotm_usuario`=? AND `cotm_inicio`>=? AND (`cotm_tipo`='turno' OR `cotm_tipo`='DESC') AND `cotm_estado`=''
        ORDER BY `cotm_usuario`, `cotm_inicio`";

    $stmt_turnos_programados = $enlace_db->prepare($sql_turnos_programados);
    $stmt_turnos_programados->bind_param("ss", $_SESSION['usu_id'], $fecha_minimo);
    $stmt_turnos_programados->execute();
    $resultado_inicio_turnos_programados = $stmt_turnos_programados->get_result()->fetch_all(MYSQLI_NUM);

    //CONSULTA MALLA DE TURNOS

    // ✅ REMEDIACIÓN SQLi: usuarios por campaña con bind_param (antes estaba concatenado)
    $consulta_string_usuarios="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_estado`, TC.`ac_nombre_campania`
        FROM `tb_administrador_usuario`
        LEFT JOIN `tb_administrador_campania` AS TC ON `tb_administrador_usuario`.`usu_campania`=TC.`ac_id`
        WHERE `usu_estado`<>'Retirado' AND `usu_campania`=?
        ORDER BY `usu_nombres_apellidos` ASC";

    $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
    $consulta_registros_usuarios->bind_param("s", $_SESSION["usu_campania"]);
    $consulta_registros_usuarios->execute();
    $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="../contenido.php";

    // Emite token CSRF para el formulario (GET)
    [$csrf_form_index, $csrf_form_token] = icbf_csrf_issue('CONTROL_TURNOS_CAMBIAR');
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
            <input type="hidden" name="_CSRF_INDEX" value="<?php echo htmlspecialchars($csrf_form_index, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="_CSRF_TOKEN" value="<?php echo htmlspecialchars($csrf_form_token, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="row justify-content-center">
            <div class="col-md-5 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="mi_turno" class="m-0">Mis turnos</label>
                            <select class="form-control form-control-sm" name="mi_turno" id="mi_turno" onchange="consultar_turnos();" <?php if($_SESSION['mturno_cambioturno_crear']==1) { echo 'disabled'; } ?> required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_inicio_turnos_programados); $i++): ?>
                                <option value="<?php echo htmlspecialchars($resultado_inicio_turnos_programados[$i][0], ENT_QUOTES, 'UTF-8'); ?>" class="font-size-11" <?php if(isset($_POST["guardar_registro"]) AND isset($mi_turno) AND $mi_turno==$resultado_inicio_turnos_programados[$i][0]){ echo "selected"; } ?>>
                                    <?php
                                        $texto_opt = ($resultado_inicio_turnos_programados[$i][2]=='turno')
                                            ? $resultado_inicio_turnos_programados[$i][6].' | '.$resultado_inicio_turnos_programados[$i][3].' A '.$resultado_inicio_turnos_programados[$i][4]
                                            : $array_convenciones[$resultado_inicio_turnos_programados[$i][2]].' | '.date('Y-m-d', strtotime($resultado_inicio_turnos_programados[$i][3]));
                                        echo htmlspecialchars($texto_opt, ENT_QUOTES, 'UTF-8');
                                    ?>
                                </option>
                              <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="usuario_destino" class="m-0">Usuario para cambio de turno</label>
                            <select class="selectpicker form-control form-control-sm font-size-11 padding-2" data-live-search="true" name="usuario_destino" id="usuario_destino" onchange="consultar_turnos();" <?php if($_SESSION['mturno_cambioturno_crear']==1) { echo 'disabled'; } ?> required>
                              <option value="" class="font-size-11">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_usuarios); $i++): ?>
                                <option value="<?php echo htmlspecialchars($resultado_registros_usuarios[$i][0], ENT_QUOTES, 'UTF-8'); ?>" class="font-size-11"
                                    data-tokens="<?php echo htmlspecialchars($resultado_registros_usuarios[$i][0].' '.$resultado_registros_usuarios[$i][1], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php if(isset($_POST["guardar_registro"]) AND isset($usuario_destino) AND $usuario_destino==$resultado_registros_usuarios[$i][0]){ echo "selected"; } ?>>
                                    <?php echo htmlspecialchars($resultado_registros_usuarios[$i][1], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                              <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="turno_disponible" class="m-0">Turnos disponibles</label>
                            <select class="form-control form-control-sm" name="turno_disponible" id="turno_disponible" <?php if($_SESSION['mturno_cambioturno_crear']==1) { echo 'disabled'; } ?> required>
                              <option value="" class="font-size-11">Seleccione</option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones" class="m-0">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" maxlength="200" <?php if($_SESSION['mturno_cambioturno_crear']==1) { echo 'readonly'; } ?>><?php if(isset($_POST["guardar_registro"]) && isset($observaciones)){ echo htmlspecialchars($observaciones, ENT_QUOTES, 'UTF-8'); } ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12" id="respuesta_datos"></div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['mturno_cambioturno_crear']==1): ?>
                                <a href="<?php echo htmlspecialchars($ruta_cancelar_finalizar, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark float-right">Finalizar</a>
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
    <script src="../js/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script type="text/javascript">
        function consultar_turnos(){
            var usuario_opcion = document.getElementById("usuario_destino");
            var usuario = usuario_opcion.options[usuario_opcion.selectedIndex].value;

            var turno_origen_opcion = document.getElementById("mi_turno");
            var turno_origen = turno_origen_opcion.options[turno_origen_opcion.selectedIndex].value;

            if(usuario!="" && turno_origen!="") {
                $("#turno_disponible").html("");
                $.post("control_turnos_cambiar_coincidencias_turnos.php?usuario="+usuario+"&turno_origen="+turno_origen, { }, function(data){
                    var resp = $.parseJSON(data);
                    if (resp.resultado_control) {
                        $("#turno_disponible").html(resp.resultado);
                        $("#respuesta_datos").html('');
                    } else {
                        $("#respuesta_datos").html(resp.resultado);
                    }
                });
            }

            $.ajax({
                success: function(){
                    $("#coincidencias").load("control_turnos_cambiar_coincidencias_turnos.php?usuario="+$("#usuario").val());
                }
            });
        }
        <?php if(isset($_POST["guardar_registro"])): ?>
            consultar_turnos();
        <?php endif; ?>
    </script>
</body>
</html>
