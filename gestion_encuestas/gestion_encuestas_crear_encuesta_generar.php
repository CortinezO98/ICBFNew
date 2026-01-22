<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $pagina=validar_input($_GET['pagina']);
    $id_filtro=validar_input($_GET['id']);
    $estado_bandeja=validar_input($_GET['est']);

    $id_matriz=validar_input(base64_decode($_GET['mat']));
    $id_registro=validar_input(base64_decode($_GET['reg']));

    $consulta_string_matriz="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_encuestas_matriz` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuestas_matriz`.`gem_registro_usuario`=TU.`usu_id` WHERE `gem_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $id_matriz);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);
    
    $consulta_string_seccion="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar`, `gems_notificacion`, `gems_seccion_complemento` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_encuesta`=? ORDER BY `gems_orden_mostrar` ASC";
    $consulta_registros_seccion = $enlace_db->prepare($consulta_string_seccion);
    $consulta_registros_seccion->bind_param("s", $id_matriz);
    $consulta_registros_seccion->execute();
    $resultado_registros_seccion = $consulta_registros_seccion->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_seccion); $i++) { 
        $array_secciones[]=$resultado_registros_seccion[$i][0];

        $array_opciones_destino[$resultado_registros_seccion[$i][0]]=$resultado_registros_seccion[$i][6];

        $array_flujo_encuesta[$resultado_registros_seccion[$i][0]]['id']=$resultado_registros_seccion[$i][0];
        $array_flujo_encuesta[$resultado_registros_seccion[$i][0]]['nombre']=$resultado_registros_seccion[$i][2];
        $array_flujo_encuesta[$resultado_registros_seccion[$i][0]]['descripcion']=$resultado_registros_seccion[$i][3];
        
        if($resultado_registros_seccion[$i][8]=="Si") {
            $array_opciones_estado_alerta_seccion[$resultado_registros_seccion[$i][0]]=$resultado_registros_seccion[$i][8];
        }

        if ($resultado_registros_seccion[$i][9]!="") {
            $array_seccion_estado_alerta_complemento[$resultado_registros_seccion[$i][0]]=$resultado_registros_seccion[$i][9];
        }
    }

    $consulta_string_preguntas="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar`, `gemp_tipo_campo` , `gemp_seccion_complemento`FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_encuesta`=? ORDER BY `gemp_orden_mostrar` ASC";
    $consulta_registros_preguntas = $enlace_db->prepare($consulta_string_preguntas);
    $consulta_registros_preguntas->bind_param("s", $id_matriz);
    $consulta_registros_preguntas->execute();
    $resultado_registros_preguntas = $consulta_registros_preguntas->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_preguntas); $i++) { 
        $array_secciones_preguntas[$resultado_registros_preguntas[$i][2]][]=$resultado_registros_preguntas[$i][0];

        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['id']=$resultado_registros_preguntas[$i][0];
        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['nombre']=$resultado_registros_preguntas[$i][3];
        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['descripcion']=$resultado_registros_preguntas[$i][4];
        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['tipo']=$resultado_registros_preguntas[$i][5];
        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['obligatorio']=$resultado_registros_preguntas[$i][6];
        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['segun_respuesta']=$resultado_registros_preguntas[$i][7];
        $array_flujo_encuesta[$resultado_registros_preguntas[$i][2]]['preguntas'][$resultado_registros_preguntas[$i][0]]['tipo_campo']=$resultado_registros_preguntas[$i][9];

        if ($resultado_registros_preguntas[$i][10]!="") {
            $array_pregunta_estado_alerta_complemento[$resultado_registros_preguntas[$i][0]]=$resultado_registros_preguntas[$i][10];
        }
    }

    $consulta_string_opciones="SELECT `gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_orden_mostrar`, `gemo_estado_registro`, `gemo_estado_contacto`, `gemo_estado_efectivo`, `gemo_notificacion` FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_encuesta`=? ORDER BY `gemo_orden_mostrar` ASC";
    $consulta_registros_opciones = $enlace_db->prepare($consulta_string_opciones);
    $consulta_registros_opciones->bind_param("s", $id_matriz);
    $consulta_registros_opciones->execute();
    $resultado_registros_opciones = $consulta_registros_opciones->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_opciones); $i++) {
        unset($array_opcion);

        $array_opciones_destino[$resultado_registros_opciones[$i][0]]=$resultado_registros_opciones[$i][5];

        $array_opciones_estado_cierre[$resultado_registros_opciones[$i][0]]=$resultado_registros_opciones[$i][7];
        $array_opciones_estado_contacto[$resultado_registros_opciones[$i][0]]=$resultado_registros_opciones[$i][8];
        $array_opciones_estado_efectivo[$resultado_registros_opciones[$i][0]]=$resultado_registros_opciones[$i][9];

        if ($resultado_registros_opciones[$i][10]) {
            $array_opciones_estado_alerta[$resultado_registros_opciones[$i][0]]=$resultado_registros_opciones[$i][10];
        }
            
        $array_opcion['id']=$resultado_registros_opciones[$i][0];
        $array_opcion['nombre']=$resultado_registros_opciones[$i][4];
        $array_opcion['seccion_destino']=$resultado_registros_opciones[$i][5];

        $array_flujo_encuesta[$resultado_registros_opciones[$i][2]]['preguntas'][$resultado_registros_opciones[$i][3]]['opciones'][]=$array_opcion;
    }

    unset($_SESSION['registro_encuesta_exitoso']);

    unset($_SESSION['registro_detalle_encuesta']);
    unset($_SESSION['registro_configuracion_encuesta_secciones']);
    unset($_SESSION['registro_configuracion_encuesta_secciones_preguntas']);
    unset($_SESSION['registro_configuracion_encuesta_detalle']);
    unset($_SESSION['registro_configuracion_encuesta_navegacion']);

    unset($_SESSION['registro_configuracion_encuesta_estado_cierre']);
    unset($_SESSION['registro_configuracion_encuesta_estado_contacto']);
    unset($_SESSION['registro_configuracion_encuesta_estado_efectivo']);
    unset($_SESSION['registro_configuracion_encuesta_estado_alerta']);
    unset($_SESSION['registro_configuracion_encuesta_estado_alerta_seccion']);
    unset($_SESSION['registro_configuracion_encuesta_alerta_complemento']);
    unset($_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion']);

    unset($_SESSION['registro_configuracion_encuesta_estado_cierre_final']);
    unset($_SESSION['registro_configuracion_encuesta_estado_contacto_final']);
    unset($_SESSION['registro_configuracion_encuesta_estado_efectivo_final']);

    $_SESSION['registro_detalle_encuesta']['id']=$resultado_registros_matriz[0][0];
    $_SESSION['registro_detalle_encuesta']['nombre']=$resultado_registros_matriz[0][1];
    $_SESSION['registro_detalle_encuesta']['estado']=$resultado_registros_matriz[0][2];
    $_SESSION['registro_detalle_encuesta']['descripcion']=$resultado_registros_matriz[0][3];

    $_SESSION['registro_configuracion_encuesta_secciones']=$array_secciones;
    $_SESSION['registro_configuracion_encuesta_secciones_preguntas']=$array_secciones_preguntas;
    $_SESSION['registro_configuracion_encuesta_opciones_destino']=$array_opciones_destino;

    $_SESSION['registro_configuracion_encuesta_estado_cierre']=$array_opciones_estado_cierre;
    $_SESSION['registro_configuracion_encuesta_estado_contacto']=$array_opciones_estado_contacto;
    $_SESSION['registro_configuracion_encuesta_estado_efectivo']=$array_opciones_estado_efectivo;
    
    $_SESSION['registro_configuracion_encuesta_estado_alerta']=$array_opciones_estado_alerta;
    $_SESSION['registro_configuracion_encuesta_estado_alerta_seccion']=$array_opciones_estado_alerta_seccion;

    $_SESSION['registro_configuracion_encuesta_alerta_complemento']=$array_pregunta_estado_alerta_complemento;
    $_SESSION['registro_configuracion_encuesta_alerta_complemento_seccion']=$array_seccion_estado_alerta_complemento;

    $_SESSION['registro_configuracion_encuesta_estado_cierre_final'][0]=0;
    $_SESSION['registro_configuracion_encuesta_estado_contacto_final'][0]=0;
    $_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][0]=0;

    $_SESSION['registro_configuracion_encuesta_detalle']=$array_flujo_encuesta;

    $_SESSION['registro_configuracion_encuesta_navegacion'][]=$array_secciones[0];

    header('Location: gestion_encuestas_crear_encuesta_informacion_general.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
?>