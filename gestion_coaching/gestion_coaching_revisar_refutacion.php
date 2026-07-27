<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");

    $titulo_header = "Coaching | Revisar refutación";

    $perfil_coaching = coachingPerfilUsuarioActual();
    if ($perfil_coaching === null || $perfil_coaching !== 'Supervisor') {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $gcp_id = validar_input(base64_decode($_GET['reg'] ?? $_POST['reg'] ?? ''));

    if (!usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil_coaching, $gcp_id)) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $paquete = obtenerPaqueteConDetalle($enlace_db, $gcp_id);
    if (!$paquete || $paquete['gcp_supervisor_id'] !== $_SESSION['usu_id']) {
        header("Location:../permiso_denegado.php");
        exit;
    }
    if ($paquete['gce_codigo'] !== 'REFUTADO') {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    // Motivo de la refutación: viene del último registro de historial con esa acción.
    $consulta_motivo = $enlace_db->prepare(
        "SELECT `gch2_comentario`, `gch2_registro_fecha` FROM `tb_gestion_coaching_historial`
         WHERE `gch2_paquete` = ? AND `gch2_accion` = 'REFUTAR' ORDER BY `gch2_registro_fecha` DESC LIMIT 1"
    );
    $consulta_motivo->bind_param('s', $gcp_id);
    $consulta_motivo->execute();
    $motivo_refutacion = $consulta_motivo->get_result()->fetch_assoc();

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";
    $error_motivo = null;

    if (isset($_POST["decision"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);
        $decision = validar_input($_POST['decision']);
        $comentario_supervisor = trim(validar_input($_POST['comentario_supervisor'] ?? ''));

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } elseif (mb_strlen($comentario_supervisor) < 10) {
            $error_motivo = 'Explique brevemente su decisión (mínimo 10 caracteres).';
        } elseif (!in_array($decision, ['REVISAR_REFUTACION_REENVIAR', 'REVISAR_REFUTACION_ACEPTAR'], true)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Decisión no válida.', 0);</script>";
        } else {
            try {
                ejecutarTransicion($enlace_db, $gcp_id, $decision, $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, $comentario_supervisor);
                $mensaje = $decision === 'REVISAR_REFUTACION_REENVIAR'
                    ? 'Paquete reenviado al agente.'
                    : 'Refutación aceptada — paquete cerrado.';
                $respuesta_accion = "<script type='text/javascript'>alertify.success('" . addslashes($mensaje) . "', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1300);</script>";
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
        .coaching_motivo_agente { background: #FDEDED; border: 1px solid #FF0000; border-radius: 6px; padding: 12px 14px; font-size: 12px; color: #1A1A1A; margin-bottom: 16px; white-space: pre-line; }
        .coaching_decision_opcion { border: 1px solid #F2F2F2; border-radius: 6px; padding: 12px; margin-bottom: 10px; cursor: pointer; }
        .coaching_decision_opcion:hover { border-color: #4CAF50; }
        .coaching_decision_opcion input { margin-right: 8px; }
        #btn_decidir[disabled] { opacity: .7; cursor: not-allowed; }
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
            <span>Revisar refutación</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-exclamation-circle"></span> Refutación del agente</div>
                    <div class="p-3">
                        <div class="coaching_motivo_agente">
                            <strong>Motivo indicado por el agente:</strong><br>
                            <?php echo nl2br(validar_output($motivo_refutacion['gch2_comentario'] ?? '(sin motivo registrado)')); ?>
                        </div>

                        <form method="POST" action="" id="form_decision">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                            <input type="hidden" name="reg" value="<?php echo base64_encode($gcp_id); ?>">

                            <label class="coaching_decision_opcion">
                                <input type="radio" name="decision" value="REVISAR_REFUTACION_REENVIAR" required>
                                <strong>Reenviar al agente</strong> — no estoy de acuerdo con la refutación, ajusto y reenvío.
                            </label>
                            <label class="coaching_decision_opcion">
                                <input type="radio" name="decision" value="REVISAR_REFUTACION_ACEPTAR" required>
                                <strong>Aceptar refutación y cerrar</strong> — el agente tiene razón, se cierra el paquete.
                            </label>

                            <label class="coaching_label mt-3" for="comentario_supervisor">Comentario de su decisión</label>
                            <textarea name="comentario_supervisor" id="comentario_supervisor" class="form-control <?php echo $error_motivo ? 'coaching_campo_error' : ''; ?>" style="height:80px;" maxlength="1000" required></textarea>
                            <?php if ($error_motivo): ?><div class="coaching_campo_error_texto"><?php echo $error_motivo; ?></div><?php endif; ?>

                            <div class="text-center mt-4">
                                <button type="submit" name="decision_enviar" id="btn_decidir" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                    <span class="fas fa-check"></span> Confirmar decisión
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
            var form = document.getElementById('form_decision');
            var boton = document.getElementById('btn_decidir');
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Guardando...';
                }, 0);
            });
        })();
        </script>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
