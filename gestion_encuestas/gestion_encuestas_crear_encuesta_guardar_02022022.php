<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Guardar";
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    $pagina=validar_input($_GET['pagina']);
    $id_filtro=validar_input($_GET['id']);
    $estado_bandeja=validar_input($_GET['est']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    function registro_notificacion($enlace_db=null, $id_encuesta=null, $id_seccion=null, $id_seccion_complemento=null) {
        /*SE CONFIGURAN PARÁMETROS A REGISTRAR EN SISTEMA DE NOTIFICACIÓN*/
        
        $id_registro_alerta=$id_encuesta.$id_seccion;
        $nc_id_set_from="1";
        // $nc_address="mariostiv@hotmail.com|mariostiv@hotmail.com;";
        $nc_address="maria.benavides@icbf.gov.co|maria.benavides@icbf.gov.co;luis.torres@icbf.gov.co|luis.torres@icbf.gov.co;KarenV.Sanchez@icbf.gov.co|KarenV.Sanchez@icbf.gov.co;irma.medina@icbf.gov.co|irma.medina@icbf.gov.co;";
        $nc_cc="edna.nino@icbf.gov.co|edna.nino@icbf.gov.co";
        $nc_bcc="mariostiv@hotmail.com|mariostiv@hotmail.com";
        $nc_subject="";
        $nc_body="";
        $nc_embeddedimage_ruta="/var/www/iqgis/html/images/firma-verde.png";
        $nc_embeddedimage_nombre="logo_firma_verde";
        $nc_embeddedimage_tipo="image/png";
        $nc_intentos="";
        $nc_eliminar="Si";
        $nc_estado_envio="Pendiente";
        $nc_fecha_envio="";
        $nc_usuario_registro=$_SESSION["usu_id"];

        $consulta_notificacion = mysqli_query($enlace_db, "INSERT INTO `tb_gestion_encuesta_registro_notificacion`(`gern_id`, `gern_encuesta`, `gern_seccion`, `gern_seccion_complemento`, `gern_id_set_from`, `gern_address`, `gern_cc`, `gern_bcc`, `gern_subject`, `gern_body`, `gern_embeddedimage_ruta`, `gern_embeddedimage_nombre`, `gern_embeddedimage_tipo`, `gern_intentos`, `gern_eliminar`, `gern_estado_envio`, `gern_fecha_envio`, `gern_usuario_registro`) VALUES ('".$id_registro_alerta."','".$id_encuesta."','".$id_seccion."','".$id_seccion_complemento."','".$nc_id_set_from."','".$nc_address."','".$nc_cc."','".$nc_bcc."','".$nc_subject."','".$nc_body."','".$nc_embeddedimage_ruta."','".$nc_embeddedimage_nombre."','".$nc_embeddedimage_tipo."','".$nc_intentos."','".$nc_eliminar."','".$nc_estado_envio."','".$nc_fecha_envio."','".$nc_usuario_registro."') ON DUPLICATE KEY UPDATE `gern_encuesta`='".$id_encuesta."', `gern_seccion`='".$id_seccion."';");

        if ($consulta_notificacion) {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject.' ['.$id_encuesta.']');
        } else {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', $nc_subject.' ['.$id_encuesta.']');
        }
    }


    if ($_SESSION['registro_encuesta_exitoso']!=1) {
        $consulta_consecutivo = mysqli_query($enlace_db, "SELECT MAX(`ger_consecutivo`) FROM `tb_gestion_encuesta_registro`");
        $resultado_consecutivo = mysqli_fetch_all($consulta_consecutivo);
        $ultimo_consecutivo=explode('EN', $resultado_consecutivo[0][0]);
        $nuevo_consecutivo=$ultimo_consecutivo[1]+1;
        $inser_consecutivo="EN".str_pad($nuevo_consecutivo, 10, 0, STR_PAD_LEFT);

        if ($_SESSION['registro_configuracion_encuesta_estado_cierre_final'][count($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])-1]) {
            $estado_radicado_final="Cerrado";
        } else {
            $estado_radicado_final="Pendiente";
        }

        if ($_SESSION['registro_configuracion_encuesta_estado_contacto_final'][count($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])-1]) {
            $estado_contacto_final="1";
        } else {
            $estado_contacto_final="0";
        }

        if ($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][count($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])-1]) {
            $estado_efectivo_final="1";
        } else {
            $estado_efectivo_final="0";
        }

        // Prepara la sentencia
        $sentencia_insert_encuesta = $enlace_db->prepare("INSERT INTO `tb_gestion_encuesta_registro`(`ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_contactado`, `ger_efectivo`, `ger_observaciones`, `ger_registro_usuario`) VALUES (?,?,?,?,?,?,?)");
        
        // Agrega variables a sentencia preparada
        $sentencia_insert_encuesta->bind_param('sssssss', $inser_consecutivo, $_SESSION['registro_detalle_encuesta']['id'], $id_registro, $estado_contacto_final, $estado_efectivo_final, $_SESSION['registro_detalle_encuesta']['observaciones_cierre'], $_SESSION['usu_id']);
        
        // Ejecuta y evalua resultado de sentencia
        if ($sentencia_insert_encuesta->execute()) {
            $consulta_string_mejor_escenario="SELECT `gera_radicado`, `gera_contactado`, `gera_efectivo` FROM `tb_gestion_encuesta_radicado` WHERE `gera_radicado`=?";

            $consulta_registros_mejor_escenario = $enlace_db->prepare($consulta_string_mejor_escenario);
            $consulta_registros_mejor_escenario->bind_param("s", $id_registro);
            $consulta_registros_mejor_escenario->execute();
            $resultado_registros_mejor_escenario = $consulta_registros_mejor_escenario->get_result()->fetch_all(MYSQLI_NUM);

            if ($resultado_registros_mejor_escenario[0][1]!=1) {
                $estado_contacto_escenario=$estado_contacto_final;
            } else {
                $estado_contacto_escenario=$resultado_registros_mejor_escenario[0][1];
            }

            if ($resultado_registros_mejor_escenario[0][2]!=1) {
                $estado_efectivo_escenario=$estado_efectivo_final;
            } else {
                $estado_efectivo_escenario=$resultado_registros_mejor_escenario[0][2];
            }

            // Prepara la sentencia
            $consulta_actualizar_intento = $enlace_db->prepare("UPDATE `tb_gestion_encuesta_radicado` SET `gera_estado_gestion`=?, `gera_intentos`=`gera_intentos`+1, `gera_contactado`=?, `gera_efectivo`=?, `gera_ultima_observacion`=?, `gera_fecha_actualizacion`=? WHERE `gera_radicado`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_intento->bind_param('ssssss', $estado_radicado_final, $estado_contacto_escenario, $estado_efectivo_escenario, $_SESSION['registro_detalle_encuesta']['observaciones_cierre'], date('Y-m-d H:i:s'), $id_registro);
            
            // Ejecuta sentencia preparada
            $consulta_actualizar_intento->execute();

            $control_insert=0;
            $control_insert_pregunta=0;
            
            // Prepara la sentencia
            $sentencia_insert_data = $enlace_db->prepare("INSERT INTO `tb_gestion_encuesta_registro_data`(`gerd_encuesta`, `gerd_seccion`, `gerd_pregunta`, `gerd_respuesta`) VALUES (?,?,?,?)");
            
            for ($i=0; $i < count($_SESSION['registro_configuracion_encuesta_secciones']); $i++) {
                $id_seccion=$_SESSION['registro_configuracion_encuesta_secciones'][$i];
                for ($j=0; $j < count($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion]); $j++) { 
                    $id_pregunta=$_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion][$j];
                    $control_insert_pregunta++;
                    $respuesta_pregunta=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion]['preguntas'][$id_pregunta]['respuesta'];
                    if ($respuesta_pregunta!="") {
                        $respuesta_pregunta_guardar=$respuesta_pregunta;
                        // Agrega variables a sentencia preparada
                        $sentencia_insert_data->bind_param('ssss', $inser_consecutivo, $id_seccion, $id_pregunta, $respuesta_pregunta_guardar);

                        if ($sentencia_insert_data->execute()) {
                            $control_insert++;
                            if ($_SESSION['registro_configuracion_encuesta_estado_alerta_seccion'][$id_seccion] OR $_SESSION['registro_configuracion_encuesta_estado_alerta'][$id_pregunta]) {
                                $id_seccion_complemento="";
                                if ($_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion'][$id_seccion]!="") {
                                    $id_seccion_complemento=$_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion'][$id_seccion];
                                } elseif($_SESSION['registro_configuracion_encuesta_alerta_complemento'][$id_pregunta]!="") {
                                    $id_seccion_complemento=$_SESSION['registro_configuracion_encuesta_alerta_complemento'][$id_pregunta];
                                }
                                registro_notificacion($enlace_db, $inser_consecutivo, $id_seccion, $id_seccion_complemento);
                            }
                        }
                    } else {
                        $respuesta_pregunta_guardar="";
                        $control_insert++;
                    }
                }
            }

            if ($control_insert_pregunta==$control_insert) {
                $_SESSION['registro_encuesta_exitoso']=1;
                $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>¡Encuesta registrada exitosamente ".$inser_consecutivo."!</p>";
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
    } else {
        $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>¡Encuesta registrada exitosamente, haga clic en <b>Finalizar</b> para salir!</p>";
    }

    $ruta_cancelar_finalizar="gestion_encuestas.php?pagina=".$pagina."&id=".$id_filtro."&est=".$estado_bandeja;
    
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
    <div class="contenido"  style="background-color: #ede7f6;">
        <div class="row justify-content-center" id="elemento_1">
            <div class="col-md-6 py-2">
                
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form name="guardar_seccion" action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                            <div class="titulo-encuesta"><?php echo ($_SESSION['registro_detalle_encuesta']['estado']=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $_SESSION['registro_detalle_encuesta']['nombre']; ?></div>
                            <div class="descripcion-seccion"><?php echo $_SESSION['registro_detalle_encuesta']['descripcion']; ?></div>
                        </div>
                        <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 pt-2">
                            <?php echo $respuesta_accion; ?>
                        </div>
                        
                        <div class="col-md-12 p-0">
                            <div class="form-group">
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
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