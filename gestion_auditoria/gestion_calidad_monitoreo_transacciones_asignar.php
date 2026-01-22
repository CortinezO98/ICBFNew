<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Monitoreos | Transacciones - Asignar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);

    // Inicializa variable tipo array
    $data_consulta=array();

    if(isset($_POST["guardar_registro"])){
        $asignar_registros=$_POST['asignar_registros'];
        $analista=validar_input($_POST['analista']);
        
        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calidad_monitoreo` SET `gcm_auditoria_responsable`=? WHERE  `gcm_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('ss', $analista, $id_registro);
        
        $control_update=0;
        $control_registro=0;
        for ($i=0; $i < count($asignar_registros); $i++) { 
            if ($asignar_registros[$i]!='') {
                $control_registro++;
                $id_registro=$asignar_registros[$i];
                // Ejecuta sentencia preparada
                $consulta_actualizar->execute();

                if (comprobarSentencia($enlace_db->info)) {
                    $control_update++;
                }
            }
        }

        if ($control_registro==$control_update) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TMC.`gcm_nota_general`, TMC.`gcm_nota_enc_estado`, TMC.`gcm_nota_ecn_estado`, TMC.`gcm_nota_ecuf_estado`, TMC.`gcm_aplica_indicador`, TMC.`gcm_fecha_monitoreo`, TMC.`gcm_registro_usuario`, TM.`gcm_observaciones`, TMC.`gcm_auditoria`, TMC.`gcm_auditoria_responsable`, TAU.`gcm_id` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_auditoria_responsable`=TUR.`usu_id` LEFT JOIN `tb_gestion_auditoria_monitoreo` AS TAU ON TMC.`gcm_id`=TAU.`gcm_id` WHERE 1=1 AND `gcm_auditoria`='Si' AND TAU.`gcm_id` IS NULL ORDER BY TMC.`gcm_id` ASC";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_analista="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE (`usu_cargo_rol` LIKE '%Auditor Calidad%') ORDER BY `usu_nombres_apellidos`";
    $consulta_registros_analistas = $enlace_db->prepare($consulta_string_analista);
    $consulta_registros_analistas->execute();
    $resultado_registros_analistas = $consulta_registros_analistas->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_monitoreo_transacciones.php?pagina=".$pagina."&id=".$filtro_permanente;
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
            <div class="col-md-8 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div id="table-fixed" class="table-responsive table-fixed">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="align-middle" style="width: 55px;"></th>
                                        <th class="align-middle">Consecutivo</th>
                                        <th class="align-middle">Matriz</th>
                                        <th class="align-middle">Responsable</th>
                                        <th class="align-middle">Skill Interacción</th>
                                        <th class="align-middle">Tipo Gestión</th>
                                        <th class="align-middle">Segmento</th>
                                        <th class="align-middle">Duración</th>
                                        <th class="align-middle">Fecha Gestión</th>
                                    </tr>
                                </thead>    
                                <tbody>    
                                    <?php
                                        for ($i=0; $i < count($resultado_registros); $i++) { 
                                    ?>
                                    <tr>
                                        <td class="align-middle text-center">
                                            <div class="form-group m-0">
                                                  <div class="form-group custom-control custom-checkbox m-0">
                                                      <input type="checkbox" class="custom-control-input" id="customCheck<?php echo $i; ?>" name="asignar_registros[]" value="<?php echo $resultado_registros[$i][0]; ?>">
                                                      <label class="custom-control-label p-0 m-0" for="customCheck<?php echo $i; ?>"></label>
                                                  </div>
                                            </div>
                                        </td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][0]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][1]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][15]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][5]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][6]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][7]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][3]; ?></td>
                                        <td class="align-middle"><?php echo $resultado_registros[$i][2]; ?></td>
                                    </tr>
                                    <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="analista" class="m-0">Analista</label>
                            <select class="form-control form-control-sm" name="analista" id="analista" required>
                              <option value="">Seleccione</option>
                              <?php for ($i=0; $i < count($resultado_registros_analistas); $i++): ?> 
                                <option value="<?php echo $resultado_registros_analistas[$i][0]; ?>" class="font-size-11" <?php if($resultado_registros[0][2]==$resultado_registros_analistas[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_analistas[$i][1]; ?></option>
                              <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"])): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
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