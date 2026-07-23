<?php
    $modulo_plataforma = "Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_transiciones.php");

    $titulo_header = "Coaching | Retroalimentación";

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

    // Solo tiene sentido diligenciar retroalimentación en estos 2 estados.
    if (!in_array($paquete['gce_codigo'], ['ASIGNADO', 'PENDIENTE_SUPERVISOR'], true)) {
        header("Location:gestion_coaching_ver.php?reg=" . base64_encode($gcp_id));
        exit;
    }

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
            $causa_raiz             = validar_input($_POST['causa_raiz']);
            $metodologia             = validar_input($_POST['metodologia']);
            $estrategia_correctiva   = validar_input($_POST['estrategia_correctiva']);
            $fortalezas              = validar_input($_POST['fortalezas']);
            $oportunidades_mejora    = validar_input($_POST['oportunidades_mejora']);
            $observaciones           = validar_input($_POST['observaciones']);

            $metodologias_validas = ['5_porques', 'espina_pescado', 'analisis_causal', 'otra'];

            if ($causa_raiz === '') { $errores_campo['causa_raiz'] = 'Describa la causa raíz.'; }
            if (!in_array($metodologia, $metodologias_validas, true)) { $errores_campo['metodologia'] = 'Seleccione una metodología válida.'; }
            if ($estrategia_correctiva === '') { $errores_campo['estrategia_correctiva'] = 'Describa la estrategia correctiva.'; }

            // Compromisos (arrays paralelos desde el formulario dinámico)
            $compromisos_post = [];
            if (isset($_POST['compromiso_descripcion']) && is_array($_POST['compromiso_descripcion'])) {
                foreach ($_POST['compromiso_descripcion'] as $idx => $descripcion) {
                    $descripcion = validar_input($descripcion);
                    if ($descripcion === '') { continue; }
                    $compromisos_post[] = [
                        'descripcion'   => $descripcion,
                        'que'           => validar_input($_POST['compromiso_que'][$idx] ?? ''),
                        'como'          => validar_input($_POST['compromiso_como'][$idx] ?? ''),
                        'fecha_limite'  => validar_input($_POST['compromiso_fecha_limite'][$idx] ?? ''),
                        'responsable'   => $paquete['gcp_agente_id'],
                    ];
                }
            }
            if (count($compromisos_post) === 0) {
                $errores_campo['compromisos'] = 'Registre al menos un compromiso.';
            }

            if (count($errores_campo) > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('Revise los campos marcados en rojo.', 0);</script>";
            } else {
                try {
                    // 1) Si el paquete aún está en ASIGNADO, primero pasa a PENDIENTE_SUPERVISOR.
                    if ($paquete['gce_codigo'] === 'ASIGNADO') {
                        ejecutarTransicion($enlace_db, $gcp_id, 'INICIAR_RETROALIMENTACION', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, null);
                    }

                    // 2) Guarda retroalimentación + compromisos.
                    guardarRetroalimentacion($enlace_db, $gcp_id, [
                        'causa_raiz'             => $causa_raiz,
                        'metodologia'            => $metodologia,
                        'estrategia_correctiva'  => $estrategia_correctiva,
                        'fortalezas'             => $fortalezas !== '' ? $fortalezas : null,
                        'oportunidades_mejora'   => $oportunidades_mejora !== '' ? $oportunidades_mejora : null,
                        'observaciones'          => $observaciones !== '' ? $observaciones : null,
                    ], $_SESSION['usu_id']);

                    guardarCompromisos($enlace_db, $gcp_id, $compromisos_post, $_SESSION['usu_id']);

                    // 3) Envía al agente.
                    ejecutarTransicion($enlace_db, $gcp_id, 'ENVIAR_A_AGENTE', $_SESSION['usu_id'], $_SERVER['REMOTE_ADDR'] ?? null, null);

                    $respuesta_accion = "<script type='text/javascript'>alertify.success('Retroalimentación enviada al agente.', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
                } catch (Throwable $e) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
                }
            }
        }
    }

    // Recarga el paquete por si la transición ya lo movió (para no re-renderizar en estado obsoleto tras un error a mitad de camino)
    $paquete = obtenerPaqueteConDetalle($enlace_db, $gcp_id);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }
        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 4px; display: block; color: #1A1A1A; }
        label.coaching_label .opcional { font-weight: normal; color: #6E6E6E; font-size: 10px; }
        .coaching_campo_error { border: 1px solid #FF0000 !important; }
        .coaching_campo_error_texto { color: #FF0000; font-size: 11px; margin-top: 3px; }
        .coaching_compromiso_fila { border: 1px solid #D8D8D8; border-radius: 5px; padding: 12px; margin-bottom: 10px; position: relative; }
        .coaching_compromiso_quitar { position: absolute; top: 8px; right: 8px; color: #FF0000; cursor: pointer; font-size: 12px; }
        .coaching_acciones_form { display: flex; justify-content: center; align-items: center; gap: 10px; }
        #btn_guardar[disabled] { opacity: .7; cursor: not-allowed; }
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
            <span>Retroalimentación</span>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="coaching_titulo_centrado text-center mb-3">
                    <h4 class="titulo_seccion mb-0">Retroalimentación — <?php echo validar_output($gcp_id); ?></h4>
                    <span class="descripcion-seccion-conocimiento">
                        Agente: <?php echo validar_output($paquete['agente_nombre'] ?? '—'); ?> ·
                        Tipo: <?php echo validar_output($paquete['gct_nombre']); ?>
                    </span>
                </div>

                <form method="POST" action="" id="form_retro">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="fas fa-search"></span> Análisis de causa</div>
                        <div class="p-3">
                            <label class="coaching_label" for="causa_raiz">Causa raíz</label>
                            <textarea name="causa_raiz" id="causa_raiz" class="form-control <?php echo isset($errores_campo['causa_raiz']) ? 'coaching_campo_error' : ''; ?>" style="height:80px;"><?php echo isset($_POST['causa_raiz']) ? htmlspecialchars($_POST['causa_raiz']) : ''; ?></textarea>
                            <?php if (isset($errores_campo['causa_raiz'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['causa_raiz']; ?></div><?php endif; ?>

                            <div class="mt-3">
                                <label class="coaching_label" for="metodologia">Metodología utilizada</label>
                                <select name="metodologia" id="metodologia" class="form-control <?php echo isset($errores_campo['metodologia']) ? 'coaching_campo_error' : ''; ?>">
                                    <option value="5_porques">5 porqués</option>
                                    <option value="espina_pescado">Espina de pescado</option>
                                    <option value="analisis_causal">Análisis causal</option>
                                    <option value="otra">Otra</option>
                                </select>
                                <?php if (isset($errores_campo['metodologia'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['metodologia']; ?></div><?php endif; ?>
                            </div>

                            <div class="mt-3">
                                <label class="coaching_label" for="estrategia_correctiva">Estrategia correctiva</label>
                                <textarea name="estrategia_correctiva" id="estrategia_correctiva" class="form-control <?php echo isset($errores_campo['estrategia_correctiva']) ? 'coaching_campo_error' : ''; ?>" style="height:80px;"><?php echo isset($_POST['estrategia_correctiva']) ? htmlspecialchars($_POST['estrategia_correctiva']) : ''; ?></textarea>
                                <?php if (isset($errores_campo['estrategia_correctiva'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['estrategia_correctiva']; ?></div><?php endif; ?>
                            </div>

                            <div class="mt-3">
                                <label class="coaching_label" for="fortalezas">Fortalezas identificadas <span class="opcional">(opcional)</span></label>
                                <textarea name="fortalezas" id="fortalezas" class="form-control" style="height:60px;"><?php echo isset($_POST['fortalezas']) ? htmlspecialchars($_POST['fortalezas']) : ''; ?></textarea>
                            </div>

                            <div class="mt-3">
                                <label class="coaching_label" for="oportunidades_mejora">Oportunidades de mejora <span class="opcional">(opcional)</span></label>
                                <textarea name="oportunidades_mejora" id="oportunidades_mejora" class="form-control" style="height:60px;"><?php echo isset($_POST['oportunidades_mejora']) ? htmlspecialchars($_POST['oportunidades_mejora']) : ''; ?></textarea>
                            </div>

                            <div class="mt-3">
                                <label class="coaching_label" for="observaciones">Observaciones <span class="opcional">(opcional)</span></label>
                                <textarea name="observaciones" id="observaciones" class="form-control" style="height:60px;"><?php echo isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2 d-flex justify-content-between align-items-center">
                            <span><span class="fas fa-handshake"></span> Compromisos</span>
                            <button type="button" id="btn_agregar_compromiso" class="btn-corp-2" style="border-radius:5px; padding:2px 10px; font-size:11px; border-color:#FFFFFF; color:#FFFFFF;">
                                <span class="fas fa-plus"></span> Agregar
                            </button>
                        </div>
                        <div class="p-3" id="contenedor_compromisos">
                            <?php if (isset($errores_campo['compromisos'])): ?><div class="coaching_campo_error_texto mb-2"><?php echo $errores_campo['compromisos']; ?></div><?php endif; ?>
                            <!-- Al menos una fila inicial -->
                            <div class="coaching_compromiso_fila">
                                <label class="coaching_label">Descripción del compromiso</label>
                                <textarea name="compromiso_descripcion[]" class="form-control mb-2" style="height:50px;" placeholder="¿Qué se compromete a hacer el agente?"></textarea>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="coaching_label">Qué</label>
                                        <input type="text" name="compromiso_que[]" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="coaching_label">Cómo</label>
                                        <input type="text" name="compromiso_como[]" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="coaching_label">Fecha límite</label>
                                        <input type="date" name="compromiso_fecha_limite[]" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <div class="coaching_acciones_form">
                            <button type="submit" name="guardar_registro" id="btn_guardar" class="btn-corp px-4 py-2" style="border-radius:5px; border:0; min-width:220px;">
                                <span class="fas fa-paper-plane"></span> Guardar y enviar al agente
                            </button>
                            <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($gcp_id); ?>" class="btn-corp-2 px-4 py-2 d-inline-block" style="border-radius:5px;">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
        (function () {
            var plantilla = document.querySelector('.coaching_compromiso_fila').cloneNode(true);
            document.getElementById('btn_agregar_compromiso').addEventListener('click', function () {
                var nueva = plantilla.cloneNode(true);
                nueva.querySelectorAll('input, textarea').forEach(function (campo) { campo.value = ''; });
                var quitar = document.createElement('span');
                quitar.className = 'coaching_compromiso_quitar';
                quitar.innerHTML = '<span class="fas fa-times-circle"></span>';
                quitar.addEventListener('click', function () { nueva.remove(); });
                nueva.appendChild(quitar);
                document.getElementById('contenedor_compromisos').appendChild(nueva);
            });

            var form = document.getElementById('form_retro');
            var boton = document.getElementById('btn_guardar');
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



