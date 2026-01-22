<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Vista Previa";

    $ruta_cancelar_finalizar="gestion_encuestas_matriz_configurar_vista_generar.php?reg=".base64_encode($_SESSION['detalle_encuesta']['id']);
    
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
    <div class="contenido"  style="background-color: #ede7f6;">
        <div class="row justify-content-center" id="elemento_1">
            <div class="col-md-6 py-2">
                
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
            <form name="guardar_seccion" action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                        <div class="titulo-encuesta"><?php echo ($_SESSION['detalle_encuesta']['estado']=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $_SESSION['detalle_encuesta']['nombre']; ?></div>
                        <div class="descripcion-seccion"><?php echo $_SESSION['detalle_encuesta']['descripcion']; ?></div>
                    </div>
                    <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 pt-2">
                        <p class="alert alert-success p-1 mb-2">Encuesta registrada exitosamente!</p>
                    </div>
                    
                    <div class="col-md-12 p-0">
                        <div class="form-group">
                            <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </div>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
</body>
</html>