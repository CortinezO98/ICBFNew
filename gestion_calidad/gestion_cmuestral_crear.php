<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    if(isset($_POST["guardar_registro"])){
        $nombre_matriz=validar_input($_POST['nombre_matriz']);
        $intervalo_confianza=validar_input($_POST['intervalo_confianza']);
        $valor_z=validar_input($_POST['valor_z']);
        $varianza_estimada=validar_input($_POST['varianza_estimada']);
        $error_muestral=validar_input($_POST['error_muestral']);
        $usuario_registro=$_SESSION["usu_id"];

        $segmento=$_POST['segmento'];
        if (!isset($segmento)) {
            $segmento=array();
        }

        $peso=$_POST['peso'];
        if (!isset($peso)) {
            $peso=array();
        }

        if($_SESSION['registro_creado']!=1){
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral`(`cm_nombre`, `cm_intervalo_confianza`, `cm_valor_z`, `cm_varianza_estimada`, `cm_error_muestral`, `cm_registro_usuario`) VALUES (?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('ssssss', $nombre_matriz, $intervalo_confianza, $valor_z, $varianza_estimada, $error_muestral, $usuario_registro);
            
            if ($sentencia_insert->execute()) {
                $id_calculadora = mysqli_insert_id($enlace_db);
                // Prepara la sentencia
                $sentencia_insert_segmento = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_segmento`(`cms_calculadora`, `cms_nombre_segmento`, `cms_peso`) VALUES (?,?,?)");

                // Agrega variables a sentencia preparada
                $sentencia_insert_segmento->bind_param('sss', $id_calculadora, $segmento_valor_insert, $peso_valor_insert);
                $control_insert_segmento=0;
                if (isset($segmento)) {
                    $total_segmento=count($segmento);
                    for ($i=0; $i < count($segmento); $i++) { 
                        $segmento_valor_insert=$segmento[$i];
                        $peso_valor_insert=$peso[$i];
                        if ($sentencia_insert_segmento->execute()) {
                            $control_insert_segmento++;
                        }
                    }
                } else {
                    $total_segmento=0;
                }

                if ($total_segmento==$control_insert_segmento) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                    $_SESSION['registro_creado']=1;
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }
            } else {
              $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_cmuestral.php?pagina=".$pagina."&id=".$filtro_permanente;
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
                          <label for="nombre_matriz">Nombre calculadora</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_matriz" id="nombre_matriz" maxlength="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nombre_matriz; } ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="intervalo_confianza">Intervalo confianza</label>
                          <input type="number" class="form-control form-control-sm" name="intervalo_confianza" id="intervalo_confianza" min="0" max="100" step="0.01" value="<?php if(isset($_POST["guardar_registro"])){ echo $intervalo_confianza; } ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="valor_z">Valor Z</label>
                          <input type="number" class="form-control form-control-sm" name="valor_z" id="valor_z" min="0" max="100" step="0.01" value="<?php if(isset($_POST["guardar_registro"])){ echo $valor_z; } ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="varianza_estimada">Varianza estimada</label>
                          <input type="number" class="form-control form-control-sm" name="varianza_estimada" id="varianza_estimada" min="0" max="100" step="0.01" value="<?php if(isset($_POST["guardar_registro"])){ echo $varianza_estimada; } ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="error_muestral">Error muestral</label>
                          <input type="number" class="form-control form-control-sm" name="error_muestral" id="error_muestral" min="0" max="100" step="0.01" value="<?php if(isset($_POST["guardar_registro"])){ echo $error_muestral; } ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                        </div>
                    </div>
                    <div class="col-md-12 mt-1" id="div_segmento">
                        <div class="form-group my-1">
                            <label for="segmento" class="font-weight-bold font-size-11 m-0">Segmento/Porcentaje</label>
                            <div class="row" id="opciones_respuestas_segmento">
                                <?php if(isset($segmento)): ?>
                                    <?php for ($i=0; $i < count($segmento); $i++): ?>
                                        <div class="row lista_segmento px-4 col-md-12">
                                            <div class="form-group col-6 mb-1 px-2">
                                                <input type="text" class="form-control form-control-sm" name="segmento[]" id="segmento" maxlength="100" value="<?php echo $segmento[$i]; ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                                            </div>
                                            <div class="form-group col-5 mb-1 px-2">
                                                <input type="number" class="form-control form-control-sm" name="peso[]" id="peso" min="0" max="100" value="<?php echo $peso[$i]; ?>" <?php if($_SESSION['registro_creado']==1) { echo 'readonly'; } ?> required>
                                            </div>
                                            <?php if($_SESSION['registro_creado']!=1): ?>
                                                <div class="col-1 mb-1 ps-1">
                                                    <a href="#" class="color-rojo font-size-11 pt-2 ps-0" id="del_field_segmento" title="Quitar segmento"><span class="fas fa-trash-alt"></span></a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </div>
                            <?php if($_SESSION['registro_creado']!=1): ?>
                                <a href="#" class="btn btn-primary font-size-11 p-0 mt-1" style="display: block; width: 185px;" id="add_field_segmento" title="Añadir segmento"><span class="fas fa-plus"></span> Añadir segmento</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['registro_creado']==1): ?>
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
    <script type="text/javascript">
        var campos_max = 50;

        var x = 0;
        $('#add_field_segmento').click (function(e) {
            e.preventDefault();
            if (x < campos_max) {
                $('#opciones_respuestas_segmento').append('<div class="row lista_segmento px-4 col-md-12">\
                    <div class="form-group col-6 mb-1 px-2">\
                        <input type="text" class="form-control form-control-sm" name="segmento[]" id="segmento_'+x+'" maxlength="100" value="" required>\
                    </div>\
                    <div class="form-group col-5 mb-1 px-2">\
                        <input type="number" class="form-control form-control-sm" name="peso[]" id="peso_'+x+'" min="0" max="100" value="" required>\
                    </div>\
                    <div class="col-1 mb-1 ps-1">\
                        <a href="#" class="color-rojo font-size-11 pt-2 ps-0" id="del_field_segmento" title="Quitar segmento"><span class="fas fa-trash-alt"></span></a>\
                    </div>\
                </div>');
                $('#segmento_'+x).selectpicker('refresh');
                x++;
            }
        });

        $('#opciones_respuestas_segmento').on("click","#del_field_segmento",function(e) {
            e.preventDefault();
            $(this).parents('div.lista_segmento').remove();
            x--;
        });
    </script>
</body>
</html>