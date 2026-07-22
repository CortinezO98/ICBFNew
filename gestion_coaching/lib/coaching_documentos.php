<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_datos.php';

/**
 * gestion_coaching/lib/coaching_documentos.php
 *
 * Generación de PDF versionado (Retroalimentación / Acta de Compromiso /
 * Felicitación / Reconocimiento).
 *
 * DEPENDENCIA: mPDF, instalada vía el composer.json PRINCIPAL del proyecto
 * (mismo vendor/ que ya usan phpdotenv, anti-csrf, laminas-escaper,
 * monolog) — NO se crea una carpeta de librería suelta al estilo
 * PHPOffice/PHPCalendario. Ver COMPOSER_PDF.md para el comando exacto de
 * instalación. Agregar una dependencia nueva a composer.json es aditivo:
 * no quita ni modifica ninguna de las 4 ya declaradas, así que no afecta
 * la funcionalidad original del sitio.
 *
 * El HTML fuente de cada tipo de documento se construye en plantillas
 * separadas (gestion_coaching/plantillas_pdf/*.php) para no mezclar
 * lógica de generación con el layout visual que replican los 3 Word
 * (Retroalimentación/Compromiso/Felicitación) que ya nos compartiste.
 */

const COACHING_DOCUMENTOS_RUTA_BASE = __DIR__ . '/../storage/coaching_documentos';

/**
 * Genera una NUEVA versión del PDF para un paquete + tipo de documento,
 * la guarda fuera del directorio público, calcula su hash y la registra
 * en tb_gestion_coaching_documento (nunca sobrescribe una versión previa).
 *
 * @param string $html_contenido HTML ya renderizado por la plantilla correspondiente
 * @return int gcd_id de la versión recién creada
 */
function generarDocumentoCoaching(
    mysqli $enlace_db,
    string $gcp_id,
    string $tipo_documento,
    string $html_contenido,
    string $usu_id_generador
): int {
    if (!class_exists('\Mpdf\Mpdf')) {
        throw new RuntimeException(
            'La librería mPDF no está instalada. Ejecuta "composer require mpdf/mpdf" ' .
            'en la raíz del proyecto (ver COMPOSER_PDF.md) antes de generar documentos.'
        );
    }

    if (!is_dir(COACHING_DOCUMENTOS_RUTA_BASE)) {
        if (!mkdir(COACHING_DOCUMENTOS_RUTA_BASE, 0750, true) && !is_dir(COACHING_DOCUMENTOS_RUTA_BASE)) {
            throw new RuntimeException('No fue posible crear el directorio de almacenamiento de documentos de coaching.');
        }
    }

    $mpdf = new \Mpdf\Mpdf([
        'mode'          => 'utf-8',
        'format'        => 'LETTER',
        'tempDir'       => sys_get_temp_dir(),
        'margin_top'    => 20,
        'margin_bottom' => 20,
    ]);
    $mpdf->SetTitle("Coaching {$tipo_documento} - {$gcp_id}");
    $mpdf->WriteHTML($html_contenido);

    // Nombre interno aleatorio, nunca el original ni algo predecible
    // (mismo criterio de seguridad ya usado en la carga de soportes de Calidad).
    $nombreInterno = 'COACH_' . $gcp_id . '_' . $tipo_documento . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.pdf';
    $rutaCompleta  = rtrim(COACHING_DOCUMENTOS_RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . $nombreInterno;

    $mpdf->Output($rutaCompleta, \Mpdf\Output\Destination::FILE);

    $hash = hash_file('sha256', $rutaCompleta);
    if ($hash === false) {
        throw new RuntimeException('No fue posible calcular el hash del documento generado.');
    }

    return insertarDocumento($enlace_db, $gcp_id, $tipo_documento, $rutaCompleta, $hash, $usu_id_generador);
}

/**
 * Sirve un documento ya generado, SOLO a través de este controlador (nunca
 * como enlace directo a disco). El archivo llamador (p.ej.
 * gestion_coaching_documento_descargar.php) debe validar autorización con
 * coaching_seguridad.php ANTES de invocar esta función.
 */
function descargarDocumentoCoaching(array $documento): void
{
    if (!is_readable($documento['gcd_ruta'])) {
        http_response_code(404);
        die('Documento no encontrado.');
    }

    $hashActual = hash_file('sha256', $documento['gcd_ruta']);
    if ($hashActual !== $documento['gcd_hash_sha256']) {
        // Integridad comprometida: el archivo en disco no coincide con el
        // hash registrado en BD. No se sirve, se debe auditar aparte.
        http_response_code(409);
        die('El documento no superó la validación de integridad.');
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($documento['gcd_ruta']) . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($documento['gcd_ruta']);
}

/**
 * Construye el HTML fuente del documento de Retroalimentación/Acta de
 * Compromiso a partir de los datos ya diligenciados. Compartida entre la
 * generación automática (gestion_coaching_responder_agente.php) y la
 * regeneración manual (gestion_coaching_documento_generar.php) para no
 * duplicar el layout en dos archivos. Layout simple y funcional; el diseño
 * definitivo (basado en los 3 Word compartidos) se ajusta aparte, sin tocar
 * la lógica de generación/versionamiento.
 */
function construirHtmlDocumentoRetroalimentacion(string $gcp_id, array $paquete, ?array $retro, array $compromisos, ?array $respuesta): string
{
    $filas_compromisos = '';
    foreach ($compromisos as $c) {
        $filas_compromisos .= '<tr>'
            . '<td>' . htmlspecialchars($c['gccm_descripcion']) . '</td>'
            . '<td>' . htmlspecialchars($c['gccm_que'] ?? '') . '</td>'
            . '<td>' . htmlspecialchars($c['gccm_como'] ?? '') . '</td>'
            . '<td>' . ($c['gccm_fecha_limite'] ? date('d/m/Y', strtotime($c['gccm_fecha_limite'])) : '-') . '</td>'
            . '</tr>';
    }

    $compromiso_general = $respuesta['gcra_compromiso_general'] ?? '(pendiente de respuesta del agente)';
    $acciones_no_reincidencia = $respuesta['gcra_acciones_no_reincidencia'] ?? '';

    return '
    <h2 style="color:#4CAF50;">' . htmlspecialchars($paquete['gct_nombre']) . ' — ' . htmlspecialchars($gcp_id) . '</h2>
    <p><strong>Agente:</strong> ' . htmlspecialchars($paquete['agente_nombre'] ?? '') . '<br>
    <strong>Supervisor:</strong> ' . htmlspecialchars($paquete['supervisor_nombre'] ?? '') . '<br>
    <strong>Fecha:</strong> ' . date('d/m/Y') . '</p>

    <h3>Retroalimentación</h3>
    <p><strong>Causa raíz:</strong> ' . nl2br(htmlspecialchars($retro['gcr_causa_raiz'] ?? '')) . '</p>
    <p><strong>Estrategia correctiva:</strong> ' . nl2br(htmlspecialchars($retro['gcr_estrategia_correctiva'] ?? '')) . '</p>

    <h3>Compromisos</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <tr style="background:#4CAF50; color:#fff;"><th>Descripción</th><th>Qué</th><th>Cómo</th><th>Fecha límite</th></tr>
        ' . $filas_compromisos . '
    </table>

    <h3>Respuesta del agente</h3>
    <p><strong>Compromiso general:</strong> ' . nl2br(htmlspecialchars($compromiso_general)) . '</p>
    <p><strong>Acciones para no reincidencia:</strong> ' . nl2br(htmlspecialchars($acciones_no_reincidencia)) . '</p>
    ';
}
