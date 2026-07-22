<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_reglas.php';
require_once __DIR__ . '/coaching_datos.php';

/**
 * gestion_coaching/lib/coaching_disparador.php
 *
 * Hook de integración de Modelo 1 (Coaching desde Monitoreo de Calidad).
 * Se llama, con UNA sola línea, desde el final de:
 *   - gestion_calidad/gestion_calidad_monitoreo_guardar.php   (creación)
 *   - gestion_calidad/gestion_calidad_monitoreo_aceptar.php   (Pendiente -> Aceptado)
 *   - gestion_calidad/gestion_calidad_monitoreo_refutar.php   (-> Refutado-Rechazado / Refutado-Aceptado)
 *
 * Alcance confirmado por negocio: SOLO tb_gestion_calidad_monitoreo.
 * Auditoría y Calibración NO disparan coaching.
 *
 * Contrato de no interferencia: esta función NUNCA debe permitir que un
 * error propio interrumpa el flujo del archivo que la llamó. El monitoreo
 * ya se guardó/aceptó/refutó exitosamente antes de esta llamada; si algo
 * falla aquí, se audita en tb_administrador_log y se continúa.
 */
function evaluarDisparoCoachingAutomatico(
    mysqli $enlace_db,
    string $gcm_id,
    string $gcm_estado,
    string $usu_id_actor
): void {
    try {
        // 1) Guardado parcial / estado no terminal: no hace nada.
        //    Esto cumple la restricción "no generar coaching automático
        //    durante un guardado parcial".
        if (!estadoMonitoreoEsTerminal($gcm_estado)) {
            return;
        }

        // 2) Trae el snapshot completo y verificado desde la BD (nunca se
        //    confía en variables sueltas pasadas desde el request web).
        $snapshot = obtenerSnapshotMonitoreoCalidad($enlace_db, $gcm_id);
        if ($snapshot === null) {
            registrarErrorCoaching($enlace_db, $gcm_id, 'Monitoreo no encontrado al evaluar disparo de coaching.');
            return;
        }

        $requiereCoaching = notaRequiereCoaching($snapshot['nota_general']);
        $paqueteVigente    = buscarPaqueteAutomaticoVigente($enlace_db, $gcm_id);

        // 3) Nota ya no amerita coaching pero existe un paquete vigente
        //    (ej.: la nota subió tras una refutación) -> se anula.
        if (!$requiereCoaching) {
            if ($paqueteVigente !== null) {
                anularPaquete($enlace_db, $paqueteVigente['gcp_id'], 'Nota corregida a >= 90 tras revisión/refutación, ya no aplica coaching.', 'SISTEMA');
            }
            return;
        }

        // 4) Nota amerita coaching y ya existe un paquete vigente:
        //    solo se actúa si la nota registrada cambió (regla confirmada
        //    por negocio: anular + recrear encadenado, no se edita en sitio
        //    el paquete ya en curso para no perder lo que el supervisor
        //    haya avanzado).
        if ($paqueteVigente !== null) {
            $notaGuardada = (float) $paqueteVigente['gcc2_nota_general'];
            if (!notaCambioTrasRefutacion($notaGuardada, $snapshot['nota_general'])) {
                return; // idéntico a lo ya existente, no hay nada que hacer
            }

            anularPaquete($enlace_db, $paqueteVigente['gcp_id'], 'Nota modificada por refutación/conciliación, se reemplaza el paquete.', 'SISTEMA');
            crearPaqueteAutomatico($enlace_db, $snapshot, $paqueteVigente['gcp_id']);
            return;
        }

        // 5) Caso normal: no existe paquete vigente y la nota lo amerita.
        crearPaqueteAutomatico($enlace_db, $snapshot);
    } catch (Throwable $e) {
        // Nunca se propaga: se audita y se sigue. El guardado del monitoreo
        // ya ocurrió y no debe verse afectado por un fallo de este módulo.
        registrarErrorCoaching($enlace_db, $gcm_id, $e->getMessage());
    }
}

/**
 * Trae y normaliza los campos de tb_gestion_calidad_monitoreo necesarios
 * para crear el paquete, en un único SELECT. Equivalencias confirmadas
 * por negocio: PECUF=gcm_nota_ecuf, PECN=gcm_nota_ecn, PENC=gcm_nota_enc.
 */
function obtenerSnapshotMonitoreoCalidad(mysqli $enlace_db, string $gcm_id): ?array
{
    $consulta = $enlace_db->prepare(
        "SELECT `gcm_id`, `gcm_matriz`, `gcm_responsable`, `gcm_analista`,
                `gcm_nota_general`, `gcm_nota_enc`, `gcm_nota_ecn`, `gcm_nota_ecuf`,
                `gcm_nota_enc_estado`, `gcm_nota_ecn_estado`, `gcm_nota_ecuf_estado`,
                `gcm_estado`, `gcm_tipo_monitoreo`, `gcm_skill_interaccion`, `gcm_tipo_gestion`,
                `gcm_segmento`, `gcm_id_sim`, `gcm_id_ani`, `gcm_observaciones_monitoreo`,
                `gcm_fecha_monitoreo`, `gcm_fecha_hora_cierre`
         FROM `tb_gestion_calidad_monitoreo` WHERE `gcm_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $gcm_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    if (!$fila) {
        return null;
    }

    // NOTA (asunción a confirmar con negocio si no coincide): gcm_responsable
    // es el agente cuya interacción fue evaluada; gcm_analista es el analista
    // de calidad que realizó la evaluación. Se toma así porque es el uso
    // consistente encontrado en las consultas reales del módulo de Calidad.
    return [
        'monitoreo_id'       => $fila['gcm_id'],
        'matriz'             => $fila['gcm_matriz'],
        'agente_id'          => $fila['gcm_responsable'],
        'analista_id'        => $fila['gcm_analista'],
        'nota_general'       => $fila['gcm_nota_general'],
        'nota_enc'           => $fila['gcm_nota_enc'],
        'nota_ecn'           => $fila['gcm_nota_ecn'],
        'nota_ecuf'          => $fila['gcm_nota_ecuf'],
        'nota_enc_estado'    => $fila['gcm_nota_enc_estado'],
        'nota_ecn_estado'    => $fila['gcm_nota_ecn_estado'],
        'nota_ecuf_estado'   => $fila['gcm_nota_ecuf_estado'],
        'gcm_estado'         => $fila['gcm_estado'],
        'tipo_monitoreo'     => $fila['gcm_tipo_monitoreo'],
        'skill_interaccion'  => $fila['gcm_skill_interaccion'],
        'tipo_gestion'       => $fila['gcm_tipo_gestion'],
        'segmento'           => $fila['gcm_segmento'],
        'id_sim'             => $fila['gcm_id_sim'],
        'id_ani'             => $fila['gcm_id_ani'],
        'observaciones'      => $fila['gcm_observaciones_monitoreo'],
        'fecha_monitoreo'    => $fila['gcm_fecha_monitoreo'] ?: null,
        'fecha_cierre'       => $fila['gcm_fecha_hora_cierre'] ?: null,
    ];
}

// Nota: registrarErrorCoaching() ahora vive en coaching_datos.php (capa
// compartida), porque coaching_firma.php también necesita auditar
// incidentes sin depender de este archivo.