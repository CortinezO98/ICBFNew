<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Sección - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_seccion=validar_input(base64_decode($_GET['sec']));

    if(isset($_POST["guardar_registro"])){
        $orden=validar_input($_POST['orden']);
        $titulo_seccion=validar_input($_POST['titulo_seccion']);
        $descripcion=validar_input($_POST['descripcion']);
        $siguiente_seccion=validar_input($_POST['siguiente_seccion']);
        $notificar_alerta=validar_input($_POST['notificar_alerta']);
        $seccion_complemento=validar_input($_POST['seccion_complemento']);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuestas_matriz_seccion` SET `gems_nombre_seccion`=?,`gems_descripcion`=?,`gems_imagen`='',`gems_video`='',`gems_siguiente_seccion`=?, `gems_notificacion`=?, `gems_seccion_complemento`=?, `gems_orden_mostrar`=? WHERE `gems_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssss', $titulo_seccion, $descripcion, $siguiente_seccion, $notificar_alerta, $seccion_complemento, $orden, $id_seccion);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar`, `gems_notificacion`, `gems_seccion_complemento` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_seccion);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_seccion="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar`, `gems_notificacion` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_encuesta`=? ORDER BY `gems_orden_mostrar` ASC";
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
            <div class="col-md-6 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="orden" id="orden">Orden</label>
                          <input type="number" step="0.1" class="form-control form-control-sm" name="orden" id="orden" maxlength="3" value="<?php echo $resultado_registros[0][7] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="titulo_seccion" id="titulo_seccion">Título sección</label>
                          <input type="text" class="form-control form-control-sm" name="titulo_seccion" id="titulo_seccion" maxlength="100" value="<?php echo $resultado_registros[0][2]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>
                          <textarea class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="1500" ><?php echo $resultado_registros[0][3]; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="siguiente_seccion">Ir a la sección</label>
                            <select class="form-control form-control-sm" name="siguiente_seccion" id="siguiente_seccion">
                                <option value="" <?php if($resultado_registros[0][6]==""){ echo "selected"; } ?>>Ir a la siguiente sección</option>
                                <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                    <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if($resultado_registros[0][6]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                <?php endfor; ?>
                                <option value="informacion_cierre" <?php if($resultado_registros[0][6]=="informacion_cierre"){ echo "selected"; } ?>>Información cierre</option>
                                <option value="observaciones_cierre" <?php if($resultado_registros[0][6]=="observaciones_cierre"){ echo "selected"; } ?>>Observaciones cierre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="notificar_alerta">Notificar alerta</label>
                            <select class="form-control form-control-sm" name="notificar_alerta" id="notificar_alerta" required>
                                <option value="No" <?php if($resultado_registros[0][8]=="No"){ echo "selected"; } ?>>No</option>
                                <option value="Si" <?php if($resultado_registros[0][8]=="Si"){ echo "selected"; } ?>>Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="seccion_complemento">Notificar sección complementaria</label>
                            <select class="form-control form-control-sm" name="seccion_complemento" id="seccion_complemento">
                                <option value="" <?php if($resultado_registros[0][9]==""){ echo "selected"; } ?>>Ninguna</option>
                                <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                    <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if($resultado_registros[0][9]==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Enviar sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
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
</body>
</html>