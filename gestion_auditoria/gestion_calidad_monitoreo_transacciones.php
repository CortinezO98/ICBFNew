<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Auditoría-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/
    $bandeja='Transacciones';
    $titulo_header = "Monitoreos | ".$bandeja;
    $pagina=validar_input($_GET['pagina']);

    // Inicializa variable tipo array
    $data_consulta=array();
    
    // Ejemplo filtro campo buscar
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
        $filtro_buscar="AND (TMC.`gcm_id` LIKE ? OR TM.`gcm_nombre_matriz` LIKE ? OR TUA.`usu_nombres_apellidos` LIKE ? OR TMC.`gcm_fecha_hora_gestion` LIKE ? OR TMC.`gcm_fecha_hora_cierre` LIKE ? OR TMC.`gcm_tipo_monitoreo` LIKE ? OR TMC.`gcm_skill_interaccion` LIKE ? OR TMC.`gcm_tipo_gestion` LIKE ? OR TMC.`gcm_segmento` LIKE ? OR TMC.`gcm_id_sim` LIKE ? OR TMC.`gcm_id_ani` LIKE ? OR TMC.`gcm_observaciones_monitoreo` LIKE ? OR TMC.`gcm_nota_enc` LIKE ? OR TMC.`gcm_nota_ecn` LIKE ? OR TMC.`gcm_nota_ecuf` LIKE ? OR TMC.`gcm_estado` LIKE ? OR TUR.`usu_nombres_apellidos` LIKE ? OR TMC.`gcm_fecha_monitoreo` LIKE ? OR TP.`ap_nombre_piloto` LIKE ? OR TS.`usu_nombres_apellidos` LIKE ? OR TSA.`usu_nombres_apellidos` LIKE ?)";

        //Contar catidad de variables a filtrar
        $cantidad_filtros=count(explode('?', $filtro_buscar))-1;

        //Agregar catidad de variables a filtrar a data consulta
        for ($i=0; $i < $cantidad_filtros; $i++) { 
            array_push($data_consulta, "%$filtro_permanente%");//Se agrega llave por ser variable evaluada en un like
        }
    }

    if ($perfil_modulo=="Administrador") {
        $filtro_perfil="";
    } elseif ($perfil_modulo=="Gestor") {
        $filtro_perfil=" AND TMC.`gcm_aplica_indicador`<>'No-Cliente'";
    } elseif($perfil_modulo=="Supervisor"){
        $filtro_perfil=" AND (TUA.`usu_supervisor`=? OR TMC.`gcm_analista`=? OR TMC.`gcm_registro_usuario`=?)";
        array_push($data_consulta, $_SESSION["usu_id"]);
        array_push($data_consulta, $_SESSION["usu_id"]);
        array_push($data_consulta, $_SESSION["usu_id"]);
    } elseif($perfil_modulo=="Formador"){
        $filtro_perfil=" AND TMC.`gcm_registro_usuario`=?";
        array_push($data_consulta, $_SESSION["usu_id"]);
    } elseif($perfil_modulo=="Usuario"){
        $filtro_perfil=" AND TMC.`gcm_analista`=?";
        array_push($data_consulta, $_SESSION["usu_id"]);
    } elseif($perfil_modulo=="Cliente"){
        $filtro_perfil=" AND TMC.`gcm_aplica_indicador`='No-Cliente'";
    }

    // Prepara string a ejecutar en sentencia preparada
    $consulta_contar_string="SELECT COUNT(TMC.`gcm_id`) FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_auditoria_responsable`=TUR.`usu_id` LEFT JOIN `tb_gestion_auditoria_monitoreo` AS TAU ON TMC.`gcm_id`=TAU.`gcm_id` WHERE 1=1 AND `gcm_auditoria`='Si' AND TAU.`gcm_id` IS NULL ".$filtro_buscar." ".$filtro_perfil."";

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
        header('Location:gestion_calidad_monitoreo_transacciones.php?pagina=1&id=null');
    }

    //Agregar pagina a array data_consulta
    array_push($data_consulta, $iniciar_pagina);
    array_push($data_consulta, $registros_x_pagina);

    $consulta_string="SELECT TMC.`gcm_id`, TM.`gcm_nombre_matriz`, TMC.`gcm_fecha_hora_gestion`, TMC.`gcm_fecha_hora_cierre`, TMC.`gcm_tipo_monitoreo`, TMC.`gcm_skill_interaccion`, TMC.`gcm_tipo_gestion`, TMC.`gcm_segmento`, TMC.`gcm_id_sim`, TMC.`gcm_id_ani`, TMC.`gcm_observaciones_monitoreo`, TMC.`gcm_nota_enc`, TMC.`gcm_nota_ecn`, TMC.`gcm_nota_ecuf`, TMC.`gcm_estado`, TUR.`usu_nombres_apellidos`, TMC.`gcm_registro_fecha`, TMC.`gcm_nota_general`, TMC.`gcm_nota_enc_estado`, TMC.`gcm_nota_ecn_estado`, TMC.`gcm_nota_ecuf_estado`, TMC.`gcm_aplica_indicador`, TMC.`gcm_fecha_monitoreo`, TMC.`gcm_registro_usuario`, TM.`gcm_observaciones`, `gcm_auditoria`, `gcm_auditoria_responsable`, TAU.`gcm_id` FROM `tb_gestion_calidad_monitoreo` AS TMC LEFT JOIN `tb_gestion_calidad_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_auditoria_responsable`=TUR.`usu_id` LEFT JOIN `tb_gestion_auditoria_monitoreo` AS TAU ON TMC.`gcm_id`=TAU.`gcm_id` WHERE 1=1 AND `gcm_auditoria`='Si' AND TAU.`gcm_id` IS NULL ".$filtro_buscar." ".$filtro_perfil." ORDER BY TMC.`gcm_id` ASC LIMIT ?,?";

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
            <div class="col-md-3 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                      <input type="text" name="id_filtro" value='<?php if (isset($_POST["filtro"])) { echo $_POST['id_filtro']; } else {if($filtro_permanente!="null"){echo $filtro_permanente;}} ?>' placeholder="Búsqueda" class="form-control" required autofocus>
                      <span class="input-group-btn">
                        <button class="btn btn-corp" type="submit" name="filtro"><span class="fas fa-search"></span></button>
                        <a href="gestion_calidad_monitoreo_transacciones.php?pagina=1&id=null" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                      </span>
                    </div>
                </form>
            </div>
            <div class="col-md-9 py-2">
                <!-- <a href="gestion_calidad_monitoreo.php?pagina=1&id=<?php echo $filtro_permanente; ?>&bandeja=<?php echo base64_encode('Histórico'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-history"></span></div><div class="pl-2 menu_res float-left">Monitoreos Histórico</div></a> -->
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Cliente"): ?>
                    <!-- <a href="gestion_calidad_monitoreo.php?pagina=1&id=<?php echo $filtro_permanente; ?>&bandeja=<?php echo base64_encode('Refutados'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-user-times"></span></div><div class="pl-2 menu_res float-left">Refutados</div></a> -->
                <?php endif; ?>
                    <a href="gestion_calidad_monitoreo.php?pagina=1&id=<?php echo $filtro_permanente; ?>&bandeja=<?php echo base64_encode('Mes Actual'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-clipboard-list"></span></div><div class="pl-2 menu_res float-left">Monitoreos Mes Actual</div></a>
                <?php if($perfil_modulo=="Administrador"): ?>
                    <a href="gestion_calidad_monitoreo_transacciones_asignar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-user-cog"></span></div><div class="pl-2 menu_res float-left">Transacciones-Asignar</div></a>
                <?php endif; ?>
                <a href="gestion_calidad_monitoreo_transacciones.php?pagina=1&id=null" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-qrcode"></span></div><div class="pl-2 menu_res float-left">Transacciones</div></a>
                <?php if($perfil_modulo=="Administrador"): ?>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-right' data-target="#dataexport"><span class="fas fa-file-excel float-left"></span><div class="pl-2 menu_res d-xs-none float-left">Reportes</div></button>
                <?php endif; ?>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=1&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-right"></a></li>
                            </ul>
                        </nav>
                    </div>
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
                                    <th class="align-middle">Id SIM</th>
                                    <th class="align-middle">Id/ANI</th>
                                    <th class="align-middle">Duración</th>
                                    <th class="align-middle">Fecha Gestión</th>
                                    <th class="align-middle">Fecha Monitoreo</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][0]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][1]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][15]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][5]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][6]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][7]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][8]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][9]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][3]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][2]; ?></td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][22]; ?></td>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=1&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_calidad_monitoreo_transacciones.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-right"></a></li>
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
        <!-- Modal MONITOREO -->
        <div class="modal fade" id="modal_ver_monitoreo" role="dialog">
            <div class="modal-dialog mw-100 w-100">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detalle monitoreo</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_monitoreo();">×</button>
                    </div>
                    <div class="modal-body modal-body-monitoreo p-1">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_monitoreo();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
        include("gestion_calidad_monitoreo_reporte.php");
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#modal_ver_monitoreo").on('hidden.bs.modal', function () {
                close_modal_monitoreo();
            });
        });
        
        function open_modal_monitoreo(id_registro) {
            $('.modal-body-monitoreo').load('gestion_calidad_monitoreo_ver.php?reg='+id_registro,function(){
                $('#modal_ver_monitoreo').modal({show:true});
            });
        }

        function close_modal_monitoreo() {
            $('.modal-body-monitoreo').html('');
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