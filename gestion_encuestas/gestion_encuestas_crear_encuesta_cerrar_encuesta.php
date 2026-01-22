<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Información General";
    
    $pagina=validar_input($_GET['pagina']);
    $id_filtro=validar_input($_GET['id']);
    $estado_bandeja=validar_input($_GET['est']);
    $id_matriz=validar_input(base64_decode($_GET['mat']));
    $id_registro=validar_input(base64_decode($_GET['reg']));

    $ruta_cancelar_finalizar="gestion_encuestas.php?pagina=".$pagina."&id=".$id_filtro."&est=".$estado_bandeja;
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
                        <div class="titulo-encuesta">Se encontró un registro de encuesta en curso, por favor cierre la gestión e intente nuevamente!</div>
                        <div class="descripcion-seccion"><?php echo $_SESSION['registro_detalle_encuesta']['descripcion']; ?></div>
                    </div>
                    <div class="col-md-12 p-0">
                        <div class="form-group">
                            <a href="gestion_encuestas_crear_encuesta_generar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $id_filtro; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($id_registro); ?>&mat=<?php echo base64_encode($id_matriz); ?>" class="btn btn-warning float-right ml-1">Validar de nuevo</a>
                            <a href="gestion_encuestas_crear_encuesta_generar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $id_filtro; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($id_registro); ?>&mat=<?php echo base64_encode($id_matriz); ?>&cierre=forzado" class="btn btn-danger float-right ml-1">Forzar cierre de encuesta activa</a>
                            <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
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