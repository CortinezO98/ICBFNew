<?php
// Validación de permisos del usuario para el módulo
$modulo_plataforma = "Gestión Alertas";

require_once("../config/validaciones_seguridad.php");
require_once("../config/conexion_db.php");

require_once('../PHPOffice/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

// Respuesta JSON
header('Content-Type: application/json; charset=UTF-8');

/**
 * Escape seguro para HTML (evita XSS en strings incrustados en HTML).
 */
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Limpieza segura para texto libre (observaciones).
 * No “rompe” saltos de línea, pero elimina caracteres de control peligrosos.
 */
function clean_textarea($value): string {
    $v = (string)$value;
    $v = str_replace(["\0"], '', $v);
    // quitar controles excepto \n y \r y \t
    $v = preg_replace('/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
    return trim($v);
}

/**
 * Valida fechas tipo YYYY-MM-DD (no cambia lógica, sólo evita basura).
 */
function is_valid_ymd(string $date): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
}

/**
 * Evita fórmula injection en Excel: si un texto inicia con = + - @, lo “neutraliza”.
 */
function excel_safe($value) {
    if (is_string($value)) {
        $v = ltrim($value);
        if ($v !== '' && in_array($v[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }
    }
    return $value;
}

/**
 * Ejecuta SELECT preparado y retorna fetch_all(MYSQLI_NUM)
 */
function fetch_all_prepared(mysqli $db, string $sql, string $types = '', array $params = []): array {
    $stmt = $db->prepare($sql);
    if (!$stmt) return [];
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res ? $res->fetch_all(MYSQLI_NUM) : [];
    $stmt->close();
    return $data;
}

/**
 * Respuesta estándar
 */
function respond(array $datos): void {
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   DEFINICIÓN DE VARIABLES
   ========================= */

$fecha_inicio = isset($_POST['inicio']) ? validar_input($_POST['inicio']) : '';
$fecha_fin    = isset($_POST['fin']) ? validar_input($_POST['fin']) : '';
$accion       = isset($_POST['acc']) ? validar_input($_POST['acc']) : '';
$observaciones = isset($_POST['observaciones']) ? clean_textarea($_POST['observaciones']) : '';

// En el código original se usa $id_registro en el INSERT de notificación, pero no se define.
// Para no afectar comportamiento (antes terminaba en ''/NULL), lo dejamos como string vacío por defecto.
$id_registro = isset($_POST['reg']) ? validar_input($_POST['reg']) : '';

if (!is_valid_ymd($fecha_inicio) || !is_valid_ymd($fecha_fin)) {
    $lista_informe = '<p class="alert alert-warning col-md-12 p-1 font-size-11 mb-1">
                        <span class="fas fa-exclamation-triangle"></span> Parámetros de fecha inválidos
                      </p>';
    respond(['informe_lista' => $lista_informe, 'informe_estado' => '']);
}

$fecha_fin_end = $fecha_fin . " 23:59:59";

/* =========================
   CONSULTAS (SQLi -> PREPARED)
   ========================= */

$sql_regionales = "SELECT
        `ga_regional`,
        TR.`gere_regional`,
        COUNT(`ga_id`) AS TOTAL,
        TR.`gere_correos_trimestral`
    FROM `tb_gestion_alertas`
    LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id`
    LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id`
    WHERE
        `ga_afecta_linea_tecnica`='Si'
        AND `ga_estado`='Escalado Regional'
        AND `ga_fecha_alerta`>=?
        AND `ga_fecha_alerta`<=?
    GROUP BY `ga_regional`
    ORDER BY TOTAL DESC, TR.`gere_regional`, TCZ.`gercz_centro_zonal`";

$resultado_regionales = fetch_all_prepared($enlace_db, $sql_regionales, 'ss', [$fecha_inicio, $fecha_fin_end]);

$sql_czonales = "SELECT
        `ga_regional`,
        TR.`gere_regional`,
        `ga_centro_zonal`,
        TCZ.`gercz_centro_zonal`,
        COUNT(`ga_id`) AS TOTAL,
        TCZ.`gercz_correos_trimestral`
    FROM `tb_gestion_alertas`
    LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id`
    LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id`
    WHERE
        `ga_afecta_linea_tecnica`='Si'
        AND `ga_estado`='Escalado Regional'
        AND `ga_fecha_alerta`>=?
        AND `ga_fecha_alerta`<=?
    GROUP BY `ga_regional`, `ga_centro_zonal`
    ORDER BY TOTAL DESC, TR.`gere_regional`, TCZ.`gercz_centro_zonal`";

$resultado_czonales = fetch_all_prepared($enlace_db, $sql_czonales, 'ss', [$fecha_inicio, $fecha_fin_end]);

$array_czonal = [];
$array_czonal_data = [];

for ($i = 0; $i < count($resultado_czonales); $i++) {
    $regional_name = (string)$resultado_czonales[$i][1];
    $cz_name       = (string)$resultado_czonales[$i][3];

    $array_czonal[$regional_name][] = $cz_name;
    $array_czonal_data[$regional_name][$cz_name]['cantidad'] = $resultado_czonales[$i][4];
    $array_czonal_data[$regional_name][$cz_name]['correos']  = $resultado_czonales[$i][5];
}

/* =========================
   HTML informe (XSS -> ESCAPE)
   ========================= */

if (count($resultado_regionales) > 0) {
    $estado_envio = "generado";

    $lista_informe = '<table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle">Regional</th>
                                <th class="align-middle">Total Alertas</th>
                            </tr>
                        </thead>
                        <tbody>';

    $total_regionales = 0;
    for ($i = 0; $i < count($resultado_regionales); $i++) {
        $lista_informe .= '<tr>
                            <td class="align-middle">' . e($resultado_regionales[$i][1]) . '</td>
                            <td class="align-middle text-center">' . e($resultado_regionales[$i][2]) . '</td>
                           </tr>';
        $total_regionales += (int)$resultado_regionales[$i][2];
    }

    $lista_informe .= '<tr>
                        <td class="align-middle font-weight-bold">Total</td>
                        <td class="align-middle text-center font-weight-bold">' . e($total_regionales) . '</td>
                       </tr>';

    $lista_informe .= '</tbody></table><br>';

    $lista_informe .= '<table class="table table-bordered table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th class="align-middle">Regional/Centro Zonal</th>
                                <th class="align-middle">Total Alertas</th>
                            </tr>
                        </thead>
                        <tbody>';

    for ($i = 0; $i < count($resultado_regionales); $i++) {
        $regional_name = (string)$resultado_regionales[$i][1];

        $lista_informe .= '<tr>
                            <td class="align-middle font-weight-bold">' . e($regional_name) . '</td>
                            <td class="align-middle text-center font-weight-bold">' . e($resultado_regionales[$i][2]) . '</td>
                           </tr>';

        if (!empty($array_czonal[$regional_name])) {
            for ($j = 0; $j < count($array_czonal[$regional_name]); $j++) {
                $cz = $array_czonal[$regional_name][$j];
                $cant = $array_czonal_data[$regional_name][$cz]['cantidad'] ?? 0;

                $lista_informe .= '<tr>
                                    <td class="align-middle pl-4 font-size-11">' . e($cz) . '</td>
                                    <td class="align-middle text-center font-size-11">' . e($cant) . '</td>
                                   </tr>';
            }
        }
    }

    $lista_informe .= '<tr>
                        <td class="align-middle font-weight-bold">Total</td>
                        <td class="align-middle text-center font-weight-bold">' . e($total_regionales) . '</td>
                       </tr>';

    $lista_informe .= '</tbody></table>';

} else {
    $lista_informe = '<p class="alert alert-warning col-md-12 p-1 font-size-11 mb-1">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                      </p>';
    $estado_envio = "";
}

/* =========================
   Enviar notificación + Excel
   ========================= */

if ($accion === "enviar") {

    if (count($resultado_regionales) > 0) {
        $destinatarios_notificacion = "";

        /* ========= GENERAR REPORTE EXCEL (manteniendo diseño, mitigando riesgos) ========= */

        $titulo_reporte = "Gestión Alertas-Consolidado " . date('Y-m-d H_i_s') . ".xlsx";
        $titulo_hoja = "Consolidado";

        $sql_reporte = "SELECT
                `ga_id`, `ga_sim_asociado`, `ga_sim_qr_encuesta`, `ga_estado`, `ga_fecha_alerta`, `ga_fecha_atencion_ciudadano`,
                `ga_regional`, `ga_centro_zonal`, `ga_peticionario_id`, `ga_peticionario_nombre`, `ga_peticionario_telefono`,
                `ga_categoria_afectacion`, `ga_descripcion_alerta`, `ga_afecta_linea_tecnica`, `ga_fecha_remite_agente_especializado`,
                `ga_fecha_require_regional`, `ga_fecha_respuesta_regional`, `ga_concepto_tecnico_pmejora`, `ga_decision_caso`, `ga_snc_ac`,
                `ga_fecha_cierre`, `ga_accion_mejora`, `ga_revisa_n1_usuario`, `ga_revisa_n1_fecha`, `ga_revisa_n2_usuario`, `ga_revisa_n2_fecha`,
                `ga_registro_usuario`, `ga_registro_fecha`,
                TR.`gere_regional`, TCZ.`gercz_centro_zonal`, TU.`usu_nombres_apellidos`, `ga_fecha_cierre`
            FROM `tb_gestion_alertas`
            LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_alertas`.`ga_registro_usuario`=TU.`usu_id`
            LEFT JOIN `tb_gestion_encuesta_regional` AS TR ON `tb_gestion_alertas`.`ga_regional`=TR.`gere_id`
            LEFT JOIN `tb_gestion_encuesta_regional_czonal` AS TCZ ON `tb_gestion_alertas`.`ga_centro_zonal`=TCZ.`gercz_id`
            WHERE
                `ga_afecta_linea_tecnica`='Si'
                AND `ga_estado`='Escalado Regional'
                AND `ga_fecha_alerta`>=?
                AND `ga_fecha_alerta`<=?
            ORDER BY `ga_id` ASC";

        $resultado_registros = fetch_all_prepared($enlace_db, $sql_reporte, 'ss', [$fecha_inicio, $fecha_fin_end]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("IQ-ICBF Gestión Integrada de Servicios")
            ->setLastModifiedBy($_SESSION['usu_nombre_completo'])
            ->setTitle("IQ-ICBF Gestión Integrada de Servicios")
            ->setSubject("IQ-ICBF Gestión Integrada de Servicios")
            ->setDescription("IQ-ICBF Gestión Integrada de Servicios")
            ->setKeywords("IQ-ICBF Gestión Integrada de Servicios")
            ->setCategory("Reporte");

        $styleArrayTitulos = [
            'font' => [
                'bold' => true,
                'size' => '8',
                'name' => 'Arial',
                'color' => ['rgb' => '2E2E2E']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '72BF44',
                ]
            ],
        ];

        $spreadsheet->getActiveSheet()->setTitle($titulo_hoja);

        // Estilos (igual al original)
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('5')->setRowHeight(15);
        $spreadsheet->getActiveSheet()->getRowDimension('6')->setRowHeight(20);

        foreach (range('A', 'U') as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(20);
        }

        $spreadsheet->getActiveSheet()->getStyle('A6:U6')->applyFromArray($styleArrayTitulos);
        $spreadsheet->getActiveSheet()->setAutoFilter('A6:U6');

        // Títulos
        $spreadsheet->getActiveSheet()->setCellValue('A6', 'NÚMERO DE ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('B6', 'ESTADO');
        $spreadsheet->getActiveSheet()->setCellValue('C6', 'SIM ASOCIADO');
        $spreadsheet->getActiveSheet()->setCellValue('D6', 'SIM QUEJA O RECLAMO AL MOMENTO DE LA ENCUESTA');
        $spreadsheet->getActiveSheet()->setCellValue('E6', 'FECHA DE ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('F6', 'FECHA DE ATENCIÓN AL CIUDADANO');
        $spreadsheet->getActiveSheet()->setCellValue('G6', 'REGIONAL AFECTADA POR LA ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('H6', 'PUNTO DE ATENCIÓN AFECTADO POR LA ALERTA (CENTRO ZONAL)');
        $spreadsheet->getActiveSheet()->setCellValue('I6', 'CATEGORÍA DE AFECTACIÓN');
        $spreadsheet->getActiveSheet()->setCellValue('J6', 'DESCRIPCIÓN DE LA ALERTA');
        $spreadsheet->getActiveSheet()->setCellValue('K6', 'VALIDACIÓN DE LA AFECTACIÓN DE LA LÍNEA TÉCNICA DEL PROCESO (SI / NO)');
        $spreadsheet->getActiveSheet()->setCellValue('L6', 'FECHA DE REMISIÓN AL AGENTE ESPECIALIZADO ');
        $spreadsheet->getActiveSheet()->setCellValue('M6', 'FECHA DE REQUERIMIENTO A LA REGIONAL');
        $spreadsheet->getActiveSheet()->setCellValue('N6', 'RESPUESTA POR PARTE DE LA REGIONAL AL REQUERIMIENTO (SI / NO)');
        $spreadsheet->getActiveSheet()->setCellValue('O6', 'FECHA RESPUESTA DE LA REGIONAL');
        $spreadsheet->getActiveSheet()->setCellValue('P6', 'SÍNTESIS DE LA RESPUESTA DE LA REGIONAL Y CONCEPTO TÉCNICO FRENTE A LA RESPUESTA DE LA REGIONAL');
        $spreadsheet->getActiveSheet()->setCellValue('Q6', 'DECISIÓN DEL CASO');
        $spreadsheet->getActiveSheet()->setCellValue('R6', 'No. SNC/AC');
        $spreadsheet->getActiveSheet()->setCellValue('S6', 'SEGUIMIENTO A LA DECISIÓN ');
        $spreadsheet->getActiveSheet()->setCellValue('T6', 'CIERRE DE LA ALERTA (SI/NO)');
        $spreadsheet->getActiveSheet()->setCellValue('U6', 'AGENTE REMITENTE');

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Gestión Alertas');
        $spreadsheet->getActiveSheet()->setCellValue('A2', 'Tipo reporte: Consolidado');
        $spreadsheet->getActiveSheet()->setCellValue('A3', 'Estado: Escalado Regional');
        $spreadsheet->getActiveSheet()->setCellValue('A4', 'Fecha filtro: ' . $fecha_inicio . ' A ' . $fecha_fin);
        $spreadsheet->getActiveSheet()->setCellValue('A5', 'Fecha reporte: ' . date('Y-m-d H:i:s'));

        // Data (mismo mapeo, mitigando fórmula injection)
        for ($i = 7; $i < count($resultado_registros) + 7; $i++) {
            $row = $resultado_registros[$i - 7];

            // Para no alterar “tipos”, usamos explícito string sólo cuando sea texto con riesgo
            $spreadsheet->getActiveSheet()->setCellValue('A' . $i, $row[0]);
            $spreadsheet->getActiveSheet()->setCellValue('B' . $i, excel_safe($row[3]));
            $spreadsheet->getActiveSheet()->setCellValue('C' . $i, excel_safe($row[1]));
            $spreadsheet->getActiveSheet()->setCellValue('D' . $i, excel_safe($row[2]));
            $spreadsheet->getActiveSheet()->setCellValue('E' . $i, excel_safe($row[4]));
            $spreadsheet->getActiveSheet()->setCellValue('F' . $i, excel_safe($row[5]));
            $spreadsheet->getActiveSheet()->setCellValue('G' . $i, excel_safe($row[28]));
            $spreadsheet->getActiveSheet()->setCellValue('H' . $i, excel_safe($row[29]));
            $spreadsheet->getActiveSheet()->setCellValue('I' . $i, excel_safe($row[11]));
            $spreadsheet->getActiveSheet()->setCellValue('J' . $i, excel_safe($row[12]));
            $spreadsheet->getActiveSheet()->setCellValue('K' . $i, excel_safe($row[13]));
            $spreadsheet->getActiveSheet()->setCellValue('L' . $i, excel_safe($row[14]));
            $spreadsheet->getActiveSheet()->setCellValue('M' . $i, excel_safe($row[15]));
            $spreadsheet->getActiveSheet()->setCellValue('N' . $i, excel_safe($row[16]));
            $spreadsheet->getActiveSheet()->setCellValue('O' . $i, excel_safe($row[16])); // se mantiene igual al original
            $spreadsheet->getActiveSheet()->setCellValue('P' . $i, '');
            $spreadsheet->getActiveSheet()->setCellValue('Q' . $i, excel_safe($row[18]));
            $spreadsheet->getActiveSheet()->setCellValue('R' . $i, excel_safe($row[19]));
            $spreadsheet->getActiveSheet()->setCellValue('S' . $i, '');
            $spreadsheet->getActiveSheet()->setCellValue('T' . $i, excel_safe($row[20]));
            $spreadsheet->getActiveSheet()->setCellValue('U' . $i, excel_safe($row[30]));
        }

        $dir_temporal = '/var/www/html/gestion_alertas/temporal/';
        if (!is_dir($dir_temporal)) {
            @mkdir($dir_temporal, 0750, true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($dir_temporal . $titulo_reporte);

        /* ========= ARMAR DESTINATARIOS (sin romper formato, limpiando control chars) ========= */

        // Correos regionales/czonales vienen del mismo resultado ya cargado
        for ($i = 0; $i < count($resultado_regionales); $i++) {
            if (!empty($resultado_regionales[$i][3])) {
                $dest_reg = explode(';', (string)$resultado_regionales[$i][3]);
                for ($k = 0; $k < count($dest_reg); $k++) {
                    $mail = trim(str_replace(["\r", "\n"], '', $dest_reg[$k]));
                    if ($mail !== '') {
                        $destinatarios_notificacion .= $mail . "|" . $mail . ";";
                    }
                }
            }
        }

        for ($i = 0; $i < count($resultado_regionales); $i++) {
            $regional_name = (string)$resultado_regionales[$i][1];
            if (!empty($array_czonal[$regional_name])) {
                for ($j = 0; $j < count($array_czonal[$regional_name]); $j++) {
                    $cz = $array_czonal[$regional_name][$j];
                    $correos_cz = $array_czonal_data[$regional_name][$cz]['correos'] ?? '';
                    if ($correos_cz !== '') {
                        $dest_cz = explode(';', (string)$correos_cz);
                        for ($k = 0; $k < count($dest_cz); $k++) {
                            $mail = trim(str_replace(["\r", "\n"], '', $dest_cz[$k]));
                            if ($mail !== '') {
                                $destinatarios_notificacion .= $mail . "|" . $mail . ";";
                            }
                        }
                    }
                }
            }
        }

        /* ========= ARMAR CUERPO DE CORREO (XSS -> ESCAPE) ========= */

        $lista_informe_correo = "<table style='width: 400px; font-size: 12px; font-family: Lato, Arial, sans-serif;'>
            <tr>
                <td style='width: 70%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Regional</td>
                <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Total Alertas</td>
            </tr>";

        $total_regionales = 0;
        for ($i = 0; $i < count($resultado_regionales); $i++) {
            $lista_informe_correo .= "<tr>
                <td style='width: 70%;padding: 3px 3px 3px 3px;background-color: #F2F2F2;'>" . e($resultado_regionales[$i][1]) . "</td>
                <td style='width: 30%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; text-align: center;'>" . e($resultado_regionales[$i][2]) . "</td>
            </tr>";
            $total_regionales += (int)$resultado_regionales[$i][2];
        }

        $lista_informe_correo .= "<tr>
            <td style='width: 70%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; font-weight: bold;'>Total</td>
            <td style='width: 30%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; text-align: center; font-weight: bold;'>" . e($total_regionales) . "</td>
        </tr>";

        $lista_informe_correo .= "</table><br>";

        $lista_informe_correo .= "<table style='width: 400px; font-size: 12px; font-family: Lato, Arial, sans-serif;'>
            <tr>
                <td style='width: 70%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Regional/Centro Zonal</td>
                <td style='width: 30%;background-color: #4CAF50; color: #FFFFFF; padding: 5px 5px 5px 5px; text-align: center;'>Total Alertas</td>
            </tr>";

        for ($i = 0; $i < count($resultado_regionales); $i++) {
            $regional_name = (string)$resultado_regionales[$i][1];

            $lista_informe_correo .= "<tr>
                <td style='width: 70%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; font-weight: bold;'>" . e($regional_name) . "</td>
                <td style='width: 30%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; font-weight: bold; text-align: center;'>" . e($resultado_regionales[$i][2]) . "</td>
            </tr>";

            if (!empty($array_czonal[$regional_name])) {
                for ($j = 0; $j < count($array_czonal[$regional_name]); $j++) {
                    $cz = $array_czonal[$regional_name][$j];
                    $cant = $array_czonal_data[$regional_name][$cz]['cantidad'] ?? 0;

                    $lista_informe_correo .= "<tr>
                        <td style='width: 70%;padding: 2px 2px 2px 15px;background-color: #F2F2F2;'>" . e($cz) . "</td>
                        <td style='width: 30%;padding: 2px 2px 2px 2px;background-color: #F2F2F2; text-align: center;'>" . e($cant) . "</td>
                    </tr>";
                }
            }
        }

        $lista_informe_correo .= "<tr>
            <td style='width: 70%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; font-weight: bold;'>Total</td>
            <td style='width: 30%;padding: 3px 3px 3px 3px;background-color: #F2F2F2; text-align: center; font-weight: bold;'>" . e($total_regionales) . "</td>
        </tr>";

        $lista_informe_correo .= "</table>";

        if ($observaciones !== "") {
            $observaciones_enviar = "<b>Notas: </b>" . nl2br(e($observaciones)) . "<br>";
        } else {
            $observaciones_enviar = "";
        }

        $contenido_correo = "<p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'><b>Cordial saludo,</b><br><br>A continuación se relacionan las alertas generadas entre el " . e($fecha_inicio) . " y el " . e($fecha_fin) . ".</p><br>
            <center>
            " . $lista_informe_correo . "
            <br>
            </center>
            <p style='font-size: 12px; color: #2E2E2E; font-family: Lato, Arial, sans-serif;'>" . $observaciones_enviar . "</p><br>
            <center>
            <table style='width: 100%; background: #2E2E2E; font-family: Lato, Arial, sans-serif;'>
                <tr>
                    <td style='font-size: 12px; padding: 5px 10px 5px 10px; color: #FFFFFF'>
                        IQ-ICBF | Gestión Integrada de Servicios | &copy; Copyright 2020 Todos los derechos reservados.
                    </td>
                </tr>
            </table>
            <p style='font-family: Lato, Arial, sans-serif; color: #2E2E2E;'>
            Esta es una notificación automática, por favor no responda este mensaje.
            </p>
            <table>
                <tr>
                    <td style='width: 50px; height: 73px;'><img src='cid:logo_firma_verde' style='margin: 5px; width: 50px; height: 73px;'></td>
                    <td>
                        <p style='font-size: 11px; color: #006400; font-family: Lato, Arial, sans-serif;'><br><b>No imprima este mensaje de no ser necesario; de ésta manera aportamos al cuidado del planeta.</b></p>
                    </td>
                </tr>
            </table>
            </center>";

        /* ========= DESTINATARIOS BASE (SQLi -> PREPARED) ========= */

        $sql_dest = "SELECT `gad_id`, `gad_filtro`, `gad_correos_to`, `gad_correos_cc`
                     FROM `tb_gestion_alertas_destinatarios`
                     WHERE `gad_filtro`='informe_regional'";

        $resultado_dest = fetch_all_prepared($enlace_db, $sql_dest);

        $to_raw = $resultado_dest[0][2] ?? '';
        $cc_raw = $resultado_dest[0][3] ?? '';

        $destino_to = explode(";", (string)$to_raw);
        $nc_address = $destinatarios_notificacion;

        for ($j = 0; $j < count($destino_to); $j++) {
            $mail = trim(str_replace(["\r", "\n"], '', $destino_to[$j]));
            if ($mail !== "") {
                $nc_address .= $mail . '|' . $mail . ';';
            }
        }

        $destino_cc = explode(";", (string)$cc_raw);

        // En este script original se referencian variables inexistentes ($resultado_registros_validar_notificacion)
        // Para NO afectar funcionalidad, dejamos CC como lista definida por BD (y vacío si no hay).
        $nc_cc = "";
        for ($j = 0; $j < count($destino_cc); $j++) {
            $mail = trim(str_replace(["\r", "\n"], '', $destino_cc[$j]));
            if ($mail !== "") {
                $nc_cc .= $mail . '|' . $mail . ';';
            }
        }

        $nc_id_set_from = "2";
        $nc_bcc = "mariostiv@hotmail.com|mariostiv@hotmail.com;";
        $nc_subject = "Informe Gestión Alertas";
        $nc_body = str_replace("'", '"', $contenido_correo);
        $nc_embeddedimage_ruta = "/var/www/html/images/firma-verde.png";
        $nc_embeddedimage_nombre = "logo_firma_verde";
        $nc_embeddedimage_tipo = "image/png";
        $nc_adjuntos_nombre = "Consolidado Alertas.xlsx;";
        $nc_adjuntos_ruta = $dir_temporal . $titulo_reporte . ";";
        $nc_intentos = "";
        $nc_eliminar = "Si";
        $nc_estado_envio = "Pendiente";
        $nc_fecha_envio = "";
        $nc_usuario_registro = $_SESSION["usu_id"];

        /* ========= INSERT NOTIFICACIÓN (SQLi -> PREPARED) ========= */

        $sql_ins = "INSERT INTO `tb_gestion_alertas_notificacion`
            (`gan_consecutivo`, `gan_tipo`, `gan_id_set_from`, `gan_address`, `gan_cc`, `gan_bcc`, `gan_subject`, `gan_body`,
             `gan_embeddedimage_ruta`, `gan_embeddedimage_nombre`, `gan_embeddedimage_tipo`,
             `gan_adjunto_nombre`, `gan_adjunto_ruta`, `gan_intentos`, `gan_eliminar`, `gan_estado_envio`, `gan_fecha_envio`, `gan_usuario_registro`)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt_ins = $enlace_db->prepare($sql_ins);

        if ($stmt_ins) {
            $gan_tipo = "informe";
            $stmt_ins->bind_param(
                'ssssssssssssssssss',
                $id_registro,
                $gan_tipo,
                $nc_id_set_from,
                $nc_address,
                $nc_cc,
                $nc_bcc,
                $nc_subject,
                $nc_body,
                $nc_embeddedimage_ruta,
                $nc_embeddedimage_nombre,
                $nc_embeddedimage_tipo,
                $nc_adjuntos_nombre,
                $nc_adjuntos_ruta,
                $nc_intentos,
                $nc_eliminar,
                $nc_estado_envio,
                $nc_fecha_envio,
                $nc_usuario_registro
            );

            $ok = $stmt_ins->execute();
            $stmt_ins->close();

            if ($ok) {
                registro_log($enlace_db, $modulo_plataforma, 'notificacion', $nc_subject . ' [' . $id_registro . ']');
                $estado_envio = 'enviado';
            } else {
                registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', $nc_subject . ' [' . $id_registro . ']');
                $estado_envio = 'no_enviado';
            }
        } else {
            registro_log($enlace_db, $modulo_plataforma, 'notificacion_error', 'Error prepare INSERT notificación');
            $estado_envio = 'no_enviado';
        }

    } else {
        $estado_envio = '<p class="alert alert-warning col-md-12 p-1 font-size-11 mb-1"><span class="fas fa-exclamation-triangle"></span> ¡No se encontraron registros para enviar!</p>';
    }
}

/* =========================
   RESPUESTA JSON
   ========================= */

$datos = [
    'informe_lista' => $lista_informe,
    'informe_estado' => $estado_envio
];

respond($datos);
?>
