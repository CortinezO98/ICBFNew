<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       Helpers de seguridad (local)
       ========================= */
    if (!function_exists('e')) {
        function e($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Encuestas | Guardar";
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');

    $pagina        = validar_input($_GET['pagina'] ?? '');
    $id_filtro     = validar_input($_GET['id'] ?? '');
    $estado_bandeja= validar_input($_GET['est'] ?? '');
    $id_registro   = validar_input(base64_decode($_GET['reg'] ?? ''));

    /**
     * Registro de notificación (REMEDIADO: SQLi + validaciones básicas).
     * Mantiene la lógica original (mismos campos y tablas).
     */
    function registro_notificacion($enlace_db=null, $id_encuesta=null, $id_seccion=null, $id_seccion_complemento=null) {
        $modulo_plataforma="Encuestas";

        if (!$enlace_db instanceof mysqli) {
            return;
        }

        // Normaliza a string (DB recibe strings)
        $id_encuesta = (string)$id_encuesta;
        $id_seccion  = (string)$id_seccion;
        $id_seccion_complemento = (string)$id_seccion_complemento;

        /*SE CONFIGURAN PARÁMETROS A REGISTRAR EN SISTEMA DE NOTIFICACIÓN*/
        $consulta_string_destinatarios="SELECT `gad_id`, `gad_filtro`, `gad_correos_to`, `gad_correos_cc`
                                        FROM `tb_gestion_alertas_destinatarios`
                                        WHERE `gad_filtro`='genera_alerta'";

        $consulta_registros_destinatarios = $enlace_db->prepare($consulta_string_destinatarios);
        if (!$consulta_registros_destinatarios) {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', 'Prepare destinatarios falló');
            return;
        }

        $consulta_registros_destinatarios->execute();
        $resultado_registros_destinatarios = $consulta_registros_destinatarios->get_result()->fetch_all(MYSQLI_NUM);
        $consulta_registros_destinatarios->close();

        if (empty($resultado_registros_destinatarios) || !isset($resultado_registros_destinatarios[0])) {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', 'Sin destinatarios configura (genera_alerta)');
            return;
        }

        $id_registro_alerta = $id_encuesta.$id_seccion;
        $nc_id_set_from="2";

        $destino_to = explode(";", (string)$resultado_registros_destinatarios[0][2]);
        $nc_address = '';
        for ($j=0; $j < count($destino_to); $j++) {
            $correo = trim($destino_to[$j]);
            if ($correo !== '') {
                $nc_address .= $correo.'|'.$correo.';';
            }
        }

        $destino_cc = explode(";", (string)$resultado_registros_destinatarios[0][3]);
        $nc_cc = '';
        for ($j=0; $j < count($destino_cc); $j++) {
            $correo = trim($destino_cc[$j]);
            if ($correo !== '') {
                $nc_cc .= $correo.'|'.$correo.';';
            }
        }

        $nc_bcc="";
        $nc_subject="";
        $nc_body="";
        $nc_embeddedimage_ruta="/var/www/html/images/firma-verde.png";
        $nc_embeddedimage_nombre="logo_firma_verde";
        $nc_embeddedimage_tipo="image/png";
        $nc_intentos="";
        $nc_eliminar="Si";
        $nc_estado_envio="Pendiente";
        $nc_fecha_envio="";
        $nc_usuario_registro = (string)($_SESSION["usu_id"] ?? '');

        // REMEDIADO: Insert con prepared statement + ON DUPLICATE
        $sql = "INSERT INTO `tb_gestion_encuesta_registro_notificacion`
                    (`gern_id`, `gern_encuesta`, `gern_seccion`, `gern_seccion_complemento`, `gern_id_set_from`,
                     `gern_address`, `gern_cc`, `gern_bcc`, `gern_subject`, `gern_body`,
                     `gern_embeddedimage_ruta`, `gern_embeddedimage_nombre`, `gern_embeddedimage_tipo`,
                     `gern_intentos`, `gern_eliminar`, `gern_estado_envio`, `gern_fecha_envio`, `gern_usuario_registro`)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE
                    `gern_encuesta`=VALUES(`gern_encuesta`),
                    `gern_seccion`=VALUES(`gern_seccion`),
                    `gern_seccion_complemento`=VALUES(`gern_seccion_complemento`)";

        $stmt = $enlace_db->prepare($sql);
        if (!$stmt) {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', 'Prepare insert notificación falló');
            return;
        }

        $stmt->bind_param(
            "ssssssssssssssssss",
            $id_registro_alerta,
            $id_encuesta,
            $id_seccion,
            $id_seccion_complemento,
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

        if ($stmt->execute()) {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject.' ['.$id_encuesta.']');
        } else {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', $nc_subject.' ['.$id_encuesta.']');
        }
        $stmt->close();
    }

    /* =========================
       Lógica principal (igual que original)
       ========================= */

    if (($_SESSION['registro_encuesta_exitoso'] ?? 0) != 1) {

        // Consecutivo (mantengo lógica original; se recomienda migrar a AUTO_INCREMENT/tabla consecutivos,
        // pero aquí NO se cambia funcionalidad).
        $consulta_consecutivo = mysqli_query($enlace_db, "SELECT MAX(`ger_consecutivo`) FROM `tb_gestion_encuesta_registro`");
        $resultado_consecutivo = mysqli_fetch_all($consulta_consecutivo);
        $max_consec = $resultado_consecutivo[0][0] ?? 'EN0000000000';
        $ultimo_consecutivo = explode('EN', (string)$max_consec);
        $nuevo_consecutivo = (int)($ultimo_consecutivo[1] ?? 0) + 1;
        $inser_consecutivo = "EN".str_pad((string)$nuevo_consecutivo, 10, "0", STR_PAD_LEFT);

        if (!isset($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])) {
            $_SESSION['registro_configuracion_encuesta_estado_cierre_final']=array();
        }
        $idx_cierre = count($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])-1;
        $estado_radicado_final = (!empty($_SESSION['registro_configuracion_encuesta_estado_cierre_final'][$idx_cierre])) ? "Cerrado" : "Pendiente";

        if (!isset($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])) {
            $_SESSION['registro_configuracion_encuesta_estado_contacto_final']=array();
        }
        $idx_contacto = count($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])-1;
        $estado_contacto_final = (!empty($_SESSION['registro_configuracion_encuesta_estado_contacto_final'][$idx_contacto])) ? "1" : "0";

        if (!isset($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])) {
            $_SESSION['registro_configuracion_encuesta_estado_efectivo_final']=array();
        }
        $idx_efectivo = count($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])-1;
        $estado_efectivo_final = (!empty($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][$idx_efectivo])) ? "1" : "0";

        // Sanitiza lo que viene de sesión (XSS en salida; aquí es para DB, pero evitamos nulls inesperados)
        $encuesta_id = (string)($_SESSION['registro_detalle_encuesta']['id'] ?? '');
        $observ_cierre = (string)($_SESSION['registro_detalle_encuesta']['observaciones_cierre'] ?? '');
        $usu_id = (string)($_SESSION['usu_id'] ?? '');

        // Prepara la sentencia
        $sentencia_insert_encuesta = $enlace_db->prepare(
            "INSERT INTO `tb_gestion_encuesta_registro`
             (`ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_contactado`, `ger_efectivo`, `ger_observaciones`, `ger_registro_usuario`)
             VALUES (?,?,?,?,?,?,?)"
        );

        if (!$sentencia_insert_encuesta) {
            $respuesta_accion = "<p class='alert alert-danger p-1 mb-2'>¡Problemas al crear la encuesta!</p>";
        } else {
            // Agrega variables a sentencia preparada
            $sentencia_insert_encuesta->bind_param(
                'sssssss',
                $inser_consecutivo,
                $encuesta_id,
                $id_registro,
                $estado_contacto_final,
                $estado_efectivo_final,
                $observ_cierre,
                $usu_id
            );

            // Ejecuta y evalua resultado de sentencia
            if ($sentencia_insert_encuesta->execute()) {

                // Mejor escenario
                $consulta_string_mejor_escenario="SELECT `gera_radicado`, `gera_contactado`, `gera_efectivo`
                                                 FROM `tb_gestion_encuesta_radicado`
                                                 WHERE `gera_radicado`=?";

                $consulta_registros_mejor_escenario = $enlace_db->prepare($consulta_string_mejor_escenario);
                $consulta_registros_mejor_escenario->bind_param("s", $id_registro);
                $consulta_registros_mejor_escenario->execute();
                $resultado_registros_mejor_escenario = $consulta_registros_mejor_escenario->get_result()->fetch_all(MYSQLI_NUM);
                $consulta_registros_mejor_escenario->close();

                $mejor_contactado = $resultado_registros_mejor_escenario[0][1] ?? 0;
                $mejor_efectivo   = $resultado_registros_mejor_escenario[0][2] ?? 0;

                $estado_contacto_escenario = ((int)$mejor_contactado !== 1) ? $estado_contacto_final : (string)$mejor_contactado;
                $estado_efectivo_escenario = ((int)$mejor_efectivo   !== 1) ? $estado_efectivo_final : (string)$mejor_efectivo;

                // Actualiza intento
                $consulta_actualizar_intento = $enlace_db->prepare(
                    "UPDATE `tb_gestion_encuesta_radicado`
                     SET `gera_estado_gestion`=?,
                         `gera_intentos`=`gera_intentos`+1,
                         `gera_contactado`=?,
                         `gera_efectivo`=?,
                         `gera_ultima_observacion`=?,
                         `gera_fecha_actualizacion`=?
                     WHERE `gera_radicado`=?"
                );
                if ($consulta_actualizar_intento) {
                    $marca_temporal = date('Y-m-d H:i:s');
                    $consulta_actualizar_intento->bind_param(
                        'ssssss',
                        $estado_radicado_final,
                        $estado_contacto_escenario,
                        $estado_efectivo_escenario,
                        $observ_cierre,
                        $marca_temporal,
                        $id_registro
                    );
                    $consulta_actualizar_intento->execute();
                    $consulta_actualizar_intento->close();
                }

                $control_insert=0;
                $control_insert_pregunta=0;

                // Prepara la sentencia (data preguntas)
                $sentencia_insert_data = $enlace_db->prepare(
                    "INSERT INTO `tb_gestion_encuesta_registro_data`
                     (`gerd_encuesta`, `gerd_seccion`, `gerd_pregunta`, `gerd_respuesta`)
                     VALUES (?,?,?,?)"
                );

                if (!isset($_SESSION['registro_configuracion_encuesta_secciones'])) {
                    $_SESSION['registro_configuracion_encuesta_secciones']=array();
                }
                if (!isset($_SESSION['registro_configuracion_encuesta_secciones_preguntas'])) {
                    $_SESSION['registro_configuracion_encuesta_secciones_preguntas']=array();
                }
                if (!isset($_SESSION['registro_configuracion_encuesta_detalle'])) {
                    $_SESSION['registro_configuracion_encuesta_detalle']=array();
                }
                if (!isset($_SESSION['registro_configuracion_encuesta_estado_alerta_seccion'])) {
                    $_SESSION['registro_configuracion_encuesta_estado_alerta_seccion']=array();
                }
                if (!isset($_SESSION['registro_configuracion_encuesta_estado_alerta'])) {
                    $_SESSION['registro_configuracion_encuesta_estado_alerta']=array();
                }
                if (!isset($_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion'])) {
                    $_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion']=array();
                }
                if (!isset($_SESSION['registro_configuracion_encuesta_alerta_complemento'])) {
                    $_SESSION['registro_configuracion_encuesta_alerta_complemento']=array();
                }

                if ($sentencia_insert_data) {
                    for ($i=0; $i < count($_SESSION['registro_configuracion_encuesta_secciones']); $i++) {
                        $id_seccion = (string)$_SESSION['registro_configuracion_encuesta_secciones'][$i];

                        if (!isset($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion])) {
                            $_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion]=array();
                        }

                        for ($j=0; $j < count($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion]); $j++) {
                            $id_pregunta = (string)$_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion][$j];
                            $control_insert_pregunta++;

                            $respuesta_pregunta = (string)($_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion]['preguntas'][$id_pregunta]['respuesta'] ?? '');

                            if ($respuesta_pregunta !== "") {
                                $respuesta_pregunta_guardar = $respuesta_pregunta;

                                $sentencia_insert_data->bind_param('ssss', $inser_consecutivo, $id_seccion, $id_pregunta, $respuesta_pregunta_guardar);

                                if ($sentencia_insert_data->execute()) {
                                    $control_insert++;

                                    $alerta_seccion = !empty($_SESSION['registro_configuracion_encuesta_estado_alerta_seccion'][$id_seccion]);
                                    $alerta_preg    = !empty($_SESSION['registro_configuracion_encuesta_estado_alerta'][$id_pregunta]);

                                    if ($alerta_seccion || $alerta_preg) {
                                        $id_seccion_complemento = "";

                                        $comp_seccion = (string)($_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion'][$id_seccion] ?? '');
                                        $comp_preg    = (string)($_SESSION['registro_configuracion_encuesta_alerta_complemento'][$id_pregunta] ?? '');

                                        if ($comp_seccion !== "") {
                                            $id_seccion_complemento = $comp_seccion;
                                        } elseif ($comp_preg !== "") {
                                            $id_seccion_complemento = $comp_preg;
                                        }

                                        registro_notificacion($enlace_db, $inser_consecutivo, $id_seccion, $id_seccion_complemento);
                                    }
                                }
                            } else {
                                // Mantiene contador como en tu lógica original
                                $control_insert++;
                            }
                        }
                    }
                    $sentencia_insert_data->close();
                }

                if ($control_insert_pregunta == $control_insert) {
                    $_SESSION['registro_encuesta_exitoso']=1;
                    // XSS: escape del consecutivo en salida
                    $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>¡Encuesta registrada exitosamente ".e($inser_consecutivo)."!</p>";

                    unset($_SESSION['registro_configuracion_encuesta_secciones']);
                    unset($_SESSION['registro_configuracion_encuesta_secciones_preguntas']);
                    unset($_SESSION['registro_configuracion_encuesta_detalle']);
                    unset($_SESSION['registro_configuracion_encuesta_navegacion']);
                    unset($_SESSION['registro_configuracion_encuesta_estado_cierre']);
                    unset($_SESSION['registro_configuracion_encuesta_estado_contacto']);
                    unset($_SESSION['registro_configuracion_encuesta_estado_efectivo']);
                    unset($_SESSION['registro_configuracion_encuesta_estado_alerta']);
                }
            } else {
                $respuesta_accion = "<p class='alert alert-danger p-1 mb-2'>¡Problemas al crear la encuesta!</p>";
            }

            $sentencia_insert_encuesta->close();
        }
    } else {
        $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>¡Encuesta registrada exitosamente, haga clic en <b>Finalizar</b> para salir!</p>";
    }

    // XSS: sanitiza querystring en href
    $ruta_cancelar_finalizar = "gestion_encuestas.php?pagina=".urlencode((string)$pagina)."&id=".urlencode((string)$id_filtro)."&est=".urlencode((string)$estado_bandeja);
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
    <div class="contenido"  style="background-color: #ede7f6;">
        <div class="row justify-content-center" id="elemento_1">
            <div class="col-md-6 py-2"></div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <form name="guardar_seccion" action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                            <div class="titulo-encuesta">
                                <?php echo (($_SESSION['registro_detalle_encuesta']['estado'] ?? '')=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?>
                                <?php echo e($_SESSION['registro_detalle_encuesta']['nombre'] ?? ''); ?>
                            </div>
                            <div class="descripcion-seccion"><?php echo e($_SESSION['registro_detalle_encuesta']['descripcion'] ?? ''); ?></div>
                        </div>

                        <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 pt-2">
                            <?php echo $respuesta_accion; ?>
                        </div>

                        <div class="col-md-12 p-0">
                            <div class="form-group">
                                <a href="<?php echo e($ruta_cancelar_finalizar); ?>" class="btn btn-dark float-right">Finalizar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>
