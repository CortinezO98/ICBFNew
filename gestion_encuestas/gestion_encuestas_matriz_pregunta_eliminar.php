<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Pregunta - Eliminar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_seccion=validar_input(base64_decode($_GET['sec']));
    $id_pregunta=validar_input(base64_decode($_GET['pre']));

    if(isset($_POST["eliminar_registro"])){
        if($_SESSION['registro_eliminado_pregunta_encuesta']!=1){
            // Prepara la sentencia
            $sentencia_delete_pregunta = $enlace_db->prepare("DELETE FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_id`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete_pregunta->bind_param('s', $id_pregunta);
            
            // Evalua resultado de ejecución sentencia preparada
            if ($sentencia_delete_pregunta->execute()) {
                $_SESSION['registro_eliminado_pregunta_encuesta']=1;
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente!', 0);</script>";
                // Prepara la sentencia
                $sentencia_delete_opcion = $enlace_db->prepare("DELETE FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_pregunta`=?");
                // Agrega variables a sentencia preparada
                $sentencia_delete_opcion->bind_param('s', $id_pregunta);
                $sentencia_delete_opcion->execute();
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al eliminar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar`, TS.`gems_nombre_seccion` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` LEFT JOIN `tb_gestion_encuestas_matriz_seccion` AS TS ON `tb_gestion_encuestas_matriz_seccion_pregunta`.`gemp_seccion`=TS.`gems_id` WHERE `gemp_id`=? AND `gemp_seccion`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("ss", $id_pregunta, $id_seccion);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

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
        <form name="eliminar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <div class="col-md-6 pt-2 background-blanco">
                <?php if($_SESSION['registro_eliminado_pregunta_encuesta']==1): ?>
                    <p class="alert alert-danger">Registro eliminado exitosamente, haga clic en <b>Finalizar</b> para salir!</p>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                              <label for="seccion" id="seccion">Sección</label>
                              <input type="text" class="form-control form-control-sm" name="seccion" id="seccion" maxlength="3" value="<?php echo $resultado_registros[0][9]; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <label for="pregunta" id="pregunta">Pregunta</label>
                              <input type="text" class="form-control form-control-sm" name="pregunta" id="pregunta" maxlength="300" value="<?php echo $resultado_registros[0][3]; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <label for="descripcion" id="descripcion">Descripción</label>
                              <input type="text" class="form-control form-control-sm" name="descripcion" id="descripcion" maxlength="800" value="<?php echo $resultado_registros[0][4]; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <p class="alert alert-danger">¡El registro será eliminado de forma permanente y no se podrá recuperar, por favor valide antes de continuar!</p>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if(count($resultado_registros_validar)==0 AND $_SESSION['registro_eliminado_pregunta_encuesta']!=1): ?>
                            <button class="btn btn-warning float-right ml-1" type="submit" name="eliminar_registro">Si, eliminar</button>
                            <?php endif; ?>
                            <?php if($_SESSION['registro_eliminado_pregunta_encuesta']==1): ?>
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