<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Usuarios | Actualizar Supervisor";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    if(isset($_POST["guardar_registro"])){
        $documento_identidad=$_POST['documento_identidad'];

        $valore = chop($documento_identidad);

        $valores = nl2br($valore);
        $array_datos = explode("<br />", $valores);

        if (!isset($array_datos)) {
            $array_datos=array();
        }


        $supervisor=validar_input($_POST['supervisor']);

        if($_SESSION['registro_actualizado_supervisor']!=1){
            // Prepara la sentencia
            $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_administrador_usuario` SET `usu_supervisor`=? WHERE `usu_id`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar->bind_param('ss', $supervisor, $id_usuario);
            
            $control_insert_usuarios=0;
            for ($i=0; $i < count($array_datos); $i++) { 
                $id_usuario=trim($array_datos[$i]);

                // Ejecuta sentencia preparada
                $consulta_actualizar->execute();
                if (comprobarSentencia($enlace_db->info)) {
                    $control_insert_usuarios++;
                    $array_actualizado[]=$id_usuario;
                } else {
                    $array_actualizado_error[]=$id_usuario;
                }
            }
            if ($control_insert_usuarios==count($array_datos)) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                $_SESSION['registro_actualizado_supervisor']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar algunos registros, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    if (!isset($array_actualizado)) {
        $array_actualizado=array();
    }

    if (!isset($array_actualizado_error)) {
        $array_actualizado_error=array();
    }

    $consulta_string_supervisor="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE `usu_cargo_rol`='Supervisor' OR `usu_cargo_rol`='Sistema' OR `usu_cargo_rol`='Supervisor' OR `usu_cargo_rol`='Coordinador' OR `usu_cargo_rol`='Líder de calidad y formación' ORDER BY `usu_nombres_apellidos`";

    $consulta_registros_supervisor = $enlace_db->prepare($consulta_string_supervisor);
    $consulta_registros_supervisor->execute();
    $resultado_registros_supervisor = $consulta_registros_supervisor->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_usuarios.php?pagina=".$pagina."&id=".$filtro_permanente;

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
                            <label for="supervisor">Supervisor</label>
                            <select class="form-control form-control-sm" name="supervisor" id="supervisor" <?php if($_SESSION['registro_actualizado_supervisor']==1) { echo 'disabled'; } ?> required>
                                <option value="">Seleccione</option>
                                <?php for ($i=0; $i < count($resultado_registros_supervisor); $i++): ?> 
                                    <option value="<?php echo $resultado_registros_supervisor[$i][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $supervisor==$resultado_registros_supervisor[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_supervisor[$i][1]; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="documento_identidad">Documento identidad</label>
                          <textarea class="form-control form-control-sm" name="documento_identidad" id="documento_identidad" <?php if($_SESSION['registro_actualizado_supervisor']==1) { echo 'readonly'; } ?> required rows="10"><?php if(isset($_POST["guardar_registro"])){ echo $documento_identidad; } ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?php if (count($array_actualizado)>0): ?>
                            <p class="alert alert-success p-1 col-md-12 font-size-11">
                                <b>Registros actualizados correctamente:</b><br>
                                <?php for ($i=0; $i < count($array_actualizado); $i++): ?>
                                    <?php echo $array_actualizado[$i].'<br>'; ?>
                                <?php endfor; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (count($array_actualizado_error)>0): ?>
                            <p class="alert alert-warning p-1 col-md-12 font-size-11">
                                <b>Registros no actualizados:</b><br>
                                <?php for ($i=0; $i < count($array_actualizado_error); $i++): ?>
                                    <?php echo $array_actualizado_error[$i].'<br>'; ?>
                                <?php endfor; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_actualizado_supervisor']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-danger" type="button" onclick="guardar_cancelar();">Cancelar</button>
                                <button class="btn btn-success" type="submit" name="guardar_registro">Guardar</button>
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