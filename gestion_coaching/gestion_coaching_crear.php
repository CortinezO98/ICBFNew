<?php
    $modulo_plataforma="Coaching-Crear-Global";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");

    $titulo_header = "Coaching | Nuevo paquete";

    $perfil_coaching = coachingPerfilUsuarioActual();
    // Solo Supervisor/Gestor/Administrador pueden crear paquetes globales.
    if ($perfil_coaching === null || !in_array($perfil_coaching, ['Supervisor', 'Gestor', 'Administrador'], true)) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    $respuesta_accion = "";
    $errores_campo = [];

    // Tipos que requieren el bloque de escalamiento (asunto/fecha/destinatario).
    $tipos_con_escalamiento = ['ESCALAMIENTO_DISCIPLINARIO'];

    if (isset($_POST["guardar_registro"])) {
        $csrf_ok = isset($_POST['_csrf_token'], $_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);

        if (!$csrf_ok) {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('Solicitud inválida (CSRF). Recargue e intente de nuevo.', 0);</script>";
        } elseif (!isset($_SESSION['registro_creado']) || $_SESSION['registro_creado'] != 1) {
            $agente_id      = validar_input($_POST['agente_id']);
            $tipo_codigo    = validar_input($_POST['tipo_codigo']);
            $indicador_ids  = array_map('intval', $_POST['indicadores'] ?? []);
            $prioridad      = validar_input($_POST['prioridad']);
            $fecha_limite   = validar_input($_POST['fecha_limite']);
            $contexto       = validar_input($_POST['contexto']);
            $segmento       = validar_input($_POST['segmento']);

            $tipos_validos = ['RETROALIMENTACION', 'ACTA_COMPROMISO', 'FELICITACION', 'RECONOCIMIENTO', 'LLAMADO_VERBAL', 'ESCALAMIENTO_DISCIPLINARIO', 'NO_RENOVACION'];
            $prioridades_validas = ['Baja', 'Normal', 'Alta', 'Urgente'];

            if ($agente_id === '') { $errores_campo['agente_id'] = 'Seleccione un agente.'; }
            if (!in_array($tipo_codigo, $tipos_validos, true)) { $errores_campo['tipo_codigo'] = 'Seleccione un tipo de paquete válido.'; }
            if (count($indicador_ids) === 0) { $errores_campo['indicadores'] = 'Seleccione al menos un indicador.'; }
            if (!in_array($prioridad, $prioridades_validas, true)) { $errores_campo['prioridad'] = 'Seleccione una prioridad válida.'; }

            $es_escalamiento = in_array($tipo_codigo, $tipos_con_escalamiento, true);
            $escalamiento_datos = null;
            if ($es_escalamiento) {
                $escalamiento_datos = [
                    'asunto'              => validar_input($_POST['escalamiento_asunto'] ?? ''),
                    'fecha_hora_envio'    => validar_input($_POST['escalamiento_fecha'] ?? ''),
                    'destinatario_nombre' => validar_input($_POST['escalamiento_destinatario'] ?? ''),
                    'destinatario_correo' => validar_input($_POST['escalamiento_correo'] ?? ''),
                    'observaciones'       => validar_input($_POST['escalamiento_observaciones'] ?? ''),
                ];
                if ($escalamiento_datos['asunto'] === '') { $errores_campo['escalamiento_asunto'] = 'Requerido.'; }
                if ($escalamiento_datos['fecha_hora_envio'] === '') { $errores_campo['escalamiento_fecha'] = 'Requerido.'; }
                if ($escalamiento_datos['destinatario_nombre'] === '') { $errores_campo['escalamiento_destinatario'] = 'Requerido.'; }
                if (!filter_var($escalamiento_datos['destinatario_correo'], FILTER_VALIDATE_EMAIL)) { $errores_campo['escalamiento_correo'] = 'Correo no válido.'; }
            }

            if (count($errores_campo) > 0) {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('Revise los campos marcados en rojo.', 0);</script>";
            } else {
                try {
                    $gcp_id = crearPaqueteGlobal($enlace_db, [
                        'agente_id'    => $agente_id,
                        'tipo_codigo'  => $tipo_codigo,
                        'indicador_id' => $indicador_ids[0],
                        'prioridad'    => $prioridad,
                        'fecha_limite' => $fecha_limite !== '' ? $fecha_limite : null,
                        'contexto'     => $contexto !== '' ? $contexto : null,
                        'segmento'     => $segmento !== '' ? $segmento : null,
                    ], $_SESSION['usu_id']);

                    guardarIndicadoresPaquete($enlace_db, $gcp_id, $indicador_ids);

                    if ($es_escalamiento) {
                        guardarEscalamiento($enlace_db, $gcp_id, $escalamiento_datos, $_SESSION['usu_id']);
                    }

                    $_SESSION['registro_creado'] = 1;
                    $gcp_id_seguro = htmlspecialchars($gcp_id, ENT_QUOTES);
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Paquete " . $gcp_id_seguro . " creado exitosamente!', 0); setTimeout(function(){ window.location='gestion_coaching_ver.php?reg=" . base64_encode($gcp_id) . "'; }, 1200);</script>";
                } catch (Throwable $e) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('" . addslashes($e->getMessage()) . "', 0);</script>";
                }
            }
        }
    } else {
        unset($_SESSION['registro_creado']);
    }

    $agentes = listarAgentesDeSupervisor($enlace_db, $_SESSION['usu_id']);
    $tipos = listarTiposActivos($enlace_db);
    $indicadores = listarIndicadoresActivos($enlace_db);
    $indicadores_por_categoria = [];
    foreach ($indicadores as $i) {
        $cat = $i['gci_categoria'] ?? 'Otros';
        $indicadores_por_categoria[$cat][] = $i;
    }

    $mapa_segmentos_agentes = [];
    foreach ($agentes as $a) {
        $cs = obtenerCampaniaYSegmentoRecienteAgente($enlace_db, $a['usu_id']);
        $mapa_segmentos_agentes[$a['usu_id']] = $cs['segmento'] ?? '';
    }

    $segmentos_conocidos = [
        'Correos y Portales', 'Línea 141', 'WhatsApp', 'Chat ICBF', 'Línea Nacional',
        'Profesional ES Psicólogos', 'Video Llamada', 'Redes Sociales', 'Canal escrito',
        'Aseguramiento', 'Profesional Es Abogados', 'Encuestas', 'Presencial',
        'Apoyo Adopciones', 'Click to Call', 'Mi Familia',
    ];

    $ayuda_tipos = [
        'RETROALIMENTACION'          => 'Oportunidad de mejora puntual. Requiere respuesta y firma del agente.',
        'ACTA_COMPROMISO'            => 'Compromiso formal frente a incumplimiento o reincidencia. Requiere respuesta y firma.',
        'FELICITACION'               => 'Reconoce un resultado destacado. No requiere respuesta ni firma.',
        'RECONOCIMIENTO'             => 'Reconocimiento institucional. No requiere respuesta ni firma.',
        'LLAMADO_VERBAL'             => 'Notificación verbal formalizada. Requiere respuesta y firma del agente.',
        'ESCALAMIENTO_DISCIPLINARIO' => 'Requiere completar los datos exactos del correo con el que se escaló el caso.',
        'NO_RENOVACION'              => 'Decisión de no renovación / no aprobación de periodo de prueba. Requiere respuesta y firma.',
    ];
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_breadcrumb { font-size: 11px; color: #6E6E6E; margin-bottom: 10px; }
        .coaching_breadcrumb a { color: #4CAF50; }

        .coaching_ayuda {
            background: #F2F2F2; border-left: 3px solid #4CAF50; border-radius: 3px;
            padding: 8px 10px; font-size: 11px; color: #1A1A1A; margin-top: 8px; display: none;
        }
        .coaching_ayuda.activa { display: block; }
        .coaching_contador { font-size: 10px; color: #6E6E6E; text-align: right; margin-top: 3px; }
        .coaching_contador.limite { color: #FF0000; }

        label.coaching_label { font-weight: bold; font-size: 12px; margin-bottom: 4px; display: block; color: #1A1A1A; }
        label.coaching_label .opcional { font-weight: normal; color: #6E6E6E; font-size: 10px; }

        .coaching_empty { text-align: center; padding: 60px 30px; background: #FFFFFF; border: 1px solid #F2F2F2; border-radius: 8px; max-width: 480px; margin: 40px auto 0; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .coaching_empty .coaching_empty_icono { width: 64px; height: 64px; border-radius: 50%; background: #F2F2F2; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; }
        .coaching_empty .coaching_empty_icono .fas { font-size: 26px; color: #6E6E6E; }
        .coaching_empty h5 { font-size: 15px; font-weight: bold; color: #1A1A1A; margin-bottom: 8px; }
        .coaching_empty p { font-size: 12px; color: #6E6E6E; line-height: 1.6; }
        .coaching_empty code { background: #F2F2F2; color: #FF0000; padding: 1px 5px; border-radius: 3px; font-size: 11px; }
        .coaching_empty_acciones { display: flex; gap: 10px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }

        .coaching_campo_error { border: 1px solid #FF0000 !important; }
        .coaching_campo_error_texto { color: #FF0000; font-size: 11px; margin-top: 3px; }

        #btn_crear_paquete[disabled] { opacity: .7; cursor: not-allowed; }
        select.form-control, input.form-control, textarea.form-control { font-size: 12px; }

        .coaching_paso_numero {
            width: 22px; height: 22px; border-radius: 50%; background: #FFFFFF; color: #4CAF50;
            display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; margin-right: 6px; flex-shrink: 0;
        }

        .coaching_indicador_caja { max-height: 260px; overflow-y: auto; border: 1px solid #F2F2F2; border-radius: 5px; padding: 10px; }
        .coaching_indicador_categoria { font-weight: bold; color: #4CAF50; font-size: 11px; margin-top: 10px; text-transform: uppercase; }
        .coaching_indicador_categoria:first-child { margin-top: 0; }
        .coaching_indicador_item { font-size: 12px; padding: 3px 0; }
        .coaching_indicador_contador { font-size: 11px; color: #6E6E6E; margin-top: 6px; }

        .coaching_bloque_escalamiento { display: none; background: #FFF8E6; border: 1px solid #F39C12; border-radius: 5px; padding: 12px; margin-top: 14px; }
        .coaching_bloque_escalamiento.activo { display: block; }
    </style>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <?php if (!empty($respuesta_accion)) { echo $respuesta_accion; } ?>

        <nav class="coaching_breadcrumb">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span class="mx-1">/</span>
            <span>Nuevo paquete</span>
        </nav>

        <?php if (count($agentes) === 0): ?>
            <div class="coaching_empty">
                <div class="coaching_empty_icono"><span class="fas fa-user-friends"></span></div>
                <h5>Todavía no tiene agentes asignados</h5>
                <p class="mb-0">
                    Ningún usuario tiene su cuenta como supervisor (<code>usu_supervisor</code>) todavía,
                    por lo que no hay a quién asignarle un paquete de coaching.
                    Verifique la asignación desde <strong>Administrador → Usuarios</strong>.
                </p>
                <div class="coaching_empty_acciones">
                    <?php $tiene_acceso_admin = isset($_SESSION['modulos_acceso_permisos']['Administrador']) && $_SESSION['modulos_acceso_permisos']['Administrador'] !== ''; ?>
                    <?php if ($tiene_acceso_admin): ?>
                        <a href="../administrador/gestion_usuarios.php?pagina=1&id=null" class="btn-corp px-3 py-2" style="border-radius:5px;">
                            <span class="fas fa-users-cog"></span> Ir a Usuarios
                        </a>
                    <?php endif; ?>
                    <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes" class="<?php echo $tiene_acceso_admin ? 'btn-corp-2' : 'btn-corp'; ?> px-3 py-2" style="border-radius:5px;">
                        <span class="fas fa-arrow-left"></span> Volver a la bandeja
                    </a>
                </div>
            </div>
        <?php else: ?>

        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="text-center mb-3">
                    <h4 class="titulo_seccion mb-0">Nuevo paquete de Coaching</h4>
                    <span class="descripcion-seccion-conocimiento">Acompañamiento a un agente de su equipo — todos los tipos disponibles en un solo formulario.</span>
                </div>

                <form method="POST" action="" id="form_coaching_crear" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="coaching_paso_numero">1</span> Datos de seguimiento</div>
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="coaching_label" for="agente_id">Colaborador intervenido</label>
                                    <select name="agente_id" id="agente_id" class="form-control <?php echo isset($errores_campo['agente_id']) ? 'coaching_campo_error' : ''; ?>" required>
                                        <option value="">Seleccione un agente de su equipo...</option>
                                        <?php foreach ($agentes as $a): ?>
                                            <option value="<?php echo htmlspecialchars($a['usu_id']); ?>" <?php echo (isset($_POST['agente_id']) && $_POST['agente_id'] === $a['usu_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($a['usu_nombres_apellidos']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errores_campo['agente_id'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['agente_id']; ?></div><?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="coaching_label" for="segmento">Segmento <span class="opcional">(sugerido, editable)</span></label>
                                    <input type="text" name="segmento" id="segmento" class="form-control" list="segmentos_lista"
                                        value="<?php echo isset($_POST['segmento']) ? htmlspecialchars($_POST['segmento']) : ''; ?>"
                                        placeholder="Seleccione un agente para sugerir el segmento...">
                                    <datalist id="segmentos_lista">
                                        <?php foreach ($segmentos_conocidos as $s): ?><option value="<?php echo htmlspecialchars($s); ?>"></option><?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            <p class="descripcion-seccion-conocimiento mt-2 mb-0">
                                <span class="fas fa-info-circle"></span> La empresa (ASD/IQ/Comware/Happy) y el líder se toman automáticamente de su cuenta y la del agente.
                            </p>
                        </div>
                    </div>

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="coaching_paso_numero">2</span> Indicadores objeto de seguimiento</div>
                        <div class="p-3">
                            <p class="descripcion-seccion-conocimiento mb-2">Puede seleccionar uno o varios indicadores.</p>
                            <div class="coaching_indicador_caja <?php echo isset($errores_campo['indicadores']) ? 'coaching_campo_error' : ''; ?>">
                                <?php $seleccionados = array_map('strval', $_POST['indicadores'] ?? []); ?>
                                <?php foreach ($indicadores_por_categoria as $categoria => $items): ?>
                                    <div class="coaching_indicador_categoria"><?php echo htmlspecialchars($categoria); ?></div>
                                    <?php foreach ($items as $i): ?>
                                        <div class="coaching_indicador_item">
                                            <label style="font-weight:normal; margin:0; cursor:pointer;">
                                                <input type="checkbox" name="indicadores[]" value="<?php echo (int) $i['gci_id']; ?>" class="coaching_indicador_check"
                                                    <?php echo in_array((string) $i['gci_id'], $seleccionados, true) ? 'checked' : ''; ?>>
                                                <?php echo htmlspecialchars($i['gci_nombre']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="coaching_indicador_contador" id="contador_indicadores">0 seleccionado(s)</div>
                            <?php if (isset($errores_campo['indicadores'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['indicadores']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="coaching_paso_numero">3</span> Acción de seguimiento ejecutada</div>
                        <div class="p-3">
                            <label class="coaching_label" for="tipo_codigo">Acción</label>
                            <select name="tipo_codigo" id="tipo_codigo" class="form-control <?php echo isset($errores_campo['tipo_codigo']) ? 'coaching_campo_error' : ''; ?>" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t['gct_codigo']); ?>" <?php echo (isset($_POST['tipo_codigo']) && $_POST['tipo_codigo'] === $t['gct_codigo']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t['gct_nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errores_campo['tipo_codigo'])): ?><div class="coaching_campo_error_texto"><?php echo $errores_campo['tipo_codigo']; ?></div><?php endif; ?>

                            <?php foreach ($ayuda_tipos as $codigo => $texto): ?>
                                <div class="coaching_ayuda" data-ayuda-tipo="<?php echo htmlspecialchars($codigo); ?>">
                                    <span class="fas fa-info-circle color-corporativo"></span> <?php echo htmlspecialchars($texto); ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="coaching_label" for="prioridad">Prioridad</label>
                                    <select name="prioridad" id="prioridad" class="form-control" required>
                                        <option value="Normal" <?php echo (!isset($_POST['prioridad']) || $_POST['prioridad'] === 'Normal') ? 'selected' : ''; ?>>Normal</option>
                                        <option value="Baja" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                        <option value="Alta" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Alta') ? 'selected' : ''; ?>>Alta</option>
                                        <option value="Urgente" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] === 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="coaching_label" for="fecha_limite">Fecha límite <span class="opcional">(opcional)</span></label>
                                    <input type="date" name="fecha_limite" id="fecha_limite" class="form-control" min="<?php echo date('Y-m-d'); ?>"
                                        value="<?php echo isset($_POST['fecha_limite']) ? htmlspecialchars($_POST['fecha_limite']) : ''; ?>">
                                </div>
                            </div>

                            <div class="coaching_bloque_escalamiento" id="bloque_escalamiento">
                                <p style="font-size:11px; color:#1A1A1A; margin-bottom:10px;">
                                    <span class="fas fa-exclamation-triangle"></span> Complete los datos exactos del correo con el que se hizo el escalamiento.
                                </p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="coaching_label" for="escalamiento_asunto">Asunto del correo escalado</label>
                                        <input type="text" name="escalamiento_asunto" id="escalamiento_asunto" class="form-control <?php echo isset($errores_campo['escalamiento_asunto']) ? 'coaching_campo_error' : ''; ?>" maxlength="255"
                                            value="<?php echo isset($_POST['escalamiento_asunto']) ? htmlspecialchars($_POST['escalamiento_asunto']) : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="coaching_label" for="escalamiento_fecha">Fecha y hora de envío</label>
                                        <input type="datetime-local" name="escalamiento_fecha" id="escalamiento_fecha" class="form-control <?php echo isset($errores_campo['escalamiento_fecha']) ? 'coaching_campo_error' : ''; ?>"
                                            value="<?php echo isset($_POST['escalamiento_fecha']) ? htmlspecialchars($_POST['escalamiento_fecha']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="coaching_label" for="escalamiento_destinatario">Escalamiento remitido a</label>
                                        <input type="text" name="escalamiento_destinatario" id="escalamiento_destinatario" class="form-control <?php echo isset($errores_campo['escalamiento_destinatario']) ? 'coaching_campo_error' : ''; ?>" maxlength="150"
                                            value="<?php echo isset($_POST['escalamiento_destinatario']) ? htmlspecialchars($_POST['escalamiento_destinatario']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label class="coaching_label" for="escalamiento_correo">Correo del destinatario</label>
                                        <input type="email" name="escalamiento_correo" id="escalamiento_correo" class="form-control <?php echo isset($errores_campo['escalamiento_correo']) ? 'coaching_campo_error' : ''; ?>" maxlength="150"
                                            value="<?php echo isset($_POST['escalamiento_correo']) ? htmlspecialchars($_POST['escalamiento_correo']) : ''; ?>">
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label class="coaching_label" for="escalamiento_observaciones">Observaciones del escalamiento <span class="opcional">(opcional)</span></label>
                                        <textarea name="escalamiento_observaciones" id="escalamiento_observaciones" class="form-control" style="height:60px;"><?php echo isset($_POST['escalamiento_observaciones']) ? htmlspecialchars($_POST['escalamiento_observaciones']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cuadro_dash mb-3">
                        <div class="cuadro_dash_titulo p-2"><span class="coaching_paso_numero">4</span> Reporte del proceso de seguimiento</div>
                        <div class="p-3">
                            <label class="coaching_label" for="contexto">Motivo / justificación del reporte <span class="opcional">(recomendado)</span></label>
                            <textarea name="contexto" id="contexto" class="form-control" style="height:90px;" maxlength="800"
                                placeholder="Registre información objetiva, verificable y conocida por el colaborador..."
                                aria-describedby="contador_contexto"><?php echo isset($_POST['contexto']) ? htmlspecialchars($_POST['contexto']) : ''; ?></textarea>
                            <div class="coaching_contador" id="contador_contexto">0 / 800</div>
                            <p class="descripcion-seccion-conocimiento mt-2 mb-0">
                                Los compromisos detallados y la respuesta del colaborador se registran en el siguiente paso, después de crear el paquete.
                            </p>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <div class="d-flex justify-content-center align-items-center" style="gap:10px;">
                            <button type="submit" name="guardar_registro" id="btn_crear_paquete" class="btn-corp px-4 py-2" style="border-radius:5px; border:0; min-width:180px;">
                                <span class="fas fa-save"></span> Registrar acompañamiento
                            </button>
                            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes" class="btn-corp-2 px-4 py-2 d-inline-block" style="border-radius:5px; min-width:120px;">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <script>
        (function () {
            var segmentosPorAgente = <?php echo json_encode($mapa_segmentos_agentes, JSON_UNESCAPED_UNICODE); ?>;
            var selectAgente = document.getElementById('agente_id');
            var campoSegmento = document.getElementById('segmento');

            function actualizarSegmentoSugerido() {
                var sugerido = segmentosPorAgente[selectAgente.value] || '';
                if (campoSegmento.value === '' || campoSegmento.dataset.autocompletado === '1') {
                    campoSegmento.value = sugerido;
                    campoSegmento.dataset.autocompletado = '1';
                }
            }
            selectAgente.addEventListener('change', actualizarSegmentoSugerido);
            campoSegmento.addEventListener('input', function () { campoSegmento.dataset.autocompletado = '0'; });
            actualizarSegmentoSugerido();

            var selectTipo = document.getElementById('tipo_codigo');
            var paneles = document.querySelectorAll('[data-ayuda-tipo]');
            var bloqueEscalamiento = document.getElementById('bloque_escalamiento');
            var TIPOS_ESCALAMIENTO = <?php echo json_encode($tipos_con_escalamiento); ?>;
            var camposEscalamiento = bloqueEscalamiento.querySelectorAll('input[type="text"], input[type="datetime-local"], input[type="email"]');

            function actualizarTipo() {
                var actual = selectTipo.value;
                paneles.forEach(function (panel) {
                    panel.classList.toggle('activa', panel.getAttribute('data-ayuda-tipo') === actual);
                });
                var esEscalamiento = TIPOS_ESCALAMIENTO.indexOf(actual) !== -1;
                bloqueEscalamiento.classList.toggle('activo', esEscalamiento);
                camposEscalamiento.forEach(function (campo) {
                    if (campo.id !== 'escalamiento_observaciones') { campo.required = esEscalamiento; }
                });
            }
            selectTipo.addEventListener('change', actualizarTipo);
            actualizarTipo();

            var checksIndicadores = document.querySelectorAll('.coaching_indicador_check');
            var contadorIndicadores = document.getElementById('contador_indicadores');
            function actualizarContadorIndicadores() {
                var n = document.querySelectorAll('.coaching_indicador_check:checked').length;
                contadorIndicadores.textContent = n + ' seleccionado(s)';
            }
            checksIndicadores.forEach(function (c) { c.addEventListener('change', actualizarContadorIndicadores); });
            actualizarContadorIndicadores();

            var textarea = document.getElementById('contexto');
            var contador = document.getElementById('contador_contexto');
            var LIMITE = 800;
            function actualizarContador() {
                var restante = textarea.value.length;
                contador.textContent = restante + ' / ' + LIMITE;
                contador.classList.toggle('limite', restante > LIMITE * 0.9);
            }
            textarea.addEventListener('input', actualizarContador);
            actualizarContador();

            var form = document.getElementById('form_coaching_crear');
            var boton = document.getElementById('btn_crear_paquete');
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    boton.disabled = true;
                    boton.innerHTML = '<span class="fas fa-spinner fa-spin"></span> Creando...';
                }, 0);
            });
        })();
        </script>

        <?php endif; ?>
    </div>
    <?php include("../footer.php"); ?>
</body>
</html>
