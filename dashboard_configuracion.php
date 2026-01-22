<?php
    require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    require_once("config/conexion_db.php");
    unset($_SESSION['dashboard_registro_creado_grupo']);
    unset($_SESSION['dashboard_registro_creado_grafica']);
    unset($_SESSION['dashboard_registro_eliminado_grupo']);
    unset($_SESSION['dashboard_registro_eliminado_grafica']);
    //agregar cero a números de un dígito
    function validarCeros($num){
        if (iconv_strlen($num)==1) {
            return "0" . $num;
        } else{
            return $num;
        }
    }
    
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    $perfil_modulo=$_SESSION['modulos_acceso_permisos'][$modulo_plataforma];

    $filtro_dashboard=validar_input(base64_decode($_GET['reg']));

    $consulta_dashboard = $enlace_db->prepare("SELECT `gd_id`, `gd_nombre`, `gd_matriz`, `gd_campania`, `gd_orden`, `gd_estado`, `gd_registro_usuario`, `gd_registro_fecha`, TM.`gem_nombre_encuesta`, TU.`usu_nombres_apellidos` FROM `tb_gestion_dashboard` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_dashboard`.`gd_matriz`=TM.`gem_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_dashboard`.`gd_registro_usuario`=TU.`usu_id` WHERE `gd_id`=?");
    $consulta_dashboard->bind_param("s", $filtro_dashboard);
    $consulta_dashboard->execute();
    $resultado_dashboard = $consulta_dashboard->get_result()->fetch_all(MYSQLI_NUM);

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

    $consulta_string_preguntas="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE (`gemp_tipo`='Casillas' OR `gemp_tipo`='Varias Opciones' OR `gemp_tipo`='Desplegable') AND `gemp_encuesta`=? ORDER BY `gemp_pregunta_nombre` ASC";
    $consulta_registros_preguntas = $enlace_db->prepare($consulta_string_preguntas);
    $consulta_registros_preguntas->bind_param("s", $resultado_dashboard[0][2]);
    $consulta_registros_preguntas->execute();
    $resultado_registros_preguntas = $consulta_registros_preguntas->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_preguntas); $i++) { 
        $array_nombre_variable[$resultado_registros_preguntas[$i][0]]=$resultado_registros_preguntas[$i][3];
    }

    $array_nombre_variable['variable_edad']="Edad";
    $array_nombre_variable['variable_genero']="Género";
    $array_nombre_variable['variable_motivo_atencion']="Motivo atención";
    $array_nombre_variable['variable_mapa_radicados']="Radicados por regional (Mapa-Cantidad)";
    $array_nombre_variable['variable_mapa_efectivas_cantidad']="Efectivas por regional (Mapa-Cantidad)";
    $array_nombre_variable['variable_mapa_efectivas_porcentaje']="Efectivas por regional (Mapa-Porcentaje)";
    $array_nombre_variable['variable_indicador_general']="Indicador general";
    $array_nombre_variable['variable_indicador_agente']="Indicador general por agente";
    $array_nombre_variable['variable_gestionadas_agente']="Gestionadas por agente";
    $array_nombre_variable['variable_contactadas_agente']="Contactadas por agente";
    $array_nombre_variable['variable_efectivas_agente']="Efectivas por agente";
    $array_nombre_variable['variable_alertas']="Alertas generadas";

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Dashboard | Configuración";
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php
        include("config/configuracion_estilos_raiz.php");
        include("config/configuracion_calendario_raiz.php");
    ?>
