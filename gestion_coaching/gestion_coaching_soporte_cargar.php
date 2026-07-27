<?php
    $modulo_plataforma = 'Coaching';
    require_once('../config/validaciones_seguridad.php');
    require_once('../config/conexion_db.php');
    require_once('lib/coaching_seguridad.php');
    require_once('lib/coaching_complementos.php');

    $perfil = coachingPerfilUsuarioActual();
    $gcp_id = validar_input(base64_decode($_GET['reg'] ?? ''));

    if (!$perfil || !usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil, $gcp_id)) {
        header('Location:../permiso_denegado.php');
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token'])) {
            $mensaje = "<div class='coaching_aviso_error'>Solicitud inválida (CSRF). Recargue e intente de nuevo.</div>";
        } else {
            try {
                guardarSoporteCoaching($enlace_db, $gcp_id, $_FILES['soporte'] ?? [], trim($_POST['tipo_documental'] ?? 'Evidencia'), $_SESSION['usu_id']);
                header('Location:gestion_coaching_ver.php?reg=' . base64_encode($gcp_id));
                exit;
            } catch (Throwable $e) {
                $mensaje = "<div class='coaching_aviso_error'>" . coachingEsc($e->getMessage()) . "</div>";
            }
        }
    }

    $titulo_header = 'Coaching | Adjuntar soporte';
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include('../config/configuracion_estilos.php'); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        .coaching_aviso_error { background: #FDEDED; border: 1px solid #FF0000; color: #FF0000; border-radius: 5px; padding: 10px 12px; font-size: 12px; margin-bottom: 14px; }
        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 4px; display: block; color: #1A1A1A; }
    </style>
</head>
<body>
    <?php include('../menu_principal.php'); include('../menu_header.php'); ?>
    <div class="contenido">
        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>"><?php echo htmlspecialchars($gcp_id); ?></a>
            <span class="mx-1">/</span>
            <span>Adjuntar soporte</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-paperclip"></span> Adjuntar soporte</div>
                    <div class="p-3">
                        <?php echo $mensaje; ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                            <div class="mb-3">
                                <label class="coaching_label" for="tipo_documental">Tipo documental</label>
                                <select class="form-control" name="tipo_documental" id="tipo_documental">
                                    <option>Evidencia</option>
                                    <option>Taller</option>
                                    <option>Compromiso</option>
                                    <option>Seguimiento</option>
                                    <option>Reconocimiento</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="coaching_label" for="soporte">Archivo <span style="font-weight:normal; color:#6E6E6E;">(máximo 10 MB — pdf, doc, docx, xls, xlsx, jpg, png)</span></label>
                                <input class="form-control" type="file" name="soporte" id="soporte" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                    <span class="fas fa-upload"></span> Cargar
                                </button>
                                <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp-2 px-4 py-2 d-inline-block ml-2" style="border-radius:5px;">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('../footer.php'); ?>
</body>
</html>
