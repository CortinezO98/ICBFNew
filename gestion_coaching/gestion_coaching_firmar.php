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

    if (isset($_POST["confirmar_firma"])) {
        // CSRF: mismo mecanismo ya usado en el resto del sistema
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } else {
            $gcd_id = (int) validar_input($_POST['gcd_id']);
            try {
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
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
        echo $respuesta_accion;
    ?>
    <div class="contenido">
        <h4>Firma del documento — Paquete <?php echo validar_output($gcp_id); ?></h4>

        <div class="embed-responsive embed-responsive-16by9 mb-3" style="height:600px;">
            <iframe class="embed-responsive-item" src="gestion_coaching_documento_descargar.php?reg=<?php echo base64_encode($gcp_id); ?>"></iframe>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
            <input type="hidden" name="gcd_id" value="<?php echo (int) $documento['gcd_id']; ?>">

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="acepto" required>
                <label class="form-check-label" for="acepto">
                    He leído y entendido el contenido de este documento de coaching. Confirmo mi aceptación
                    electrónica del mismo, con el mismo valor y efecto que mi firma manuscrita para efectos
                    de este proceso interno.
                </label>
            </div>

            <button type="submit" name="confirmar_firma" class="btn btn-corp">
                <span class="fas fa-signature"></span> Firmar
            </button>
            <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>