</head>
<body>
    <?php
        $menu_principal=includeFileContent("menu_principal.php");
        echo str_replace('../', '', $menu_principal);
        include("menu_header_raiz.php");
    ?>
    <div class="contenido" style="background-color: #EEEEEE;">
        <div class="row">
            <div class="col-md-12 py-0 px-0 mb-1 mt-0 fondo-gris border-top-gris">
                <div class="col-md-12 color-blanco titulo-dashboard text-uppercase"><img src="images/img_dashboard.svg" class="img-fluid" style="width: 40px;"> <?php echo $resultado_dashboard[0][1]; ?></div>
                <div class="col-md-12 color-blanco detalle-dashboard"><span class="fas fa-tasks"></span> <?php echo $resultado_dashboard[0][8]; ?> | <span class="fas fa-user"></span> <?php echo $resultado_dashboard[0][9]; ?> | <span class="fas fa-calendar-alt"></span> <?php echo $resultado_dashboard[0][7]; ?></div>
            </div>
            <div class="col-md-12 py-0 px-1">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <a href="administrador/configuracion_dashboard_grupo_crear.php?reg=<?php echo base64_encode($filtro_dashboard); ?>" class="btn btn-corp menu float-right ml-1" title="Crear Grupo"><div class="float-left"><span class="fas fa-plus"></span> Crear Grupo</div></a>
                <?php endif; ?>
                <a href="contenido.php" class="btn btn-corp menu float-right ml-1" title="Regresar"><div class="float-left"><span class="fas fa-arrow-left"></span> Regresar</div></a>
            </div>
        </div>
        <div class="row p-1">
            <?php for ($k=0; $k < count($resultado_grupos); $k++): ?>
                <div class="col-md-12 mb-1">
                    <div class="row">
                        <div class="col-md-12 cuadro_dash_titulo text-center mb-1">
                            <a href="administrador/configuracion_dashboard_grafica_crear.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&gru=<?php echo base64_encode($resultado_grupos[$k][0]); ?>" class="btn btn-corp-3 p-0" title="Crear gráfica"><span class="fas fa-chart-pie"></span></a>
                            <a href="administrador/configuracion_dashboard_grupo_editar.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&gru=<?php echo base64_encode($resultado_grupos[$k][0]); ?>" class="btn btn-corp-3 p-0" title="Editar"><span class="fas fa-pen"></span></a>
                            <a href="administrador/configuracion_dashboard_grupo_eliminar.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&gru=<?php echo base64_encode($resultado_grupos[$k][0]); ?>" class="btn btn-corp-3 p-0" title="Eliminar"><span class="fas fa-trash-alt"></span></a>
                            <span class="titulo_dash_grupo"><?php echo $resultado_grupos[$k][2]; ?></span>
                        </div>
                        <?php for ($i=0; $i < count($array_grupos_graficas_lista[$resultado_grupos[$k][0]]); $i++): ?>
                            <div class="col-md-<?php echo $array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['ancho']; ?> py-0 mb-2">
                                <div class="row px-1">
                                    <div class="col-md-12 fondo-blanco border">
                                        <div class="col-md-12">
                                            <a href="administrador/configuracion_dashboard_grafica_editar.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&gra=<?php echo base64_encode($array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]); ?>" class="color-corporativo p-0" style="font-size: 11px;" title="Editar"><span class="fas fa-pen"></span></a>
                                            <a href="administrador/configuracion_dashboard_grafica_eliminar.php?reg=<?php echo base64_encode($filtro_dashboard); ?>&gra=<?php echo base64_encode($array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]); ?>" class="color-rojo p-0" style="font-size: 11px;" title="Eliminar"><span class="fas fa-trash-alt"></span></a>
                                        </div>
                                        <div class="col-md-12 detalle-dashboard"><span class="fas fa-list-ol"></span> <?php echo $array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['orden']; ?></div>
                                        <div class="col-md-12 detalle-dashboard"><span class="fas fa-text-width"></span> <?php echo $array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['ancho']; ?></div>
                                        <div class="col-md-12 detalle-dashboard">
                                            <?php if($array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['tipo']=="torta"): ?>
                                                <span class="fas fa-chart-pie"></span>
                                            <?php elseif($array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['tipo']=="barra"): ?>
                                                <span class="fas fa-chart-bar"></span>
                                            <?php endif; ?>
                                            <?php echo $array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['nombre']; ?></div>
                                        <div class="col-md-12 detalle-dashboard"><span class="fas fa-cogs"></span> <?php echo $array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['tipo']; ?></div>
                                        <div class="col-md-12 detalle-dashboard"><span class="fas fa-tasks"></span> <?php echo $array_nombre_variable[$array_grupos_graficas[$resultado_grupos[$k][0]][$array_grupos_graficas_lista[$resultado_grupos[$k][0]][$i]]['variable']]; ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>                                
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php
        include("footer.php");
        include("config/configuracion_js_raiz.php");
    ?>
</body>
</html>