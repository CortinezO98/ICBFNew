<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       Helpers de seguridad
       ========================= */
    if (!function_exists('safe_base64_decode')) {
        function safe_base64_decode($value) {
            if ($value === null || $value === '') { return ''; }
            // base64 decode "estricto" para evitar basura; si falla retorna vacío
            $decoded = base64_decode($value, true);
            return ($decoded === false) ? '' : $decoded;
        }
    }

    if (!function_exists('h')) {
        function h($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    if (!function_exists('hbr')) {
        function hbr($value) {
            // Para mantener saltos de línea en campos de texto (sin permitir HTML)
            return nl2br(h($value));
        }
    }

    // Inicializa bandera de doble envío si no existe (evita warnings)
    if (!isset($_SESSION['registro_creado_cambio_estado'])) {
        $_SESSION['registro_creado_cambio_estado'] = 0;
    }

    // CSRF token (no afecta diseño; solo agrega un hidden)
    if (empty($_SESSION['csrf_token_refutar'])) {
        $_SESSION['csrf_token_refutar'] = bin2hex(random_bytes(32));
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Refutar";

    $pagina = isset($_GET['pagina']) ? validar_input($_GET['pagina']) : '';
    $bandeja = isset($_GET['bandeja']) ? validar_input(safe_base64_decode($_GET['bandeja'])) : '';
    $filtro_permanente = isset($_GET['id']) ? validar_input($_GET['id']) : '';
    $id_registro = isset($_GET['reg']) ? validar_input(safe_base64_decode($_GET['reg'])) : '';
    $agente_calidad = isset($_GET['calidad']) ? validar_input($_GET['calidad']) : '';

    // Enlace para botón finalizar y cancelar (se define temprano para redirecciones seguras)
    $ruta_cancelar_finalizar = "gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode($bandeja);

    // Validación mínima del consecutivo (mantiene comportamiento, evita errores por parámetros corruptos)
    if ($id_registro === '' || !ctype_digit((string)$id_registro)) {
        header("Location: ".$ruta_cancelar_finalizar);
        exit;
    }

    if(isset($_POST["guardar_registro"])){

        // Valida CSRF (sin cambiar UX; si falla, muestra alerta)
        $token_post = isset($_POST['csrf_token_refutar']) ? (string)$_POST['csrf_token_refutar'] : '';
        if (empty($token_post) || empty($_SESSION['csrf_token_refutar']) || !hash_equals($_SESSION['csrf_token_refutar'], $token_post)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Sesión inválida o expirada. Por favor recargue la página e intente nuevamente.', 0);</script>";
        } else {

            // No cambiamos el contenido que se guarda (para no afectar negocio); solo se escapa al mostrar/enviar
            $observaciones = isset($_POST['observaciones']) ? (string)$_POST['observaciones'] : '';

            if ($agente_calidad!="" AND $agente_calidad=="Rechazar") {
                $tipo_cambio="Refutar-Rechazado";
                $estado="Refutado-Rechazado";
            } else {
                $tipo_cambio="Refutar";
                $estado="Refutado";
            }
            $id_usuario=$_SESSION['usu_id'];

            if($_SESSION['registro_creado_cambio_estado']!=1){
                // Prepara la sentencia
                $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_monitoreo_historial`(`gcmh_monitoreo`, `gcmh_tipo_cambio`, `gcmh_comentarios`, `gcmh_registro_usuario`) VALUES (?,?,?,?)");

                // Agrega variables a sentencia preparada
                $sentencia_insert->bind_param('ssss', $id_registro, $tipo_cambio, $observaciones, $id_usuario);

                if ($sentencia_insert->execute()) {
                    // Prepara la sentencia
                    $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo` SET `gcm_estado`=? WHERE `gcm_id`=?");

                    // Agrega variables a sentencia preparada
                    $consulta_actualizar->bind_param('ss', $estado, $id_registro);

                    // Ejecuta sentencia preparada
                    $consulta_actualizar->execute();

                    if (comprobarSentencia($enlace_db->info)) {
                        //insert log eventos
                            $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";

                            $log_modulo=$modulo_plataforma;
                            $log_tipo="editar";
                            $log_accion="Editar registro";
                            $log_detalle=$tipo_cambio." monitoreo [".$id_registro."]";
                            $log_usuario=$_SESSION["usu_id"];

                            $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                            $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                            $consulta_registros_log->execute();
                        //insert log eventos

                        $consulta_string_validar="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

                        $consulta_registros_validar = $enlace_db->prepare($consulta_string_validar);
                        $consulta_registros_validar->bind_param("s", $id_registro);
                        $consulta_registros_validar->execute();
                        $resultado_registros_validar = $consulta_registros_validar->get_result()->fetch_all(MYSQLI_NUM);

                        if (!empty($resultado_registros_validar) && $resultado_registros_validar[0][25] != 'No-Cliente') {

                            $consulta_string_calidad="SELECT TA.`usu_id`, TA.`usu_nombres_apellidos`, TL.`usu_id`, TL.`usu_nombres_apellidos`, TL.`usu_correo_corporativo` FROM `tb_gestion_calidad_monitoreo` AS TM LEFT JOIN `tb_administrador_usuario` AS TA ON TM.`gcm_analista`=TA.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TL ON TM.`gcm_registro_usuario`=TL.`usu_id` WHERE `gcm_id`=?";

                            $consulta_registros_calidad = $enlace_db->prepare($consulta_string_calidad);
                            $consulta_registros_calidad->bind_param("s", $id_registro);
                            $consulta_registros_calidad->execute();
                            $resultado_registros_calidad = $consulta_registros_calidad->get_result()->fetch_all(MYSQLI_NUM);

                            if (!empty($resultado_registros_calidad)) {
                                // PROGRAMAR NOTIFICACIÓN CORREO
                                /*SE DEFINEN DESTINATARIOS*/
                                $array_correo_destino['correos']['TO'] = $resultado_registros_calidad[0][4]."|".$resultado_registros_calidad[0][3];
                                $array_correo_destino['correos']['CC'] = "";

                                // Escapado seguro para HTML de correo (sin ejecutar HTML del usuario)
                                $observaciones_html = nl2br(htmlspecialchars($observaciones, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

                                /*SE ESTRUCTURA COTENIDO DE CORREO*/
                                $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>Cordial Saludo,<br><br>Se ha refutado por parte del supervisor, el siguiente monitoreo. Por favor verificar el detalle ingresando al siguiente link: <a href='http://52.188.206.38/' target='_blank'>ICBF-IQGIS</a></p><br>
                                    <center>
                                    <table style='width: 500px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                        <tr>
                                            <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Monitoreo</td>
                                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". htmlspecialchars($id_registro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ."</td>
                                        </tr>
                                        <tr>
                                            <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Id Analista</td>
                                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". htmlspecialchars($resultado_registros_calidad[0][0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ."</td>
                                        </tr>
                                        <tr>
                                            <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Nombres y Apellidos</td>
                                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". htmlspecialchars($resultado_registros_calidad[0][1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ."</td>
                                        </tr>
                                        <tr>
                                            <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Observaciones</td>
                                            <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>". $observaciones_html ."</td>
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

                                // Ya no necesitamos str_replace para "proteger" comillas: usamos sentencia preparada
                                $nc_body=$contenido_correo;

                                $nc_embeddedimage_ruta="/var/www/iqgis/html/images/firma-verde.png";
                                $nc_embeddedimage_nombre="logo_firma_verde";
                                $nc_embeddedimage_tipo="image/png";
                                $nc_intentos="";
                                $nc_eliminar="Si";
                                $nc_estado_envio="Pendiente";
                                $nc_fecha_envio="";
                                $nc_usuario_registro=$id_usuario;

                                // ✅ Remediación SQLi: INSERT con prepared statement (misma tabla/campos)
                                $sql_notif = "INSERT INTO `tb_notificaciones_central`
                                    (`nc_id_modulo`, `nc_prioridad`, `nc_id_set_from`, `nc_address`, `nc_cc`, `nc_bcc`, `nc_reply_to`,
                                     `nc_subject`, `nc_body`, `nc_embeddedimage_ruta`, `nc_embeddedimage_nombre`, `nc_embeddedimage_tipo`,
                                     `nc_intentos`, `nc_eliminar`, `nc_estado_envio`, `nc_fecha_envio`, `nc_usuario_registro`)
                                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                                $stmt_notif = $enlace_db->prepare($sql_notif);
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
                                    $stmt_notif->execute();
                                }
                            }
                        }

                        $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                        $_SESSION['registro_creado_cambio_estado']=1;

                        // Rotación del token tras una acción exitosa (hardening; no afecta UX)
                        $_SESSION['csrf_token_refutar'] = bin2hex(random_bytes(32));

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
    }

    $consulta_string_monitoreo="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TUA.`usu_nombres_apellidos`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`, `gcm_fecha_monitoreo`, TMC.`gcm_analista`, TM.`gcm_observaciones`, TSA.`usu_nombres_apellidos` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TSA ON TUA.`usu_supervisor`=TSA.`usu_id` WHERE TMC.`gcm_id`=?";

    $consulta_registros_monitoreo = $enlace_db->prepare($consulta_string_monitoreo);
    $consulta_registros_monitoreo->bind_param("s", $id_registro);
    $consulta_registros_monitoreo->execute();
    $resultado_registros_monitoreo = $consulta_registros_monitoreo->get_result()->fetch_all(MYSQLI_NUM);

    // Si no existe el registro, volver sin romper UI
    if (empty($resultado_registros_monitoreo)) {
        header("Location: ".$ruta_cancelar_finalizar);
        exit;
    }
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
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
        <!-- CSRF (no cambia diseño) -->
        <input type="hidden" name="csrf_token_refutar" value="<?php echo h($_SESSION['csrf_token_refutar']); ?>">
        <div class="row justify-content-center">
            <div class="col-md-5 pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Consecutivo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][0]); ?></td>
                                <input type="hidden" name="estado_old" value="<?php echo h($resultado_registros_monitoreo[0][15]); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Matriz</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][1]); ?><br>[<?php echo h($resultado_registros_monitoreo[0][28]); ?>]</td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Analista</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][2]); ?></td>
                                <input type="hidden" name="id_analista" value="<?php echo h($resultado_registros_monitoreo[0][27]); ?>">
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Responsable</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][29]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][5]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Skill Interacción</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][6]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Tipo Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][7]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Segmento</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][8]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id SIM</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][9]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Id/ANI</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][10]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Gestión</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][3]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Fecha Monitoreo</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][26]); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECUF</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][24]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][24]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ECN</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][23]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][23]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota ENC</th>
                                <td class="align-middle py-0 font-size-11 <?php if($resultado_registros_monitoreo[0][22]){echo 'aceptado';}else{echo'rechazado';} ?>"><?php if($resultado_registros_monitoreo[0][22]){echo "<span class='fas fa-check-circle'></span>";}else{echo "<span class='fas fa-times-circle'></span>";} ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Nota General</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][21]); ?>%</td>
                            </tr>
                            <?php if($perfil_modulo!="Cliente"): ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Indicador</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][25]); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle py-0 font-size-11"><?php echo hbr($resultado_registros_monitoreo[0][11]); ?></td>
                            </tr>
                            <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Supervisor" OR $perfil_modulo=="Formador" OR $perfil_modulo=="Cliente"): ?>
                            <tr>
                                <th class="align-middle py-0 font-size-11 text-left" style="min-width: 110px;">Registrado por</th>
                                <td class="align-middle py-0 font-size-11"><?php echo h($resultado_registros_monitoreo[0][16]); ?></td>
                            </tr>
                            <?php endif; ?>
                        </thead>
                    </table>
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
                                    } else {
                                        echo "Observaciones para refutar/comentarios";
                                    }
                                ?>
                          </label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" rows="10" <?php if($_SESSION['registro_creado_cambio_estado']==1) { echo 'readonly'; } ?> required><?php if(isset($_POST["guardar_registro"])){ echo h($observaciones); } ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado_cambio_estado']==1): ?>
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
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>
