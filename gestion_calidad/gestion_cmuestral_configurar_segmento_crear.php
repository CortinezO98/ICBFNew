<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Segmento - Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));


    if(isset($_POST["guardar_registro"])){
        $nombre_segmento=validar_input($_POST['nombre_segmento']);
        $peso=validar_input($_POST['peso']);

        if($_SESSION['registro_creado_segmento']!=1){
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_segmento`(`cms_calculadora`, `cms_nombre_segmento`, `cms_peso`) VALUES (?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sss', $id_registro, $nombre_segmento, $peso);
            
            if ($sentencia_insert->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                $_SESSION['registro_creado_segmento']=1;
            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_cmuestral_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro);

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
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="nombre_segmento">Nombre segmento</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_segmento" id="nombre_segmento" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nombre_segmento; } ?>" <?php if($_SESSION['registro_creado_segmento']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="peso">Peso</label>
                          <input type="number" class="form-control form-control-sm" name="peso" id="peso" min="0" max="100" step="0.01" value="<?php if(isset($_POST["guardar_registro"])){ echo $peso; } ?>" <?php if($_SESSION['registro_creado_segmento']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado_segmento']==1): ?>
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