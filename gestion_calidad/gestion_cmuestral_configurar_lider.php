<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("../config/validar_festivos.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\IOFactory;
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Configuración - Líder Calidad";
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_segmento=validar_input(base64_decode($_GET['seg']));
    $fecha_calculadora=validar_input($_GET['date']);
    
    // Inicializa variable tipo array
    $data_consulta_usuarios=array();

    $control_insert=0;
    $control_fail=0;
    $string_fail="";
    $control_duplicado=0;
    $string_duplicado="";

    $consulta_string_fechas="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, TS.`cms_nombre_segmento`, `cmm_semana_inicio`, `cmm_semana_fin` FROM `tb_gestion_calidad_cmuestral_mensual` LEFT JOIN `tb_gestion_calidad_cmuestral_segmento` AS TS ON `tb_gestion_calidad_cmuestral_mensual`.`cmm_segmento`=TS.`cms_id` WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros_fechas = $enlace_db->prepare($consulta_string_fechas);
    $consulta_registros_fechas->bind_param("sss", $id_registro, $fecha_calculadora, $id_segmento);
    $consulta_registros_fechas->execute();
    $resultado_registros_fechas = $consulta_registros_fechas->get_result()->fetch_all(MYSQLI_NUM);

    if(isset($_POST["guardar_registro"])){
        $documento_identidad_pre=$_POST['documento_identidad'];
        $valore = chop($documento_identidad_pre);
        $valores = nl2br($valore);
        $documento_identidad = explode("<br />", $valores);

        $filtro_usuarios="";
        for ($i=0; $i < count($documento_identidad); $i++) {
            $filtro_usuarios.="TU.`usu_id`=? OR ";
            $filtro_usuarios_turno.="`cotm_usuario`=? OR ";
            array_push($data_consulta_usuarios, trim($documento_identidad[$i]));//Se agrega llave por ser variable evaluada en un like
        }

        $filtro_usuarios=" AND (".substr($filtro_usuarios, 0, -4).")";
        $filtro_usuarios_turno=" AND (".substr($filtro_usuarios_turno, 0, -4).")";

        $consulta_string_usuarios="SELECT TU.`usu_id`, TU.`usu_nombres_apellidos`, TU.`usu_correo_corporativo`, TU.`usu_campania`, TU.`usu_cargo_rol`, TU.`usu_ciudad`, TU.`usu_estado`, TU.`usu_supervisor`, TU.`usu_lider_calidad`, TU.`usu_piloto`, TC.`ac_nombre_campania`, TS.`usu_nombres_apellidos`, TU.`usu_fecha_incorporacion` FROM `tb_administrador_usuario` AS TU LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` LEFT JOIN `tb_administrador_usuario` AS TS ON TU.`usu_supervisor`=TS.`usu_id` WHERE TU.`usu_estado`='Activo' ".$filtro_usuarios." ORDER BY TU.`usu_nombres_apellidos` ASC";
        $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
        $consulta_registros_usuarios->bind_param(str_repeat("s", count($data_consulta_usuarios)), ...$data_consulta_usuarios);
        $consulta_registros_usuarios->execute();
        $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_usuarios); $i++) { 
            $id_usuarios[]=$resultado_registros_usuarios[$i][0];
            $usuarios_detalle[$resultado_registros_usuarios[$i][0]]['segmento']=$resultado_registros_usuarios[$i][3];
            $usuarios_detalle[$resultado_registros_usuarios[$i][0]]['supervisor']=$resultado_registros_usuarios[$i][7];
        }

        //CONSULTA TURNO PROGRAMADO
        $consulta_inicio_turnos_programados_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, `cotm_estado` FROM `tb_control_turno_malla` WHERE `cotm_inicio`>='".$resultado_registros_fechas[0][15]."' AND `cotm_inicio`<='".$resultado_registros_fechas[0][16]." 23:59:59' ".$filtro_usuarios_turno." ORDER BY `cotm_usuario`, `cotm_inicio`";
        $consulta_inicio_turnos_programados = $enlace_db->prepare($consulta_inicio_turnos_programados_string);
        $consulta_inicio_turnos_programados->bind_param(str_repeat("s", count($data_consulta_usuarios)), ...$data_consulta_usuarios);
        $consulta_inicio_turnos_programados->execute();
        $resultado_inicio_turnos_programados = $consulta_inicio_turnos_programados->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_inicio_turnos_programados); $i++) { 
            $fecha_turno=date('Y-m-d', strtotime($resultado_inicio_turnos_programados[$i][3]));
            $hora_inicio=date('H:i', strtotime($resultado_inicio_turnos_programados[$i][3]));
            $hora_fin=date('H:i', strtotime($resultado_inicio_turnos_programados[$i][4]));
            if ($resultado_inicio_turnos_programados[$i][2]=="turno") {
                $array_turno_programado[$resultado_inicio_turnos_programados[$i][1]]['turno'][date('w', strtotime($fecha_turno))]=$hora_inicio.'-'.$hora_fin;
            } else {
                $array_turno_programado[$resultado_inicio_turnos_programados[$i][1]]['turno'][date('w', strtotime($fecha_turno))]=$resultado_inicio_turnos_programados[$i][2];
            }
        }

        // Prepara la sentencia
        $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_malla_lider`(`ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_registro_usuario`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

        // Agrega variables a sentencia preparada
        $sentencia_insert->bind_param('ssssssssssss', $id_registro_turno, $fecha_calculadora_registro, $id_segmento, $registro_usuario, $registro_turno_lunes, $registro_turno_martes, $registro_turno_miercoles, $registro_turno_jueves, $registro_turno_viernes, $registro_turno_sabado, $registro_turno_domingo, $ccmm_registro_usuario);
        $control_insert=0;
        $control_registros=0;

        for ($i=0; $i < count($documento_identidad); $i++) { 
            $registro_usuario=trim($documento_identidad[$i]);
            $id_registro_turno=$registro_usuario.'-'.$fecha_calculadora.'-'.$id_segmento;
            $fecha_calculadora_registro=$fecha_calculadora;
            $registro_turno_lunes=$array_turno_programado[$registro_usuario]['turno'][1];
            $registro_turno_martes=$array_turno_programado[$registro_usuario]['turno'][2];
            $registro_turno_miercoles=$array_turno_programado[$registro_usuario]['turno'][3];
            $registro_turno_jueves=$array_turno_programado[$registro_usuario]['turno'][4];
            $registro_turno_viernes=$array_turno_programado[$registro_usuario]['turno'][5];
            $registro_turno_sabado=$array_turno_programado[$registro_usuario]['turno'][6];
            $registro_turno_domingo=$array_turno_programado[$registro_usuario]['turno'][0];
            $ccmm_registro_usuario=$_SESSION["usu_id"];

            if ($sentencia_insert->execute()) {
                $control_insert++;
                $array_actualizado[]=$registro_usuario;
            } else {
                $array_actualizado_error[]=$registro_usuario;
                // echo "INSERT INTO `tb_gestion_calidad_cmuestral_malla`(`ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_martes`, `ccmm_monitoreo_miercoles`, `ccmm_monitoreo_jueves`, `ccmm_monitoreo_viernes`, `ccmm_monitoreo_sabado`, `ccmm_monitoreo_domingo`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_monitor`, `ccmm_registro_usuario`) VALUES ('".$id_registro_turno."','".$fecha_calculadora_registro."','".$id_segmento."','".$registro_usuario."','".$registro_usuario_segmento."','".$registro_coordinador."','".$registro_turno_lunes."','".$registro_turno_martes."','".$registro_turno_miercoles."','".$registro_turno_jueves."','".$registro_turno_viernes."','".$registro_turno_sabado."','".$registro_turno_domingo."','".$registro_turno_mlunes."','".$registro_turno_mmartes."','".$registro_turno_mmiercoles."','".$registro_turno_mjueves."','".$registro_turno_mviernes."','".$registro_turno_msabado."','".$registro_turno_mdomingo."','".$ccmm_actualiza_usuario."','".$ccmm_actualiza_fecha."','".$registro_turno_monitor."','".$ccmm_registro_usuario."') ON DUPLICATE KEY UPDATE `ccmm_actualiza_usuario`='".$ccmm_actualiza_usuario."', `ccmm_actualiza_fecha`='".$ccmm_actualiza_fecha."'<br>";
            }
            $control_registros++;
        }

        if ($control_insert==$control_registros) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registros creados exitosamente!', 0);</script>";
            $_SESSION['registro_cargue_lider']=1;
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear los registros, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $ruta_cancelar_finalizar="gestion_cmuestral_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro)."&date=".$fecha_calculadora;
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
                    <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                    <?php echo "<pre>";
                    // print_r($array_turno_programado);
                    echo "</pre>"; ?>
                    <?php if($control_fail>0): ?>
                        <p class="alert alert-danger p-1">Descargar log de radicados con error: <a href="gestion_cmuestral_configurar_malla_descargar_error.php?ruta=<?php echo $nombre_temporal_control; ?>" target="_blank"><span class="fas fa-download"></span> Descargar</a></p>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                              <label for="semana" class="m-0">Semana</label>
                              <input type="text" class="form-control form-control-sm" name="semana" id="semana" value="<?php echo $fecha_calculadora; ?>" readonly required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                              <label for="segmento" class="m-0">Segmento</label>
                              <input type="text" class="form-control form-control-sm" name="segmento" id="segmento" value="<?php echo $resultado_registros_fechas[0][14]; ?>" readonly required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                              <label for="documento_identidad">Documento identidad</label>
                              <textarea class="form-control form-control-sm" name="documento_identidad" id="documento_identidad" <?php if($_SESSION['registro_cargue_lider']==1) { echo 'disabled'; } ?> required rows="10"><?php if(isset($_POST["guardar_registro"])){ echo $documento_identidad_pre; } ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php if($_SESSION['registro_cargue_lider']==1): ?>
                                    <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                                <?php else: ?>
                                    <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Asociar Usuarios</button>
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