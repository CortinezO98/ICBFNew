<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Matriz Calidad";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz de Calidad | Configurar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    
    unset($_SESSION['gcmc_registro_creado']);
    unset($_SESSION['gcmc_registro_eliminado']);
    // CAST(`gcmi_item_consecutivo` AS UNSIGNED), 
    $consulta_string="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id`, `gcmi_nota_maxima`, `gcmi_nota_sla` FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_matriz`=? ORDER BY `gcmi_item_consecutivo` ASC";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros); $i++) { 
        if ($resultado_registros[$i][2]=="Grupo") {
            $array_grupos[]=$resultado_registros[$i][0];
        }
        if ($resultado_registros[$i][2]=="Sub-Grupo") {
            $array_grupos_sub[$resultado_registros[$i][11]][]=$resultado_registros[$i][0];
        }
        if ($resultado_registros[$i][2]=="Item") {
            $array_item[$resultado_registros[$i][11]][$resultado_registros[$i][12]][]=$resultado_registros[$i][0];
        }
        if ($resultado_registros[$i][2]=="Sub-Item") {
            $array_item_sub[$resultado_registros[$i][11]][$resultado_registros[$i][12]][$resultado_registros[$i][13]][]=$resultado_registros[$i][0];
        }
    }

    $consulta_string_matriz="SELECT `gcm_id`, `gcm_nombre_matriz`, `gcm_estado`, `gcm_observaciones`, `gcm_registro_usuario`, `gcm_registro_fecha` FROM `tb_gestion_calidad_matriz` WHERE `gcm_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $id_registro);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_conteo_monitoreos="SELECT COUNT(`gcm_id`) FROM `tb_gestion_calidad_monitoreo` WHERE `gcm_matriz`=?";

    $consulta_registros_conteo_monitoreos = $enlace_db->prepare($consulta_string_conteo_monitoreos);
    $consulta_registros_conteo_monitoreos->bind_param("s", $id_registro);
    $consulta_registros_conteo_monitoreos->execute();
    $resultado_registros_conteo_monitoreos = $consulta_registros_conteo_monitoreos->get_result()->fetch_all(MYSQLI_NUM);
    
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
                
            </div>
            <div class="col-md-9 py-2">
                <?php if(($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor") AND $resultado_registros_conteo_monitoreos[0][0]==0): ?>
                    <a href="gestion_calidad_matriz_configurar_crear.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&tipo=<?php echo base64_encode('Grupo'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-plus"></span></div><div class="pl-2 menu_res float-left">Crear Grupo</div></a>
                <?php endif; ?>
                <a href="gestion_calidad_matriz.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-arrow-left"></span></div><div class="pl-2 menu_res float-left">Regresar</div></a>
            </div>
        </div>
        <div class="row" id="tabla_fixed">
            <div class="col-md-12">
                <?php
                    echo "<pre>";
                    // print_r($array_grupos);
                    // print_r($array_grupos_sub);
                    // print_r($array_item);
                    // print_r($array_item_sub);
                    // print_r($array_matriz_detalle);
                    echo "</pre>";
                ?>
                <?php if (count($resultado_registros)>0): ?>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <?php if ($resultado_registros_conteo_monitoreos[0][0]>0): ?>
                        <p class="alert alert-warning p-1">¡No es posible modificar la estructura debido a que ya se han cargado monitoreos realizados!</p>
                        <?php endif; ?>
                        <p class="alert alert-success p-1 text-center">Matriz: <?php echo $resultado_registros_matriz[0][1]; ?> | Estado: <?php echo $resultado_registros_matriz[0][2]; ?></p>
                        <p class="alert alert-warning p-1" style="font-size: 12px;">Por favor tener en cuenta la siguiente información para una correcta configuración:<br>
                        <span class="fas fa-check-square"> Cada matriz puede tener como máximo 3 grupos preconfigurados (ENC, ECU y ECN).</span><br>
                        <span class="fas fa-check-square"> El orden de visualización de cada item está dado por el campo "Consecutivo".</span><br>
                        <span class="fas fa-check-square"> El campo "Visible" permite que un item sea visible o no al momento de crear un monitoreo.</span><br>
                        <span class="fas fa-check-square"> El campo "Calificable" permite que un item tenga o no, check para calificación al momento de crear un monitoreo.</span><br>
                        <span class="fas fa-check-square"> El campo "Grupo Calificación" permite agrupar items para que el cálculo de la nota sea agrupada, es decir, que si uno de los items del grupo no se cumple, la nota del grupo será 0 (Cero).</span><br>
                        <span class="fas fa-check-square"> El campo "Peso" permite asignar distribución de porcentaje para cada item, proporcional al peso que debe tener sobre la calificación del grupo al que pertenece.</span>
                        </p>
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle" style="width: 80px;">Acciones</th>
                                    <th class="align-middle" style="width: 100px;">Consecutivo</th>
                                    <th class="align-middle" style="width: 50px;">Visible</th>
                                    <th class="align-middle">Atributos de Evaluación</th>
                                    <th class="align-middle" style="width: 100px;">Calificable</th>
                                    <th class="align-middle" style="width: 100px;">Grupo Calificación</th>
                                    <th class="align-middle" style="width: 100px;">Peso / SLA</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle">
                                        <?php if(($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor") AND $resultado_registros_conteo_monitoreos[0][0]==0): ?>
                                            <?php if($resultado_registros[$i][2]!="Sub-Item"): 
                                                if ($resultado_registros[$i][2]=="Grupo") {
                                                    $tipo_item_agregar="Sub-Grupo";
                                                } elseif ($resultado_registros[$i][2]=="Sub-Grupo") {
                                                    $tipo_item_agregar="Item";
                                                } elseif ($resultado_registros[$i][2]=="Item") {
                                                    $tipo_item_agregar="Sub-Item";
                                                }
                                            ?>
                                                <a href="gestion_calidad_matriz_configurar_crear.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&tipo=<?php echo base64_encode($tipo_item_agregar); ?>&tierr=<?php echo base64_encode($resultado_registros[$i][10]); ?>&idg_grupo=<?php echo base64_encode($resultado_registros[$i][11]); ?>&idg_subgrupo=<?php echo base64_encode($resultado_registros[$i][12]); ?>&idg_item=<?php echo base64_encode($resultado_registros[$i][13]); ?>" class="btn btn-success btn-sm btn-width" title="Agregar Item"><span class="fas fa-plus"></span></a>
                                            <?php endif; ?>

                                            <?php if($resultado_registros[$i][2]!="Grupo"): ?>
                                                <a href="gestion_calidad_matriz_configurar_editar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&tipo=<?php echo base64_encode($resultado_registros[$i][2]); ?>&item=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-warning btn-sm btn-width" title="Editar"><span class="fas fa-pen"></span></a>
                                            <?php endif; ?>
                                            <?php if($resultado_registros[$i][2]=="Grupo" AND count($array_grupos_sub[$resultado_registros[$i][11]])==0): 
                                                    $mostrar_eliminar=1;
                                                elseif($resultado_registros[$i][2]=="Sub-Grupo" AND count($array_item[$resultado_registros[$i][11]][$resultado_registros[$i][12]])==0):
                                                    $mostrar_eliminar=1;
                                                elseif($resultado_registros[$i][2]=="Item" AND count($array_item_sub[$resultado_registros[$i][11]][$resultado_registros[$i][12]][$resultado_registros[$i][13]])==0):
                                                    $mostrar_eliminar=1;
                                                elseif($resultado_registros[$i][2]=="Sub-Item"):
                                                    $mostrar_eliminar=1;
                                                else:
                                                    $mostrar_eliminar=0;
                                                endif;
                                            ?>
                                            <?php if($mostrar_eliminar==1): ?>
                                            <a href="gestion_calidad_matriz_configurar_eliminar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&tipo=<?php echo base64_encode($resultado_registros[$i][2]); ?>&item=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-danger btn-sm btn-width" title="Eliminar"><span class="fas fa-trash-alt"></span></a>
                                            <?php endif; ?>
                                            
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle <?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?>"><?php echo $resultado_registros[$i][3]; ?></td>
                                    <td class="align-middle <?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?>"><?php echo $resultado_registros[$i][9]; ?></td>
                                    <td class="align-middle <?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?>"><?php echo $resultado_registros[$i][5]; ?></td>
                                    <td class="align-middle <?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?> text-center"><?php echo $resultado_registros[$i][7]; ?></td>
                                    <td class="align-middle <?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?> text-center"><?php echo ($resultado_registros[$i][8]!="") ? 'G-'.$resultado_registros[$i][8] : ''; ?></td>
                                    <td class="align-middle <?php if($resultado_registros[$i][2]=='Grupo'){echo'matriz-grupo';} elseif($resultado_registros[$i][2]=='Sub-Grupo'){echo'matriz-grupo-sub';} elseif($resultado_registros[$i][2]=='Item'){echo'matriz-item';}?> text-center"><?php echo $resultado_registros[$i][6]; ?>% <?php if($resultado_registros[$i][2]=='Grupo') { echo '/ SLA-'.$resultado_registros[$i][16].'%'; } ?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
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
    ?>
    <script type="text/javascript">
        function tabla_fixed(){
            var tabla_fixed = document.getElementById("tabla_fixed");
            var elemento = document.getElementById("table-fixed");
            var elemento_1 = document.getElementById("elemento_1");
            alto_ventana=window.outerHeight-elemento_1.clientHeight-100;
            alto_tabla=tabla_fixed.clientHeight+200;

            if (alto_tabla>alto_ventana) {
                alto_elemento=alto_ventana-120;
                elemento.style.height=alto_elemento+"px";
            }
        }
    </script>
</body>
</html>