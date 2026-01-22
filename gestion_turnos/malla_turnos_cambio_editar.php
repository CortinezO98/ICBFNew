<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       CSRF (one-time token)
       - No cambia diseño
       - Solo valida POST legítimo
    ========================= */
    if (!function_exists('icbf_csrf_issue')) {
        function icbf_csrf_issue(string $scope = 'MALLA_TURNOS_CAMBIO_EDITAR'): array {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            if (!isset($_SESSION['_csrf_tokens']) || !is_array($_SESSION['_csrf_tokens'])) {
                $_SESSION['_csrf_tokens'] = [];
            }
            $idx = rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
            $tok = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $_SESSION['_csrf_tokens'][$scope][$idx] = ['t' => $tok, 'ts' => time()];
            return [$idx, $tok];
        }
    }

    if (!function_exists('icbf_csrf_verify')) {
        function icbf_csrf_verify(string $idx, string $tok, string $scope = 'MALLA_TURNOS_CAMBIO_EDITAR', int $ttlSeconds = 1800): bool {
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
            unset($_SESSION['_csrf_tokens'][$scope][$idx]); // one-time
            return $ok;
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Gestión Turnos | Cambio Turno - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $FechaInicio = base64_decode($_GET['fechainicio']);
    $filtro_operacion = base64_decode($_GET["operacion"]);

    if (!isset($_SESSION['mturno_cambioturno_editar'])) {
        $_SESSION['mturno_cambioturno_editar'] = 0;
    }

    $csrf_error = false;

    if(isset($_POST["guardar_registro"])){

        // Verifica CSRF (sin afectar UI)
        $csrf_idx = $_POST['_CSRF_INDEX'] ?? '';
        $csrf_tok = $_POST['_CSRF_TOKEN'] ?? '';
        if (!icbf_csrf_verify($csrf_idx, $csrf_tok, 'MALLA_TURNOS_CAMBIO_EDITAR')) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recarga e intenta de nuevo.', 0);</script>";
            $csrf_error = true;
        }

        $estado=validar_input($_POST['estado']);
        $estado_old=validar_input($_POST['estado_old']);

        if(!$csrf_error){
            if($_SESSION['mturno_cambioturno_editar']!=1){
                // Prepara la sentencia
                $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_control_turno_malla_cambio_turno` SET `cmtct_estado`=? WHERE `cmtct_id`=?");

                // Agrega variables a sentencia preparada
                $consulta_actualizar->bind_param('ss', $estado, $id_registro);

                // Ejecuta sentencia preparada
                $consulta_actualizar->execute();

                if (comprobarSentencia($enlace_db->info)) {
                    $consulta_string_detalle="SELECT `cmtct_id`, `cmtct_usuario_origen`, TUO.`usu_nombres_apellidos`, TCUO.`ac_nombre_campania`, `cmtct_uo_turno_anterior_inicio`, `cmtct_uo_turno_anterior_fin`, `cmtct_uo_turno_nuevo_inicio`, `cmtct_uo_turno_nuevo_fin`, `cmtct_usuario_destino`, TUD.`usu_nombres_apellidos`, TCUD.`ac_nombre_campania`, `cmtct_ud_turno_anterior_inicio`, `cmtct_ud_turno_anterior_fin`, `cmtct_ud_turno_nuevo_inicio`, `cmtct_ud_turno_nuevo_fin`, `cmtct_responsable`, TR.`usu_nombres_apellidos`, `cmtct_estado`, `cmtct_observaciones`, `cmtct_registro_fecha`, TUO.`usu_correo_corporativo`, TUD.`usu_correo_corporativo`, TR.`usu_correo_corporativo`
                        FROM `tb_control_turno_malla_cambio_turno`
                        LEFT JOIN `tb_administrador_usuario` AS TUO ON `tb_control_turno_malla_cambio_turno`.`cmtct_usuario_origen`=TUO.`usu_id`
                        LEFT JOIN `tb_administrador_usuario` AS TUD ON `tb_control_turno_malla_cambio_turno`.`cmtct_usuario_destino`=TUD.`usu_id`
                        LEFT JOIN `tb_administrador_usuario` AS TR ON `tb_control_turno_malla_cambio_turno`.`cmtct_responsable`=TR.`usu_id`
                        LEFT JOIN `tb_administrador_campania` AS TCUO ON TUO.`usu_campania`=TCUO.`ac_id`
                        LEFT JOIN `tb_administrador_campania` AS TCUD ON TUD.`usu_campania`=TCUD.`ac_id`
                        WHERE `cmtct_id`=?";

                    $consulta_registros_detalle = $enlace_db->prepare($consulta_string_detalle);
                    $consulta_registros_detalle->bind_param("s", $id_registro);
                    $consulta_registros_detalle->execute();
                    $resultado_registros_detalle = $consulta_registros_detalle->get_result()->fetch_all(MYSQLI_NUM);

                    // Guardas para no romper flujo en caso de registro inexistente
                    if (count($resultado_registros_detalle) == 0) {
                        $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡No fue posible cargar el detalle de la solicitud, verifique e intente nuevamente!', 0);</script>";
                    } else {

                        if ($estado=="Aprobado" AND $estado_old=="Pendiente") {
                            $mi_turno=$resultado_registros_detalle[0][1].substr($resultado_registros_detalle[0][4], 0, 10);
                            $turno_disponible=$resultado_registros_detalle[0][8].substr($resultado_registros_detalle[0][11], 0, 10);

                            $consulta_string_tprogramado_origen="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TU.`usu_supervisor`
                                FROM `tb_control_turno_malla`
                                LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id`
                                WHERE `cotm_id`=? ORDER BY `cotm_usuario`, `cotm_inicio`";
                            $consulta_registros_tprogramado_origen = $enlace_db->prepare($consulta_string_tprogramado_origen);
                            $consulta_registros_tprogramado_origen->bind_param('s', $mi_turno);
                            $consulta_registros_tprogramado_origen->execute();
                            $resultado_registros_tprogramado_origen = $consulta_registros_tprogramado_origen->get_result()->fetch_all(MYSQLI_NUM);

                            $consulta_string_tprogramado_destino="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`
                                FROM `tb_control_turno_malla`
                                LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id`
                                WHERE `cotm_id`=? ORDER BY `cotm_usuario`, `cotm_inicio`";
                            $consulta_registros_tprogramado_destino = $enlace_db->prepare($consulta_string_tprogramado_destino);
                            $consulta_registros_tprogramado_destino->bind_param('s', $turno_disponible);
                            $consulta_registros_tprogramado_destino->execute();
                            $resultado_registros_tprogramado_destino = $consulta_registros_tprogramado_destino->get_result()->fetch_all(MYSQLI_NUM);

                            if (count($resultado_registros_tprogramado_origen) > 0 && count($resultado_registros_tprogramado_destino) > 0) {

                                // Prepara la sentencia
                                $consulta_actualizar_turnos = $enlace_db->prepare("UPDATE `tb_control_turno_malla` SET `cotm_inicio`=?, `cotm_fin`=?, `cotm_duracion`=?, `cotm_jornada`=?, `cotm_estado`=? WHERE `cotm_id`=?");
                                $consulta_actualizar_turnos->bind_param('ssssss', $cotm_inicio, $cotm_fin, $cotm_duracion, $cotm_jornada, $cotm_estado, $cotm_id);

                                $cotm_inicio=$resultado_registros_tprogramado_origen[0][3];
                                $cotm_fin=$resultado_registros_tprogramado_origen[0][4];
                                $cotm_duracion=$resultado_registros_tprogramado_origen[0][5];
                                $cotm_jornada=$resultado_registros_tprogramado_origen[0][6];
                                $cotm_estado='Cambio turno';
                                $cotm_id=$turno_disponible;
                                $consulta_actualizar_turnos->execute();

                                $control_actualiza=0;
                                if (comprobarSentencia($enlace_db->info)) {
                                    $control_actualiza++;
                                }

                                $cotm_inicio=$resultado_registros_tprogramado_destino[0][3];
                                $cotm_fin=$resultado_registros_tprogramado_destino[0][4];
                                $cotm_duracion=$resultado_registros_tprogramado_destino[0][5];
                                $cotm_jornada=$resultado_registros_tprogramado_destino[0][6];
                                $cotm_estado='Cambio turno';
                                $cotm_id=$mi_turno;
                                $consulta_actualizar_turnos->execute();

                                if (comprobarSentencia($enlace_db->info)) {
                                    $control_actualiza++;
                                }

                                if ($control_actualiza==2) {
                                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                                    $_SESSION['mturno_cambioturno_editar']=1;
                                } else {
                                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                                }

                            } else {
                                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡No fue posible cargar los turnos para realizar el cambio, verifique e intente nuevamente!', 0);</script>";
                            }
                        }

                        if ($estado=="Cancelado" AND $estado_old=="Aprobado") {
                            $mi_turno=$resultado_registros_detalle[0][8].substr($resultado_registros_detalle[0][11], 0, 10);
                            $turno_disponible=$resultado_registros_detalle[0][1].substr($resultado_registros_detalle[0][4], 0, 10);

                            $consulta_string_tprogramado_origen="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TU.`usu_supervisor`
                                FROM `tb_control_turno_malla`
                                LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id`
                                WHERE `cotm_id`=? ORDER BY `cotm_usuario`, `cotm_inicio`";
                            $consulta_registros_tprogramado_origen = $enlace_db->prepare($consulta_string_tprogramado_origen);
                            $consulta_registros_tprogramado_origen->bind_param('s', $mi_turno);
                            $consulta_registros_tprogramado_origen->execute();
                            $resultado_registros_tprogramado_origen = $consulta_registros_tprogramado_origen->get_result()->fetch_all(MYSQLI_NUM);

                            $consulta_string_tprogramado_destino="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`
                                FROM `tb_control_turno_malla`
                                LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id`
                                WHERE `cotm_id`=? ORDER BY `cotm_usuario`, `cotm_inicio`";
                            $consulta_registros_tprogramado_destino = $enlace_db->prepare($consulta_string_tprogramado_destino);
                            $consulta_registros_tprogramado_destino->bind_param('s', $turno_disponible);
                            $consulta_registros_tprogramado_destino->execute();
                            $resultado_registros_tprogramado_destino = $consulta_registros_tprogramado_destino->get_result()->fetch_all(MYSQLI_NUM);

                            if (count($resultado_registros_tprogramado_origen) > 0 && count($resultado_registros_tprogramado_destino) > 0) {

                                // Prepara la sentencia
                                $consulta_actualizar_turnos = $enlace_db->prepare("UPDATE `tb_control_turno_malla` SET `cotm_inicio`=?, `cotm_fin`=?, `cotm_duracion`=?, `cotm_jornada`=?, `cotm_estado`=? WHERE `cotm_id`=?");
                                $consulta_actualizar_turnos->bind_param('ssssss', $cotm_inicio, $cotm_fin, $cotm_duracion, $cotm_jornada, $cotm_estado, $cotm_id);

                                $cotm_inicio=$resultado_registros_tprogramado_origen[0][3];
                                $cotm_fin=$resultado_registros_tprogramado_origen[0][4];
                                $cotm_duracion=$resultado_registros_tprogramado_origen[0][5];
                                $cotm_jornada=$resultado_registros_tprogramado_origen[0][6];
                                $cotm_estado='';
                                $cotm_id=$turno_disponible;
                                $consulta_actualizar_turnos->execute();

                                $control_actualiza=0;
                                if (comprobarSentencia($enlace_db->info)) {
                                    $control_actualiza++;
                                }

                                $cotm_inicio=$resultado_registros_tprogramado_destino[0][3];
                                $cotm_fin=$resultado_registros_tprogramado_destino[0][4];
                                $cotm_duracion=$resultado_registros_tprogramado_destino[0][5];
                                $cotm_jornada=$resultado_registros_tprogramado_destino[0][6];
                                $cotm_estado='';
                                $cotm_id=$mi_turno;
                                $consulta_actualizar_turnos->execute();

                                if (comprobarSentencia($enlace_db->info)) {
                                    $control_actualiza++;
                                }

                                if ($control_actualiza==2) {
                                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                                    $_SESSION['mturno_cambioturno_editar']=1;
                                } else {
                                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                                }

                            } else {
                                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡No fue posible cargar los turnos para realizar el cambio, verifique e intente nuevamente!', 0);</script>";
                            }
                        }

                        if ($estado=="Rechazado") {
                            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                            $_SESSION['mturno_cambioturno_editar']=1;
                        }

                        if ($estado!=$estado_old AND $_SESSION['mturno_cambioturno_editar']==1) {
                            // PROGRAMAR NOTIFICACIÓN CORREO
                            /*SE DEFINEN DESTINATARIOS*/
                            $array_correo_destino['correos']['TO']=$resultado_registros_detalle[0][20]."|".$resultado_registros_detalle[0][20].";".$resultado_registros_detalle[0][21]."|".$resultado_registros_detalle[0][21];
                            $array_correo_destino['correos']['CC']=$resultado_registros_detalle[0][22]."|".$resultado_registros_detalle[0][22];

                            /*SE ESTRUCTURA COTENIDO DE CORREO*/
                            $contenido_correo="<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>Cordial Saludo,<br><br>Se notifica cambio de estado para la solicitud de cambio de turno relacionada a continuación. Mayor información ingresando al siguiente link: <a href='http://52.188.206.38/' target='_blank'>ICBF-IQGIS</a></p><br>
                                <center>
                                <table style='width: 500px; font-size: 13px; font-family: Lato, Arial, sans-serif;'>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Estado</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][17]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Fecha</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('Y-m-d', strtotime($resultado_registros[0][4]))."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Doc. Solicitante</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][1]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Solicitante</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][2]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Campaña</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][3]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Anterior</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($resultado_registros[0][4])).'-'.date('H:i', strtotime($resultado_registros[0][5]))."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Nuevo</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($resultado_registros[0][6])).'-'.date('H:i', strtotime($resultado_registros[0][7]))."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Doc. Solicitado</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][8]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Solicitado</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][9]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Anterior</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($resultado_registros[0][11]))."-".date('H:i', strtotime($resultado_registros[0][12]))."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Turno Nuevo</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".date('H:i', strtotime($resultado_registros[0][13]))."-".date('H:i', strtotime($resultado_registros[0][14]))."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Responsable</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][16]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Observaciones</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][18]."</td>
                                    </tr>
                                    <tr>
                                        <td style='width: 30%;background-color: #069169; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Fecha Registro</td>
                                        <td style='width: 70%;padding: 5px 5px 5px 5px;background-color: #F2F2F2;'>".$resultado_registros[0][19]."</td>
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
                            $nc_subject="[".$estado."] Cambio de Turno";
                            $nc_body=str_replace("'", '"', $contenido_correo);
                            $nc_embeddedimage_ruta="/var/www/iqgis/html/images/firma-verde.png";
                            $nc_embeddedimage_nombre="logo_firma_verde";
                            $nc_embeddedimage_tipo="image/png";
                            $nc_intentos="";
                            $nc_eliminar="Si";
                            $nc_estado_envio="Pendiente";
                            $nc_fecha_envio="";
                            $nc_usuario_registro=$_SESSION['usu_id'];

                            // ✅ REMEDIACIÓN SQLi: INSERT notificación con sentencia preparada (antes estaba concatenado)
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
                        }
                    }
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
            }
        }
    }

    $consulta_string="SELECT `cmtct_id`, `cmtct_usuario_origen`, TUO.`usu_nombres_apellidos`, TCUO.`ac_nombre_campania`, `cmtct_uo_turno_anterior_inicio`, `cmtct_uo_turno_anterior_fin`, `cmtct_uo_turno_nuevo_inicio`, `cmtct_uo_turno_nuevo_fin`, `cmtct_usuario_destino`, TUD.`usu_nombres_apellidos`, TCUD.`ac_nombre_campania`, `cmtct_ud_turno_anterior_inicio`, `cmtct_ud_turno_anterior_fin`, `cmtct_ud_turno_nuevo_inicio`, `cmtct_ud_turno_nuevo_fin`, `cmtct_responsable`, TR.`usu_nombres_apellidos`, `cmtct_estado`, `cmtct_observaciones`, `cmtct_registro_fecha`
        FROM `tb_control_turno_malla_cambio_turno`
        LEFT JOIN `tb_administrador_usuario` AS TUO ON `tb_control_turno_malla_cambio_turno`.`cmtct_usuario_origen`=TUO.`usu_id`
        LEFT JOIN `tb_administrador_usuario` AS TUD ON `tb_control_turno_malla_cambio_turno`.`cmtct_usuario_destino`=TUD.`usu_id`
        LEFT JOIN `tb_administrador_usuario` AS TR ON `tb_control_turno_malla_cambio_turno`.`cmtct_responsable`=TR.`usu_id`
        LEFT JOIN `tb_administrador_campania` AS TCUO ON TUO.`usu_campania`=TCUO.`ac_id`
        LEFT JOIN `tb_administrador_campania` AS TCUD ON TUD.`usu_campania`=TCUD.`ac_id`
        WHERE `cmtct_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="malla_turnos_cambio.php?pagina=".$pagina."&id=".$filtro_permanente."&fechainicio=".base64_encode($FechaInicio)."&operacion=".base64_encode($filtro_operacion);

    // Emite token CSRF para el formulario (GET)
    [$csrf_form_index, $csrf_form_token] = icbf_csrf_issue('MALLA_TURNOS_CAMBIO_EDITAR');
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
            <div class="col-md-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Estado</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][17] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Fecha</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars(date('Y-m-d', strtotime($resultado_registros[0][4] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Doc. Solicitante</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][1] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Solicitante</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][2] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Campaña</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][3] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Turno Anterior</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars(date('H:i', strtotime($resultado_registros[0][4] ?? '')).'-'.date('H:i', strtotime($resultado_registros[0][5] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Turno Nuevo</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars(date('H:i', strtotime($resultado_registros[0][6] ?? '')).'-'.date('H:i', strtotime($resultado_registros[0][7] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Doc. Solicitado</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][8] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Solicitado</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][9] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Turno Anterior</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars(date('H:i', strtotime($resultado_registros[0][11] ?? '')).'-'.date('H:i', strtotime($resultado_registros[0][12] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Turno Nuevo</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars(date('H:i', strtotime($resultado_registros[0][13] ?? '')).'-'.date('H:i', strtotime($resultado_registros[0][14] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Responsable</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][16] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Observaciones</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][18] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle font-size-11 text-left" style="min-width: 110px;">Fecha Registro</th>
                                <td class="align-middle font-size-11"><?php echo htmlspecialchars($resultado_registros[0][19] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="col-md-4 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="estado" class="m-0">Estado</label>
                            <select class="form-control form-control-sm" name="estado" id="estado" <?php if($_SESSION['mturno_cambioturno_editar']==1) { echo 'disabled'; } ?> required>
                                <option value="">Seleccione</option>
                                <option value="Aprobado" <?php if(($resultado_registros[0][17] ?? '')=="Aprobado"){ echo "selected"; } ?>>Aprobado</option>
                                <?php if(($resultado_registros[0][17] ?? '')=="Pendiente"): ?>
                                    <option value="Rechazado" <?php if(($resultado_registros[0][17] ?? '')=="Rechazado"){ echo "selected"; } ?>>Rechazado</option>
                                <?php endif; ?>
                                <?php if(($resultado_registros[0][17] ?? '')=="Aprobado"): ?>
                                    <option value="Cancelado" <?php if(($resultado_registros[0][17] ?? '')=="Cancelado"){ echo "selected"; } ?>>Cancelado</option>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="estado_old" value="<?php echo htmlspecialchars($resultado_registros[0][17] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['mturno_cambioturno_editar']==1): ?>
                                <a href="<?php echo htmlspecialchars($ruta_cancelar_finalizar, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark float-right ml-1">Finalizar</a>
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
