<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas-Configuración";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
    $estado_bandeja=validar_input($_GET['est']);
    $titulo_header = "Gestión Alertas - Configuración | Regional";
    $pagina=validar_input($_GET['pagina']);

    unset($_SESSION['gestion_alerta_valida_n1']);
    unset($_SESSION['gestion_alerta_valida_n2']);

    // Inicializa variable tipo array
    $data_consulta=array();
    
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
        $filtro_buscar="AND (`gere_regional` LIKE ? OR `gere_id_mapa` LIKE ? OR `gere_correos` LIKE ? OR `gere_correos_trimestral` LIKE ?)";

        //Contar catidad de variables a filtrar
        $cantidad_filtros=count(explode('?', $filtro_buscar))-1;

        //Agregar catidad de variables a filtrar a data consulta
        for ($i=0; $i < $cantidad_filtros; $i++) { 
            array_push($data_consulta, "%$filtro_permanente%");//Se agrega llave por ser variable evaluada en un like
        }
    }

    // Prepara string a ejecutar en sentencia preparada
    $consulta_contar_string="SELECT COUNT(`gere_id`) FROM `tb_gestion_encuesta_regional` WHERE 1=1 ".$filtro_buscar."";

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
        header('Location:gestion_alertas_regional.php?pagina=1&id=null');
    }

    //Agregar pagina a array data_consulta
    array_push($data_consulta, $iniciar_pagina);
    array_push($data_consulta, $registros_x_pagina);

    $consulta_string="SELECT `gere_id`, `gere_regional`, `gere_id_mapa`, `gere_correos`, `gere_correos_trimestral`, `gere_registro_fecha` FROM `tb_gestion_encuesta_regional` WHERE 1=1 ".$filtro_buscar." ORDER BY `gere_regional` ASC LIMIT ?,?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    if (count($data_consulta)>0) {
        $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
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
<body>
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
                        <a href="gestion_alertas_regional.php?pagina=1&id=null" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                      </span>
                    </div>
                </form>
            </div>
            <div class="col-md-9 py-2">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <a href="gestion_alertas_destinatarios.php?pagina=1&id=null" class="btn btn-corp menu float-right" title="Destinatarios"><span class="fas fa-envelope"></span> Destinatarios</a>
                    <a href="gestion_alertas_czonales.php?pagina=1&id=null" class="btn btn-corp menu float-right" title="Centro Zonal"><span class="fas fa-map-marker-alt"></span> Centro Zonal</a>
                    <a href="gestion_alertas_regional.php?pagina=1&id=null" class="btn btn-corp menu float-right" title="Regional"><span class="fas fa-map-marked-alt"></span> Regional</a>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=1&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-right"></a></li>
                            </ul>
                        </nav>
                    </div>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle" style="width: 55px;"></th>
                                    <th class="align-middle">Regional</th>
                                    <th class="align-middle">Correos</th>
                                    <th class="align-middle">Correos Informes</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($i=0; $i < count($resultado_registros); $i++) { 
                                ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <?php if($perfil_modulo=="Administrador"): ?>
                                            <a href="gestion_alertas_regional_editar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($resultado_registros[$i][0]); ?>" class="btn btn-primary btn-sm btn-width" title="Editar"><span class="fas fa-pen"></span></a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo $resultado_registros[$i][1]; ?></td>
                                    <td class="align-middle"><?php echo (strlen($resultado_registros[$i][3])>80) ? substr($resultado_registros[$i][3], 0, 80)."..." : $resultado_registros[$i][3]; ?></td>
                                    <td class="align-middle"><?php echo (strlen($resultado_registros[$i][4])>80) ? substr($resultado_registros[$i][4], 0, 80)."..." : $resultado_registros[$i][4]; ?></td>
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
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=1&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-left"></span></a></li>
                                <li class="page-item <?php echo $pagina<=1 ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $pagina-1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-left"></span></a></li>
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
                                    <li class="page-item <?php echo $pagina==$i ? 'active':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $i; ?>&id=<?php echo $filtro_permanente; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $pagina+1; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-right"></a></li>
                                <li class="page-item <?php echo $pagina>=$numero_paginas ? 'disabled':'' ?>"><a class="page-link" href="gestion_alertas_regional.php?pagina=<?php echo $numero_paginas; ?>&id=<?php echo $filtro_permanente; ?>"><span class="fas fa-angle-double-right"></a></li>
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
        include("gestion_alertas_informe.php");
        include("gestion_alertas_reporte.php");
    ?>
    <script type="text/javascript">
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