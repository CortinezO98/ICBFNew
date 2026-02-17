<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Monitoreos";

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

    // Helper selected tolerante a espacios (como informacion.php)
    if (!function_exists('selected_trim')) {
        function selected_trim($current, $option){
            return (trim((string)$current) === trim((string)$option)) ? 'selected' : '';
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Información General - Editar";
    $pagina = validar_input($_GET['pagina'] ?? '');
    $filtro_permanente = validar_input($_GET['id'] ?? '');
    $bandeja = validar_input(base64_decode($_GET['bandeja'] ?? '') ?: '');
    $id_registro = validar_input(base64_decode($_GET['reg'] ?? '') ?: '');

    // Inicializa variable tipo array
    $data_consulta=array();

    if(isset($_POST["guardar_registro"])){

        $fecha_gestion     = validar_input($_POST['fecha_gestion'] ?? '');
        $fecha_monitoreo   = validar_input($_POST['fecha_monitoreo'] ?? '');
        $duracion          = validar_input($_POST['duracion'] ?? '');
        $tipo_monitoreo    = validar_input($_POST['tipo_monitoreo'] ?? '');
        $skill_interaccion = validar_input($_POST['skill_interaccion'] ?? '');
        $tipo_gestion      = validar_input($_POST['tipo_gestion'] ?? '');
        $segmento          = validar_input($_POST['segmento'] ?? '');
        $id_sim            = validar_input($_POST['id_sim'] ?? '');
        $id_ani            = validar_input($_POST['id_ani'] ?? '');
        $indicador         = validar_input($_POST['indicador'] ?? '');
        $analista          = validar_input($_POST['analista'] ?? '');
        $analista_old      = validar_input($_POST['analista_old'] ?? '');
        $gcm_encuesta      = validar_input($_POST['gcm_encuesta'] ?? '');

        $consulta_string_supervisor_nuevo="SELECT `usu_supervisor` FROM `tb_administrador_usuario` WHERE `usu_id`=?";

        $consulta_registros_supervisor_nuevo = $enlace_db->prepare($consulta_string_supervisor_nuevo);
        $consulta_registros_supervisor_nuevo->bind_param("s", $analista);
        $consulta_registros_supervisor_nuevo->execute();
        $resultado_registros_supervisor_nuevo = $consulta_registros_supervisor_nuevo->get_result()->fetch_all(MYSQLI_NUM);

        $supervisor_nuevo = $resultado_registros_supervisor_nuevo[0][0] ?? '';

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo`
            SET `gcm_fecha_hora_gestion`=?, `gcm_fecha_hora_cierre`=?,`gcm_tipo_monitoreo`=?,`gcm_skill_interaccion`=?,
                `gcm_tipo_gestion`=?,`gcm_segmento`=?,`gcm_id_sim`=?,`gcm_id_ani`=?, `gcm_analista`=?, `gcm_responsable`=?,
                `gcm_encuesta`=?
            WHERE  `gcm_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param(
            'ssssssssssss',
            $fecha_gestion,
            $duracion,
            $tipo_monitoreo,
            $skill_interaccion,
            $tipo_gestion,
            $segmento,
            $id_sim,
            $id_ani,
            $analista,
            $supervisor_nuevo,
            $gcm_encuesta,
            $id_registro
        );

        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();

        if ($perfil_modulo=="Administrador") {
            // Prepara la sentencia
            $consulta_actualizar_indicador = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo`
                SET `gcm_aplica_indicador`=?, `gcm_fecha_monitoreo`=?
                WHERE `gcm_id`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_indicador->bind_param('sss', $indicador, $fecha_monitoreo, $id_registro);

            // Ejecuta sentencia preparada
            $consulta_actualizar_indicador->execute();
        }

        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";

            $consulta_string="SELECT
                    TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_analista`, TUA.`usu_nombres_apellidos`,
                    TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`,
                    TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`,
                    TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`,
                    TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`,
                    TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`,
                    `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`,
                    `gcm_fecha_monitoreo`, TM.`gcm_observaciones`, TMC.`gcm_encuesta`
                FROM `tb_gestion_calidad_monitoreo` AS TMC
                LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id`
                LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id`
                LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id`
                LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id`
                LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id`
                WHERE TMC.`gcm_id`=?";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            $consulta_registros->bind_param("s", $id_registro);
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

            $enc_estado  = $resultado_registros[0][23] ?? 0;
            $ecn_estado  = $resultado_registros[0][24] ?? 0;
            $ecuf_estado = $resultado_registros[0][25] ?? 0;
            $indic = $resultado_registros[0][26] ?? '';

            if ($analista!=$analista_old AND ($enc_estado==0 OR $ecn_estado==0 OR $ecuf_estado==0) AND $indic!='No-Cliente') {

                $consulta_string_supervisor="SELECT
                        TU.`usu_id`, TU.`usu_nombres_apellidos`, TL.`usu_id`, TL.`usu_nombres_apellidos`,
                        TL.`usu_correo_corporativo`, TU.`usu_correo_corporativo`
                    FROM `tb_administrador_usuario` AS TU
                    LEFT JOIN `tb_administrador_usuario` AS TL ON TU.`usu_supervisor`=TL.`usu_id`
                    WHERE TU.`usu_id`=?";

                $consulta_registros_supervisor = $enlace_db->prepare($consulta_string_supervisor);
                $consulta_registros_supervisor->bind_param("s", $analista);
                $consulta_registros_supervisor->execute();
                $resultado_registros_supervisor = $consulta_registros_supervisor->get_result()->fetch_all(MYSQLI_NUM);

                // PROGRAMAR NOTIFICACIÓN CORREO
                /*SE DEFINEN DESTINATARIOS*/
                $to_mail = ($resultado_registros_supervisor[0][5] ?? '');
                $to_name = ($resultado_registros_supervisor[0][1] ?? '');
                $cc_mail = ($resultado_registros_supervisor[0][4] ?? '');
                $cc_name = ($resultado_registros_supervisor[0][3] ?? '');

                $array_correo_destino['correos']['TO'] = $to_mail."|".$to_name;
                $array_correo_destino['correos']['CC'] = $cc_mail."|".$cc_name;

                $nota_correo_enc  = ($enc_estado)  ? 'CUMPLE' : 'INCUMPLE';
                $nota_correo_ecuf = ($ecuf_estado) ? 'CUMPLE' : 'INCUMPLE';
                $nota_correo_ecn  = ($ecn_estado)  ? 'CUMPLE' : 'INCUMPLE';

                /*SE ESTRUCTURA COTENIDO DE CORREO (se mantiene igual para no afectar funcionalidad)*/
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
                $nc_subject="[Reasignado] Monitoreo Calidad | ".$resultado_registros[0][0];
                $nc_body=str_replace("'", '"', $contenido_correo);
                $nc_embeddedimage_ruta="/var/www/icbf/html/images/firma-verde.png";
                $nc_embeddedimage_nombre="logo_firma_verde";
                $nc_embeddedimage_tipo="image/png";
                $nc_intentos="";
                $nc_eliminar="Si";
                $nc_estado_envio="Pendiente";
                $nc_fecha_envio="";
                $nc_usuario_registro=$_SESSION['usu_id'];

                /* =======================
                   Remediación SQLi: INSERT con prepared statement
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

                    for ($i=0; $i < 5; $i++) {
                        $ok = $stmt_notif->execute();
                        if ($ok) {
                            registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject);
                            break;
                        }
                    }
                    $stmt_notif->close();
                }
            }

        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT
            TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_analista`, TUA.`usu_nombres_apellidos`,
            TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`,
            TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`,
            TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`,
            TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`,
            TP.`ap_nombre_piloto`, TS.`usu_nombres_apellidos`, TMC.`gcm_matriz`, `gcm_nota_general`,
            `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_aplica_indicador`,
            `gcm_fecha_monitoreo`, TM.`gcm_observaciones`, TMC.`gcm_encuesta`
        FROM `tb_gestion_calidad_monitoreo` AS TMC
        LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id`
        LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id`
        LEFT JOIN `tb_administrador_usuario` AS TUA ON TMC.`gcm_analista`=TUA.`usu_id`
        LEFT JOIN `tb_administrador_piloto` AS TP ON TUA.`usu_piloto`=TP.`ap_id`
        LEFT JOIN `tb_administrador_usuario` AS TS ON TMC.`gcm_responsable`=TS.`usu_id`
        WHERE TMC.`gcm_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $filtro_perfil = "";
    if ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Formador" OR $perfil_modulo=="Cliente") {
        $filtro_perfil="";
    } elseif($perfil_modulo=="Supervisor"){
        $filtro_perfil=" AND (`usu_supervisor`=?)";
        array_push($data_consulta, $_SESSION["usu_id"]);
    }

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos`
        FROM `tb_administrador_usuario`
        WHERE (`usu_cargo_rol` LIKE '%Agente%' OR `usu_cargo_rol` LIKE '%Supervisor%') ".$filtro_perfil."
        ORDER BY `usu_nombres_apellidos`";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    if (count($data_consulta)>0) {
        $consulta_registros_analistas->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode($bandeja);

    // ====== Valores actuales (normalizados para selects) ======
    $sel_tipo_mon   = trim((string)($resultado_registros[0][6] ?? ''));
    $sel_skill      = (string)($resultado_registros[0][7] ?? '');
    $sel_tipo_gest  = (string)($resultado_registros[0][8] ?? '');
    $sel_segmento   = (string)($resultado_registros[0][9] ?? '');
    $sel_indicador  = (string)($resultado_registros[0][26] ?? '');
    $sel_encuesta   = trim((string)($resultado_registros[0][29] ?? ''));

    // Listas EXACTAS como informacion.php (para que tengas las mismas opciones)
    $op_tipo_monitoreo = ["En línea","Grabación","Al lado"];

    $op_skills = [
        "Click to Call",
        "Línea Verde",
        "Legal",
        "Línea 141",
        "Línea Anticorrupción",
        "Línea Nacional",
        "Mi Familia",
        "SNBF",
        "Violencia",
        "Correos",
        "Redes Sociales",
        "Escrito",
        "Chat",
        "WhatsApp",
        "Videollamada",
        "Apoyo Adopciones",
        "N/A"
    ];

    $op_tipo_gestion = [
        "Acompañamiento familiar",
        "Actuación ANNACC",
        "ACVN",
        "Anexo",
        "Orientación en derecho de familia",
        "Atención en Crisis",
        "Búsqueda de Orígenes",
        "Comunicación NNA",
        "Consulta",
        "Correos",
        "Encuestas",
        "Inobservancia",
        "IO",
        "IOT",
        "No Gestionó",
        "No Serio",
        "Observación",
        "Oficio",
        "Quejas",
        "RAVD",
        "Reclamo",
        "Reporte a Policía",
        "SRD",
        "Sugerencias",
        "TAE",
        "N/A",
        "Transferencia"
    ];

    $op_segmento = [
        "Aseguramiento",
        "Canal escrito",
        "Chat ICBF",
        "Conmutador",
        "Correos y Portales",
        "Encuestas",
        "Línea 141",
        "Línea Nacional",
        "Mi Familia",
        "Presencial",
        "Profesional Es Abogados",
        "Profesional ES Psicólogos",
        "Redes Sociales",
        "Video Llamada",
        "WhatsApp"
    ];

    // Indicador (mismas opciones que informacion.php + compatibilidad con valores viejos)
    $op_indicador_nuevo = ["Indicador","Encuesta","No- Curva","No- Supervisor","No- Gestor"];

?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php include("../config/configuracion_estilos.php"); ?>
    <link rel="stylesheet" href="../js/bootstrap-select/dist/css/bootstrap-select.min.css">
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
            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="matriz" class="m-0">Matriz</label>
                          <input type="text" class="form-control form-control-sm" name="matriz" id="matriz"
                                 value="<?php echo h_attr(($resultado_registros[0][1] ?? '').' ['.($resultado_registros[0][28] ?? '').']'); ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="analista" class="m-0">Analista</label>
                            <select class="selectpicker form-control form-control-sm font-size-11 padding-2" data-live-search="true" name="analista" id="analista" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_analistas); $i++): ?>
                                <option value="<?php echo h_attr($resultado_registros_analistas[$i][0] ?? ''); ?>"
                                        class="font-size-11"
                                        data-tokens="<?php echo h_attr(($resultado_registros_analistas[$i][0] ?? '').' '.($resultado_registros_analistas[$i][1] ?? '')); ?>"
                                        <?php if(($resultado_registros[0][2] ?? '')==($resultado_registros_analistas[$i][0] ?? '')){ echo "selected"; } ?>>
                                    <?php echo h($resultado_registros_analistas[$i][1] ?? ''); ?>
                                </option>
                              <?php endfor; ?>
                            </select>
                            <input type="hidden" name="analista_old" id="analista_old" value="<?php echo h_attr($resultado_registros[0][2] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_gestion" class="m-0">Fecha gestión</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_gestion" id="fecha_gestion"
                                 max="<?php echo h_attr(date('Y-m-d')); ?>"
                                 value="<?php echo h_attr($resultado_registros[0][4] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="duracion" class="m-0">Duración</label>
                          <input type="number" class="form-control form-control-sm" name="duracion" id="duracion" step="1" min="0" max="180"
                                 value="<?php echo h_attr($resultado_registros[0][5] ?? ''); ?>" required>
                        </div>
                    </div>

                    <?php if($perfil_modulo=="Administrador"): ?>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_monitoreo" class="m-0">Fecha monitoreo</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_monitoreo" id="fecha_monitoreo"
                                 value="<?php echo h_attr($resultado_registros[0][27] ?? ''); ?>" required>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_monitoreo" class="m-0">Tipo monitoreo</label>
                            <select class="form-control form-control-sm" name="tipo_monitoreo" id="tipo_monitoreo" required>
                              <option value="">Seleccione</option>
                              <?php
                                // Compat: si viene un valor viejo no listado, lo muestro
                                $en_lista = false;
                                foreach ($op_tipo_monitoreo as $o){ if(trim($sel_tipo_mon) === trim($o)) { $en_lista = true; break; } }
                                if ($sel_tipo_mon !== '' && !$en_lista){
                                    echo '<option value="'.h_attr($sel_tipo_mon).'" selected>'.h($sel_tipo_mon).'</option>';
                                }
                                foreach ($op_tipo_monitoreo as $opt){
                                    echo '<option value="'.h_attr($opt).'" '.selected_trim($sel_tipo_mon, $opt).'>'.h($opt).'</option>';
                                }
                              ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="skill_interaccion" class="m-0">Skill interacción</label>
                            <select class="form-control form-control-sm" name="skill_interaccion" id="skill_interaccion" required>
                              <option value="">Seleccione</option>
                              <?php
                                // Igual que informacion.php: lista completa + si hay valor viejo no listado, se conserva visible
                                $en_lista = false;
                                foreach ($op_skills as $o){ if(trim($sel_skill) === trim($o)) { $en_lista = true; break; } }
                                if ($sel_skill !== '' && !$en_lista){
                                    echo '<option value="'.h_attr($sel_skill).'" selected>'.h($sel_skill).'</option>';
                                }
                                foreach ($op_skills as $opt){
                                    $label = ($opt === "N/A") ? "No Aplica (N/A)" : $opt;
                                    echo '<option value="'.h_attr($opt).'" '.selected_trim($sel_skill, $opt).'>'.h($label).'</option>';
                                }
                              ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_gestion" class="m-0">Tipo gestión</label>
                            <select class="form-control form-control-sm" name="tipo_gestion" id="tipo_gestion" required>
                              <option value="">Seleccione</option>
                              <?php
                                $sel = $sel_tipo_gest;
                                $en_lista = false;
                                foreach ($op_tipo_gestion as $o){ if(trim($sel) === trim($o)) { $en_lista = true; break; } }
                                if ($sel !== '' && !$en_lista){
                                    echo '<option value="'.h_attr($sel).'" selected>'.h($sel).'</option>';
                                }
                                foreach ($op_tipo_gestion as $opt){
                                    echo '<option value="'.h_attr($opt).'" '.selected_trim($sel, $opt).'>'.h($opt).'</option>';
                                }
                              ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="segmento" class="m-0">Segmento</label>
                            <select class="form-control form-control-sm" name="segmento" id="segmento" required>
                              <option value="">Seleccione</option>
                              <?php
                                $sel = $sel_segmento;
                                $en_lista = false;
                                foreach ($op_segmento as $o){ if(trim($sel) === trim($o)) { $en_lista = true; break; } }
                                if ($sel !== '' && !$en_lista){
                                    echo '<option value="'.h_attr($sel).'" selected>'.h($sel).'</option>';
                                }
                                foreach ($op_segmento as $opt){
                                    echo '<option value="'.h_attr($opt).'" '.selected_trim($sel, $opt).'>'.h($opt).'</option>';
                                }
                              ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_sim" class="m-0">Id SIM</label>
                          <input type="text" class="form-control form-control-sm" name="id_sim" id="id_sim" maxlength="100"
                                 value="<?php echo h_attr($resultado_registros[0][10] ?? ''); ?>"
                                 onkeyup="consultar_idsim();" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_ani" class="m-0">ID/ANI</label>
                          <input type="text" class="form-control form-control-sm" name="id_ani" id="id_ani" maxlength="100"
                                 value="<?php echo h_attr($resultado_registros[0][11] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="gcm_encuesta" class="m-0">Encuesta</label>
                            <select class="form-control form-control-sm" name="gcm_encuesta" id="gcm_encuesta" required>
                              <option value="">Seleccione</option>
                              <option value="Si"        <?php echo ($sel_encuesta==="Si") ? "selected" : ""; ?>>Si</option>
                              <option value="No"        <?php echo ($sel_encuesta==="No") ? "selected" : ""; ?>>No</option>
                              <option value="No aplica" <?php echo ($sel_encuesta==="" || $sel_encuesta==="No aplica") ? "selected" : ""; ?>>No aplica</option>
                            </select>
                        </div>
                    </div>

                    <?php if ($perfil_modulo=="Administrador"): ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="indicador" class="m-0">Indicador</label>
                                <select class="form-control form-control-sm" name="indicador" id="indicador" required>
                                  <option value="">Seleccione</option>
                                  <?php
                                    // MISMAS opciones que informacion.php
                                    $en_lista = false;
                                    foreach ($op_indicador_nuevo as $o){ if(trim($sel_indicador) === trim($o)) { $en_lista = true; break; } }

                                    // Compat: si el registro tiene valor viejo (Si, No-Gestor, No-Formador, No-Curva Aprendizaje, etc.)
                                    if ($sel_indicador !== '' && !$en_lista){
                                        echo '<option value="'.h_attr($sel_indicador).'" selected>'.h($sel_indicador).'</option>';
                                    }

                                    foreach ($op_indicador_nuevo as $opt){
                                        echo '<option value="'.h_attr($opt).'" '.selected_trim($sel_indicador, $opt).'>'.h($opt).'</option>';
                                    }
                                  ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-12" id="coincidencias"></div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>

                            <?php if(!isset($_POST["guardar_registro"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>

                            <?php if(isset($_POST["guardar_registro"])): ?>
                                <a href="<?php echo h_attr($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
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
        // Igual que informacion.php: consulta de duplicados por ID SIM
        function consultar_idsim(){
            $.ajax({
                success: function(){
                    $("#coincidencias").load(
                        "gestion_calidad_monitoreo_informacion_duplicado.php?id=" + encodeURIComponent($("#id_sim").val())
                    );
                }
            });
        }
    </script>
</body>
</html>
