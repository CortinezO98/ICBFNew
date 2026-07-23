<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");
    require_once("lib/coaching_documentos.php");

    $titulo_header = "Coaching | Mi respuesta";

    $perfil_coaching = coachingPerfilUsuarioActual();
    if ($perfil_coaching === null || $perfil_coaching !== 'Agente') {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $gcp_id = validar_input(base64_decode($_GET['reg']));

    if (!usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil_coaching, $gcp_id)) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $paquete = obtenerPaqueteConDetalle($enlace_db, $gcp_id);
    if (!$paquete) {
        header("Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes");
        exit;
    }

    // El agente autenticado debe ser exactamente el asignado (sin excepción de perfil).
    if ($paquete['gcp_agente_id'] !== $_SESSION['usu_id']) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    if ($paquete['gce_codigo'] !== 'PENDIENTE_AGENTE') {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    $retro = obtenerRetroalimentacion($enlace_db, $gcp_id);
    $compromisos = listarCompromisosPorPaquete($enlace_db, $gcp_id);

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";
    $errores_campo = [];

    if (isset($_POST["guardar_registro"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } else {
            $compromiso_general       = validar_input($_POST['compromiso_general']);
            $acciones_no_reincidencia = validar_input($_POST['acciones_no_reincidencia']);
            $aspectos_relevantes      = validar_input($_POST['aspectos_relevantes']);
            $observaciones            = validar_input($_POST['observaciones']);
            $confirma_claridad        = isset($_POST['confirma_claridad']);

            if ($compromiso_general === '') { $errores_campo['compromiso_general'] = 'Este campo es obligatorio.'; }
            if (!$confirma_claridad) { $errores_campo['confirma_claridad'] = 'Debe confirmar que la retroalimentación fue clara.'; }

            if (count($errores_campo) > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('Revise los campos marcados en rojo.', 0);</script>";
            } else {
                try {
                    guardarRespuestaAgente($enlace_db, $gcp_id, [
                        'compromiso_general'       => $compromiso_general,
                        'acciones_no_reincidencia' => $acciones_no_reincidencia !== '' ? $acciones_no_reincidencia : null,
                        'aspectos_relevantes'      => $aspectos_relevantes !== '' ? $aspectos_relevantes : null,
                        'confirma_claridad'        => $confirma_claridad,
                        'observaciones'            => $observaciones !== '' ? $observaciones : null,
                    ], $_SESSION['usu_id']);

                    ejecutarTransicion($enlace_db, $gcp_id, 'RESPONDER', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, null);

                    // Genera el documento automáticamente (perfil 'Sistema' en
                    // la máquina de estados) y avanza a PENDIENTE_FIRMA_AGENTE.
                    // Si la generación de PDF falla (p. ej. mPDF no instalado
                    // todavía), NO se revierte la respuesta ya guardada — el
                    // paquete queda en RESPONDIDO_AGENTE y el supervisor puede
                    // reintentar la generación manualmente después.
                    try {
                        $indicadores_adicionales = listarIndicadoresPaquete($enlace_db, $gcp_id);
                        $escalamiento = obtenerEscalamiento($enlace_db, $gcp_id);
                        $html = construirHtmlDocumentoPorTipo($gcp_id, $paquete, $retro, $compromisos, [
                            'gcra_compromiso_general' => $compromiso_general,
                            'gcra_acciones_no_reincidencia' => $acciones_no_reincidencia,
                        ], $indicadores_adicionales, $escalamiento);
                        $tipo_documento = $paquete['gct_codigo'] === 'ACTA_COMPROMISO' ? 'Acta_Compromiso' : 'Retroalimentacion';
                        generarDocumentoCoaching($enlace_db, $gcp_id, $tipo_documento, $html, 'SISTEMA');
                        ejecutarTransicion($enlace_db, $gcp_id, 'GENERAR_DOCUMENTO', 'SISTEMA', null, null);
                        $respuesta_accion = "<script type='text/javascript'>alertify.success('Respuesta registrada. El documento quedó listo para firma.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1400);</script>";
                    } catch (Throwable $e) {
                        registrarErrorCoaching($enlace_db, $gcp_id, 'Fallo al generar documento tras respuesta del agente: ' . $e->getMessage());
                        $respuesta_accion = "<script type='text/javascript'>alertify.success('Respuesta registrada. El documento se generará en breve.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1400);</script>";
                    }
                } catch (Throwable $e) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
                }
            }
        }
    }

    // construirHtmlDocumentoRetroalimentacion() ahora vive en
    // coaching_documentos.php (capa compartida), porque
    // gestion_coaching_documento_generar.php también la necesita para
    // regenerar manualmente.
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 4px; display: block; color: #1A1A1A; }
        .coaching_campo_error { border: 1px solid #FF0000 !important; }
        .coaching_campo_error_texto { color: #FF0000; font-size: 11px; margin-top: 3px; }
        .coaching_solo_lectura { background: #F2F2F2; border-radius: 5px; padding: 10px; font-size: 12px; white-space: pre-line; }
        #btn_guardar[disabled] { opacity: .7; cursor: not-allowed; }

        .coaching_card_titulo { display: flex; align-items: center; gap: 8px; }
        .coaching_card_titulo .badge-paso {
            width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; color: #4CAF50;
            display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; flex-shrink: 0;
        }
        .coaching_contador { font-size: 10px; color: #6E6E6E; text-align: right; margin-top: 3px; }

        /* La casilla de confirmación usa clases de Bootstrap que el
           style.css del sitio sobreescribe globalmente (deja el checkbox
           estirado como una barra) — se corrige aquí con un checkbox
           propio de tamaño y forma fijos. */
        .coaching_confirmacion {
            background: #F1F8F2; border: 1px solid #4CAF50; border-radius: 6px;
            padding: 14px; margin-top: 20px; display: flex; align-items: flex-start; gap: 10px;
        }
        .coaching_confirmacion input[type="checkbox"] {
            all: revert !important;
            -webkit-appearance: checkbox !important; appearance: checkbox !important;
            opacity: 1 !important; visibility: visible !important; position: static !important;
            width: 18px !important; height: 18px !important; min-width: 18px !important;
            margin: 2px 0 0 0 !important; flex-shrink: 0; cursor: pointer;
        }
        .coaching_confirmacion input[type="checkbox"]::before,
        .coaching_confirmacion input[type="checkbox"]::after { content: none !important; display: none !important; }
        .coaching_confirmacion label { font-size: 12px; color: #1A1A1A; margin: 0; cursor: pointer; line-height: 1.5; }
        .coaching_confirmacion.error { border-color: #FF0000; background: #FDEDED; }
    </style>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
        echo $respuesta_accion;
    ?>
    <div class="contenido">
        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>"><?php echo validar_output($gcp_id); ?></a>
            <span class="mx-1">/</span>
            <span>Mi respuesta</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-3">
                    <h4 class="titulo_seccion mb-0">Retroalimentación recibida</h4>
                    <span class="descripcion-seccion-conocimiento">Paquete <?php echo validar_output($gcp_id); ?> · Lea con atención antes de responder</span>
                </div>

                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2 coaching_card_titulo"><span class="badge-paso">1</span> Lo que registró su supervisor</div>
                    <div class="p-3">
                        <label class="coaching_label">Causa raíz</label>
                        <div class="coaching_solo_lectura mb-3"><?php echo nl2br(validar_output($retro['gcr_causa_raiz'] ?? '')); ?></div>
                        <label class="coaching_label">Estrategia correctiva</label>
                        <div class="coaching_solo_lectura"><?php echo nl2br(validar_output($retro['gcr_estrategia_correctiva'] ?? '')); ?></div>
                    </div>
                </div>

                <?php if (count($compromisos) > 0): ?>
                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2 coaching_card_titulo"><span class="badge-paso">2</span> Compromisos definidos</div>
                    <div class="p-3">
                        <?php foreach ($compromisos as $c): ?>
                            <div class="coaching_solo_lectura mb-2"><?php echo nl2br(validar_output($c['gccm_descripcion'])); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="form_responder">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2 coaching_card_titulo"><span class="badge-paso"><?php echo count($compromisos) > 0 ? '3' : '2'; ?></span> Su respuesta</div>
                        <div class="p-3">
                            <label class="coaching_label" for="compromiso_general">Compromiso frente a la situación</label>
                            <textarea name="compromiso_general" id="compromiso_general" class="form-control <?php echo isset($errores_campo['compromiso_general']) ? 'coaching_campo_error' : ''; ?>" style="height:80px;" maxlength="1000" required><?php echo isset($_POST['compromiso_general']) ? htmlspecialchars($_POST['compromiso_general']) : ''; ?></textarea>
                            <div class="coaching_contador" id="contador_compromiso">0 / 1000</div>
                            <?php if (isset($errores_campo['compromiso_general'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['compromiso_general']; ?></div><?php endif; ?>

                            <div class="mt-3">
                                <label class="coaching_label" for="acciones_no_reincidencia">Acciones para evitar reincidencia <span style="font-weight:normal;color:#6E6E6E;font-size:10px;">(opcional)</span></label>
                                <textarea name="acciones_no_reincidencia" id="acciones_no_reincidencia" class="form-control" style="height:70px;"><?php echo isset($_POST['acciones_no_reincidencia']) ? htmlspecialchars($_POST['acciones_no_reincidencia']) : ''; ?></textarea>
                            </div>

                            <div class="mt-3">
                                <label class="coaching_label" for="aspectos_relevantes">Aspectos relevantes de la retroalimentación <span style="font-weight:normal;color:#6E6E6E;font-size:10px;">(opcional)</span></label>
                                <textarea name="aspectos_relevantes" id="aspectos_relevantes" class="form-control" style="height:70px;"><?php echo isset($_POST['aspectos_relevantes']) ? htmlspecialchars($_POST['aspectos_relevantes']) : ''; ?></textarea>
                            </div>

                            <div class="mt-3">
                                <label class="coaching_label" for="observaciones">Observaciones <span style="font-weight:normal;color:#6E6E6E;font-size:10px;">(opcional)</span></label>
                                <textarea name="observaciones" id="observaciones" class="form-control" style="height:50px;"><?php echo isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : ''; ?></textarea>
                            </div>

                            <div class="coaching_confirmacion <?php echo isset($errores_campo['confirma_claridad']) ? 'error' : ''; ?>" id="caja_confirmacion">
                                <input type="checkbox" id="confirma_claridad" name="confirma_claridad" required <?php echo isset($_POST['confirma_claridad']) ? 'checked' : ''; ?>>
                                <label for="confirma_claridad">
                                    Confirmo que la retroalimentación fue clara y entendí lo esperado.
                                    <?php if (isset($errores_campo['confirma_claridad'])): ?>
                                        <div class="coaching_campo_error_texto mt-1"><?php echo $errores_campo['confirma_claridad']; ?></div>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <button type="submit" name="guardar_registro" id="btn_guardar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0; min-width:220px;">
                            <span class="fas fa-paper-plane"></span> Enviar respuesta
                        </button>
                        <p class="descripcion-seccion-conocimiento mt-2 mb-0">
                            Una vez enviada, no podrá editar su respuesta.
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <script>
        (function () {
            var textarea = document.getElementById('compromiso_general');
            var contador = document.getElementById('contador_compromiso');
            var LIMITE = 1000;
            function actualizarContador() {
                contador.textContent = textarea.value.length + ' / ' + LIMITE;
            }
            textarea.addEventListener('input', actualizarContador);
            actualizarContador();

            var form = document.getElementById('form_responder');
            var boton = document.getElementById('btn_guardar');
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Enviando...';
                }, 0);
            });
        })();
        </script>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
