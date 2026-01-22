<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\IOFactory;

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Reasignar - Base";

    if(isset($_POST["cargar_base"])){
        $extension = pathinfo( $_FILES['documento']['name'], PATHINFO_EXTENSION);
        
        if ($_SESSION['registro_reasignar_base']!=1) {
            if ($_FILES['documento']["error"] > 0) {
                $message_error = "Problemas al cargar el documento, por favor intente más tarde!";
            } else {
                /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                $nombre_directorio="storage_temporal/";
                $nombre_archivo=$_FILES['documento']['name'];
                if (move_uploaded_file($_FILES['documento']['tmp_name'], $nombre_directorio.$nombre_archivo)) {
                    $nombre_archivo = $nombre_directorio.$nombre_archivo;

                    if (file_exists ($nombre_archivo)){
                        clearstatcache();
                        // unset($objPHPExcel);
                        // unset($objReader);
                        // ini_set('memory_limit', '2048M');

                        $documento = IOFactory::load($nombre_archivo);
                        $hojaActual = $documento->getSheet(0);
                        $numeroMayorDeFila = $hojaActual->getHighestRow();

                        $control_item=0;

                        for ($indicefila = 2; $indicefila <= $numeroMayorDeFila; $indicefila++) {
                            $columna_a = $hojaActual->getCellByColumnAndRow(1, $indicefila)->getValue();
                            $array_data_base[$control_item]['A']=validar_input($columna_a);
                            $control_item++;
                        }

                        // Prepara la sentencia
                        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuesta_radicado` SET `gera_usuario_gestion`=? WHERE `gera_radicado`=?");

                        // Agrega variables a sentencia preparada
                        $consulta_actualizar->bind_param('ss', $_SESSION['usuarios_reasignacion'][0], $columna_a);
                        
                        $control_insert=0;
                        $control_fail=0;
                        $string_fail="";
                        for ($i=0; $i < count($array_data_base); $i++) { 
                            $columna_a=$array_data_base[$i]['A'];

                            // Ejecuta sentencia preparada
                            $consulta_actualizar->execute();

                            if (comprobarSentencia($enlace_db->info)) {
                                $control_insert++;
                            } else {
                                $control_fail++;
                                $string_fail.=$columna_a."\r\n";
                            }
                        }

                        if (($control_insert+$control_fail)==count($array_data_base)) {
                            $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>Base cargada exitosamente | Cargado: ".$control_insert." | Error: ".$control_fail."</p>";
                            $_SESSION['registro_reasignar_base']=1;
                            $nombre_temporal_control="storage_temporal/REASIGNAR_FAIL".date('YmdHis').".txt";
                            $archivo_fail = fopen($nombre_temporal_control,'a');
                            fputs($archivo_fail,$string_fail);
                            fclose($archivo_fail);
                        } else {
                            $respuesta_accion = "<p class='alert alert-warning p-1 mb-2'>¡Problemas al cargar base, por favor intente nuevamente!</p>";
                        }
                    } else {
                      $respuesta_accion = "<p class='alert alert-warning p-1 mb-2'>¡Problemas al cargar base, por favor intente nuevamente!</p>";
                    }
                } else {
                  $respuesta_accion = "<p class='alert alert-warning p-1 mb-2'>¡Problemas al cargar base, por favor intente nuevamente!</p>";
                }
            }
        } else {
            $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>¡La base ya ha sido cargada, haga clic en Finalizar para salir!</p>";
        }
    }

    $consulta_string_usuarios="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE `usu_estado`='Activo'";
    $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
    $consulta_registros_usuarios->execute();
    $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_usuarios); $i++) { 
        $array_nombre_usuario[$resultado_registros_usuarios[$i][0]]=$resultado_registros_usuarios[$i][1];
    }

    $ruta_cancelar_finalizar="gestion_encuestas.php?pagina=1&id=null&est=Pendientes";
    
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
        <div class="row">
            <div class="col-md-3 py-2">
                
            </div>
            <div class="col-md-9 conten-justify-center py-2">
                
            </div>
        </div>
        <form name="cargar_base" action="" method="POST" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <p class="alert alert-success p-1"><b>Usuarios seleccionados para asignación:</b><br><br>
                    <?php for ($i=0; $i < count($_SESSION['usuarios_reasignacion']); $i++) { 
                        echo "<span class='fas fa-user'></span> ".$array_nombre_usuario[$_SESSION['usuarios_reasignacion'][$i]]."<br>"; 
                    } ?>
                    </p>
                    
                </div>
                <div class="col-md-6">
                    <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                    <?php if($control_fail>0): ?>
                        <p class="alert alert-danger p-1">Descargar log de radicados con error: <a href="gestion_encuestas_base_descargar_error.php?ruta=<?php echo $nombre_temporal_control; ?>" target="_blank"><span class="fas fa-download"></span> Descargar</a></p>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="inputGroupFile01">Documento base</label>
                                <div class="custom-file">
                                    <input type="file" name="documento" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01" <?php if($_SESSION['registro_reasignar_base']==1) { echo 'disabled'; } ?> accept=".xlsx, .XLSX" required>
                                    <label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01label">Seleccione un archivo</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php if($_SESSION['registro_reasignar_base']==1): ?>
                                    <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                                <?php else: ?>
                                    <button class="btn btn-success float-right ml-1" type="submit" name="cargar_base">Cargar base</button>
                                    <a href="gestion_encuestas_base_asignar_usuarios.php" class="btn btn-warning float-right ml-1">Regresar</a>
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
        $("#inputGroupFile01").change(function(){
            var valor_opcion = document.getElementById("inputGroupFile01").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
            }
        });
    </script>
</body>
</html>