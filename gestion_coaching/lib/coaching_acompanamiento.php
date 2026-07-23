<?php
declare(strict_types=1);

require_once __DIR__ . '/coaching_datos.php';

function generarConsecutivoAcompanamiento(mysqli $db): string
{
    $rs = $db->query("SELECT MAX(CAST(SUBSTRING(`gca_id`,3) AS UNSIGNED)) AS ultimo FROM `tb_gestion_coaching_acompanamiento` FOR UPDATE");
    $ultimo = (int) (($rs && ($f = $rs->fetch_assoc())) ? ($f['ultimo'] ?? 0) : 0);
    return 'AC' . str_pad((string) ($ultimo + 1), 10, '0', STR_PAD_LEFT);
}

function listarAccionesAcompanamiento(mysqli $db): array
{
    return $db->query("SELECT * FROM `tb_gestion_coaching_accion_seguimiento` WHERE `gcas_activo`=1 ORDER BY `gcas_orden`")->fetch_all(MYSQLI_ASSOC);
}

function listarSegmentosAcompanamiento(mysqli $db): array
{
    return $db->query("SELECT * FROM `tb_gestion_coaching_segmento` WHERE `gcsg_activo`=1 ORDER BY `gcsg_orden`,`gcsg_nombre`")->fetch_all(MYSQLI_ASSOC);
}

function listarIndicadoresAcompanamiento(mysqli $db): array
{
    return $db->query("SELECT * FROM `tb_gestion_coaching_indicador_seguimiento` WHERE `gcis_activo`=1 ORDER BY `gcis_categoria`,`gcis_orden`,`gcis_nombre`")->fetch_all(MYSQLI_ASSOC);
}

