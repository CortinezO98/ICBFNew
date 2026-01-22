<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Alertas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /* =========================
       Helpers de seguridad (local)
       ========================= */
    if (!function_exists('e')) {
        function e($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Gestión Alertas | Estadísticas";

    // Inicializa variables tipo array
    $data_consulta = array();
    $array_czonal = array();
    $array_czonal_data = array();

    // Filtros
    if (isset($_POST["filtro"])) {
        $fecha_inicio = isset($_POST['fecha_inicio']) ? validar_input($_POST['fecha_inicio']) : '';
        $fecha_fin    = isset($_POST['fecha_fin']) ? validar_input($_POST['fecha_fin']) : '';
        $afectalt     = isset($_POST['afectalt']) ? validar_input($_POST['afectalt']) : 'Si';
    } else {
        $fecha_inicio = date('Y-m')."-01";
        $fecha_fin    = date('Y-m-d');
        $afectalt     = "Si";
    }

    // Whitelist (no afecta funcionalidad: solo "Si"/"No" existen en UI)
    if ($afectalt !== 'Si' && $afectalt !== 'No') {
        $afectalt = 'Si';
    }

    $fecha_fin_hasta = $fecha_fin . " 23:59:59";

    /* =========================
       Consultas REMEDIADAS (SQLi)
       ========================= */

    // Regionales
    $resultado_regionales = [];
    $sql_regionales = "SELECT ga_regional, TR.gere_regional, COUNT(ga_id) AS TOTAL
        FROM tb_gestion_alertas
        LEFT JOIN tb_gestion_encuesta_regional AS TR ON tb_gestion_alertas.ga_regional=TR.gere_id
        LEFT JOIN tb_gestion_encuesta_regional_czonal AS TCZ ON tb_gestion_alertas.ga_centro_zonal=TCZ.gercz_id
        WHERE ga_afecta_linea_tecnica=?
          AND ga_fecha_require_regional<>''
          AND ga_fecha_alerta>=?
          AND ga_fecha_alerta<=?
        GROUP BY ga_regional
        ORDER BY TOTAL DESC, TR.gere_regional, TCZ.gercz_centro_zonal";

    if ($stmt = $enlace_db->prepare($sql_regionales)) {
        $stmt->bind_param("sss", $afectalt, $fecha_inicio, $fecha_fin_hasta);
        $stmt->execute();
        $resultado_regionales = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        $stmt->close();
    }

    // Centro zonales
    $resultado_czonales = [];
    $sql_czonales = "SELECT ga_regional, TR.gere_regional, ga_centro_zonal, TCZ.gercz_centro_zonal, COUNT(ga_id) AS TOTAL
        FROM tb_gestion_alertas
        LEFT JOIN tb_gestion_encuesta_regional AS TR ON tb_gestion_alertas.ga_regional=TR.gere_id
        LEFT JOIN tb_gestion_encuesta_regional_czonal AS TCZ ON tb_gestion_alertas.ga_centro_zonal=TCZ.gercz_id
        WHERE ga_afecta_linea_tecnica=?
          AND ga_fecha_require_regional<>''
          AND ga_fecha_alerta>=?
          AND ga_fecha_alerta<=?
        GROUP BY ga_regional, ga_centro_zonal
        ORDER BY TOTAL DESC, TR.gere_regional, TCZ.gercz_centro_zonal";

    if ($stmt = $enlace_db->prepare($sql_czonales)) {
        $stmt->bind_param("sss", $afectalt, $fecha_inicio, $fecha_fin_hasta);
        $stmt->execute();
        $resultado_czonales = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        $stmt->close();
    }

    // Armado arrays para drilldown
    for ($i=0; $i < count($resultado_czonales); $i++) {
        $regional_nombre = (string)$resultado_czonales[$i][1];
        $czonal_nombre   = (string)$resultado_czonales[$i][3];
        $cantidad        = (int)$resultado_czonales[$i][4];

        if (!isset($array_czonal[$regional_nombre])) {
            $array_czonal[$regional_nombre] = [];
        }
        $array_czonal[$regional_nombre][] = $czonal_nombre;

        if (!isset($array_czonal_data[$regional_nombre])) {
            $array_czonal_data[$regional_nombre] = [];
        }
        if (!isset($array_czonal_data[$regional_nombre][$czonal_nombre])) {
            $array_czonal_data[$regional_nombre][$czonal_nombre] = [];
        }
        $array_czonal_data[$regional_nombre][$czonal_nombre]['cantidad'] = $cantidad;
    }

    // Listado HTML (XSS: se escapan valores al construir)
    if (count($resultado_regionales)>0) {
        $lista_informe='<table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle">Regional</th>
                                    <th class="align-middle">Total Alertas</th>
                                </tr>
                            </thead>
                            <tbody>';
        $total_regionales=0;

        for ($i=0; $i < count($resultado_regionales); $i++) {
            $reg_name = e($resultado_regionales[$i][1]);
            $reg_total = (int)$resultado_regionales[$i][2];

            $lista_informe.='<tr>
                            <td class="align-middle">'.$reg_name.'</td>
                            <td class="align-middle text-center">'.$reg_total.'</td>
                            </tr>';
            $total_regionales += $reg_total;
        }

        $lista_informe.='<tr>
                            <td class="align-middle font-weight-bold">Total</td>
                            <td class="align-middle text-center font-weight-bold">'.$total_regionales.'</td>
                            </tr>';

        $lista_informe.='</tbody>
                        </table><br>';

        $lista_informe.='<table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="align-middle">Regional/Centro Zonal</th>
                                    <th class="align-middle">Total Alertas</th>
                                </tr>
                            </thead>
                            <tbody>';

        for ($i=0; $i < count($resultado_regionales); $i++) {
            $reg_raw  = (string)$resultado_regionales[$i][1];
            $reg_name = e($reg_raw);
            $reg_total = (int)$resultado_regionales[$i][2];

            $lista_informe.='<tr>
                            <td class="align-middle font-weight-bold">'.$reg_name.'</td>
                            <td class="align-middle text-center font-weight-bold">'.$reg_total.'</td>
                            </tr>';

            $cz_list = $array_czonal[$reg_raw] ?? [];
            for ($j=0; $j < count($cz_list); $j++) {
                $cz_raw = (string)$cz_list[$j];
                $cz_name = e($cz_raw);
                $cz_cant = (int)($array_czonal_data[$reg_raw][$cz_raw]['cantidad'] ?? 0);

                $lista_informe.='<tr>
                                <td class="align-middle pl-4 font-size-11">'.$cz_name.'</td>
                                <td class="align-middle text-center font-size-11">'.$cz_cant.'</td>
                                </tr>';
            }
        }

        $lista_informe.='<tr>
                            <td class="align-middle font-weight-bold">Total</td>
                            <td class="align-middle text-center font-weight-bold">'.$total_regionales.'</td>
                            </tr>';

        $lista_informe.='</tbody>
                        </table>';
    } else {
        $lista_informe='<p class="alert alert-warning col-md-12 p-1 font-size-11 mb-1">
                            <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                        </p>';
    }

    // Acción de mejora (remediado SQLi)
    $resultado_accion_mejora = [];
    $sql_accion_mejora = "SELECT ga_accion_mejora, COUNT(ga_id)
        FROM tb_gestion_alertas
        WHERE ga_afecta_linea_tecnica='Si'
          AND ga_accion_mejora<>''
          AND ga_fecha_alerta>=?
          AND ga_fecha_alerta<=?
        GROUP BY ga_accion_mejora";

    if ($stmt = $enlace_db->prepare($sql_accion_mejora)) {
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_hasta);
        $stmt->execute();
        $resultado_accion_mejora = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        $stmt->close();
    }

    $array_accion_mejora = ['Si'=>0, 'No'=>0];
    for ($i=0; $i < count($resultado_accion_mejora); $i++) {
        $key = (string)$resultado_accion_mejora[$i][0];
        $val = (int)$resultado_accion_mejora[$i][1];
        if (!isset($array_accion_mejora[$key])) {
            $array_accion_mejora[$key] = 0;
        }
        $array_accion_mejora[$key] += $val;
    }

    // Afecta LT (remediado SQLi)
    $resultado_afectalt = [];
    $sql_afectalt = "SELECT ga_afecta_linea_tecnica, COUNT(ga_id)
        FROM tb_gestion_alertas
        WHERE ga_afecta_linea_tecnica<>''
          AND ga_estado<>'Pendiente RN2'
          AND ga_fecha_alerta>=?
          AND ga_fecha_alerta<=?
        GROUP BY ga_afecta_linea_tecnica";

    if ($stmt = $enlace_db->prepare($sql_afectalt)) {
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_hasta);
        $stmt->execute();
        $resultado_afectalt = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        $stmt->close();
    }

    $array_afectalt = ['Si'=>0, 'No'=>0];
    for ($i=0; $i < count($resultado_afectalt); $i++) {
        $key = (string)$resultado_afectalt[$i][0];
        $val = (int)$resultado_afectalt[$i][1];
        if (!isset($array_afectalt[$key])) {
            $array_afectalt[$key] = 0;
        }
        $array_afectalt[$key] += $val;
    }

    /* =========================
       Data segura para JS (XSS/JS injection)
       ========================= */
    $hc_series_regionales = [];
    $hc_drilldown_series  = [];

    for ($i=0; $i < count($resultado_regionales); $i++) {
        $reg_raw   = (string)$resultado_regionales[$i][1];
        $reg_total = (int)$resultado_regionales[$i][2];

        $hc_series_regionales[] = [
            'name'      => $reg_raw,
            'y'         => $reg_total,
            'drilldown' => $reg_raw
        ];

        $cz_list = $array_czonal[$reg_raw] ?? [];
        $dd_data = [];
        for ($j=0; $j < count($cz_list); $j++) {
            $cz_raw = (string)$cz_list[$j];
            $cz_cant = (int)($array_czonal_data[$reg_raw][$cz_raw]['cantidad'] ?? 0);
            $dd_data[] = [$cz_raw, $cz_cant];
        }

        $hc_drilldown_series[] = [
            'name' => $reg_raw,
            'id'   => $reg_raw,
            'data' => $dd_data
        ];
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php include("../config/configuracion_estilos.php"); ?>
    <script src="../Highcharts/code/highcharts.js"></script>
    <script src="../Highcharts/code/modules/drilldown.js"></script>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row" id="elemento_1">
            <div class="col-md-6 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                        <label class="pt-1 pr-2">Afecta LT:</label>
                        <select class="form-control form-control-sm" name="afectalt" id="afectalt" required style="max-width: 100px;">
                          <option value="Si" <?php if($afectalt=="Si"){ echo "selected"; } ?>>Si</option>
                          <option value="No" <?php if($afectalt=="No"){ echo "selected"; } ?>>No</option>
                        </select>
                        <label class="pt-1 pr-2 pl-2">Inicio:</label>
                        <input type="date"
                               name="fecha_inicio"
                               value='<?php echo e(isset($_POST["filtro"]) ? ($_POST['fecha_inicio'] ?? '') : ($fecha_inicio!="null" ? $fecha_inicio : "")); ?>'
                               class="form-control" required autofocus>
                        <label class="pt-1 pr-2 pl-2">Fin:</label>
                        <input type="date"
                               name="fecha_fin"
                               value='<?php echo e(isset($_POST["filtro"]) ? ($_POST['fecha_fin'] ?? '') : ($fecha_fin!="null" ? $fecha_fin : "")); ?>'
                               class="form-control" required>
                        <span class="input-group-btn">
                        <button class="btn btn-corp" type="submit" name="filtro"><span class="fas fa-search"></span></button>
                        </span>
                    </div>
                </form>
            </div>
            <div class="col-md-9 py-2"></div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-3">
                <?php echo $lista_informe; ?>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-12">
                        <div id="gra_regionales" style="min-width: 310px; height: 300px; margin: 0 auto;"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="gra_accion_mejora" style="min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto;"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="gra_afectalt" style="min-width: 310px; height: 300px; max-width: 600px; margin: 0 auto;"></div>
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
        Highcharts.chart('gra_regionales', {
            chart: { type: 'column' },
            title: {
                text: 'ALERTAS REGIONAL/CENTRO ZONAL',
                style: { fontSize: '14px' }
            },
            credits: { enabled: false },
            subtitle: { text: null },
            xAxis: { type: 'category' },
            yAxis: {
                title: { text: 'Cantidad Alertas' }
            },
            legend: { enabled: false },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: { enabled: true, format: '{point.y:.0f}' }
                }
            },
            tooltip: { pointFormat: 'Cantidad: <b>{point.y}</b>' },
            series: [{
                name: "Regionales",
                colorByPoint: true,
                data: <?php echo json_encode($hc_series_regionales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
            }],
            drilldown: {
                series: <?php echo json_encode($hc_drilldown_series, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
            }
        });

        Highcharts.chart('gra_accion_mejora', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'APLICA ACCIÓN DE MEJORA',
                style: { fontSize: '14px' }
            },
            credits: { enabled: false },
            subtitle: { text: null },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br>Cantidad: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        distance: 5,
                        format: '{point.percentage:.1f} % <br>{point.y}'
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Porcentaje',
                colorByPoint: true,
                data: [{
                    name: 'Acción de mejora',
                    y: <?php echo (int)($array_accion_mejora['Si'] ?? 0); ?>,
                    sliced: true,
                    selected: true
                }, {
                    name: 'No aplica',
                    y: <?php echo (int)($array_accion_mejora['No'] ?? 0); ?>
                }]
            }]
        });

        Highcharts.chart('gra_afectalt', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'AFECTA LÍNEA TÉCNICA',
                style: { fontSize: '14px' }
            },
            credits: { enabled: false },
            subtitle: { text: null },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br>Cantidad: <b>{point.y}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        distance: 5,
                        format: '{point.percentage:.1f} % <br>{point.y}'
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Porcentaje',
                colorByPoint: true,
                data: [{
                    name: 'Afecta LT',
                    y: <?php echo (int)($array_afectalt['Si'] ?? 0); ?>,
                    sliced: true,
                    selected: true
                }, {
                    name: 'No afecta LT',
                    y: <?php echo (int)($array_afectalt['No'] ?? 0); ?>
                }]
            }]
        });
    </script>
</body>
</html>
