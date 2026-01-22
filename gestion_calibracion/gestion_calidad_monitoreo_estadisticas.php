<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calibración-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    /*DEFINICIÓN DE VARIABLES*/
    $array_meses=[1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];
    $bandeja=validar_input(base64_decode($_GET['bandeja']));
    $titulo_header = "Monitoreos | Estadísticas";
    $pagina=validar_input($_GET['pagina']);

    unset($_SESSION['monitoreo_creado']);
    unset($_SESSION["mon_informacion"]);
    unset($_SESSION['registro_creado_cambio_estado']);
    unset($_SESSION['monitoreo_registro_eliminado']);

    // Inicializa variable tipo array
    $data_consulta=array();
    
    // Ejemplo filtro campo buscar GTO
    if (isset($_POST["filtro"])) {
        $pagina=1;
        $filtro_permanente=validar_input($_POST['id_filtro']);
        $filtro_mes=" AND `gcm_fecha_monitoreo` LIKE ?";
        array_push($data_consulta, "$filtro_permanente%");
    } else {
        $filtro_permanente=date('Y-m');
        $filtro_mes=" AND `gcm_fecha_monitoreo` LIKE ?";
        array_push($data_consulta, "$filtro_permanente%");
    }

    //CONSTRUIR ARRAY AÑO-MES-DIA
        $anio_mes_separado=explode("-", $filtro_permanente);
        $numero_dias_mes = cal_days_in_month(CAL_GREGORIAN, $anio_mes_separado[1], $anio_mes_separado[0]); //cantidad de días del mes
        for ($k=1; $k <= $numero_dias_mes; $k++) { 
            $array_anio_mes_dias_num[]=validar_cero($k);
            $fecha_dia=$filtro_permanente."-".validar_cero($k);
            $array_anio_mes_dias[] = $fecha_dia;
        }

    //CONSULTA GRÁFICA GESTIÓN y RESULTADO INDICADORES
        $consulta_string_gestion="SELECT COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` WHERE 1=1 AND `gcm_aplica_indicador`='Si' ".$filtro_mes."";
        $consulta_registros_gestion = $enlace_db->prepare($consulta_string_gestion);
        $consulta_registros_gestion->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion->execute();
        $resultado_registros_gestion = $consulta_registros_gestion->get_result()->fetch_all(MYSQLI_NUM);

        $consulta_string_gestion_ecuf="SELECT COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` WHERE 1=1 AND `gcm_aplica_indicador`='Si' AND `gcm_nota_ecuf_estado`='0' ".$filtro_mes."";
        $consulta_registros_gestion_ecuf = $enlace_db->prepare($consulta_string_gestion_ecuf);
        $consulta_registros_gestion_ecuf->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_ecuf->execute();
        $resultado_registros_gestion_ecuf = $consulta_registros_gestion_ecuf->get_result()->fetch_all(MYSQLI_NUM);

        $consulta_string_gestion_ecn="SELECT COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` WHERE 1=1 AND `gcm_aplica_indicador`='Si' AND `gcm_nota_ecn_estado`='0' ".$filtro_mes."";
        $consulta_registros_gestion_ecn = $enlace_db->prepare($consulta_string_gestion_ecn);
        $consulta_registros_gestion_ecn->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_ecn->execute();
        $resultado_registros_gestion_ecn = $consulta_registros_gestion_ecn->get_result()->fetch_all(MYSQLI_NUM);

        $consulta_string_gestion_enc="SELECT COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` WHERE 1=1 AND `gcm_aplica_indicador`='Si' AND `gcm_nota_enc_estado`='0' ".$filtro_mes."";
        $consulta_registros_gestion_enc = $enlace_db->prepare($consulta_string_gestion_enc);
        $consulta_registros_gestion_enc->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_enc->execute();
        $resultado_registros_gestion_enc = $consulta_registros_gestion_enc->get_result()->fetch_all(MYSQLI_NUM);

        $array_gestion['monitoreos']=$resultado_registros_gestion[0][0]+0;
        $array_gestion['ecuf']=$resultado_registros_gestion_ecuf[0][0]+0;
        $array_gestion['ecn']=$resultado_registros_gestion_ecn[0][0]+0;
        $array_gestion['enc']=$resultado_registros_gestion_enc[0][0]+0;

        if ($array_gestion['monitoreos']>0) {
            $array_gestion['pecuf']=(($array_gestion['monitoreos']-$array_gestion['ecuf'])/$array_gestion['monitoreos'])*100;
            $array_gestion['pecn']=(($array_gestion['monitoreos']-$array_gestion['ecn'])/$array_gestion['monitoreos'])*100;
            $array_gestion['penc']=(($array_gestion['monitoreos']-$array_gestion['enc'])/$array_gestion['monitoreos'])*100;
        } else {
            $array_gestion['pecuf']=0;
            $array_gestion['pecn']=0;
            $array_gestion['penc']=0;
        }
            
    //CONSULTA GESTIÓN POR MONITOR
        $consulta_string_gestion_monitor="SELECT `gcm_registro_usuario`, TUR.`usu_nombres_apellidos`, COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` LEFT JOIN `tb_administrador_usuario` AS TUR ON `gcm_registro_usuario`=TUR.`usu_id` WHERE 1=1 AND `gcm_aplica_indicador`='Si' ".$filtro_mes." GROUP BY `gcm_registro_usuario` ORDER BY TUR.`usu_nombres_apellidos` ASC";
        $consulta_registros_gestion_monitor = $enlace_db->prepare($consulta_string_gestion_monitor);
        $consulta_registros_gestion_monitor->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_monitor->execute();
        $resultado_registros_gestion_monitor = $consulta_registros_gestion_monitor->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_gestion_monitor); $i++) { 
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['monitoreos']=$resultado_registros_gestion_monitor[$i][2];
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['nombre']=$resultado_registros_gestion_monitor[$i][1];
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['ecuf']=0;
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['ecn']=0;
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['enc']=0;
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['pecuf']=0;
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['pecn']=0;
            $array_gestion_monitores[$resultado_registros_gestion_monitor[$i][0]]['penc']=0;
            $array_gestion_monitores_doc[]=$resultado_registros_gestion_monitor[$i][0];
        }

        $consulta_string_gestion_ecuf_monitor="SELECT `gcm_registro_usuario`, TUR.`usu_nombres_apellidos`, COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` LEFT JOIN `tb_administrador_usuario` AS TUR ON `gcm_registro_usuario`=TUR.`usu_id` WHERE 1=1 AND `gcm_aplica_indicador`='Si' AND `gcm_nota_ecuf_estado`='0' ".$filtro_mes." GROUP BY `gcm_registro_usuario`";
        $consulta_registros_gestion_ecuf_monitor = $enlace_db->prepare($consulta_string_gestion_ecuf_monitor);
        $consulta_registros_gestion_ecuf_monitor->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_ecuf_monitor->execute();
        $resultado_registros_gestion_ecuf_monitor = $consulta_registros_gestion_ecuf_monitor->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_gestion_ecuf_monitor); $i++) { 
            $array_gestion_monitores[$resultado_registros_gestion_ecuf_monitor[$i][0]]['ecuf']=$resultado_registros_gestion_ecuf_monitor[$i][2]+0;
        }

        $consulta_string_gestion_ecn_monitor="SELECT `gcm_registro_usuario`, TUR.`usu_nombres_apellidos`, COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` LEFT JOIN `tb_administrador_usuario` AS TUR ON `gcm_registro_usuario`=TUR.`usu_id` WHERE 1=1 AND `gcm_aplica_indicador`='Si' AND `gcm_nota_ecn_estado`='0' ".$filtro_mes." GROUP BY `gcm_registro_usuario`";
        $consulta_registros_gestion_ecn_monitor = $enlace_db->prepare($consulta_string_gestion_ecn_monitor);
        $consulta_registros_gestion_ecn_monitor->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_ecn_monitor->execute();
        $resultado_registros_gestion_ecn_monitor = $consulta_registros_gestion_ecn_monitor->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_gestion_ecn_monitor); $i++) { 
            $array_gestion_monitores[$resultado_registros_gestion_ecn_monitor[$i][0]]['ecn']=$resultado_registros_gestion_ecn_monitor[$i][2];
        }

        $consulta_string_gestion_enc_monitor="SELECT `gcm_registro_usuario`, TUR.`usu_nombres_apellidos`, COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` LEFT JOIN `tb_administrador_usuario` AS TUR ON `gcm_registro_usuario`=TUR.`usu_id` WHERE 1=1 AND `gcm_aplica_indicador`='Si' AND `gcm_nota_enc_estado`='0' ".$filtro_mes." GROUP BY `gcm_registro_usuario`";
        $consulta_registros_gestion_enc_monitor = $enlace_db->prepare($consulta_string_gestion_enc_monitor);
        $consulta_registros_gestion_enc_monitor->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_gestion_enc_monitor->execute();
        $resultado_registros_gestion_enc_monitor = $consulta_registros_gestion_enc_monitor->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_gestion_enc_monitor); $i++) { 
            $array_gestion_monitores[$resultado_registros_gestion_enc_monitor[$i][0]]['enc']=$resultado_registros_gestion_enc_monitor[$i][2];
        }

        for ($i=0; $i < count($array_gestion_monitores_doc); $i++) { 
            if ($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos']>0) {
                $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['pecuf']=(($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos']-$array_gestion_monitores[$array_gestion_monitores_doc[$i]]['ecuf'])/$array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos'])*100;
                $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['pecn']=(($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos']-$array_gestion_monitores[$array_gestion_monitores_doc[$i]]['ecn'])/$array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos'])*100;
                $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['penc']=(($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos']-$array_gestion_monitores[$array_gestion_monitores_doc[$i]]['enc'])/$array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos'])*100;
            } else {
                $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['pecuf']=0;
                $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['pecn']=0;
                $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['penc']=0;
            }
        }

    //CONSULTA GESTIÓN POR MONITOR-FECHA
        $array_semanas['total_1']=0;
        $array_semanas['total_2']=0;
        $array_semanas['total_3']=0;
        $array_semanas['total_4']=0;

        $consulta_string_monitor_dia="SELECT `gcm_registro_usuario`, TUR.`usu_nombres_apellidos`, `gcm_fecha_monitoreo`, COUNT(`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` LEFT JOIN `tb_administrador_usuario` AS TUR ON `gcm_registro_usuario`=TUR.`usu_id` WHERE 1=1 AND `gcm_aplica_indicador`='Si' ".$filtro_mes." GROUP BY `gcm_registro_usuario`, `gcm_fecha_monitoreo` ORDER BY TUR.`usu_nombres_apellidos` ASC, `gcm_fecha_monitoreo` ASC";
        $consulta_registros_monitor_dia = $enlace_db->prepare($consulta_string_monitor_dia);
        $consulta_registros_monitor_dia->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        $consulta_registros_monitor_dia->execute();
        $resultado_registros_monitor_dia = $consulta_registros_monitor_dia->get_result()->fetch_all(MYSQLI_NUM);

        for ($i=0; $i < count($resultado_registros_monitor_dia); $i++) { 
            $array_monitor_dia[$resultado_registros_monitor_dia[$i][0]]['monitoreos'][$resultado_registros_monitor_dia[$i][2]]=$resultado_registros_monitor_dia[$i][3];
            $array_monitor_dia[$resultado_registros_monitor_dia[$i][0]]['nombre']=$resultado_registros_monitor_dia[$i][1];
            $array_monitor_dia_doc[]=$resultado_registros_monitor_dia[$i][0];

            $dia_recorre=intval(date('d', strtotime($resultado_registros_monitor_dia[$i][2])));

            if ($dia_recorre>=1 AND $dia_recorre<=6) {
                $array_semanas['total_1']+=$resultado_registros_monitor_dia[$i][3]+0;
            }

            if ($dia_recorre>=7 AND $dia_recorre<=13) {
                $array_semanas['total_2']+=$resultado_registros_monitor_dia[$i][3]+0;
            }

            if ($dia_recorre>=14 AND $dia_recorre<=20) {
                $array_semanas['total_3']+=$resultado_registros_monitor_dia[$i][3]+0;
            }

            if ($dia_recorre>=21) {
                $array_semanas['total_4']+=$resultado_registros_monitor_dia[$i][3]+0;
            }
        }

        for ($i=0; $i < count($resultado_registros_monitor_dia); $i++) { 
            for ($j=0; $j < count($array_anio_mes_dias); $j++) { 
                $array_monitor_dia[$resultado_registros_monitor_dia[$i][0]]['monitoreos'][$array_anio_mes_dias[$j]]+=0;
            }
        }

        $array_monitor_dia_doc=array_values(array_unique($array_monitor_dia_doc));

        $array_semanas['rango_1']='01 al 06 de '.$filtro_permanente;
        $array_semanas['rango_2']='07 al 13 de '.$filtro_permanente;
        $array_semanas['rango_3']='14 al 20 de '.$filtro_permanente;
        $array_semanas['rango_4']='21 al '.$numero_dias_mes.' de '.$filtro_permanente;

    $consulta_string="SELECT TMC.`gcm_matriz`, TM.`gcm_nombre_matriz`, TMC.`gcm_registro_usuario`, TUR.`usu_nombres_apellidos`, COUNT(TMC.`gcm_id`) FROM `tb_gestion_calibracion_monitoreo` AS TMC LEFT JOIN `tb_gestion_calibracion_matriz` AS TM ON TMC.`gcm_matriz`=TM.`gcm_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON TMC.`gcm_registro_usuario`=TUR.`usu_id` WHERE 1=1 ".$filtro_mes." GROUP BY TMC.`gcm_matriz`, TMC.`gcm_registro_usuario`";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros); $i++) { 
        $array_usuarios[]=$resultado_registros[$i][2];
        $array_matrices[]=$resultado_registros[$i][0];
        $array_usuarios_detalle[$resultado_registros[$i][2]]['nombre']=$resultado_registros[$i][3];
        $array_matrices_detalle[$resultado_registros[$i][0]]['nombre_matriz']=$resultado_registros[$i][1];
        $array_matrices_detalle[$resultado_registros[$i][0]]['cantidad']+=$resultado_registros[$i][4];
        $array_usuario_monitoreos[$resultado_registros[$i][0]][$resultado_registros[$i][2]]+=$resultado_registros[$i][4];
    }

    $array_usuarios=array_values(array_unique($array_usuarios));
    $array_matrices=array_values(array_unique($array_matrices));

    for ($i=0; $i < count($array_matrices); $i++) { 
        for ($j=0; $j < count($array_usuarios); $j++) { 
            $array_usuario_monitoreos[$array_matrices[$i]][$array_usuarios[$j]]+=0;
        }
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
    <script src="../Highcharts/code/highcharts.js"></script>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido" style="background-color: #EEEEEE;">
        <div class="row" id="elemento_1">
            <div class="col-md-3 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                      <input type="month" name="id_filtro" value='<?php if (isset($_POST["filtro"])) { echo $_POST['id_filtro']; } else {if($filtro_permanente!="null"){echo $filtro_permanente;}} ?>' placeholder="Búsqueda" class="form-control" required autofocus>
                      <span class="input-group-btn">
                        <button class="btn btn-corp" type="submit" name="filtro"><span class="fas fa-search"></span></button>
                        <a href="gestion_calidad_monitoreo_estadisticas.php?pagina=1&id=null&bandeja=<?php echo base64_encode($bandeja); ?>" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                      </span>
                    </div>
                </form>
            </div>
            <div class="col-md-9 py-2">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <a href="gestion_calidad_monitoreo.php?pagina=1&id=null&bandeja=<?php echo base64_encode('Histórico'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-history"></span></div><div class="pl-2 menu_res float-left">Monitoreos Histórico</div></a>
                    <a href="gestion_calidad_monitoreo.php?pagina=1&id=null&bandeja=<?php echo base64_encode('Refutados'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-user-times"></span></div><div class="pl-2 menu_res float-left">Refutados</div></a>
                    <a href="gestion_calidad_monitoreo.php?pagina=1&id=null&bandeja=<?php echo base64_encode('Mes Actual'); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-clipboard-list"></span></div><div class="pl-2 menu_res float-left">Monitoreos Mes Actual</div></a>
                    <?php if($perfil_modulo=="Administrador"): ?>
                        <a href="gestion_calidad_monitoreo_estadisticas.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&bandeja=<?php echo base64_encode($bandeja); ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-chart-pie"></span></div><div class="pl-2 menu_res float-left">Estadísticas</div></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="col-md-12">
                <?php if (count($resultado_registros)>0): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_gestion"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_resultado_indicadores"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_gestion_monitor"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_monitor_dia"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_matriz"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_semana"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row p-1">
                                <div class="col-md-12 p-1 fondo-blanco radius-5">
                                    <div id="grafica_monitoreos_matriz"></div>
                                </div>
                            </div>
                        </div>
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
        Highcharts.chart('grafica_gestion', {
            chart: {
                type: 'column',
                height: 300
            },
            title: {
                text: 'Gestión General | <?php echo $array_meses[intval(date('m', strtotime($filtro_permanente)))]; ?> <?php echo date('Y', strtotime($filtro_permanente)); ?>',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            xAxis: {
                categories: ['Monitoreos', 'ECUF', 'ECN', 'ENC'],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Cantidad monitoreos',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}: <b>{point.y}</span>',
                pointFormat: '',
                footerFormat: '',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: false,
            credits: {
                enabled: false
            },
            series: [{
                name: 'Casos',
                colorByPoint: true,
                data: [<?php echo $array_gestion['monitoreos']; ?>, <?php echo $array_gestion['ecuf']; ?>, <?php echo $array_gestion['ecn']; ?>, <?php echo $array_gestion['enc']; ?>]
            }]
        });

        Highcharts.chart('grafica_resultado_indicadores', {
            chart: {
                type: 'bar',
                height: 300
            },
            title: {
                text: 'Resultado Indicadores General | <?php echo $array_meses[intval(date('m', strtotime($filtro_permanente)))]; ?> <?php echo date('Y', strtotime($filtro_permanente)); ?>',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            xAxis: {
                categories: ['PENC', 'PECN', 'PECUF'],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                max: 100,
                title: {
                    text: 'Porcentaje',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}: <b>{point.y} %</span>',
                pointFormat: '',
                footerFormat: '',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: false,
            credits: {
                enabled: false
            },
            series: [{
                name: 'Porcentaje',
                colorByPoint: true,
                data: [<?php echo number_format($array_gestion['penc'], 2, '.', ''); ?>, <?php echo number_format($array_gestion['pecn'], 2, '.', ''); ?>, <?php echo number_format($array_gestion['pecuf'], 2, '.', ''); ?>]
            }]
        });

        Highcharts.chart('grafica_gestion_monitor', {
            chart: {
                zoomType: 'xy',
                height: 500,
            },
            title: {
                text: 'Indicadores Monitor | <?php echo $array_meses[intval(date('m', strtotime($filtro_permanente)))]; ?> <?php echo date('Y', strtotime($filtro_permanente)); ?>',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            credits: {
                enabled: false
            },
            xAxis: [{
                categories: [
                            <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                                '<?php echo $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['nombre']; ?>',
                            <?php endfor; ?>
                            ],
                crosshair: true
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    format: '{value}',
                    style: {
                        color: Highcharts.getOptions().colors[2]
                    }
                },
                title: {
                    text: 'Porcentaje',
                    style: {
                        color: Highcharts.getOptions().colors[2]
                    }
                },
                opposite: true,
            }, { // Secondary yAxis
                gridLineWidth: 0,
                title: {
                    text: 'Cantidad monitoreos',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                labels: {
                    format: '{value}',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                }

            }],
            tooltip: {
                shared: true
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                x: 0,
                verticalAlign: 'top',
                y: -20,
                floating: false,
                backgroundColor:
                    Highcharts.defaultOptions.legend.backgroundColor || // theme
                    'rgba(255,255,255,0.25)'
            },
            series: [{
                name: 'Monitoreos',
                type: 'column',
                yAxis: 1,
                color: '#4472C4',
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['monitoreos']; ?>,
                        <?php endfor; ?>
                ],
                dataLabels: {
                    enabled: false,
                    inside: true,
                    rotation: 270,
                    align: 'left',
                    verticalAlign: 'bottom',
                    y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                tooltip: {
                    valueSuffix: ''
                }

            },{
                name: 'ECUF',
                type: 'column',
                yAxis: 1,
                color: '#ED7D31',
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['ecuf']; ?>,
                        <?php endfor; ?>
                ],
                dataLabels: {
                    enabled: false,
                    inside: true,
                    rotation: 270,
                    align: 'left',
                    verticalAlign: 'bottom',
                    y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                tooltip: {
                    valueSuffix: ''
                }

            },{
                name: 'ECN',
                type: 'column',
                yAxis: 1,
                color: '#A5A5A5',
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['ecn']; ?>,
                        <?php endfor; ?>
                ],
                dataLabels: {
                    enabled: false,
                    inside: true,
                    rotation: 270,
                    align: 'left',
                    verticalAlign: 'bottom',
                    y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                tooltip: {
                    valueSuffix: ''
                }

            },{
                name: 'ENC',
                type: 'column',
                yAxis: 1,
                color: '#FFC000',
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo $array_gestion_monitores[$array_gestion_monitores_doc[$i]]['enc']; ?>,
                        <?php endfor; ?>
                ],
                dataLabels: {
                    enabled: false,
                    inside: true,
                    rotation: 270,
                    align: 'left',
                    verticalAlign: 'bottom',
                    y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                tooltip: {
                    valueSuffix: ''
                }

            }, {
                name: '% PECUF',
                type: 'spline',
                color: '#5B9BD5',
                yAxis: 0,
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo number_format($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['pecuf'], 2, '.', ''); ?>,
                        <?php endfor; ?>
                ],
                marker: {
                    enabled: true
                },
                dataLabels: {
                    enabled: false,
                    inside: false,
                    // rotation: 270,
                    // align: 'left',
                    // verticalAlign: 'bottom',
                    // y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                dashStyle: 'shortdot',
                tooltip: {
                    valueSuffix: ' %'
                }

            }, {
                name: '% PECN',
                type: 'spline',
                color: '#70AD47',
                yAxis: 0,
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo number_format($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['pecn'], 2, '.', ''); ?>,
                        <?php endfor; ?>
                ],
                marker: {
                    enabled: true
                },
                dataLabels: {
                    enabled: false,
                    inside: false,
                    // rotation: 270,
                    // align: 'left',
                    // verticalAlign: 'bottom',
                    // y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                dashStyle: 'shortdot',
                tooltip: {
                    valueSuffix: ' %'
                }

            }, {
                name: '% PENC',
                type: 'spline',
                color: '#264478',
                yAxis: 0,
                data: [
                        <?php for ($i=0; $i < count($array_gestion_monitores_doc); $i++): ?>
                            <?php echo number_format($array_gestion_monitores[$array_gestion_monitores_doc[$i]]['penc'], 2, '.', ''); ?>,
                        <?php endfor; ?>
                ],
                marker: {
                    enabled: true
                },
                dataLabels: {
                    enabled: false,
                    inside: false,
                    // rotation: 270,
                    // align: 'left',
                    // verticalAlign: 'bottom',
                    // y: -5,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal'
                    }
                },
                dashStyle: 'shortdot',
                tooltip: {
                    valueSuffix: ' %'
                }

            }],
        });
        
        Highcharts.chart('grafica_monitor_dia', {
            chart: {
                type: 'spline',
                height: 300
            },
            title: {
                text: 'Gestión Diaria Monitor | <?php echo $array_meses[intval(date('m', strtotime($filtro_permanente)))]; ?> <?php echo date('Y', strtotime($filtro_permanente)); ?>',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            xAxis: {
                categories: [
                            <?php for ($i=0; $i < count($array_anio_mes_dias_num); $i++): ?>
                                '<?php echo $array_anio_mes_dias_num[$i]; ?>',    
                            <?php endfor; ?>
                            ],
            },
            yAxis: {
                title: {
                    text: 'Cantidad monitoreos'
                }
            },
            tooltip: {
                shared: true,
                style: {
                    fontSize: '10px'
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                itemStyle: {
                    fontWeight: 'normal',
                    fontSize: '10px'
                }
            },
            plotOptions: {
                spline: {
                    dataLabels: {
                        enabled: false
                    },
                }
            },
            credits: {
                enabled: false
            },
            series: [
            <?php for ($i=0; $i < count($array_monitor_dia_doc); $i++): ?>
                {
                    name: '<?php echo $array_monitor_dia[$array_monitor_dia_doc[$i]]['nombre']; ?>',
                    data: [
                        <?php for ($j=0; $j < count($array_anio_mes_dias_num); $j++): ?>    
                            <?php echo $array_monitor_dia[$array_monitor_dia_doc[$i]]['monitoreos'][$array_anio_mes_dias[$j]]; ?>,
                        <?php endfor; ?>
                        ]
                },
            <?php endfor; ?>
            ],
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        });

        Highcharts.chart('grafica_matriz', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie',
                height: 300,
            },
            credits: {
                enabled: false
            },
            title: {
                text: 'Monitoreos por Matriz',
                style: {
                    fontSize: '14px'
                }
            },
            tooltip: {
                pointFormat: '<b>{point.y}</b> ({point.percentage:.1f}%)'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}: {point.y} [{point.percentage:.1f} %]'
                    }
                }
            },
            series: [{
                colorByPoint: true,
                data: [
                <?php for ($i=0; $i < count($array_matrices); $i++): ?>
                {
                    name: '<?php echo $array_matrices_detalle[$array_matrices[$i]]['nombre_matriz']; ?>',
                    y: <?php echo $array_matrices_detalle[$array_matrices[$i]]['cantidad']; ?>,
                },
                <?php endfor; ?>
                ]
            }]
        });

        Highcharts.chart('grafica_semana', {
            chart: {
                type: 'spline',
                height: 300
            },
            title: {
                text: '% Participación Monitoreos Semanal | <?php echo $array_meses[intval(date('m', strtotime($filtro_permanente)))]; ?> <?php echo date('Y', strtotime($filtro_permanente)); ?>',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            xAxis: {
                categories: ['<?php echo $array_semanas['rango_1']; ?>', '<?php echo $array_semanas['rango_2']; ?>', '<?php echo $array_semanas['rango_3']; ?>', '<?php echo $array_semanas['rango_4']; ?>'],
            },
            yAxis: {
                title: {
                    text: 'Cantidad monitoreos'
                }
            },
            tooltip: {
                shared: true,
                style: {
                    fontSize: '10px'
                }
            },
            legend: {
                layout: 'vertical',
                align: 'center',
                verticalAlign: 'top',
                itemStyle: {
                    fontWeight: 'normal',
                    fontSize: '10px'
                }
            },
            plotOptions: {
                spline: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            var total = <?php echo $array_semanas['total_1']; ?> + <?php echo $array_semanas['total_2']; ?> + <?php echo $array_semanas['total_3']; ?> + <?php echo $array_semanas['total_4']; ?>;
                            var porcentaje = (this.y/total)*100;
                            return this.y + ' [' + porcentaje.toFixed(2) + '%]';
                        }
                        // format: '{point.y} [{point.percentage:.1f} %]'
                    }
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                    name: 'Cantidad monitoreos',
                    data: [<?php echo $array_semanas['total_1']; ?>, <?php echo $array_semanas['total_2']; ?>, <?php echo $array_semanas['total_3']; ?>, <?php echo $array_semanas['total_4']; ?>]
                }]
        });

        Highcharts.chart('grafica_monitoreos_matriz', {
            chart: {
                type: 'bar',
                height: <?php echo (count($array_usuarios)<3)? 200 : count($array_usuarios)*50; ?>
            },
            title: {
                text: 'Gestión Monitores por Matriz | <?php echo $array_meses[intval(date('m', strtotime($filtro_permanente)))]; ?> <?php echo date('Y', strtotime($filtro_permanente)); ?>',
                style: {
                    fontSize: '14px'
                }
            },
            subtitle: {
                text: null
            },
            xAxis: {
                categories: [
                            <?php for ($i=0; $i < count($array_usuarios); $i++): ?>
                                '<?php echo $array_usuarios_detalle[$array_usuarios[$i]]['nombre']; ?>',
                            <?php endfor; ?>
                ],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Cantidad monitoreos',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: ' monitoreos',
                shared: true
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'top',
                x: 0,
                y: -20,
                floating: false,
                borderWidth: 0,
                backgroundColor:
                    Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
                shadow: false
            },
            credits: {
                enabled: false
            },
            series: [
                <?php for ($i=0; $i < count($array_matrices); $i++): ?>
                    {
                        name: '<?php echo $array_matrices_detalle[$array_matrices[$i]]['nombre_matriz']; ?>',
                        data: [
                            <?php for ($j=0; $j < count($array_usuarios); $j++): ?>
                                <?php echo $array_usuario_monitoreos[$array_matrices[$i]][$array_usuarios[$j]]; ?>,
                            <?php endfor; ?>
                            ]
                    },
                <?php endfor; ?>
            ]
        });
    </script>
</body>
</html>