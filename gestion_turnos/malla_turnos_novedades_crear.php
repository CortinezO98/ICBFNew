<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos-Malla";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    $titulo_header = "Gestión Turnos | Novedades - Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $FechaInicio = base64_decode($_GET['fechainicio']);
    $filtro_operacion = base64_decode($_GET["operacion"]);

    $data_consulta=array();

    if(isset($_POST["guardar_registro"])){
        $novedad=validar_input($_POST['novedad']);
        $usuario=validar_input($_POST['usuario']);
        $fecha_inicio=validar_input($_POST['fecha_inicio']);
        $fecha_fin=validar_input($_POST['fecha_fin']);
        $observaciones=validar_input($_POST['observaciones']);
        $usuario_registro=$_SESSION["usu_id"];

        //AGREGAR FECHAS A ARRAY
        $dias_novedad = array();
        $fecha_control=$fecha_inicio;
        array_push($dias_novedad, $fecha_control);
        while ($fecha_fin>$fecha_control) {
            $fecha_control = date("Y-m-d", strtotime("+ 1 day", strtotime($fecha_control)));
            array_push($dias_novedad, $fecha_control);
        }

        if($_SESSION['mturno_novedad_crear']!=1){
            $consulta_string_bloqueo="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha` FROM `tb_control_turno_malla` WHERE `cotm_usuario`=? AND `cotm_inicio`>=? AND `cotm_fin`<=? AND `cotm_tipo`<>'turno' AND `cotm_tipo`<>'DESC' ORDER BY `cotm_usuario`, `cotm_inicio`";
            $consulta_registros_bloqueo = $enlace_db->prepare($consulta_string_bloqueo);
            $consulta_registros_bloqueo->bind_param("sss", $usuario, $fecha_inicio, $fecha_fin);
            $consulta_registros_bloqueo->execute();
            $resultado_registros_bloqueo = $consulta_registros_bloqueo->get_result()->fetch_all(MYSQLI_NUM);

            if (count($resultado_registros_bloqueo)==0) {
                $documento_cantidad=0;
                $documento_registrados=0;
                foreach($_FILES["documento_soporte"]['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES["documento_soporte"]["name"][$key]!="") {
                        $documento_cantidad++;
                        $archivo_extension = strtolower(pathinfo($_FILES['documento_soporte']['name'][$key], PATHINFO_EXTENSION));
                        
                        $nombre_soporte_1="novedad-".$novedad.'-'.$usuario.'-'.date('YmdHis').'-'.$_FILES['documento_soporte']['name'][$key];
                        $ruta_final="storage/".$nombre_soporte_1;
                        if ($_FILES['documento_soporte']["error"][$key] > 0) {
                            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                            $control_documento_2=0;
                        } else {
                          /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                            if (move_uploaded_file($_FILES['documento_soporte']['tmp_name'][$key], $ruta_final)) {
                                $control_documento_2=1;
                                $documento_registrados++;
                            } else {
                                $control_documento_2=0;
                            }
                        }
                    } else {
                        $control_documento_2=1;
                    }
                }

                if ($documento_cantidad==$documento_registrados) {
                    // Prepara la sentencia
                    $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_control_turno_malla_novedades`(`ctm_usuario`, `ctmn_tipo`, `ctmn_fecha_inicio`, `ctmn_fecha_fin`, `ctmn_observaciones`, `ctmn_sop_nombre`, `ctmn_sop_ruta`, `ctmn_sop_extension`, `ctmn_registro_usuario`) VALUES (?,?,?,?,?,?,?,?,?)");

                    // Agrega variables a sentencia preparada
                    $sentencia_insert->bind_param('sssssssss', $usuario, $novedad, $fecha_inicio, $fecha_fin, $observaciones, $nombre_soporte_1, $ruta_final, $archivo_extension, $usuario_registro);
                    
                    if ($sentencia_insert->execute()) {
                        // Prepara la sentencia
                        $sentencia_insert_data = $enlace_db->prepare("INSERT INTO `tb_control_turno_malla`(`cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_estado`) VALUES (?,?,?,?,?,'','Diurno','','','Novedad') ON DUPLICATE KEY UPDATE `cotm_tipo`=?, `cotm_inicio`=?, `cotm_fin`=?");

                        // Agrega variables a sentencia preparada
                        $sentencia_insert_data->bind_param('ssssssss', $id_turno_registro, $id_usuario, $tipo_turno, $turno_inicio, $turno_fin, $tipo_turno, $turno_inicio, $turno_fin);
                        $control_insert=0;
                        $control_fail=0;
                        $string_fail='';
                        for ($i=0; $i < count($dias_novedad); $i++) {
                            $id_turno_registro=$usuario.$dias_novedad[$i];
                            $id_usuario=$usuario;
                            $tipo_turno=$novedad;
                            $turno_inicio=$dias_novedad[$i].' 08:00';
                            $turno_fin=$dias_novedad[$i].' 17:00';
                            if ($sentencia_insert_data->execute()) {
                                $control_insert++;
                            } else {
                                $control_fail++;
                                $string_fail.=$columna_a."\r\n";
                            }
                        }

                        if ($control_insert==count($dias_novedad)) {
                            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                            $_SESSION['mturno_novedad_crear']=1;
                        } else {
                            unlink($ruta_final);
                            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                        }
                    } else {
                        unlink($ruta_final);
                        $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                    }
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, se encontraron turnos bloqueados para el rango de fechas seleccionado!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE (`usu_cargo_rol` LIKE '%Agente%' OR `usu_cargo_rol` LIKE '%Supervisor%') ".$filtro_perfil." ORDER BY `usu_nombres_apellidos`";

    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    if (count($data_consulta)>0) {
        // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
        $consulta_registros_analistas->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="malla_turnos_novedades.php?pagina=".$pagina."&id=".$filtro_permanente."&fechainicio=".base64_encode($FechaInicio)."&operacion=".base64_encode($filtro_operacion);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
    <link rel="stylesheet" href="../js/bootstrap-select/dist/css/bootstrap-select.min.css">
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
                        <div class="form-group">
                            <label for="novedad" class="m-0">Novedad</label>
                            <select class="form-control form-control-sm" name="novedad" id="novedad" onchange="consultar_turnos();" <?php if($_SESSION['mturno_novedad_crear']==1) { echo 'disabled'; } ?> required>
                              <option value="">Seleccione</option>
                              <option value="INCA" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="INCA"){ echo "selected"; } ?>>Incapacidad</option>
                              <option value="LICL" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="LICL"){ echo "selected"; } ?>>Licencia por luto</option>
                              <option value="CALD" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="CALD"){ echo "selected"; } ?>>Calamidad domestica</option>
                              <option value="LICM" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="LICM"){ echo "selected"; } ?>>Licencia de maternidad</option>
                              <option value="AUSI" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="AUSI"){ echo "selected"; } ?>>Ausencia injustificada</option>
                              <option value="LLET" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="LLET"){ echo "selected"; } ?>>Llegada tarde</option>
                              <option value="PERM" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="PERM"){ echo "selected"; } ?>>Permiso (médico, personal, entre otros)</option>
                              <option value="VACA" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="VACA"){ echo "selected"; } ?>>Vacaciones</option>
                              <option value="RETI" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="RETI"){ echo "selected"; } ?>>Retiro</option>
                              <option value="SUSP" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="SUSP"){ echo "selected"; } ?>>Suspensión</option>
                              <option value="CUMP" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="CUMP"){ echo "selected"; } ?>>Cumpleaños</option>
                              <option value="BENE" <?php if(isset($_POST["guardar_registro"]) AND $novedad=="BENE"){ echo "selected"; } ?>>Beneficio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="usuario" class="m-0">Usuario</label>
                            <select class="selectpicker form-control form-control-sm font-size-11 padding-2" data-live-search="true" name="usuario" id="usuario" onchange="consultar_turnos();" <?php if($_SESSION['mturno_novedad_crear']==1) { echo 'disabled'; } ?> required>
                              <option value="" class="font-size-11">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_analistas); $i++): ?> 
                                <option value="<?php echo $resultado_registros_analistas[$i][0]; ?>" class="font-size-11" data-tokens="<?php echo $resultado_registros_analistas[$i][0].' '.$resultado_registros_analistas[$i][1]; ?>" <?php if(isset($_POST["guardar_registro"]) AND $usuario==$resultado_registros_analistas[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_analistas[$i][1]; ?></option>
                              <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="fecha_inicio" class="m-0">Fecha inicio</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="fecha_inicio" value="<?php if(isset($_POST["guardar_registro"])){ echo $fecha_inicio; } ?>" <?php if($_SESSION['mturno_novedad_crear']==1) { echo 'readonly'; } ?> onchange="consultar_turnos();" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="fecha_fin" class="m-0">Fecha fin</label>
                          <input type="date" class="form-control form-control-sm" name="fecha_fin" id="fecha_fin" value="<?php if(isset($_POST["guardar_registro"])){ echo $fecha_fin; } ?>" <?php if($_SESSION['mturno_novedad_crear']==1) { echo 'readonly'; } ?> onchange="consultar_turnos();" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="inputGroupFile01_label" class="m-0">Soporte novedad <a href="#" onclick="reset_adjunto('inputGroupFile01');" class="color-rojo"><span class="fas fa-trash-alt"></span></a></label>
                            <div class="custom-file">
                                <input type="file" name="documento_soporte[]" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon02" <?php if($_SESSION['mturno_novedad_crear']==1) { echo 'disabled'; } ?>>
                                <label class="custom-file-label" for="inputGroupFile01" id="inputGroupFile01label">Seleccione un archivo</label>
                                <p class="alert alert-warning p-1 font-size-11">*Recuerde que es obligatorio el cargue del soporte de la novedad a registrar</p>
                                <div class="col-md-12 mb-1 p-0 mt-0 font-size-11" id="soportes_seleccionados_inputGroupFile01"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="observaciones" class="m-0">Observaciones</label>
                          <textarea class="form-control form-control-sm" name="observaciones" id="observaciones" maxlength="200" <?php if($_SESSION['mturno_novedad_crear']==1) { echo 'readonly'; } ?>><?php if(isset($_POST["guardar_registro"])){ echo $observaciones; } ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-12" id="coincidencias"></div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['mturno_novedad_crear']==1): ?>
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
    <script src="../js/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script type="text/javascript">
        function consultar_turnos(){
            $.ajax({
                success: function(){
                    $("#coincidencias").load("malla_turnos_novedades_coincidencias_turnos.php?usuario="+$("#usuario").val()+"&fecha_inicio="+$("#fecha_inicio").val()+"&fecha_fin="+$("#fecha_fin").val());
                }
            });
        }
        $("#inputGroupFile01").change(function(){
            var soportes='<b>Documentos seleccionados:</b><br>';
            for (var i = 0; i < document.getElementById("inputGroupFile01").files.length; i++) {
                soportes += '<p class="alert alert-success px-1 py-0 mb-0">'+document.getElementById("inputGroupFile01").files[i].name+'</p>';
            }

            var valor_opcion = document.getElementById("inputGroupFile01").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
                document.getElementById('soportes_seleccionados_inputGroupFile01').innerHTML=soportes;
            } else {
                document.getElementById('soportes_seleccionados_inputGroupFile01').innerHTML="";
            }
        });

        function reset_adjunto(id_file) {
            document.getElementById(id_file).value = "";
            document.getElementById(id_file+'label').innerHTML='Seleccione un archivo';
            document.getElementById('soportes_seleccionados_'+id_file).innerHTML="";
            $('#'+id_file+'label').removeClass("color-verde");
        }
    </script>
</body>
</html>