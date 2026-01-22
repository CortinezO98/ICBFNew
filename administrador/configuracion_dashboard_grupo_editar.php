<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Dashboard Configuración | Grupo-Editar";
    $id_dashboard=validar_input(base64_decode($_GET['reg']));
    $id_grupo=validar_input(base64_decode($_GET['gru']));

    if(isset($_POST["guardar_registro"])){
        $nombre_grupo=validar_input($_POST['nombre_grupo']);
        $descripcion=validar_input($_POST['descripcion']);
        $orden=validar_input($_POST['orden']);
        
        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_dashboard_grupos` SET `gdg_grupo_nombre`=?,`gdg_grupo_descripcion`=?, `gdg_orden`=? WHERE `gdg_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('ssss', $nombre_grupo, $descripcion, $orden, $id_grupo);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gdg_id`, `gdg_dashboard`, `gdg_grupo_nombre`, `gdg_grupo_descripcion`, `gdg_orden` FROM `tb_gestion_dashboard_grupos` WHERE `gdg_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_grupo);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

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
                          <input type="number" step="1" max="999" class="form-control form-control-sm" name="orden" id="orden" maxlength="100" value="<?php echo $resultado_registros[0][4]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="nombre_grupo">Nombre grupo</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_grupo" id="nombre_grupo" maxlength="100" value="<?php echo $resultado_registros[0][2]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>
                          <textarea class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="500"><?php echo $resultado_registros[0][3]; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"]) AND !isset($_POST["reset_contrasena"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"]) OR isset($_POST["reset_contrasena"])): ?>
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