<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");

    $titulo_header = "Coaching | Detalle";

    $perfil_coaching = coachingPerfilUsuarioActual();
    if ($perfil_coaching === null) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $gcp_id = validar_input(base64_decode($_GET['reg']));

    // Autorización por RECURSO: revalida siempre en servidor, sin confiar
    // en que el usuario solo haya llegado aquí desde un link de su bandeja.
    if (!usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil_coaching, $gcp_id)) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    // Datos principales del paquete
    $consulta_paquete = $enlace_db->prepare(
        "SELECT P.`gcp_id`, P.`gcp_origen_tipo`, P.`gcp_monitoreo_id`, T.`gct_nombre`, T.`gct_codigo`,
                E.`gce_nombre`, E.`gce_codigo`,
                TA.`usu_nombres_apellidos` AS agente_nombre, TA.`usu_id` AS agente_id,
                TS.`usu_nombres_apellidos` AS supervisor_nombre, TS.`usu_id` AS supervisor_id,
                P.`gcp_fecha_asignacion`, P.`gcp_fecha_limite`,
                P.`gcp_fecha_cierre`, P.`gcp_prioridad`, P.`gcp_activo`, P.`gcp_motivo_anulacion`,
                P.`gcp_registro_fecha`
         FROM `tb_gestion_coaching_paquete` AS P
         LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
         LEFT JOIN `tb_gestion_coaching_estado` AS E ON P.`gcp_estado_id` = E.`gce_id`
         LEFT JOIN `tb_administrador_usuario` AS TA ON P.`gcp_agente_id` = TA.`usu_id`
         LEFT JOIN `tb_administrador_usuario` AS TS ON P.`gcp_supervisor_id` = TS.`usu_id`
         WHERE P.`gcp_id` = ? LIMIT 1"
    );
    $consulta_paquete->bind_param('s', $gcp_id);
    $consulta_paquete->execute();
    $paquete = $consulta_paquete->get_result()->fetch_assoc();

    if (!$paquete) {
        header("Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes");
        exit;
    }

    // Fotografía de calidad (solo si origen = monitoreo)
    $calidad = null;
    if ($paquete['gcp_origen_tipo'] === 'monitoreo') {
        $consulta_calidad = $enlace_db->prepare(
            "SELECT * FROM `tb_gestion_coaching_calidad` WHERE `gcc2_paquete` = ? LIMIT 1"
        );
        $consulta_calidad->bind_param('s', $gcp_id);
        $consulta_calidad->execute();
        $calidad = $consulta_calidad->get_result()->fetch_assoc();
    }

    // Compromisos
    $consulta_compromisos = $enlace_db->prepare(
        "SELECT * FROM `tb_gestion_coaching_compromiso` WHERE `gccm_paquete` = ? ORDER BY `gccm_id` ASC"
    );
    $consulta_compromisos->bind_param('s', $gcp_id);
    $consulta_compromisos->execute();
    $compromisos = $consulta_compromisos->get_result()->fetch_all(MYSQLI_ASSOC);

    // Historial (timeline) - inmutable, orden cronológico
    $consulta_historial = $enlace_db->prepare(
        "SELECT H.*, U.`usu_nombres_apellidos` FROM `tb_gestion_coaching_historial` AS H
         LEFT JOIN `tb_administrador_usuario` AS U ON H.`gch2_usuario` = U.`usu_id`
         WHERE H.`gch2_paquete` = ? ORDER BY H.`gch2_registro_fecha` ASC"
    );
    $consulta_historial->bind_param('s', $gcp_id);
    $consulta_historial->execute();
    $historial = $consulta_historial->get_result()->fetch_all(MYSQLI_ASSOC);

    // Documento vigente + firma (si el tipo la requiere)
    $documento_vigente = obtenerDocumentoVigenteSiExiste($enlace_db, $gcp_id, $paquete['gct_codigo']);
    $firma_vigente = null;
    if ($documento_vigente) {
        $firma_vigente = obtenerFirmaPorDocumento($enlace_db, (int) $documento_vigente['gcd_id']);
    }

    /** Helper local: nombre del documento según el código de tipo de paquete. */
    function obtenerDocumentoVigenteSiExiste(mysqli $enlace_db, string $gcp_id, string $tipo_codigo): ?array
    {
        $mapa = [
            'RETROALIMENTACION' => 'Retroalimentacion',
            'ACTA_COMPROMISO'   => 'Acta_Compromiso',
            'FELICITACION'      => 'Felicitacion',
            'RECONOCIMIENTO'    => 'Reconocimiento',
        ];
        $tipo_documento = $mapa[$tipo_codigo] ?? null;
        if (!$tipo_documento) {
            return null;
        }
        return obtenerDocumentoVigente($enlace_db, $gcp_id, $tipo_documento);
    }

    /**
     * Clase de badge por estado, siguiendo EXACTAMENTE el patrón visual que
     * ya usa el sistema en dash_estado_* (pastilla: borde de color + fondo
     * tenue + texto de color, no "badges" sólidos de Bootstrap).
     */
    function claseEstadoCoaching(string $gce_codigo): string
    {
        $mapa = [
            'BORRADOR'               => 'coaching_estado_gris',
            'ASIGNADO'                => 'coaching_estado_azul',
            'PENDIENTE_SUPERVISOR'    => 'coaching_estado_azul',
            'PENDIENTE_AGENTE'        => 'coaching_estado_naranja',
            'RESPONDIDO_AGENTE'       => 'coaching_estado_azul',
            'PENDIENTE_FIRMA_AGENTE'  => 'coaching_estado_naranja',
            'EN_SEGUIMIENTO'          => 'coaching_estado_morado',
            'PENDIENTE_CIERRE'        => 'coaching_estado_azul',
            'CERRADO'                 => 'coaching_estado_verde',
            'RECHAZADO'               => 'coaching_estado_rojo',
            'ANULADO'                 => 'coaching_estado_gris',
        ];
        return $mapa[$gce_codigo] ?? 'coaching_estado_gris';
    }

    /** Ícono FontAwesome por acción de historial. */
    function iconoAccionHistorial(string $accion): string
    {
        $mapa = [
            'CREAR_AUTOMATICO' => 'fa-robot',
            'CREAR_MANUAL'     => 'fa-user-plus',
            'ASIGNAR'          => 'fa-user-check',
            'RESPONDER'        => 'fa-reply',
            'FIRMAR'           => 'fa-signature',
            'CERRAR'           => 'fa-check-circle',
            'ANULAR'           => 'fa-ban',
            'RECHAZAR'         => 'fa-times-circle',
        ];
        return $mapa[$accion] ?? 'fa-circle';
    }

    /**
     * Texto de orientación de "próximo paso" según el estado actual —
     * mejora de UX: el usuario nunca debería preguntarse qué sigue.
     * Las pantallas de retroalimentación/respuesta aún no están construidas
     * (ver INTEGRACION.md/roadmap), por eso aquí solo se informa, sin
     * enlazar a una pantalla que todavía no existe.
     */
    function proximoPasoTexto(string $gce_codigo, string $perfil_actual): ?string
    {
        $mapa = [
            'ASIGNADO'               => 'El supervisor debe iniciar la retroalimentación de este paquete.',
            'PENDIENTE_SUPERVISOR'   => 'El supervisor debe completar la retroalimentación y enviarla al agente.',
            'PENDIENTE_AGENTE'       => 'El agente debe registrar su respuesta y compromiso.',
            'RESPONDIDO_AGENTE'      => 'El sistema generará el documento para firma del agente.',
            'PENDIENTE_FIRMA_AGENTE' => 'El agente debe firmar el documento generado.',
            'EN_SEGUIMIENTO'         => 'El supervisor debe hacer seguimiento a los compromisos pendientes.',
            'PENDIENTE_CIERRE'       => 'El supervisor debe cerrar el paquete.',
        ];
        return $mapa[$gce_codigo] ?? null;
    }

    $proximo_paso = proximoPasoTexto($paquete['gce_codigo'], $perfil_coaching);

    // Días restantes / vencido, para el indicador de fecha límite.
    $dias_restantes = null;
    if ($paquete['gcp_fecha_limite']) {
        $dias_restantes = (int) floor((strtotime($paquete['gcp_fecha_limite']) - strtotime(date('Y-m-d'))) / 86400);
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_estado_pill { display: inline-block; font-size: 12px; padding: 2px 10px 2px 10px; border-radius: 10px; font-weight: normal; }
        .coaching_estado_verde   { color: #00BF6F; border: solid 1px #00BF6F; background-color: rgba(40, 180, 99, 0.15); }
        .coaching_estado_naranja { color: #F39C12; border: solid 1px #F39C12; background-color: rgba(243, 156, 18, 0.15); }
        .coaching_estado_azul    { color: #175E83; border: solid 1px #175E83; background-color: rgba(23, 94, 131, 0.15); }
        .coaching_estado_morado  { color: #6C3483; border: solid 1px #6C3483; background-color: rgba(108, 52, 131, 0.15); }
        .coaching_estado_rojo    { color: #FF0000; border: solid 1px #FF0000; background-color: rgba(255, 0, 0, 0.15); }
        .coaching_estado_gris    { color: #6E6E6E; border: solid 1px #6E6E6E; background-color: rgba(110, 110, 110, 0.15); }

        .coaching_vencido      { color: #FF0000; font-weight: bold; }
        .coaching_por_vencer   { color: #F39C12; font-weight: bold; }

        .coaching_meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-top: 15px; padding-top: 12px; border-top: 1px solid #D8D8D8; }
        .coaching_meta .etiqueta { font-size: 10px; text-transform: uppercase; letter-spacing: .03em; color: #6E6E6E; font-weight: bold; }
        .coaching_meta .valor { font-size: 12px; color: #1A1A1A; margin-top: 2px; }

        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }

        .coaching_timeline { position: relative; margin: 0; padding: 10px; list-style: none; }
        .coaching_timeline::before { content: ''; position: absolute; left: 25px; top: 15px; bottom: 15px; width: 2px; background: #D8D8D8; }
        .coaching_timeline li { position: relative; padding-left: 40px; padding-bottom: 15px; font-size: 12px; }
        .coaching_timeline li:last-child { padding-bottom: 0; }
        .coaching_timeline .icono { position: absolute; left: 0; top: 0; width: 28px; height: 28px; border-radius: 50%; background: #FFFFFF; border: 2px solid #4CAF50; color: #4CAF50; display: flex; align-items: center; justify-content: center; font-size: 11px; z-index: 1; }
        .coaching_timeline .accion { font-weight: bold; color: #1A1A1A; }
        .coaching_timeline .meta { font-size: 11px; color: #6E6E6E; }
        .coaching_timeline .comentario { font-size: 11px; color: #555; margin-top: 3px; font-style: italic; }

        .coaching_empty_mini { color: #6E6E6E; font-size: 12px; padding: 10px; }

        .coaching_siguiente_paso {
            background: #FFFFFF;
            border: 1px solid #4CAF50;
            border-left: 4px solid #4CAF50;
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 12px;
            color: #1A1A1A;
        }
        .coaching_siguiente_paso .fas { color: #4CAF50; margin-right: 5px; }
    </style>
</head>
<body onresize="tabla_fixed();" onload="tabla_fixed();">
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
        include("lib/coaching_widget_flotante.php");
    ?>
    <div class="contenido">

        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <span><?php echo validar_output($paquete['gcp_id']); ?></span>
        </nav>

        <div class="d-flex justify-content-end mb-2">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes" class="btn-corp-2 px-2 py-1" style="border-radius:5px;">
                <span class="fas fa-arrow-left"></span> Volver a la bandeja
            </a>
        </div>

        <!-- Encabezado resumen, estilo cuadro_dash -->
        <div class="cuadro_dash mb-3">
            <div class="cuadro_dash_titulo p-2">
                <strong><?php echo validar_output($paquete['gcp_id']); ?></strong>
                <span class="coaching_estado_pill <?php echo $clase_estado; ?> ml-2"><?php echo validar_output($paquete['gce_nombre']); ?></span>
                <?php if ((int) $paquete['gcp_activo'] === 0): ?>
                    <span class="coaching_estado_pill coaching_estado_gris">Anulado</span>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <div class="descripcion-seccion-conocimiento-detalle">
                    <?php echo validar_output($paquete['gct_nombre']); ?> ·
                    Origen <?php echo validar_output(ucfirst($paquete['gcp_origen_tipo'])); ?>
                    <?php echo $paquete['gcp_monitoreo_id'] ? ' (' . validar_output($paquete['gcp_monitoreo_id']) . ')' : ''; ?>
                    · Creado el <?php echo date('d/m/Y', strtotime($paquete['gcp_registro_fecha'])); ?>
                </div>

                <div class="coaching_meta">
                    <div>
                        <div class="etiqueta">Agente</div>
                        <div class="valor"><?php echo validar_output($paquete['agente_nombre'] ?? '—'); ?></div>
                    </div>
                    <div>
                        <div class="etiqueta">Supervisor</div>
                        <div class="valor"><?php echo validar_output($paquete['supervisor_nombre'] ?? '—'); ?></div>
                    </div>
                    <div>
                        <div class="etiqueta">Prioridad</div>
                        <div class="valor"><?php echo validar_output($paquete['gcp_prioridad']); ?></div>
                    </div>
                    <div>
                        <div class="etiqueta">Fecha límite</div>
                        <div class="valor">
                            <?php if (!$paquete['gcp_fecha_limite']): ?>
                                —
                            <?php elseif ($dias_restantes !== null && $dias_restantes < 0 && (int) $paquete['gcp_activo'] === 1 && $paquete['gce_codigo'] !== 'CERRADO'): ?>
                                <span class="coaching_vencido"><span class="fas fa-exclamation-triangle"></span> <?php echo date('d/m/Y', strtotime($paquete['gcp_fecha_limite'])); ?> (vencido)</span>
                            <?php elseif ($dias_restantes !== null && $dias_restantes <= 3 && (int) $paquete['gcp_activo'] === 1 && $paquete['gce_codigo'] !== 'CERRADO'): ?>
                                <span class="coaching_por_vencer"><span class="fas fa-clock"></span> <?php echo date('d/m/Y', strtotime($paquete['gcp_fecha_limite'])); ?> (<?php echo $dias_restantes; ?> día<?php echo $dias_restantes == 1 ? '' : 's'; ?>)</span>
                            <?php else: ?>
                                <?php echo date('d/m/Y', strtotime($paquete['gcp_fecha_limite'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ((int) $paquete['gcp_activo'] === 0): ?>
                    <div class="mensaje_error_2 mt-3" style="text-align:left;">
                        <strong>Motivo de anulación:</strong> <?php echo validar_output($paquete['gcp_motivo_anulacion'] ?? ''); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($proximo_paso && (int) $paquete['gcp_activo'] === 1): ?>
        <div class="coaching_siguiente_paso mb-3 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <span class="fas fa-arrow-circle-right"></span>
                <strong>Próximo paso:</strong> <?php echo validar_output($proximo_paso); ?>
            </div>
            <div class="mt-2 mt-md-0">
                <?php if ($paquete['gce_codigo'] === 'ASIGNADO' && $perfil_coaching === 'Supervisor'): ?>
                    <a href="gestion_coaching_retroalimentacion.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">Iniciar retroalimentación</a>
                <?php elseif ($paquete['gce_codigo'] === 'PENDIENTE_SUPERVISOR' && $perfil_coaching === 'Supervisor'): ?>
                    <a href="gestion_coaching_retroalimentacion.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">Continuar retroalimentación</a>
                <?php elseif ($paquete['gce_codigo'] === 'PENDIENTE_AGENTE' && $perfil_coaching === 'Agente'): ?>
                    <a href="gestion_coaching_responder_agente.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">Responder</a>
                <?php elseif ($paquete['gce_codigo'] === 'RESPONDIDO_AGENTE' && in_array($perfil_coaching, ['Supervisor', 'Gestor', 'Administrador'], true)): ?>
                    <a href="gestion_coaching_documento_generar.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">Generar documento</a>
                <?php elseif ($paquete['gce_codigo'] === 'PENDIENTE_FIRMA_AGENTE' && $perfil_coaching === 'Agente'): ?>
                    <a href="gestion_coaching_firmar.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">Firmar documento</a>
                <?php elseif (in_array($paquete['gce_codigo'], ['PENDIENTE_CIERRE', 'EN_SEGUIMIENTO'], true) && in_array($perfil_coaching, ['Supervisor', 'Gestor', 'Administrador'], true)): ?>
                    <a href="gestion_coaching_aprobar.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp px-3 py-1" style="border-radius:5px;">Gestionar cierre</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="<?php echo $documento_vigente ? 'col-md-8' : 'col-md-12'; ?>">

                <?php if ($calidad): ?>
                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-clipboard-check"></span> Origen — Monitoreo de Calidad</div>
                    <div class="p-3">
                        <div class="coaching_meta" style="border-top:none; padding-top:0; margin-top:0;">
                            <div>
                                <div class="etiqueta">Nota general</div>
                                <div class="valor"><?php echo validar_output((string) $calidad['gcc2_nota_general']); ?></div>
                            </div>
                            <div>
                                <div class="etiqueta">PENC</div>
                                <div class="valor"><?php echo validar_output($calidad['gcc2_nota_enc'] ?? '—'); ?></div>
                            </div>
                            <div>
                                <div class="etiqueta">PECN</div>
                                <div class="valor"><?php echo validar_output($calidad['gcc2_nota_ecn'] ?? '—'); ?></div>
                            </div>
                            <div>
                                <div class="etiqueta">PECUF</div>
                                <div class="valor"><?php echo validar_output($calidad['gcc2_nota_ecuf'] ?? '—'); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($calidad['gcc2_observaciones'])): ?>
                        <div class="mt-3">
                            <div class="etiqueta">Observaciones del monitoreo</div>
                            <div class="valor"><?php echo nl2br(validar_output($calidad['gcc2_observaciones'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-handshake"></span> Compromisos</div>
                    <?php if (count($compromisos) === 0): ?>
                        <p class="coaching_empty_mini mb-0">Aún no se han registrado compromisos para este paquete.</p>
                    <?php else: ?>
                        <div class="div_tabla">
                            <table class="tabla_list">
                                <thead>
                                    <tr><th>Descripción</th><th>Responsable</th><th>Fecha límite</th><th>Estado</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($compromisos as $c): ?>
                                    <tr class="tabla_contenido_1">
                                        <td><?php echo nl2br(validar_output($c['gccm_descripcion'])); ?></td>
                                        <td><?php echo validar_output($c['gccm_responsable']); ?></td>
                                        <td><?php echo $c['gccm_fecha_limite'] ? date('d/m/Y', strtotime($c['gccm_fecha_limite'])) : '—'; ?></td>
                                        <td>
                                            <span class="coaching_estado_pill <?php echo $c['gccm_estado'] === 'Pendiente' ? 'coaching_estado_naranja' : 'coaching_estado_verde'; ?>">
                                                <?php echo validar_output($c['gccm_estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-history"></span> Historial</div>
                    <?php if (count($historial) === 0): ?>
                        <p class="coaching_empty_mini mb-0">Sin movimientos registrados todavía.</p>
                    <?php else: ?>
                        <ul class="coaching_timeline">
                            <?php foreach ($historial as $h): ?>
                                <li>
                                    <div class="icono"><span class="fas <?php echo iconoAccionHistorial($h['gch2_accion']); ?>"></span></div>
                                    <div class="accion"><?php echo validar_output($h['gch2_accion']); ?></div>
                                    <div class="meta">
                                        <?php echo validar_output($h['usu_nombres_apellidos'] ?? $h['gch2_usuario']); ?>
                                        · <?php echo date('d/m/Y H:i', strtotime($h['gch2_registro_fecha'])); ?>
                                    </div>
                                    <?php if (!empty($h['gch2_comentario'])): ?>
                                        <div class="comentario"><?php echo nl2br(validar_output($h['gch2_comentario'])); ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>

            <?php if ($documento_vigente): ?>
            <div class="col-md-4">
                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-file-pdf"></span> Documento</div>
                    <div class="p-3">
                        <p class="mb-2" style="font-size:12px;">
                            Versión vigente: <strong>v<?php echo (int) $documento_vigente['gcd_version']; ?></strong>
                        </p>
                        <a href="gestion_coaching_documento_descargar.php?reg=<?php echo base64_encode($gcp_id); ?>" target="_blank" class="btn-corp-2 d-block text-center py-2 mb-3" style="border-radius:5px;">
                            <span class="fas fa-eye"></span> Ver / descargar PDF
                        </a>

                        <?php if ($firma_vigente): ?>
                            <div class="dash_estado_provisionado" style="width:100%; text-align:center; display:block;">
                                <span class="fas fa-check-circle"></span> Firmado por <?php echo validar_output($firma_vigente['gcf_firmante_usuario']); ?>
                            </div>
                            <div class="coaching_empty_mini text-center">
                                el <?php echo date('d/m/Y H:i', strtotime($firma_vigente['gcf_registro_fecha'])); ?>
                            </div>
                        <?php elseif ($paquete['gce_codigo'] === 'PENDIENTE_FIRMA_AGENTE' && $perfil_coaching === 'Agente'): ?>
                            <a href="gestion_coaching_firmar.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp d-block text-center py-2" style="border-radius:5px;">
                                <span class="fas fa-signature"></span> Firmar documento
                            </a>
                        <?php else: ?>
                            <p class="coaching_empty_mini mb-0">Pendiente de firma del agente.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
