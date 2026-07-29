<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_disparador.php");

    $titulo_header = "Coaching | Generar desde monitoreo";

    // Puede entrar aquí cualquiera de los 2 caminos:
    //   a) Tiene acceso al módulo real de Calidad-Monitoreos (el analista
    //      de Calidad que ve el botón desde la bandeja/detalle real).
    //   b) Es Administrador o Supervisor de Coaching — para que puedan
    //      generar paquetes desde monitoreo aunque no tengan asignado el
    //      módulo de Calidad directamente.
    $perfil_coaching = coachingPerfilUsuarioActual();
    $tiene_calidad = isset($_SESSION['modulos_acceso_permisos']['Calidad-Monitoreos'])
        && $_SESSION['modulos_acceso_permisos']['Calidad-Monitoreos'] !== '';
    $es_admin_o_supervisor_coaching = in_array($perfil_coaching, ['Administrador', 'Supervisor', 'Gestor'], true);

    if (!$tiene_calidad && !$es_admin_o_supervisor_coaching) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $gcm_id = validar_input($_GET['monitoreo'] ?? $_POST['monitoreo'] ?? '');
    if ($gcm_id === '') {
        header("Location:../gestion_calidad/gestion_calidad_monitoreo.php");
        exit;
    }

    $snapshot = obtenerSnapshotMonitoreoCalidad($enlace_db, $gcm_id);
    if (!$snapshot) {
        header("Location:../gestion_calidad/gestion_calidad_monitoreo.php");
        exit;
    }

    $elegible = coachingMonitoreoElegibleParaGenerar($enlace_db, $gcm_id);

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    $respuesta_accion = "";

    if (isset($_POST['confirmar_generar'])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);
        $fecha_limite_elegida = validar_input($_POST['fecha_limite'] ?? '');

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } elseif (!$elegible) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Este monitoreo ya no es elegible (nota corregida, o ya existe un paquete).', 0);</script>";
        } else {
            try {
                if ($fecha_limite_elegida !== '' && strtotime($fecha_limite_elegida) < strtotime(date('Y-m-d'))) {
                    throw new RuntimeException('La fecha límite no puede ser anterior a hoy.');
                }
                $gcp_id = crearPaqueteAutomatico($enlace_db, $snapshot, null, $fecha_limite_elegida !== '' ? $fecha_limite_elegida : null);
                $respuesta_accion = "<script type='text/javascript'>alertify.success('Paquete de Coaching " . htmlspecialchars($gcp_id, ENT_QUOTES) . " generado.', 0); setTimeout(function(){ window.location='../gestion_coaching/gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
                $elegible = false;
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
        .coaching_nota_destacada { font-size: 42px; font-weight: bold; color: #FF0000; text-align: center; }
        .coaching_ficha_mini { font-size: 12px; margin-top: 15px; }
        .coaching_ficha_mini .fila { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #F2F2F2; }
        .coaching_ficha_mini .etiqueta { color: #6E6E6E; }
        #btn_generar[disabled] { opacity: .7; cursor: not-allowed; }
        .coaching_aviso_no_elegible { background: #F2F2F2; border: 1px solid #6E6E6E; border-radius: 6px; padding: 12px 14px; font-size: 12px; color: #1A1A1A; text-align: center; }
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
            <a href="../gestion_calidad/gestion_calidad_monitoreo.php">Calidad</a>
            <span class="mx-1">/</span>
            <span>Generar Coaching</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="cuadro_dash">
                    <div class="cuadro_dash_titulo p-2"><span class="fas fa-graduation-cap"></span> Generar paquete de Coaching</div>
                    <div class="p-4">
                        <p style="font-size:12px; color:#6E6E6E; text-align:center;">Monitoreo <strong><?php echo validar_output($gcm_id); ?></strong></p>
                        <div class="coaching_nota_destacada"><?php echo validar_output((string) $snapshot['nota_general']); ?></div>
                        <p style="font-size:11px; color:#6E6E6E; text-align:center;">Nota general: Inferior a 90, amerita seguimiento de Coaching</p>

                        <div class="coaching_ficha_mini">
                            <div class="fila"><span class="etiqueta">Analista</span><strong><?php echo validar_output($snapshot['analista_id']); ?></strong></div>
                            <div class="fila"><span class="etiqueta">Agente evaluado</span><strong><?php echo validar_output($snapshot['agente_id']); ?></strong></div>
                            <div class="fila"><span class="etiqueta">Tipo de monitoreo</span><strong><?php echo validar_output($snapshot['tipo_monitoreo'] ?? '—'); ?></strong></div>
                            <div class="fila"><span class="etiqueta">Skill / Canal</span><strong><?php echo validar_output($snapshot['skill_interaccion'] ?? '—'); ?></strong></div>
                        </div>

                        <?php if ($elegible && !estadoMonitoreoEsTerminal($snapshot['gcm_estado'])): ?>
                            <div class="coaching_aviso_no_elegible mt-3" style="background:#FFF8E6; border-color:#F39C12; text-align:left;">
                                <span class="fas fa-exclamation-triangle" style="color:#F39C12;"></span>
                                Este monitoreo todavía está en estado <strong>"<?php echo validar_output($snapshot['gcm_estado']); ?>"</strong> Calidad
                                aún no lo ha aceptado ni resuelto una refutación. Puede generar el paquete de todos
                                modos, pero tenga en cuenta: si la nota cambia cuando se acepte o resuelva la
                                refutación, el sistema <strong>ajustará o anulará automáticamente</strong> este
                                paquete para que refleje la nota final.
                            </div>
                        <?php endif; ?>

                        <?php if ($elegible): ?>
                            <form method="POST" action="" id="form_generar" class="mt-4 text-left">
                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
                                <input type="hidden" name="monitoreo" value="<?php echo htmlspecialchars($gcm_id); ?>">

                                <label class="coaching_label" for="fecha_limite" style="font-weight:bold; font-size:12px; display:block; margin-bottom:4px;">
                                    Fecha límite <span style="font-weight:normal; color:#6E6E6E; font-size:10px;">(Sugerida: 5 días Editable)</span>
                                </label>
                                <input type="date" name="fecha_limite" id="fecha_limite" class="form-control mb-3"
                                    min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo date('Y-m-d', strtotime('+' . COACHING_DIAS_LIMITE_AUTOMATICO . ' days')); ?>">

                                <div class="text-center">
                                    <button type="submit" name="confirmar_generar" id="btn_generar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0;">
                                        <span class="fas fa-graduation-cap"></span> Generar Coaching
                                    </button>
                                    <a href="../gestion_calidad/gestion_calidad_monitoreo.php" class="btn-corp-2 px-4 py-2 d-inline-block ml-2" style="border-radius:5px;">Cancelar</a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="coaching_aviso_no_elegible mt-4">
                                <span class="fas fa-info-circle"></span>
                                Este monitoreo ya no es elegible para generar Coaching (la nota fue corregida a 90 o más, o ya existe un paquete vigente para este monitoreo).
                            </div>
                            <div class="text-center mt-3">
                                <a href="../gestion_calidad/gestion_calidad_monitoreo.php" class="btn-corp-2 px-4 py-2 d-inline-block" style="border-radius:5px;">Volver</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var form = document.getElementById('form_generar');
            if (!form) { return; }
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
