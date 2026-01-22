<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Configuración";
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $fecha_calculadora=validar_input($_GET['date']);
    unset($_SESSION['registro_creado_segmento']);
    unset($_SESSION['registro_creado_fecha']);
    unset($_SESSION['registro_cargue_malla']);
    unset($_SESSION['calculadora_malla_calidad_eliminado']);
    unset($_SESSION['registro_cargue_lider']);
    unset($_SESSION['registro_creado_random']);
    unset($_SESSION['registro_creado_random_eliminado']);
    unset($_SESSION['calculadora_lider_calidad_eliminado']);
    unset($_SESSION['registro_creado_recalcular']);

    // Inicializa variable tipo array
    $data_consulta_segmento=array();
    $data_consulta_segmento_2=array();
    $array_meses=[1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];

    $consulta_string="SELECT `cm_id`, `cm_nombre`, `cm_intervalo_confianza`, `cm_valor_z`, `cm_varianza_estimada`, `cm_error_muestral`, `cm_registro_usuario`, `cm_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral`.`cm_registro_usuario`=TU.`usu_id` WHERE `cm_id`=?";
    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_segmento="SELECT `cms_id`, `cms_calculadora`, `cms_nombre_segmento`, `cms_peso` FROM `tb_gestion_calidad_cmuestral_segmento` WHERE `cms_calculadora`=? ORDER BY `cms_nombre_segmento` ASC";

    $consulta_registros_segmento = $enlace_db->prepare($consulta_string_segmento);
    $consulta_registros_segmento->bind_param("s", $id_registro);
    $consulta_registros_segmento->execute();
    $resultado_registros_segmento = $consulta_registros_segmento->get_result()->fetch_all(MYSQLI_NUM);

    $filtro_segmento_2="";
    array_push($data_consulta_segmento_2, $fecha_calculadora);
    for ($i=0; $i < count($resultado_registros_segmento); $i++) { 
        $filtro_segmento_2.="`ccmm_segmento`=? OR ";
        array_push($data_consulta_segmento_2, $resultado_registros_segmento[$i][0]);//Se agrega llave por ser variable evaluada en un like
    }

    $filtro_segmento_2=" AND (".substr($filtro_segmento_2, 0, -4).")";

    if (count($resultado_registros_segmento)>0) {
        $consulta_string_fechas="SELECT DISTINCT SUBSTR(`cmm_mes`, 1, 7) AS FECHA FROM `tb_gestion_calidad_cmuestral_mensual` WHERE `cmm_calculadora`=? ORDER BY FECHA ASC";
        $consulta_registros_fechas = $enlace_db->prepare($consulta_string_fechas);
        $consulta_registros_fechas->bind_param("s", $id_registro);
        $consulta_registros_fechas->execute();
        $resultado_registros_fechas = $consulta_registros_fechas->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_fechas); $i++) {
            $detalle_mes=explode('-', $resultado_registros_fechas[$i][0]);
            $mes_num=$detalle_mes[1]*1;
            $fechas_array[]=$detalle_mes[0].'-'.$array_meses[$mes_num];
            $fechas_array_link[]=$resultado_registros_fechas[$i][0];
        }
    
        $consulta_string_malla="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_martes`, `ccmm_monitoreo_miercoles`, `ccmm_monitoreo_jueves`, `ccmm_monitoreo_viernes`, `ccmm_monitoreo_sabado`, `ccmm_monitoreo_domingo`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos`, TC.`usu_nombres_apellidos`, TCAM.`ac_nombre_campania`, `ccmm_monitor`, TMONITOR.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TC ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_coordinador`=TC.`usu_id` LEFT JOIN `tb_administrador_campania` AS TCAM ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario_segmento`=TCAM.`ac_id` LEFT JOIN `tb_administrador_usuario` AS TMONITOR ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_monitor`=TMONITOR.`usu_id` WHERE `ccmm_mes`=? ".$filtro_segmento_2." ORDER BY TU.`usu_nombres_apellidos` ASC";
        $consulta_registros_malla = $enlace_db->prepare($consulta_string_malla);
        $consulta_registros_malla->bind_param(str_repeat("s", count($data_consulta_segmento_2)), ...$data_consulta_segmento_2);
        $consulta_registros_malla->execute();
        $resultado_registros_malla = $consulta_registros_malla->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_malla); $i++) {
            $turno_seg_usuario[$resultado_registros_malla[$i][2]][]=$resultado_registros_malla[$i][3];
            $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['id']=$resultado_registros_malla[$i][0];
            $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['nombre']=$resultado_registros_malla[$i][24];
            $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['coordinador']=$resultado_registros_malla[$i][25];
            $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['monitor']=$resultado_registros_malla[$i][28];
            $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['segmento']=$resultado_registros_malla[$i][26];
            $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['muestra']=$resultado_registros_malla[$i][13].$resultado_registros_malla[$i][14].$resultado_registros_malla[$i][15].$resultado_registros_malla[$i][16].$resultado_registros_malla[$i][17].$resultado_registros_malla[$i][18].$resultado_registros_malla[$i][19];
            
            if ($array_convenciones[$resultado_registros_malla[$i][6]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['turno']=$array_convenciones[$resultado_registros_malla[$i][6]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['color']=$array_convenciones_color[$resultado_registros_malla[$i][6]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['turno']=$resultado_registros_malla[$i][6];
            }

            if ($array_convenciones[$resultado_registros_malla[$i][7]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['turno']=$array_convenciones[$resultado_registros_malla[$i][7]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['color']=$array_convenciones_color[$resultado_registros_malla[$i][7]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['turno']=$resultado_registros_malla[$i][7];
            }

            if ($array_convenciones[$resultado_registros_malla[$i][8]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['turno']=$array_convenciones[$resultado_registros_malla[$i][8]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['color']=$array_convenciones_color[$resultado_registros_malla[$i][8]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['turno']=$resultado_registros_malla[$i][8];
            }

            if ($array_convenciones[$resultado_registros_malla[$i][9]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['turno']=$array_convenciones[$resultado_registros_malla[$i][9]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['color']=$array_convenciones_color[$resultado_registros_malla[$i][9]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['turno']=$resultado_registros_malla[$i][9];
            }

            if ($array_convenciones[$resultado_registros_malla[$i][10]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['turno']=$array_convenciones[$resultado_registros_malla[$i][10]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['color']=$array_convenciones_color[$resultado_registros_malla[$i][10]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['turno']=$resultado_registros_malla[$i][10];
            }

            if ($array_convenciones[$resultado_registros_malla[$i][11]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['turno']=$array_convenciones[$resultado_registros_malla[$i][11]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['color']=$array_convenciones_color[$resultado_registros_malla[$i][11]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['turno']=$resultado_registros_malla[$i][11];
            }

            if ($array_convenciones[$resultado_registros_malla[$i][12]]!="") {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['turno']=$array_convenciones[$resultado_registros_malla[$i][12]];
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['color']=$array_convenciones_color[$resultado_registros_malla[$i][12]];
            } else {
                $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['turno']=$resultado_registros_malla[$i][12];
            }
        }

        $consulta_string_malla_lider="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_malla_lider` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla_lider`.`ccmm_usuario`=TU.`usu_id` WHERE `ccmm_mes`=? ".$filtro_segmento_2." ORDER BY TU.`usu_nombres_apellidos` ASC";
        $consulta_registros_malla_lider = $enlace_db->prepare($consulta_string_malla_lider);
        $consulta_registros_malla_lider->bind_param(str_repeat("s", count($data_consulta_segmento_2)), ...$data_consulta_segmento_2);
        $consulta_registros_malla_lider->execute();
        $resultado_registros_malla_lider = $consulta_registros_malla_lider->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_malla_lider); $i++) {
            $turno_seg_lider[$resultado_registros_malla_lider[$i][2]][]=$resultado_registros_malla_lider[$i][3];
            $turno_mostrar_datos_lider[$resultado_registros_malla_lider[$i][3]]['id']=$resultado_registros_malla_lider[$i][0];
            $turno_mostrar_datos_lider[$resultado_registros_malla_lider[$i][3]]['nombre']=$resultado_registros_malla_lider[$i][13];
            
            if ($array_convenciones[$resultado_registros_malla_lider[$i][4]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][0]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][4]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][0]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][4]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][0]['turno']=$resultado_registros_malla_lider[$i][4];
            }

            if ($array_convenciones[$resultado_registros_malla_lider[$i][5]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][1]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][5]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][1]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][5]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][1]['turno']=$resultado_registros_malla_lider[$i][5];
            }

            if ($array_convenciones[$resultado_registros_malla_lider[$i][6]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][2]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][6]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][2]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][6]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][2]['turno']=$resultado_registros_malla_lider[$i][6];
            }

            if ($array_convenciones[$resultado_registros_malla_lider[$i][7]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][3]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][7]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][3]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][7]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][3]['turno']=$resultado_registros_malla_lider[$i][7];
            }

            if ($array_convenciones[$resultado_registros_malla_lider[$i][8]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][4]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][8]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][4]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][8]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][4]['turno']=$resultado_registros_malla_lider[$i][8];
            }

            if ($array_convenciones[$resultado_registros_malla_lider[$i][9]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][5]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][9]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][5]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][9]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][5]['turno']=$resultado_registros_malla_lider[$i][9];
            }

            if ($array_convenciones[$resultado_registros_malla_lider[$i][10]]!="") {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][6]['turno']=$array_convenciones[$resultado_registros_malla_lider[$i][10]];
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][6]['color']=$array_convenciones_color[$resultado_registros_malla_lider[$i][10]];
            } else {
                $turno_mostrar_lider[$resultado_registros_malla_lider[$i][2]][$resultado_registros_malla_lider[$i][3]][6]['turno']=$resultado_registros_malla_lider[$i][10];
            }
        }

        $filtro_mes='%'.substr($fecha_calculadora, 0, -3).'%';
        $consulta_string_parametros="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, `cmm_muestra_realizada`, `cmm_muestra_recalculada` FROM `tb_gestion_calidad_cmuestral_mensual` WHERE `cmm_calculadora`=? AND `cmm_mes` LIKE ? ORDER BY `cmm_mes` ASC";
        $consulta_registros_parametros = $enlace_db->prepare($consulta_string_parametros);
        $consulta_registros_parametros->bind_param("ss", $id_registro, $filtro_mes);
        $consulta_registros_parametros->execute();
        $resultado_registros_parametros = $consulta_registros_parametros->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_parametros); $i++) { 
            $id_semana=substr($resultado_registros_parametros[$i][2], 8, 2);
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]]['total_mes']=$resultado_registros_parametros[$i][4];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]]['muestra_calculada']=$resultado_registros_parametros[$i][5];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]]['muestra_auditoria']=$resultado_registros_parametros[$i][6];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['muestra_semanal']=$resultado_registros_parametros[$i][13];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['dias_semanal']=$resultado_registros_parametros[$i][10];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['peso_semanal']=$resultado_registros_parametros[$i][12];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['numero_agentes_semanal']=$resultado_registros_parametros[$i][7];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['monitoreos_agentes_semanal']=$resultado_registros_parametros[$i][9];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['monitoreos_realizados']=$resultado_registros_parametros[$i][14];
            $array_segmento_detalle[$resultado_registros_parametros[$i][3]][$id_semana]['monitoreos_recalculados']=$resultado_registros_parametros[$i][15];
        }

        $consulta_string_semanas="SELECT DISTINCT `cmm_mes` FROM `tb_gestion_calidad_cmuestral_mensual` WHERE `cmm_calculadora`=? ORDER BY `cmm_mes` ASC";
        $consulta_registros_semanas = $enlace_db->prepare($consulta_string_semanas);
        $consulta_registros_semanas->bind_param("s", $id_registro);
        $consulta_registros_semanas->execute();
        $resultado_registros_semanas = $consulta_registros_semanas->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_semanas); $i++) { 
            $array_semanas[substr($resultado_registros_semanas[$i][0], 0, 7)][]=$resultado_registros_semanas[$i][0];
        }

        $consulta_string_muestra="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_muestras` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_monitor`=TU.`usu_id` WHERE `cmm_calculadora`=? AND `cmm_mes`=?";
        $consulta_registros_muestra = $enlace_db->prepare($consulta_string_muestra);
        $consulta_registros_muestra->bind_param("ss", $id_registro, $fecha_calculadora);
        $consulta_registros_muestra->execute();
        $resultado_registros_muestra = $consulta_registros_muestra->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_muestra); $i++) { 
            $array_muestra[$resultado_registros_muestra[$i][3]][]=$resultado_registros_muestra[$i][6].' | '.$resultado_registros_muestra[$i][7];
        }

        $control_recalcular=0;
        for ($i=0; $i < count($resultado_registros_segmento); $i++) { 
            if(isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]])) {
                $control_recalcular++;
            }
        }
    }
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
        <div class="row">
            <div class="col-md-2 pt-2 pl-1">
                <div class="col-md-12 border mb-2 py-1 context fondo-blanco">
                    <div class="titulo-seccion-conocimiento color-corporativo">
                        <?php echo $resultado_registros[0][1]; ?>
                    </div>
                    <div class="descripcion-seccion-conocimiento-detalle pl-3">
                        <span class="fas fa-cog" title="Intervalo confianza"></span> Intervalo confianza: <?php echo $resultado_registros[0][2]; ?>
                        <br><span class="fas fa-cog" title="Valor Z"></span> Valor Z: <?php echo $resultado_registros[0][3]; ?>
                        <br><span class="fas fa-cog" title="Varianza estimada"></span> Varianza estimada: <?php echo $resultado_registros[0][4]; ?>
                        <br><span class="fas fa-cog" title="Error muestral"></span> Error muestral: <?php echo $resultado_registros[0][5]; ?>
                    </div>
                    <div class="descripcion-seccion-conocimiento-detalle">
                        <hr class="my-1">
                        <span class="fas fa-clipboard-list" title="Segmentos"></span> Segmentos:<br>
                        <ul>
                        <?php for ($i=0; $i < count($resultado_registros_segmento); $i++): ?>
                            <li><?php echo $resultado_registros_segmento[$i][2]; ?></li> 
                        <?php endfor; ?>
                        </ul>
                        <hr class="my-1">
                        <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                            <a href="gestion_cmuestral_configurar_fecha_crear.php?reg=<?php echo base64_encode($id_registro); ?>" class="color-corporativo font-size-11" title="Crear fecha"><span class="fas fa-plus"></span></a>
                        <?php endif; ?>
                        <span class="fas fa-calendar-alt" title="Fechas"></span> Fechas:<br>
                        <?php for ($m=0; $m < count($fechas_array); $m++): ?>
                            <div class="ml-3">
                                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                                    <!-- <a href="gestion_cmuestral_configurar_fecha_editar.php?reg=<?php echo base64_encode($id_registro); ?>" class="color-corporativo font-size-11" title="Editar fecha"><span class="fas fa-pen"></span></a> -->
                                <?php endif; ?>
                                <b><span class="fas fa-calendar-check"></span> <?php echo $fechas_array[$m]; ?></b><br>
                                <?php for ($i=0; $i < count($array_semanas[$fechas_array_link[$m]]); $i++): ?>
                                    <a href="gestion_cmuestral_configurar.php?reg=<?php echo base64_encode($id_registro); ?>&date=<?php echo $array_semanas[$fechas_array_link[$m]][$i]; ?>" class="pl-3">Semana <?php echo $i+1; ?></a><br>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-10 p-1">
                <div class="row">
                    <div class="col-md-12 py-2">
                        <?php if($fecha_calculadora!=""): ?>
                            <!-- <a href="gestion_cmuestral_configurar_reporte_excel.php?reg=<?php echo base64_encode($id_registro); ?>&date=<?php echo $fecha_calculadora; ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-file-excel"></span></div><div class="pl-2 menu_res float-left">Generar Reporte</div></a> -->
                        <?php endif; ?>
                        <a href="gestion_cmuestral.php?pagina=1&id=null" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-arrow-left"></span></div><div class="pl-2 menu_res float-left">Regresar</div></a>
                        <?php if($control_recalcular==0 AND substr($fecha_calculadora, 8, 2)!='S1'): ?>
                            <a href="gestion_cmuestral_muestra_recalcular.php?reg=<?php echo base64_encode($id_registro); ?>&date=<?php echo $fecha_calculadora; ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-sync-alt"></span></div><div class="pl-2 menu_res float-left">Recalcular Muestra</div></a>
                        <?php endif; ?>
                        <?php if($fecha_calculadora!=""): ?>
                            <div class="float-left descripcion-seccion-conocimiento-detalle">
                                Fecha: <span class="fas fa-calendar-check"></span> <?php echo $fecha_calculadora; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($fecha_calculadora==""): ?>
                        <div class="col-md-12 pt-2">
                            <p class="alert alert-warning col-md-12 p-1 font-size-11">
                                <span class="fas fa-exclamation-triangle"></span> Por favor seleccione una fecha
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if ($fecha_calculadora!=""): ?>
                        <div class="col-md-12 pt-2 pl-1 pt-0">
                            <div class="col-md-12 border mb-2 py-1 context fondo-blanco">
                                <div class="titulo-seccion-conocimiento">
                                    <span class="fas fa-cogs"></span> Parámetros
                                </div>
                                <div class="table-responsive table-fixed">
                                    <table class="table table-bordered table-striped table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th class="align-middle" rowspan="2">Segmento</th>
                                                <th class="align-middle" rowspan="2">Total Mes</th>
                                                <th class="align-middle" rowspan="2">Peso</th>
                                                <th class="align-middle" rowspan="2">Muestra Calculada</th>
                                                <th class="align-middle" rowspan="2">Muestra Auditoria</th>
                                                <th class="align-middle" colspan="5">Muestra Calculada</th>
                                                <th class="align-middle" colspan="5">Agentes - Monitoreos</th>
                                                <th class="align-middle" colspan="5">Monitoreos Realizados</th>
                                                <th class="align-middle" colspan="5">Muestra Recalculada</th>
                                            </tr>
                                            <tr>
                                                <th class="align-middle">S1</th>
                                                <th class="align-middle">S2</th>
                                                <th class="align-middle">S3</th>
                                                <th class="align-middle">S4</th>
                                                <th class="align-middle">S5</th>
                                                <th class="align-middle">S1</th>
                                                <th class="align-middle">S2</th>
                                                <th class="align-middle">S3</th>
                                                <th class="align-middle">S4</th>
                                                <th class="align-middle">S5</th>
                                                <th class="align-middle">S1</th>
                                                <th class="align-middle">S2</th>
                                                <th class="align-middle">S3</th>
                                                <th class="align-middle">S4</th>
                                                <th class="align-middle">S5</th>
                                                <th class="align-middle">S1</th>
                                                <th class="align-middle">S2</th>
                                                <th class="align-middle">S3</th>
                                                <th class="align-middle">S4</th>
                                                <th class="align-middle">S5</th>
                                            </tr>
                                        </thead>    
                                        <tbody>    
                                            <?php
                                                $total_mes=0;
                                                $total_peso=0;
                                                $total_muestra_calculada=0;
                                                $total_muestra_auditoria=0;
                                                
                                                $total_muestra_s1=0;
                                                $total_muestra_s2=0;
                                                $total_muestra_s3=0;
                                                $total_muestra_s4=0;
                                                $total_muestra_s5=0;

                                                $total_agentes_s1=0;
                                                $total_agentes_s2=0;
                                                $total_agentes_s3=0;
                                                $total_agentes_s4=0;
                                                $total_agentes_s5=0;

                                                $total_monitoreos_agentes_s1=0;
                                                $total_monitoreos_agentes_s2=0;
                                                $total_monitoreos_agentes_s3=0;
                                                $total_monitoreos_agentes_s4=0;
                                                $total_monitoreos_agentes_s5=0;

                                                $total_monitoreos_realizados_s1=0;
                                                $total_monitoreos_realizados_s2=0;
                                                $total_monitoreos_realizados_s3=0;
                                                $total_monitoreos_realizados_s4=0;
                                                $total_monitoreos_realizados_s5=0;

                                                $total_monitoreos_recalculado_s1=0;
                                                $total_monitoreos_recalculado_s2=0;
                                                $total_monitoreos_recalculado_s3=0;
                                                $total_monitoreos_recalculado_s4=0;
                                                $total_monitoreos_recalculado_s5=0;

                                                for ($i=0; $i < count($resultado_registros_segmento); $i++) {
                                                    $total_mes+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['total_mes']);
                                                    $total_peso+=floatval($resultado_registros_segmento[$i][3]);
                                                    $total_muestra_calculada+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['muestra_calculada']);
                                                    $total_muestra_auditoria+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['muestra_auditoria']);
                                                    
                                                    $total_muestra_s1+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['muestra_semanal']);
                                                    $total_muestra_s2+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['muestra_semanal']);
                                                    $total_muestra_s3+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['muestra_semanal']);
                                                    $total_muestra_s4+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['muestra_semanal']);
                                                    $total_muestra_s5+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['muestra_semanal']);

                                                    $total_agentes_s1+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['numero_agentes_semanal']);
                                                    $total_agentes_s2+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['numero_agentes_semanal']);
                                                    $total_agentes_s3+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['numero_agentes_semanal']);
                                                    $total_agentes_s4+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['numero_agentes_semanal']);
                                                    $total_agentes_s5+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['numero_agentes_semanal']);

                                                    $total_monitoreos_agentes_s1+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['monitoreos_agentes_semanal']);
                                                    $total_monitoreos_agentes_s2+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['monitoreos_agentes_semanal']);
                                                    $total_monitoreos_agentes_s3+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['monitoreos_agentes_semanal']);
                                                    $total_monitoreos_agentes_s4+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['monitoreos_agentes_semanal']);
                                                    $total_monitoreos_agentes_s5+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['monitoreos_agentes_semanal']);

                                                    $total_monitoreos_realizados_s1+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['monitoreos_realizados']);
                                                    $total_monitoreos_realizados_s2+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['monitoreos_realizados']);
                                                    $total_monitoreos_realizados_s3+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['monitoreos_realizados']);
                                                    $total_monitoreos_realizados_s4+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['monitoreos_realizados']);
                                                    $total_monitoreos_realizados_s5+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['monitoreos_realizados']);

                                                    $total_monitoreos_recalculado_s1+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['monitoreos_recalculados']);
                                                    $total_monitoreos_recalculado_s2+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['monitoreos_recalculados']);
                                                    $total_monitoreos_recalculado_s3+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['monitoreos_recalculados']);
                                                    $total_monitoreos_recalculado_s4+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['monitoreos_recalculados']);
                                                    $total_monitoreos_recalculado_s5+=floatval($array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['monitoreos_recalculados']);
                                            ?>
                                            <tr>
                                                <td class="align-middle"><?php echo $resultado_registros_segmento[$i][2]; ?></td>
                                                <td class="align-middle text-center"><?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['total_mes']; ?></td>
                                                <td class="align-middle text-center"><?php echo $resultado_registros_segmento[$i][3]; ?>%</td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['muestra_calculada']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['muestra_auditoria']; ?>
                                                </td>

                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['muestra_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['muestra_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['muestra_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['muestra_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['muestra_semanal']; ?>
                                                </td>

                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['numero_agentes_semanal']; ?> - <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['monitoreos_agentes_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['numero_agentes_semanal']; ?> - <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['monitoreos_agentes_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['numero_agentes_semanal']; ?> - <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['monitoreos_agentes_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['numero_agentes_semanal']; ?> - <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['monitoreos_agentes_semanal']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['numero_agentes_semanal']; ?> - <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['monitoreos_agentes_semanal']; ?>
                                                </td>

                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['monitoreos_realizados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['monitoreos_realizados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['monitoreos_realizados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['monitoreos_realizados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['monitoreos_realizados']; ?>
                                                </td>

                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S1']['monitoreos_recalculados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S2']['monitoreos_recalculados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S3']['monitoreos_recalculados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S4']['monitoreos_recalculados']; ?>
                                                </td>
                                                <td class="align-middle text-center p-0">
                                                    <?php echo $array_segmento_detalle[$resultado_registros_segmento[$i][0]]['S5']['monitoreos_recalculados']; ?>
                                                </td>
                                            </tr>
                                            <?php
                                                }
                                            ?>
                                            <tr>
                                                <th class="align-middle">Totales</th>
                                                <th class="align-middle"><?php echo $total_mes; ?></th>
                                                <th class="align-middle"><?php echo $total_peso; ?>%</th>
                                                <th class="align-middle"><?php echo $total_muestra_calculada; ?></th>
                                                <th class="align-middle"><?php echo $total_muestra_auditoria; ?></th>
                                                <th class="align-middle"><?php echo $total_muestra_s1; ?></th>
                                                <th class="align-middle"><?php echo $total_muestra_s2; ?></th>
                                                <th class="align-middle"><?php echo $total_muestra_s3; ?></th>
                                                <th class="align-middle"><?php echo $total_muestra_s4; ?></th>
                                                <th class="align-middle"><?php echo $total_muestra_s5; ?></th>
                                                <th class="align-middle"><?php echo $total_agentes_s1; ?></th>
                                                <th class="align-middle"><?php echo $total_agentes_s2; ?></th>
                                                <th class="align-middle"><?php echo $total_agentes_s3; ?></th>
                                                <th class="align-middle"><?php echo $total_agentes_s4; ?></th>
                                                <th class="align-middle"><?php echo $total_agentes_s5; ?></th>

                                                <th class="align-middle"><?php echo $total_monitoreos_realizados_s1; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_realizados_s2; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_realizados_s3; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_realizados_s4; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_realizados_s5; ?></th>

                                                <th class="align-middle"><?php echo $total_monitoreos_recalculado_s1; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_recalculado_s2; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_recalculado_s3; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_recalculado_s4; ?></th>
                                                <th class="align-middle"><?php echo $total_monitoreos_recalculado_s5; ?></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php for ($i=0; $i < count($resultado_registros_segmento); $i++): ?>
                                <div class="col-md-12 border mb-2 py-1 context fondo-blanco">
                                    <div class="titulo-seccion-conocimiento">
                                        <?php if(($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor")): ?>
                                            <?php if(!isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_malla.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="color-corporativo font-size-11" title="Crear malla"><span class="fas fa-calendar-plus"></span></a>
                                            <?php endif; ?>
                                            <?php if(isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]]) AND !isset($array_muestra[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_malla_eliminar.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="color-rojo font-size-11" title="Eliminar malla"><span class="fas fa-calendar-times"></span></a>
                                            <?php endif; ?>
                                            <?php if(!isset($turno_seg_lider[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_lider.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="color-corporativo font-size-11" title="Configurar líder de calidad"><span class="fas fa-user-tie"></span></a>
                                            <?php endif; ?>
                                            <?php if(isset($turno_seg_lider[$resultado_registros_segmento[$i][0]]) AND !isset($array_muestra[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_lider_eliminar.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="color-rojo font-size-11" title="Eliminar líder de calidad"><span class="fas fa-user-times"></span></a>
                                            <?php endif; ?>
                                            <?php if(isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]]) AND isset($turno_seg_lider[$resultado_registros_segmento[$i][0]]) AND !isset($array_muestra[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_aleatorio.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="color-corporativo font-size-11" title="Generar Aleatorio"><span class="fas fa-random"></span></a>
                                            <?php endif; ?>

                                            <?php if(isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]]) AND isset($turno_seg_lider[$resultado_registros_segmento[$i][0]]) AND isset($array_muestra[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_aleatorio_eliminar.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="color-rojo font-size-11" title="Eliminar Aleatorio"><span class="fas fa-random"></span></a>
                                            <?php endif; ?>

                                            <?php if(isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]]) AND isset($turno_seg_lider[$resultado_registros_segmento[$i][0]]) AND isset($array_muestra[$resultado_registros_segmento[$i][0]])): ?>
                                                <a href="gestion_cmuestral_configurar_aleatorio_excel.php?reg=<?php echo base64_encode($id_registro); ?>&seg=<?php echo base64_encode($resultado_registros_segmento[$i][0]); ?>&date=<?php echo $fecha_calculadora; ?>" class="btn btn-corp py-0 px-1"><div class="float-left"><span class="fas fa-file-excel"></span></div><div class="pl-1 float-left">Muestra Aleatoria</div></a>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                        <?php echo $resultado_registros_segmento[$i][2]; ?>
                                        <?php if (isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]]) OR isset($turno_seg_lider[$resultado_registros_segmento[$i][0]])): ?>
                                            <a href="#"  onclick="mostrar_ocultar_contenido('n_<?php echo $resultado_registros_segmento[$i][0]; ?>');" class="btn btn-corp float-right px-1 py-0"><span class="fas fa-chevron-down" id="n_<?php echo $resultado_registros_segmento[$i][0]; ?>_icono"></span></a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="table-responsive table-fixed d-none" id="n_<?php echo $resultado_registros_segmento[$i][0]; ?>">
                                        <?php if (isset($turno_seg_usuario[$resultado_registros_segmento[$i][0]])): ?>
                                            <?php if (count($turno_seg_usuario[$resultado_registros_segmento[$i][0]])>0): ?>
                                                <table class="table table-bordered table-striped table-hover table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th class="align-middle" colspan="13">Usuarios Muestra</th>
                                                        </tr>
                                                        <tr>
                                                            <th class="align-middle">Documento</th>
                                                            <th class="align-middle">Nombres y Apellidos</th>
                                                            <th class="align-middle">Segmento</th>
                                                            <th class="align-middle">Responsable</th>
                                                            <th class="align-middle">Líder Calidad</th>
                                                            <th class="align-middle">Lunes</th>
                                                            <th class="align-middle">Martes</th>
                                                            <th class="align-middle">Miércoles</th>
                                                            <th class="align-middle">Jueves</th>
                                                            <th class="align-middle">Viernes</th>
                                                            <th class="align-middle">Sábado</th>
                                                            <th class="align-middle">Domingo</th>
                                                            <th class="align-middle">Muestra Aleatoria</th>
                                                        </tr>
                                                    </thead>    
                                                    <tbody>    
                                                        <?php
                                                            for ($j=0; $j < count($turno_seg_usuario[$resultado_registros_segmento[$i][0]]); $j++) {
                                                                $id_usuario_item=$turno_seg_usuario[$resultado_registros_segmento[$i][0]][$j];
                                                        ?>
                                                        <tr>
                                                            <td class="align-middle"><?php echo $id_usuario_item; ?></td>
                                                            <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['nombre']; ?></td>
                                                            <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['segmento']; ?></td>
                                                            <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['coordinador']; ?></td>
                                                            <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['monitor']; ?></td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][0]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][0]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][0]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][1]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][1]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][1]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][2]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][2]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][2]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][3]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][3]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][3]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][4]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][4]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][4]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][5]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][5]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][5]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][6]['color']; ?>; <?php echo ($turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][6]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar[$resultado_registros_segmento[$i][0]][$id_usuario_item][6]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center py-0">
                                                                <?php if($turno_mostrar_datos[$id_usuario_item]['muestra']!=""): ?>
                                                                    <a href="#" onClick="open_modal_detalle('<?php echo base64_encode($turno_mostrar_datos[$id_usuario_item]['id']); ?>');" class="btn btn-secondary btn-sm btn-width" title="Detalle Muestra"><span class="fas fa-eye"></span></a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                            }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <p class="alert alert-warning col-md-12 p-1 font-size-11">
                                                    <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                                                </p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="alert alert-warning col-md-12 p-1 font-size-11">
                                                <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                                            </p>
                                        <?php endif; ?>
                                        <?php if (isset($turno_seg_lider[$resultado_registros_segmento[$i][0]])): ?>
                                            <?php if (count($turno_seg_lider[$resultado_registros_segmento[$i][0]])>0): ?>
                                                <table class="table table-bordered table-striped table-hover table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th class="align-middle" colspan="13">Líderes de Calidad</th>
                                                        </tr>
                                                        <tr>
                                                            <th class="align-middle">Documento</th>
                                                            <th class="align-middle">Nombres y Apellidos</th>
                                                            <th class="align-middle">Lunes</th>
                                                            <th class="align-middle">Martes</th>
                                                            <th class="align-middle">Miércoles</th>
                                                            <th class="align-middle">Jueves</th>
                                                            <th class="align-middle">Viernes</th>
                                                            <th class="align-middle">Sábado</th>
                                                            <th class="align-middle">Domingo</th>
                                                        </tr>
                                                    </thead>    
                                                    <tbody>    
                                                        <?php
                                                            for ($j=0; $j < count($turno_seg_lider[$resultado_registros_segmento[$i][0]]); $j++) {
                                                                $id_usuario_item_lider=$turno_seg_lider[$resultado_registros_segmento[$i][0]][$j];
                                                        ?>
                                                        <tr>
                                                            <td class="align-middle"><?php echo $id_usuario_item_lider; ?></td>
                                                            <td class="align-middle"><?php echo $turno_mostrar_datos_lider[$id_usuario_item_lider]['nombre']; ?></td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][0]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][0]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][0]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][1]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][1]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][1]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][2]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][2]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][2]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][3]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][3]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][3]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][4]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][4]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][4]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][5]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][5]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][5]['turno']; ?>
                                                            </td>
                                                            <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][6]['color']; ?>; <?php echo ($turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][6]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                                                <?php echo $turno_mostrar_lider[$resultado_registros_segmento[$i][0]][$id_usuario_item_lider][6]['turno']; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                            }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <p class="alert alert-warning col-md-12 p-1 font-size-11">
                                                    <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                                                </p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="alert alert-warning col-md-12 p-1 font-size-11">
                                                <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Modal DETALLE -->
        <div class="modal fade" id="modal_ver_detalle" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detalle muestra aleatoria</h4>
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
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#modal_ver_detalle").on('hidden.bs.modal', function () {
                close_modal_detalle();
            });
        });
        
        function open_modal_detalle(id_registro) {
            $('.modal-body-detalle').load('gestion_cmuestral_configurar_malla_editar_calcular_ver.php?reg='+id_registro,function(){
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

        function mostrar_ocultar_contenido(id_elemento){
            if ($("#"+id_elemento).hasClass("d-block")) {
                $("#"+id_elemento).removeClass('d-block').addClass('d-none');
                $("#"+id_elemento+"_icono").removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                $("#"+id_elemento).removeClass('d-none').addClass('d-block');
                $("#"+id_elemento+"_icono").removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        }
    </script>
</body>
</html>