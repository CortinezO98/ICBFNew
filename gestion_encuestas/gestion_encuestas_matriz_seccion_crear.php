<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Sección - Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $titulo_seccion=validar_input($_POST['titulo_seccion']);
        $descripcion=validar_input($_POST['descripcion']);
        $orden=validar_input($_POST['orden']);
        $siguiente_seccion=validar_input($_POST['siguiente_seccion']);
        $notificar_alerta=validar_input($_POST['notificar_alerta']);
        $seccion_complemento=validar_input($_POST['seccion_complemento']);
        
        if($_SESSION['encuesta_seccion_registro_creado']!=1){
            $codigo_unico=generar_codigo(10);
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_encuestas_matriz_seccion`(`gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_notificacion`, `gems_seccion_complemento`, `gems_orden_mostrar`) VALUES (?,?,?,?,'','',?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('ssssssss', $codigo_unico, $id_registro, $titulo_seccion, $descripcion, $siguiente_seccion, $notificar_alerta, $seccion_complemento, $orden);
            
            if ($sentencia_insert->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
              $_SESSION['encuesta_seccion_registro_creado']=1;
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
                          <input type="number" step="0.1" class="form-control form-control-sm" name="orden" id="orden" maxlength="3" value="<?php if(isset($_POST["guardar_registro"])){ echo $orden; } else { echo count($resultado_registros_seccion)+1; } ?>" <?php if($_SESSION['encuesta_seccion_registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="titulo_seccion" id="titulo_seccion">Título sección</label>
                          <input type="text" class="form-control form-control-sm" name="titulo_seccion" id="titulo_seccion" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $titulo_seccion; } ?>" <?php if($_SESSION['encuesta_seccion_registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>
                          <textarea class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="1500" <?php if($_SESSION['encuesta_seccion_registro_creado']==1) { echo 'readonly'; } ?>><?php if(isset($_POST["guardar_registro"])){ echo $descripcion; } ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="siguiente_seccion">Ir a la sección</label>
                            <select class="form-control form-control-sm" name="siguiente_seccion" id="siguiente_seccion" <?php if($_SESSION['encuesta_seccion_registro_creado']==1) { echo 'disabled'; } ?>>
                                <option value="" <?php if(isset($_POST["guardar_registro"]) AND $siguiente_seccion==""){ echo "selected"; } ?>>Ir a la siguiente sección</option>
                                <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                    <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $siguiente_seccion==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                <?php endfor; ?>
                                <option value="informacion_cierre" <?php if(isset($_POST["guardar_registro"]) AND $siguiente_seccion=="informacion_cierre"){ echo "selected"; } ?>>Información cierre</option>
                                <option value="observaciones_cierre" <?php if(isset($_POST["guardar_registro"]) AND $siguiente_seccion=="observaciones_cierre"){ echo "selected"; } ?>>Observaciones cierre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="notificar_alerta">Notificar alerta</label>
                            <select class="form-control form-control-sm" name="notificar_alerta" id="notificar_alerta" <?php if($_SESSION['encuesta_seccion_registro_creado']==1) { echo 'disabled'; } ?> required>
                                <option value="No" <?php if(isset($_POST["guardar_registro"]) AND $notificar_alerta=="No"){ echo "selected"; } ?>>No</option>
                                <option value="Si" <?php if(isset($_POST["guardar_registro"]) AND $notificar_alerta=="Si"){ echo "selected"; } ?>>Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="seccion_complemento">Notificar sección complementaria</label>
                            <select class="form-control form-control-sm" name="seccion_complemento" id="seccion_complemento" <?php if($_SESSION['encuesta_seccion_registro_creado']==1) { echo 'disabled'; } ?>>
                                <option value="" <?php if(isset($_POST["guardar_registro"]) AND $seccion_complemento==""){ echo "selected"; } ?>>Ninguna</option>
                                <?php for ($l=0; $l < count($resultado_registros_seccion); $l++): ?>
                                    <option value="<?php echo $resultado_registros_seccion[$l][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $seccion_complemento==$resultado_registros_seccion[$l][0]){ echo "selected"; } ?>>Ir a la sección (<?php echo $resultado_registros_seccion[$l][2]; ?>)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['encuesta_seccion_registro_creado']==1): ?>
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
</body>
</html>