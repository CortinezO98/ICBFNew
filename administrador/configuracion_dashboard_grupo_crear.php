<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Dashboard Configuración | Grupo-Crear";
    $id_dashboard=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $nombre_grupo=validar_input($_POST['nombre_grupo']);
        $descripcion=validar_input($_POST['descripcion']);
        $orden=validar_input($_POST['orden']);
        
        if($_SESSION['dashboard_registro_creado_grupo']!=1){
            $codigo_unico=generar_codigo(10);
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_dashboard_grupos`(`gdg_id`, `gdg_dashboard`, `gdg_grupo_nombre`, `gdg_grupo_descripcion`, `gdg_orden`) VALUES (?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sssss', $codigo_unico, $id_dashboard, $nombre_grupo, $descripcion, $orden);
            
            if ($sentencia_insert->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                $_SESSION['dashboard_registro_creado_grupo']=1;
            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="../dashboard_configuracion.php?reg=".base64_encode($id_dashboard);

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
                          <label for="orden">Orden</label>
                          <input type="number" step="1" max="999" class="form-control form-control-sm" name="orden" id="orden" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $orden; } ?>" <?php if($_SESSION['dashboard_registro_creado_grupo']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="nombre_grupo">Nombre grupo</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_grupo" id="nombre_grupo" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nombre_grupo; } ?>" <?php if($_SESSION['dashboard_registro_creado_grupo']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>
                          <textarea class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="500" <?php if($_SESSION['dashboard_registro_creado_grupo']==1) { echo 'readonly'; } ?>><?php if(isset($_POST["guardar_registro"])){ echo $descripcion; } ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['dashboard_registro_creado_grupo']==1): ?>
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