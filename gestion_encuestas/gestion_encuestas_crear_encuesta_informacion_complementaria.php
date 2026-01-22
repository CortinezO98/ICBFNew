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

    if(isset($_POST["guardar_seccion"])){
        $fecha_peticion=validar_input($_POST['fecha_peticion']);
        $genero=validar_input($_POST['genero']);
        $edad_ciudadano=validar_input($_POST['edad_ciudadano']);
        $motivo_visita=validar_input($_POST['motivo_visita']);
        $regional=validar_input($_POST['regional']);
        $centro_zonal=validar_input($_POST['centro_zonal']);

        $consulta_string_revisar="SELECT `gera_radicado`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_genero`, `gera_motivo`, `gera_edad`, TR.`gere_regional`, TCZ.`gercz_centro_zonal` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_encuesta_radicado`.`gera_centro_zonal`=TCZ.`gercz_id` WHERE `gera_radicado`=?";

        $consulta_registros_revisar = $enlace_db->prepare($consulta_string_revisar);
        $consulta_registros_revisar->bind_param("s", $id_registro);
        $consulta_registros_revisar->execute();
        $resultado_registros_revisar = $consulta_registros_revisar->get_result()->fetch_all(MYSQLI_NUM);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuesta_radicado` SET `gera_fecha_peticion`=?, `gera_genero`=?, `gera_edad`=?, `gera_motivo`=?, `gera_regional`=?, `gera_centro_zonal`=? WHERE `gera_radicado`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sssssss', $fecha_peticion, $genero, $edad_ciudadano, $motivo_visita, $regional, $centro_zonal, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        // echo "UPDATE `tb_gestion_encuesta_radicado` SET `gera_fecha_peticion`='".$fecha_peticion."', `gera_genero`='".$genero."', `gera_edad`='".$edad_ciudadano."', `gera_motivo`='".$motivo_visita."', `gera_regional`='".$regional."', `gera_centro_zonal`='".$centro_zonal."' WHERE `gera_radicado`='".$id_registro."'";

        if (comprobarSentencia($enlace_db->info)) {
            // echo "ingreso actualiza edad: ".$edad_ciudadano;
            //Preparar consulta log insertar
            $consulta_string_log = "INSERT INTO `tb_gestion_encuesta_radicado_log`(`gerlog_radicado`, `gerlog_campo`, `gerlog_anterior`, `gerlog_nuevo`, `gerlog_registro_usuario`) VALUES (?,?,?,?,?)";
            $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
            $consulta_registros_log->bind_param("sssss", $id_registro, $log_campo, $log_anterior, $log_nuevo, $_SESSION["usu_id"]);

            //Registro log nombre campos
            $array_log_valor_campos[]="Fecha Petición";
            $array_log_valor_campos[]="Género";
            $array_log_valor_campos[]="Edad";
            $array_log_valor_campos[]="Motivo Visita";
            $array_log_valor_campos[]="Regional";
            $array_log_valor_campos[]="Centro Zonal";

            // Registro log valores anteriores
            $array_log_valor_old[]=$resultado_registros_revisar[0][1];
            $array_log_valor_old[]=$resultado_registros_revisar[0][4];
            $array_log_valor_old[]=$resultado_registros_revisar[0][6];
            $array_log_valor_old[]=$resultado_registros_revisar[0][5];
            $array_log_valor_old[]=$resultado_registros_revisar[0][7];
            $array_log_valor_old[]=$resultado_registros_revisar[0][8];
            
            // Registro log valores nuevos
            $array_log_valor_new[]=$fecha_peticion;
            $array_log_valor_new[]=$genero;
            $array_log_valor_new[]=$edad_ciudadano;
            $array_log_valor_new[]=$motivo_visita;
            $array_log_valor_new[]=nombre_regional($enlace_db, $regional);
            $array_log_valor_new[]=nombre_centro_zonal($enlace_db, $centro_zonal);
            
            for ($i=0; $i < count($array_log_valor_old); $i++) { 
                if ($array_log_valor_old[$i]!=$array_log_valor_new[$i]) {
                    $log_campo=$array_log_valor_campos[$i];
                    $log_anterior=$array_log_valor_old[$i];
                    $log_nuevo=$array_log_valor_new[$i];
                    $consulta_registros_log->execute();
                }
            }
            header('Location: gestion_encuestas_crear_encuesta_observaciones.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        } else {
            header('Location: gestion_encuestas_crear_encuesta_informacion_complementaria.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        }
    }

    $consulta_string="SELECT `gera_radicado`, `gera_matriz_id`, TE.`gem_nombre_encuesta`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_genero`, `gera_motivo`, `gera_regional`, `gera_centro_zonal`, `gera_estado_gestion`, `gera_intentos`, `gera_contactado`, `gera_efectivo`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, `gera_usuario_gestion`, TU.`usu_nombres_apellidos`, `gera_registro_fecha`, `gera_edad` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TE ON `tb_gestion_encuesta_radicado`.`gera_matriz_id`=TE.`gem_id` WHERE `gera_radicado`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_motivo="SELECT `gemo_id`, `gemo_motivo` FROM `tb_gestion_encuesta_motivo` ORDER BY `gemo_motivo` ASC";
    $consulta_registros_motivo = $enlace_db->prepare($consulta_string_motivo);
    $consulta_registros_motivo->execute();
    $resultado_registros_motivo = $consulta_registros_motivo->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_regional="SELECT `gere_id`, `gere_regional` FROM `tb_gestion_encuesta_regional` ORDER BY `gere_regional` ASC";
    $consulta_registros_regional = $enlace_db->prepare($consulta_string_regional);
    $consulta_registros_regional->execute();
    $resultado_registros_regional = $consulta_registros_regional->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_czonal="SELECT `gercz_id`, `gercz_regional`, `gercz_centro_zonal`, `gercz_registro_fecha` FROM `tb_gestion_encuesta_regional_czonal` WHERE `gercz_regional`='".$resultado_registros[0][10]."' ORDER BY `gercz_centro_zonal` ASC";
    $consulta_registros_czonal = $enlace_db->prepare($consulta_string_czonal);
    $consulta_registros_czonal->execute();
    $resultado_registros_czonal = $consulta_registros_czonal->get_result()->fetch_all(MYSQLI_NUM);

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
                        Sección <?php echo count($_SESSION['registro_configuracion_encuesta_secciones'])+2; ?> de <?php echo count($_SESSION['registro_configuracion_encuesta_secciones'])+3; ?>
                    </div>
                    <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                        <div class="row p-0">
                            <div class="col-md-12">
                                <div class="py-2 px-3">
                                    <b>Información Complementaria - Despedida</b>
                                    <div class="descripcion-seccion py-1 px-3">“Señor (a) xxxx, agradezco que haya atendido mi llamada, con esto damos por terminada las preguntas. Para finalizar, le recuerdo que, si requiere información sobre nuestra oferta institucional o presentar una sugerencia, queja o reclamo, lo podrá realizar las 24 horas del día a través de los diferentes canales de atención disponibles por parte del ICBF”.<hr class="py-0 my-1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-6">
                                <div class="pb-1">
                                    <?php echo "Fecha de atención al ciudadano (SIM)"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="date" class="form-control form-control-sm" name="fecha_peticion" value="<?php echo $resultado_registros[0][3]; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Género"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <select class="form-control form-control-sm" name="genero" id="genero" required>
                                        <option value="NI" <?php if($resultado_registros[0][8]=="NI") { echo "selected"; } ?>>No indica</option>
                                        <option value="F" <?php if($resultado_registros[0][8]=="F") { echo "selected"; } ?>>Femenino</option>
                                        <option value="M" <?php if($resultado_registros[0][8]=="M") { echo "selected"; } ?>>Másculino</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Edad"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="number" class="form-control form-control-sm" step="1" min="5" max="100" name="edad_ciudadano" value="<?php echo $resultado_registros[0][21]; ?>" required autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Motivo de visita (Registrado en SIM)"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <select class="form-control form-control-sm" name="motivo_visita" id="motivo_visita" required>
                                        <option value="">Seleccione</option>
                                        <?php for ($k=0; $k < count($resultado_registros_motivo); $k++): ?>
                                            <option value="<?php echo $resultado_registros_motivo[$k][1]; ?>" <?php echo ($resultado_registros_motivo[$k][1]==$resultado_registros[0][9]) ? 'selected' : ''; ?>><?php echo $resultado_registros_motivo[$k][1]; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Regional"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <select class="form-control form-control-sm" name="regional" id="regional" required>
                                        <option value="">Seleccione</option>
                                        <?php for ($k=0; $k < count($resultado_registros_regional); $k++): ?>
                                            <option value="<?php echo $resultado_registros_regional[$k][0]; ?>" <?php echo ($resultado_registros_regional[$k][0]==$resultado_registros[0][10]) ? 'selected' : ''; ?>><?php echo $resultado_registros_regional[$k][1]; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Centro zonal"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <select class="form-control form-control-sm" name="centro_zonal" id="centro_zonal" required>
                                        <option value="">Seleccione</option>
                                        <?php for ($k=0; $k < count($resultado_registros_czonal); $k++): ?>
                                            <option value="<?php echo $resultado_registros_czonal[$k][0]; ?>" <?php echo ($resultado_registros_czonal[$k][0]==$resultado_registros[0][11]) ? 'selected' : ''; ?>><?php echo $resultado_registros_czonal[$k][2]; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                    </div>
                    <div class="col-md-12 p-0">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_seccion">Siguiente</button>
                            <?php if(count($_SESSION['registro_configuracion_encuesta_navegacion'])>1): ?>
                                <a href="gestion_encuestas_crear_encuesta_diligenciar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $id_filtro; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($id_registro); ?>&regresar=on" class="btn btn-warning float-right ml-1">Regresar</a>
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
    <script type="text/javascript">
        $(document).ready(function(){
           $("#regional").change(function () {
               $("#regional option:selected").each(function () {
                id=$(this).val();
                $.post("gestion_encuestas_crear_encuesta_informacion_complementaria_validar_czonal.php", { id:id }, function(data){
                $("#centro_zonal").html(data);
                });
            });
           })
        });
    </script>
</body>
</html>