<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $estado=validar_input($_POST['estado']);
        $nombre_matriz=validar_input($_POST['nombre_matriz']);
        $campania=validar_input($_POST['campania']);
        $descripcion=validar_input($_POST['descripcion']);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuestas_matriz` SET `gem_nombre_encuesta`=?, `gem_estado`=?, `gem_descripcion`=?, `gem_campania`=? WHERE `gem_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssss', $nombre_matriz, $estado, $descripcion, $campania, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha`, TU.`usu_nombres_apellidos`, `gem_campania` FROM `tb_gestion_encuestas_matriz` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuestas_matriz`.`gem_registro_usuario`=TU.`usu_id` WHERE `gem_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_campania = $enlace_db->prepare("SELECT `ac_id`, `ac_nombre_campania`, `ac_observaciones` FROM `tb_administrador_campania` ORDER BY `ac_nombre_campania` ASC");
    $consulta_campania->execute();
    $resultado_campania = $consulta_campania->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_encuestas_matriz.php?pagina=".$pagina."&id=".$filtro_permanente;

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
                            <select class="form-control form-control-sm" name="estado" id="estado" required>
                              <option value="">Seleccione</option>
                              <option value="Activo" <?php if($resultado_registros[0][2]=="Activo"){ echo "selected"; } ?>>Activo</option>
                              <option value="Inactivo" <?php if($resultado_registros[0][2]=="Inactivo"){ echo "selected"; } ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="nombre_matriz" id="nombre_matriz">Nombre matriz encuesta</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_matriz" id="nombre_matriz" maxlength="100" value="<?php echo $resultado_registros[0][1]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="campania">Campaña</label>
                          <select class="form-control form-control-sm" name="campania" id="campania" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_campania); $i++): ?>
                                <option value="<?php echo $resultado_campania[$i][0]; ?>" <?php if($resultado_registros[0][7]==$resultado_campania[$i][0]){ echo "selected"; } ?>><?php echo $resultado_campania[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>
                          <textarea class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="200" ><?php echo $resultado_registros[0][3]; ?></textarea>
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