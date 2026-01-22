<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Dashboard | Editar";
    $id_dashboard=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $orden=validar_input($_POST['orden']);
        $nombre_dashboard=validar_input($_POST['nombre_dashboard']);
        $matriz=validar_input($_POST['matriz']);
        $estado=validar_input($_POST['estado']);
        $campania=validar_input($_POST['campania']);
        
        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_dashboard` SET `gd_nombre`=?,`gd_matriz`=?,`gd_campania`=?,`gd_orden`=?,`gd_estado`=?, `gd_registro_fecha`=? WHERE `gd_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssss', $nombre_dashboard, $matriz, $campania, $orden, $estado, date('Y-m-d H:i:s'), $id_dashboard);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_dashboard = $enlace_db->prepare("SELECT `gd_id`, `gd_nombre`, `gd_matriz`, `gd_campania`, `gd_orden`, `gd_estado`, `gd_registro_usuario`, `gd_registro_fecha`, TM.`gem_nombre_encuesta`, TU.`usu_nombres_apellidos` FROM `tb_gestion_dashboard` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_dashboard`.`gd_matriz`=TM.`gem_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_dashboard`.`gd_registro_usuario`=TU.`usu_id` WHERE `gd_id`=?");
    $consulta_dashboard->bind_param("s", $id_dashboard);
    $consulta_dashboard->execute();
    $resultado_dashboard = $consulta_dashboard->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_matriz_encuesta = $enlace_db->prepare("SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha` FROM `tb_gestion_encuestas_matriz` ORDER BY `gem_nombre_encuesta`");
    $consulta_matriz_encuesta->execute();
    $resultado_matriz_encuesta = $consulta_matriz_encuesta->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_campania = $enlace_db->prepare("SELECT `ac_id`, `ac_nombre_campania`, `ac_observaciones` FROM `tb_administrador_campania` ORDER BY `ac_nombre_campania` ASC");
    $consulta_campania->execute();
    $resultado_campania = $consulta_campania->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="../contenido.php";
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
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="orden">Orden</label>
                          <input type="number" step="1" max="999" class="form-control form-control-sm" name="orden" id="orden" maxlength="100" value="<?php echo $resultado_dashboard[0][4]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="nombre_dashboard">Nombre dashboard</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_dashboard" id="nombre_dashboard" maxlength="100" value="<?php echo $resultado_dashboard[0][1]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="matriz">Matriz encuesta</label>
                          <select class="form-control form-control-sm" name="matriz" id="matriz" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_matriz_encuesta); $i++): ?>
                                <option value="<?php echo $resultado_matriz_encuesta[$i][0]; ?>" <?php if($resultado_dashboard[0][2]==$resultado_matriz_encuesta[$i][0]){ echo "selected"; } ?>><?php echo $resultado_matriz_encuesta[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="estado">Estado</label>
                          <select class="form-control form-control-sm" name="estado" id="estado" required>
                                <option value="">Seleccione</option>
                                <option value="Activo" <?php if($resultado_dashboard[0][5]=="Activo"){ echo "selected"; } ?>>Activo</option>
                                <option value="Inactivo" <?php if($resultado_dashboard[0][5]=="Inactivo"){ echo "selected"; } ?>>Inactivo</option>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="campania">Campaña</label>
                          <select class="form-control form-control-sm" name="campania" id="campania" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_campania); $i++): ?>
                                <option value="<?php echo $resultado_campania[$i][0]; ?>" <?php if($resultado_dashboard[0][3]==$resultado_campania[$i][0]){ echo "selected"; } ?>><?php echo $resultado_campania[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"]) AND !isset($_POST["reset_contrasena"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"]) OR isset($_POST["reset_contrasena"])): ?>
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