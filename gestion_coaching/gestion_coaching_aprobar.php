<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");
    require_once("lib/coaching_reglas.php");

    $titulo_header = "Coaching | Cerrar paquete";

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

    if (!in_array($paquete['gce_codigo'], ['PENDIENTE_CIERRE', 'EN_SEGUIMIENTO'], true)) {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";

    // --- Actualizar estado de un compromiso individual (independiente del cierre) ---
    if (isset($_POST["actualizar_compromiso"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } else {
            $gccm_id = (int) validar_input($_POST['gccm_id']);
            $nuevo_estado = validar_input($_POST['nuevo_estado']);
            $resultado = validar_input($_POST['resultado']);

            try {
                $ok = actualizarEstadoCompromiso($enlace_db, $gccm_id, $gcp_id, $nuevo_estado, $resultado !== '' ? $resultado : null, $_SESSION['usu_id']);
                if ($ok) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('Compromiso actualizado.', 0); setTimeout(function(){ window.location='gestion_coaching_aprobar.php?reg=" . base64_encode($gcp_id) . "'; }, 900);</script>";
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('No fue posible actualizar el compromiso.', 0);</script>";
                }
            } catch (Throwable $e) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
            }
        }
    }

    // --- Acción de cierre / seguimiento ---
    if (isset($_POST["accion"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } else {
            $accion = validar_input($_POST['accion']);
            $comentario = validar_input($_POST['comentario']);

            // Recarga compromisos justo antes de decidir, para no usar datos
            // obsoletos si se acaban de marcar cumplidos en este mismo request.
            $compromisos_actuales = listarCompromisosPorPaquete($enlace_db, $gcp_id);
            $pendientes_actuales = array_filter($compromisos_actuales, fn($c) => $c['gccm_estado'] === 'Pendiente');

            try {
                if ($accion === 'cerrar' && $paquete['gce_codigo'] === 'PENDIENTE_CIERRE') {
                    if (count($pendientes_actuales) > 0) {
                        throw new RuntimeException('No se puede cerrar: existen compromisos pendientes. Márquelos como cumplidos o envíe el paquete a seguimiento.');
                    }
                    ejecutarTransicion($enlace_db, $gcp_id, 'CERRAR', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, $comentario !== '' ? $comentario : null);
                    $mensaje = 'Paquete cerrado correctamente.';
                } elseif ($accion === 'seguimiento' && $paquete['gce_codigo'] === 'PENDIENTE_CIERRE') {
                    ejecutarTransicion($enlace_db, $gcp_id, 'REQUIERE_SEGUIMIENTO', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, $comentario !== '' ? $comentario : null);
                    $mensaje = 'Paquete enviado a seguimiento.';
                } elseif ($accion === 'cerrar_seguimiento' && $paquete['gce_codigo'] === 'EN_SEGUIMIENTO') {
                    ejecutarTransicion($enlace_db, $gcp_id, 'CERRAR_TRAS_SEGUIMIENTO', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, $comentario !== '' ? $comentario : null);
                    $mensaje = 'Paquete cerrado tras seguimiento.';
                } else {
                    throw new RuntimeException('Acción no válida para el estado actual del paquete.');
                }

                $respuesta_accion = "<script type='text/javascript'>alertify.success('" . addslashes($mensaje) . "', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
            } catch (Throwable $e) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
            }
        }
    }

    // Se carga (o recarga) al final, para reflejar cualquier actualización de compromiso recién hecha.
    $compromisos = listarCompromisosPorPaquete($enlace_db, $gcp_id);
    $compromisos_pendientes = array_filter($compromisos, fn($c) => $c['gccm_estado'] === 'Pendiente');
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 4px; display: block; color: #1A1A1A; }
        .coaching_compromiso_item { padding: 10px; border-bottom: 1px solid #F0F0F0; font-size: 12px; }
        .coaching_estado_naranja { color: #F39C12; border: solid 1px #F39C12; background-color: rgba(243, 156, 18, 0.15); }
        .coaching_estado_verde { color: #00BF6F; border: solid 1px #00BF6F; background-color: rgba(40, 180, 99, 0.15); }
        .coaching_estado_rojo { color: #FF0000; border: solid 1px #FF0000; background-color: rgba(255, 0, 0, 0.15); }
        .coaching_estado_pill { display: inline-block; font-size: 11px; padding: 2px 10px; border-radius: 10px; }
        .coaching_compromiso_acciones { margin-top: 8px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .coaching_compromiso_acciones input[type="text"] { flex: 1; min-width: 160px; font-size: 11px; padding: 4px 6px; height: auto !important; border: 1px solid #D8D8D8; border-radius: 4px; }
        .btn-mini { font-size: 11px; padding: 3px 10px; border-radius: 4px; border: 0; cursor: pointer; }
        .btn-mini-verde { background: #00BF6F; color: #fff; }
        .btn-mini-rojo { background: #FF0000; color: #fff; }
        button[disabled] { opacity: .7; cursor: not-allowed; }
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
            <span>Cerrar</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="text-center mb-3">
                    <h4 class="titulo_seccion mb-0">Cerrar paquete — <?php echo validar_output($gcp_id); ?></h4>
                </div>

                <?php if (count($compromisos) > 0): ?>
                <div class="cuadro_dash mb-3">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-handshake"></span> Compromisos</div>
                    <div>
                        <?php foreach ($compromisos as $c):
                            $clase_estado = $c['gccm_estado'] === 'Pendiente' ? 'coaching_estado_naranja' : ($c['gccm_estado'] === 'Cumplido' ? 'coaching_estado_verde' : 'coaching_estado_rojo');
                        ?>
                            <div class="coaching_compromiso_item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span><?php echo validar_output($c['gccm_descripcion']); ?></span>
                                    <span class="coaching_estado_pill <?php echo $clase_estado; ?>"><?php echo validar_output($c['gccm_estado']); ?></span>
                                </div>
                                <?php if ($c['gccm_estado'] === 'Pendiente'): ?>
                                <form method="POST" action="" class="coaching_compromiso_acciones">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                                    <input type="hidden" name="gccm_id" value="<?php echo (int) $c['gccm_id']; ?>">
                                    <input type="text" name="resultado" placeholder="Resultado / observación (opcional)" maxlength="200">
                                    <button type="submit" name="actualizar_compromiso" class="btn-mini btn-mini-verde" onclick="this.form.nuevo_estado.value='Cumplido';">
                                        <span class="fas fa-check"></span> Cumplido
                                    </button>
                                    <button type="submit" name="actualizar_compromiso" class="btn-mini btn-mini-rojo" onclick="this.form.nuevo_estado.value='No cumplido';">
                                        <span class="fas fa-times"></span> No cumplido
                                    </button>
                                    <input type="hidden" name="nuevo_estado" value="">
                                </form>
                                <?php elseif (!empty($c['gccm_resultado'])): ?>
                                    <div class="descripcion-seccion-conocimiento-detalle mt-1"><?php echo validar_output($c['gccm_resultado']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-check-circle"></span> Acción</div>
                    <div class="p-3">
                        <form method="POST" action="" id="form_cerrar">
                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                            <input type="hidden" name="accion" id="accion_valor" value="">

                            <label class="coaching_label" for="comentario">Comentario <span style="font-weight:normal;color:#6E6E6E;font-size:10px;">(opcional, obligatorio si envía a seguimiento)</span></label>
                            <textarea name="comentario" id="comentario" class="form-control mb-3" style="height:70px;"></textarea>

                            <?php if ($paquete['gce_codigo'] === 'PENDIENTE_CIERRE'): ?>
                                <?php if (count($compromisos_pendientes) > 0): ?>
                                    <div class="mensaje_error_2 mb-3" style="text-align:left;">
                                        Hay <?php echo count($compromisos_pendientes); ?> compromiso(s) pendiente(s) — márquelos arriba como
                                        "Cumplido" o "No cumplido" para poder cerrar, o envíe el paquete a seguimiento.
                                    </div>
                                    <button type="submit" class="btn-corp-2 px-4 py-2" style="border-radius:5px;" onclick="document.getElementById('accion_valor').value='seguimiento';">
                                        <span class="fas fa-eye"></span> Enviar a seguimiento
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn-corp px-4 py-2 mr-2" style="border-radius:5px; border:0;" onclick="document.getElementById('accion_valor').value='cerrar';">
                                        <span class="fas fa-check-circle"></span> Cerrar paquete
                                    </button>
                                    <button type="submit" class="btn-corp-2 px-4 py-2" style="border-radius:5px;" onclick="document.getElementById('accion_valor').value='seguimiento';">
                                        <span class="fas fa-eye"></span> Enviar a seguimiento
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button type="submit" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;" onclick="document.getElementById('accion_valor').value='cerrar_seguimiento';">
                                    <span class="fas fa-check-circle"></span> Cerrar tras seguimiento
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var form = document.getElementById('form_cerrar');
            form.addEventListener('submit', function (e) {
                if (document.getElementById('accion_valor').value === 'seguimiento' && document.getElementById('comentario').value.trim() === '') {
                    e.preventDefault();
                    alertify.warning('El comentario es obligatorio para enviar a seguimiento.', 0);
                }
            });
        })();
        </script>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
