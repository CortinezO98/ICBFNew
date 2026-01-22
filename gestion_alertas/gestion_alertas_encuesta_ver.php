<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

// error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/

    $id_registro=validar_input(base64_decode($_GET['reg']));
    
    $consulta_string_encuestas="SELECT `ger_consecutivo`, `ger_encuesta_id`, `ger_radicado`, `ger_observaciones`, `ger_registro_usuario`, `ger_registro_fecha`, TU.`usu_nombres_apellidos`, `ger_contactado`, `ger_efectivo` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` WHERE `ger_consecutivo`=?";

    $consulta_registros_encuestas = $enlace_db->prepare($consulta_string_encuestas);
    $consulta_registros_encuestas->bind_param("s", $id_registro);
    $consulta_registros_encuestas->execute();
    $resultado_registros_encuestas = $consulta_registros_encuestas->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_matriz="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_encuestas_matriz` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuestas_matriz`.`gem_registro_usuario`=TU.`usu_id` WHERE `gem_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $resultado_registros_encuestas[0][1]);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);
    
    $consulta_string_seccion="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_encuesta`=? ORDER BY `gems_orden_mostrar` ASC";
    $consulta_registros_seccion = $enlace_db->prepare($consulta_string_seccion);
    $consulta_registros_seccion->bind_param("s", $resultado_registros_encuestas[0][1]);
    $consulta_registros_seccion->execute();
    $resultado_registros_seccion = $consulta_registros_seccion->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_seccion); $i++) { 
        $array_secciones[$resultado_registros_seccion[$i][0]]['nombre']=$resultado_registros_seccion[$i][2];
    }

    $consulta_string_preguntas="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar`, `gemp_tipo_campo` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_encuesta`=? ORDER BY `gemp_orden_mostrar` ASC";
    $consulta_registros_preguntas = $enlace_db->prepare($consulta_string_preguntas);
    $consulta_registros_preguntas->bind_param("s", $resultado_registros_encuestas[0][1]);
    $consulta_registros_preguntas->execute();
    $resultado_registros_preguntas = $consulta_registros_preguntas->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_preguntas); $i++) { 
        $array_secciones[$resultado_registros_preguntas[$i][2]]['preguntas'][]=$resultado_registros_preguntas[$i][0];

        $array_preguntas[$resultado_registros_preguntas[$i][0]]['nombre']=$resultado_registros_preguntas[$i][3];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['descripcion']=$resultado_registros_preguntas[$i][4];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['tipo']=$resultado_registros_preguntas[$i][5];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['obligatorio']=$resultado_registros_preguntas[$i][6];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['segun_respuesta']=$resultado_registros_preguntas[$i][7];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['orden']=$resultado_registros_preguntas[$i][8];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['tipo_campo']=$resultado_registros_preguntas[$i][9];
    }

    $consulta_string_opciones="SELECT `gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_orden_mostrar`, `gemo_estado_registro`, `gemo_estado_contacto`, `gemo_estado_efectivo`, `gemo_notificacion` FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_encuesta`=? ORDER BY `gemo_orden_mostrar` ASC";
    $consulta_registros_opciones = $enlace_db->prepare($consulta_string_opciones);
    $consulta_registros_opciones->bind_param("s", $resultado_registros_encuestas[0][1]);
    $consulta_registros_opciones->execute();
    $resultado_registros_opciones = $consulta_registros_opciones->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_opciones); $i++) {
        $array_preguntas[$resultado_registros_opciones[$i][3]]['opciones'][]=$resultado_registros_opciones[$i][0];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['nombre']=$resultado_registros_opciones[$i][4];
        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['siguiente_seccion']=$resultado_registros_opciones[$i][5];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_cierre']=$resultado_registros_opciones[$i][7];
        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_contacto']=$resultado_registros_opciones[$i][8];
        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_efectivo']=$resultado_registros_opciones[$i][9];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_notificacion']=$resultado_registros_opciones[$i][10];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['orden']=$resultado_registros_opciones[$i][6];
    }

    $consulta_string_data="SELECT TD.`gerd_seccion`, TD.`gerd_pregunta`, TD.`gerd_respuesta`, TPR.`gemp_tipo`, TOP.`gemo_opcion_nombre` FROM `tb_gestion_encuesta_registro` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_registro`.`ger_registro_usuario`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_encuesta_registro`.`ger_encuesta_id`=TM.`gem_id` LEFT JOIN `tb_gestion_encuesta_radicado` AS TR ON `tb_gestion_encuesta_registro`.`ger_radicado`=TR.`gera_radicado` LEFT JOIN `tb_gestion_encuesta_registro_data` AS TD ON `tb_gestion_encuesta_registro`.`ger_consecutivo`=TD.`gerd_encuesta` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta` AS TPR ON TD.`gerd_pregunta`=TPR.`gemp_id` LEFT JOIN `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` AS TOP ON TD.`gerd_respuesta`=TOP.`gemo_id` WHERE `ger_consecutivo`=? ORDER BY `gemo_orden_mostrar` ASC";
    $consulta_registros_data = $enlace_db->prepare($consulta_string_data);
    $consulta_registros_data->bind_param("s", $resultado_registros_encuestas[0][0]);
    $consulta_registros_data->execute();
    $resultado_registros_data = $consulta_registros_data->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_data); $i++) {
        if ($resultado_registros_data[$i][3]=="Varias opciones" OR $resultado_registros_data[$i][3]=="Casillas" OR $resultado_registros_data[$i][3]=="Desplegable") {
            $array_respuestas[$resultado_registros_data[$i][0]][$resultado_registros_data[$i][1]]=$resultado_registros_data[$i][4];
        } else {
            $array_respuestas[$resultado_registros_data[$i][0]][$resultado_registros_data[$i][1]]=$resultado_registros_data[$i][2];
        }
    }
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                <div class="titulo-encuesta"><?php echo ($resultado_registros_matriz[0][2]=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $resultado_registros_matriz[0][1]; ?></div>
                <div class="descripcion-seccion"><?php echo $resultado_registros_matriz[0][3]; ?></div>
            </div>
            <?php if(count($resultado_registros_seccion)>0): ?>
                <?php for ($i=0; $i < count($resultado_registros_seccion); $i++): ?>
                    <?php if(isset($array_respuestas[$resultado_registros_seccion[$i][0]])): ?>
                        <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                            <div class="row p-0">
                                <div class="col-md-12">
                                    <div class="py-2 px-3">
                                        <b><?php echo $resultado_registros_seccion[$i][2]; ?></b>
                                    </div>
                                    <?php if($resultado_registros_seccion[$i][3]!=""): ?>
                                        <div class="descripcion-seccion py-1 px-3"><?php echo nl2br($resultado_registros_seccion[$i][3]); ?><hr class="py-0 my-1"></div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            <?php for ($j=0; $j < count($array_secciones[$resultado_registros_seccion[$i][0]]['preguntas']); $j++): ?>
                                <?php
                                    $id_pregunta=$array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'][$j];
                                    $id_seccion=$resultado_registros_seccion[$i][0];
                                    $nombre=$array_preguntas[$id_pregunta]['nombre'];
                                    $descripcion=$array_preguntas[$id_pregunta]['descripcion'];
                                    $tipo=$array_preguntas[$id_pregunta]['tipo'];
                                    $obligatorio=$array_preguntas[$id_pregunta]['obligatorio'];
                                    $segun_respuesta=$array_preguntas[$id_pregunta]['segun_respuesta'];
                                    $orden=$array_preguntas[$id_pregunta]['orden'];
                                    $tipo_campo=$array_preguntas[$id_pregunta]['tipo_campo'];
                                    if (!isset($array_preguntas[$id_pregunta]['opciones'])) {
                                        $array_preguntas[$id_pregunta]['opciones']=array();
                                    }
                                    $conteo_opciones=count($array_preguntas[$id_pregunta]['opciones']);
                                ?>
                                <div class="row px-3 py-1 m-0">
                                    <div class="col-md-12">
                                        <div class="pb-1">
                                            <?php echo $nombre; ?>
                                            <?php echo ($descripcion!="")? "<div class='descripcion-seccion'>$descripcion</div>" : ''; ?>
                                        </div>
                                        <p class="alert alert-warning p-1"><?php echo $array_respuestas[$id_seccion][$id_pregunta]; ?></p>
                                    </div>
                                </div>
                                <hr class="mx-3 my-1">
                            <?php endfor; ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="py-1 px-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php else: ?>
                <div class="col-md-12 fondo-blanco p-0 mb-2">
                    <p class="alert alert-warning text-left p-1 m-0"><span class="fas fa-exclamation-triangle"></span> No se encontraron registros</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>