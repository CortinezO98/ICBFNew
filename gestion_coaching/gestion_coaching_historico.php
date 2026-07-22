<?php
    $modulo_plataforma = "Coaching-Reportes";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");

    $titulo_header = "Coaching | Histórico Seguimiento";

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

    // ---- Filtros: rango de fechas + búsqueda libre por cédula/nombre ----
    $fecha_desde = validar_input($_GET['desde'] ?? '');
    $fecha_hasta = validar_input($_GET['hasta'] ?? '');
    if ($fecha_desde === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) { $fecha_desde = date('Y-m-d', strtotime('-180 days')); }
    if ($fecha_hasta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) { $fecha_hasta = date('Y-m-d'); }

    $buscar = validar_input($_GET['buscar'] ?? '');

    $condiciones = " AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ? ";
    $parametros = array_merge($parametros_alcance, [$fecha_desde, $fecha_hasta]);

    if ($buscar !== '') {
        $condiciones .= " AND (P.`gcp_agente_id` LIKE ? OR TA.`usu_nombres_apellidos` LIKE ?) ";
        $parametros[] = "%{$buscar}%";
        $parametros[] = "%{$buscar}%";
    }

    $tipos_bind = str_repeat('s', count($parametros));

    $sql =
        "SELECT P.`gcp_id`, P.`gcp_origen_tipo`, P.`gcp_registro_fecha`, P.`gcp_agente_id`,
                TA.`usu_nombres_apellidos` AS agente_nombre, TS.`usu_nombres_apellidos` AS supervisor_nombre,
                P.`gcp_segmento`, T.`gct_nombre`, AC.`ac_nombre_campania`,
                I.`gci_nombre` AS indicador_global,
                C.`gcc2_nota_general`, C.`gcc2_matriz`,
                R.`gcr_causa_raiz`, R.`gcr_estrategia_correctiva`,
                RA.`gcra_compromiso_general`
         FROM `tb_gestion_coaching_paquete` AS P
         LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
         LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
         LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
         LEFT JOIN `tb_administrador_campania` AS AC ON P.`gcp_campania_id` = AC.`ac_id`
         LEFT JOIN `tb_gestion_coaching_indicador` AS I ON P.`gcp_indicador_id` = I.`gci_id`
         LEFT JOIN `tb_gestion_coaching_calidad` AS C ON P.`gcp_id` = C.`gcc2_paquete`
         LEFT JOIN `tb_gestion_coaching_retroalimentacion` AS R ON P.`gcp_id` = R.`gcr_paquete`
         LEFT JOIN `tb_gestion_coaching_respuesta_agente` AS RA ON P.`gcp_id` = RA.`gcra_paquete`
         WHERE P.`gcp_activo` = 1 {$filtro_alcance_sql} {$condiciones}
         ORDER BY P.`gcp_registro_fecha` DESC
         LIMIT 500";

    $stmt = $enlace_db->prepare($sql);
    if (count($parametros) > 0) {
        $stmt->bind_param($tipos_bind, ...$parametros);
    }
    $stmt->execute();
    $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $meses_es = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

    /** Unifica el "indicador" mostrado según el origen del paquete. */
    function indicadorHistorico(array $r): string
    {
        if ($r['gcp_origen_tipo'] === 'monitoreo') {
            $nota = $r['gcc2_nota_general'] ?? null;
            $matriz = $r['gcc2_matriz'] ?? '';
            return 'Monitoreo de Calidad' . ($nota !== null ? " (Nota {$nota})" : '') . ($matriz !== '' ? " — {$matriz}" : '');
        }
        return $r['indicador_global'] ?? '—';
    }

    $query_filtros = http_build_query(['desde' => $fecha_desde, 'hasta' => $fecha_hasta, 'buscar' => $buscar]);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        .coaching_filtros { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 15px; }
        .coaching_filtros label { font-size: 11px; font-weight: bold; color: #1A1A1A; display: block; margin-bottom: 3px; }
        .coaching_filtros input { font-size: 12px; }
        .coaching_tabla_historico { font-size: 11px; }
        .coaching_tabla_historico th { white-space: nowrap; }
        .coaching_tabla_historico td { max-width: 220px; }
        .coaching_celda_texto { max-height: 70px; overflow-y: auto; display: block; }
        .div_tabla { overflow-x: auto; }
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
            <span>Histórico Seguimiento</span>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h4 class="titulo_seccion mb-0">Histórico Seguimiento</h4>
            <a href="gestion_coaching_estadisticas.php?<?php echo $query_filtros; ?>" class="btn-corp-2 px-3 py-1" style="border-radius:5px;">
                <span class="fas fa-chart-pie"></span> Ver estadísticas
            </a>
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
            <div style="flex:1; min-width:200px;">
                <label for="buscar">Cédula o nombre del colaborador</label>
                <input type="text" name="buscar" id="buscar" class="form-control" value="<?php echo htmlspecialchars($buscar); ?>" placeholder="Ingrese documento o nombre...">
            </div>
            <div>
                <button type="submit" class="btn-corp px-3 py-2" style="border-radius:5px; border:0;">
                    <span class="fas fa-filter"></span> Filtrar
                </button>
            </div>
        </form>

        <p style="font-size:11px; color:#6E6E6E;">
            <?php echo count($registros); ?> registro(s) cargado(s) <?php echo count($registros) === 500 ? '(máximo 500, refine el filtro para ver todos)' : ''; ?>
        </p>

        <div class="div_tabla">
            <table class="tabla_list coaching_tabla_historico">
                <thead>
                    <tr>
                        <th>Fecha de ingreso</th>
                        <th>Cédula colaborador</th>
                        <th>Nombre colaborador</th>
                        <th>Jefe inmediato</th>
                        <th>Segmento</th>
                        <th>Mes de la afectación</th>
                        <th>Indicador</th>
                        <th>Acción ejecutada</th>
                        <th>Comentarios líder</th>
                        <th>Compromiso del colaborador</th>
                        <th>Empresa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($registros) === 0): ?>
                        <tr><td colspan="11" class="text-center" style="padding:15px;">No hay registros para los filtros seleccionados.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($registros as $r):
                        $comentarios = trim(($r['gcr_causa_raiz'] ?? '') . (!empty($r['gcr_estrategia_correctiva']) ? ' — ' . $r['gcr_estrategia_correctiva'] : ''));
                        $mes_num = $r['gcp_registro_fecha'] ? (int) date('n', strtotime($r['gcp_registro_fecha'])) : null;
                    ?>
                        <tr class="tabla_contenido_1">
                            <td><?php echo $r['gcp_registro_fecha'] ? date('d/m/Y H:i', strtotime($r['gcp_registro_fecha'])) : '—'; ?></td>
                            <td><?php echo validar_output($r['gcp_agente_id']); ?></td>
                            <td><?php echo validar_output($r['agente_nombre'] ?? '—'); ?></td>
                            <td><?php echo validar_output($r['supervisor_nombre'] ?? '—'); ?></td>
                            <td><?php echo validar_output($r['gcp_segmento'] ?? '—'); ?></td>
                            <td><?php echo $mes_num ? $meses_es[$mes_num] : '—'; ?></td>
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
    <?php include("../footer.php"); ?>
</body>
</html>
