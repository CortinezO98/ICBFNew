<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Campañas | Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $nombre_campania=validar_input($_POST['nombre_campania']);
        $observaciones=validar_input($_POST['observaciones']);
        
        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_administrador_campania` SET `ac_nombre_campania`=?,`ac_observaciones`=? WHERE `ac_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sss', $nombre_campania, $observaciones, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `ac_id`, `ac_nombre_campania`, `ac_observaciones` FROM `tb_administrador_campania` WHERE `ac_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_campanias.php?pagina=".$pagina."&id=".$filtro_permanente;

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
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="nombre_campania">Nombre campaña</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_campania" id="nombre_campania" maxlength="100" value="<?php echo $resultado_registros[0][1]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" maxlength="500"><?php echo $resultado_registros[0][2]; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if(!isset($_POST["guardar_registro"]) AND !isset($_POST["reset_contrasena"])): ?>
                                <button class="btn btn-danger" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                                <button class="btn btn-success" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(isset($_POST["guardar_registro"]) OR isset($_POST["reset_contrasena"])): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark">Finalizar</a>
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