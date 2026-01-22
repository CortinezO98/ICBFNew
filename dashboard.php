<?php
    require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    require_once("config/conexion_db.php");

    //agregar cero a números de un dígito
    function validarCeros($num){
        if (iconv_strlen($num)==1) {
            return "0" . $num;
        } else{
            return $num;
        }
    }

    $filtro_dashboard=validar_input(base64_decode($_GET['reg']));

    if (!isset($_SESSION[$filtro_dashboard]['filtro_anio_mes'])) {
        $_SESSION[$filtro_dashboard]['filtro_anio_mes']=array();
    }

    if (!isset($_SESSION[$filtro_dashboard]['filtro_regional'])) {
        $_SESSION[$filtro_dashboard]['filtro_regional']=array();
    }

    if (!isset($_SESSION[$filtro_dashboard]['filtro_centro_zonal'])) {
        $_SESSION[$filtro_dashboard]['filtro_centro_zonal']=array();
    }

    if (count($_SESSION[$filtro_dashboard]['filtro_anio_mes'])==0) {
        $_SESSION[$filtro_dashboard]['filtro_anio_mes'][]=date('Y-m');
    }

    $array_meses=[1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];

    $consulta_dashboard = $enlace_db->prepare("SELECT `gd_id`, `gd_nombre`, `gd_matriz`, `gd_campania`, `gd_orden`, `gd_estado`, `gd_registro_usuario`, `gd_registro_fecha`, TM.`gem_nombre_encuesta`, TU.`usu_nombres_apellidos` FROM `tb_gestion_dashboard` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_dashboard`.`gd_matriz`=TM.`gem_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_dashboard`.`gd_registro_usuario`=TU.`usu_id` WHERE `gd_id`=?");
    $consulta_dashboard->bind_param("s", $filtro_dashboard);
    $consulta_dashboard->execute();
    $resultado_dashboard = $consulta_dashboard->get_result()->fetch_all(MYSQLI_NUM);

    //VALIDACIÓN FILTROS
        if(isset($_POST["aplicar_filtro"])){
            $filtro_anio_mes=validar_input($_POST['filtro_anio_mes']);
            $filtro_regional=validar_input($_POST['filtro_regional']);
            $filtro_centro_zonal=validar_input($_POST['filtro_centro_zonal']);

            if ($filtro_anio_mes!="") {
                $_SESSION[$filtro_dashboard]['filtro_anio_mes'][]=$filtro_anio_mes;
            }

            if ($filtro_regional!="") {
                $_SESSION[$filtro_dashboard]['filtro_regional'][]=$filtro_regional;
            }

            if ($filtro_centro_zonal!="") {
                $_SESSION[$filtro_dashboard]['filtro_centro_zonal'][]=$filtro_centro_zonal;
            }
            $_SESSION[$filtro_dashboard]['filtro_anio_mes']=array_values(array_unique($_SESSION[$filtro_dashboard]['filtro_anio_mes']));
            $_SESSION[$filtro_dashboard]['filtro_regional']=array_values(array_unique($_SESSION[$filtro_dashboard]['filtro_regional']));
            $_SESSION[$filtro_dashboard]['filtro_centro_zonal']=array_values(array_unique($_SESSION[$filtro_dashboard]['filtro_centro_zonal']));
            
            header('Location:dashboard.php?reg='.base64_encode($filtro_dashboard));
        }

        if ($_GET['eliminar']=="si") {
            if ($_GET['id_eliminar_anio_mes']!="") {
                unset($_SESSION[$filtro_dashboard]['filtro_anio_mes'][$_GET['id_eliminar_anio_mes']]);
            }

            if ($_GET['id_eliminar_regional']!="") {
                unset($_SESSION[$filtro_dashboard]['filtro_regional'][$_GET['id_eliminar_regional']]);
            }

            if ($_GET['id_eliminar_centro_zonal']!="") {
                unset($_SESSION[$filtro_dashboard]['filtro_centro_zonal'][$_GET['id_eliminar_centro_zonal']]);
            }

            $_SESSION[$filtro_dashboard]['filtro_anio_mes']=array_values(array_unique($_SESSION[$filtro_dashboard]['filtro_anio_mes']));
            $_SESSION[$filtro_dashboard]['filtro_regional']=array_values(array_unique($_SESSION[$filtro_dashboard]['filtro_regional']));
            $_SESSION[$filtro_dashboard]['filtro_centro_zonal']=array_values(array_unique($_SESSION[$filtro_dashboard]['filtro_centro_zonal']));

            header('Location:dashboard.php?reg='.base64_encode($filtro_dashboard));
        }

        if ($_GET['eliminar']=="all") {
            unset($_SESSION[$filtro_dashboard]['filtro_anio_mes']);
            unset($_SESSION[$filtro_dashboard]['filtro_regional']);
            unset($_SESSION[$filtro_dashboard]['filtro_centro_zonal']);
            
            header('Location:dashboard.php?reg='.base64_encode($filtro_dashboard));
        }
    //VALIDACIÓN FILTROS

    $data_consulta=array();
    $data_consulta_regional=array();

    //CONSULTA MATRIZ
        $consulta_anio_filtro = $enlace_db->prepare("SELECT DISTINCT DATE_FORMAT(`gera_registro_fecha`, '%Y-%m') AS anio_mes FROM `tb_gestion_encuesta_radicado` WHERE `gera_matriz_id`=? ORDER BY anio_mes DESC");
        $consulta_anio_filtro->bind_param("s", $resultado_dashboard[0][2]);
        $consulta_anio_filtro->execute();
        $resultado_anio_filtro = $consulta_anio_filtro->get_result()->fetch_all(MYSQLI_NUM);
        
        if (count($_SESSION[$filtro_dashboard]['filtro_anio_mes'])>0) {
            $consulta_regional = $enlace_db->prepare("SELECT `gere_id`, `gere_regional`, `gere_id_mapa`, `gere_registro_fecha` FROM `tb_gestion_encuesta_regional` ORDER BY `gere_regional` ASC");
            $consulta_regional->execute();
            $resultado_regional = $consulta_regional->get_result()->fetch_all(MYSQLI_NUM);

            for ($i=0; $i < count($resultado_regional); $i++) {
                $array_nombre_regional[$resultado_regional[$i][0]]=$resultado_regional[$i][1];
            }

            if (count($_SESSION[$filtro_dashboard]['filtro_regional'])>0) {
                $string_filtro_regional="AND (";
                for ($i=0; $i < count($_SESSION[$filtro_dashboard]['filtro_regional']); $i++) { 
                    $string_filtro_regional.="`gercz_regional`=? OR ";
                    array_push($data_consulta_regional, $_SESSION[$filtro_dashboard]['filtro_regional'][$i]);
                }
                $string_filtro_regional=substr($string_filtro_regional, 0, -4).")";

                $consulta_centro_zonal = $enlace_db->prepare("SELECT `gercz_id`, `gercz_regional`, `gercz_centro_zonal`, `gercz_registro_fecha` FROM `tb_gestion_encuesta_regional_czonal` WHERE 1=1 ".$string_filtro_regional." ORDER BY `gercz_centro_zonal` ASC");
                $consulta_centro_zonal->bind_param(str_repeat("s", count($data_consulta_regional)), ...$data_consulta_regional);
                $consulta_centro_zonal->execute();
                $resultado_centro_zonal = $consulta_centro_zonal->get_result()->fetch_all(MYSQLI_NUM);

                for ($i=0; $i < count($resultado_centro_zonal); $i++) {
                    $array_nombre_centro_zonal[$resultado_centro_zonal[$i][0]]=$resultado_centro_zonal[$i][2];
                }
            }
        }
    //CONSULTA MATRIZ
    
    $consulta_grupos = $enlace_db->prepare("SELECT `gdg_id`, `gdg_dashboard`, `gdg_grupo_nombre`, `gdg_grupo_descripcion` FROM `tb_gestion_dashboard_grupos` WHERE `gdg_dashboard`=? ORDER BY `gdg_orden` ASC");
    $consulta_grupos->bind_param("s", $resultado_dashboard[0][0]);
    $consulta_grupos->execute();
    $resultado_grupos = $consulta_grupos->get_result()->fetch_all(MYSQLI_NUM);
    
    $consulta_graficas = $enlace_db->prepare("SELECT `gdgg_id`, `gdgg_dashboard`, `gdgg_grupo`, `gdgg_tipo_grafica`, `gdgg_nombre`, `gdgg_variable`, `gdgg_orden`, `gdgg_ancho` FROM `tb_gestion_dashboard_grupos_graficas` LEFT JOIN `tb_gestion_dashboard_grupos` AS TG ON `tb_gestion_dashboard_grupos_graficas`.`gdgg_grupo`=TG.`gdg_id` WHERE `gdgg_dashboard`=? ORDER BY `gdgg_orden` ASC");
    $consulta_graficas->bind_param("s", $filtro_dashboard);
    $consulta_graficas->execute();
    $resultado_graficas = $consulta_graficas->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_graficas); $i++) { 
        $array_grupos_graficas[$resultado_graficas[$i][2]][$resultado_graficas[$i][0]]['orden']=$resultado_graficas[$i][6];
        $array_grupos_graficas[$resultado_graficas[$i][2]][$resultado_graficas[$i][0]]['tipo']=$resultado_graficas[$i][3];
        $array_grupos_graficas[$resultado_graficas[$i][2]][$resultado_graficas[$i][0]]['nombre']=$resultado_graficas[$i][4];
        $array_grupos_graficas[$resultado_graficas[$i][2]][$resultado_graficas[$i][0]]['variable']=$resultado_graficas[$i][5];
        $array_grupos_graficas[$resultado_graficas[$i][2]][$resultado_graficas[$i][0]]['ancho']=$resultado_graficas[$i][7];

        $array_grupos_graficas_lista[$resultado_graficas[$i][2]][]=$resultado_graficas[$i][0];
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Dashboard | Visor";
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php
        include("config/configuracion_estilos_raiz.php");
        include("config/configuracion_calendario_raiz.php");
    ?>
    <script src="Highcharts-Maps/code/highcharts.js"></script>
    <script src="Highcharts-Maps/code/modules/map.js"></script>
    <script src="https://code.highcharts.com/mapdata/countries/co/co-all.js"></script>
    <script src="anychart/js/anychart-core.min.js" type="text/javascript"></script>
    <script src="anychart/js/anychart-bundle.min.js" type="text/javascript"></script>
    <script src="anychart/js/anychart-base.min.js" type="text/javascript"></script>
</head>
<body>
    <?php
        $menu_principal=includeFileContent("menu_principal.php");
        echo str_replace('../', '', $menu_principal);
        include("menu_header_raiz.php");
    ?>
    <div class="contenido" style="background-color: #EEEEEE;">
        <div class="row">
            <div class="col-md-12 py-0 px-0 mb-0 mt-0 fondo-gris border-top-gris">
                <div class="col-md-12 color-blanco titulo-dashboard text-uppercase"><img src="images/img_dashboard.svg" class="img-fluid" style="width: 40px;"> <?php echo $resultado_dashboard[0][1]; ?></div>
                <div class="col-md-12 color-blanco detalle-dashboard"><span class="fas fa-tasks"></span> <?php echo $resultado_dashboard[0][8]; ?></div>
            </div>
            <div class="col-md-12 py-1 px-1">
                <button type="button" data-toggle="modal" class='btn btn-corp menu float-left' data-target="#datafilter_anio_mes" title="Año-Mes"><span class="fas fa-calendar-alt float-left"></span><div class="ml-1 float-left">Año-Mes</div></button>
                <?php if (count($_SESSION[$filtro_dashboard]['filtro_anio_mes'])>0): ?>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-left' data-target="#datafilter_regional" title="Regional"><span class="fas fa-globe-americas float-left"></span><div class="ml-1 float-left">Regional</div></button>
                    <?php if (count($_SESSION[$filtro_dashboard]['filtro_regional'])>0):?>
                        <button type="button" data-toggle="modal" class='btn btn-corp menu float-left' data-target="#datafilter_centro_zonal" title="Centro Zonal"><span class="fas fa-map-marker-alt float-left"></span><div class="ml-1 float-left">Centro Zonal</div></button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ($resultado_dashboard[0][2]!=""): ?>
                <div class="col-md-12 pb-1 px-1">
                    <?php for ($i=0; $i < count($_SESSION[$filtro_dashboard]['filtro_anio_mes']); $i++): ?> 
                        <a href="dashboard.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&eliminar=si&id_eliminar_anio_mes=<?php echo $i; ?>" class="btn btn-corp-filtro float-left ml-1 py-0 px-1"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-1 float-left"><?php echo $_SESSION[$filtro_dashboard]['filtro_anio_mes'][$i]; ?></div></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            <div class="col-md-12 pb-1 px-1">
                <?php for ($i=0; $i < count($_SESSION[$filtro_dashboard]['filtro_regional']); $i++): ?> 
                    <a href="dashboard.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&eliminar=si&id_eliminar_regional=<?php echo $i; ?>" class="btn btn-corp-filtro float-left ml-1 py-0 px-1"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-1 float-left"><?php echo $array_nombre_regional[$_SESSION[$filtro_dashboard]['filtro_regional'][$i]]; ?></div></a>
                <?php endfor; ?>
            </div>
            <?php if (count($_SESSION[$filtro_dashboard]['filtro_centro_zonal'])>0):?>
                <div class="col-md-12 pb-0 px-1">
                    <?php for ($i=0; $i < count($_SESSION[$filtro_dashboard]['filtro_centro_zonal']); $i++): ?> 
                        <a href="dashboard.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&eliminar=si&id_eliminar_centro_zonal=<?php echo $i; ?>" class="btn btn-corp-filtro float-left ml-1 py-0 px-1"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-1 float-left"><?php echo $array_nombre_centro_zonal[$_SESSION[$filtro_dashboard]['filtro_centro_zonal'][$i]]; ?></div></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="row p-1">
            <?php
            echo "<pre>";
            // print_r($_SESSION[$filtro_dashboard]);
            echo "</pre>";
            ?>
            <?php for ($k=0; $k < count($resultado_grupos); $k++): ?>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 cuadro_dash_titulo text-center mb-1">
                            <span class="titulo_dash_grupo"><?php echo $resultado_grupos[$k][2]; ?></span>
                        </div>
                        <?php for ($i=0; $i < count($array_grupos_graficas_lista[$resultado_grupos[$k][0]]); $i++): ?>
                            <?php
                                $variable_div=$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['variable'];
                                $tipo_grafica_div=$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['tipo'];
                                $ancho_grafica=$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['ancho'];
                            ?>
                            <?php if($variable_div=="variable_edad" OR $variable_div=="variable_genero" OR $variable_div=="variable_motivo_atencion" OR $variable_div=="variable_mapa_radicados" OR $variable_div=="variable_mapa_efectivas_cantidad" OR $variable_div=="variable_mapa_efectivas_porcentaje" OR $variable_div=="variable_indicador_general" OR $variable_div=="variable_indicador_agente" OR $variable_div=="variable_gestionadas_agente" OR $variable_div=="variable_contactadas_agente" OR $variable_div=="variable_efectivas_agente" OR $id_variable=="variable_alertas"): ?>

                                <?php if($variable_div=="variable_mapa_radicados" OR $variable_div=="variable_mapa_efectivas_cantidad" OR $variable_div=="variable_mapa_efectivas_porcentaje"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_edad" OR $variable_div=="variable_genero" OR $variable_div=="variable_motivo_atencion"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_indicador_general"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_indicador_agente"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_gestionadas_agente"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_contactadas_agente"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_efectivas_agente"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                    </div>
                                <?php elseif($variable_div=="variable_alertas"): ?>
                                    <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                        <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="col-md-<?php echo $ancho_grafica; ?> py-0 px-1 mb-2">
                                    <div id="grafica_<?php echo $array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]; ?>" style="width: 100%; height: 100%;"></div>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>                                
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php
        include("footer.php");
        include("config/configuracion_js_raiz.php");
        include("contenido_dashboard_filtro_anio_mes.php");
        include("contenido_dashboard_filtro_regional.php");
        include("contenido_dashboard_filtro_centro_zonal.php");
    ?>
    <?php if(count($_SESSION[$filtro_dashboard]['filtro_anio_mes'])>0): ?>
        <script type="text/javascript">
            <?php for ($k=0; $k < count($resultado_grupos); $k++): ?>
                <?php for ($i=0; $i < count($array_grupos_graficas_lista[$resultado_grupos[$k][0]]); $i++): ?>
                    <?php
                        $tipo_grafica=$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['tipo'];
                        $id_grafica=$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i];
                        $id_variable=$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['variable'];

                        $nombre_grafica=$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['nombre'];
                    ?>
                    <?php if($id_variable=="variable_edad" OR $id_variable=="variable_genero" OR $id_variable=="variable_motivo_atencion" OR $id_variable=="variable_mapa_radicados" OR $id_variable=="variable_mapa_efectivas_cantidad" OR $id_variable=="variable_mapa_efectivas_porcentaje" OR $id_variable=="variable_indicador_general" OR $id_variable=="variable_indicador_agente" OR $id_variable=="variable_gestionadas_agente" OR $id_variable=="variable_contactadas_agente" OR $id_variable=="variable_efectivas_agente" OR $id_variable=="variable_alertas"): ?>
                        <?php if($id_variable=="variable_mapa_radicados"): ?>
                            <?php
                                echo generar_grafica_mapa($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, '', $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php elseif($id_variable=="variable_mapa_efectivas_cantidad"): ?>
                            <?php
                                echo generar_grafica_mapa($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, 'cantidad', $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php elseif($id_variable=="variable_mapa_efectivas_porcentaje"): ?>
                            <?php
                                echo generar_grafica_mapa($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, 'porcentaje', $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php elseif($id_variable=="variable_edad" OR $id_variable=="variable_genero" OR $id_variable=="variable_motivo_atencion" OR $id_variable=="variable_alertas"): ?>
                            <?php if($tipo_grafica=="torta"): ?>
                                <?php
                                    echo generar_grafica_torta($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard]);
                                ?>
                            <?php elseif($tipo_grafica=="barra"): ?>
                                <?php
                                    echo generar_grafica_barra($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard]);
                                ?>
                            <?php endif; ?>
                        <?php elseif($id_variable=="variable_indicador_general"): ?>
                            <?php
                                echo generar_grafica_indicador_gral($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php elseif($id_variable=="variable_indicador_agente"): ?>
                            <?php
                                echo generar_grafica_indicador_agente($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php elseif($id_variable=="variable_gestionadas_agente"): ?>
                            <?php
                                echo generar_grafica_indicador_agente_pro($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard], 'Gestionadas');
                            ?>
                        <?php elseif($id_variable=="variable_contactadas_agente"): ?>
                            <?php
                                echo generar_grafica_indicador_agente_pro($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard], 'Contactadas');
                            ?>
                        <?php elseif($id_variable=="variable_efectivas_agente"): ?>
                            <?php
                                echo generar_grafica_indicador_agente_pro($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard], 'Efectivas');
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if($tipo_grafica=="torta"): ?>
                            <?php
                                echo generar_grafica_torta($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php elseif($tipo_grafica=="barra"): ?>
                            <?php
                                echo generar_grafica_barra($enlace_db, $id_grafica, $nombre_grafica, $resultado_dashboard[0][2], $id_variable, $_SESSION[$filtro_dashboard]);
                            ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endfor; ?>
        </script>
            
    <?php endif; ?>
</body>
</html>