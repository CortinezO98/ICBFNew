<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("../config/validar_festivos.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\IOFactory;
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Turnos | Malla de Turnos - Cargar Malla";
    $actualizar_duplicados = base64_decode($_GET['actdup']);

    $array_columna[]="B";
    $array_columna[]="C";
    $array_columna[]="D";
    $array_columna[]="E";
    $array_columna[]="F";
    $array_columna[]="G";
    $array_columna[]="H";

    $control_insert=0;
    $control_fail=0;
    $string_fail="";
    $control_duplicado=0;
    $string_duplicado="";

    if(isset($_POST["cargar_base"])){
        $extension = pathinfo( $_FILES['documento']['name'], PATHINFO_EXTENSION);
        $semana=validar_input($_POST['semana']);
        
        //AGREGAR NÚMERO AL DÍA, SEGÚN SEMANA SELECCIONADA
        $dias_semana = array();
        for ($i=0; $i < 7; $i++) { 
            array_push($dias_semana, date("Y-m-d", strtotime("first day", strtotime($semana . $i))));
        }

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

                            if ($columna_a!="") {
                                $array_data_base[$control_item]['A']=validar_input($columna_a);
                                $array_data_base[$control_item]['B']=validar_input($columna_b);
                                $array_data_base[$control_item]['C']=validar_input($columna_c);
                                $array_data_base[$control_item]['D']=validar_input($columna_d);
                                $array_data_base[$control_item]['E']=validar_input($columna_e);
                                $array_data_base[$control_item]['F']=validar_input($columna_f);
                                $array_data_base[$control_item]['G']=validar_input($columna_g);
                                $array_data_base[$control_item]['H']=validar_input($columna_h);
                                $array_data_base[$control_item]['I']=validar_input($columna_i);
                                $control_item++;
                            }
                        }

                        // Prepara la sentencia
                        $sentencia_insert_data = $enlace_db->prepare("INSERT INTO `tb_control_turno_malla`(`cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_estado`) VALUES (?,?,?,?,?,?,?,'','','')");
                        // Agrega variables a sentencia preparada
                        $sentencia_insert_data->bind_param('sssssss', $id_turno_validar, $id_usuario, $tipo_turno, $turno_inicio, $turno_fin, $turno_duracion, $tipo_jornada);

                        //Consulta turnos duplicados
                        $consulta_string_duplicado="SELECT `cotm_id`, `cotm_estado` FROM `tb_control_turno_malla` WHERE `cotm_id`=?";
                        $consulta_registros_duplicado = $enlace_db->prepare($consulta_string_duplicado);
                        $consulta_registros_duplicado->bind_param("s", $id_turno_validar);
                        
                        for ($i=0; $i < count($array_data_base); $i++) { 
                            $id_usuario=trim($array_data_base[$i]['A']);
                            if ($id_usuario!="") {
                                for ($j=0; $j < count($dias_semana); $j++) {
                                    $dia_semana=$dias_semana[$j];
                                    $id_turno_validar=$id_usuario.$dia_semana;
                                    $consulta_registros_duplicado->execute();
                                    $resultado_registros_duplicado = $consulta_registros_duplicado->get_result()->fetch_all(MYSQLI_NUM);

                                    if (count($resultado_registros_duplicado)>0 AND ($resultado_registros_duplicado[0][1]=='' OR $resultado_registros_duplicado[0][1]==NULL)) {
                                        $control_duplicado++;
                                        $string_duplicado.=$id_usuario."\r\n";
                                    }
                                }
                            }
                        }

                        if ($control_duplicado>0) {
                            $_SESSION['malla_temporal']=$array_data_base;
                        } else {
                            for ($i=0; $i < count($array_data_base); $i++) { 
                                $id_usuario=trim($array_data_base[$i]['A']);
                                
                                for ($j=0; $j < count($array_columna); $j++) {
                                    $dia_semana=$dias_semana[$j];
                                    $id_turno_validar=$id_usuario.$dia_semana;
                                    $id_columna=$array_columna[$j];
                                    $valor_celda=trim($array_data_base[$i][$id_columna]);
                                    
                                    if ($valor_celda=="") {
                                        $tipo_turno='na';
                                        $tipo_jornada='na';
                                        $turno_duracion='';
                                    } elseif (isset($array_convenciones[$valor_celda])) {
                                        $tipo_turno=$valor_celda;
                                        $tipo_jornada='Diurno';
                                        $turno_inicio=$dia_semana.' 08:00';
                                        $turno_fin=$dia_semana.' 17:00';
                                        $turno_duracion=conversorSegundosHoras_sn(dateDiff($turno_inicio, $turno_fin));
                                    } else {
                                        $tipo_turno='turno';
                                        $tipo_jornada='Diurno';
                                        $horario_explode=explode('-', $array_data_base[$i][$array_columna[$j]]);
                                        $turno_inicio=$dia_semana.' '.trim($horario_explode[0]);
                                        $turno_fin=$dia_semana.' '.trim($horario_explode[1]);
                                        if ($turno_inicio>$turno_fin) {
                                            $turno_fin=date("Y-m-d H:i", strtotime("+ 1 day", strtotime($turno_fin)));
                                            $tipo_jornada='Nocturno';
                                        }
                                        $turno_duracion=conversorSegundosHoras_sn(dateDiff($turno_inicio, $turno_fin));
                                    }

                                    if ($sentencia_insert_data->execute()) {
                                        $control_insert++;
                                    } else {
                                        $control_fail++;
                                        // echo "INSERT INTO `tb_control_turno_malla`(`cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_estado`) VALUES ('".$id_turno_validar."','".$id_usuario."','".$tipo_turno."','".$turno_inicio."','".$turno_fin."','".$turno_duracion."','".$tipo_jornada."','','','');";
                                        $string_fail.="Id turno: ".$id_turno_validar." | Usuario: ".$id_usuario." | Tipo: ".$tipo_turno." | Fecha: ".$turno_inicio." A ".$turno_fin."\r\n";
                                    }
                                }
                            }
                        }

                        if (($control_insert+$control_fail)==(count($array_data_base)*7) AND $control_duplicado==0) {
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

    // echo "<pre>";
    // print_r($_SESSION['malla_temporal']);
    // echo "</pre>";

    if ($actualizar_duplicados=='actualizar' AND count($_SESSION['malla_temporal'])>0) {
        $semana=validar_input($_GET['semana']);

        //AGREGAR NÚMERO AL DÍA, SEGÚN SEMANA SELECCIONADA
        $dias_semana = array();
        for ($i=0; $i < 7; $i++) { 
            array_push($dias_semana, date("Y-m-d", strtotime("first day", strtotime($semana . $i))));
        }
        if ($_SESSION['registro_cargue_base']!=1) {
            // Prepara la sentencia
            $sentencia_insert_data = $enlace_db->prepare("INSERT INTO `tb_control_turno_malla`(`cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_estado`) VALUES (?,?,?,?,?,?,?,'','','') ON DUPLICATE KEY UPDATE `cotm_tipo`=?, `cotm_inicio`=?, `cotm_fin`=?, `cotm_duracion`=?, `cotm_jornada`=?");
            // Agrega variables a sentencia preparada
            $sentencia_insert_data->bind_param('ssssssssssss', $id_turno_validar, $id_usuario, $tipo_turno, $turno_inicio, $turno_fin, $turno_duracion, $tipo_jornada, $tipo_turno, $turno_inicio, $turno_fin, $turno_duracion, $tipo_jornada);

            for ($i=0; $i < count($_SESSION['malla_temporal']); $i++) { 
                $id_usuario=trim($_SESSION['malla_temporal'][$i]['A']);
                
                for ($j=0; $j < count($array_columna); $j++) {
                    $dia_semana=$dias_semana[$j];
                    $id_turno_validar=$id_usuario.$dia_semana;
                    $id_columna=$array_columna[$j];
                    $valor_celda=trim($_SESSION['malla_temporal'][$i][$id_columna]);
                    
                    if ($valor_celda=="") {
                        $tipo_turno='na';
                        $tipo_jornada='';
                        $turno_duracion='';
                    } elseif (isset($array_convenciones[$valor_celda])) {
                        $tipo_turno=$valor_celda;
                        $turno_inicio=$dia_semana.' 08:00';
                        $turno_fin=$dia_semana.' 17:00';
                        $tipo_jornada='Diurno';
                        $turno_duracion=conversorSegundosHoras_sn(dateDiff($turno_inicio, $turno_fin));
                    } else {
                        $tipo_turno='turno';
                        $tipo_jornada='Diurno';
                        $horario_explode=explode('-', $_SESSION['malla_temporal'][$i][$array_columna[$j]]);
                        $turno_inicio=$dia_semana.' '.trim($horario_explode[0]);
                        $turno_fin=$dia_semana.' '.trim($horario_explode[1]);
                        if ($turno_inicio>$turno_fin) {
                            $turno_fin=date("Y-m-d H:i", strtotime("+ 1 day", strtotime($turno_fin)));
                            $tipo_jornada='Nocturno';
                        }
                        $turno_duracion=conversorSegundosHoras_sn(dateDiff($turno_inicio, $turno_fin));
                    }

                    if ($sentencia_insert_data->execute()) {
                        $control_insert++;
                    } else {
                        $control_fail++;
                        $string_fail.=$columna_a."\r\n";
                    }
                }
            }

            if (($control_insert+$control_fail)==(count($_SESSION['malla_temporal'])*7) AND $control_duplicado==0) {
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
            $respuesta_accion = "<p class='alert alert-success p-1 mb-2'>¡La base ya ha sido cargada, haga clic en Finalizar para salir!</p>";
        }
    }

    $ruta_cancelar_finalizar="malla_turnos.php?fechainicio=".base64_encode(date('Y')."-W".date('W'))."&operacion=".base64_encode('Todas')."&id=null";
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
                <div class="col-md-6">
                    <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                    <?php if($control_duplicado>0): ?>
                        <p class="alert alert-danger p-1">Se encontraron  <?php echo $control_duplicado; ?> turnos programados que se reemplazarán. Por favor confirme si desea reemplazarlos <a href="malla_turnos_cargar.php?actdup=<?php echo base64_encode('actualizar'); ?>&semana=<?php echo $semana; ?>"><span class="fas fa-retweet"></span> Si, reemplazar todo</a></p>
                    <?php endif; ?>
                    <?php if($control_fail>0): ?>
                        <p class="alert alert-danger p-1">Descargar log de radicados con error: <a href="gestion_encuestas_base_descargar_error.php?ruta=<?php echo $nombre_temporal_control; ?>" target="_blank"><span class="fas fa-download"></span> Descargar</a></p>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                              <label for="semana" class="m-0">Semana</label>
                              <input type="week" class="form-control form-control-sm" name="semana" id="semana" value="<?php if(isset($_POST["cargar_base"]) OR isset($_GET['semana'])){ echo $semana; } ?>" <?php if($_SESSION['registro_cargue_base']==1) { echo 'disabled'; } ?> required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="inputGroupFile01">Formato malla</label>
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
                                    <button class="btn btn-success float-right ml-1" type="submit" name="cargar_base">Cargar malla</button>
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