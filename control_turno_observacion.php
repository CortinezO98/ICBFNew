<?php
    error_reporting(0);
    require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    
    require_once("config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Control Turno | Observaciones";

    if(isset($_POST["guardar_registro"])){
        $observaciones=validar_input($_POST['observaciones']);

        if($_SESSION['observacion_turno_creada']!=1){
            // Prepara la sentencia
            $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_control_turno` SET `cot_observaciones_inicio`=? WHERE `cot_usuario`=? AND `cot_tipo`='turno' AND `cot_inicio` LIKE '".date('Y-m-d')."%' AND `cot_fin`=''");

            // Agrega variables a sentencia preparada
            $consulta_actualizar->bind_param('ss', $observaciones, $_SESSION['usu_id']);
            // Ejecuta sentencia preparada
            $consulta_actualizar->execute();

            if (comprobarSentencia($enlace_db->info)) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('Observación creada exitosamente!', 0);</script>";
                $_SESSION['observacion_creada']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear la observación, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('Observación creada exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="contenido.php";
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php
        include("config/configuracion_estilos_raiz.php");
    ?>
</head>
<body>
    <?php
        $menu_principal=includeFileContent("menu_principal.php");
        echo str_replace('../', '', $menu_principal);
        include("menu_header_raiz.php");
    ?>
    <div class="contenido fondo-gris">
        <div class="row justify-content-center">
            <div class="col-md-8 p-1">
                <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                  <label for="observaciones">Observaciones del turno</label>
                                  <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" maxlength="500" <?php if($_SESSION['observacion_creada']==1) { echo 'readonly'; } ?> required autofocus><?php if(isset($_POST["guardar_registro"])){ echo $observaciones; } ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php if($_SESSION['observacion_creada']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php
        include("footer.php");
        include("config/configuracion_js_raiz.php");
    ?>        
</body>
</html>