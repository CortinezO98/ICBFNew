<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");
    require_once("lib/coaching_documentos.php");

    $titulo_header = "Coaching | Generar documento";

    $perfil_coaching = coachingPerfilUsuarioActual();
    if ($perfil_coaching === null || !in_array($perfil_coaching, ['Supervisor', 'Gestor', 'Administrador'], true)) {
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

    // Solo tiene sentido (re)generar el documento en estos 2 estados:
    // RESPONDIDO_AGENTE (la generación automática falló y hay que
    // reintentar) o PENDIENTE_FIRMA_AGENTE (regenerar una versión nueva,
    // p. ej. si se corrigió algo antes de que el agente firme).
    if (!in_array($paquete['gce_codigo'], ['RESPONDIDO_AGENTE', 'PENDIENTE_FIRMA_AGENTE'], true)) {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    $retro = obtenerRetroalimentacion($enlace_db, $gcp_id);
    $compromisos = listarCompromisosPorPaquete($enlace_db, $gcp_id);
    $respuesta_agente = obtenerRespuestaAgente($enlace_db, $gcp_id);

    if (!$retro) {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";

    if (isset($_POST["generar_documento"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } else {
            try {
                $html = construirHtmlDocumentoRetroalimentacion($gcp_id, $paquete, $retro, $compromisos, $respuesta_agente);
                $tipo_documento = $paquete['gct_codigo'] === 'ACTA_COMPROMISO' ? 'Acta_Compromiso' : 'Retroalimentacion';
                generarDocumentoCoaching($enlace_db, $gcp_id, $tipo_documento, $html, $_SESSION['usu_id']);

                // Solo dispara la transición de estado si todavía no se había
                // hecho (si ya estaba en PENDIENTE_FIRMA_AGENTE, esto es una
                // regeneración de versión, no un cambio de estado).
                if ($paquete['gce_codigo'] === 'RESPONDIDO_AGENTE') {
                    ejecutarTransicion($enlace_db, $gcp_id, 'GENERAR_DOCUMENTO', 'SISTEMA', null, 'Generado manualmente por ' . $_SESSION['usu_id']);
                }

                $respuesta_accion = "<script type='text/javascript'>alertify.success('Documento generado correctamente.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
            } catch (Throwable $e) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        #btn_generar[disabled] { opacity: .7; cursor: not-allowed; }
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
            <span>Generar documento</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-file-pdf"></span> Generar documento</div>
                    <div class="p-4 text-center">
                        <?php if (!$respuesta_agente): ?>
                            <p style="font-size:12px; color:#6E6E6E;">
                                El agente todavía no ha registrado su respuesta. El documento se puede generar,
                                pero mostrará esa sección como pendiente.
                            </p>
                        <?php endif; ?>
                        <p style="font-size:12px;">
                            Se generará una nueva versión del documento con la retroalimentación, los compromisos
                            y la respuesta del agente registrados hasta el momento.
                        </p>
                        <form method="POST" action="" id="form_generar">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                            <button type="submit" name="generar_documento" id="btn_generar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                <span class="fas fa-file-pdf"></span> Generar documento
                            </button>
                        </form>
                        <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="d-inline-block mt-3" style="font-size:12px;">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var form = document.getElementById('form_generar');
            var boton = document.getElementById('btn_generar');
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Generando...';
                }, 0);
            });
        })();
        </script>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
