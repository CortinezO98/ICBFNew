<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos-Malla";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("../config/validar_festivos.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Gestión Turnos | Configuración Recargos";
    $pagina=validar_input($_GET['pagina']);

    unset($_SESSION['mturno_novedad_crear']);

    // Inicializa variable tipo array
    $data_consulta=array();
    $data_consulta_supervisor=array();
    
    // Ejemplo filtro campo buscar
    if (isset($_POST["filtro"])) {
        $filtro_permanente=validar_input($_POST['id_filtro']);
        $FechaInicio = $_POST["filtro_fecha"];
    } else {
        $filtro_permanente=validar_input($_GET['id']);
        $FechaInicio = base64_decode($_GET['fechainicio']);
    }

    array_push($data_consulta, $FechaInicio);

    // Configuracón Paginación
    $registros_x_pagina=50;
    $iniciar_pagina=($pagina-1)*$registros_x_pagina;

    // Valida que filtro se deba ejecutar
    if ($filtro_permanente!="null" AND $filtro_permanente!="") {
        $filtro_buscar="AND (`ctmcr_usuario` LIKE ? OR TU.`usu_nombres_apellidos` LIKE ? OR TC.`ac_nombre_campania` LIKE ?)";

        //Contar catidad de variables a filtrar
        $cantidad_filtros=count(explode('?', $filtro_buscar))-1;

        //Agregar catidad de variables a filtrar a data consulta
        for ($i=0; $i < $cantidad_filtros; $i++) { 
            array_push($data_consulta, "%$filtro_permanente%");//Se agrega llave por ser variable evaluada en un like
        }
    }

    // Prepara string a ejecutar en sentencia preparada
    $consulta_contar_string="SELECT COUNT(`ctmcr_id`) FROM `tb_control_turno_malla_configuracion_recargos` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` WHERE `ctmcr_mes`=? ".$filtro_buscar."";

    // Agrega string a sentencia preparada
    $consulta_contar_registros = $enlace_db->prepare($consulta_contar_string);
    
    if (count($data_consulta)>0) {
        // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
        $consulta_contar_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    // Ejecuta sentencia preparada
    $consulta_contar_registros->execute();
    // Obtiene array resultado de ejecución sentencia preparada
    $resultado_registros_contar = $consulta_contar_registros->get_result()->fetch_all(MYSQLI_NUM);
    $registros_cantidad_total = $resultado_registros_contar[0][0];
    //Cálculo número de páginas 
    $numero_paginas=ceil($registros_cantidad_total/$registros_x_pagina);

    if (!isset($_GET['pagina']) || ($pagina>$numero_paginas AND $numero_paginas>0) || $pagina<=0) {
        header('Location:malla_turnos_configuracion_recargos.php?pagina=1&id=null');
    }

    //Agregar pagina a array data_consulta
    array_push($data_consulta, $iniciar_pagina);
    array_push($data_consulta, $registros_x_pagina);

    $consulta_string="SELECT `ctmcr_id`, `ctmcr_mes`, `ctmcr_usuario`, `ctmcr_registro_usuario`, `ctmcr_registro_fecha`, TU.`usu_nombres_apellidos`, TUR.`usu_nombres_apellidos`, TC.`ac_nombre_campania` FROM `tb_control_turno_malla_configuracion_recargos` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` WHERE `ctmcr_mes`=? ".$filtro_buscar." ORDER BY TU.`usu_nombres_apellidos` LIMIT ?,?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
</head>
<body onresize="tabla_fixed();" onload="tabla_fixed();">
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row" id="elemento_1">
            <div class="col-md-5 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                        <input type="month" class="form-control form-control-sm" name="filtro_fecha" value='<?php echo $FechaInicio; ?>' placeholder="Búsqueda" class="form-control" required autofocus>
                        <input type="text" class="form-control form-control-sm" name="id_filtro" value='<?php if (isset($_POST["filtro"])) { echo $_POST['id_filtro']; } else {if($filtro_permanente!="null"){echo $filtro_permanente;}} ?>' placeholder="Búsqueda" class="form-control">
                        <span class="input-group-btn">
                            <button class="btn btn-corp" type="submit" name="filtro"><span class="fas fa-search"></span></button>
                            <a href="malla_turnos_configuracion_recargos.php?pagina=1&id=null&fechainicio=<?php echo base64_encode(date('Y-m'));?>&operacion=<?php echo base64_encode('Todas'); ?>" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                        </span>
                    </div>
                </form>
            </div>
            <div class="col-md-7 py-2">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-right' data-target="#dataexport"><span class="fas fa-file-excel float-left"></span></button>
                <?php endif; ?>
                <a href="malla_turnos_cambio.php?pagina=1&id=null&fechainicio=<?php echo base64_encode(date('Y-m'));?>&operacion=<?php echo base64_encode('Todas'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-retweet"></span></div><div class="pl-2 menu_res float-left">Cambio Turno</div></a>
                <a href="malla_turnos_novedades.php?pagina=1&id=null&fechainicio=<?php echo base64_encode(date('Y-m'));?>&operacion=<?php echo base64_encode('Todas'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-bell"></span></div><div class="pl-2 menu_res float-left">Novedades</div></a>
                <a href="malla_turnos.php?fechainicio=<?php echo base64_encode(date('Y')."-W".date('W'));?>&operacion=<?php echo base64_encode('Todas'); ?>&id=null" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-calendar-week"></span></div><div class="pl-2 menu_res float-left">Malla Turnos</div></a>
                <a href="malla_turnos_configuracion_recargos_editar.php?pagina=1&id=null&fechainicio=<?php echo base64_encode($FechaInicio);?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-pen"></span></div><div class="pl-2 menu_res float-left">Configurar Recargos</div></a>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=1&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-double-right"></a></li>
                            </ul>
                        </nav>
                    </div>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle" style="width: 55px;"></th>
                                    <th class="align-middle">Mes</th>
                                    <th class="align-middle">Doc. Usuario</th>
                                    <th class="align-middle">Nombres y Apellidos</th>
                                    <th class="align-middle">Campaña</th>
                                    <th class="align-middle">Usuario Registro</th>
                                    <th class="align-middle">Fecha Registro</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <?php if($resultado_registros[$i][11]!=""): ?>
                                            <a href="#" onClick="open_modal_detalle('<?php echo base64_encode($resultado_registros[$i][0]); ?>');" class="btn btn-secondary btn-sm btn-width mb-1" title="Soporte"><span class="fas fa-file-alt"></span></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][1]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][2]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][5]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][7]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][6]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][4]; ?></td>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=1&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="malla_turnos_configuracion_recargos.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>&fechainicio=<?php echo base64_encode($FechaInicio); ?>&operacion=<?php echo base64_encode($filtro_operacion); ?>"><span class="fas fa-angle-double-right"></a></li>
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
        <!-- Modal detalle -->
        <div class="modal fade" id="modal_ver_detalle" role="dialog">
            <div class="modal-dialog mw-100 w-100">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Soporte</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_detalle();">×</button>
                    </div>
                    <div class="modal-body modal-body-detalle p-1">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_detalle();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
        include("malla_turnos_novedades_reporte.php");
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#modal_ver_detalle").on('hidden.bs.modal', function () {
                close_modal_detalle();
            });
        });
        
        function open_modal_detalle(id_registro) {
            $('.modal-body-detalle').load('malla_turnos_novedades_ver.php?reg='+id_registro,function(){
                $('#modal_ver_detalle').modal({show:true});
            });
        }

        function close_modal_detalle() {
            $('.modal-body-detalle').html('');
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
    </script>
</body>
</html>