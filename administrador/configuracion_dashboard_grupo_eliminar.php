<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Dashboard Configuración | Grupo-Eliminar";
    $id_dashboard=validar_input(base64_decode($_GET['reg']));
    $id_grupo=validar_input(base64_decode($_GET['gru']));

    if(isset($_POST["eliminar_registro"])){
        if($_SESSION['dashboard_registro_eliminado_grupo']!=1){
            // Prepara la sentencia
            $sentencia_delete_seccion = $enlace_db->prepare("DELETE FROM `tb_gestion_dashboard_grupos` WHERE `gdg_id`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete_seccion->bind_param('s', $id_grupo);
            
            // Evalua resultado de ejecución sentencia preparada
            if ($sentencia_delete_seccion->execute()) {
                $_SESSION['dashboard_registro_eliminado_grupo']=1;
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente!', 0);</script>";
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gdg_id`, `gdg_dashboard`, `gdg_grupo_nombre`, `gdg_grupo_descripcion`, `gdg_orden` FROM `tb_gestion_dashboard_grupos` WHERE `gdg_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_grupo);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_validar = $enlace_db->prepare("SELECT `gdgg_id`, `gdgg_dashboard`, `gdgg_grupo`, `gdgg_tipo_grafica`, `gdgg_nombre`, `gdgg_variable`, `gdgg_orden` FROM `tb_gestion_dashboard_grupos_graficas` WHERE `gdgg_grupo`=?");
    $consulta_validar->bind_param("s", $id_grupo);
    $consulta_validar->execute();
    $resultado_validar = $consulta_validar->get_result()->fetch_all(MYSQLI_NUM);

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
                <?php if(count($resultado_validar)>0): ?>
                    <p class="alert alert-danger">¡No es posible eliminar el registro, por favor verifique e intente nuevamente!</p>
                <?php elseif($_SESSION['dashboard_registro_eliminado_grupo']==1): ?>
                    <p class="alert alert-danger">¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!</p>
                <?php else: ?>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="orden">Orden</label>
                          <input type="number" step="1" max="999" class="form-control form-control-sm" name="orden" id="orden" value="<?php echo $resultado_registros[0][4]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="nombre_grupo">Nombre grupo</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_grupo" id="nombre_grupo" maxlength="100" value="<?php echo $resultado_registros[0][2]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>
                          <textarea class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="500" readonly><?php echo $resultado_registros[0][3]; ?></textarea>
                        </div>
                    </div>
                </div>
                <p class="alert alert-danger">¡El registro será eliminado de forma permanente y no se podrá recuperar, por favor valide antes de continuar!</p>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if(count($resultado_validar)==0 AND $_SESSION['dashboard_registro_eliminado_grupo']!=1): ?>
                            <button class="btn btn-warning float-right ml-1" type="submit" name="eliminar_registro">Si, eliminar</button>
                            <?php endif; ?>
                            <?php if($_SESSION['dashboard_registro_eliminado_grupo']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
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