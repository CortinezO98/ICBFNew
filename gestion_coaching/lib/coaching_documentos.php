<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_datos.php';

// Autoload de Composer del proyecto (mismo vendor/ real de la raíz del
// sitio, no una copia separada). Sin esto, class_exists('\Mpdf\Mpdf')
// siempre da falso aunque mPDF esté instalado, porque PHP nunca llega a
// cargar la clase — este era el bug real detrás del aviso persistente de
// "librería no instalada".
$coaching_autoload_composer = __DIR__ . '/../../vendor/autoload.php';
if (is_readable($coaching_autoload_composer)) {
    require_once $coaching_autoload_composer;
}

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
 * Dispatcher: elige la plantilla real según el tipo de paquete. Cada tipo
 * tiene su propio diseño, basado en el contenido EXACTO de los 3 Word
 * reales que compartió el negocio (Retroalimentación / Acta de
 * Compromiso / Felicitación-Reconocimiento). Los tipos nuevos del enfoque
 * evolutivo (Llamado Verbal, Escalamiento, No renovación) todavía no
 * tienen un formato Word propio — usan la plantilla de Acta de Compromiso
 * como base, con el título y las secciones ajustadas.
 */
function construirHtmlDocumentoPorTipo(
    string $gcp_id,
    array $paquete,
    ?array $retro,
    array $compromisos,
    ?array $respuesta,
    array $indicadores_adicionales = [],
    ?array $escalamiento = null
): string {
    switch ($paquete['gct_codigo']) {
        case 'RETROALIMENTACION':
            return construirHtmlRetroalimentacion($gcp_id, $paquete, $retro, $compromisos, $respuesta, $indicadores_adicionales);
        case 'FELICITACION':
        case 'RECONOCIMIENTO':
            return construirHtmlFelicitacion($gcp_id, $paquete);
        case 'ACTA_COMPROMISO':
        case 'LLAMADO_VERBAL':
        case 'ESCALAMIENTO_DISCIPLINARIO':
        case 'NO_RENOVACION':
        default:
            return construirHtmlActaCompromiso($gcp_id, $paquete, $retro, $compromisos, $respuesta, $indicadores_adicionales, $escalamiento);
    }
}

/** Mantiene compatibilidad con el nombre anterior (usado en pantallas ya entregadas). */
function construirHtmlDocumentoRetroalimentacion(string $gcp_id, array $paquete, ?array $retro, array $compromisos, ?array $respuesta): string
{
    return construirHtmlDocumentoPorTipo($gcp_id, $paquete, $retro, $compromisos, $respuesta);
}

/**
 * Encabezado y bloque de firma compartidos entre las 3 plantillas — nota
 * legal EXACTA copiada de los 3 Word (es idéntica en Retroalimentación y
 * Acta de Compromiso).
 */
function coachingEncabezadoDocumento(string $titulo): string
{
    return '
    <div style="text-align:center; margin-bottom:10px;">
        <h2 style="color:#4CAF50; margin-bottom:2px;">' . htmlspecialchars($titulo) . '</h2>
        <p style="font-size:11px; color:#6E6E6E; margin-top:0;">Línea ICBF</p>
    </div>';
}

function coachingBloqueFirmaLegal(string $paquete_id): string
{
    return '
    <div style="margin-top:30px; page-break-inside:avoid;">
        <p>De conformidad.</p>
        <p style="margin-top:40px;">Firma: ____________________________________</p>
        <p>Nombre del colaborador: ____________________________________</p>
        <p>Fecha y hora cierre de paquete: ____________________________________</p>
        <p style="font-size:9px; color:#6E6E6E; margin-top:15px; text-align:justify;">
            <strong>Nota:</strong> La aprobación de cierre del paquete coaching, certifica que fue usted fue
            notificado, orientado y retroalimentado adecuadamente para dar cumplimiento a sus objetivos y demás
            actividades contractuales, por lo cual, establece un compromiso a dar cumplimiento a cada una de las
            metas definidas para cada uno de los indicadores, así como también a todas aquellas actividades,
            instrucciones de índole laboral definidas por el supervisor, propias de su rol como agente (general,
            técnico, profesional o especializado, según corresponda). Código de paquete: ' . htmlspecialchars($paquete_id) . '.
        </p>
    </div>';
}

function coachingListaIndicadores(array $paquete, array $indicadores_adicionales): string
{
    $nombres = [];
    if (!empty($paquete['indicador_nombre'] ?? null)) { $nombres[] = $paquete['indicador_nombre']; }
    foreach ($indicadores_adicionales as $i) { $nombres[] = $i['gci_nombre']; }
    if (count($nombres) === 0) { return 'El registrado en la aplicación en el paquete coaching'; }
    return htmlspecialchars(implode(', ', array_unique($nombres)));
}

/**
 * FORMATO RETROALIMENTACIÓN — replica exacta de
 * "Cedula_Agente_-_Formato_Retroalimentacion_Indicador.docx":
 * datos del colaborador, oportunidad de mejora (jefe), compromisos
 * pactados (colaborador), encuesta 1-5 (placeholder hasta que se
 * construya la pantalla de encuesta), firma.
 */
