<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    $consulta_string_matriz="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha` FROM `tb_gestion_encuestas_matriz` WHERE `gem_estado`='Activo'";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_encuestas.php?pagina=".$pagina."&id=".$filtro_permanente;

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
            <div class="col-md-5 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <p class="alert alert-success p-1">¡Seleccione una encuesta para iniciar!</p>
                    </div>
                    <div class="col-md-12">
                        <?php for ($i=0; $i < count($resultado_registros_matriz); $i++): ?>
                            <a href="gestion_encuestas_crear_encuesta_generar.php?mat=<?php echo base64_encode($resultado_registros_matriz[$i][0]); ?>" class="btn btn-corp col-md-12 text-left mb-1"><?php echo $resultado_registros_matriz[$i][1]; ?><span class="fas fa-arrow-right float-right"></span></a>
                        <?php endfor; ?>    
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 pt-2">
                        <div class="form-group">
                            <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
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