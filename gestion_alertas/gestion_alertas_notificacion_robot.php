<?php
/**
 * Robot de envío de notificaciones - Gestión Alertas
 * Remediaciones aplicadas (sin cambiar la lógica funcional):
 * - SQLi: consultas UPDATE preparadas (y SELECT con prepare).
 * - Manejo de excepciones PHPMailer (evita caída del robot y fuga de errores).
 * - Evita fuga de info sensible: no mostrar errores en pantalla, solo log.
 * - Validación/saneamiento de correos (evita header injection / datos inválidos).
 * - Validación de rutas para adjuntos/embebidos (mitiga traversal/exfiltración).
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_error_gestion_alertas_notificacion_robot.log');

require_once("/var/www/html/config/conexion_db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '/var/www/html/PHPMailer-master/src/Exception.php';
require '/var/www/html/PHPMailer-master/src/PHPMailer.php';
require '/var/www/html/PHPMailer-master/src/SMTP.php';

/* =========================
   Helpers de seguridad
   ========================= */

function normalize_bool($v): bool {
    if (is_bool($v)) return $v;
    $s = strtolower(trim((string)$v));
    return in_array($s, ['1','true','t','si','sí','yes','y','on'], true);
}

function sanitize_header_value(string $s): string {
    // Evita inyección de cabeceras (CRLF)
    return str_replace(["\r", "\n"], '', $s);
}

function clean_display_name(string $s): string {
    $s = trim($s);
    $s = str_replace(["\r", "\n", "\0"], '', $s);
    // Eliminar caracteres de control
    $s = preg_replace('/[\x01-\x1F\x7F]/u', '', $s);
    return $s;
}

