<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calibración-Matriz Calidad";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz de Calidad | Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    if(isset($_POST["guardar_registro"])){
        $estado=validar_input($_POST['estado']);
        $nombre_matriz=validar_input($_POST['nombre_matriz']);
        $observaciones=validar_input($_POST['observaciones']);
        $usuario_registro=$_SESSION["usu_id"];

        if($_SESSION['registro_creado']!=1){
            $codigo_registro=generar_codigo(10);
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calibracion_matriz`(`gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`) VALUES (?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sssss', $codigo_registro, $nombre_matriz, $estado, $observaciones, $usuario_registro);
            
            if ($sentencia_insert->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
              $_SESSION['registro_creado']=1;
            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
            
            
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_matriz.php?pagina=".$pagina."&id=".$filtro_permanente;

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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select class="form-control form-control-sm" name="estado" id="estado" <?php if($_SESSION['registro_creado']==1) { echo 'disabled'; } ?> required>
                              <option value="">Seleccione</option>
                              <option value="Activo" <?php if(isset($_POST["guardar_registro"]) AND $estado=="Activo"){ echo "selected"; } ?>>Activo</option>
                              <option value="Inactivo" <?php if(isset($_POST["guardar_registro"]) AND $estado=="Inactivo"){ echo "selected"; } ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="nombre_matriz" id="nombre_matriz">Nombre matriz</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_matriz" id="nombre_matriz" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nombre_matriz; } ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" maxlength="200" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?>><?php if(isset($_POST["guardar_registro"])){ echo $observaciones; } ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado']==1): ?>
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