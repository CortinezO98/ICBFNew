<?php
declare(strict_types=1);

/**
 * Funciones complementarias del módulo Coaching: soportes/adjuntos y
 * detalle estructurado por tipo (Acta/Felicitación).
 *
 * NOTA: las funciones de encuesta que traía originalmente este archivo
 * se eliminaron — usaban columnas que no existen en la base real
 * (prefijos gce_ / gcer_, no confirmados en la BD). La encuesta de
 * percepción REAL ya vive en
 * lib/coaching_datos.php (listarPreguntasEncuestaActivas,
 * guardarEncuestaPercepcion, obtenerEncuestaPercepcion) e integrada en
 * gestion_coaching_firmar.php — no duplicar aquí. Ver
 * ESTADO_REAL_MODULO_COACHING_v2.md.
 */
function coachingEsc(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// ---------------------------------------------------------------------
// Detalle estructurado por tipo (Acta de Compromiso / Felicitación).
// Tabla NUEVA, sin choque con nada existente — requiere la migración
// 20260724_014_create_detalle_tipo.sql antes de usarse.
// ---------------------------------------------------------------------
function obtenerDetalleTipo(mysqli $db, string $paquete): ?array
{
    $s = $db->prepare("SELECT * FROM `tb_gestion_coaching_detalle_tipo` WHERE `gcdt_paquete` = ? LIMIT 1");
    $s->bind_param('s', $paquete);
    $s->execute();
    return $s->get_result()->fetch_assoc() ?: null;
}

function guardarDetalleTipo(mysqli $db, string $paquete, array $d, string $usuario): void
{
    $sql = "INSERT INTO `tb_gestion_coaching_detalle_tipo`
                (`gcdt_paquete`, `gcdt_fecha_ocurrencia`, `gcdt_descripcion_falta`, `gcdt_impacto`, `gcdt_evidencias`,
                 `gcdt_reincidente`, `gcdt_retroalimentaciones_previas`, `gcdt_tipo_reconocimiento`, `gcdt_periodo_reconocido`,
                 `gcdt_resultado_obtenido`, `gcdt_meta`, `gcdt_monitoreo_destacado`, `gcdt_fecha_monitoreo`,
                 `gcdt_fortalezas_reconocimiento`, `gcdt_descripcion_reconocimiento`, `gcdt_fecha_entrega`,
                 `gcdt_responsable_entrega`, `gcdt_actualizado_por`)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
                `gcdt_fecha_ocurrencia` = VALUES(`gcdt_fecha_ocurrencia`),
                `gcdt_descripcion_falta` = VALUES(`gcdt_descripcion_falta`),
                `gcdt_impacto` = VALUES(`gcdt_impacto`),
                `gcdt_evidencias` = VALUES(`gcdt_evidencias`),
                `gcdt_reincidente` = VALUES(`gcdt_reincidente`),
                `gcdt_retroalimentaciones_previas` = VALUES(`gcdt_retroalimentaciones_previas`),
                `gcdt_tipo_reconocimiento` = VALUES(`gcdt_tipo_reconocimiento`),
                `gcdt_periodo_reconocido` = VALUES(`gcdt_periodo_reconocido`),
                `gcdt_resultado_obtenido` = VALUES(`gcdt_resultado_obtenido`),
                `gcdt_meta` = VALUES(`gcdt_meta`),
                `gcdt_monitoreo_destacado` = VALUES(`gcdt_monitoreo_destacado`),
                `gcdt_fecha_monitoreo` = VALUES(`gcdt_fecha_monitoreo`),
                `gcdt_fortalezas_reconocimiento` = VALUES(`gcdt_fortalezas_reconocimiento`),
                `gcdt_descripcion_reconocimiento` = VALUES(`gcdt_descripcion_reconocimiento`),
                `gcdt_fecha_entrega` = VALUES(`gcdt_fecha_entrega`),
                `gcdt_responsable_entrega` = VALUES(`gcdt_responsable_entrega`),
                `gcdt_actualizado_por` = VALUES(`gcdt_actualizado_por`),
                `gcdt_actualizado_fecha` = NOW()";
    $s = $db->prepare($sql);
    $vals = [
        $paquete, $d['fecha_ocurrencia'] ?? null, $d['descripcion_falta'] ?? null, $d['impacto'] ?? null, $d['evidencias'] ?? null,
        $d['reincidente'] ?? null, $d['retroalimentaciones_previas'] ?? null, $d['tipo_reconocimiento'] ?? null, $d['periodo_reconocido'] ?? null,
        $d['resultado_obtenido'] ?? null, $d['meta'] ?? null, $d['monitoreo_destacado'] ?? null, $d['fecha_monitoreo'] ?? null,
        $d['fortalezas_reconocimiento'] ?? null, $d['descripcion_reconocimiento'] ?? null, $d['fecha_entrega'] ?? null,
        $d['responsable_entrega'] ?? null, $usuario,
    ];
    $s->bind_param(str_repeat('s', count($vals)), ...$vals);
    if (!$s->execute()) {
        throw new RuntimeException('No fue posible guardar el detalle especializado.');
    }
}

// ---------------------------------------------------------------------
// Soportes/adjuntos — CORREGIDO para usar las columnas REALES de
// tb_gestion_coaching_soporte (prefijo gcsp_*, ya desplegada desde la
// migración 004). La lógica de validación (hash SHA-256, MIME real vía
// finfo, nombre interno aleatorio) se mantiene igual — era correcta.
// ---------------------------------------------------------------------
const COACHING_SOPORTES_RUTA = __DIR__ . '/../storage/coaching_soportes';

function listarSoportesCoaching(mysqli $db, string $paquete): array
{
    $s = $db->prepare("SELECT * FROM `tb_gestion_coaching_soporte` WHERE `gcsp_paquete` = ? AND `gcsp_estado` = 'Activo' ORDER BY `gcsp_registro_fecha` DESC");
    $s->bind_param('s', $paquete);
    $s->execute();
    return $s->get_result()->fetch_all(MYSQLI_ASSOC);
}

function guardarSoporteCoaching(mysqli $db, string $paquete, array $archivo, string $tipo, string $usuario): int
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible recibir el archivo.');
    }
    $max = 10 * 1024 * 1024;
    if ((int) $archivo['size'] > $max) {
        throw new RuntimeException('El archivo supera 10 MB.');
    }
    $permitidos = [
        'pdf' => 'application/pdf', 'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
    ];
    $ext = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
    if (!isset($permitidos[$ext])) {
        throw new RuntimeException('Tipo de archivo no permitido.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if ($mime !== $permitidos[$ext]) {
        throw new RuntimeException('El contenido del archivo no coincide con su extensión.');
    }
    if (!is_dir(COACHING_SOPORTES_RUTA) && !mkdir(COACHING_SOPORTES_RUTA, 0750, true) && !is_dir(COACHING_SOPORTES_RUTA)) {
        throw new RuntimeException('No fue posible preparar el almacenamiento.');
    }
    $interno = bin2hex(random_bytes(20)) . '.' . $ext;
    $ruta = COACHING_SOPORTES_RUTA . DIRECTORY_SEPARATOR . $interno;
    if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
        throw new RuntimeException('No fue posible almacenar el soporte.');
    }
    chmod($ruta, 0640);
    $hash = hash_file('sha256', $ruta);
    $nombre = mb_substr(basename((string) $archivo['name']), 0, 255);

    $s = $db->prepare(
        "INSERT INTO `tb_gestion_coaching_soporte`
            (`gcsp_paquete`, `gcsp_nombre_original`, `gcsp_nombre_interno`, `gcsp_ruta`, `gcsp_extension`,
             `gcsp_mime`, `gcsp_tamano_bytes`, `gcsp_hash_sha256`, `gcsp_tipo_documental`, `gcsp_usuario`)
         VALUES (?,?,?,?,?,?,?,?,?,?)"
    );
    $size = (int) $archivo['size'];
    $s->bind_param('ssssssisss', $paquete, $nombre, $interno, $ruta, $ext, $mime, $size, $hash, $tipo, $usuario);
    if (!$s->execute()) {
        @unlink($ruta);
        throw new RuntimeException('No fue posible registrar el soporte.');
    }
    return $db->insert_id;
}

