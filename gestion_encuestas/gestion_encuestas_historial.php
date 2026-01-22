<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Historial";
    $id_registro=validar_input(base64_decode($_GET['reg']));

    $consulta_string_radicado="SELECT `gera_radicado`, `gera_matriz_id`, TE.`gem_nombre_encuesta`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_genero`, `gera_motivo`, `gera_regional`, `gera_centro_zonal`, `gera_estado_gestion`, `gera_intentos`, `gera_contactado`, `gera_efectivo`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, `gera_usuario_gestion`, TU.`usu_nombres_apellidos`, `gera_registro_fecha`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, TR.`gere_regional`, TCZ.`gercz_centro_zonal` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TE ON `tb_gestion_encuesta_radicado`.`gera_matriz_id`=TE.`gem_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_encuesta_radicado`.`gera_centro_zonal`=TCZ.`gercz_id` WHERE `gera_radicado`=?";

    $consulta_registros_radicado = $enlace_db->prepare($consulta_string_radicado);
    $consulta_registros_radicado->bind_param("s", $id_registro);
    $consulta_registros_radicado->execute();
    $resultado_registros_radicado = $consulta_registros_radicado->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_encuestas="SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_observaciones`, `ger_registro_usuario`, `ger_registro_fecha`, TU.`usu_nombres_apellidos`, `ger_contactado`, `ger_efectivo` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` WHERE `ger_radicado`=? ORDER BY `ger_registro_fecha` DESC";

    $consulta_registros_encuestas = $enlace_db->prepare($consulta_string_encuestas);
    $consulta_registros_encuestas->bind_param("s", $id_registro);
    $consulta_registros_encuestas->execute();
    $resultado_registros_encuestas = $consulta_registros_encuestas->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_log_cambios="SELECT `gerlog_id`, `gerlog_radicado`, `gerlog_campo`, `gerlog_anterior`, `gerlog_nuevo`, `gerlog_registro_usuario`, `gerlog_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_encuesta_radicado_log` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado_log`.`gerlog_registro_usuario`=TU.`usu_id` WHERE `gerlog_radicado`=? ORDER BY `gerlog_registro_fecha` DESC";

    $consulta_registros_log_cambios = $enlace_db->prepare($consulta_string_log_cambios);
    $consulta_registros_log_cambios->bind_param("s", $id_registro);
    $consulta_registros_log_cambios->execute();
    $resultado_registros_log_cambios = $consulta_registros_log_cambios->get_result()->fetch_all(MYSQLI_NUM);    
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
</head>
<body>
    <div class="menu-bar">
        <a href="javascript:window.close();" class="home">
            <img src="../images/logo.png" class="logo_home">
        </a>
        <p class="titulo_header"><?php echo $titulo_header; ?></p>
    </div>
    <div class="contenido">
        <div class="row">
            <div class="col-md-3 py-2">
                
            </div>
            <div class="col-md-9 py-2">
                <a href="javascript:window.close();" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
            </div>
        </div>
        <form name="guardar_monitoreo" action="" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle text-left">Matriz Encuesta</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][2]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Radicado</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][0]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Estado</th>
                                <td class="align-middle">
                                    <?php if($resultado_registros_radicado[0][12]=="Pendiente"): ?>
                                        <span class="fas fa-user-clock color-gris" title="Pendiente"></span>
                                    <?php elseif($resultado_registros_radicado[0][12]=="Cerrado"): ?>
                                        <span class="fas fa-user-lock color-verde" title="Cerrado"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Contacto</th>
                                <td class="align-middle">
                                    <?php if($resultado_registros_radicado[0][14]==""): ?>
                                        <span class="fas fa-minus-circle color-gris" title="Pendiente"></span>
                                    <?php elseif($resultado_registros_radicado[0][14]==1): ?>
                                        <span class="fas fa-phone color-verde" title="Contactado"></span>
                                    <?php elseif($resultado_registros_radicado[0][14]==0): ?>
                                        <span class="fas fa-phone-slash color-rojo" title="No contactado"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Efectivo</th>
                                <td class="align-middle">
                                    <?php if($resultado_registros_radicado[0][15]==""): ?>
                                        <span class="fas fa-minus-circle color-gris" title="Pendiente"></span>
                                    <?php elseif($resultado_registros_radicado[0][15]==1): ?>
                                        <span class="fas fa-user-check color-verde" title="Efectivo"></span>
                                    <?php elseif($resultado_registros_radicado[0][15]==0): ?>
                                        <span class="fas fa-user-times color-rojo" title="No efectivo"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Intentos</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][13]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Doc. Peticionario</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][4]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Nombres y Apellidos</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][5]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Teléfono</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][6]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Celular</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][7]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Regional</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][23]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Centro Zonal</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][24]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Última actualización</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][17]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Observaciones</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][16]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Usuario Asignado</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][19]; ?></td>
                            </tr>
                            <tr>
                                <th class="align-middle text-left">Fecha Registro</th>
                                <td class="align-middle"><?php echo $resultado_registros_radicado[0][20]; ?></td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="col-md-8">
                <?php if (count($resultado_registros_encuestas)>0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th colspan="6">Encuestas Asociadas</th>
                                </tr>
                                <tr>
                                    <th>Consecutivo</th>
                                    <th>Contacto</th>
                                    <th>Efectivo</th>
                                    <th>Observaciones</th>
                                    <th>Usuario Registro</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    for ($i=0; $i < count($resultado_registros_encuestas); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle text-center"><?php echo $resultado_registros_encuestas[$i][0]; ?></td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros_encuestas[$i][7]==""): ?>
                                            <span class="fas fa-minus-circle color-gris" title="Pendiente"></span>
                                        <?php elseif($resultado_registros_encuestas[$i][7]==1): ?>
                                            <span class="fas fa-phone color-verde" title="Contactado"></span>
                                        <?php elseif($resultado_registros_encuestas[$i][7]==0): ?>
                                            <span class="fas fa-phone-slash color-rojo" title="No contactado"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros_encuestas[$i][8]==""): ?>
                                            <span class="fas fa-minus-circle color-gris" title="Pendiente"></span>
                                        <?php elseif($resultado_registros_encuestas[$i][8]==1): ?>
                                            <span class="fas fa-user-check color-verde" title="Efectivo"></span>
                                        <?php elseif($resultado_registros_encuestas[$i][8]==0): ?>
                                            <span class="fas fa-user-times color-rojo" title="No efectivo"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo nl2br($resultado_registros_encuestas[$i][3]); ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros_encuestas[$i][6]; ?></td>
                                    <td class="align-middle text-center"><?php echo $resultado_registros_encuestas[$i][5]; ?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning p-1">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros de encuestas
                    </p>
                <?php endif; ?>
                <?php if (count($resultado_registros_log_cambios)>0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th colspan="5">Control Cambios</th>
                                </tr>
                                <tr>
                                    <th>Campo</th>
                                    <th>Anterior</th>
                                    <th>Nuevo</th>
                                    <th>Usuario Registro</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    for ($i=0; $i < count($resultado_registros_log_cambios); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle"><?php echo $resultado_registros_log_cambios[$i][2]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros_log_cambios[$i][3]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros_log_cambios[$i][4]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros_log_cambios[$i][7]; ?></td>
                                    <td class="align-middle text-center"><?php echo $resultado_registros_log_cambios[$i][6]; ?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning p-1">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros de log de cambios
                    </p>
                <?php endif; ?>
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