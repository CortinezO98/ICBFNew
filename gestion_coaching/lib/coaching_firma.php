<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_datos.php';
require_once __DIR__ . '/coaching_reglas.php';
require_once __DIR__ . '/coaching_transiciones.php';

/**
 * gestion_coaching/lib/coaching_firma.php
 *
 * Aceptación electrónica del agente (no firma digital criptográfica —
 * decisión documentada en el Adendo del Entregable 4: el sistema no tiene
 * PKI y sería sobre-ingeniería para este caso).
 *
 * Texto de consentimiento versionado: si el texto legal cambia en el
 * futuro, las firmas anteriores siguen mostrando exactamente lo que el
 * usuario aceptó en su momento (no se reescribe retroactivamente).
 */
const COACHING_TEXTO_CONSENTIMIENTO_V1 =
    'He leído y entendido el contenido de este documento de coaching. ' .
    'Al hacer clic en "Firmar" confirmo mi aceptación electrónica del mismo, ' .
    'con el mismo valor y efecto que mi firma manuscrita para efectos de este proceso interno.';

/**
 * Firma el documento vigente de un paquete. Lanza excepción con mensaje
 * funcional (seguro de mostrar al usuario) si alguna validación falla.
 */
function firmarDocumentoCoaching(
    mysqli $enlace_db,
    string $gcp_id,
    int $gcd_id,
    string $usu_id,
    string $rol,
    string $ip,
    ?string $user_agent
): int {
    // Bloque 1: validar + registrar la firma, en su propia transacción.
    // No se anida con ejecutarTransicion() a propósito (mysqli no soporta
    // transacciones anidadas reales: un begin_transaction() dentro de otra
    // ya abierta hace COMMIT implícito de la anterior). Se prefiere una
    // secuencia de 2 operaciones atómicas independientes: si la segunda
    // (el cambio de estado) fallara, la firma queda registrada y el
    // paquete simplemente puede reintentarse desde "reenviar transición",
    // sin perder la evidencia de que el agente ya aceptó el documento.
    $enlace_db->begin_transaction();
    try {
        $consulta = $enlace_db->prepare(
            "SELECT * FROM `tb_gestion_coaching_documento` WHERE `gcd_id` = ? AND `gcd_paquete` = ? FOR UPDATE"
        );
        $consulta->bind_param('is', $gcd_id, $gcp_id);
        $consulta->execute();
        $documento = $consulta->get_result()->fetch_assoc();

        if (!$documento) {
            throw new RuntimeException('El documento indicado no existe para este paquete.');
        }

        $firmaExistente = obtenerFirmaPorDocumento($enlace_db, $gcd_id);

        if (!puedeFirmar($documento, $firmaExistente)) {
            throw new RuntimeException(
                'Este documento ya no puede firmarse: o ya fue firmado, o existe una versión más reciente ' .
                'generada después de este documento (el contenido cambió y debe firmarse la versión vigente).'
            );
        }

        // Verificación de integridad: el PDF en disco debe coincidir
        // exactamente con el hash que se registró al generarlo. Si no
        // coincide, se aborta y se audita como incidente, no se deja firmar
        // un documento que pudo haber sido alterado después de generado.
        $hashActual = hash_file('sha256', $documento['gcd_ruta']);
        if ($hashActual === false || $hashActual !== $documento['gcd_hash_sha256']) {
            throw new RuntimeException(
                'No fue posible firmar: el documento no superó la validación de integridad. ' .
                'Contacte al administrador del sistema.'
            );
        }

        $gcf_id = insertarFirma(
            $enlace_db,
            $gcp_id,
            $gcd_id,
            $usu_id,
            $rol,
            $hashActual,
            $ip,
            $user_agent,
            COACHING_TEXTO_CONSENTIMIENTO_V1
        );

        $enlace_db->commit();
    } catch (Throwable $e) {
        $enlace_db->rollback();
        if (str_contains($e->getMessage(), 'validación de integridad')) {
            registrarErrorCoaching($enlace_db, $gcp_id, "Intento de firma con hash no coincidente en documento gcd_id={$gcd_id}.");
        }
        throw $e;
    }

    // Bloque 2: transición de estado, en su propia transacción independiente.
    ejecutarTransicion($enlace_db, $gcp_id, 'FIRMAR', $usu_id, $ip, null);

    return $gcf_id;
}
