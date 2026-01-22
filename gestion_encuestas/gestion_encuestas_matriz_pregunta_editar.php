<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Pregunta - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_seccion=validar_input(base64_decode($_GET['sec']));
    $id_pregunta=validar_input(base64_decode($_GET['pre']));

    if(isset($_POST["guardar_registro"])){
        $orden=validar_input($_POST['orden']);
        $pregunta=validar_input($_POST['pregunta']);
        $descripcion=validar_input($_POST['descripcion']);
        $tipo_pregunta=validar_input($_POST['tipo_pregunta']);
        $obligatorio=validar_input($_POST['obligatorio']);
        $tipo_campo=validar_input($_POST['tipo_campo']);
        $segun_respuesta=validar_input($_POST['segun_respuesta']);
        $segun_respuesta_valor=$_POST['segun_respuesta_valor'];
        $opciones_respuesta_valor=$_POST['opciones_respuesta_valor'];

        $seccion_complemento=validar_input($_POST['seccion_complemento']);

        if (!isset($_POST['opcion_cierre'][0])) {
            $_POST['opcion_cierre']=array();
        }
        for ($i=0; $i < count($_POST['opcion_cierre']); $i++) { 
            $opcion_cierre_array[$_POST['opcion_cierre'][$i]]=1;
        }

        if (!isset($_POST['opcion_contacto'][0])) {
            $_POST['opcion_contacto']=array();
        }
        for ($i=0; $i < count($_POST['opcion_contacto']); $i++) { 
            $opcion_contacto_array[$_POST['opcion_contacto'][$i]]=1;
        }

        if (!isset($_POST['opcion_efectivo'][0])) {
            $_POST['opcion_efectivo']=array();
        }
        for ($i=0; $i < count($_POST['opcion_efectivo']); $i++) { 
            $opcion_efectivo_array[$_POST['opcion_efectivo'][$i]]=1;
        }

        if (!isset($_POST['opcion_notificacion'][0])) {
            $_POST['opcion_notificacion']=array();
        }
        for ($i=0; $i < count($_POST['opcion_notificacion']); $i++) { 
            $opcion_notificacion_array[$_POST['opcion_notificacion'][$i]]=1;
        }

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuestas_matriz_seccion_pregunta` SET `gemp_pregunta_nombre`=?, `gemp_pregunta_descripcion`=?,`gemp_tipo`=?,`gemp_obligatorio`=?, `gemp_tipo_campo`=?,`gemp_seccion_segun_respuesta`=?, `gemp_seccion_complemento`=?,`gemp_orden_mostrar`=? WHERE `gemp_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssssss', $pregunta, $descripcion, $tipo_pregunta, $obligatorio, $tipo_campo, $segun_respuesta, $seccion_complemento, $orden, $id_pregunta);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {

            $sentencia_delete_opciones = $enlace_db->prepare("DELETE FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_pregunta`=?");

            // Agrega variables a sentencia preparada
            $sentencia_delete_opciones->bind_param('s', $id_pregunta);
            
            if ($sentencia_delete_opciones->execute()) {
                // Prepara la sentencia
                $sentencia_insert_opciones = $enlace_db->prepare("INSERT INTO `tb_gestion_encuestas_matriz_seccion_pregunta_opcion`(`gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_estado_registro`, `gemo_estado_contacto`, `gemo_estado_efectivo`, `gemo_notificacion`, `gemo_orden_mostrar`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                // Agrega variables a sentencia preparada
                $sentencia_insert_opciones->bind_param('sssssssssss', $codigo_unico_opcion, $id_registro, $id_seccion, $id_pregunta, $opciones_respuesta_valor_insert, $opciones_respuesta_seccion_insert, $opcion_estado_insert, $opcion_contacto_insert, $opcion_efectivo_insert, $opcion_notificacion_insert, $orden_opcion);
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
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
          $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string_pregunta="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar`, `gemp_tipo_campo`, `gemp_seccion_complemento` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_id`=?";
    $consulta_registros_pregunta = $enlace_db->prepare($consulta_string_pregunta);
    $consulta_registros_pregunta->bind_param("s", $id_pregunta);
    $consulta_registros_pregunta->execute();
    $resultado_registros_pregunta = $consulta_registros_pregunta->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_opciones="SELECT `gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_orden_mostrar`, `gemo_estado_registro`, `gemo_estado_contacto`, `gemo_estado_efectivo`, `gemo_notificacion` FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_pregunta`=? ORDER BY `gemo_orden_mostrar` ASC";
    $consulta_registros_opciones = $enlace_db->prepare($consulta_string_opciones);
    $consulta_registros_opciones->bind_param("s", $id_pregunta);
    $consulta_registros_opciones->execute();
    $resultado_registros_opciones = $consulta_registros_opciones->get_result()->fetch_all(MYSQLI_NUM);

    unset($opciones_respuesta_valor);
    unset($segun_respuesta_valor);

    for ($i=0; $i < count($resultado_registros_opciones); $i++) { 
        $opciones_respuesta_valor[]=$resultado_registros_opciones[$i][4];
        $segun_respuesta_valor[]=$resultado_registros_opciones[$i][5];
        
        $opcion_cierre[]=$resultado_registros_opciones[$i][7];
        $opcion_contacto[]=$resultado_registros_opciones[$i][8];
        $opcion_efectivo[]=$resultado_registros_opciones[$i][9];
        $opcion_notificacion[]=$resultado_registros_opciones[$i][10];

            
    }

    $consulta_string_seccion="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_encuesta`=? ORDER BY `gems_orden_mostrar` ASC";
    $consulta_registros_seccion = $enlace_db->prepare($consulta_string_seccion);
    $consulta_registros_seccion->bind_param("s", $id_registro);
    $consulta_registros_seccion->execute();
    $resultado_registros_seccion = $consulta_registros_seccion->get_result()->fetch_all(MYSQLI_NUM);

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
                          <input type="number" step="1" class="form-control form-control-sm" name="orden" id="orden" maxlength="3" value="<?php echo $resultado_registros_pregunta[0][8]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="pregunta" id="pregunta">Pregunta</label>
                          <input type="text" class="form-control form-control-sm" name="pregunta" id="pregunta" maxlength="300" value="<?php echo $resultado_registros_pregunta[0][3]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion" id="descripcion">Descripción</label>
                          <input type="text" class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="800" value="<?php echo $resultado_registros_pregunta[0][4]; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_pregunta">Tipo</label>
                            <select class="form-control form-control-sm" name="tipo_pregunta" id="tipo_pregunta" onchange="validar_tipo_pregunta();" required>
                              <option value="">Seleccione</option>
                              <option value="Respuesta corta" <?php if($resultado_registros_pregunta[0][5]=="Respuesta corta"){ echo "selected"; } ?>>Respuesta corta</option>
                              <option value="Párrafo" <?php if($resultado_registros_pregunta[0][5]=="Párrafo"){ echo "selected"; } ?>>Párrafo</option>
                              <option value="Varias opciones" <?php if($resultado_registros_pregunta[0][5]=="Varias opciones"){ echo "selected"; } ?>>Varias opciones</option>
                              <option value="Casillas" <?php if($resultado_registros_pregunta[0][5]=="Casillas"){ echo "selected"; } ?>>Casillas</option>
                              <option value="Desplegable" <?php if($resultado_registros_pregunta[0][5]=="Desplegable"){ echo "selected"; } ?>>Desplegable</option>
                              <option value="Fecha" <?php if($resultado_registros_pregunta[0][5]=="Fecha"){ echo "selected"; } ?>>Fecha</option>
                              <option value="Hora" <?php if($resultado_registros_pregunta[0][5]=="Hora"){ echo "selected"; } ?>>Hora</option>
                              <option value="Fecha y hora" <?php if($resultado_registros_pregunta[0][5]=="Fecha y hora"){ echo "selected"; } ?>>Fecha y hora</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-none" id="check_seccion_respuesta">
                        <div class="form-group">
                            <label for="segun_respuesta">Sección según respuesta</label>
                            <select class="form-control form-control-sm" name="segun_respuesta" id="segun_respuesta" onchange="validar_segun_respuesta();" required disabled>
                              <option value="No" <?php if($resultado_registros_pregunta[0][7]=="No"){ echo "selected"; } ?>>No</option>
                              <option value="Si" <?php if($resultado_registros_pregunta[0][7]=="Si"){ echo "selected"; } ?>>Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-none" id="check_tipo_campo">
                        <div class="form-group">
                            <label for="tipo_campo">Tipo campo</label>
                            <select class="form-control form-control-sm" name="tipo_campo" id="tipo_campo" required disabled>
                              <option value="Alfanumérico" <?php if($resultado_registros_pregunta[0][9]=="Alfanumérico"){ echo "selected"; } ?>>Alfanumérico</option>
                              <option value="Numérico" <?php if($resultado_registros_pregunta[0][9]=="Numérico"){ echo "selected"; } ?>>Numérico</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seccion_complemento">Notificar sección complementaria</label>
                            <select class="form-control form-control-sm" name="seccion_complemento" id="seccion_complemento">
                                <option value="" <?php if($resultado_registros_pregunta[0][10]==""){ echo "selected"; } ?>>Ninguna</option>
                                <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                    <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if($resultado_registros_pregunta[0][10]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Enviar sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="stackedCheck1" class="form-check-label">Obligatorio</label>
                            <div class="form-check pl-0 pt-2">
                              <input id="stackedCheck1" name="obligatorio" class="form-check-input" type="checkbox" data-toggle="toggle" data-on="Si" data-off="No" <?php if($resultado_registros_pregunta[0][6]=="on"){ echo "checked"; } ?> >
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
                            <input type="text" class="form-control form-control-sm" name="" placeholder="Respuesta">
                        </div>
                    </div>
                    <div class="col-md-12 d-none" id="vista_previa_parrafo">
                        <div class="form-group">
                            <textarea class="form-control form-control-sm" name="" placeholder="Respuesta"></textarea>
                        </div>
                    </div>
                    <div class="col-md-4 d-none" id="vista_previa_fecha">
                        <div class="form-group">
                            <input type="date" class="form-control form-control-sm" name="">
                        </div>
                    </div>
                    <div class="col-md-4 d-none" id="vista_previa_hora">
                        <div class="form-group">
                            <input type="time" class="form-control form-control-sm" name="">
                        </div>
                    </div>
                    <div class="col-md-4 d-none" id="vista_previa_fecha_hora">
                        <div class="form-group">
                            <input type="datetime-local" class="form-control form-control-sm" name="">
                        </div>
                    </div>
                </div>
                <div class="row d-none" id="opciones_respuestas">
                    <div class="col-md-12" id="opciones_0" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                  <input type="text" class="form-control form-control-sm" name="opciones_respuesta_valor[]" id="opciones_respuesta_valor_0" maxlength="100" value="<?php echo $opciones_respuesta_valor[0]; ?>" placeholder="Opción 1" required disabled>
                                </div>
                            </div>
                            <div class="col-md-4" id="opciones_seccion_00" style="display: none;">
                                <div class="form-group">
                                    <select class="form-control form-control-sm" name="segun_respuesta_valor[]" id="segun_respuesta_0" disabled>
                                        <option value="" <?php if($segun_respuesta_valor[0]==""){ echo "selected"; } ?>>Ir a la siguiente sección</option>
                                        <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                            <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if($segun_respuesta_valor[0]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                        <?php endfor; ?>
                                        <option value="informacion_cierre" <?php if($segun_respuesta_valor[0]=="informacion_cierre"){ echo "selected"; } ?>>Información cierre</option>
                                        <option value="observaciones_cierre" <?php if($segun_respuesta_valor[0]=="observaciones_cierre"){ echo "selected"; } ?>>Observaciones cierre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pt-1" id="opciones_estado_00">
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_contacto_0" name="opcion_contacto[]" value="0" <?php if($opcion_contacto[0]){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_contacto_0"><span class="fas fa-phone-alt color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_efectivo_0" name="opcion_efectivo[]" value="0" <?php if($opcion_efectivo[0]){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_efectivo_0"><span class="fas fa-user-check color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_cierre_0" name="opcion_cierre[]" value="0" <?php if($opcion_cierre[0]){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_cierre_0"><span class="fas fa-lock color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_notificacion_0" name="opcion_notificacion[]" value="0" <?php if($opcion_notificacion[0]=="Si"){ echo "checked"; } ?>>
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
                                  <input type="text" class="form-control form-control-sm" name="opciones_respuesta_valor[]" id="opciones_respuesta_valor_<?php echo $j; ?>" maxlength="100" value="<?php echo $opciones_respuesta_valor[$j]; ?>" placeholder="Opción <?php echo $j+1; ?>" required disabled>
                                </div>
                            </div>
                            <div class="col-md-4" id="opciones_seccion_<?php echo $j.$j; ?>" style="display: none;">
                                <div class="form-group">
                                    <select class="form-control form-control-sm" name="segun_respuesta_valor[]" id="segun_respuesta_<?php echo $j; ?>" disabled>
                                        <option value="" <?php if($segun_respuesta_valor[$j]==""){ echo "selected"; } ?>>Ir a la siguiente sección</option>
                                        <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                            <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if($segun_respuesta_valor[$j]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                        <?php endfor; ?>
                                        <option value="informacion_cierre" <?php if($segun_respuesta_valor[$j]=="informacion_cierre"){ echo "selected"; } ?>>Información cierre</option>
                                        <option value="observaciones_cierre" <?php if($segun_respuesta_valor[$j]=="observaciones_cierre"){ echo "selected"; } ?>>Observaciones cierre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 pt-1" id="opciones_estado_<?php echo $j.$j; ?>">
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_contacto_<?php echo $j; ?>" name="opcion_contacto[]" value="<?php echo $j; ?>" <?php if($opcion_contacto[$j]){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_contacto_<?php echo $j; ?>"><span class="fas fa-phone-alt color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_efectivo_<?php echo $j; ?>" name="opcion_efectivo[]" value="<?php echo $j; ?>" <?php if($opcion_efectivo[$j]){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_efectivo_<?php echo $j; ?>"><span class="fas fa-user-check color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_cierre_<?php echo $j; ?>" name="opcion_cierre[]" value="<?php echo $j; ?>" <?php if($opcion_cierre[$j]){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_cierre_<?php echo $j; ?>"><span class="fas fa-lock color-verde"></span></label>
                                </div>
                                <div class="form-group custom-control custom-checkbox m-0 float-left ml-2">
                                    <input type="checkbox" class="custom-control-input" id="opcion_notificacion_<?php echo $j; ?>" name="opcion_notificacion[]" value="<?php echo $j; ?>" <?php if($opcion_notificacion[$j]=="Si"){ echo "checked"; } ?>>
                                    <label class="custom-control-label" for="opcion_notificacion_<?php echo $j; ?>"><span class="fas fa-envelope color-verde"></span></label>
                                </div>
                            </div>
                        </div>
                        
                        <a href="#" class="btn btn-primary float-left"  style="display: block; width: 100px;" id="add_duplicar_<?php echo $j; ?>" onclick='mostrar_contenido("<?php echo $k; ?>");' title="Añadir opción"><span class="fas fa-plus"></span> Añadir opción</a>
                        <a href="#" class="btn btn-danger btn-eliminar float-left ml-1"  style="display: block; width: 100px;" id="rem_duplicar_<?php echo $j; ?>" onclick='ocultar_contenido("<?php echo $j; ?>");' title="Quitar opción"><span class="fas fa-trash-alt"></span> Quitar opción</a>
                        
                    </div>    
                </div>
                <?php endfor; ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"])): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
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

                    <?php for ($i=0; $i < count($segun_respuesta_valor); $i++): ?>
                        // document.getElementById('segun_respuesta_<?php echo $i; ?>').disabled=true;

                    <?php endfor; ?>
                <?php if($_SESSION['encuesta_pregunta_registro_creado']==1): ?>
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
                    // document.getElementById('add_duplicar_<?php echo $i; ?>').style.display='none';
                    <?php if($i>0): ?>
                    // document.getElementById('rem_duplicar_<?php echo $i; ?>').style.display='none';
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

        validar_tipo_pregunta();
        validar_segun_respuesta();
        <?php if($resultado_registros_pregunta[0][5]=="Varias opciones" OR $resultado_registros_pregunta[0][5]=="Casillas" OR $resultado_registros_pregunta[0][5]=="Desplegable"): ?>
            <?php for ($i=1; $i < count($opciones_respuesta_valor); $i++): ?>
                mostrar_contenido(<?php echo $i; ?>);
            <?php endfor; ?>    
        <?php endif; ?>
    </script>
</body>
</html>