function construirHtmlRetroalimentacion(string $gcp_id, array $paquete, ?array $retro, array $compromisos, ?array $respuesta, array $indicadores_adicionales = []): string
{
    $html = coachingEncabezadoDocumento('FORMATO RETROALIMENTACIÓN — ' . $gcp_id);

    $html .= '
    <h4 style="background:#4CAF50; color:#FFFFFF; padding:5px 8px;">DATOS PERSONALES DEL COLABORADOR</h4>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:15px;">
        <tr><td style="width:33%;"><strong>Nombre completo</strong><br>' . htmlspecialchars($paquete['agente_nombre'] ?? '') . '</td>
            <td style="width:33%;"><strong>Cédula</strong><br>' . htmlspecialchars($paquete['gcp_agente_id']) . '</td>
            <td><strong>Fecha y hora de elaboración</strong><br>' . date('d/m/Y H:i') . '</td></tr>
    </table>

    <h4 style="background:#4CAF50; color:#FFFFFF; padding:5px 8px;">OPORTUNIDAD DE MEJORA (Documenta jefe inmediato)</h4>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:15px;">
        <tr><td style="width:25%;"><strong>Indicador</strong></td><td>' . coachingListaIndicadores($paquete, $indicadores_adicionales) . '</td></tr>
        <tr><td><strong>Causa raíz</strong><br><span style="font-size:9px; color:#6E6E6E;">(5 porqués / espina de pescado / análisis causal)</span></td>
            <td>' . nl2br(htmlspecialchars($retro['gcr_causa_raiz'] ?? '')) . '</td></tr>
        <tr><td><strong>Estrategia correctiva y/o de mejora</strong></td>
            <td>' . nl2br(htmlspecialchars($retro['gcr_estrategia_correctiva'] ?? '')) . '</td></tr>
    </table>

    <h4 style="background:#4CAF50; color:#FFFFFF; padding:5px 8px;">COMPROMISOS PACTADOS (Documenta colaborador)</h4>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:15px;">
        <tr style="background:#F2F2F2;"><th>Descripción</th><th>Qué</th><th>Cómo</th><th>Fecha límite</th></tr>';
    foreach ($compromisos as $c) {
        $html .= '<tr>'
            . '<td>' . nl2br(htmlspecialchars($c['gccm_descripcion'])) . '</td>'
            . '<td>' . htmlspecialchars($c['gccm_que'] ?? '') . '</td>'
            . '<td>' . htmlspecialchars($c['gccm_como'] ?? '') . '</td>'
            . '<td>' . ($c['gccm_fecha_limite'] ? date('d/m/Y', strtotime($c['gccm_fecha_limite'])) : '-') . '</td>'
            . '</tr>';
    }
    $html .= '</table>

    <p><strong>Respuesta del agente:</strong> ' . nl2br(htmlspecialchars($respuesta['gcra_compromiso_general'] ?? '(pendiente)')) . '</p>
    <p><strong>Acciones para no reincidencia:</strong> ' . nl2br(htmlspecialchars($respuesta['gcra_acciones_no_reincidencia'] ?? '')) . '</p>

    <h4 style="background:#4CAF50; color:#FFFFFF; padding:5px 8px;">ENCUESTA DEL ESPACIO (Documenta colaborador)</h4>
    <p style="font-size:10px; color:#6E6E6E;">El agente responde esta encuesta (escala 1 a 5) al momento de firmar el documento — sus respuestas quedan registradas y visibles en el detalle del paquete en la plataforma, no en esta versión impresa generada antes de la firma.</p>';

    $html .= coachingBloqueFirmaLegal($gcp_id);
    return $html;
}

/**
 * ACTA DE COMPROMISO — replica de "Formato_Compromiso_Indicador.docx".
 * También sirve de base para Llamado Verbal, Escalamiento Disciplinario
 * y No renovación (que aún no tienen Word propio), ajustando el título.
 */
