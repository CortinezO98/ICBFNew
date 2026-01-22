<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Dashboard | Crear";

    if(isset($_POST["guardar_registro"])){
        $orden=validar_input($_POST['orden']);
        $nombre_dashboard=validar_input($_POST['nombre_dashboard']);
        $matriz=validar_input($_POST['matriz']);
        $estado=validar_input($_POST['estado']);
        $campania=validar_input($_POST['campania']);
        
        if($_SESSION['dashboard_registro_creado_dash']!=1){
            $codigo_unico=generar_codigo(10);
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_dashboard`(`gd_id`, `gd_nombre`, `gd_matriz`, `gd_campania`, `gd_orden`, `gd_estado`, `gd_registro_usuario`, `gd_registro_fecha`) VALUES (?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('ssssssss', $codigo_unico, $nombre_dashboard, $matriz, $campania, $orden, $estado, $_SESSION["usu_id"], date('Y-m-d H:i:s'));
            
            if ($sentencia_insert->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                    $_SESSION['dashboard_registro_creado_dash']=1;
            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

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
                          <input type="number" step="1" max="999" class="form-control form-control-sm" name="orden" id="orden" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $orden; } ?>" <?php if($_SESSION['dashboard_registro_creado_dash']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="form-group">
                          <label for="nombre_dashboard">Nombre dashboard</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_dashboard" id="nombre_dashboard" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nombre_dashboard; } ?>" <?php if($_SESSION['dashboard_registro_creado_dash']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="matriz">Matriz encuesta</label>
                          <select class="form-control form-control-sm" name="matriz" id="matriz" required <?php if($_SESSION['dashboard_registro_creado_dash']==1) { echo 'disabled'; } ?>>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_matriz_encuesta); $i++): ?>
                                <option value="<?php echo $resultado_matriz_encuesta[$i][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $matriz==$resultado_matriz_encuesta[$i][0]){ echo "selected"; } ?>><?php echo $resultado_matriz_encuesta[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="estado">Estado</label>
                          <select class="form-control form-control-sm" name="estado" id="estado" required <?php if($_SESSION['dashboard_registro_creado_dash']==1) { echo 'disabled'; } ?>>
                                <option value="">Seleccione</option>
                                <option value="Activo" <?php if(isset($_POST["guardar_registro"]) AND $estado=="Activo"){ echo "selected"; } ?>>Activo</option>
                                <option value="Inactivo" <?php if(isset($_POST["guardar_registro"]) AND $estado=="Inactivo"){ echo "selected"; } ?>>Inactivo</option>
                          </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="campania">Campaña</label>
                          <select class="form-control form-control-sm" name="campania" id="campania" required <?php if($_SESSION['dashboard_registro_creado_dash']==1) { echo 'disabled'; } ?>>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_campania); $i++): ?>
                                <option value="<?php echo $resultado_campania[$i][0]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $campania==$resultado_campania[$i][0]){ echo "selected"; } ?>><?php echo $resultado_campania[$i][1]; ?></option>
                              <?php endfor; ?>
                          </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['dashboard_registro_creado_dash']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
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