function obtenerAccionAcompanamiento(mysqli $db, int $accionId): ?array
{
    $st = $db->prepare("SELECT * FROM `tb_gestion_coaching_accion_seguimiento` WHERE `gcas_id`=? AND `gcas_activo`=1 LIMIT 1");
    $st->bind_param('i', $accionId);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function crearAcompanamiento(mysqli $db, array $d, string $usuario): array
{
    $db->begin_transaction();
    try {
        $stAg = $db->prepare("SELECT `usu_id`,`usu_nombres_apellidos`,`usu_correo_corporativo`,`usu_supervisor`,`usu_campania` FROM `tb_administrador_usuario` WHERE `usu_id`=? AND `usu_estado`='Activo' LIMIT 1 FOR UPDATE");
        $stAg->bind_param('s', $d['colaborador_id']);
        $stAg->execute();
        $agente = $stAg->get_result()->fetch_assoc();
        if (!$agente) {
            throw new RuntimeException('El colaborador seleccionado no existe o no está activo.');
        }
        $perfil = coachingPerfilUsuarioActual();
        if ($perfil === 'Supervisor' && $agente['usu_supervisor'] !== $usuario) {
            throw new RuntimeException('El colaborador seleccionado no pertenece a su equipo.');
        }

        $accion = obtenerAccionAcompanamiento($db, (int)$d['accion_id']);
        if (!$accion) {
            throw new RuntimeException('La acción de seguimiento no es válida.');
        }

        $idsIndicadores = array_values(array_unique(array_map('intval', $d['indicadores'] ?? [])));
        if (!$idsIndicadores) {
            throw new RuntimeException('Debe seleccionar al menos un indicador.');
        }

        $paqueteId = null;
        if ((int)$accion['gcas_genera_paquete'] === 1 && !empty($accion['gcas_tipo_paquete_codigo'])) {
            $paqueteId = generarConsecutivoPaquete($db);
            $estadoId = obtenerEstadoIdPorCodigo($db, 'ASIGNADO');
            $tipoId = obtenerTipoIdPorCodigo($db, $accion['gcas_tipo_paquete_codigo']);
            $prioridad = $d['prioridad'] ?? 'Normal';
            $fechaLimite = $d['fecha_limite'] ?: null;
            $segmentoNombre = $d['segmento_nombre'];
            $campaniaId = $agente['usu_campania'] ?: null;
            $indicadorPrincipal = null;
            $insP = $db->prepare("INSERT INTO `tb_gestion_coaching_paquete`
                (`gcp_id`,`gcp_origen_tipo`,`gcp_tipo_id`,`gcp_indicador_id`,`gcp_agente_id`,`gcp_supervisor_id`,`gcp_estado_id`,`gcp_campania_id`,`gcp_segmento`,`gcp_prioridad`,`gcp_fecha_asignacion`,`gcp_fecha_limite`,`gcp_creado_por`)
                VALUES (?,'global',?,?,?,?,?,?,?,?,NOW(),?,?)");
            $insP->bind_param('siissiissss', $paqueteId, $tipoId, $indicadorPrincipal, $d['colaborador_id'], $usuario, $estadoId, $campaniaId, $segmentoNombre, $prioridad, $fechaLimite, $usuario);
            if (!$insP->execute()) {
                throw new RuntimeException('No fue posible crear el paquete asociado.');
            }
            insertarHistorial($db, $paqueteId, null, $estadoId, 'CREAR_DESDE_ACOMPANAMIENTO', $d['motivo'], $usuario, null);
        }

        $gcaId = generarConsecutivoAcompanamiento($db);
        $estado = 'REGISTRADO';
        $anio = (int)$d['anio'];
        $mes = (int)$d['mes'];
        $accionId = (int)$d['accion_id'];
        $segmentoId = (int)$d['segmento_id'];
        $ins = $db->prepare("INSERT INTO `tb_gestion_coaching_acompanamiento`
            (`gca_id`,`gca_paquete_id`,`gca_colaborador_id`,`gca_lider_id`,`gca_correo_lider`,`gca_segmento_id`,`gca_empresa`,`gca_anio`,`gca_mes`,`gca_accion_id`,`gca_motivo_justificacion`,`gca_compromisos_colaborador`,`gca_estado`,`gca_registro_usuario`)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $ins->bind_param('sssssisiiissss', $gcaId, $paqueteId, $d['colaborador_id'], $usuario, $d['correo_lider'], $segmentoId, $d['empresa'], $anio, $mes, $accionId, $d['motivo'], $d['compromisos'], $estado, $usuario);
        if (!$ins->execute()) {
            throw new RuntimeException('No fue posible registrar el acompañamiento.');
        }

        $insI = $db->prepare("INSERT INTO `tb_gestion_coaching_acompanamiento_indicador` (`gcai_acompanamiento_id`,`gcai_indicador_id`,`gcai_registro_usuario`) VALUES (?,?,?)");
        foreach ($idsIndicadores as $idIndicador) {
            $insI->bind_param('sis', $gcaId, $idIndicador, $usuario);
            if (!$insI->execute()) {
                throw new RuntimeException('No fue posible asociar los indicadores.');
            }
        }

        if ((int)$accion['gcas_requiere_escalamiento'] === 1) {
            if (trim((string)($d['escalamiento_asunto'] ?? '')) === '' || trim((string)($d['escalamiento_fecha'] ?? '')) === '' || trim((string)($d['escalamiento_destinatario'] ?? '')) === '') {
                throw new RuntimeException('Complete asunto, fecha/hora y destinatario del escalamiento.');
            }
            $insE = $db->prepare("INSERT INTO `tb_gestion_coaching_escalamiento`
                (`gces_acompanamiento_id`,`gces_tipo`,`gces_asunto_correo`,`gces_fecha_hora_envio`,`gces_destinatario_nombre`,`gces_destinatario_correo`,`gces_observaciones`,`gces_registro_usuario`)
                VALUES (?,?,?,?,?,?,?,?)");
            $destCorreo = $d['escalamiento_correo'] ?: null;
            $obs = $d['escalamiento_observaciones'] ?: null;
            $fechaEscalamiento = str_replace('T', ' ', $d['escalamiento_fecha']);
            $insE->bind_param('ssssssss', $gcaId, $accion['gcas_codigo'], $d['escalamiento_asunto'], $fechaEscalamiento, $d['escalamiento_destinatario'], $destCorreo, $obs, $usuario);
            if (!$insE->execute()) {
                throw new RuntimeException('No fue posible registrar el escalamiento.');
            }
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $detalle = 'Registro creado. Acción: ' . $accion['gcas_nombre'];
        $hist = $db->prepare("INSERT INTO `tb_gestion_coaching_acompanamiento_historial` (`gcah_acompanamiento_id`,`gcah_accion`,`gcah_detalle`,`gcah_usuario`,`gcah_ip`) VALUES (?,'CREAR',?,?,?)");
        $hist->bind_param('ssss', $gcaId, $detalle, $usuario, $ip);
        $hist->execute();

        $db->commit();
        return ['acompanamiento_id' => $gcaId, 'paquete_id' => $paqueteId];
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function usuarioPuedeVerAcompanamiento(mysqli $db, string $id, string $usuario, string $perfil): bool
{
    if (in_array($perfil, ['Administrador','Gestor','Calidad','Coordinación','Gerencia'], true)) {
        return true;
    }
    $st = $db->prepare("SELECT `gca_colaborador_id`,`gca_lider_id` FROM `tb_gestion_coaching_acompanamiento` WHERE `gca_id`=? AND `gca_activo`=1 LIMIT 1");
    $st->bind_param('s', $id);
    $st->execute();
    $f = $st->get_result()->fetch_assoc();
    if (!$f) return false;
    return ($perfil === 'Agente' && $f['gca_colaborador_id'] === $usuario)
        || ($perfil === 'Supervisor' && $f['gca_lider_id'] === $usuario);
}
