<?php
declare(strict_types=1);

/**
 * gestion_coaching/lib/coaching_reglas.php
 *
 * Reglas de negocio PURAS: reciben datos ya cargados, no tocan la BD.
 * Esto es lo que las hace testeables con PHPUnit sin mockear mysqli.
 */

/**
 * Estados terminales de tb_gestion_calidad_monitoreo.gcm_estado en los que
 * la nota se considera FIRME (confirmado con negocio: solo estos 3).
 * Pendiente, Refutado y Refutado-Conciliación quedan fuera por estar en proceso.
 */
const COACHING_ESTADOS_MONITOREO_TERMINALES = ['Aceptado', 'Refutado-Rechazado', 'Refutado-Aceptado'];

/**
 * Umbral de negocio confirmado: nota general < 90 dispara coaching.
 * Aislado en constante para no repetir el número mágico en el código.
 */
const COACHING_UMBRAL_NOTA_GENERAL = 90.0;

function estadoMonitoreoEsTerminal(string $gcm_estado): bool
{
    return in_array($gcm_estado, COACHING_ESTADOS_MONITOREO_TERMINALES, true);
}

/**
 * $nota_general puede venir como string desde una columna varchar(100)
 * heredada (tb_gestion_calidad_monitoreo.gcm_nota_general), incluyendo
 * el valor "NA". Se castea explícitamente y se maneja ese caso.
 */
function notaRequiereCoaching(string|int|float|null $nota_general): bool
{
    if ($nota_general === null || $nota_general === '' || strtoupper((string) $nota_general) === 'NA') {
        return false;
    }
    if (!is_numeric($nota_general)) {
        return false;
    }
    return ((float) $nota_general) < COACHING_UMBRAL_NOTA_GENERAL;
}

/**
 * Compara la nota histórica guardada en el paquete vigente contra la nota
 * actual del monitoreo, para decidir si hubo cambio por refutación
 * (regla confirmada por negocio: si cambia, se anula y se re-evalúa).
 */
function notaCambioTrasRefutacion(float $nota_guardada, string|int|float|null $nota_actual): bool
{
    if ($nota_actual === null || !is_numeric($nota_actual)) {
        return true; // paso de numérica a NA también cuenta como cambio relevante
    }
    // Comparación con tolerancia de centavos por posibles redondeos de decimal(6,2)
    return abs($nota_guardada - (float) $nota_actual) > 0.001;
}

/**
 * Valida si un documento puede firmarse: debe ser la versión VIGENTE
 * (no reemplazada) y no tener ya una firma registrada para esa versión.
 */
function puedeFirmar(array $documentoVigente, ?array $firmaExistente): bool
{
    if (($documentoVigente['gcd_estado'] ?? '') !== 'Vigente') {
        return false;
    }
    return $firmaExistente === null;
}

/**
 * Valida si un paquete puede cerrarse. Recibe el paquete ya cargado con
 * sus relaciones (compromisos, firma, encuesta) resueltas por el llamador.
 * @return string[] lista de errores; vacío = puede cerrarse
 */
function puedeCerrarse(array $paquete, array $tipo, array $compromisos, ?array $firma_vigente, ?array $encuesta): array
{
    $errores = [];

    if (!empty($tipo['gct_requiere_respuesta_agente']) && empty($paquete['respuesta_agente_id'])) {
        $errores[] = 'El agente aún no ha registrado su respuesta.';
    }

    foreach ($compromisos as $compromiso) {
        if (($compromiso['gccm_estado'] ?? '') === 'Pendiente') {
            $errores[] = 'Existen compromisos pendientes sin resolver.';
            break;
        }
    }

    if (!empty($tipo['gct_requiere_firma_agente']) && $firma_vigente === null) {
        $errores[] = 'El agente aún no ha firmado el documento vigente.';
    }

    return $errores;
}



