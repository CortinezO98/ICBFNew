<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calibración-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Monitoreos | Guardar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_fecha_incorporacion`, `usu_piloto` FROM `tb_administrador_usuario` WHERE `usu_id`=?";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    $consulta_registros_analistas->bind_param("s", $_SESSION["mon_informacion"]["analista"]);
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    if(isset($_POST["guardar_monitoreo"])){
        $gcm_matriz=$_SESSION["mon_informacion"]["matriz"];
        $gcm_analista=$_SESSION["mon_informacion"]["analista"];
        $gcm_responsable=$_SESSION["mon_informacion"]["responsable"];
        $gcm_fecha_hora_gestion=$_SESSION["mon_informacion"]["fecha_gestion"];
        $duracion=$_SESSION["mon_informacion"]["duracion"];
        $gcm_fecha_monitoreo=$_SESSION["mon_informacion"]["fecha_monitoreo"];
        $gcm_tipo_monitoreo=$_SESSION["mon_informacion"]["tipo_monitoreo"];
        $gcm_skill_interaccion=$_SESSION["mon_informacion"]["skill_interaccion"];
        $gcm_tipo_gestion=$_SESSION["mon_informacion"]["tipo_gestion"];
        $gcm_segmento=$_SESSION["mon_informacion"]["segmento"];
        $gcm_id_sim=$_SESSION["mon_informacion"]["id_sim"];
        $gcm_id_ani=$_SESSION["mon_informacion"]["id_ani"];
        $gcm_aplica_indicador=$_SESSION["mon_informacion"]["indicador"];
        $gcm_encuesta=$_SESSION["mon_informacion"]["encuesta"];


        $_SESSION["mon_informacion"]["observaciones"]=validar_input($_POST['observaciones']);
        $gcm_observaciones_monitoreo=$_SESSION["mon_informacion"]["observaciones"];

        $items_matriz=$_POST['id_campos'];
        $grupo_peso=$_POST['grupo_peso'];
        $peso_nota=$_POST['peso_nota'];
        $tipo_error=$_POST['tipo_error'];
        $peso_sla_ecuf=$_POST['peso_sla_ecuf'];
        $peso_sla_enc=$_POST['peso_sla_enc'];
        $peso_sla_ecn=$_POST['peso_sla_ecn'];

        $grupos_tipo_error=array_values(array_unique($tipo_error));

        for ($i=0; $i < count($items_matriz); $i++) { 
            if ($grupo_peso[$i]!="") {
                $grupos_items_nota['G-'.$grupo_peso[$i]]=100;
                $grupos_peso_id[]='G-'.$grupo_peso[$i];
            }

            if (isset($_POST['respuesta_'.$items_matriz[$i]])) {
                $item_respuesta[]=$_POST['respuesta_'.$items_matriz[$i]];
                $item_id_respuesta[$items_matriz[$i]]=$_POST['respuesta_'.$items_matriz[$i]];
            } else {
                $item_respuesta[]="";
                $item_id_respuesta[$items_matriz[$i]]="";
            }

            if (isset($_POST['comentario_'.$items_matriz[$i]])) {
                $item_comentario[]=$_POST['comentario_'.$items_matriz[$i]];
            } else {
                $item_comentario[]="";
            }
        }

        $grupos_peso_id=array_values(array_unique($grupos_peso_id));

        for ($i=0; $i < count($items_matriz); $i++) { 
            if ($grupo_peso[$i]=="") {
                if ($item_respuesta[$i]=="No") {
                    $item_calificable_tipo_error[$tipo_error[$i]][$items_matriz[$i]]=0;
                } else {
                    $item_calificable_tipo_error[$tipo_error[$i]][$items_matriz[$i]]=$peso_nota[$i];
                }
            } else {
                $item_calificable_tipo_error[$tipo_error[$i]]['G-'.$grupo_peso[$i]]=$peso_nota[$i];
            }

            if ($grupo_peso[$i]!="" and $item_respuesta[$i]=="No") {
                $grupos_items_nota['G-'.$grupo_peso[$i]]=0;
            }
        }

        for ($i=0; $i < count($grupos_peso_id); $i++) { 
            for ($j=0; $j < count($tipo_error); $j++) { 
                if (isset($item_calificable_tipo_error[$tipo_error[$j]][$grupos_peso_id[$i]])) {
                    if ($grupos_items_nota[$grupos_peso_id[$i]]==0) {
                        $item_calificable_tipo_error[$tipo_error[$j]][$grupos_peso_id[$i]]=0;
                    }
                }
            }
        }
        
        if (isset($item_calificable_tipo_error['ENC'])) {
            if (count($item_calificable_tipo_error['ENC'])>0) {
                $gcm_nota_enc=array_sum($item_calificable_tipo_error['ENC']);
            } else {
                $gcm_nota_enc="NA";
            }
        } else {
            $gcm_nota_enc="NA";
        }
                      
               
        if (isset($item_calificable_tipo_error['ECU'])) {
            if (count($item_calificable_tipo_error['ECU'])>0) {
                $gcm_nota_ecuf=array_sum($item_calificable_tipo_error['ECU']);
            } else {
                $gcm_nota_ecuf="NA";
            }
        } else {
            $gcm_nota_ecuf="NA";
        }

        if (isset($item_calificable_tipo_error['ECN'])) {
            if (count($item_calificable_tipo_error['ECN'])>0) {
                $gcm_nota_ecn=array_sum($item_calificable_tipo_error['ECN']);
            } else {
                $gcm_nota_ecn="NA";
            }
        } else {
            $gcm_nota_ecn="NA";
        }

        $nota_general=0;

        if ($gcm_nota_enc==="NA") {
            $control_estado_enc=1;
        } else {
            $nota_general+=$gcm_nota_enc;
            if ($gcm_nota_enc>=$peso_sla_enc) {
                $control_estado_enc=1;
            } else {
                $control_estado_enc=0;
            }
        }

        if ($gcm_nota_ecuf==="NA") {
            $control_estado_ecuf=1;
        } else {
            $nota_general+=$gcm_nota_ecuf;
            if ($gcm_nota_ecuf>=$peso_sla_ecuf) {
                $control_estado_ecuf=1;
            } else {
                $control_estado_ecuf=0;
            }
        }

        if ($gcm_nota_ecn==="NA") {
            $control_estado_ecn=1;
        } else {
            $nota_general+=$gcm_nota_ecn;
            if ($gcm_nota_ecn>=$peso_sla_ecn) {
                $control_estado_ecn=1;
            } else {
                $control_estado_ecn=0;
            }
        }


        if ($control_estado_enc==1 AND $control_estado_ecuf==1 AND $control_estado_ecn==1) {
            $gcm_estado="Aceptado";
        } else {
            $gcm_estado="Pendiente";
        }

        if ($resultado_registros_analistas[0][3]=='Si') {
            $fecha_aplica_indicador=date('Y-m-d', strtotime('+3 month', strtotime($resultado_registros_analistas[0][2])));
        } else {
            $fecha_aplica_indicador=date('Y-m-d', strtotime('+2 month', strtotime($resultado_registros_analistas[0][2])));
        }

        if ($perfil_modulo=="Cliente") {
            $gcm_aplica_indicador='No-Cliente';
        } elseif (date('Y-m-d', strtotime($gcm_fecha_hora_gestion))<=$fecha_aplica_indicador) {
            $gcm_aplica_indicador='No-Curva Aprendizaje';
        } elseif ($perfil_modulo=="Supervisor") {
            $gcm_aplica_indicador='No-Supervisor';
        } elseif ($perfil_modulo=="Formador") {
            $gcm_aplica_indicador='No-Formador';
        } elseif (date('Y-m-d', strtotime($gcm_fecha_hora_gestion))>$fecha_aplica_indicador) {
            if ($gcm_aplica_indicador=='No-Gestor') {
                $gcm_aplica_indicador=$gcm_aplica_indicador;
            } else {
                $gcm_aplica_indicador='Si';
            }
        }
        
        if($_SESSION['monitoreo_creado']!=1){ 
            $consulta_consecutivo = mysqli_query($enlace_db, "SELECT MAX(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo`");
            $resultado_consecutivo = mysqli_fetch_all($consulta_consecutivo);
            $ultimo_consecutivo=explode('MC', $resultado_consecutivo[0][0]);
            $nuevo_consecutivo=$ultimo_consecutivo[1]+1;
            $inser_consecutivo="MC".str_pad($nuevo_consecutivo, 10, 0, STR_PAD_LEFT);

            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calibracion_monitoreo`(`gcm_id`, `gcm_matriz`, `gcm_analista`, `gcm_responsable`, `gcm_fecha_hora_gestion`, `gcm_fecha_hora_cierre`, `gcm_tipo_monitoreo`, `gcm_skill_interaccion`, `gcm_tipo_gestion`, `gcm_segmento`, `gcm_id_sim`, `gcm_id_ani`, `gcm_observaciones_monitoreo`, `gcm_nota_enc`, `gcm_nota_ecn`, `gcm_nota_ecuf`, `gcm_nota_general`, `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`, `gcm_estado`, `gcm_aplica_indicador`, `gcm_encuesta`, `gcm_fecha_monitoreo`, `gcm_registro_usuario`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sssssssssssssssssssssssss', $inser_consecutivo, $gcm_matriz, $gcm_analista, $gcm_responsable, $gcm_fecha_hora_gestion, $duracion, $gcm_tipo_monitoreo, $gcm_skill_interaccion, $gcm_tipo_gestion, $gcm_segmento, $gcm_id_sim, $gcm_id_ani, $gcm_observaciones_monitoreo, $gcm_nota_enc, $gcm_nota_ecn, $gcm_nota_ecuf, $nota_general, $control_estado_enc, $control_estado_ecn, $control_estado_ecuf, $gcm_estado, $gcm_aplica_indicador, $gcm_encuesta, $gcm_fecha_monitoreo, $_SESSION['usu_id']);
            // Ejecuta y evalua resultado de sentencia
            if ($sentencia_insert->execute()) {
                $_SESSION['id_monitoreo']=$inser_consecutivo;
                $control_insert=0;
                
                // Prepara la sentencia
                $sentencia_insert_calificaciones = $enlace_db->prepare("INSERT INTO `tb_gestion_calibracion_monitoreo_calificaciones`(`gcmc_monitoreo`, `gcmc_pregunta`, `gcmc_respuesta`, `gcmc_afectaciones`, `gcmc_comentarios`) VALUES (?,?,?,?,?)");
                
                for ($i=0; $i < count($items_matriz); $i++) {
                    $item_matriz_pregunta=$items_matriz[$i];
                    $afectaciones="";
                    $respuesta_item=$item_respuesta[$i];
                    $comentarios_insert=$item_comentario[$i];
                    
                    // Agrega variables a sentencia preparada
                    $sentencia_insert_calificaciones->bind_param('sssss', $inser_consecutivo, $item_matriz_pregunta, $respuesta_item, $afectaciones, $comentarios_insert);

                    if ($sentencia_insert_calificaciones->execute()) {
                        $control_insert++;
                    }
                }

                if (count($items_matriz)==$control_insert) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('Monitoreo creado exitosamente!', 0);</script>";
                    $_SESSION['monitoreo_creado']=1;

                    // $documento_cantidad=0;
                    // $documento_registrados=0;
                    // foreach($_FILES["documento"]['tmp_name'] as $key => $tmp_name) {
                    //     if ($_FILES["documento"]["name"][$key]!="") {
                    //         $documento_cantidad++;
                    //         $ruta_guardar="storage/".$inser_consecutivo;
                    //         if (!file_exists($ruta_guardar)) {
                    //             mkdir($ruta_guardar, 0777, true);
                    //         }
                            
                    //         $archivo_extension = strtolower(pathinfo($_FILES['documento']['name'][$key], PATHINFO_EXTENSION));
                            
                    //         $codigo_1 = $inser_consecutivo."-documento-".$_FILES['documento']['name'][$key];
                    //         $nombre_soporte_1=$codigo_1;
                    //         $ruta_final=$ruta_guardar."/".$nombre_soporte_1;
                    //         if ($_FILES['documento']["error"][$key] > 0) {
                    //             $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                    //             $control_documento_1=0;
                    //         } else {
                    //           /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                    //             if (move_uploaded_file($_FILES['documento']['tmp_name'][$key], $ruta_final)) {
                    //                 $control_documento_1=1;
                    //                 // Prepara la sentencia
                    //                 $consulta_registro_soporte_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calibracion_monitoreo_soportes`(`gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`) VALUES (?,?,?,?,?);");

                    //                 // Agrega variables a sentencia preparada
                    //                 $consulta_registro_soporte_insert->bind_param('sssss', $inser_consecutivo,$nombre_soporte_1,$ruta_final,$archivo_extension, $_SESSION['usu_id']);

                    //                 if ($consulta_registro_soporte_insert->execute()) {
                    //                     $control_documento_1=1;
                    //                     $documento_registrados++;
                    //                 } else {
                    //                     $control_documento_1=0;
                    //                 }
                    //             } else {
                    //                 $control_documento_1=0;
                    //             }
                    //         }
                    //     } else {
                    //         $control_documento_1=1;
                    //     }
                    // }

                    // foreach($_FILES["documento_llamada"]['tmp_name'] as $key => $tmp_name) {
                    //     if ($_FILES["documento_llamada"]["name"][$key]!="") {
                    //         $documento_cantidad++;
                    //         $ruta_guardar="storage/".$inser_consecutivo;
                    //         if (!file_exists($ruta_guardar)) {
                    //             mkdir($ruta_guardar, 0777, true);
                    //         }
                            
                    //         $archivo_extension = strtolower(pathinfo($_FILES['documento_llamada']['name'][$key], PATHINFO_EXTENSION));
                            
                    //         $codigo_1 = $inser_consecutivo."-llamada-".$_FILES['documento_llamada']['name'][$key];
                    //         $nombre_soporte_1=$codigo_1;
                    //         $ruta_final=$ruta_guardar."/".$nombre_soporte_1;
                    //         if ($_FILES['documento_llamada']["error"][$key] > 0) {
                    //             $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                    //             $control_documento_2=0;
                    //         } else {
                    //           /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                    //             if (move_uploaded_file($_FILES['documento_llamada']['tmp_name'][$key], $ruta_final)) {
                    //                 $control_documento_2=1;
                    //                 // Prepara la sentencia
                    //                 $consulta_registro_soporte_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calibracion_monitoreo_soportes`(`gcms_monitoreo`, `gcms_nombre`, `gcms_ruta`, `gcms_extension`, `gcms_registro_usuario`) VALUES (?,?,?,?,?);");

                    //                 // Agrega variables a sentencia preparada
                    //                 $consulta_registro_soporte_insert->bind_param('sssss', $inser_consecutivo,$nombre_soporte_1,$ruta_final,$archivo_extension, $_SESSION['usu_id']);

                    //                 if ($consulta_registro_soporte_insert->execute()) {
                    //                     $control_documento_2=1;
                    //                     $documento_registrados++;
                    //                 } else {
                    //                     $control_documento_2=0;
                    //                 }
                    //             } else {
                    //                 $control_documento_2=0;
                    //             }
                    //         }
                    //     } else {
                    //         $control_documento_2=1;
                    //     }
                    // }

                    //insert log eventos
                        $consulta_string_log = "INSERT INTO `tb_administrador_log`(`clog_log_modulo`, `clog_log_tipo`, `clog_log_accion`, `clog_log_detalle`, `clog_registro_usuario`) VALUES (?,?,?,?,?)";
                    
                        $log_modulo=$modulo_plataforma;
                        $log_tipo="crear";
                        $log_accion="Crear registro";
                        $log_detalle="Monitoreo Calidad | ".$_SESSION['id_monitoreo'];
                        $log_usuario=$_SESSION["usu_id"];
                        
                        $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
                        $consulta_registros_log->bind_param("sssss", $log_modulo, $log_tipo, $log_accion, $log_detalle, $log_usuario);
                        $consulta_registros_log->execute();
                    //insert log eventos
                }
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el monitoreo, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('Monitoreo creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_matriz="SELECT `gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`, `gcm_registro_fecha` FROM `tb_gestion_calibracion_matriz` WHERE `gcm_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $_SESSION["mon_informacion"]["matriz"]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo.php?pagina=".$pagina."&id=".$filtro_permanente."&bandeja=".base64_encode('Mes Actual');
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
        <div class="row justify-content-center">
            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <p class="alert alert-success p-1">¡Se ha generado el monitoreo <?php echo $_SESSION['id_monitoreo']; ?>!</p>
                        <?php if ($documento_cantidad!=$documento_registrados): ?>
                            <p class="alert alert-danger p-1">¡Problemas al cargar algunos soportes, por favor verifique en la bandeja principal!</p>
                        <?php endif; ?>
                        <div class="form-group">
                          <label for="matriz" class="m-0">Matriz</label>
                          <input type="text" class="form-control form-control-sm" name="matriz" id="matriz" maxlength="50" value="<?php echo $resultado_registros_matriz[0][1]; ?> [<?php echo $resultado_registros_matriz[$i][3]; ?>]" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="analista" class="m-0">Analista</label>
                            <input type="text" class="form-control form-control-sm" name="analista" id="analista" maxlength="50" value="<?php echo $resultado_registros_analistas[0][1]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="fecha_gestion" class="m-0">Fecha gestión</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_gestion" id="fecha_gestion" maxlength="20" value="<?php if(isset($_SESSION["mon_informacion"]["fecha_gestion"])){ echo $_SESSION["mon_informacion"]["fecha_gestion"]; } ?>" required readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_monitoreo" class="m-0">Tipo monitoreo</label>
                            <select class="form-control form-control-sm" name="tipo_monitoreo" id="tipo_monitoreo" required disabled>
                              <option value="">Seleccione</option>
                              <option value="Calibración Interna" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND $_SESSION["mon_informacion"]["tipo_monitoreo"]=="Calibración Interna"){ echo "selected"; } ?>>Calibración Interna</option>
                              <option value="Calibración Externa" <?php if(isset($_SESSION["mon_informacion"]["tipo_monitoreo"]) AND $_SESSION["mon_informacion"]["tipo_monitoreo"]=="Calibración Externa"){ echo "selected"; } ?>>Calibración Externa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="skill_interaccion" class="m-0">Skill interacción</label>
                            <select class="form-control form-control-sm" name="skill_interaccion" id="skill_interaccion" required disabled>
                              <option value="">Seleccione</option>
                              <option value="Click to Call" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Click to Call"){ echo "selected"; } ?>>Click to Call</option>
                              <option value="Legal" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Legal"){ echo "selected"; } ?>>Legal</option>
                              <option value="Línea 141" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Línea 141"){ echo "selected"; } ?>>Línea 141</option>
                              <option value="Línea Anticorrupción" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Línea Anticorrupción"){ echo "selected"; } ?>>Línea Anticorrupción</option>
                              <option value="Línea Nacional " <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Línea Nacional "){ echo "selected"; } ?>>Línea Nacional </option>
                              <option value="Línea Verde" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Línea Verde"){ echo "selected"; } ?>>Línea Verde</option>
                              <option value="SNBF" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="SNBF"){ echo "selected"; } ?>>SNBF</option>
                              <option value="Violencia " <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="Violencia "){ echo "selected"; } ?>>Violencia </option>
                              <option value="N/A" <?php if(isset($_SESSION["mon_informacion"]["skill_interaccion"]) AND $_SESSION["mon_informacion"]["skill_interaccion"]=="N/A"){ echo "selected"; } ?>>N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_gestion" class="m-0">Tipo gestión</label>
                            <select class="form-control form-control-sm" name="tipo_gestion" id="tipo_gestion" required disabled>
                              <option value="">Seleccione</option>
                              <option value="Actuación ANNACC" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Actuación ANNACC"){ echo "selected"; } ?>>Actuación ANNACC</option>
                              <option value="ACVN" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="ACVN"){ echo "selected"; } ?>>ACVN</option>
                              <option value="Anexo" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Anexo"){ echo "selected"; } ?>>Anexo</option>
                              <option value="Asesoría en Derecho de Familia" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Asesoría en Derecho de Familia"){ echo "selected"; } ?>>Asesoría en Derecho de Familia</option>
                              <option value="Atención en Crisis" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Atención en Crisis"){ echo "selected"; } ?>>Atención en Crisis</option>
                              <option value="Búsqueda de Orígenes" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Búsqueda de Orígenes"){ echo "selected"; } ?>>Búsqueda de Orígenes</option>
                              <option value="Comunicación NNA" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Comunicación NNA"){ echo "selected"; } ?>>Comunicación NNA</option>
                              <option value="Consulta" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Consulta"){ echo "selected"; } ?>>Consulta</option>
                              <option value="Correos" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Correos"){ echo "selected"; } ?>>Correos</option>
                              <option value="Encuestas " <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Encuestas "){ echo "selected"; } ?>>Encuestas </option>
                              <option value="Inobservancia" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Inobservancia"){ echo "selected"; } ?>>Inobservancia</option>
                              <option value="IO" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="IO"){ echo "selected"; } ?>>IO</option>
                              <option value="IOT" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="IOT"){ echo "selected"; } ?>>IOT</option>
                              <option value="No Gestionó" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="No Gestionó"){ echo "selected"; } ?>>No Gestionó</option>
                              <option value="No Serio" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="No Serio"){ echo "selected"; } ?>>No Serio</option>
                              <option value="Observación" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Observación"){ echo "selected"; } ?>>Observación</option>
                              <option value="Oficio" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Oficio"){ echo "selected"; } ?>>Oficio</option>
                              <option value="Quejas" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Quejas"){ echo "selected"; } ?>>Quejas</option>
                              <option value="RAVD" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="RAVD"){ echo "selected"; } ?>>RAVD</option>
                              <option value="Reclamo" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Reclamo"){ echo "selected"; } ?>>Reclamo</option>
                              <option value="Reporte a Policía" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Reporte a Policía"){ echo "selected"; } ?>>Reporte a Policía</option>
                              <option value="SRD" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="SRD"){ echo "selected"; } ?>>SRD</option>
                              <option value="Sugerencias" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="Sugerencias"){ echo "selected"; } ?>>Sugerencias</option>
                              <option value="TAE" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="TAE"){ echo "selected"; } ?>>TAE</option>
                              <option value="N/A" <?php if(isset($_SESSION["mon_informacion"]["tipo_gestion"]) AND $_SESSION["mon_informacion"]["tipo_gestion"]=="N/A"){ echo "selected"; } ?>>N/A</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="segmento" class="m-0">Segmento</label>
                            <select class="form-control form-control-sm" name="segmento" id="segmento" required disabled>
                              <option value="">Seleccione</option>
                              <option value="Aseguramiento" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Aseguramiento"){ echo "selected"; } ?>>Aseguramiento</option>
                              <option value="Canal escrito" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Canal escrito"){ echo "selected"; } ?>>Canal escrito</option>
                              <option value="Chat ICBF" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Chat ICBF"){ echo "selected"; } ?>>Chat ICBF</option>
                              <option value="Conmutador" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Conmutador"){ echo "selected"; } ?>>Conmutador</option>
                              <option value="Correos y Portales" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Correos y Portales"){ echo "selected"; } ?>>Correos y Portales</option>
                              <option value="Encuestas" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Encuestas"){ echo "selected"; } ?>>Encuestas</option>
                              <option value="Línea 141" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Línea 141"){ echo "selected"; } ?>>Línea 141</option>
                              <option value="Línea Nacional" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Línea Nacional"){ echo "selected"; } ?>>Línea Nacional</option>
                              <option value="Línea Verde" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Línea Verde"){ echo "selected"; } ?>>Línea Verde</option>
                              <option value="Presencial" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Presencial"){ echo "selected"; } ?>>Presencial</option>
                              <option value="Profesional Es Abogados" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Profesional Es Abogados"){ echo "selected"; } ?>>Profesional Es Abogados</option>
                              <option value="Profesional Es Psicólogos" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Profesional Es Psicólogos"){ echo "selected"; } ?>>Profesional Es Psicólogos</option>
                              <option value="Profesional Es Violencia Sexual" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Profesional Es Violencia Sexual"){ echo "selected"; } ?>>Profesional Es Violencia Sexual</option>
                              <option value="Redes Sociales" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Redes Sociales"){ echo "selected"; } ?>>Redes Sociales</option>
                              <option value="Video Llamada" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="Video Llamada"){ echo "selected"; } ?>>Video Llamada</option>
                              <option value="WhatsApp" <?php if(isset($_SESSION["mon_informacion"]["segmento"]) AND $_SESSION["mon_informacion"]["segmento"]=="WhatsApp"){ echo "selected"; } ?>>WhatsApp</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_sim" class="m-0">Id SIM</label>
                          <input type="text" class="form-control form-control-sm" name="id_sim" id="id_sim" maxlength="100" value="<?php if(isset($_SESSION["mon_informacion"]["id_sim"])){ echo $_SESSION["mon_informacion"]["id_sim"]; } ?>" required readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="id_ani" class="m-0">ID/ANI</label>
                          <input type="text" class="form-control form-control-sm" name="id_ani" id="id_ani" maxlength="100" value="<?php if(isset($_SESSION["mon_informacion"]["id_ani"])){ echo $_SESSION["mon_informacion"]["id_ani"]; } ?>" required readonly>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" readonly><?php if(isset($_SESSION["mon_informacion"]["observaciones"])){ echo $_SESSION["mon_informacion"]["observaciones"]; } ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['monitoreo_creado']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>