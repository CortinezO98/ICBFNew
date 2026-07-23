<?php
    $modulo_plataforma="Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_firma.php");

    $titulo_header = "Coaching | Firmar documento";

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

    $respuesta_accion = "";
    $error_general = null;
    $errores_encuesta = [];

    $paquete = obtenerPaqueteConDetalle($enlace_db, $gcp_id);
    if (!$paquete) {
        header("Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes");
        exit;
    }
    // La encuesta de percepción solo aplica al formato de Retroalimentación
    // (así viene en el Word real; Acta/Felicitación no la incluyen).
    $requiere_encuesta = $paquete['gct_codigo'] === 'RETROALIMENTACION' && !paqueteTieneEncuesta($enlace_db, $gcp_id);
    $preguntas_encuesta = $requiere_encuesta ? listarPreguntasEncuestaActivas($enlace_db) : [];

    if (isset($_POST["confirmar_firma"])) {
        // CSRF: mismo mecanismo ya usado en el resto del sistema
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } elseif (!isset($_POST['acepto'])) {
            $error_general = 'Debe marcar la casilla de aceptación antes de firmar.';
        } else {
            $respuestas_encuesta = [];
            if ($requiere_encuesta) {
                foreach ($preguntas_encuesta as $p) {
                    $valor = validar_input($_POST['encuesta'][$p['gcep_codigo']] ?? '');
                    if ($valor === '' || (int) $valor < 1 || (int) $valor > 5) {
                        $errores_encuesta[$p['gcep_codigo']] = 'Requerido.';
                    } else {
                        $respuestas_encuesta[$p['gcep_codigo']] = (int) $valor;
                    }
                }
            }

            if (count($errores_encuesta) > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('Responda todas las preguntas de la encuesta.', 0);</script>";
            } else {
                $gcd_id = (int) validar_input($_POST['gcd_id']);
                try {
                    if ($requiere_encuesta) {
                        guardarEncuestaPercepcion($enlace_db, $gcp_id, $respuestas_encuesta, $_SESSION['usu_id']);
                    }
                    firmarDocumentoCoaching(
                        $enlace_db,
                        $gcp_id,
                        $gcd_id,
                        $_SESSION['usu_id'],
                        'Agente',
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    );
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('Documento firmado correctamente.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
                } catch (Throwable $e) {
                    // Mensaje funcional, nunca el detalle técnico (ver Entregable 5, manejo de errores)
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
                }
            }
        }
    }

    $documento = obtenerDocumentoVigente($enlace_db, $gcp_id, 'Retroalimentacion')
        ?? obtenerDocumentoVigente($enlace_db, $gcp_id, 'Acta_Compromiso');

    if (!$documento) {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }

        .coaching_visor_documento {
            border: 1px solid #F2F2F2; border-radius: 6px; overflow: hidden;
        }
        .coaching_visor_documento iframe { width: 100%; height: 720px; border: 0; display: block; }

        .coaching_panel_firma { position: sticky; top: 15px; }

        .coaching_confirmacion {
            background: #F1F8F2; border: 1px solid #4CAF50; border-radius: 6px;
            padding: 14px; margin-bottom: 14px; display: flex; align-items: flex-start; gap: 10px;
        }
        .coaching_confirmacion.error { border-color: #FF0000; background: #FDEDED; }

        /* Reset agresivo: el style.css real del sitio pinta los checkboxes
           nativos como una barra rota (probablemente parte de un esquema
           de checkbox "custom" que no estamos usando aquí). "all: revert"
           borra cualquier herencia global antes de aplicar el tamaño fijo,
           así queda a prueba de esa interferencia sin importar de dónde
           venga. */
        .coaching_confirmacion input[type="checkbox"] {
            all: revert !important;
            -webkit-appearance: checkbox !important;
            appearance: checkbox !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: static !important;
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            margin: 2px 0 0 0 !important;
            flex-shrink: 0;
            cursor: pointer;
        }
        .coaching_confirmacion input[type="checkbox"]::before,
        .coaching_confirmacion input[type="checkbox"]::after { content: none !important; display: none !important; }
        .coaching_confirmacion label { font-size: 12px; color: #1A1A1A; margin: 0; cursor: pointer; line-height: 1.5; }

        .coaching_nota_legal { font-size: 10px; color: #6E6E6E; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 5px; background: #F2F2F2; border-radius: 5px; padding: 8px 10px; }

        .coaching_ficha_doc { font-size: 11px; color: #1A1A1A; margin-bottom: 14px; }
        .coaching_ficha_doc .fila { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #F2F2F2; }
        .coaching_ficha_doc .fila:last-child { border-bottom: none; }
        .coaching_ficha_doc .etiqueta { color: #6E6E6E; }

        #btn_firmar[disabled] { opacity: .7; cursor: not-allowed; }

        @media (max-width: 767px) {
            .coaching_panel_firma { position: static; margin-top: 15px; }
        }
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
            <span>Firmar documento</span>
        </nav>

        <div class="mb-3">
            <h4 class="titulo_seccion mb-0">Firma del documento</h4>
            <span class="descripcion-seccion-conocimiento">Paquete <?php echo validar_output($gcp_id); ?> · Versión v<?php echo (int) $documento['gcd_version']; ?></span>
        </div>

        <form method="POST" action="" id="form_firmar">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
            <input type="hidden" name="gcd_id" value="<?php echo (int) $documento['gcd_id']; ?>">

            <div class="row">
                <div class="col-md-8 mb-3">
                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="fas fa-file-pdf"></span> Revise el documento completo antes de firmar</div>
                        <div class="p-2">
                            <div class="coaching_visor_documento">
                                <iframe src="gestion_coaching_documento_descargar.php?reg=<?php echo base64_encode($gcp_id); ?>"></iframe>
                            </div>
                        </div>
                    </div>

                    <?php if ($requiere_encuesta): ?>
                    <div class="cuadro_dash">
                        <div class="cuadro_dash_titulo p-2"><span class="fas fa-poll"></span> Encuesta del espacio</div>
                        <div class="p-3">
                            <p style="font-size:11px; color:#6E6E6E; margin-bottom:12px;">Marque con una X: 1 en desacuerdo — 5 muy de acuerdo.</p>
                            <?php foreach ($preguntas_encuesta as $p): ?>
                                <div class="mb-3">
                                    <label class="coaching_label" style="font-size:12px; font-weight:bold; display:block; margin-bottom:6px;">
                                        <?php echo validar_output($p['gcep_texto']); ?>
                                    </label>
                                    <div style="display:flex; gap:16px;">
                                        <?php for ($v = 1; $v <= 5; $v++): ?>
                                            <label style="font-weight:normal; font-size:12px; display:flex; align-items:center; gap:4px; cursor:pointer;">
                                                <input type="radio" name="encuesta[<?php echo htmlspecialchars($p['gcep_codigo']); ?>]" value="<?php echo $v; ?>" required
                                                    style="width:16px; height:16px; cursor:pointer;">
                                                <?php echo $v; ?>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if (isset($errores_encuesta[$p['gcep_codigo']])): ?>
                                        <div class="coaching_campo_error_texto" style="color:#FF0000; font-size:11px; margin-top:3px;">Requerido.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="coaching_panel_firma">
                        <div class="cuadro_dash">
                            <div class="cuadro_dash_titulo p-2"><span class="fas fa-signature"></span> Aceptación electrónica</div>
                            <div class="p-3">
                                <div class="coaching_ficha_doc">
                                    <div class="fila"><span class="etiqueta">Paquete</span><strong><?php echo validar_output($gcp_id); ?></strong></div>
                                    <div class="fila"><span class="etiqueta">Versión</span><strong>v<?php echo (int) $documento['gcd_version']; ?></strong></div>
                                    <div class="fila"><span class="etiqueta">Fecha</span><strong><?php echo date('d/m/Y'); ?></strong></div>
                                </div>

                                <div class="coaching_confirmacion <?php echo $error_general ? 'error' : ''; ?>">
                                    <input type="checkbox" id="acepto" name="acepto" required>
                                    <label for="acepto">
                                        He leído y entendido el contenido de este documento de coaching. Confirmo mi
                                        aceptación electrónica, con el mismo valor y efecto que mi firma manuscrita
                                        para efectos de este proceso interno.
                                        <?php if ($error_general): ?>
                                            <div style="color:#FF0000; font-size:11px; margin-top:4px;"><?php echo validar_output($error_general); ?></div>
                                        <?php endif; ?>
                                    </label>
                                </div>

                                <div class="coaching_nota_legal">
                                    <span class="fas fa-shield-alt"></span>
                                    <span>Se registrará la fecha, hora, dirección IP y el documento exacto que está firmando, como respaldo de esta aceptación.</span>
                                </div>

                                <button type="submit" name="confirmar_firma" id="btn_firmar" class="btn-corp px-4 py-2 d-block w-100 mb-2" style="border-radius:5px; border:0;">
                                    <span class="fas fa-signature"></span> Firmar documento
                                </button>
                                <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp-2 px-4 py-2 d-block w-100 text-center" style="border-radius:5px;">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        (function () {
            var form = document.getElementById('form_firmar');
            var boton = document.getElementById('btn_firmar');
            form.addEventListener('submit', function (e) {
                if (!confirm('¿Confirma que quiere firmar este documento? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                    return;
                }
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Firmando...';
                }, 0);
            });
        })();
        </script>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
