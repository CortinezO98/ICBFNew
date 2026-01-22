<?php
	require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    
    require_once("config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Configuración Seguridad";

    if(isset($_POST["guardar_registro"])){
        $contrasena=validar_input($_POST['contrasena']);
        $contrasena_confirm=validar_input($_POST['contrasena_confirm']);
        $usuario_registro=$_SESSION["usu_id"];
        if($_SESSION['contrasena_actualizada']!=1){
            if ($contrasena==$contrasena_confirm AND $contrasena!=$usuario_registro) {
                $inicio_sesion=1;
                $salt = substr(base64_encode(openssl_random_pseudo_bytes('30')), 0, 22);
                $salt = strtr($salt, array('+' => '.'));
                $contrasena_final = crypt($contrasena, '$2y$10$' . $salt);

                // Prepra la sentencia
                $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_administrador_usuario` SET `usu_contrasena`=?, `usu_inicio_sesion`=? WHERE `usu_id`=?");
                // Agrega variables a sentencia preparada
                $consulta_actualizar->bind_param("sss", $contrasena_final, $inicio_sesion, $usuario_registro);
                // Ejecuta sentencia preparada
                $consulta_actualizar->execute();
                        
                if (comprobarSentencia($enlace_db->info)) {
                  $respuesta_accion = "<p class='alert alert-success'>Contraseña actualizada exitosamente!</p>";
                  $_SESSION['contrasena_actualizada']=1;
                } else {
                  $respuesta_accion = "<p class='alert alert-warning'>¡Problemas al actualizar contraseña, por favor verifique e intente nuevamente!</p>";
                }
            } else {
              $respuesta_accion = "<p class='alert alert-warning'>¡Problemas al actualizar contraseña, por favor verifique e intente nuevamente!</p>";
            }
        } else {
            $respuesta_accion = "<p class='alert alert-success'>¡Contraseña actualizada exitosamente, haga clic en <b>Finalizar</b> para salir!</p>";
        }
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("config/configuracion_estilos_raiz.php");
    ?>
</head>
<body>
    <div class="menu-bar">
        <a class="home">
            <img src="images/logo.png" class="logo_home">
        </a>
        <p class="titulo_header"><?php echo $titulo_header; ?></p>
        <a href="logout.php" class="menu_configuracion" id="tour-11">
            <span class="fas fa-power-off"></span>
        </a>
    </div>
    <div class="contenido">
        <div class="row justify-content-center">
            <div class="col-md-6 background-blanco border-top-rojo">
                <h4>Cambio Contraseña</h4>
                <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
                    <div class="col-md-12">
                        <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                        <div class="form-group">
                          <label for="contrasena">Nueva contraseña*</label>
                          <input type="password" class="form-control" name="contrasena" id="contrasena" minlength="10" maxlength="15" value="" <?php if($_SESSION['contrasena_actualizada']==1) { echo 'readonly'; } ?> required autofocus>
                        </div>
                        <div class="form-group">
                          <label for="contrasena_confirm">Confirme contraseña*</label>
                          <input type="password" class="form-control" name="contrasena_confirm" id="contrasena_confirm" minlength="10" maxlength="15" value="" <?php if($_SESSION['contrasena_actualizada']==1) { echo 'readonly'; } ?> required>
                        </div>
                        
                        <div class="form-group">
                            <?php if($_SESSION['contrasena_actualizada']==1): ?>
                                <a href="logout.php" class="btn btn-dark">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-success" type="submit" name="guardar_registro">Guardar</button>
                            <?php endif; ?>
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