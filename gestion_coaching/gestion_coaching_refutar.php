<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");

    $titulo_header = "Coaching | Refutar";

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
    if (!$paquete || $paquete['gcp_agente_id'] !== $_SESSION['usu_id']) {
        header("Location:../permiso_denegado.php");
        exit;
    }
    if ($paquete['gce_codigo'] !== 'PENDIENTE_AGENTE') {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";
    $error_motivo = null;

    if (isset($_POST["confirmar_refutacion"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        $motivo = trim(validar_input($_POST['motivo_refutacion'] ?? ''));

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } elseif (mb_strlen($motivo) < 20) {
            $error_motivo = 'Explique con al menos 20 caracteres por qué refuta este paquete.';
        } else {
            try {
                ejecutarTransicion($enlace_db, $gcp_id, 'REFUTAR', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, $motivo);
                $respuesta_accion = "<script type='text/javascript'>alertify.success('Refutación registrada. Su supervisor la revisará.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1300);</script>";
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
        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 4px; display: block; color: #1A1A1A; }
        .coaching_campo_error { border: 1px solid #FF0000 !important; }
        .coaching_campo_error_texto { color: #FF0000; font-size: 11px; margin-top: 3px; }
        .coaching_aviso_refutar { background: #FFF8E6; border: 1px solid #F39C12; border-radius: 6px; padding: 12px 14px; font-size: 12px; color: #1A1A1A; margin-bottom: 16px; }
        #btn_refutar[disabled] { opacity: .7; cursor: not-allowed; }
        .coaching_contador { font-size: 10px; color: #6E6E6E; text-align: right; margin-top: 3px; }
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
            <span>Refutar</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-exclamation-circle"></span> Refutar paquete</div>
                    <div class="p-3">
                        <div class="coaching_aviso_refutar">
                            <span class="fas fa-info-circle"></span>
                            Si considera que la retroalimentación no aplica o tiene información incorrecta, puede
                            refutarla explicando el motivo. Su supervisor revisará su explicación y decidirá si
                            reenvía el paquete ajustado o lo cierra aceptando su refutación.
                        </div>

                        <form method="POST" action="" id="form_refutar">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">

                            <label class="coaching_label" for="motivo_refutacion">Motivo de la refutación</label>
                            <textarea name="motivo_refutacion" id="motivo_refutacion" class="form-control <?php echo $error_motivo ? 'coaching_campo_error' : ''; ?>" style="height:120px;" maxlength="1500" required><?php echo isset($_POST['motivo_refutacion']) ? htmlspecialchars($_POST['motivo_refutacion']) : ''; ?></textarea>
                            <div class="coaching_contador" id="contador_motivo">0 / 1500</div>
                            <?php if ($error_motivo): ?><div class="coaching_campo_error_texto"><?php echo $error_motivo; ?></div><?php endif; ?>

                            <div class="text-center mt-4">
                                <button type="submit" name="confirmar_refutacion" id="btn_refutar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                    <span class="fas fa-exclamation-circle"></span> Enviar refutación
                                </button>
                                <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp-2 px-4 py-2 d-inline-block ml-2" style="border-radius:5px;">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var textarea = document.getElementById('motivo_refutacion');
            var contador = document.getElementById('contador_motivo');
            var LIMITE = 1500;
            function actualizarContador() { contador.textContent = textarea.value.length + ' / ' + LIMITE; }
            textarea.addEventListener('input', actualizarContador);
            actualizarContador();

            var form = document.getElementById('form_refutar');
            var boton = document.getElementById('btn_refutar');
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