function obtenerSoporteCoaching(mysqli $db, int $id): ?array
{
    $s = $db->prepare("SELECT * FROM `tb_gestion_coaching_soporte` WHERE `gcsp_id` = ? AND `gcsp_estado` = 'Activo' LIMIT 1");
    $s->bind_param('i', $id);
    $s->execute();
    return $s->get_result()->fetch_assoc() ?: null;
}

/** Marca inactivo sin borrar el archivo físico (requisito de auditoría). */
function inactivarSoporteCoaching(mysqli $db, int $id): void
{
    $s = $db->prepare("UPDATE `tb_gestion_coaching_soporte` SET `gcsp_estado` = 'Inactivo' WHERE `gcsp_id` = ?");
    $s->bind_param('i', $id);
    if (!$s->execute()) {
        throw new RuntimeException('No fue posible inactivar el soporte.');
    }
}

function descargarSoporteCoaching(array $s): void
{
    if (!is_readable($s['gcsp_ruta']) || hash_file('sha256', $s['gcsp_ruta']) !== $s['gcsp_hash_sha256']) {
        http_response_code(409);
        exit('Soporte no disponible o con integridad comprometida.');
    }
    header('Content-Type: ' . $s['gcsp_mime']);
    header('Content-Disposition: attachment; filename="' . rawurlencode($s['gcsp_nombre_original']) . '"');
    header('X-Content-Type-Options: nosniff');
    header('Content-Length: ' . filesize($s['gcsp_ruta']));
    readfile($s['gcsp_ruta']);
    exit;
}
