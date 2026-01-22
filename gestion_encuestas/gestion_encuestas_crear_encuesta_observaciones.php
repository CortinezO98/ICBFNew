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
    
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["enviar_encuesta"])){
        $_SESSION['registro_detalle_encuesta']['observaciones_cierre']=validar_input($_POST['observaciones_cierre']);
        header('Location: gestion_encuestas_crear_encuesta_guardar.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));

    }

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
                        <div class="titulo-encuesta"><?php echo ($_SESSION['registro_detalle_encuesta']['estado']=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $_SESSION['registro_detalle_encuesta']['nombre']; ?></div>
                        <div class="descripcion-seccion"><?php echo $_SESSION['registro_detalle_encuesta']['descripcion']; ?></div>
                    </div>
                    <div class="col-md-3 titulo-seccion-conteo py-1 my-0">
                        Sección <?php echo count($_SESSION['registro_configuracion_encuesta_secciones'])+3; ?> de <?php echo count($_SESSION['registro_configuracion_encuesta_secciones'])+3; ?>
                    </div>
                    <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                        <div class="row p-0">
                            <div class="col-md-12">
                                <div class="py-2 px-3">
                                    <b>Observaciones</b>
                                </div>
                            </div>
                        </div>
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Observaciones"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <textarea class="form-control form-control-sm" name="observaciones_cierre" value="" placeholder="Respuesta" maxlength="500" required></textarea>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                    </div>
                    <div class="col-md-12 p-0">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="enviar_encuesta">Enviar Encuesta</button>
                            <?php if(count($_SESSION['registro_configuracion_encuesta_navegacion'])>1 AND $_SESSION['registro_configuracion_encuesta_estado_efectivo_final']!=1): ?>
                                <a href="gestion_encuestas_crear_encuesta_diligenciar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $id_filtro; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($id_registro); ?>&regresar=on" class="btn btn-warning float-right ml-1">Regresar</a>
                            <?php elseif(count($_SESSION['registro_configuracion_encuesta_navegacion'])>1 AND $_SESSION['registro_configuracion_encuesta_estado_efectivo_final']): ?>
                                <a href="gestion_encuestas_crear_encuesta_informacion_complementaria.php?pagina=<?php echo $pagina; ?>&id=<?php echo $id_filtro; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($id_registro); ?>&regresar=on" class="btn btn-warning float-right ml-1">Regresar</a>
                            <?php endif; ?>
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