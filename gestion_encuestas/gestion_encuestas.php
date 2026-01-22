<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $estado_bandeja=validar_input($_GET['est']);
    $titulo_header = "Encuestas | ".$estado_bandeja;
    $pagina=validar_input($_GET['pagina']);

    unset($_SESSION['registro_cargue_base']);
    unset($_SESSION['usuarios_asignacion']);
    unset($_SESSION['registro_reasignar_base']);
    unset($_SESSION['usuarios_reasignacion']);

    // Inicializa variable tipo array
    $data_consulta=array();
    
    if ($estado_bandeja=="Pendientes") {
        $filtro_estado=" AND (`gera_estado_gestion`=?)";
        array_push($data_consulta, 'Pendiente');
    } elseif($estado_bandeja=="Cerrado"){
        $filtro_estado=" AND (`gera_estado_gestion`=?)";
        array_push($data_consulta, 'Cerrado');
    } elseif($estado_bandeja=="Cancelado"){
        $filtro_estado=" AND (`gera_estado_gestion`=?)";
        array_push($data_consulta, 'Cancelado');
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
        $filtro_buscar="AND (`gera_radicado` LIKE ? OR `gera_matriz_id` LIKE ? OR TE.`gem_nombre_encuesta` LIKE ? OR `gera_fecha_peticion` LIKE ? OR `gera_peticionario_id` LIKE ? OR `gera_peticionario_nombre` LIKE ? OR `gera_tel_fijo` LIKE ? OR `gera_tel_movil` LIKE ? OR `gera_genero` LIKE ? OR `gera_motivo` LIKE ? OR `gera_regional` LIKE ? OR `gera_centro_zonal` LIKE ? OR `gera_estado_gestion` LIKE ? OR `gera_intentos` LIKE ? OR `gera_contactado` LIKE ? OR `gera_efectivo` LIKE ? OR `gera_ultima_observacion` LIKE ? OR `gera_fecha_actualizacion` LIKE ? OR `gera_usuario_gestion` LIKE ? OR TU.`usu_nombres_apellidos` LIKE ? OR `gera_registro_fecha` LIKE ? OR `gera_ultima_observacion` LIKE ? OR `gera_fecha_actualizacion` LIKE ? OR TR.`gere_regional` LIKE ? OR TCZ.`gercz_centro_zonal` LIKE ?)";

        //Contar catidad de variables a filtrar
        $cantidad_filtros=count(explode('?', $filtro_buscar))-1;

        //Agregar catidad de variables a filtrar a data consulta
        for ($i=0; $i < $cantidad_filtros; $i++) { 
            array_push($data_consulta, "%$filtro_permanente%");//Se agrega llave por ser variable evaluada en un like
        }
    }

    if ($perfil_modulo=="Administrador") {
        $filtro_perfil="";
    } elseif($perfil_modulo=="Gestor"){
        $filtro_perfil=" AND (TE.`gem_campania`=?)";
        array_push($data_consulta, $_SESSION["usu_campania"]);
    } elseif($perfil_modulo=="Usuario"){
        $filtro_perfil=" AND (`gera_usuario_gestion`=?)";
        array_push($data_consulta, $_SESSION["usu_id"]);
    }
    
    // Prepara string a ejecutar en sentencia preparada
    $consulta_contar_string="SELECT COUNT(`gera_radicado`) FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TE ON `tb_gestion_encuesta_radicado`.`gera_matriz_id`=TE.`gem_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_encuesta_radicado`.`gera_centro_zonal`=TCZ.`gercz_id` WHERE 1=1 ".$filtro_estado." ".$filtro_buscar." ".$filtro_perfil."";

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
        header('Location:gestion_encuestas.php?pagina=1&id=null&est=Pendientes');
    }

    //Agregar pagina a array data_consulta
    array_push($data_consulta, $iniciar_pagina);
    array_push($data_consulta, $registros_x_pagina);

    $consulta_string="SELECT `gera_radicado`, `gera_matriz_id`, TE.`gem_nombre_encuesta`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_genero`, `gera_motivo`, `gera_regional`, `gera_centro_zonal`, `gera_estado_gestion`, `gera_intentos`, `gera_contactado`, `gera_efectivo`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, `gera_usuario_gestion`, TU.`usu_nombres_apellidos`, `gera_registro_fecha`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, TR.`gere_regional`, TCZ.`gercz_centro_zonal` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TE ON `tb_gestion_encuesta_radicado`.`gera_matriz_id`=TE.`gem_id` LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_encuesta_radicado`.`gera_regional`=TR.`gere_id` LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_encuesta_radicado`.`gera_centro_zonal`=TCZ.`gercz_id` WHERE 1=1 ".$filtro_estado." ".$filtro_buscar." ".$filtro_perfil." ORDER BY `gera_radicado` ASC LIMIT ?,?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_matriz="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha` FROM `tb_gestion_encuestas_matriz` ORDER BY `gem_nombre_encuesta`";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
    <script type="text/javascript">
        <?php for ($i=0; $i < count($resultado_registros); $i++): ?>
            <?php if ($resultado_registros[$i][22]!="" AND $resultado_registros[$i][12]!="Cerrado" AND $resultado_registros[$i][12]!="Cancelado"): ?>
                //tiempo_gestion
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_anio=<?php echo date('Y',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_mes=<?php echo date('m',strtotime($resultado_registros[$i][22]))-1;?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_dia=<?php echo date('d',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_hora=<?php echo date('H',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_minuto=<?php echo date('i',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_segundo=<?php echo date('s',strtotime($resultado_registros[$i][22]));?>;
            <?php else: ?>
                //tiempo_gestion
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_anio=<?php echo date('Y',strtotime($resultado_registros[$i][20]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_mes=<?php echo date('m',strtotime($resultado_registros[$i][20]))-1;?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_dia=<?php echo date('d',strtotime($resultado_registros[$i][20]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_hora=<?php echo date('H',strtotime($resultado_registros[$i][20]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_minuto=<?php echo date('i',strtotime($resultado_registros[$i][20]));?>;
                sessionStorage.inc_<?php echo $i; ?>_tiempo_gestion_segundo=<?php echo date('s',strtotime($resultado_registros[$i][20]));?>;
            <?php endif; ?>

            <?php if ($resultado_registros[$i][12]=="Cerrado" OR $resultado_registros[$i][12]=="Cancelado"): ?>
                //fecha_resuelto cuando está diligenciada
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_anio=<?php echo date('Y',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_mes=<?php echo date('m',strtotime($resultado_registros[$i][22]))-1;?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_dia=<?php echo date('d',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_hora=<?php echo date('H',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_minuto=<?php echo date('i',strtotime($resultado_registros[$i][22]));?>;
                sessionStorage.inc_<?php echo $i; ?>_fecha_resuelto_segundo=<?php echo date('s',strtotime($resultado_registros[$i][22]));?>;
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
                        <a href="gestion_encuestas.php?pagina=1&id=null&est=<?php echo $estado_bandeja; ?>" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                      </span>
                    </div>
                </form>
            </div>
            <div class="col-md-9 py-2">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <a href="gestion_encuestas_base_asignar_usuarios.php" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-exchange-alt"></span></div><div class="pl-2 menu_res float-left">Reasignar</div></a>
                    <a href="gestion_encuestas_base_cargar_usuarios.php" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-upload"></span></div><div class="pl-2 menu_res float-left">Cargar Base</div></a>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-right' data-target="#dataexport"><span class="fas fa-file-excel float-left"></span><div class="pl-2 menu_res d-xs-none float-left">Reportes</div></button>
                <?php endif; ?>
                <a href="gestion_encuestas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Cancelado" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-times-circle"></span></div><div class="pl-2 menu_res float-left">Cancelados</div></a>
                <a href="gestion_encuestas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Cerrado" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-lock"></span></div><div class="pl-2 menu_res float-left">Cerrados</div></a>
                <a href="gestion_encuestas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=Pendientes" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-cogs"></span></div><div class="pl-2 menu_res float-left">Pendientes</div></a>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-right"></a></li>
                            </ul>
                        </nav>
                    </div>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle" style="width: 55px;"></th>
                                    <th class="align-middle">Estado</th>
                                    <th class="align-middle">Contacto</th>
                                    <th class="align-middle">Efectivo</th>
                                    <th class="align-middle">Intentos</th>
                                    <th class="align-middle">Tiempo Gestión</th>
                                    <th class="align-middle">Radicado</th>
                                    <th class="align-middle">Matriz Encuesta</th>
                                    <th class="align-middle">Doc. Peticionario</th>
                                    <th class="align-middle">Nombres y Apellidos</th>
                                    <th class="align-middle">Teléfono</th>
                                    <th class="align-middle">Celular</th>
                                    <th class="align-middle">Regional</th>
                                    <th class="align-middle">Centro Zonal</th>
                                    <th class="align-middle">Observaciones</th>
                                    <th class="align-middle">Usuario Asignado</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <?php if($perfil_modulo!="Visitante"): ?>
                                            <?php if($resultado_registros[$i][12]=="Pendiente"): ?>
                                                <a href="gestion_encuestas_crear_encuesta_generar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>&mat=<?php echo base64_encode($resultado_registros[$i][1]); ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-danger btn-sm btn-width" title="Cargar Encuesta"><span class="fas fa-flag"></span></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <a href="#" onClick="crear_enlace('gestion_encuestas_historial.php?&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>');" class="btn btn-warning btn-sm btn-width" title="Historial"><span class="fas fa-history"></span></a>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][12]=="Pendiente"): ?>
                                            <span class="fas fa-user-clock color-gris" title="Pendiente"></span>
                                        <?php elseif($resultado_registros[$i][12]=="Cerrado"): ?>
                                            <span class="fas fa-user-lock color-verde" title="Cerrado"></span>
                                        <?php elseif($resultado_registros[$i][12]=="Cancelado"): ?>
                                            <span class="fas fa-times-circle color-rojo" title="Cancelado"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][14]==""): ?>
                                            <span class="fas fa-minus-circle color-gris" title="Pendiente"></span>
                                        <?php elseif($resultado_registros[$i][14]==1): ?>
                                            <span class="fas fa-phone color-verde" title="Contactado"></span>
                                        <?php elseif($resultado_registros[$i][14]==0): ?>
                                            <span class="fas fa-phone-slash color-rojo" title="No contactado"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][15]==""): ?>
                                            <span class="fas fa-minus-circle color-gris" title="Pendiente"></span>
                                        <?php elseif($resultado_registros[$i][15]==1): ?>
                                            <span class="fas fa-user-check color-verde" title="Efectivo"></span>
                                        <?php elseif($resultado_registros[$i][15]==0): ?>
                                            <span class="fas fa-user-times color-rojo" title="No efectivo"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center"><?php echo $resultado_registros[$i][13]; ?></td>
                                    <td class="align-middle"><p class="alert alert-success text-center p-1 m-0" id='tiempo_gestion_<?php echo $i; ?>'></p></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][0]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][2]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][4]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][5]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][6]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][7]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][23]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][24]; ?></td>
                                    <td class="align-middle">
                                        <div class="scroll" style="max-width: 200px; max-height: 80px; overflow-y: scroll;">
                                            <?php echo $resultado_registros[$i][21]; ?>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][19]; ?></td>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=1&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_encuestas.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>&est=<?php echo $estado_bandeja; ?>"><span class="fas fa-angle-double-right"></a></li>
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
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
        include("gestion_encuestas_reporte.php");
    ?>
    <script type="text/javascript">
        function crear_enlace(url) {
            params  = 'width='+screen.width*0.8;
            params += ', height='+screen.height*0.8;
            params += ', top=0, left=-100'
            params += ', fullscreen=yes';
            params += ', directories=no';
            params += ', location=no';
            params += ', menubar=no';
            params += ', resizable=no';
            params += ', scrollbars=no';
            params += ', status=no';
            params += ', toolbar=no';
            params += ', titlebar=no';
            window.open(url , '' , params)
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