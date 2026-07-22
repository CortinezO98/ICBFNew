<?php
    $modulo_plataforma = "Coaching-Reportes";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");

    $titulo_header = "Coaching | Estadísticas";

    $tiene_reportes = isset($_SESSION['modulos_acceso_permisos']['Coaching-Reportes']) && $_SESSION['modulos_acceso_permisos']['Coaching-Reportes'] !== '';
    if (!$tiene_reportes) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $perfil_coaching = coachingPerfilUsuarioActual();
    $filtro_alcance_sql = '';
    $parametros_alcance = [];
    if ($perfil_coaching === 'Supervisor') {
        [$filtro_alcance_sql, $parametros_alcance] = coachingFiltroAlcance('Supervisor', $_SESSION['usu_id']);
    }

    // ---- Pestaña activa ----
    $vistas_validas = ['consolidado', 'lider', 'agente', 'historico'];
    $vista = validar_input($_GET['vista'] ?? 'consolidado');
    if (!in_array($vista, $vistas_validas, true)) { $vista = 'consolidado'; }

    // ---- Filtro de fechas (compartido por las 4 pestañas) ----
    $fecha_desde = validar_input($_GET['desde'] ?? '');
    $fecha_hasta = validar_input($_GET['hasta'] ?? '');
    if ($fecha_desde === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) { $fecha_desde = date('Y-m-d', strtotime('-180 days')); }
    if ($fecha_hasta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) { $fecha_hasta = date('Y-m-d'); }

    // ---- Filtros propios de cada pestaña ----
    $lider_id = validar_input($_GET['lider_id'] ?? '');
    $agente_buscar = validar_input($_GET['agente_buscar'] ?? '');

    // Filtro extra según la pestaña (además del alcance por rol y la fecha)
    $filtro_extra_sql = '';
    $filtro_extra_params = [];
    if ($vista === 'lider' && $lider_id !== '') {
        $filtro_extra_sql = " AND P.`gcp_supervisor_id` = ? ";
        $filtro_extra_params[] = $lider_id;
    }
    if (($vista === 'agente' || $vista === 'historico') && $agente_buscar !== '') {
        $filtro_extra_sql = " AND (P.`gcp_agente_id` LIKE ? OR TA.`usu_nombres_apellidos` LIKE ?) ";
        $filtro_extra_params[] = "%{$agente_buscar}%";
        $filtro_extra_params[] = "%{$agente_buscar}%";
    }

    $filtro_fecha_sql = " AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ? ";

    function ejecutarConteo(mysqli $enlace_db, string $sql, array $params, string $tipos): array
    {
        $stmt = $enlace_db->prepare($sql);
        if (count($params) > 0) {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Calcula todo el set de agregados (KPIs + series de gráficas) para un
     * alcance dado. Se reutiliza igual en Consolidado, Seguimiento Líder y
     * Seguimiento Agente — solo cambia el filtro extra que se le pasa.
     */
    function calcularDashboardCoaching(mysqli $enlace_db, string $filtro_alcance, string $filtro_extra, array $params, string $tipos): array
    {
        $where_comun = "P.`gcp_activo` = 1 {$filtro_alcance} {$filtro_extra} AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ?";
        // Nota: params ya trae [alcance..., extra..., fecha_desde, fecha_hasta]
        // en ese orden — coherente con cómo se arma $where_comun arriba.

        $por_estado = ejecutarConteo($enlace_db,
            "SELECT E.`gce_nombre`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_gestion_coaching_estado` AS E ON P.`gcp_estado_id` = E.`gce_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY E.`gce_id` ORDER BY E.`gce_orden`", $params, $tipos);

        $por_tipo = ejecutarConteo($enlace_db,
            "SELECT T.`gct_nombre`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY T.`gct_id` ORDER BY total DESC", $params, $tipos);

        $por_campania = ejecutarConteo($enlace_db,
            "SELECT AC.`ac_nombre_campania`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_campania` AS AC ON P.`gcp_campania_id` = AC.`ac_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY AC.`ac_id` ORDER BY total DESC", $params, $tipos);

        $por_segmento = ejecutarConteo($enlace_db,
            "SELECT P.`gcp_segmento`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} AND P.`gcp_segmento` IS NOT NULL AND P.`gcp_segmento` != ''
             GROUP BY P.`gcp_segmento` ORDER BY total DESC", $params, $tipos);

        $por_mes_raw = ejecutarConteo($enlace_db,
            "SELECT DATE_FORMAT(P.`gcp_registro_fecha`, '%Y-%m') AS anio_mes, COUNT(*) AS total
             FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY anio_mes ORDER BY anio_mes ASC", $params, $tipos);
        $meses_es = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
        $categorias_mes = []; $datos_mes = [];
        foreach ($por_mes_raw as $m) {
            [$anio, $mes] = explode('-', $m['anio_mes']);
            $categorias_mes[] = $meses_es[(int) $mes] . ' ' . $anio;
            $datos_mes[] = (int) $m['total'];
        }

        $por_agente = ejecutarConteo($enlace_db,
            "SELECT TA.`usu_id`, TA.`usu_nombres_apellidos`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY P.`gcp_agente_id` ORDER BY total DESC LIMIT 10", $params, $tipos);

        $por_lider = ejecutarConteo($enlace_db,
            "SELECT TS.`usu_id`, TS.`usu_nombres_apellidos`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY P.`gcp_supervisor_id` ORDER BY total DESC LIMIT 10", $params, $tipos);

        $compromisos_raw = ejecutarConteo($enlace_db,
            "SELECT C.`gccm_estado`, COUNT(*) AS total FROM `tb_gestion_coaching_compromiso` AS C
             INNER JOIN `tb_gestion_coaching_paquete` AS P ON C.`gccm_paquete` = P.`gcp_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY C.`gccm_estado`", $params, $tipos);
        $compromisos = ['Pendiente' => 0, 'Cumplido' => 0, 'No cumplido' => 0];
        foreach ($compromisos_raw as $c) { $compromisos[$c['gccm_estado']] = (int) $c['total']; }

        $origen_raw = ejecutarConteo($enlace_db,
            "SELECT P.`gcp_origen_tipo`, COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} GROUP BY P.`gcp_origen_tipo`", $params, $tipos);
        $automaticos = 0; $manuales = 0;
        foreach ($origen_raw as $o) {
            if ($o['gcp_origen_tipo'] === 'monitoreo') { $automaticos = (int) $o['total']; } else { $manuales += (int) $o['total']; }
        }

        $vencidos_raw = ejecutarConteo($enlace_db,
            "SELECT COUNT(*) AS total FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_gestion_coaching_estado` AS E ON P.`gcp_estado_id` = E.`gce_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} AND E.`gce_terminal` = 0 AND P.`gcp_fecha_limite` IS NOT NULL AND P.`gcp_fecha_limite` < CURDATE()", $params, $tipos);

        $cierre_raw = ejecutarConteo($enlace_db,
            "SELECT AVG(DATEDIFF(P.`gcp_fecha_cierre`, P.`gcp_registro_fecha`)) AS promedio, COUNT(*) AS total
             FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             WHERE {$where_comun} AND P.`gcp_fecha_cierre` IS NOT NULL", $params, $tipos);

        $total = array_sum(array_column($por_estado, 'total'));

        return [
            'total' => $total, 'automaticos' => $automaticos, 'manuales' => $manuales,
            'vencidos' => (int) ($vencidos_raw[0]['total'] ?? 0),
            'promedio_cierre' => $cierre_raw[0]['promedio'] ?? null, 'total_cerrados' => (int) ($cierre_raw[0]['total'] ?? 0),
            'por_estado' => $por_estado, 'por_tipo' => $por_tipo, 'por_campania' => $por_campania,
            'por_segmento' => $por_segmento, 'categorias_mes' => $categorias_mes, 'datos_mes' => $datos_mes,
            'por_agente' => $por_agente, 'por_lider' => $por_lider, 'compromisos' => $compromisos,
        ];
    }

    $params_finales = array_merge($parametros_alcance, $filtro_extra_params, [$fecha_desde, $fecha_hasta]);
    $tipos_finales = str_repeat('s', count($params_finales));

    $datos = null;
    if (in_array($vista, ['consolidado', 'lider', 'agente'], true)) {
        $datos = calcularDashboardCoaching($enlace_db, $filtro_alcance_sql, $filtro_extra_sql, $params_finales, $tipos_finales);
    }

    // ---- Lista de líderes para el selector de la pestaña "Seguimiento Líder" ----
    $lideres_disponibles = [];
    if ($vista === 'lider') {
        $params_lideres = array_merge($parametros_alcance, [$fecha_desde, $fecha_hasta]);
        $lideres_disponibles = ejecutarConteo($enlace_db,
            "SELECT DISTINCT TS.`usu_id`, TS.`usu_nombres_apellidos` FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
             WHERE P.`gcp_activo` = 1 {$filtro_alcance_sql} AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ?
             ORDER BY TS.`usu_nombres_apellidos`",
            $params_lideres, str_repeat('s', count($params_lideres)));
    }

    // ---- Tabla de detalle (Líder / Agente / Histórico) ----
    $detalle = [];
    if (in_array($vista, ['lider', 'agente', 'historico'], true)) {
        $params_detalle = array_merge($parametros_alcance, $filtro_extra_params, [$fecha_desde, $fecha_hasta]);
        $sql_detalle =
            "SELECT P.`gcp_id`, P.`gcp_origen_tipo`, P.`gcp_registro_fecha`, P.`gcp_agente_id`,
                    TA.`usu_nombres_apellidos` AS agente_nombre, TS.`usu_nombres_apellidos` AS supervisor_nombre,
                    P.`gcp_segmento`, T.`gct_nombre`, AC.`ac_nombre_campania`,
                    I.`gci_nombre` AS indicador_global, C.`gcc2_nota_general`, C.`gcc2_matriz`,
                    R.`gcr_causa_raiz`, R.`gcr_estrategia_correctiva`, RA.`gcra_compromiso_general`
             FROM `tb_gestion_coaching_paquete` AS P
             LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
             LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
             LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
             LEFT JOIN `tb_administrador_campania` AS AC ON P.`gcp_campania_id` = AC.`ac_id`
             LEFT JOIN `tb_gestion_coaching_indicador` AS I ON P.`gcp_indicador_id` = I.`gci_id`
             LEFT JOIN `tb_gestion_coaching_calidad` AS C ON P.`gcp_id` = C.`gcc2_paquete`
             LEFT JOIN `tb_gestion_coaching_retroalimentacion` AS R ON P.`gcp_id` = R.`gcr_paquete`
             LEFT JOIN `tb_gestion_coaching_respuesta_agente` AS RA ON P.`gcp_id` = RA.`gcra_paquete`
             WHERE P.`gcp_activo` = 1 {$filtro_alcance_sql} {$filtro_extra_sql} AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ?
             ORDER BY P.`gcp_registro_fecha` DESC LIMIT 500";
        $detalle = ejecutarConteo($enlace_db, $sql_detalle, $params_detalle, str_repeat('s', count($params_detalle)));
    }

    $meses_es_largo = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    function indicadorHistorico(array $r): string
    {
        if ($r['gcp_origen_tipo'] === 'monitoreo') {
            $nota = $r['gcc2_nota_general'] ?? null;
            return 'Monitoreo de Calidad' . ($nota !== null ? " (Nota {$nota})" : '');
        }
        return $r['indicador_global'] ?? '—';
    }

    function urlVista(string $v, string $desde, string $hasta, string $lider = '', string $agente = ''): string
    {
        return 'gestion_coaching_estadisticas.php?' . http_build_query(array_filter([
            'vista' => $v, 'desde' => $desde, 'hasta' => $hasta, 'lider_id' => $lider, 'agente_buscar' => $agente,
        ], fn($x) => $x !== ''));
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <script src="../anychart/js/anychart-core.min.js" type="text/javascript"></script>
    <script src="../anychart/js/anychart-bundle.min.js" type="text/javascript"></script>
    <script src="../anychart/js/anychart-base.min.js" type="text/javascript"></script>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }

        .coaching_kpi_grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 16px; }
        .coaching_kpi { background: #FFFFFF; border: 1px solid #E3E6EA; border-radius: 8px; padding: 12px; text-align: center; }
        .coaching_kpi .valor { font-size: 22px; font-weight: bold; color: #4CAF50; }
        .coaching_kpi .valor.alerta { color: #FF0000; }
        .coaching_kpi .etiqueta { font-size: 10px; color: #6E6E6E; text-transform: uppercase; letter-spacing: .03em; margin-top: 3px; }

        .coaching_filtros { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 15px; }
        .coaching_filtros label { font-size: 11px; font-weight: bold; color: #1A1A1A; display: block; margin-bottom: 3px; }
        .coaching_filtros input, .coaching_filtros select { font-size: 12px; }

        .coaching_grafica_caja { background: #FFFFFF; border: 1px solid #E3E6EA; border-radius: 6px; padding: 8px; margin-bottom: 15px; }
        .coaching_grafica_caja > div { height: 300px; }

        .coaching_tabla_mini td, .coaching_tabla_mini th { font-size: 11px; padding: 5px 8px; }

        .coaching_tabs { display: flex; gap: 4px; margin-bottom: 18px; border-bottom: 2px solid #E3E6EA; flex-wrap: wrap; }
        .coaching_tab {
            padding: 10px 18px; font-size: 12px; font-weight: bold; color: #6E6E6E; text-decoration: none;
            border-bottom: 3px solid transparent; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 6px;
        }
        .coaching_tab:hover { color: #4CAF50; text-decoration: none; }
        .coaching_tab.activo { color: #4CAF50; border-bottom-color: #4CAF50; }

        .coaching_tabla_historico { font-size: 11px; }
        .coaching_tabla_historico td { max-width: 220px; }
        .coaching_celda_texto { max-height: 70px; overflow-y: auto; display: block; }
        .div_tabla { overflow-x: auto; }

        .coaching_empty_estado { text-align: center; padding: 40px 20px; color: #6E6E6E; font-size: 12px; }
    </style>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <span>Estadísticas</span>
        </nav>

        <h4 class="titulo_seccion mb-3">Estadísticas de Coaching</h4>

        <div class="coaching_tabs">
            <a href="<?php echo urlVista('consolidado', $fecha_desde, $fecha_hasta); ?>" class="coaching_tab <?php echo $vista === 'consolidado' ? 'activo' : ''; ?>"><span class="fas fa-chart-pie"></span> Consolidado Línea</a>
            <a href="<?php echo urlVista('lider', $fecha_desde, $fecha_hasta); ?>" class="coaching_tab <?php echo $vista === 'lider' ? 'activo' : ''; ?>"><span class="fas fa-user-tie"></span> Seguimiento Líder</a>
            <a href="<?php echo urlVista('agente', $fecha_desde, $fecha_hasta); ?>" class="coaching_tab <?php echo $vista === 'agente' ? 'activo' : ''; ?>"><span class="fas fa-user"></span> Seguimiento Agente</a>
            <a href="<?php echo urlVista('historico', $fecha_desde, $fecha_hasta); ?>" class="coaching_tab <?php echo $vista === 'historico' ? 'activo' : ''; ?>"><span class="fas fa-history"></span> Histórico Seguimiento</a>
            <a href="gestion_coaching_reporte.php" class="coaching_tab" style="margin-left:auto;"><span class="fas fa-file-excel"></span> Exportar reporte</a>
        </div>

        <form method="GET" action="" class="coaching_filtros">
            <input type="hidden" name="vista" value="<?php echo htmlspecialchars($vista); ?>">
            <div>
                <label for="desde">Desde</label>
                <input type="date" name="desde" id="desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            <div>
                <label for="hasta">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>

            <?php if ($vista === 'lider'): ?>
                <div style="min-width:220px;">
                    <label for="lider_id">Filtrar por líder de equipo</label>
                    <select name="lider_id" id="lider_id" class="form-control">
                        <option value="">Todos los líderes (de su alcance)</option>
                        <?php foreach ($lideres_disponibles as $l): ?>
                            <?php if (!$l['usu_id']) { continue; } ?>
                            <option value="<?php echo htmlspecialchars($l['usu_id']); ?>" <?php echo $lider_id === $l['usu_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($l['usu_nombres_apellidos']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($vista === 'agente' || $vista === 'historico'): ?>
                <div style="min-width:220px; flex:1;">
                    <label for="agente_buscar">Documento o nombre del colaborador</label>
                    <input type="text" name="agente_buscar" id="agente_buscar" class="form-control" value="<?php echo htmlspecialchars($agente_buscar); ?>" placeholder="Ingrese documento o nombre...">
                </div>
            <?php endif; ?>

            <div>
                <button type="submit" class="btn-corp px-3 py-2" style="border-radius:5px; border:0;">
                    <span class="fas fa-filter"></span> Filtrar
                </button>
            </div>
        </form>

        <?php if (in_array($vista, ['consolidado', 'lider', 'agente'], true)): ?>

            <?php if ($vista === 'agente' && $agente_buscar === ''): ?>
                <div class="coaching_empty_estado"><span class="fas fa-search fa-2x mb-2 d-block"></span>Ingrese un documento o nombre para ver el seguimiento de un agente específico — mientras tanto se muestra el consolidado de su alcance.</div>
            <?php endif; ?>

            <div class="coaching_kpi_grid">
                <div class="coaching_kpi"><div class="valor"><?php echo $datos['total']; ?></div><div class="etiqueta">Total paquetes</div></div>
                <div class="coaching_kpi"><div class="valor"><?php echo $datos['automaticos']; ?></div><div class="etiqueta">Por nota &lt; 90</div></div>
                <div class="coaching_kpi"><div class="valor"><?php echo $datos['manuales']; ?></div><div class="etiqueta">Manuales</div></div>
                <div class="coaching_kpi"><div class="valor <?php echo $datos['vencidos'] > 0 ? 'alerta' : ''; ?>"><?php echo $datos['vencidos']; ?></div><div class="etiqueta">Vencidos</div></div>
                <div class="coaching_kpi"><div class="valor"><?php echo $datos['promedio_cierre'] !== null ? round((float) $datos['promedio_cierre'], 1) : '—'; ?></div><div class="etiqueta">Días prom. cierre</div></div>
            </div>

            <div class="row">
                <div class="col-md-6"><div class="coaching_grafica_caja"><div id="grafica_tendencia_mes"></div></div></div>
                <div class="col-md-6"><div class="coaching_grafica_caja"><div id="grafica_por_empresa"></div></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="coaching_grafica_caja"><div id="grafica_por_estado"></div></div></div>
                <div class="col-md-6"><div class="coaching_grafica_caja"><div id="grafica_por_tipo"></div></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="coaching_grafica_caja"><div id="grafica_por_segmento"></div></div></div>
                <div class="col-md-6"><div class="coaching_grafica_caja"><div id="grafica_compromisos"></div></div></div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="fas fa-user-tie"></span> Líder de equipo</div>
                        <div class="p-2">
                            <table class="tabla_list coaching_tabla_mini">
                                <thead><tr><th>Nombre</th><th>Cantidad</th></tr></thead>
                                <tbody>
                                    <?php if (count($datos['por_lider']) === 0): ?><tr><td colspan="2" class="text-center">Sin datos.</td></tr><?php endif; ?>
                                    <?php foreach ($datos['por_lider'] as $l): ?>
                                        <tr class="tabla_contenido_1">
                                            <td><a href="<?php echo urlVista('lider', $fecha_desde, $fecha_hasta, (string) $l['usu_id']); ?>"><?php echo validar_output($l['usu_nombres_apellidos'] ?? '—'); ?></a></td>
                                            <td><?php echo (int) $l['total']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="fas fa-users"></span> Agentes con más paquetes</div>
                        <div class="p-2">
                            <table class="tabla_list coaching_tabla_mini">
                                <thead><tr><th>Nombre</th><th>Cantidad</th></tr></thead>
                                <tbody>
                                    <?php if (count($datos['por_agente']) === 0): ?><tr><td colspan="2" class="text-center">Sin datos.</td></tr><?php endif; ?>
                                    <?php foreach ($datos['por_agente'] as $a): ?>
                                        <tr class="tabla_contenido_1">
                                            <td><a href="<?php echo urlVista('agente', $fecha_desde, $fecha_hasta, '', (string) $a['usu_id']); ?>"><?php echo validar_output($a['usu_nombres_apellidos'] ?? '—'); ?></a></td>
                                            <td><?php echo (int) $a['total']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                anychart.onDocumentReady(function () {
                    // ---- Tendencia mensual (línea) ----
                    var etiquetasMes = <?php echo json_encode($datos['categorias_mes']); ?>;
                    var valoresMes = <?php echo json_encode($datos['datos_mes']); ?>;
                    var datosLinea = etiquetasMes.map(function (cat, i) { return [cat, valoresMes[i]]; });
                    var chartLinea = anychart.line(datosLinea);
                    chartLinea.title('Paquetes creados por mes');
                    chartLinea.getSeries(0).stroke('2 #4CAF50').color('#4CAF50');
                    chartLinea.legend(false);
                    chartLinea.container('grafica_tendencia_mes');
                    chartLinea.draw();

                    // ---- Por empresa (donut) ----
                    var datosEmpresa = [
                        <?php foreach ($datos['por_campania'] as $c): ?>
                        { x: <?php echo json_encode($c['ac_nombre_campania'] ?? 'Sin asignar'); ?>, value: <?php echo (int) $c['total']; ?> },
                        <?php endforeach; ?>
                    ];
                    var chartEmpresa = anychart.pie(datosEmpresa);
                    chartEmpresa.title('Por empresa');
                    chartEmpresa.innerRadius('55%');
                    chartEmpresa.labels().format('{%X}: {%PercentValue}%');
                    chartEmpresa.legend(true);
                    chartEmpresa.container('grafica_por_empresa');
                    chartEmpresa.draw();

                    // ---- Por estado (barra horizontal) ----
                    var datosEstado = [
                        <?php foreach ($datos['por_estado'] as $e): ?>
                        { x: <?php echo json_encode($e['gce_nombre']); ?>, value: <?php echo (int) $e['total']; ?> },
                        <?php endforeach; ?>
                    ];
                    var chartEstado = anychart.bar(datosEstado);
                    chartEstado.title('Por estado');
                    chartEstado.legend(false);
                    chartEstado.container('grafica_por_estado');
                    chartEstado.draw();

                    // ---- Por tipo (barra horizontal) ----
                    var datosTipo = [
                        <?php foreach ($datos['por_tipo'] as $t): ?>
                        { x: <?php echo json_encode($t['gct_nombre']); ?>, value: <?php echo (int) $t['total']; ?> },
                        <?php endforeach; ?>
                    ];
                    var chartTipo = anychart.bar(datosTipo);
                    chartTipo.title('Por tipo de paquete');
                    chartTipo.getSeries(0).color('#F39C12');
                    chartTipo.legend(false);
                    chartTipo.container('grafica_por_tipo');
                    chartTipo.draw();

                    // ---- Por segmento (barra horizontal) ----
                    var datosSegmento = [
                        <?php foreach ($datos['por_segmento'] as $s): ?>
                        { x: <?php echo json_encode($s['gcp_segmento']); ?>, value: <?php echo (int) $s['total']; ?> },
                        <?php endforeach; ?>
                    ];
                    var chartSegmento = anychart.bar(datosSegmento);
                    chartSegmento.title('Por segmento');
                    chartSegmento.getSeries(0).color('#175E83');
                    chartSegmento.legend(false);
                    chartSegmento.container('grafica_por_segmento');
                    chartSegmento.draw();

                    // ---- Compromisos (donut) ----
                    var datosCompromisos = [
                        { x: 'Cumplidos', value: <?php echo $datos['compromisos']['Cumplido']; ?> },
                        { x: 'Pendientes', value: <?php echo $datos['compromisos']['Pendiente']; ?> },
                        { x: 'No cumplidos', value: <?php echo $datos['compromisos']['No cumplido']; ?> }
                    ];
                    var chartCompromisos = anychart.pie(datosCompromisos);
                    chartCompromisos.title('Compromisos');
                    chartCompromisos.innerRadius('55%');
                    chartCompromisos.palette(['#00BF6F', '#F39C12', '#FF0000']);
                    chartCompromisos.legend(true);
                    chartCompromisos.container('grafica_compromisos');
                    chartCompromisos.draw();
                });
            </script>
        <?php endif; ?>

        <?php if (in_array($vista, ['lider', 'agente', 'historico'], true)): ?>
            <div class="cuadro_dash mb-3">
                <div class="cuadro_dash_titulo p-2"><span class="fas fa-table"></span> Detalle de seguimiento</div>
                <div class="p-2">
                    <p style="font-size:11px; color:#6E6E6E; margin:5px;">
                        <?php echo count($detalle); ?> registro(s) <?php echo count($detalle) === 500 ? '(máximo 500, refine el filtro)' : ''; ?>
                    </p>
                    <div class="div_tabla">
                        <table class="tabla_list coaching_tabla_historico">
                            <thead>
                                <tr>
                                    <th>Fecha</th><th>Cédula</th><th>Nombre</th><th>Jefe inmediato</th>
                                    <th>Segmento</th><th>Mes</th><th>Indicador</th><th>Acción ejecutada</th>
                                    <th>Comentarios líder</th><th>Compromiso</th><th>Empresa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($detalle) === 0): ?>
                                    <tr><td colspan="11" class="text-center" style="padding:15px;">No hay registros para los filtros seleccionados.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($detalle as $r):
                                    $comentarios = trim(($r['gcr_causa_raiz'] ?? '') . (!empty($r['gcr_estrategia_correctiva']) ? ' — ' . $r['gcr_estrategia_correctiva'] : ''));
                                    $mes_num = $r['gcp_registro_fecha'] ? (int) date('n', strtotime($r['gcp_registro_fecha'])) : null;
                                ?>
                                    <tr class="tabla_contenido_1">
                                        <td><?php echo $r['gcp_registro_fecha'] ? date('d/m/Y H:i', strtotime($r['gcp_registro_fecha'])) : '—'; ?></td>
                                        <td><?php echo validar_output($r['gcp_agente_id']); ?></td>
                                        <td><?php echo validar_output($r['agente_nombre'] ?? '—'); ?></td>
                                        <td><?php echo validar_output($r['supervisor_nombre'] ?? '—'); ?></td>
                                        <td><?php echo validar_output($r['gcp_segmento'] ?? '—'); ?></td>
                                        <td><?php echo $mes_num ? $meses_es_largo[$mes_num] : '—'; ?></td>
                                        <td><?php echo validar_output(indicadorHistorico($r)); ?></td>
                                        <td><?php echo validar_output($r['gct_nombre'] ?? '—'); ?></td>
                                        <td><span class="coaching_celda_texto"><?php echo $comentarios !== '' ? nl2br(validar_output($comentarios)) : '—'; ?></span></td>
                                        <td><span class="coaching_celda_texto"><?php echo !empty($r['gcra_compromiso_general']) ? nl2br(validar_output($r['gcra_compromiso_general'])) : '—'; ?></span></td>
                                        <td><?php echo validar_output($r['ac_nombre_campania'] ?? '—'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
