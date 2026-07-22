<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");

    $titulo_header = "Coaching | Acuse de recibido";

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

    // El agente autenticado debe ser exactamente el asignado.
    if ($paquete['gcp_agente_id'] !== $_SESSION['usu_id']) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    // Esta pantalla es SOLO para tipos que no requieren respuesta ni firma
    // (Felicitación/Reconocimiento). Si el tipo sí las requiere, se manda
    // al flujo normal — nunca se debe poder "saltar" la respuesta real.
    if ($paquete['gce_codigo'] !== 'PENDIENTE_AGENTE') {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }
    if ((int) $paquete['gct_requiere_respuesta_agente'] === 1) {
        header("Location:gestion_coaching_responder_agente.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";

    if (isset($_POST["acusar_recibido"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } else {
            try {
                ejecutarTransicion($enlace_db, $gcp_id, 'ACUSAR_RECIBIDO', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, null);
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Gracias! Acuse de recibido registrado.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
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
        .coaching_reconocimiento_icono { text-align: center; padding: 20px 0 10px; }
        .coaching_reconocimiento_icono .fas { font-size: 48px; color: #F39C12; }
        #btn_acusar[disabled] { opacity: .7; cursor: not-allowed; }
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
            <span>Acuse de recibido</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-award"></span> <?php echo validar_output($paquete['gct_nombre']); ?></div>
                    <div class="p-4 text-center">
                        <div class="coaching_reconocimiento_icono">
                            <span class="fas fa-award"></span>
                        </div>
                        <h5 class="mb-3">¡Felicitaciones!</h5>
                        <p style="font-size:13px; color:#333;">
                            Su supervisor le ha registrado un reconocimiento de tipo
                            <strong><?php echo validar_output($paquete['gct_nombre']); ?></strong>.
                            Confirme que lo recibió para cerrar el proceso.
                        </p>

                        <form method="POST" action="" id="form_acuse">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                            <button type="submit" name="acusar_recibido" id="btn_acusar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                <span class="fas fa-check-circle"></span> Acuso recibido
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var form = document.getElementById('form_acuse');
            var boton = document.getElementById('btn_acusar');
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Registrando...';
                }, 0);
            });
        })();
        </script>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
