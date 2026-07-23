<?php
    $modulo_plataforma = "Coaching-Reportes";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once('../PHPOffice/vendor/autoload.php');

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    // Mismos filtros que gestion_coaching_reporte.php, para que el Excel
    // exportado sea exactamente lo que la persona ya está viendo en pantalla.
    $fecha_desde = validar_input($_GET['desde'] ?? '');
    $fecha_hasta = validar_input($_GET['hasta'] ?? '');
    if ($fecha_desde === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) { $fecha_desde = date('Y-m-d', strtotime('-90 days')); }
    if ($fecha_hasta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) { $fecha_hasta = date('Y-m-d'); }

    $filtro_estado = validar_input($_GET['estado'] ?? '');
    $filtro_tipo = validar_input($_GET['tipo'] ?? '');
    $filtro_origen = validar_input($_GET['origen'] ?? '');

    $condiciones = " AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ? ";
    $parametros = array_merge($parametros_alcance, [$fecha_desde, $fecha_hasta]);

    if ($filtro_estado !== '' && $filtro_estado !== 'Todos') { $condiciones .= " AND E.`gce_codigo` = ? "; $parametros[] = $filtro_estado; }
    if ($filtro_tipo !== '' && $filtro_tipo !== 'Todos') { $condiciones .= " AND T.`gct_codigo` = ? "; $parametros[] = $filtro_tipo; }
    if ($filtro_origen !== '' && $filtro_origen !== 'Todos') { $condiciones .= " AND P.`gcp_origen_tipo` = ? "; $parametros[] = $filtro_origen; }

    $tipos_bind = str_repeat('s', count($parametros));

    // Sin límite de 500 aquí (a diferencia de la vista en pantalla): el
    // Excel sí debe traer el set completo que cumple el filtro.
    $sql =
        "SELECT P.`gcp_id`, P.`gcp_origen_tipo`, T.`gct_nombre`, E.`gce_nombre`,
                TA.`usu_nombres_apellidos` AS agente_nombre, TS.`usu_nombres_apellidos` AS supervisor_nombre,
                P.`gcp_prioridad`, P.`gcp_registro_fecha`, P.`gcp_fecha_limite`, P.`gcp_fecha_cierre`,
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
         ORDER BY P.`gcp_registro_fecha` DESC";

    $stmt = $enlace_db->prepare($sql);
    if (count($parametros) > 0) {
        $stmt->bind_param($tipos_bind, ...$parametros);
    }
    $stmt->execute();
    $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // ---- Construcción del Excel ----
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $spreadsheet->getActiveSheet()->setTitle('Coaching');

    $encabezados = ['Código', 'Origen', 'Tipo', 'Agente', 'Supervisor', 'Prioridad', 'Estado', 'Fecha creación', 'Fecha límite', 'Fecha cierre', 'Indicadores', 'Escalamiento - Destinatario', 'Escalamiento - Asunto'];
    $columnas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];

    foreach ($columnas as $col) {
        $sheet->getColumnDimension($col)->setWidth(20);
    }

    $sheet->setCellValue('A1', 'Reporte de Coaching — IQ-ICBF');
    $sheet->mergeCells('A1:M1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $sheet->setCellValue('A2', 'Rango: ' . $fecha_desde . ' a ' . $fecha_hasta . ' — Generado: ' . date('d/m/Y H:i') . ' por ' . $_SESSION['usu_id']);
    $sheet->mergeCells('A2:M2');
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);

    $fila_actual = 4;
    foreach ($columnas as $i => $col) {
        $sheet->setCellValue($col . $fila_actual, $encabezados[$i]);
    }
    $sheet->getStyle('A' . $fila_actual . ':M' . $fila_actual)->getFont()->setBold(true);
    $sheet->getStyle('A' . $fila_actual . ':M' . $fila_actual)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('4CAF50');
    $sheet->getStyle('A' . $fila_actual . ':M' . $fila_actual)->getFont()->getColor()->setRGB('FFFFFF');

    $fila_actual++;
    foreach ($registros as $r) {
        $sheet->setCellValue('A' . $fila_actual, $r['gcp_id']);
        $sheet->setCellValue('B' . $fila_actual, ucfirst($r['gcp_origen_tipo']));
        $sheet->setCellValue('C' . $fila_actual, $r['gct_nombre']);
        $sheet->setCellValue('D' . $fila_actual, $r['agente_nombre'] ?? '—');
        $sheet->setCellValue('E' . $fila_actual, $r['supervisor_nombre'] ?? '—');
        $sheet->setCellValue('F' . $fila_actual, $r['gcp_prioridad']);
        $sheet->setCellValue('G' . $fila_actual, $r['gce_nombre']);
        $sheet->setCellValue('H' . $fila_actual, $r['gcp_registro_fecha'] ? date('d/m/Y', strtotime($r['gcp_registro_fecha'])) : '');
        $sheet->setCellValue('I' . $fila_actual, $r['gcp_fecha_limite'] ? date('d/m/Y', strtotime($r['gcp_fecha_limite'])) : '');
        $sheet->setCellValue('J' . $fila_actual, $r['gcp_fecha_cierre'] ? date('d/m/Y', strtotime($r['gcp_fecha_cierre'])) : '');
        $sheet->setCellValue('K' . $fila_actual, $r['indicadores_multiples'] ?? '');
        $sheet->setCellValue('L' . $fila_actual, $r['gcesc_destinatario_nombre'] ?? '');
        $sheet->setCellValue('M' . $fila_actual, $r['gcesc_asunto'] ?? '');
        $fila_actual++;
    }

    $nombre_archivo = 'Coaching_Reporte_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
