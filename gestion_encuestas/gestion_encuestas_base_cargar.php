<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\IOFactory;

    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Cargar - Base";

    $consulta_string_regional="SELECT `gere_id`, `gere_regional` FROM `tb_gestion_encuesta_regional`";
    $consulta_registros_regional = $enlace_db->prepare($consulta_string_regional);
    $consulta_registros_regional->execute();
    $resultado_registros_regional = $consulta_registros_regional->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_regional); $i++) { 
        $array_regional[$resultado_registros_regional[$i][1]]=$resultado_registros_regional[$i][0];
    }

    $consulta_string_czonal="SELECT `gercz_id`, `gercz_regional`, `gercz_centro_zonal` FROM `tb_gestion_encuesta_regional_czonal`";
    $consulta_registros_czonal = $enlace_db->prepare($consulta_string_czonal);
    $consulta_registros_czonal->execute();
    $resultado_registros_czonal = $consulta_registros_czonal->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_czonal); $i++) { 
        $array_czonal[$resultado_registros_czonal[$i][1]][$resultado_registros_czonal[$i][2]]=$resultado_registros_czonal[$i][0];
    }

    if(isset($_POST["cargar_base"])){
        $extension = pathinfo( $_FILES['documento']['name'], PATHINFO_EXTENSION);
        $tipo_encuesta=validar_input($_POST['tipo_encuesta']);
        
        if ($_SESSION['registro_cargue_base']!=1) {
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

                        $numero_total_registros=$numeroMayorDeFila-1;

                        $cantidad_xusuario=$numero_total_registros/count($_SESSION['usuarios_asignacion']);

                        for ($i=0; $i < count($_SESSION['usuarios_asignacion']); $i++) { 
                            $array_usuario_cantidad[$_SESSION['usuarios_asignacion'][$i]]=intval($cantidad_xusuario);
                            $total_asignado+=intval($cantidad_xusuario);

                            $usuario_pendiente_asignacion[$_SESSION['usuarios_asignacion'][$i]]=1;
                        }
                        
                        $total_pendiente_asignar=$numero_total_registros-$total_asignado;
                        
                        $array_usuario_cantidad[$_SESSION['usuarios_asignacion'][array_rand($_SESSION['usuarios_asignacion'])]]+=$total_pendiente_asignar;

                        $control_item=0;

                        for ($indicefila = 2; $indicefila <= $numeroMayorDeFila; $indicefila++) {
                            $columna_a = $hojaActual->getCellByColumnAndRow(1, $indicefila)->getValue();
                            $columna_b = $hojaActual->getCellByColumnAndRow(2, $indicefila)->getValue();
                            $columna_c = $hojaActual->getCellByColumnAndRow(3, $indicefila)->getValue();
                            $columna_d = $hojaActual->getCellByColumnAndRow(4, $indicefila)->getValue();
                            $columna_e = $hojaActual->getCellByColumnAndRow(5, $indicefila)->getValue();
                            $columna_f = $hojaActual->getCellByColumnAndRow(6, $indicefila)->getValue();
                            $columna_g = $hojaActual->getCellByColumnAndRow(7, $indicefila)->getValue();
                            $columna_h = $hojaActual->getCellByColumnAndRow(8, $indicefila)->getValue();
                            $columna_i = $hojaActual->getCellByColumnAndRow(9, $indicefila)->getValue();
                            $columna_j = $hojaActual->getCellByColumnAndRow(10, $indicefila)->getValue();

                            $columna_k = $hojaActual->getCellByColumnAndRow(11, $indicefila)->getValue();
                            $columna_l = $hojaActual->getCellByColumnAndRow(12, $indicefila)->getValue();
                            $columna_m = $hojaActual->getCellByColumnAndRow(13, $indicefila)->getValue();

                            $array_data_base[$control_item]['A']=validar_input($columna_a);

                            // echo "Registro: ".$columna_a;
                            // echo "|";
                            // echo date('Y-m-d', strtotime($columna_b));
                            // echo "|";
                            // echo var_dump($columna_b);
                            // echo "|";


                            if ($columna_b!='' AND (is_float($columna_b) OR is_int($columna_b))) {
                                // echo "ingreso filtro 1: ";

                                $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($columna_b);
                                $timestamp += 86400; // Añadir un día en segundos (24 horas * 60 minutos * 60 segundos)
                                // echo "|";
                                $fecha_formateada = date("Y-m-d", $timestamp);
                                $array_data_base[$control_item]['B']=$fecha_formateada;
                            } else {
                                // echo "ingreso filtro 2: ";
                                $array_data_base[$control_item]['B']=validar_input($columna_b);
                            }
                            // echo "<br>";
                            
                            $array_data_base[$control_item]['C']=validar_input($columna_c);
                            $array_data_base[$control_item]['D']=validar_input($columna_d);
                            $array_data_base[$control_item]['E']=validar_input($columna_e);
                            $array_data_base[$control_item]['F']=validar_input($columna_f);
                            $array_data_base[$control_item]['G']=validar_input($columna_g);
                            $array_data_base[$control_item]['H']=validar_input($columna_h);
                            $array_data_base[$control_item]['I']=validar_input($columna_i);
                            $array_data_base[$control_item]['J']=validar_input($columna_j);
                            $array_data_base[$control_item]['K']=validar_input($columna_k);
                            $array_data_base[$control_item]['L']=validar_input($columna_l);
                            $array_data_base[$control_item]['M']=validar_input($columna_m);

                            $id_usuario_asignado=array_rand($usuario_pendiente_asignacion);

                            $array_data_base[$control_item]['usuario_asignado']=$id_usuario_asignado;

                            $array_usuario_control[$id_usuario_asignado]+=1;

                            if ($array_usuario_control[$id_usuario_asignado]==$array_usuario_cantidad[$id_usuario_asignado]) {
                                unset($usuario_pendiente_asignacion[$id_usuario_asignado]);
                            }

                            $control_item++;
                        }

                        // Prepara la sentencia
                        $sentencia_insert_data = $enlace_db->prepare("INSERT INTO `tb_gestion_encuesta_radicado`(`gera_radicado`, `gera_matriz_id`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_genero`, `gera_edad`, `gera_motivo`, `gera_regional`, `gera_centro_zonal`, `gera_auxiliar1`, `gera_auxiliar2`, `gera_auxiliar3`, `gera_estado_gestion`, `gera_intentos`, `gera_contactado`, `gera_efectivo`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, `gera_usuario_gestion`) VALUES (?,?,?,?,?,?,?,?,'',?,?,?,?,?,?,'Pendiente','0','','','','',?);");
                        
                        // Agrega variables a sentencia preparada
                        $sentencia_insert_data->bind_param('sssssssssssssss', $columna_a, $tipo_encuesta, $columna_b, $columna_c, $columna_d, $columna_e, $columna_f, $columna_g, $columna_h, $columna_j, $columna_i, $columna_k, $columna_l, $columna_m, $id_usuario_registro);
                        
                        $control_insert=0;
                        $control_fail=0;
                        $string_fail="";

                        for ($i=0; $i < count($array_data_base); $i++) { 
                            $columna_a=$array_data_base[$i]['A'];

                            // if ($array_data_base[$i]['B']!='' AND (is_float($array_data_base[$i]['B']) OR is_int($array_data_base[$i]['B']))) {
                            //     $columna_b=date('Y-m-d', $array_data_base[$i]['B']);
                            // } else {
                            // }
                            $columna_b=$array_data_base[$i]['B'];

                            // $columna_b=date('Y-m-d', $array_data_base[$i]['B']);
                            $columna_c=$array_data_base[$i]['C'];
                            $columna_d=$array_data_base[$i]['D'];
                            $columna_e=$array_data_base[$i]['E'];
                            $columna_f=$array_data_base[$i]['F'];
                            $columna_g=$array_data_base[$i]['G'];
                            $columna_h=$array_data_base[$i]['H'];
                            $columna_i=$array_czonal[$array_regional[$array_data_base[$i]['J']]][$array_data_base[$i]['I']];
                            $columna_j=$array_regional[$array_data_base[$i]['J']];
                            $columna_k=$array_data_base[$i]['K'];
                            $columna_l=$array_data_base[$i]['L'];
                            $columna_m=$array_data_base[$i]['M'];

                            $id_usuario_registro=$array_data_base[$i]['usuario_asignado'];

                            try {
                                if ($sentencia_insert_data->execute()) {
                                    $control_insert++;
                                } else {
                                    $control_fail++;
                                    $string_fail.=$columna_a."\r\n";
                                }
                            } catch (Exception $e) {
                                $control_fail++;
                                $string_fail.=$columna_a."\r\n";
                            }
                        }

                        if (($control_insert+$control_fail)==count($array_data_base)) {
                            $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>Base cargada exitosamente | Cargado: ".$control_insert." | Error: ".$control_fail."</p>";
                            $_SESSION['registro_cargue_base']=1;
                            $nombre_temporal_control="storage_temporal/CARGAR_FAIL".date('YmdHis').".txt";
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

    $consulta_string_matriz="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha` FROM `tb_gestion_encuestas_matriz` WHERE `gem_estado`='Activo'";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

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
                    <?php for ($i=0; $i < count($_SESSION['usuarios_asignacion']); $i++) { 
                        echo "<span class='fas fa-user'></span> ".$array_nombre_usuario[$_SESSION['usuarios_asignacion'][$i]]."<br>"; 
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
                                <label for="tipo_encuesta">Tipo encuesta</label>
                                <select class="form-control form-control-sm" name="tipo_encuesta" id="tipo_encuesta" <?php if($_SESSION['registro_cargue_base']==1) { echo 'disabled'; } ?> required>
                                  <option value="">Seleccione</option>
                                  <?php for ($i=0; $i < count($resultado_registros_matriz); $i++): ?>
                                    <option value="<?php echo $resultado_registros_matriz[$i][0]; ?>" <?php if(isset($_POST["cargar_base"]) AND $tipo_encuesta==$resultado_registros_matriz[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_matriz[$i][1]; ?></option>
                                  <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="inputGroupFile01">Documento base</label>
                                <div class="custom-file">
                                    <input type="file" name="documento" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01" <?php if($_SESSION['registro_cargue_base']==1) { echo 'disabled'; } ?> accept=".xlsx, .XLSX" required>
                                    <label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01label">Seleccione un archivo</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php if($_SESSION['registro_cargue_base']==1): ?>
                                    <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                                <?php else: ?>
                                    <button class="btn btn-success float-right ml-1" type="submit" name="cargar_base">Cargar base</button>
                                    <a href="gestion_encuestas_base_cargar_usuarios.php" class="btn btn-warning float-right ml-1">Regresar</a>
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