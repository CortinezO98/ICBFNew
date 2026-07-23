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


/** Renderiza la plantilla oficial según el tipo del paquete. */
function construirHtmlDocumentoCoaching(string $gcp_id,array $paquete,?array $retro,array $compromisos,?array $respuesta,?array $encuesta=null,?array $detalle=null): string {
    $codigo=$paquete['gct_codigo']??'RETROALIMENTACION';
    $map=['RETROALIMENTACION'=>'retroalimentacion.php','ACTA_COMPROMISO'=>'acta_compromiso.php','FELICITACION'=>'felicitacion.php','RECONOCIMIENTO'=>'reconocimiento.php'];
    $archivo=__DIR__.'/../plantillas_pdf/'.($map[$codigo]??$map['RETROALIMENTACION']);
    if(!is_readable($archivo)) throw new RuntimeException('No existe la plantilla PDF requerida: '.$codigo);
    ob_start(); require $archivo; return (string)ob_get_clean();
}
/** Alias compatible con llamadas existentes. */
function construirHtmlDocumentoRetroalimentacion(string $gcp_id,array $paquete,?array $retro,array $compromisos,?array $respuesta): string {
    require_once __DIR__.'/coaching_complementos.php';
    global $enlace_db;
    $encuesta=isset($enlace_db)&&$enlace_db instanceof mysqli?obtenerEncuestaPaquete($enlace_db,$gcp_id):null;
    $detalle=isset($enlace_db)&&$enlace_db instanceof mysqli?obtenerDetalleTipo($enlace_db,$gcp_id):null;
    return construirHtmlDocumentoCoaching($gcp_id,$paquete,$retro,$compromisos,$respuesta,$encuesta,$detalle);
}
