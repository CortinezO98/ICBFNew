<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas-Configuración";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Alertas - Configuración | Destinatarios - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $correos_to=validar_input($_POST['correos_to']);
        $correos_cc=validar_input($_POST['correos_cc']);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_alertas_destinatarios` SET `gad_correos_to`=?, `gad_correos_cc`=? WHERE `gad_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sss', $correos_to, $correos_cc, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            registro_log($enlace_db, $modulo_plataforma, 'editar', 'Modificación destinatarios alertas '.$correos_to.'|'.$correos_cc);
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gad_id`, `gad_filtro`, `gad_correos_to`, `gad_correos_cc` FROM `tb_gestion_alertas_destinatarios` WHERE `gad_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_alertas_destinatarios.php?pagina=".$pagina."&id=".$filtro_permanente;
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
            <div class="col-md-4 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="filtro">Filtro</label>
                          <input type="text" class="form-control form-control-sm" name="filtro" id="filtro" maxlength="100" value="<?php echo $resultado_registros[0][1]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="correos_to">Correos TO</label>
                          <input type="text" class="form-control form-control-sm" name="correos_to" id="correos_to" maxlength="5000" value="<?php echo $resultado_registros[0][2]; ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="correos_cc">Correos CC</label>
                          <input type="text" class="form-control form-control-sm" name="correos_cc" id="correos_cc" maxlength="5000" value="<?php echo $resultado_registros[0][3]; ?>">
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