<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Pregunta - Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $seccion=validar_input(base64_decode($_GET['sec']));

    if(isset($_POST["guardar_registro"])){
        $orden=validar_input($_POST['orden']);
        $pregunta=validar_input($_POST['pregunta']);
        $descripcion=validar_input($_POST['descripcion']);
        $tipo_pregunta=validar_input($_POST['tipo_pregunta']);
        $tipo_campo=validar_input($_POST['tipo_campo']);
        $obligatorio=validar_input($_POST['obligatorio']);
        $segun_respuesta=validar_input($_POST['segun_respuesta']);
        $segun_respuesta_valor=$_POST['segun_respuesta_valor'];
        $opciones_respuesta_valor=$_POST['opciones_respuesta_valor'];

        $seccion_complemento=validar_input($_POST['seccion_complemento']);

        for ($i=0; $i < count($_POST['opcion_cierre']); $i++) { 
            $opcion_cierre_array[$_POST['opcion_cierre'][$i]]=1;
        }

        for ($i=0; $i < count($_POST['opcion_contacto']); $i++) { 
            $opcion_contacto_array[$_POST['opcion_contacto'][$i]]=1;
        }

        for ($i=0; $i < count($_POST['opcion_efectivo']); $i++) { 
            $opcion_efectivo_array[$_POST['opcion_efectivo'][$i]]=1;
        }

        for ($i=0; $i < count($_POST['opcion_notificacion']); $i++) { 
            $opcion_notificacion_array[$_POST['opcion_notificacion'][$i]]=1;
        }

        $opcion_cierre=$_POST['opcion_cierre'];
        $opcion_contacto=$_POST['opcion_contacto'];
        $opcion_efectivo=$_POST['opcion_efectivo'];
        $opcion_notificacion=$_POST['opcion_notificacion'];
        
        if($_SESSION['encuesta_pregunta_registro_creado']!=1){
            $codigo_unico=generar_codigo(10);
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_encuestas_matriz_seccion_pregunta`(`gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_tipo_campo`, `gemp_seccion_segun_respuesta`, `gemp_seccion_complemento`, `gemp_orden_mostrar`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sssssssssss', $codigo_unico, $id_registro, $seccion, $pregunta, $descripcion, $tipo_pregunta, $obligatorio, $tipo_campo, $segun_respuesta, $seccion_complemento, $orden);
            
            if ($sentencia_insert->execute()) {
                // Prepara la sentencia
                $sentencia_insert_opciones = $enlace_db->prepare("INSERT INTO `tb_gestion_encuestas_matriz_seccion_pregunta_opcion`(`gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_estado_registro`, `gemo_estado_contacto`, `gemo_estado_efectivo`, `gemo_notificacion`, `gemo_orden_mostrar`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                // Agrega variables a sentencia preparada
                $sentencia_insert_opciones->bind_param('sssssssssss', $codigo_unico_opcion, $id_registro, $seccion, $codigo_unico, $opciones_respuesta_valor_insert, $opciones_respuesta_seccion_insert, $opcion_estado_insert, $opcion_contacto_insert, $opcion_efectivo_insert, $opcion_notificacion_insert, $orden_opcion);
                $control_insert_opciones=0;
                for ($i=0; $i < count($opciones_respuesta_valor); $i++) { 
                    $codigo_unico_opcion=generar_codigo(10);
                    $opciones_respuesta_valor_insert=$opciones_respuesta_valor[$i];
                    $opciones_respuesta_seccion_insert=$segun_respuesta_valor[$i];

                    if ($opcion_cierre_array[$i]!="") {
                        $opcion_estado_insert=1;
                    } else {
                        $opcion_estado_insert="";
                    }

                    if ($opcion_contacto_array[$i]!="") {
                        $opcion_contacto_insert=1;
                    } else {
                        $opcion_contacto_insert="";
                    }

                    if ($opcion_efectivo_array[$i]!="") {
                        $opcion_efectivo_insert=1;
                    } else {
                        $opcion_efectivo_insert="";
                    }

                    if ($opcion_notificacion_array[$i]!="") {
                        $opcion_notificacion_insert="Si";
                    } else {
                        $opcion_notificacion_insert="";
                    }

                    $orden_opcion=$i+1;

                    if ($sentencia_insert_opciones->execute()) {
                        $control_insert_opciones++;
                    }
                }

                if ($control_insert_opciones==count($opciones_respuesta_valor)) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                    $_SESSION['encuesta_pregunta_registro_creado']=1;
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                    $sentencia_delete_opciones = $enlace_db->prepare("DELETE FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_pregunta`=? AND `gemo_seccion`=? AND `gemo_encuesta`=?");

                    // Agrega variables a sentencia preparada
                    $sentencia_delete_opciones->bind_param('sss', $codigo_unico, $seccion, $id_registro);
                    $sentencia_delete_opciones->execute();

                    $sentencia_delete_pregunta = $enlace_db->prepare("DELETE FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_id`=? AND `gemp_encuesta`=? AND `gemp_seccion`=?");

                    // Agrega variables a sentencia preparada
                    $sentencia_delete_pregunta->bind_param('sss', $codigo_unico, $id_registro, $seccion);
                    $sentencia_delete_pregunta->execute();
                }
            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_seccion="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_encuesta`=? ORDER BY `gems_orden_mostrar` ASC";
    $consulta_registros_seccion = $enlace_db->prepare($consulta_string_seccion);
    $consulta_registros_seccion->bind_param("s", $id_registro);
    $consulta_registros_seccion->execute();
    $resultado_registros_seccion = $consulta_registros_seccion->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_preguntas="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar`, `gemp_tipo_campo` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_encuesta`=? AND `gemp_seccion`=?";
    $consulta_registros_preguntas = $enlace_db->prepare($consulta_string_preguntas);
    $consulta_registros_preguntas->bind_param("ss", $id_registro, $seccion);
    $consulta_registros_preguntas->execute();
    $resultado_registros_preguntas = $consulta_registros_preguntas->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_encuestas_matriz_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro);

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
        <div class="row justify-content-center">
            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="orden" id="orden">Orden</label>
                          <input type="number" step="1" class="form-control form-control-sm" name="orden" id="orden" maxlength="3" value="<?php if(isset($_POST["guardar_registro"])){ echo $orden; } else { echo count($resultado_registros_preguntas)+1; } ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="pregunta" id="pregunta">Pregunta</label>
                          <input type="text" class="form-control form-control-sm" name="pregunta" id="pregunta" maxlength="300" value="<?php if(isset($_POST["guardar_registro"])){ echo $pregunta; } ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion" id="descripcion">Descripción</label>
                          <input type="text" class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="800" value="<?php if(isset($_POST["guardar_registro"])){ echo $descripcion; } ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?>>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_pregunta">Tipo</label>
                            <select class="form-control form-control-sm" name="tipo_pregunta" id="tipo_pregunta" onchange="validar_tipo_pregunta();" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?> required>
                              <option value="">Seleccione</option>
                              <option value="Respuesta corta" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Respuesta corta"){ echo "selected"; } ?>>Respuesta corta</option>
                              <option value="Párrafo" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Párrafo"){ echo "selected"; } ?>>Párrafo</option>
                              <option value="Varias opciones" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Varias opciones"){ echo "selected"; } ?>>Varias opciones</option>
                              <option value="Casillas" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Casillas"){ echo "selected"; } ?>>Casillas</option>
                              <option value="Desplegable" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Desplegable"){ echo "selected"; } ?>>Desplegable</option>
                              <option value="Fecha" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Fecha"){ echo "selected"; } ?>>Fecha</option>
                              <option value="Hora" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Hora"){ echo "selected"; } ?>>Hora</option>
                              <option value="Fecha y hora" <?php if(isset($_POST["guardar_registro"]) AND $tipo_pregunta=="Fecha y hora"){ echo "selected"; } ?>>Fecha y hora</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-none" id="check_seccion_respuesta">
                        <div class="form-group">
                            <label for="segun_respuesta">Sección según respuesta</label>
                            <select class="form-control form-control-sm" name="segun_respuesta" id="segun_respuesta" onchange="validar_segun_respuesta();" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?> required disabled>
                              <option value="No" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta=="No"){ echo "selected"; } ?>>No</option>
                              <option value="Si" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta=="Si"){ echo "selected"; } ?>>Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-none" id="check_tipo_campo">
                        <div class="form-group">
                            <label for="tipo_campo">Tipo campo</label>
                            <select class="form-control form-control-sm" name="tipo_campo" id="tipo_campo" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?> required disabled>
                              <option value="Alfanumérico" <?php if(isset($_POST["guardar_registro"]) AND $tipo_campo=="Alfanumérico"){ echo "selected"; } ?>>Alfanumérico</option>
                              <option value="Numérico" <?php if(isset($_POST["guardar_registro"]) AND $tipo_campo=="Numérico"){ echo "selected"; } ?>>Numérico</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                                <div class="form-group">
                                    <label for="seccion_complemento">Notificar sección complementaria</label>
                                    <select class="form-control form-control-sm" name="seccion_complemento" id="seccion_complemento" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                        <option value="" <?php if(isset($_POST["guardar_registro"]) AND $seccion_complemento==""){ echo "selected"; } ?>>Ninguna</option>
                                        <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                            <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $seccion_complemento==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="stackedCheck1" class="form-check-label">Obligatorio</label>
                            <div class="form-check pl-0 pt-2">
                              <input id="stackedCheck1" name="obligatorio" class="form-check-input" type="checkbox" data-toggle="toggle" data-on="Si" data-off="No" <?php if(isset($_POST["guardar_registro"]) AND $obligatorio=="on"){ echo "checked"; } if(!isset($_POST["guardar_registro"])){ echo "checked"; } ?>  <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 d-none" id="vista_previa">
                        <p class="p-1 border-bottom" id="titulo_vista_previa"></p>
                    </div>
                    <div class="col-md-12 d-none" id="vista_previa_respuesta_corta">
                        <div class="form-group">
                            <input type="text" class="form-control form-control-sm" name="" placeholder="Respuesta" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?>>
                        </div>
                    </div>
                    <div class="col-md-12 d-none" id="vista_previa_parrafo">
                        <div class="form-group">
                            <textarea class="form-control form-control-sm" name="" placeholder="Respuesta" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?>></textarea>
                        </div>
                    </div>
                    <div class="col-md-4 d-none" id="vista_previa_fecha">
                        <div class="form-group">
                            <input type="date" class="form-control form-control-sm" name="" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?>>
                        </div>
                    </div>
                    <div class="col-md-4 d-none" id="vista_previa_hora">
                        <div class="form-group">
                            <input type="time" class="form-control form-control-sm" name="" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?>>
                        </div>
                    </div>
                    <div class="col-md-4 d-none" id="vista_previa_fecha_hora">
                        <div class="form-group">
                            <input type="datetime-local" class="form-control form-control-sm" name="" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?>>
                        </div>
                    </div>
                </div>
                <div class="row d-none" id="opciones_respuestas">
                    <div class="col-md-12" id="opciones_0" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                  <input type="text" class="form-control form-control-sm" name="opciones_respuesta_valor[]" id="opciones_respuesta_valor_0" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $opciones_respuesta_valor[0]; } ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?> placeholder="Opción 1" required disabled>
                                </div>
                            </div>
                            <div class="col-md-4" id="opciones_seccion_00" style="display: none;">
                                <div class="form-group">
                                    <select class="form-control form-control-sm" name="segun_respuesta_valor[]" id="segun_respuesta_0" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?> disabled>
                                        <option value="" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[0]==""){ echo "selected"; } ?>>Ir a la siguiente sección</option>
                                        <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                            <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[0]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                        <?php endfor; ?>
                                        <option value="informacion_cierre" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[0]=="informacion_cierre"){ echo "selected"; } ?>>Información cierre</option>
                                        <option value="observaciones_cierre" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[0]=="observaciones_cierre"){ echo "selected"; } ?>>Observaciones cierre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pt-1" id="opciones_estado_00">
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_contacto_0" name="opcion_contacto[]" value="0" <?php if(isset($_POST["guardar_registro"]) AND in_array("0", $opcion_contacto)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_contacto_0"><span class="fas fa-phone-square-alt color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_efectivo_0" name="opcion_efectivo[]" value="0" <?php if(isset($_POST["guardar_registro"]) AND in_array("0", $opcion_efectivo)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_efectivo_0"><span class="fas fa-user-check color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_cierre_0" name="opcion_cierre[]" value="0" <?php if(isset($_POST["guardar_registro"]) AND in_array("0", $opcion_cierre)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_cierre_0"><span class="fas fa-lock color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_notificacion_0" name="opcion_notificacion[]" value="0" <?php if(isset($_POST["guardar_registro"]) AND in_array("0", $opcion_notificacion)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_notificacion_0"><span class="fas fa-envelope color-verde"></span></label>
                                </div>
                            </div>
                        </div>
                        <a href="#" class="btn btn-primary btn-width" style="display: block; width: 100px;" id="add_duplicar_0" onclick='mostrar_contenido(1);' title="Añadir opción"><span class="fas fa-plus"></span> Añadir opción</a>
                    </div>    
                    </div>
                    <?php for ($j=1; $j < 300; $j++):
                        $k=$j+1;
                    ?>
                    <div class="row">
                    <div class="col-md-12" id="opciones_<?php echo $j; ?>" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                  <input type="text" class="form-control form-control-sm" name="opciones_respuesta_valor[]" id="opciones_respuesta_valor_<?php echo $j; ?>" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $opciones_respuesta_valor[$j]; } ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'readonly'; } ?> placeholder="Opción <?php echo $j+1; ?>" required disabled>
                                </div>
                            </div>
                            <div class="col-md-4" id="opciones_seccion_<?php echo $j.$j; ?>" style="display: none;">
                                <div class="form-group">
                                    <select class="form-control form-control-sm" name="segun_respuesta_valor[]" id="segun_respuesta_<?php echo $j; ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?> disabled>
                                        <option value="" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[$j]==""){ echo "selected"; } ?>>Ir a la siguiente sección</option>
                                        <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                            <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[$j]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                        <?php endfor; ?>
                                        <option value="informacion_cierre" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[$j]=="informacion_cierre"){ echo "selected"; } ?>>Información cierre</option>
                                        <option value="observaciones_cierre" <?php if(isset($_POST["guardar_registro"]) AND $segun_respuesta_valor[$j]=="observaciones_cierre"){ echo "selected"; } ?>>Observaciones cierre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pt-1" id="opciones_estado_<?php echo $j.$j; ?>">
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_contacto_<?php echo $j; ?>" name="opcion_contacto[]" value="<?php echo $j; ?>" <?php if(isset($_POST["guardar_registro"]) AND in_array($j, $opcion_contacto)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_contacto_<?php echo $j; ?>"><span class="fas fa-phone-square-alt color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_efectivo_<?php echo $j; ?>" name="opcion_efectivo[]" value="<?php echo $j; ?>" <?php if(isset($_POST["guardar_registro"]) AND in_array($j, $opcion_efectivo)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_efectivo_<?php echo $j; ?>"><span class="fas fa-user-check color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_cierre_<?php echo $j; ?>" name="opcion_cierre[]" value="<?php echo $j; ?>" <?php if(isset($_POST["guardar_registro"]) AND in_array($j, $opcion_cierre)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_cierre_<?php echo $j; ?>"><span class="fas fa-lock color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_notificacion_<?php echo $j; ?>" name="opcion_notificacion[]" value="<?php echo $j; ?>" <?php if(isset($_POST["guardar_registro"]) AND in_array($j, $opcion_notificacion)){ echo "checked"; } ?> <?php if($_SESSION['encuesta_pregunta_registro_creado']==1) { echo 'disabled'; } ?>>
                                    <label class="custom-control-label" for="opcion_notificacion_<?php echo $j; ?>"><span class="fas fa-envelope color-verde"></span></label>
                                </div>
                            </div>
                        </div>
                        
                        <a href="#" class="btn btn-primary float-left"  style="display: block; width: 100px;" id="add_duplicar_<?php echo $j; ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']!=1){ echo "onclick='mostrar_contenido(".$k.");'"; } ?> title="Añadir opción"><span class="fas fa-plus"></span> Añadir opción</a>
                        <a href="#" class="btn btn-danger btn-eliminar float-left ml-1"  style="display: block; width: 100px;" id="rem_duplicar_<?php echo $j; ?>" <?php if($_SESSION['encuesta_pregunta_registro_creado']!=1){ echo "onclick='ocultar_contenido(".$j.");'"; } ?> title="Quitar opción"><span class="fas fa-trash-alt"></span> Quitar opción</a>
                        
                    </div>    
                </div>
                <?php endfor; ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['encuesta_pregunta_registro_creado']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
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
        function validar_tipo_pregunta() {
            var tipo_pregunta_opcion = document.getElementById("tipo_pregunta");
            var tipo_pregunta = tipo_pregunta_opcion.options[tipo_pregunta_opcion.selectedIndex].value;

            var estado_campo_segun_respuesta = document.getElementById('segun_respuesta').disabled=true;
            var estado_campo_tipo = document.getElementById('tipo_campo').disabled=true;

            if (tipo_pregunta!="") {
                $("#vista_previa").removeClass('d-none').addClass('d-block');
            } else {
                $("#vista_previa").removeClass('d-block').addClass('d-none');
            }
            $("#vista_previa_respuesta_corta").removeClass('d-block').addClass('d-none');
            $("#vista_previa_parrafo").removeClass('d-block').addClass('d-none');
            $("#vista_previa_fecha").removeClass('d-block').addClass('d-none');
            $("#vista_previa_hora").removeClass('d-block').addClass('d-none');
            $("#vista_previa_fecha_hora").removeClass('d-block').addClass('d-none');
            $("#check_seccion_respuesta").removeClass('d-block').addClass('d-none');
            $("#check_tipo_campo").removeClass('d-block').addClass('d-none');
            $("#opciones_respuestas").removeClass('d-block').addClass('d-none');

            if (tipo_pregunta=="Respuesta corta") {
                document.getElementById("titulo_vista_previa").innerHTML="Vista previa";
                $("#vista_previa_respuesta_corta").removeClass('d-none').addClass('d-block');
                ocultar_todo_opciones();
                $("#check_tipo_campo").removeClass('d-none').addClass('d-block');
                var estado_campo_tipo = document.getElementById('tipo_campo').disabled=false;
                
            } else if (tipo_pregunta=="Párrafo") {
                document.getElementById("titulo_vista_previa").innerHTML="Vista previa";
                $("#vista_previa_parrafo").removeClass('d-none').addClass('d-block');
                ocultar_todo_opciones();

            } else if (tipo_pregunta=="Varias opciones" || tipo_pregunta=="Casillas" || tipo_pregunta=="Desplegable") {
                var estado_campo_segun_respuesta = document.getElementById('segun_respuesta').disabled=false;

                document.getElementById("titulo_vista_previa").innerHTML="Opciones de respuesta";
                $("#opciones_respuestas").removeClass('d-none').addClass('d-block');
                $("#check_seccion_respuesta").removeClass('d-none').addClass('d-block');
                document.getElementById('opciones_0').style.display='block';
                var estado_campo = document.getElementById('opciones_respuesta_valor_0').disabled=false;
                var estado_campo_2 = document.getElementById('opciones_respuesta_valor_1').disabled;

                if (estado_campo_2) {
                    document.getElementById('add_duplicar_0').style.display='block';
                }

                validar_segun_respuesta();

            } else if (tipo_pregunta=="Fecha") {
                document.getElementById("titulo_vista_previa").innerHTML="Vista previa";
                $("#vista_previa_fecha").removeClass('d-none').addClass('d-block');
                ocultar_todo_opciones();

            } else if (tipo_pregunta=="Hora") {
                document.getElementById("titulo_vista_previa").innerHTML="Vista previa";
                $("#vista_previa_hora").removeClass('d-none').addClass('d-block');
                ocultar_todo_opciones();

            } else if (tipo_pregunta=="Fecha y hora") {
                document.getElementById("titulo_vista_previa").innerHTML="Vista previa";
                $("#vista_previa_fecha_hora").removeClass('d-none').addClass('d-block');
                ocultar_todo_opciones();
            }
        }

        function validar_segun_respuesta() {
            var segun_respuesta_opcion = document.getElementById("segun_respuesta");
            var segun_respuesta = segun_respuesta_opcion.options[segun_respuesta_opcion.selectedIndex].value;

            if (segun_respuesta=="Si") {
                <?php for ($i=0; $i < 300; $i++): ?>
                    var estado_opcion = document.getElementById('opciones_respuesta_valor_<?php echo $i; ?>').disabled;
                    document.getElementById('segun_respuesta_<?php echo $i; ?>').disabled=estado_opcion;
                    if (estado_opcion==false) {
                        document.getElementById('opciones_seccion_<?php echo $i.$i; ?>').style.display='block';
                    }
                <?php endfor; ?>

                <?php if($_SESSION['encuesta_pregunta_registro_creado']==1): ?>
                    <?php for ($i=0; $i < count($segun_respuesta_valor); $i++): ?>
                        document.getElementById('segun_respuesta_<?php echo $i; ?>').disabled=true;

                    <?php endfor; ?>
                <?php endif; ?>
            } else {
                <?php for ($i=0; $i < 300; $i++): ?>
                    document.getElementById('segun_respuesta_<?php echo $i; ?>').disabled=true;
                    document.getElementById('opciones_seccion_<?php echo $i.$i; ?>').style.display='none';

                <?php endfor; ?>
            }
        }
        
        function ocultar_todo_opciones() {
            document.getElementById("segun_respuesta").selectedIndex = "0";
            <?php for ($i=0; $i < 300; $i++): ?>
                document.getElementById('opciones_respuesta_valor_<?php echo $i; ?>').disabled=true;
                document.getElementById('opciones_respuesta_valor_<?php echo $i; ?>').value="";
                document.getElementById('opciones_<?php echo $i; ?>').style.display='none';
            <?php endfor; ?>
        }

        function mostrar_contenido(id_opcion){
            var id_anterior=id_opcion-1;
            document.getElementById('opciones_'+id_opcion).style.display='block';
            
            var estado_campo = document.getElementById('opciones_respuesta_valor_'+id_opcion);
            estado_campo.disabled = !estado_campo.disabled;
            
            if (id_anterior>0) {
                document.getElementById('rem_duplicar_'+id_anterior).style.display='none';
            }

            document.getElementById('add_duplicar_'+id_anterior).style.display='none';

            document.getElementById('rem_duplicar_'+id_opcion).style.display='block';
            document.getElementById('add_duplicar_'+id_opcion).style.display='block';
            validar_segun_respuesta();
            <?php if($_SESSION['encuesta_pregunta_registro_creado']==1): ?>
                <?php for ($i=0; $i < 300; $i++): ?>
                    document.getElementById('add_duplicar_<?php echo $i; ?>').style.display='none';
                    <?php if($i>0): ?>
                    document.getElementById('rem_duplicar_<?php echo $i; ?>').style.display='none';
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        }

        function ocultar_contenido(id_opcion){
            var id_anterior=id_opcion-1;
            document.getElementById('opciones_'+id_opcion).style.display='none';

            var estado_campo_1 = document.getElementById('opciones_respuesta_valor_'+id_opcion);
            estado_campo_1.disabled = !estado_campo_1.disabled;

            document.getElementById('rem_duplicar_'+id_opcion).style.display='none';
            document.getElementById('add_duplicar_'+id_opcion).style.display='none';

            if (id_anterior>0) {
                document.getElementById('rem_duplicar_'+id_anterior).style.display='block';
            }
            document.getElementById('add_duplicar_'+id_anterior).style.display='block';
            validar_segun_respuesta();
        }

        <?php if (isset($_POST["guardar_registro"])): ?>
            validar_tipo_pregunta();
            validar_segun_respuesta();
            <?php if($_SESSION['encuesta_pregunta_registro_creado']==1): ?>
                var estado_campo_tipo = document.getElementById('tipo_campo').disabled=true;
                var estado_campo_segun_respuesta = document.getElementById('segun_respuesta').disabled=true;
            <?php endif; ?>
            <?php if($tipo_pregunta=="Varias opciones" OR $tipo_pregunta=="Casillas" OR $tipo_pregunta=="Desplegable"): ?>
                <?php for ($i=1; $i < count($opciones_respuesta_valor); $i++): ?>
                    mostrar_contenido(<?php echo $i; ?>);
                <?php endfor; ?>    
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>