function is_valid_email(string $email): bool {
    $email = trim($email);
    if ($email === '') return false;
    if (strlen($email) > 254) return false;
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Valida que un archivo exista y esté dentro de una de las rutas permitidas.
 */
function safe_realpath_in_allowed(string $path, array $allowedBases): ?string {
    $path = str_replace("\0", '', $path);
    $real = realpath($path);
    if ($real === false) return null;

    foreach ($allowedBases as $base) {
        $baseReal = realpath($base);
        if ($baseReal !== false) {
            $baseReal = rtrim($baseReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (strpos($real, $baseReal) === 0) {
                return $real;
            }
        }
    }
    return null;
}

/**
 * Parse de lista "correo|nombre;correo|nombre;..."
 */
function parse_recipients(string $raw): array {
    $out = [];
    $parts = explode(';', (string)$raw);
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $sub = explode('|', $p, 2);
        $email = isset($sub[0]) ? trim($sub[0]) : '';
        $name  = isset($sub[1]) ? clean_display_name($sub[1]) : '';
        if (is_valid_email($email)) {
            $out[] = [$email, $name !== '' ? $name : $email];
        }
    }
    return $out;
}

/**
 * Parse de listas separadas por ";" en paralelo (rutas/nombres/tipos)
 */
function split_semicolon(string $raw): array {
    $arr = explode(';', (string)$raw);
    // Mantener índices, pero quitamos espacios
    return array_map('trim', $arr);
}

/* =========================
   SELECT notificaciones (prepared)
   ========================= */

$sql = "SELECT
            gan_id, gan_consecutivo, gan_tipo, gan_id_set_from, gan_address, gan_cc, gan_bcc, gan_subject, gan_body,
            gan_embeddedimage_ruta, gan_embeddedimage_nombre, gan_embeddedimage_tipo,
            gan_intentos, gan_eliminar, gan_estado_envio, gan_fecha_envio, gan_fecha_registro, gan_usuario_registro,
            TN.ncr_host, TN.ncr_port, TN.ncr_smtpsecure, TN.ncr_smtpauth, TN.ncr_username, TN.ncr_password,
            TN.ncr_setfrom, TN.ncr_setfrom_name,
            gan_adjunto_nombre, gan_adjunto_ruta
        FROM tb_gestion_alertas_notificacion
        LEFT JOIN tb_notificaciones_central_remitente AS TN
            ON tb_gestion_alertas_notificacion.gan_id_set_from = TN.ncr_id
        WHERE gan_estado_envio='Pendiente'
        LIMIT 5 OFFSET 0";

$stmt = $enlace_db->prepare($sql);
if (!$stmt) {
    error_log("Error prepare SELECT notificaciones: " . $enlace_db->error);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();
$resultado_notificaciones = $res ? $res->fetch_all(MYSQLI_NUM) : [];
$stmt->close();

if (count($resultado_notificaciones) <= 0) {
    exit;
}

/* =========================
   UPDATE estado (prepared)
   ========================= */
$sql_update = "UPDATE tb_gestion_alertas_notificacion
               SET gan_estado_envio=?, gan_fecha_envio=?, gan_intentos=?
               WHERE gan_id=?";

$stmt_update = $enlace_db->prepare($sql_update);
if (!$stmt_update) {
    error_log("Error prepare UPDATE notificaciones: " . $enlace_db->error);
    exit;
}

/* =========================
   Rutas permitidas (adjuntos/embebidos)
   ========================= */
$ALLOWED_BASES = [
    '/var/www/html/images/',
    '/var/www/html/gestion_alertas/temporal/',
    '/var/www/html/gestion_alertas/',
];

/* =========================
   Proceso envío
   ========================= */
for ($i = 0; $i < count($resultado_notificaciones); $i++) {

    $marca_temporal = date("Y-m-d H:i:s");
    $id_correo = (string)$resultado_notificaciones[$i][0];

    // Campos mínimos: set_from id, subject, body
    $set_from_id = trim((string)$resultado_notificaciones[$i][3]);
    $subject_raw = (string)$resultado_notificaciones[$i][7];
    $body_raw    = (string)$resultado_notificaciones[$i][8];

    // Intentos
    $num_intentos = (int)$resultado_notificaciones[$i][12] + 1;
    $estado_error = ($num_intentos >= 2) ? "Error" : "Pendiente";

    if ($set_from_id === '' || $subject_raw === '' || $body_raw === '') {
        // Estructura incompleta
        $estado = "Error-estructura";
        $stmt_update->bind_param('ssis', $estado, $marca_temporal, $num_intentos, $id_correo);
        $stmt_update->execute();
        continue;
    }

    // Config SMTP desde tabla remitente
    $host       = trim((string)$resultado_notificaciones[$i][18]);
    $port       = (int)$resultado_notificaciones[$i][19];
    $secureRaw  = strtolower(trim((string)$resultado_notificaciones[$i][20]));
    $smtpAuth   = normalize_bool($resultado_notificaciones[$i][21]);
    $username   = (string)$resultado_notificaciones[$i][22];
    $password   = (string)$resultado_notificaciones[$i][23];
    $setfrom    = (string)$resultado_notificaciones[$i][24];
    $setfromName= clean_display_name((string)$resultado_notificaciones[$i][25]);

    // Normaliza SMTPSecure
    $smtpSecure = '';
    if (in_array($secureRaw, ['tls','ssl','starttls'], true)) {
        $smtpSecure = ($secureRaw === 'starttls') ? PHPMailer::ENCRYPTION_STARTTLS : $secureRaw;
    } elseif ($secureRaw === 'none' || $secureRaw === '') {
        $smtpSecure = '';
    } else {
        // valor no esperado -> no forzar, mantener vacío
        $smtpSecure = '';
    }

    // Subject seguro (sin CRLF)
    $subject = sanitize_header_value($subject_raw);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port > 0 ? $port : 25;

        // Si viene vacío, PHPMailer usa sin encriptación (igual al comportamiento previo cuando venía vacío)
        if ($smtpSecure !== '') {
            $mail->SMTPSecure = $smtpSecure;
        }

        $mail->SMTPAuth = $smtpAuth;
        $mail->Username = $username;
        $mail->Password = $password;

        // Validación de SetFrom (si está mal, no enviar)
        if (!is_valid_email($setfrom)) {
            throw new Exception("SetFrom inválido");
        }
        $mail->setFrom($setfrom, $setfromName !== '' ? $setfromName : $setfrom);

        // Recipients
        $to_list  = parse_recipients((string)$resultado_notificaciones[$i][4]);
        $cc_list  = parse_recipients((string)$resultado_notificaciones[$i][5]);
        $bcc_list = parse_recipients((string)$resultado_notificaciones[$i][6]);

        // Si no hay destinatarios válidos, marcar error-estructura
        if (count($to_list) === 0 && count($cc_list) === 0 && count($bcc_list) === 0) {
            throw new Exception("Sin destinatarios válidos");
        }

        foreach ($to_list as [$email, $name]) {
            $mail->addAddress($email, $name);
        }
        foreach ($cc_list as [$email, $name]) {
            $mail->addCC($email, $name);
        }
        foreach ($bcc_list as [$email, $name]) {
            $mail->addBCC($email, $name);
        }

        // Embedded images (rutas y nombres separados por ;)
        $image_rutas  = split_semicolon((string)$resultado_notificaciones[$i][9]);
        $image_cids   = split_semicolon((string)$resultado_notificaciones[$i][10]);
        $image_tipos  = split_semicolon((string)$resultado_notificaciones[$i][11]);

        $maxImages = min(count($image_rutas), count($image_cids), count($image_tipos));
        for ($j = 0; $j < $maxImages; $j++) {
            $ruta = trim($image_rutas[$j]);
            $cid  = trim($image_cids[$j]);
            $tipo = trim($image_tipos[$j]);

            if ($ruta === '' || $cid === '' || $tipo === '') continue;

            $safePath = safe_realpath_in_allowed($ruta, $ALLOWED_BASES);
            if ($safePath && is_file($safePath)) {
                $mail->addEmbeddedImage($safePath, $cid, basename($safePath), 'base64', $tipo);
            }
        }

        // Attachments (rutas y nombres separados por ;)
        $adj_rutas  = split_semicolon((string)$resultado_notificaciones[$i][27]);
        $adj_nombres= split_semicolon((string)$resultado_notificaciones[$i][26]);

        $maxAdj = min(count($adj_rutas), count($adj_nombres));
        for ($j = 0; $j < $maxAdj; $j++) {
            $ruta = trim($adj_rutas[$j]);
            $nombre = trim($adj_nombres[$j]);
            if ($ruta === '' || $nombre === '') continue;

            $safePath = safe_realpath_in_allowed($ruta, $ALLOWED_BASES);
            if ($safePath && is_file($safePath)) {
                $mail->addAttachment($safePath, $nombre);
            }
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $body_raw;

        // Envío
        $mail->send();

        // OK
        $estado = "Enviado";
        $stmt_update->bind_param('ssis', $estado, $marca_temporal, $num_intentos, $id_correo);
        $stmt_update->execute();

    } catch (Exception $e) {
        // Error controlado
        // Mantener la lógica de reintentos: Error si >=2, si no Pendiente.
        $estado = ($e->getMessage() === 'Sin destinatarios válidos' || $e->getMessage() === 'SetFrom inválido')
            ? 'Error-estructura'
            : $estado_error;

        $stmt_update->bind_param('ssis', $estado, $marca_temporal, $num_intentos, $id_correo);
        $stmt_update->execute();

        // Log interno (sin imprimir credenciales ni contenido sensible)
        error_log("Envio fallido gan_id={$id_correo} estado={$estado}: " . $e->getMessage());
    }
}

$stmt_update->close();
?>
