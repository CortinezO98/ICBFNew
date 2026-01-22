<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $estado_bandeja=validar_input($_GET['est']);
    $titulo_header = "Gestión Alertas | ".$estado_bandeja;
    $pagina=validar_input($_GET['pagina']);

    unset($_SESSION['gestion_alerta_valida_n1']);
    unset($_SESSION['gestion_alerta_valida_n2']);
    unset($_SESSION['gestion_alerta_reenvia']);
    // Inicializa variable tipo array
    $data_consulta=array();
    
    if ($estado_bandeja=="Pendientes") {
        $filtro_estado=" AND (`ga_estado`=?)";
        array_push($data_consulta, 'Pendiente');
    } elseif($estado_bandeja=="Revisado N1"){
        $filtro_estado=" AND (`ga_estado`=?)";
        array_push($data_consulta, 'Pendiente RN2');
    } elseif($estado_bandeja=="Escalado Regional"){
        $filtro_estado=" AND (`ga_estado`=?)";
        array_push($data_consulta, 'Escalado Regional');
    } elseif($estado_bandeja=="Cerrados"){
        $filtro_estado=" AND (`ga_estado`=?)";
        array_push($data_consulta, 'Cerrado');
    }

    // Ejemplo filtro campo buscar GTO
    if (isset($_POST["filtro"])) {
        $pagina=1;
        $filtro_permanente=validar_input($_POST['id_filtro']);
    } else {
        $filtro_permanente=validar_input($_GET['id']);
    }

    // Configuracón Paginación
    $registros_x_pagina=50;
    $iniciar_pagina=($pagina-1)*$registros_x_pagina;

    // Valida que filtro se deba ejecutar
    if ($filtro_permanente!="null" AND $filtro_permanente!="") {
        $filtro_buscar="AND (`ga_id` LIKE ? OR `ga_sim_asociado` LIKE ? OR `ga_sim_qr_encuesta` LIKE ? OR `ga_estado` LIKE ? OR `ga_fecha_alerta` LIKE ? OR `ga_fecha_atencion_ciudadano` LIKE ? OR `ga_regional` LIKE ? OR `ga_centro_zonal` LIKE ? OR `ga_peticionario_id` LIKE ? OR `ga_peticionario_nombre` LIKE ? OR `ga_peticionario_telefono` LIKE ? OR `ga_categoria_afectacion` LIKE ? OR `ga_descripcion_alerta` LIKE ? OR `ga_afecta_linea_tecnica` LIKE ? OR `ga_fecha_remite_agente_especializado` LIKE ? OR `ga_fecha_require_regional` LIKE ? OR `ga_fecha_respuesta_regional` LIKE ? OR `ga_concepto_tecnico_pmejora` LIKE ? OR `ga_decision_caso` LIKE ? OR `ga_snc_ac` LIKE ? OR `ga_registro_usuario` LIKE ? OR `ga_registro_fecha` LIKE ? OR TR.`gere_regional` LIKE ? OR TCZ.`gercz_centro_zonal` LIKE ? OR TU.`usu_nombres_apellidos` LIKE ?)";

        //Contar catidad de variables a filtrar
        $cantidad_filtros=count(explode('?', $filtro_buscar))-1;

        //Agregar catidad de variables a filtrar a data consulta
        for ($i=0; $i < $cantidad_filtros; $i++) { 
            array_push($data_consulta, "%$filtro_permanente%");//Se agrega llave por ser variable evaluada en un like
        }
    }

    // Prepara string a ejecutar en sentencia preparada
    $consulta_contar_string="SELECT COUNT(`ga_id`) FROM `tb_gestion_alertas` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id` WHERE 1=1 ".$filtro_estado." ".$filtro_buscar."";

    // Agrega string a sentencia preparada
    $consulta_contar_registros = $enlace_db->prepare($consulta_contar_string);
    
    // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
    $consulta_contar_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    // Ejecuta sentencia preparada
    $consulta_contar_registros->execute();
    // Obtiene array resultado de ejecución sentencia preparada
    $resultado_registros_contar = $consulta_contar_registros->get_result()->fetch_all(MYSQLI_NUM);
    $registros_cantidad_total = $resultado_registros_contar[0][0];
    //Cálculo número de páginas 
    $numero_paginas=ceil($registros_cantidad_total/$registros_x_pagina);

    if (!isset($_GET['pagina']) || ($pagina>$numero_paginas AND $numero_paginas>0) || $pagina<=0) {
        header('Location:gestion_alertas.php?pagina=1&id=null&est=Pendientes');
    }

    //Agregar pagina a array data_consulta
    array_push($data_consulta, $iniciar_pagina);
    array_push($data_consulta, $registros_x_pagina);

    $consulta_string="SELECT `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`, `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`, `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`, `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`, `ga_registro_usuario`, `ga_registro_fecha`, TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`, `ga_fecha_cierre` FROM `tb_gestion_alertas` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id` WHERE 1=1 ".$filtro_estado." ".$filtro_buscar." ORDER BY `ga_id` ASC LIMIT ?,?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_conteo_estado="SELECT `ga_estado`, COUNT(`ga_id`) FROM `tb_gestion_alertas` GROUP BY `ga_estado`";
    $consulta_registros_conteo_estado = $enlace_db->prepare($consulta_string_conteo_estado);
    $consulta_registros_conteo_estado->execute();
    $resultado_registros_conteo_estado = $consulta_registros_conteo_estado->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_conteo_estado); $i++) { 
        $array_conteo_estado[$resultado_registros_conteo_estado[$i][0]]=$resultado_registros_conteo_estado[$i][1];
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
    <script type="text/javascript">
        <?php for ($i=0; $i < count($resultado_registros); $i++): ?>
            //tiempo_gestion
            sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_anio=<?php echo date('Y',strtotime($resultado_registros[$i][4]));?>;
            sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_mes=<?php echo date('m',strtotime($resultado_registros[$i][4]))-1;?>;
            sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_dia=<?php echo date('d',strtotime($resultado_registros[$i][4]));?>;
            sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_hora=<?php echo date('H',strtotime($resultado_registros[$i][4]));?>;
            sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_minuto=<?php echo date('i',strtotime($resultado_registros[$i][4]));?>;
            sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_segundo=<?php echo date('s',strtotime($resultado_registros[$i][4]));?>;
            
            <?php if ($resultado_registros[$i][3]=="Cerrado"): ?>
                //fecha_resuelto cuando está diligenciada
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_anio=<?php echo date('Y',strtotime($resultado_registros[$i][25]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_mes=<?php echo date('m',strtotime($resultado_registros[$i][25]))-1;?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_dia=<?php echo date('d',strtotime($resultado_registros[$i][25]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_hora=<?php echo date('H',strtotime($resultado_registros[$i][25]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_minuto=<?php echo date('i',strtotime($resultado_registros[$i][25]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_segundo=<?php echo date('s',strtotime($resultado_registros[$i][25]));?>;
            <?php else: ?>
                //fecha_resuelto cuando está vacía
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_anio="";
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_mes="";
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_dia="";
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_hora="";
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_minuto="";
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_segundo="";
            <?php endif; ?>
        <?php endfor; ?>
    </script>
</head>
<body onresize="tabla_fixed();" onload="tabla_fixed();
<?php for ($i=0; $i < count($resultado_registros); $i++): ?>
tiempo_gestion_<?php echo $i; ?>(sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_anio,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_mes,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_dia,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_hora,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_minuto,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_segundo);
<?php endfor; ?>
">
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row" id="elemento_1">
            <div class="col-md-3 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                      <input type="text" name="id_filtro" value='<?php if (isset($_POST["filtro"])) { echo $_POST['id_filtro']; } else {if($filtro_permanente!="null"){echo $filtro_permanente;}} ?>' placeholder="Búsqueda" class="form-control" required autofocus>
                      <span class="input-group-btn">
                        <button class="btn btn-corp" type="submit" name="filtro"><span class="fas fa-search"></span></button>
                        <a href="gestion_alertas.php?pagina=1&id=null&est=<?php echo $estado_bandeja; ?>" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                      </span>
                    </div>
                </form>
            </div>
            <div class="col-md-9 py-2">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Usuario"): ?>
                    <a href="gestion_alertas_estadisticas.php" class="btn btn-corp menu float-right" target="_blank" title="Estadístias"><span class="fas fa-chart-pie"></span></a>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-right' data-target="#dataexport" title="Reportes"><span class="fas fa-file-excel float-left"></span></button>
                <?php endif; ?>
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-right' data-target="#datainforme" title="Informes"><span class="fas fa-envelope float-left"></span></button>
                <?php endif; ?>
                <a href="gestion_alertas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Cerrados" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-user-lock"></span></div><div class="pl-2 menu_res float-left">Cerrados</div></a>
                <a href="gestion_alertas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Escalado Regional" class="btn btn-corp menu float-right"><div class="float-left"><?php echo ($array_conteo_estado['Escalado Regional']>0) ? "<div class='float-left alert_conteo_menu mr-1'>".$array_conteo_estado['Escalado Regional']."</div>" : ""; ?><span class="fas fa-check-double"></span></div><div class="pl-2 menu_res float-left">Escalado Regional</div></a>
                <a href="gestion_alertas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Revisado N1" class="btn btn-corp menu float-right"><div class="float-left"><?php echo ($array_conteo_estado['Pendiente RN2']>0) ? "<div class='float-left alert_conteo_menu mr-1'>".$array_conteo_estado['Pendiente RN2']."</div>" : ""; ?><span class="fas fa-user-check"></span></div><div class="pl-2 menu_res float-left">Revisado N1</div></a>
                <a href="gestion_alertas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Pendientes" class="btn btn-corp menu float-right"><div class="float-left"><?php echo ($array_conteo_estado['Pendiente']>0) ? "<div class='float-left alert_conteo_menu mr-1'>".$array_conteo_estado['Pendiente']."</div>" : ""; ?><span class="fas fa-user-clock"></span></div><div class="pl-2 menu_res float-left">Pendientes</div></a>
            </div>
        </div>
        <div class="row" id="tabla_fixed">
            <div class="col-md-12">
                <?php if ($registros_cantidad_total>0): ?>
                    <div class="col-md-7 float-left">
                        <!-- sub menú (cambiar a col-md-5 a paginacion) -->
                    </div>
                    <div class="col-md-12 float-left">
                        <nav aria-label="Paginación" class="paginacion">
                            <ul class="pagination justify-content-end">
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-left"></span></a></li>
                                <?php
                                    if ($numero_paginas<=5 OR $pagina<=3) {
                                        $pagina_inicio=1; $pagina_fin=$numero_paginas;
                                        if ($pagina<=3 AND $numero_paginas>=5) {
                                            $pagina_fin=5;
                                        }
                                    } else {
                                        $pagina_inicio=$pagina-2; $pagina_fin=$pagina+2;
                                        if (($numero_paginas-$pagina_inicio)<=5) {
                                            $pagina_inicio=$numero_paginas-4; $pagina_fin=$numero_paginas;
                                        }
                                    }
                                ?>
                                <?php for ($i=$pagina_inicio; $i <= $pagina_fin; $i++): ?>
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-right"></a></li>
                            </ul>
                        </nav>
                    </div>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle" style="width: 55px;"></th>
                                    <th class="align-middle">Consecutivo</th>
                                    <th class="align-middle">Estado</th>
                                    <th class="align-middle">SIM Asociado</th>
                                    <th class="align-middle">Fecha Alerta</th>
                                    <th class="align-middle">Fecha Atención</th>
                                    <th class="align-middle">Tiempo Gestión</th>
                                    <th class="align-middle">Regional</th>
                                    <th class="align-middle">Centro Zonal</th>
                                    <th class="align-middle">Doc. Peticionario</th>
                                    <th class="align-middle">Nombres y Apellidos</th>
                                    <th class="align-middle">Contacto</th>
                                    <th class="align-middle">Categoría</th>
                                    <th class="align-middle">Descripción</th>
                                    <th class="align-middle">Afecta LT</th>
                                    <th class="align-middle">Concepto Técnico</th>
                                    <th class="align-middle">Decisión Caso</th>
                                    <th class="align-middle">Agente Remite</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <?php if($perfil_modulo!="Visitante"): ?>
                                            <?php if($resultado_registros[$i][3]=="Pendiente" AND ($perfil_modulo=="Usuario" OR $perfil_modulo=="Administrador")): ?>
                                                <a href="gestion_alertas_editar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-primary btn-sm btn-width mb-1" title="Editar"><span class="fas fa-pen"></span></a>
                                                
                                            <?php elseif($resultado_registros[$i][3]=="Pendiente RN2" AND ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor")): ?>
                                                <a href="gestion_alertas_editar_n2.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-primary btn-sm btn-width mb-1" title="Editar"><span class="fas fa-pen"></span></a>
                                                
                                            <?php elseif($resultado_registros[$i][3]=="Escalado Regional" AND ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor")): ?>
                                                <a href="gestion_alertas_editar_info.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-primary btn-sm btn-width mb-1" title="Editar"><span class="fas fa-pen"></span></a>
                                                <a href="gestion_alertas_seguimiento.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-danger btn-sm btn-width mb-1" title="Seguimiento"><span class="fas fa-flag"></span></a>
                                                <a href="gestion_alertas_reenviar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-dark btn-sm btn-width mb-1" title="Reenviar Notificación"><span class="fas fa-envelope"></span></a>
                                            <?php elseif($resultado_registros[$i][3]=="Cerrado"): ?>
                                                <a href="#" onClick="open_modal_solicitud('<?php echo base64_encode($resultado_registros[$i][0]); ?>');" class="btn btn-success btn-sm btn-width mb-1" title="Ver Detalle"><span class="fas fa-file-alt"></span></a>
                                            <?php endif; ?>
                                            <?php if($resultado_registros[$i][3]!="Pendiente"): ?>
                                                <a href="gestion_alertas_editar_reiniciar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-warning btn-sm btn-width mb-1" title="Reiniciar validación"><span class="fas fa-retweet"></span></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center"><?php echo $resultado_registros[$i][0]; ?></td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][3]=="Pendiente"): ?>
                                            <span class="fas fa-user-clock color-gris" title="Pendiente"></span>
                                        <?php elseif($resultado_registros[$i][3]=="Pendiente RN2"): ?>
                                            <span class="fas fa-user-check color-verde" title="Pendiente RN2"></span>
                                        <?php elseif($resultado_registros[$i][3]=="Escalado Regional"): ?>
                                            <span class="fas fa-check-double color-naranja" title="Escalado Regional"></span>
                                        <?php elseif($resultado_registros[$i][3]=="Cerrado"): ?>
                                            <span class="fas fa-user-lock color-verde" title="Cerrado"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][1]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][4]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][5]; ?></td>
                                    <td class="align-middle"><p class="alert alert-success text-center p-1 m-0" id='tiempo_gestion_<?php echo $i; ?>'></p></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][22]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][23]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][8]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][9]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][10]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][11]; ?></td>
                                    <td class="align-middle">
                                        <div class="scroll" style="max-width: 200px; max-height: 80px; overflow-y: scroll;">
                                            <?php echo $resultado_registros[$i][12]; ?>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][13]; ?></td>
                                    <td class="align-middle">
                                        <div class="scroll" style="max-width: 200px; max-height: 80px; overflow-y: scroll;">
                                            <?php echo $resultado_registros[$i][17]; ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="scroll" style="max-width: 200px; max-height: 80px; overflow-y: scroll;">
                                            <?php echo $resultado_registros[$i][18]; ?>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][24]; ?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-7 float-left">
                        <p class="paginacion_descripcion">Mostrando <?php if($registros_cantidad_total>0){ echo ($pagina*$registros_x_pagina)-$registros_x_pagina+1;}else{echo "0";} ?> a <?php if(($pagina*$registros_x_pagina)>$registros_cantidad_total) { echo $registros_cantidad_total; } else { echo $pagina*$registros_x_pagina; } ?> de <?php echo $registros_cantidad_total; ?></p>
                    </div>
                    <div class="col-md-5 float-left">
                        <nav aria-label="Paginación" class="paginacion">
                            <ul class="pagination justify-content-end">
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-left"></span></a></li>
                                <?php
                                    if ($numero_paginas<=5 OR $pagina<=3) {
                                        $pagina_inicio=1; $pagina_fin=$numero_paginas;
                                        if ($pagina<=3 AND $numero_paginas>=5) {
                                            $pagina_fin=5;
                                        }
                                    } else {
                                        $pagina_inicio=$pagina-2; $pagina_fin=$pagina+2;
                                        if (($numero_paginas-$pagina_inicio)<=5) {
                                            $pagina_inicio=$numero_paginas-4; $pagina_fin=$numero_paginas;
                                        }
                                    }
                                ?>
                                <?php for ($i=$pagina_inicio; $i <= $pagina_fin; $i++): ?>
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-right"></a></li>
                            </ul>
                        </nav>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal SOLICITUD -->
        <div class="modal fade" id="modal_ver_solicitud" role="dialog">
            <div class="modal-dialog modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detalle alerta</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_solicitud();">×</button>
                    </div>
                    <div class="modal-body modal-body-solicitud p-1">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_solicitud();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
                    </div>
                </div>
            </div>
        </div>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
        include("gestion_alertas_informe.php");
        include("gestion_alertas_reporte.php");
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#modal_ver_solicitud").on('hidden.bs.modal', function () {
                close_modal_solicitud();
            });
        });
        
        function open_modal_solicitud(id_registro) {
            $('.modal-body-solicitud').load('gestion_alertas_detalle.php?reg='+id_registro,function(){
                $('#modal_ver_solicitud').modal({show:true});
            });
        }

        function close_modal_solicitud() {
            $('.modal-body-solicitud').html('');
        }

        function tabla_fixed(){
            var tabla_fixed = document.getElementById("tabla_fixed");
            var elemento = document.getElementById("table-fixed");
            var elemento_1 = document.getElementById("elemento_1");
            alto_ventana=window.outerHeight-elemento_1.clientHeight-100;
            alto_tabla=tabla_fixed.clientHeight+200;

            if (alto_tabla>alto_ventana) {
                alto_elemento=alto_ventana-200;
                elemento.style.height=alto_elemento+"px";
            }
        }

        <?php for ($i=0; $i < count($resultado_registros); $i++): ?>
            function tiempo_gestion_<?php echo $i; ?>(anio, mes, dia, hora, minuto, segundo){
                //set de variables para calculos
                var set_segundos = 1000;
                var set_minutos = set_segundos * 60;
                var set_horas = set_minutos * 60;
                var set_dias = set_horas * 24;
                //se definen las fechas de inicio y fin
                var fecha_inicio = new Date(anio,mes,dia,hora,minuto,segundo);
                if(sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_anio!="") {
                    //se define la fecha de resuelto
                    var fecha_fin = new Date(sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_anio,sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_mes,sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_dia,sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_hora,sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_minuto,sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_segundo);
                } else {
                    var fecha_fin = new Date(); //fecha actual
                }

                //se calcula la diferencia entre fechas segun la que sea mayor entre la de inicio y fin
                var diff=new Date(fecha_fin-fecha_inicio);
                
                //se calcula la difenrencia obtenida entre inicio y fin y se convierte en milisegundos
                diff_milisegundos = diff.getTime()

                //calculo de dias
                var result_dias = Math.floor(diff_milisegundos / set_dias);
                diff_milisegundos = diff_milisegundos - (result_dias * set_dias);

                //calculo de horas
                var result_horas = Math.floor(diff_milisegundos / set_horas);
                diff_milisegundos = diff_milisegundos - (result_horas * set_horas);

                //calculo de minutos
                var result_minutos = Math.floor(diff_milisegundos / set_minutos);
                diff_milisegundos = diff_milisegundos - (result_minutos * set_minutos);

                //calculo de segundos
                var result_segundos = Math.floor(diff_milisegundos / set_segundos);
                //se asigna a la variable result la cadena que muestra el resultado de dias, horas, minutos y segundos
                var result = result_dias + "d:" + result_horas + "h:" + result_minutos + "m:" + result_segundos + "s";
                //mostrar el resultado en la celda con el identificador asignado
                document.getElementById('tiempo_gestion_<?php echo $i; ?>').innerHTML = result;
                //Indicamos que se ejecute esta función nuevamente dentro de 1 segundo
                timeout=setTimeout("tiempo_gestion_<?php echo $i; ?>(sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_anio,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_mes,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_dia,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_hora,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_minuto,sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_segundo)",1000);
            }
        <?php endfor; ?>
    </script>
</body>
</html>