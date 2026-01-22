<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas-Configuración";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Alertas - Configuración | Regional - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $correos=validar_input($_POST['correos']);
        $correos_trimestral=validar_input($_POST['correos_trimestral']);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuesta_regional` SET `gere_correos`=?, `gere_correos_trimestral`=? WHERE `gere_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sss', $correos, $correos_trimestral, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            registro_log($enlace_db, $modulo_plataforma, 'editar', 'Modificación destinatarios alertas '.$correos.'|'.$correos_trimestral);
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gere_id`, `gere_regional`, `gere_id_mapa`, `gere_correos`, `gere_correos_trimestral`, `gere_registro_fecha` FROM `tb_gestion_encuesta_regional` WHERE `gere_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_alertas_regional.php?pagina=".$pagina."&id=".$filtro_permanente;

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
                          <label for="regional">Regional</label>
                          <input type="text" class="form-control form-control-sm" name="regional" id="regional" maxlength="100" value="<?php echo $resultado_registros[0][1]; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="correos">Correos</label>
                          <input type="text" class="form-control form-control-sm" name="correos" id="correos" maxlength="5000" value="<?php echo $resultado_registros[0][3]; ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="correos_trimestral">Correos informes</label>
                          <input type="text" class="form-control form-control-sm" name="correos_trimestral" id="correos_trimestral" maxlength="5000" value="<?php echo $resultado_registros[0][4]; ?>">
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