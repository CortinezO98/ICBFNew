<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Matriz Calidad";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz de Calidad | Configurar Eliminar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_item=validar_input(base64_decode($_GET['item']));
    $tipo_item=validar_input(base64_decode($_GET['tipo']));

    if(isset($_POST["eliminar_registro"])){
        if($_SESSION['gcmc_registro_eliminado']!=1){

            // Prepara la sentencia
            $sentencia_delete = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_id`=?");

            // Agrega variables a sentencia preparada
            $sentencia_delete->bind_param('s', $id_item);
            
            // Evalua resultado de ejecución sentencia preparada
            if ($sentencia_delete->execute()) {
                $_SESSION['gcmc_registro_eliminado']=1;
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente!', 0);</script>";
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id` FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_id`=?";

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
        <form name="eliminar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <div class="col-md-6 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="consecutivo" id="consecutivo">Consecutivo</label>
                          <input type="text" class="form-control form-control-sm" name="consecutivo" id="consecutivo" minlength="1" maxlength="10" value="<?php echo $resultado_registros[0][3]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="nombre" id="nombre">Nombre</label>
                          <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" maxlength="200" value="<?php echo $resultado_registros[0][5]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">Tipo sección</label>
                            <input type="text" class="form-control form-control-sm" name="tipo" id="tipo" minlength="1" maxlength="10" value="<?php echo $resultado_registros[0][2]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="tipo_error">Tipo de error</label>
                            <select class="form-control form-control-sm" name="tipo_error" id="tipo_error" disabled>
                                <option value="">Seleccione</option>
                                <option value="ENC" <?php if($resultado_registros[0][10]=="ENC"){ echo "selected"; } ?>>ERROR NO CRÍTICO</option>
                                <option value="ECU" <?php if($resultado_registros[0][10]=="ECU"){ echo "selected"; } ?>>ERROR CRÍTICO DE USUARIO</option>
                                <option value="ECN" <?php if($resultado_registros[0][10]=="ECN"){ echo "selected"; } ?>>ERROR CRÍTICO DE NEGOCIO</option>
                            </select>
                        </div>
                    </div>
                    <?php if($tipo_lista!="Grupo"): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="calificable">Calificable</label>
                                <select class="form-control form-control-sm" name="calificable" id="calificable" disabled>
                                  <option value="">Seleccione</option>
                                  <option value="Si" <?php if($resultado_registros[0][7]=="Si"){ echo "selected"; } ?>>Si</option>
                                  <option value="No" <?php if($resultado_registros[0][7]=="No"){ echo "selected"; } ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                              <label for="peso" id="peso">Peso</label>
                              <input type="number" class="form-control form-control-sm" name="peso" id="peso" min="1" step="0.1" max="100" value="<?php echo $resultado_registros[0][6] ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                              <label for="grupo_peso">Grupo peso</label>
                              <input type="number" class="form-control form-control-sm" name="grupo_peso" id="grupo_peso" min="1" step="0.1" max="100" value="<?php echo $resultado_registros[0][8] ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="visible">Visible</label>
                                <select class="form-control form-control-sm" name="visible" id="visible" disabled>
                                  <option value="">Seleccione</option>
                                  <option value="Si" <?php if($resultado_registros[0][9]=="Si"){ echo "selected"; } ?>>Si</option>
                                  <option value="No" <?php if($resultado_registros[0][9]=="No"){ echo "selected"; } ?>>No</option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="alert alert-danger p-1">El registro será eliminado de forma permanente y no se podrá recuperar, por favor valide antes de continuar!</p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if(count($resultado_registros_validar)==0 AND $_SESSION['gcmc_registro_eliminado']!=1): ?>
                            <button class="btn btn-warning float-right ml-1" type="submit" name="eliminar_registro">Si, eliminar</button>
                            <?php endif; ?>
                            <?php if($_SESSION['gcmc_registro_eliminado']==1): ?>
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