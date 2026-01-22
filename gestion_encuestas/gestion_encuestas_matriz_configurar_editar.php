<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Matriz Calidad";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz de Calidad | Configurar Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_item=validar_input(base64_decode($_GET['item']));
    $tipo_item=validar_input(base64_decode($_GET['tipo']));

    if(isset($_POST["guardar_registro"])){
        $tipo=validar_input($_POST['tipo']);
        $consecutivo=validar_input($_POST['consecutivo']);
        $orden=validar_input($_POST['orden']);
        $nombre=validar_input($_POST['nombre']);
        $peso=validar_input($_POST['peso']);
        $calificable=validar_input($_POST['calificable']);
        $grupo_peso=validar_input($_POST['grupo_peso']);
        $visible=validar_input($_POST['visible']);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calidad_matriz_item` SET `gcmi_item_tipo`=?,`gcmi_item_consecutivo`=?,`gcmi_item_orden`=?,`gcmi_descripcion`=?,`gcmi_peso`=?,`gcmi_calificable`=?,`gcmi_grupo_peso`=?,`gcmi_visible`=? WHERE  `gcmi_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssssss', $tipo, $consecutivo, $orden, $nombre, $peso, $calificable, $grupo_peso, $visible, $id_item);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible` FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_item);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_matriz_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro);

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
                          <label for="nombre" id="nombre">Nombre</label>
                          <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" maxlength="200" value="<?php echo $resultado_registros[0][5]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <input type="text" class="form-control form-control-sm" name="tipo" id="tipo" minlength="1" maxlength="10" value="<?php echo $resultado_registros[0][2]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="consecutivo" id="consecutivo">Consecutivo</label>
                          <input type="text" class="form-control form-control-sm" name="consecutivo" id="consecutivo" minlength="1" maxlength="10" value="<?php echo $resultado_registros[0][3]; ?>" required>
                        </div>
                    </div>
                    <?php if($tipo_lista!="Grupo"): ?>
                        <?php if($tipo_lista!="Sub-Grupo"): ?>
                            <div class="col-md-2">
                                <div class="form-group">
                                  <label for="orden" id="orden">Orden</label>
                                  <input type="number" class="form-control form-control-sm" name="orden" id="orden" min="1" maxlength="10" value="<?php echo $resultado_registros[0][4]; ?>" required>
                                </div>
                            </div>
                        <?php endif; ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="calificable">Calificable</label>
                            <select class="form-control form-control-sm" name="calificable" id="calificable" required>
                              <option value="">Seleccione</option>
                              <option value="Si" <?php if($resultado_registros[0][7]=="Si"){ echo "selected"; } ?>>Si</option>
                              <option value="No" <?php if($resultado_registros[0][7]=="No"){ echo "selected"; } ?>>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="peso" id="peso">Peso</label>
                          <input type="number" class="form-control form-control-sm" name="peso" id="peso" min="1" step="0.1" max="100" value="<?php echo $resultado_registros[0][6] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="grupo_peso">Grupo peso</label>
                          <input type="number" class="form-control form-control-sm" name="grupo_peso" id="grupo_peso" min="1" step="0.1" max="100" value="<?php echo $resultado_registros[0][8] ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="visible">Visible</label>
                            <select class="form-control form-control-sm" name="visible" id="visible" required>
                              <option value="">Seleccione</option>
                              <option value="Si" <?php if($resultado_registros[0][9]=="Si"){ echo "selected"; } ?>>Si</option>
                              <option value="No" <?php if($resultado_registros[0][9]=="No"){ echo "selected"; } ?>>No</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if(!isset($_POST["guardar_registro"])): ?>
                                <button class="btn btn-danger" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                                <button class="btn btn-success" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(isset($_POST["guardar_registro"])): ?>
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