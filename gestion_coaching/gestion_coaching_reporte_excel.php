<?php
declare(strict_types=1);

/**
 * Exportación Excel del reporte de Coaching.
 *
 * Correcciones principales:
 * - Relación real: paquete -> acompañamiento -> escalamiento.
 * - Columnas reales de escalamiento con prefijo gces_.
 * - Conversión explícita de collations incompatibles.
 * - Limpieza del buffer antes de enviar el XLSX.
 * - Manejo de errores para evitar pantallas blancas.
 */

$modulo_plataforma = 'Coaching-Reportes';

require_once __DIR__ . '/../config/validaciones_seguridad.php';
require_once __DIR__ . '/../config/conexion_db.php';
require_once __DIR__ . '/lib/coaching_seguridad.php';

$autoload = __DIR__ . '/../PHPOffice/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('No se encontró PHPOffice/vendor/autoload.php. Verifique la instalación de PhpSpreadsheet.');
}

require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Permite mostrar el error en desarrollo sin dejar una pantalla blanca.
 * En producción conviene establecerlo en false.
 */
$modo_desarrollo = true;

if ($modo_desarrollo) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

try {
    if (!isset($enlace_db) || !($enlace_db instanceof mysqli)) {
        throw new RuntimeException('No se encontró una conexión mysqli válida en $enlace_db.');
    }

    $enlace_db->set_charset('utf8mb4');

    $tiene_reportes =
        isset($_SESSION['modulos_acceso_permisos']['Coaching-Reportes'])
        && $_SESSION['modulos_acceso_permisos']['Coaching-Reportes'] !== '';

    if (!$tiene_reportes) {
        header('Location: ../permiso_denegado.php');
        exit;
    }

    $usuario_actual = (string)($_SESSION['usu_id'] ?? '');
    if ($usuario_actual === '') {
        throw new RuntimeException('No fue posible identificar al usuario autenticado.');
    }

    $perfil_coaching = coachingPerfilUsuarioActual();

    $filtro_alcance_sql = '';
    $parametros_alcance = [];

    if (in_array($perfil_coaching, ['Supervisor', 'Agente'], true)) {
        [$filtro_alcance_sql, $parametros_alcance] = coachingFiltroAlcance(
            $perfil_coaching,
            $usuario_actual
        );
    }

    /*
     * Se mantienen los mismos filtros de la vista web.
     */
    $fecha_desde = validar_input($_GET['desde'] ?? '');
    $fecha_hasta = validar_input($_GET['hasta'] ?? '');

    if (
        $fecha_desde === ''
        || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)
        || DateTime::createFromFormat('Y-m-d', $fecha_desde) === false
    ) {
        $fecha_desde = date('Y-m-d', strtotime('-90 days'));
    }

    if (
        $fecha_hasta === ''
        || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)
        || DateTime::createFromFormat('Y-m-d', $fecha_hasta) === false
    ) {
        $fecha_hasta = date('Y-m-d');
    }

    if ($fecha_desde > $fecha_hasta) {
        [$fecha_desde, $fecha_hasta] = [$fecha_hasta, $fecha_desde];
    }

    $filtro_estado = validar_input($_GET['estado'] ?? '');
    $filtro_tipo   = validar_input($_GET['tipo'] ?? '');
    $filtro_origen = validar_input($_GET['origen'] ?? '');

    $condiciones = ' AND DATE(P.`gcp_registro_fecha`) BETWEEN ? AND ? ';
    $parametros = array_merge($parametros_alcance, [$fecha_desde, $fecha_hasta]);

    if ($filtro_estado !== '' && $filtro_estado !== 'Todos') {
        $condiciones .= ' AND E.`gce_codigo` = ? ';
        $parametros[] = $filtro_estado;
    }

    if ($filtro_tipo !== '' && $filtro_tipo !== 'Todos') {
        $condiciones .= ' AND T.`gct_codigo` = ? ';
        $parametros[] = $filtro_tipo;
    }

    if ($filtro_origen !== '' && $filtro_origen !== 'Todos') {
        $condiciones .= ' AND P.`gcp_origen_tipo` = ? ';
        $parametros[] = $filtro_origen;
    }

    $tipos_bind = str_repeat('s', count($parametros));

    /*
     * Relación real:
     *
     * P.gcp_id
     *      -> A.gca_paquete_id
     *      -> A.gca_id
     *      -> ESC.gces_acompanamiento_id
     *
     * Se convierten a utf8mb4_unicode_ci solamente las comparaciones
     * entre columnas que actualmente tienen collations diferentes.
     */
    $sql = "
        SELECT
            P.`gcp_id`,
            P.`gcp_origen_tipo`,
            T.`gct_nombre`,
            E.`gce_nombre`,
            TA.`usu_nombres_apellidos` AS agente_nombre,
            TS.`usu_nombres_apellidos` AS supervisor_nombre,
            P.`gcp_prioridad`,
            P.`gcp_registro_fecha`,
            P.`gcp_fecha_limite`,
            P.`gcp_fecha_cierre`,

            (
                SELECT GROUP_CONCAT(
                    DISTINCT I.`gci_nombre`
                    ORDER BY I.`gci_nombre`
                    SEPARATOR '; '
                )
                FROM `tb_gestion_coaching_paquete_indicador` AS PI
                INNER JOIN `tb_gestion_coaching_indicador` AS I
                    ON PI.`gcpi_indicador_id` = I.`gci_id`
                WHERE
                    CONVERT(PI.`gcpi_paquete` USING utf8mb4)
                        COLLATE utf8mb4_unicode_ci
                    =
                    CONVERT(P.`gcp_id` USING utf8mb4)
                        COLLATE utf8mb4_unicode_ci
            ) AS indicadores_multiples,

            A.`gca_id` AS acompanamiento_id,
            A.`gca_estado` AS acompanamiento_estado,

            ESC.`gces_destinatario_nombre`,
            ESC.`gces_asunto_correo`

        FROM `tb_gestion_coaching_paquete` AS P

        LEFT JOIN `tb_gestion_coaching_estado` AS E
            ON P.`gcp_estado_id` = E.`gce_id`

        LEFT JOIN `tb_gestion_coaching_tipo` AS T
            ON P.`gcp_tipo_id` = T.`gct_id`

        LEFT JOIN `tb_administrador_usuario` AS TA
            ON CONVERT(P.`gcp_agente_id` USING utf8mb4)
                COLLATE utf8mb4_unicode_ci
             = CONVERT(TA.`usu_id` USING utf8mb4)
                COLLATE utf8mb4_unicode_ci

        LEFT JOIN `tb_administrador_usuario` AS TS
            ON CONVERT(P.`gcp_supervisor_id` USING utf8mb4)
                COLLATE utf8mb4_unicode_ci
             = CONVERT(TS.`usu_id` USING utf8mb4)
                COLLATE utf8mb4_unicode_ci

        LEFT JOIN `tb_gestion_coaching_acompanamiento` AS A
            ON CONVERT(A.`gca_paquete_id` USING utf8mb4)
                COLLATE utf8mb4_unicode_ci
             = CONVERT(P.`gcp_id` USING utf8mb4)
                COLLATE utf8mb4_unicode_ci
           AND A.`gca_activo` = 1

        LEFT JOIN `tb_gestion_coaching_escalamiento` AS ESC
            ON ESC.`gces_acompanamiento_id` = A.`gca_id`

        WHERE P.`gcp_activo` = 1
        {$filtro_alcance_sql}
        {$condiciones}

        ORDER BY P.`gcp_registro_fecha` DESC
    ";

    $stmt = $enlace_db->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException(
            'No fue posible preparar la consulta del reporte: ' . $enlace_db->error
        );
    }

    if ($parametros !== []) {
        $stmt->bind_param($tipos_bind, ...$parametros);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    if (!$resultado) {
        throw new RuntimeException('No fue posible obtener los resultados del reporte.');
    }

    $registros = $resultado->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    /*
     * Construcción del Excel.
     */
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Coaching');

    $encabezados = [
        'Código',
        'Origen',
        'Tipo',
        'Agente',
        'Supervisor',
        'Prioridad',
        'Estado',
        'Fecha creación',
        'Fecha límite',
        'Fecha cierre',
        'Indicadores',
        'Escalamiento - Destinatario',
        'Escalamiento - Asunto',
    ];

    $columnas = range('A', 'M');

    $anchos = [
        'A' => 18,
        'B' => 16,
        'C' => 24,
        'D' => 30,
        'E' => 30,
        'F' => 15,
        'G' => 22,
        'H' => 18,
        'I' => 18,
        'J' => 18,
        'K' => 40,
        'L' => 32,
        'M' => 45,
    ];

    foreach ($anchos as $columna => $ancho) {
        $sheet->getColumnDimension($columna)->setWidth($ancho);
    }

    $sheet->setCellValue('A1', 'Reporte de Coaching — IQ-ICBF');
    $sheet->mergeCells('A1:M1');

    $sheet->getStyle('A1:M1')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 15,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1F4E78'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $sheet->getRowDimension(1)->setRowHeight(26);

    $sheet->setCellValue(
        'A2',
        'Rango: '
        . date('d/m/Y', strtotime($fecha_desde))
        . ' a '
        . date('d/m/Y', strtotime($fecha_hasta))
        . ' — Generado: '
        . date('d/m/Y H:i')
        . ' por '
        . $usuario_actual
    );

    $sheet->mergeCells('A2:M2');
    $sheet->getStyle('A2:M2')->applyFromArray([
        'font' => [
            'italic' => true,
            'size' => 9,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_LEFT,
        ],
    ]);

    $fila_encabezado = 4;

    foreach ($columnas as $indice => $columna) {
        $sheet->setCellValue(
            $columna . $fila_encabezado,
            $encabezados[$indice]
        );
    }

    $sheet->getStyle("A{$fila_encabezado}:M{$fila_encabezado}")
        ->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9E2F3'],
                ],
            ],
        ]);

    $sheet->freezePane('A5');
    $sheet->setAutoFilter("A{$fila_encabezado}:M{$fila_encabezado}");

    $fila_actual = 5;

    foreach ($registros as $registro) {
        $sheet->setCellValue('A' . $fila_actual, (string)$registro['gcp_id']);
        $sheet->setCellValue(
            'B' . $fila_actual,
            ucfirst((string)$registro['gcp_origen_tipo'])
        );
        $sheet->setCellValue('C' . $fila_actual, (string)($registro['gct_nombre'] ?? ''));
        $sheet->setCellValue('D' . $fila_actual, (string)($registro['agente_nombre'] ?? '—'));
        $sheet->setCellValue('E' . $fila_actual, (string)($registro['supervisor_nombre'] ?? '—'));
        $sheet->setCellValue('F' . $fila_actual, (string)($registro['gcp_prioridad'] ?? ''));
        $sheet->setCellValue('G' . $fila_actual, (string)($registro['gce_nombre'] ?? ''));

        $sheet->setCellValue(
            'H' . $fila_actual,
            !empty($registro['gcp_registro_fecha'])
                ? date('d/m/Y H:i', strtotime((string)$registro['gcp_registro_fecha']))
                : ''
        );

        $sheet->setCellValue(
            'I' . $fila_actual,
            !empty($registro['gcp_fecha_limite'])
                ? date('d/m/Y H:i', strtotime((string)$registro['gcp_fecha_limite']))
                : ''
        );

        $sheet->setCellValue(
            'J' . $fila_actual,
            !empty($registro['gcp_fecha_cierre'])
                ? date('d/m/Y H:i', strtotime((string)$registro['gcp_fecha_cierre']))
                : ''
        );

        $sheet->setCellValue(
            'K' . $fila_actual,
            (string)($registro['indicadores_multiples'] ?? '')
        );

        $sheet->setCellValue(
            'L' . $fila_actual,
            (string)($registro['gces_destinatario_nombre'] ?? '')
        );

        $sheet->setCellValue(
            'M' . $fila_actual,
            (string)($registro['gces_asunto_correo'] ?? '')
        );

        $fila_actual++;
    }

    if ($registros === []) {
        $sheet->setCellValue('A5', 'No se encontraron registros para los filtros seleccionados.');
        $sheet->mergeCells('A5:M5');
        $sheet->getStyle('A5:M5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $fila_actual = 6;
    }

    $ultima_fila = max(5, $fila_actual - 1);

    $sheet->getStyle("A5:M{$ultima_fila}")
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_TOP)
        ->setWrapText(true);

    $sheet->getStyle("A4:M{$ultima_fila}")
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN)
        ->getColor()
        ->setRGB('D9E2F3');

    $sheet->getPageSetup()
        ->setOrientation(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
        )
        ->setFitToWidth(1)
        ->setFitToHeight(0);

    $sheet->getPageMargins()
        ->setTop(0.5)
        ->setRight(0.3)
        ->setLeft(0.3)
        ->setBottom(0.5);

    $nombre_archivo = 'Coaching_Reporte_' . date('Ymd_His') . '.xlsx';

    /*
     * Cualquier espacio, BOM, warning o HTML previo corrompe el XLSX.
     * Se limpian todos los buffers antes de enviar la descarga.
     */
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header(
        'Content-Disposition: attachment; filename="' . $nombre_archivo . '"'
    );
    header('Cache-Control: max-age=0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');

    $writer = new Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);
    $writer->save('php://output');

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

    exit;
} catch (Throwable $error) {
    error_log(
        '[gestion_coaching_reporte_excel.php] '
        . $error->getMessage()
        . PHP_EOL
        . $error->getTraceAsString()
    );

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');

    if ($modo_desarrollo) {
        echo "No fue posible generar el Excel.\n\n";
        echo 'Error: ' . $error->getMessage() . "\n";
        echo 'Archivo: ' . $error->getFile() . "\n";
        echo 'Línea: ' . $error->getLine() . "\n";
    } else {
        echo 'No fue posible generar el reporte. Consulte el registro de errores del servidor.';
    }

    exit;
}