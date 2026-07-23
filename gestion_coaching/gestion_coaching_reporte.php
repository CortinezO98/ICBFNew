<?php
    $modulo_plataforma = "Coaching-Reportes";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    $titulo_header = "Coaching | Reporte";

    $tiene_reportes = isset($_SESSION['modulos_acceso_permisos']['Coaching-Reportes']) && $_SESSION['modulos_acceso_permisos']['Coaching-Reportes'] !== '';
    if (!$tiene_reportes) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $perfil_coaching = coachingPerfilUsuarioActual();
    $filtro_alcance_sql = '';
    $parametros_alcance = [];
    if (in_array($perfil_coaching, ['Supervisor', 'Agente'], true)) {
        [$filtro_alcance_sql, $parametros_alcance] = coachingFiltroAlcance($perfil_coaching, $_SESSION['usu_id']);
    }

    // ---- Filtros ----
    $fecha_desde = validar_input($_GET['desde'] ?? '');
    $fecha_hasta = validar_input($_GET['hasta'] ?? '');
    if ($fecha_desde === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) { $fecha_desde = date('Y-m-d', strtotime('-90 days')); }
    if ($fecha_hasta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) { $fecha_hasta = date('Y-m-d'); }

    $filtro_estado = validar_input($_GET['estado'] ?? '');
    $filtro_tipo = validar_input($_GET['tipo'] ?? '');
    $filtro_origen = validar_input($_GET['origen'] ?? '');

    $condiciones = " AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ? ";
    $parametros = array_merge($parametros_alcance, [$fecha_desde, $fecha_hasta]);

    if ($filtro_estado !== '' && $filtro_estado !== 'Todos') {
        $condiciones .= " AND E.`gce_codigo` = ? ";
        $parametros[] = $filtro_estado;
    }
    if ($filtro_tipo !== '' && $filtro_tipo !== 'Todos') {
        $condiciones .= " AND T.`gct_codigo` = ? ";
        $parametros[] = $filtro_tipo;
    }
    if ($filtro_origen !== '' && $filtro_origen !== 'Todos') {
        $condiciones .= " AND P.`gcp_origen_tipo` = ? ";
        $parametros[] = $filtro_origen;
    }

    $tipos_bind = str_repeat('s', count($parametros));

    $sql =
        "SELECT P.`gcp_id`, P.`gcp_origen_tipo`, T.`gct_nombre`, E.`gce_nombre`, E.`gce_codigo`,
                TA.`usu_nombres_apellidos` AS agente_nombre, TS.`usu_nombres_apellidos` AS supervisor_nombre,
                P.`gcp_registro_fecha`, P.`gcp_fecha_limite`, P.`gcp_fecha_cierre`, P.`gcp_prioridad`,
                (SELECT GROUP_CONCAT(I.`gci_nombre` SEPARATOR '; ')
                 FROM `tb_gestion_coaching_paquete_indicador` AS PI
                 INNER JOIN `tb_gestion_coaching_indicador` AS I ON PI.`gcpi_indicador_id` = I.`gci_id`
                 WHERE PI.`gcpi_paquete` = P.`gcp_id`) AS indicadores_multiples,
                ESC.`gcesc_destinatario_nombre`, ESC.`gcesc_asunto`
         FROM `tb_gestion_coaching_paquete` AS P
         LEFT JOIN `tb_gestion_coaching_estado` AS E ON P.`gcp_estado_id` = E.`gce_id`
         LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
         LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
         LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
         LEFT JOIN `tb_gestion_coaching_escalamiento` AS ESC ON P.`gcp_id` = ESC.`gcesc_paquete`
         WHERE P.`gcp_activo` = 1 {$filtro_alcance_sql} {$condiciones}
         ORDER BY P.`gcp_registro_fecha` DESC
         LIMIT 500";

    $stmt = $enlace_db->prepare($sql);
    if (count($parametros) > 0) {
        $stmt->bind_param($tipos_bind, ...$parametros);
    }
    $stmt->execute();
    $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Catálogos para los <select> de filtro
    $tipos_catalogo = $enlace_db->query("SELECT `gct_codigo`, `gct_nombre` FROM `tb_gestion_coaching_tipo` WHERE `gct_activo`=1 ORDER BY `gct_nombre`")->fetch_all(MYSQLI_ASSOC);
    $estados_catalogo = $enlace_db->query("SELECT `gce_codigo`, `gce_nombre` FROM `tb_gestion_coaching_estado` WHERE `gce_activo`=1 ORDER BY `gce_orden`")->fetch_all(MYSQLI_ASSOC);

    function claseEstadoCoachingRep(string $gce_codigo): string
    {
        $mapa = [
            'BORRADOR' => 'coaching_estado_gris', 'ASIGNADO' => 'coaching_estado_azul',
            'PENDIENTE_SUPERVISOR' => 'coaching_estado_azul', 'PENDIENTE_AGENTE' => 'coaching_estado_naranja',
            'RESPONDIDO_AGENTE' => 'coaching_estado_azul', 'PENDIENTE_FIRMA_AGENTE' => 'coaching_estado_naranja',
            'EN_SEGUIMIENTO' => 'coaching_estado_morado', 'PENDIENTE_CIERRE' => 'coaching_estado_azul',
            'CERRADO' => 'coaching_estado_verde', 'RECHAZADO' => 'coaching_estado_rojo', 'ANULADO' => 'coaching_estado_gris',
        ];
        return $mapa[$gce_codigo] ?? 'coaching_estado_gris';
    }

    // Query string para reusar los filtros actuales en el link de Excel/estadísticas
    $query_filtros = http_build_query(['desde' => $fecha_desde, 'hasta' => $fecha_hasta, 'estado' => $filtro_estado, 'tipo' => $filtro_tipo, 'origen' => $filtro_origen]);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        .coaching_estado_pill { display: inline-block; font-size: 11px; padding: 2px 10px; border-radius: 10px; }
        .coaching_estado_verde   { color: #00BF6F; border: solid 1px #00BF6F; background-color: rgba(40, 180, 99, 0.15); }
        .coaching_estado_naranja { color: #F39C12; border: solid 1px #F39C12; background-color: rgba(243, 156, 18, 0.15); }
        .coaching_estado_azul    { color: #175E83; border: solid 1px #175E83; background-color: rgba(23, 94, 131, 0.15); }
        .coaching_estado_morado  { color: #6C3483; border: solid 1px #6C3483; background-color: rgba(108, 52, 131, 0.15); }
        .coaching_estado_rojo    { color: #FF0000; border: solid 1px #FF0000; background-color: rgba(255, 0, 0, 0.15); }
        .coaching_estado_gris    { color: #6E6E6E; border: solid 1px #6E6E6E; background-color: rgba(110, 110, 110, 0.15); }
        .coaching_filtros { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 15px; }
        .coaching_filtros label { font-size: 11px; font-weight: bold; color: #1A1A1A; display: block; margin-bottom: 3px; }
        .coaching_filtros select, .coaching_filtros input { font-size: 12px; }
        .coaching_tabla td.col-centro, .coaching_tabla th.col-centro { text-align: center; }
        .coaching_tabla td.col-izq, .coaching_tabla th.col-izq { text-align: left; }
    </style>
</head>
<body onresize="tabla_fixed();" onload="tabla_fixed();">
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <span>Reporte</span>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h4 class="titulo_seccion mb-0">Reporte de Coaching</h4>
            <div>
                <a href="gestion_coaching_estadisticas.php?<?php echo $query_filtros; ?>" class="btn-corp-2 px-3 py-1" style="border-radius:5px;">
                    <span class="fas fa-chart-pie"></span> Ver estadísticas
                </a>
                <a href="gestion_coaching_reporte_excel.php?<?php echo $query_filtros; ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">
                    <span class="fas fa-file-excel"></span> Exportar a Excel
                </a>
            </div>
        </div>

        <form method="GET" action="" class="coaching_filtros">
            <div>
                <label for="desde">Desde</label>
                <input type="date" name="desde" id="desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            <div>
                <label for="hasta">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
            <div>
                <label for="estado">Estado</label>
                <select name="estado" id="estado" class="form-control">
                    <option value="Todos">Todos</option>
                    <?php foreach ($estados_catalogo as $e): ?>
                        <option value="<?php echo htmlspecialchars($e['gce_codigo']); ?>" <?php echo $filtro_estado === $e['gce_codigo'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($e['gce_nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="Todos">Todos</option>
                    <?php foreach ($tipos_catalogo as $t): ?>
                        <option value="<?php echo htmlspecialchars($t['gct_codigo']); ?>" <?php echo $filtro_tipo === $t['gct_codigo'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['gct_nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="origen">Origen</label>
                <select name="origen" id="origen" class="form-control">
                    <option value="Todos">Todos</option>
                    <option value="monitoreo" <?php echo $filtro_origen === 'monitoreo' ? 'selected' : ''; ?>>Monitoreo (automático)</option>
                    <option value="global" <?php echo $filtro_origen === 'global' ? 'selected' : ''; ?>>Global (manual)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-corp px-3 py-2" style="border-radius:5px; border:0;">
                    <span class="fas fa-filter"></span> Filtrar
                </button>
            </div>
        </form>

        <p style="font-size:11px; color:#6E6E6E;">
            <?php echo count($registros); ?> resultado(s) <?php echo count($registros) === 500 ? '(máximo 500, refine el filtro para ver todos)' : ''; ?>
        </p>

        <div class="div_tabla">
            <table class="tabla_list coaching_tabla">
                <thead>
                    <tr>
                        <th class="col-izq">Código</th>
                        <th class="col-centro">Origen</th>
                        <th class="col-centro">Tipo</th>
                        <th class="col-izq">Agente</th>
                        <th class="col-izq">Supervisor</th>
                        <th class="col-izq">Indicadores</th>
                        <th class="col-centro">Estado</th>
                        <th class="col-centro">Creado</th>
                        <th class="col-centro">Cierre</th>
                        <th class="col-centro"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) === 0): ?>
                        <tr><td colspan="10" class="text-center" style="font-size:12px; color:#6E6E6E; padding:15px;">No hay resultados para los filtros seleccionados.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($registros as $r): ?>
                        <tr class="tabla_contenido_1">
                            <td class="col-izq"><strong><?php echo validar_output($r['gcp_id']); ?></strong></td>
                            <td class="col-centro"><?php echo validar_output(ucfirst($r['gcp_origen_tipo'])); ?></td>
                            <td class="col-centro"><?php echo validar_output($r['gct_nombre']); ?></td>
                            <td class="col-izq"><?php echo validar_output($r['agente_nombre'] ?? '—'); ?></td>
                            <td class="col-izq"><?php echo validar_output($r['supervisor_nombre'] ?? '—'); ?></td>
                            <td class="col-izq" style="font-size:11px; max-width:200px;"><?php echo validar_output($r['indicadores_multiples'] ?? '—'); ?></td>
                            <td class="col-centro"><span class="coaching_estado_pill <?php echo claseEstadoCoachingRep($r['gce_codigo']); ?>"><?php echo validar_output($r['gce_nombre']); ?></span></td>
                            <td class="col-centro"><?php echo date('d/m/Y', strtotime($r['gcp_registro_fecha'])); ?></td>
                            <td class="col-centro"><?php echo $r['gcp_fecha_cierre'] ? date('d/m/Y', strtotime($r['gcp_fecha_cierre'])) : '—'; ?></td>
                            <td class="col-centro">
                                <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($r['gcp_id']); ?>" class="btn-corp" style="width:26px;height:26px;padding:0;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;" title="Ver">
                                    <span class="fas fa-eye"></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