function construirHtmlActaCompromiso(string $gcp_id, array $paquete, ?array $retro, array $compromisos, ?array $respuesta, array $indicadores_adicionales = [], ?array $escalamiento = null): string
{
    $titulo = $paquete['gct_nombre'] ?? 'ACTA DE COMPROMISO';
    $html = coachingEncabezadoDocumento(strtoupper($titulo) . ' — ' . $gcp_id);

    $html .= '
    <p><strong>Nombre del colaborador:</strong> ' . htmlspecialchars($paquete['agente_nombre'] ?? '') . '</p>
    <p><strong>Documento de identidad:</strong> ' . htmlspecialchars($paquete['gcp_agente_id']) . '</p>
    <p><strong>Fecha de elaboración:</strong> ' . date('d/m/Y') . '</p>
    <p><strong>Objetivo del documento:</strong> Asegurar la implementación de las acciones de mejora derivadas del
    acompañamiento realizado al funcionario, con el fin de garantizar el cumplimiento de las obligaciones
    contractuales y funciones establecidas para su cargo.</p>
    <p><strong>Indicador(es) sujeto de compromiso:</strong> ' . coachingListaIndicadores($paquete, $indicadores_adicionales) . '</p>
    <p>Para efectos de lo anterior, el trabajador se compromete a adelantar las gestiones pertinentes para mejorar
    y cumplir con las funciones pertinentes a su cargo. La empresa realizará el seguimiento necesario para
    verificar el cumplimiento de los compromisos pactados en la presente acta.</p>';

    if ($escalamiento) {
        $html .= '
        <h4 style="background:#175E83; color:#FFFFFF; padding:5px 8px;">DETALLE DEL ESCALAMIENTO</h4>
        <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:15px;">
            <tr><td style="width:30%;"><strong>Asunto</strong></td><td>' . htmlspecialchars($escalamiento['gcesc_asunto']) . '</td></tr>
            <tr><td><strong>Fecha y hora de envío</strong></td><td>' . date('d/m/Y H:i', strtotime($escalamiento['gcesc_fecha_hora_envio'])) . '</td></tr>
            <tr><td><strong>Remitido a</strong></td><td>' . htmlspecialchars($escalamiento['gcesc_destinatario_nombre']) . ' (' . htmlspecialchars($escalamiento['gcesc_destinatario_correo']) . ')</td></tr>
            <tr><td><strong>Observaciones</strong></td><td>' . nl2br(htmlspecialchars($escalamiento['gcesc_observaciones'] ?? '')) . '</td></tr>
        </table>';
    }

    $html .= '
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:10px;">
        <tr style="background:#F2F2F2;"><th style="width:50%;">DESCRIPCIÓN DEL COMPROMISO</th><th>COMPROMISO DEL COLABORADOR</th></tr>';
    if (count($compromisos) === 0) {
        $html .= '<tr><td colspan="2" style="text-align:center; color:#6E6E6E;">Sin compromisos registrados.</td></tr>';
    }
    foreach ($compromisos as $c) {
        $html .= '<tr>'
            . '<td>' . nl2br(htmlspecialchars($c['gccm_descripcion'])) . '</td>'
            . '<td>' . nl2br(htmlspecialchars($respuesta['gcra_compromiso_general'] ?? '(pendiente de respuesta del agente)')) . '</td>'
            . '</tr>';
    }
    $html .= '</table>';

    $html .= coachingBloqueFirmaLegal($gcp_id);
    return $html;
}

/**
 * FELICITACIÓN / RECONOCIMIENTO — replica de
 * "Formato_Felicitacion_Indicador.docx" (formato memorando).
 *
 * Nota: la tabla estructurada de "Descripción del reconocimiento"
 * (periodo, resultado, monitoreo destacado) todavía no tiene pantalla
 * propia — por ahora usa el contexto/motivo libre capturado al crear el
 * paquete (gcp_contexto). Cuando se construyan los campos estructurados
 * de Felicitación/Reconocimiento (pendiente del alcance original), esta
 * función se actualiza sin tocar el resto del generador.
 */
function construirHtmlFelicitacion(string $gcp_id, array $paquete): string
{
    $es_reconocimiento = $paquete['gct_codigo'] === 'RECONOCIMIENTO';
    $referencia = $es_reconocimiento ? 'MEMORANDO DE RECONOCIMIENTO' : 'MEMORANDO DE FELICITACIÓN';

    $html = '
    <p style="text-align:right;">Bogotá, ' . date('d \d\e F \d\e Y') . '</p>
    <p>Señor(a):<br><strong>' . htmlspecialchars($paquete['agente_nombre'] ?? '') . '</strong><br>Línea ICBF</p>
    <p style="text-align:center; font-weight:bold; margin:20px 0;">REFERENCIA: ' . $referencia . '</p>
    <p style="text-align:justify;">Por medio de la presente, la operación del centro de contactos del ICBF se
    permite reconocer en usted su compromiso, regularidad y consistencia en factores de desempeño que permiten
    destacar los siguientes aspectos:</p>

    <h4 style="background:#4CAF50; color:#FFFFFF; padding:5px 8px;">DESCRIPCIÓN DEL ' . ($es_reconocimiento ? 'RECONOCIMIENTO' : 'RECONOCIMIENTO') . '</h4>
    <div style="border:1px solid #F2F2F2; padding:10px; min-height:60px;">' . nl2br(htmlspecialchars($paquete['gcp_contexto'] ?? '')) . '</div>

    <p style="text-align:justify; margin-top:15px;">Es importante generar estos gestos de agradecimiento y
    reconocimiento, pues estamos seguros de que su compromiso, cumplimiento y disciplina se verán reflejados en
    las labores encomendadas para su rol.</p>

    <p>Un gran y enérgico saludo. Cordialmente,</p>
    <p style="margin-top:40px;">____________________________________<br>
    ' . htmlspecialchars($paquete['supervisor_nombre'] ?? '') . '<br>Jefe Directo</p>

    <p style="margin-top:20px;">Nombre completo colaborador: ' . htmlspecialchars($paquete['agente_nombre'] ?? '') . '<br>
    Fecha entrega: ' . date('d/m/Y') . '</p>';

    return $html;
}

