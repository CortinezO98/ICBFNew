<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Alertas | Seguimiento";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $estado=validar_input($_GET['est']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_registro"])){
        $observaciones=$_POST['observaciones'];
        $observaciones_avance="Seguimiento: ".$observaciones;
        
        $ruta_actual="storage/";

        if ($_FILES['documento1']['name']!="") {
            $codigo_documento1=generar_codigo(10);
            $archivo_extension1 = strtolower(pathinfo($_FILES['documento1']['name'], PATHINFO_EXTENSION));
            $NombreArchivo1=$codigo_documento1.".".$archivo_extension1;
            $ruta_final1=$ruta_actual.$NombreArchivo1;
            if ($_FILES['documento1']["error"] > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                $control_documento1=0;
            } else {
              /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                if (move_uploaded_file($_FILES['documento1']['tmp_name'], $ruta_final1)) {
                    $control_documento1=1;
                } else {
                    $control_documento1=0;
                }
            }
        } else {
            $control_documento1=1;
        }

        if ($_FILES['documento2']['name']!="") {
            $codigo_documento2=generar_codigo(10);
            $archivo_extension2 = strtolower(pathinfo($_FILES['documento2']['name'], PATHINFO_EXTENSION));
            $NombreArchivo2=$codigo_documento2.".".$archivo_extension2;
            $ruta_final2=$ruta_actual.$NombreArchivo2;
            if ($_FILES['documento2']["error"] > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                $control_documento2=0;
            } else {
              /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                if (move_uploaded_file($_FILES['documento2']['tmp_name'], $ruta_final2)) {
                    $control_documento2=1;
                } else {
                    $control_documento2=0;
                }
            }
        } else {
            $control_documento2=1;
        }

        if ($_FILES['documento3']['name']!="") {
            $codigo_documento3=generar_codigo(10);
            $archivo_extension3 = strtolower(pathinfo($_FILES['documento3']['name'], PATHINFO_EXTENSION));
            $NombreArchivo3=$codigo_documento3.".".$archivo_extension3;
            $ruta_final3=$ruta_actual.$NombreArchivo3;
            if ($_FILES['documento3']["error"] > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al cargar el documento, por favor verifique e intente nuevamente!', 0);</script>";
                $control_documento3=0;
            } else {
              /*ahora co la funcion move_uploaded_file lo guardaremos en el destino que queramos*/
                if (move_uploaded_file($_FILES['documento3']['tmp_name'], $ruta_final3)) {
                    $control_documento3=1;
                } else {
                    $control_documento3=0;
                }
            }
        } else {
            $control_documento3=1;
        }

        if ($control_documento1==1 AND $control_documento2==1 AND $control_documento3==1) {
            // Prepara la sentencia
            $sentencia_insert_avance = $enlace_db->prepare("INSERT INTO `tb_gestion_alertas_avances`(`gaa_consecutivo`, `gaa_avance`, `gaa_sop1_nombre`, `gaa_sop1_ruta`, `gaa_sop1_extension`, `gaa_sop2_nombre`, `gaa_sop2_ruta`, `gaa_sop2_extension`, `gaa_sop3_nombre`, `gaa_sop3_ruta`, `gaa_sop3_extension`, `gaa_registro_usuario`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert_avance->bind_param('ssssssssssss', $id_registro, $observaciones_avance, $_FILES['documento1']['name'], $ruta_final1, $archivo_extension1, $_FILES['documento2']['name'], $ruta_final2, $archivo_extension2, $_FILES['documento3']['name'], $ruta_final3, $archivo_extension3, $_SESSION['usu_id']);
            
            if ($sentencia_insert_avance->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                header('Location:gestion_alertas_seguimiento.php?pagina='.$pagina.'&id='.$filtro_permanente.'&est='.$estado.'&reg='.base64_encode($id_registro));
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos` FROM `tb_gestion_alertas` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id` WHERE `ga_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_encuestas="SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_observaciones`, `ger_registro_usuario`, `ger_registro_fecha`, TU.`usu_nombres_apellidos`, `ger_contactado`, `ger_efectivo` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` WHERE `ger_radicado`=? ORDER BY `ger_registro_fecha` DESC LIMIT 0,1";

    $consulta_registros_encuestas = $enlace_db->prepare($consulta_string_encuestas);
    $consulta_registros_encuestas->bind_param("s", $resultado_registros[0][1]);
    $consulta_registros_encuestas->execute();
    $resultado_registros_encuestas = $consulta_registros_encuestas->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_avances="SELECT `gaa_id`, `gaa_consecutivo`, `gaa_avance`, `gaa_registro_usuario`, TU.`usu_nombres_apellidos`, `gaa_registro_fecha`, `gaa_sop1_nombre`, `gaa_sop1_ruta`, `gaa_sop1_extension`, `gaa_sop2_nombre`, `gaa_sop2_ruta`, `gaa_sop2_extension`, `gaa_sop3_nombre`, `gaa_sop3_ruta`, `gaa_sop3_extension` FROM `tb_gestion_alertas_avances` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas_avances`.`gaa_registro_usuario`=TU.`usu_id` WHERE `gaa_consecutivo`=? ORDER BY `gaa_registro_fecha` DESC";

    $consulta_registros_avances = $enlace_db->prepare($consulta_string_avances);
    $consulta_registros_avances->bind_param("s", $id_registro);
    $consulta_registros_avances->execute();
    $resultado_registros_avances = $consulta_registros_avances->get_result()->fetch_all(MYSQLI_NUM);

    
?>

<div class="row justify-content-center">
    <div class="col-md-4 pt-2">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th class="align-middle text-left">Consecutivo</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][0]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Radicado SIM</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][1]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left" style="min-width: 100px;">Fecha Alerta</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][4]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Fecha Atención al Ciudadano</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][5]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Regional</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][22]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Centro Zonal</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][23]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Doc. Peticionario</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][8]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Nombre Peticionario</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][9]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Teléfono</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][10]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Agente</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][24]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Radicado SIM Encuesta</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][2]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Categoría</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][11]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Contexto</th>
                        <td class="align-middle"><?php echo $resultado_registros[0][12]; ?></td>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="col-md-4 pt-2">
        <div><span class="fas fa-history"></span> Historial de gestión</div>
        <div class="avances-seguimiento" style="max-height: 450px;">
            
        <?php for ($i=0; $i < count($resultado_registros_avances); $i++): ?>
            <div class="alert alert-warning p-1 font-size-11 mb-1">
                <span class="fas fa-calendar-alt"></span> <?php echo $resultado_registros_avances[$i][5]; ?> 
                 | <span class="fas fa-user"></span> <?php echo $resultado_registros_avances[$i][4]; ?>
                <?php if($resultado_registros_avances[$i][6]!=""): ?>
                <br><a href="<?php echo $resultado_registros_avances[$i][7]; ?>" target="_blank" title="<?php echo $resultado_registros_avances[$i][6]; ?>"><span class="fas fa-external-link-alt"></span></a> <a href="#" onClick="srcChange('<?php echo $resultado_registros_avances[$i][7]; ?>#toolbar=0&navpanes=0&zoom=50', '<?php echo $resultado_registros_avances[$i][6]; ?>')" title="<?php echo $resultado_registros_avances[$i][6]; ?>"><span class="fas fa-eye"></span> <?php echo validar_extension_icono($resultado_registros_avances[$i][8])." ".substr($resultado_registros_avances[$i][6], 0,40)."..."; ?></a>
                <?php endif; ?>
                <?php if($resultado_registros_avances[$i][9]!=""): ?>
                <br><a href="<?php echo $resultado_registros_avances[$i][10]; ?>" target="_blank" title="<?php echo $resultado_registros_avances[$i][9]; ?>"><span class="fas fa-external-link-alt"></span></a> <a href="#" onClick="srcChange('<?php echo $resultado_registros_avances[$i][10]; ?>#toolbar=0&navpanes=0&zoom=50', '<?php echo $resultado_registros_avances[$i][9]; ?>')" title="<?php echo $resultado_registros_avances[$i][9]; ?>"><span class="fas fa-eye"></span> <?php echo validar_extension_icono($resultado_registros_avances[$i][11])." ".substr($resultado_registros_avances[$i][9], 0,40)."..."; ?></a>
                <?php endif; ?>
                <?php if($resultado_registros_avances[$i][12]!=""): ?>
                <br><a href="<?php echo $resultado_registros_avances[$i][13]; ?>" target="_blank" title="<?php echo $resultado_registros_avances[$i][12]; ?>"><span class="fas fa-external-link-alt"></span></a> <a href="#" onClick="srcChange('<?php echo $resultado_registros_avances[$i][13]; ?>#toolbar=0&navpanes=0&zoom=50', '<?php echo $resultado_registros_avances[$i][12]; ?>')" title="<?php echo $resultado_registros_avances[$i][12]; ?>"><span class="fas fa-eye"></span> <?php echo validar_extension_icono($resultado_registros_avances[$i][14])." ".substr($resultado_registros_avances[$i][12], 0,40)."..."; ?></a>
                <?php endif; ?>
                <br><span class="fas fa-file-alt"></span> <?php echo nl2br($resultado_registros_avances[$i][2]); ?>
            </div>
        <?php endfor; ?>
        </div>
    </div>
    <div class="col-md-4 pt-2 background-blanco">
        <p class="alert alert-warning p-1 d-none mb-0 font-size-11" id="titulo_visor"></p>                
        <embed id="embed1" src="" width="100%" height="500px" class="d-none mt-0 visor_div" type="application/pdf"/>
        
    </div>
</div>
<script type="text/javascript">
    function srcChange(url, nombre) {
        $("#titulo_visor").removeClass('d-none').addClass('d-block');
        $("#embed1").removeClass('d-none').addClass('d-block');
        $("#titulo_visor").html('<span class="fas fa-times" onClick="cerrar_documento();"></span> Documento: '+nombre);
        var oldEmbed = document.getElementById('embed1');
        var newEmbed = oldEmbed.cloneNode(true);
        newEmbed.src = url;
        oldEmbed.parentNode.replaceChild(newEmbed, oldEmbed);
    }

    function cerrar_documento() {
        $("#titulo_visor").removeClass('d-block').addClass('d-none');
        $("#embed1").removeClass('d-block').addClass('d-none');
        $("#titulo_visor").html('');
        var oldEmbed = document.getElementById('embed1');
        var newEmbed = oldEmbed.cloneNode(true);
        newEmbed.src = '';
        oldEmbed.parentNode.replaceChild(newEmbed, oldEmbed);
    }

</script>