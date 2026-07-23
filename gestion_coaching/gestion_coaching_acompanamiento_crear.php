<?php
$modulo_plataforma = 'Coaching-Crear-Global';
require_once '../config/validaciones_seguridad.php';
require_once '../config/conexion_db.php';
require_once 'lib/coaching_seguridad.php';
require_once 'lib/coaching_datos.php';
require_once 'lib/coaching_acompanamiento.php';

$titulo_header = 'Coaching | Nuevo acompañamiento';
$perfil = coachingPerfilUsuarioActual();
if ($perfil === null || !in_array($perfil, ['Supervisor','Gestor','Administrador'], true)) {
    header('Location:../permiso_denegado.php'); exit;
}
if (empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));

$agentes = listarAgentesDeSupervisor($enlace_db, $_SESSION['usu_id']);
if (in_array($perfil, ['Gestor','Administrador'], true)) {
    $agentes = $enlace_db->query("SELECT `usu_id`,`usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE `usu_estado`='Activo' ORDER BY `usu_nombres_apellidos`")->fetch_all(MYSQLI_ASSOC);
}
$acciones = listarAccionesAcompanamiento($enlace_db);
$segmentos = listarSegmentosAcompanamiento($enlace_db);
$indicadores = listarIndicadoresAcompanamiento($enlace_db);
$indicadoresPorCategoria = [];
foreach ($indicadores as $i) $indicadoresPorCategoria[$i['gcis_categoria']][] = $i;

$correoLider = '';
$st = $enlace_db->prepare("SELECT `usu_correo_corporativo` FROM `tb_administrador_usuario` WHERE `usu_id`=? LIMIT 1");
$st->bind_param('s', $_SESSION['usu_id']); $st->execute();
$correoLider = (string)($st->get_result()->fetch_assoc()['usu_correo_corporativo'] ?? '');
$respuesta_accion = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_registro'])) {
    $csrfOk = isset($_POST['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], (string)$_POST['_csrf_token']);
    $d = [
        'colaborador_id' => validar_input($_POST['colaborador_id'] ?? ''),
        'correo_lider' => validar_input($_POST['correo_lider'] ?? ''),
        'segmento_id' => (int)($_POST['segmento_id'] ?? 0),
        'segmento_nombre' => '',
        'empresa' => validar_input($_POST['empresa'] ?? ''),
        'anio' => (int)($_POST['anio'] ?? date('Y')),
        'mes' => (int)($_POST['mes'] ?? date('n')),
        'accion_id' => (int)($_POST['accion_id'] ?? 0),
        'indicadores' => $_POST['indicadores'] ?? [],
        'motivo' => trim((string)($_POST['motivo'] ?? '')),
        'compromisos' => trim((string)($_POST['compromisos'] ?? '')),
        'prioridad' => validar_input($_POST['prioridad'] ?? 'Normal'),
        'fecha_limite' => validar_input($_POST['fecha_limite'] ?? ''),
        'escalamiento_asunto' => trim((string)($_POST['escalamiento_asunto'] ?? '')),
        'escalamiento_fecha' => validar_input($_POST['escalamiento_fecha'] ?? ''),
        'escalamiento_destinatario' => trim((string)($_POST['escalamiento_destinatario'] ?? '')),
        'escalamiento_correo' => trim((string)($_POST['escalamiento_correo'] ?? '')),
        'escalamiento_observaciones' => trim((string)($_POST['escalamiento_observaciones'] ?? '')),
    ];
    foreach ($segmentos as $s) if ((int)$s['gcsg_id'] === $d['segmento_id']) $d['segmento_nombre'] = $s['gcsg_nombre'];

    if (!$csrfOk) $errores[] = 'Solicitud inválida. Recargue la página.';
    if ($d['colaborador_id'] === '') $errores[] = 'Seleccione el colaborador.';
    if (!filter_var($d['correo_lider'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo del líder no es válido.';
    if ($d['segmento_id'] < 1 || $d['segmento_nombre'] === '') $errores[] = 'Seleccione el segmento.';
    if (!in_array($d['empresa'], ['ASD','IQ'], true)) $errores[] = 'Seleccione la empresa.';
    if ($d['mes'] < 1 || $d['mes'] > 12) $errores[] = 'Seleccione el mes.';
    if ($d['accion_id'] < 1) $errores[] = 'Seleccione la acción ejecutada.';
    if (!is_array($d['indicadores']) || count($d['indicadores']) === 0) $errores[] = 'Seleccione al menos un indicador.';
    if (mb_strlen($d['motivo']) < 20) $errores[] = 'La justificación debe tener al menos 20 caracteres.';
    if (mb_strlen($d['compromisos']) < 10) $errores[] = 'Registre los compromisos o acuerdos alcanzados.';

    if (!$errores) {
        try {
            $r = crearAcompanamiento($enlace_db, $d, $_SESSION['usu_id']);
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
            $url = 'gestion_coaching_acompanamiento_ver.php?reg=' . base64_encode($r['acompanamiento_id']);
            $respuesta_accion = "<script>alertify.success('Acompañamiento {$r['acompanamiento_id']} creado correctamente.',0);setTimeout(function(){location.href='".htmlspecialchars($url,ENT_QUOTES)."';},1000);</script>";
        } catch (Throwable $e) {
            $errores[] = $e->getMessage();
        }
    }
}
$meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
?>
<!DOCTYPE html><html lang="ES"><head>
<?php include '../config/configuracion_estilos.php'; ?>
<style>
.coach-step{background:#fff;border:1px solid #F2F2F2;border-radius:6px;margin-bottom:12px}.coach-step-title{background:#4CAF50;color:#fff;font-weight:bold;padding:9px 12px}.coach-step-body{padding:14px}.indicator-box{max-height:330px;overflow:auto;border:1px solid #F2F2F2;padding:10px;border-radius:4px}.indicator-category{font-weight:bold;color:#4CAF50;margin-top:8px}.required:after{content:' *';color:#FF0000}.conditional{display:none}.help{font-size:11px;color:#6E6E6E}.summary-bar{position:sticky;bottom:0;background:#fff;border-top:2px solid #4CAF50;padding:14px 16px;z-index:5;box-shadow:0 -2px 8px rgba(0,0,0,.06);display:flex;justify-content:center;align-items:center;gap:10px}
</style></head><body>
<?php include '../menu_principal.php'; include '../menu_header.php'; ?>
<div class="contenido fondo-gris"><div class="container-fluid py-2">
<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0"><span class="fas fa-user-check"></span> Acompañamiento - Líder de equipo</h5><a class="btn-corp px-3 py-1" style="border-radius:5px;" href="gestion_coaching_acompanamientos.php"><span class="fas fa-list"></span> Ver acompañamientos</a></div>
<?php echo $respuesta_accion; ?>
<?php if ($errores): ?><div class="alert alert-danger"><strong>Revise la información:</strong><ul class="mb-0"><?php foreach($errores as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" id="formAcompanamiento">
<input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token']); ?>">
<div class="coach-step"><div class="coach-step-title">1. Datos de seguimiento</div><div class="coach-step-body"><div class="row">
<div class="col-md-6 form-group"><label class="required">Correo electrónico del líder</label><input class="form-control form-control-sm" type="email" name="correo_lider" maxlength="180" required value="<?php echo htmlspecialchars($_POST['correo_lider'] ?? $correoLider); ?>"></div>
<div class="col-md-6 form-group"><label class="required">Colaborador intervenido</label><select class="form-control form-control-sm" name="colaborador_id" required><option value="">Seleccione...</option><?php foreach($agentes as $a): ?><option value="<?php echo htmlspecialchars($a['usu_id']); ?>" <?php echo (($_POST['colaborador_id']??'')===$a['usu_id'])?'selected':''; ?>><?php echo htmlspecialchars($a['usu_nombres_apellidos'].' - '.$a['usu_id']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-4 form-group"><label class="required">Segmento</label><select class="form-control form-control-sm" name="segmento_id" required><option value="">Seleccione...</option><?php foreach($segmentos as $s): ?><option value="<?php echo (int)$s['gcsg_id']; ?>" <?php echo ((int)($_POST['segmento_id']??0)===(int)$s['gcsg_id'])?'selected':''; ?>><?php echo htmlspecialchars($s['gcsg_nombre']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-2 form-group"><label class="required">Empresa</label><select class="form-control form-control-sm" name="empresa" required><option value="">Seleccione</option><option <?php echo (($_POST['empresa']??'')==='ASD')?'selected':''; ?>>ASD</option><option <?php echo (($_POST['empresa']??'')==='IQ')?'selected':''; ?>>IQ</option></select></div>
<div class="col-md-3 form-group"><label class="required">Año</label><input class="form-control form-control-sm" type="number" min="2020" max="2100" name="anio" required value="<?php echo (int)($_POST['anio']??date('Y')); ?>"></div>
<div class="col-md-3 form-group"><label class="required">Mes del seguimiento</label><select class="form-control form-control-sm" name="mes" required><?php foreach($meses as $n=>$m): ?><option value="<?php echo $n; ?>" <?php echo ((int)($_POST['mes']??date('n'))===$n)?'selected':''; ?>><?php echo $m; ?></option><?php endforeach; ?></select></div>
</div></div></div>

<div class="coach-step"><div class="coach-step-title">2. Indicadores objeto de seguimiento</div><div class="coach-step-body"><p class="help">Puede seleccionar uno o varios indicadores.</p><div class="indicator-box">
<?php foreach($indicadoresPorCategoria as $cat=>$items): ?><div class="indicator-category"><?php echo htmlspecialchars($cat); ?></div><?php foreach($items as $i): ?><div class="custom-control custom-checkbox"><input class="custom-control-input" type="checkbox" name="indicadores[]" id="ind<?php echo (int)$i['gcis_id']; ?>" value="<?php echo (int)$i['gcis_id']; ?>" <?php echo in_array((string)$i['gcis_id'], array_map('strval',$_POST['indicadores']??[]),true)?'checked':''; ?>><label class="custom-control-label" for="ind<?php echo (int)$i['gcis_id']; ?>"><?php echo htmlspecialchars($i['gcis_nombre']); ?></label></div><?php endforeach; ?><?php endforeach; ?>
</div></div></div>

<div class="coach-step"><div class="coach-step-title">3. Acción de seguimiento ejecutada</div><div class="coach-step-body"><div class="row">
<div class="col-md-6 form-group"><label class="required">Acción</label><select class="form-control form-control-sm" name="accion_id" id="accion_id" required><option value="">Seleccione...</option><?php foreach($acciones as $a): ?><option value="<?php echo (int)$a['gcas_id']; ?>" data-codigo="<?php echo htmlspecialchars($a['gcas_codigo']); ?>" data-escalamiento="<?php echo (int)$a['gcas_requiere_escalamiento']; ?>" data-paquete="<?php echo (int)$a['gcas_genera_paquete']; ?>" <?php echo ((int)($_POST['accion_id']??0)===(int)$a['gcas_id'])?'selected':''; ?>><?php echo htmlspecialchars($a['gcas_nombre']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-3 form-group conditional" id="bloquePrioridad"><label>Prioridad</label><select class="form-control form-control-sm" name="prioridad"><option>Baja</option><option selected>Normal</option><option>Alta</option><option>Urgente</option></select></div>
<div class="col-md-3 form-group conditional" id="bloqueFecha"><label>Fecha límite</label><input class="form-control form-control-sm" type="date" name="fecha_limite" value="<?php echo htmlspecialchars($_POST['fecha_limite']??''); ?>"></div>
</div>
<div class="conditional" id="bloqueEscalamiento"><div class="alert alert-warning py-2">Complete los datos exactos del correo utilizado para el escalamiento.</div><div class="row">
<div class="col-md-6 form-group"><label>Asunto del correo</label><input class="form-control form-control-sm" name="escalamiento_asunto" maxlength="300" value="<?php echo htmlspecialchars($_POST['escalamiento_asunto']??''); ?>"></div>
<div class="col-md-3 form-group"><label>Fecha y hora de envío</label><input class="form-control form-control-sm" type="datetime-local" name="escalamiento_fecha" value="<?php echo htmlspecialchars($_POST['escalamiento_fecha']??''); ?>"></div>
<div class="col-md-3 form-group"><label>Destinatario</label><input class="form-control form-control-sm" name="escalamiento_destinatario" maxlength="180" value="<?php echo htmlspecialchars($_POST['escalamiento_destinatario']??''); ?>"></div>
<div class="col-md-6 form-group"><label>Correo destinatario</label><input class="form-control form-control-sm" type="email" name="escalamiento_correo" maxlength="180" value="<?php echo htmlspecialchars($_POST['escalamiento_correo']??''); ?>"></div>
<div class="col-md-6 form-group"><label>Observaciones del escalamiento</label><textarea class="form-control form-control-sm" name="escalamiento_observaciones" maxlength="2000"><?php echo htmlspecialchars($_POST['escalamiento_observaciones']??''); ?></textarea></div>
</div></div></div></div>

<div class="coach-step"><div class="coach-step-title">4. Reporte del proceso de seguimiento</div><div class="coach-step-body"><div class="form-group"><label class="required">Motivo - Justificación del reporte</label><textarea class="form-control form-control-sm" name="motivo" rows="5" maxlength="8000" required><?php echo htmlspecialchars($_POST['motivo']??''); ?></textarea><div class="help">Registre información objetiva, verificable y conocida por el colaborador.</div></div><div class="form-group"><label class="required">Compromisos adquiridos por el colaborador</label><textarea class="form-control form-control-sm" name="compromisos" rows="5" maxlength="8000" required><?php echo htmlspecialchars($_POST['compromisos']??''); ?></textarea></div></div></div>

<div class="summary-bar"><a class="btn-corp-2 px-4 py-2" style="border-radius:5px; min-width:120px; text-align:center;" href="gestion_coaching_acompanamientos.php">Cancelar</a><button class="btn-corp px-4 py-2" style="border-radius:5px; border:0; min-width:220px;" type="submit" name="guardar_registro"><span class="fas fa-save"></span> Registrar acompañamiento</button></div>
</form></div></div>
<?php include '../footer.php'; include '../config/configuracion_js.php'; ?>
<script>
function actualizarAccion(){var o=document.querySelector('#accion_id option:checked');var esc=o&&o.dataset.escalamiento==='1';var pack=o&&o.dataset.paquete==='1';document.getElementById('bloqueEscalamiento').style.display=esc?'block':'none';document.getElementById('bloquePrioridad').style.display=pack?'block':'none';document.getElementById('bloqueFecha').style.display=pack?'block':'none';document.querySelectorAll('#bloqueEscalamiento input').forEach(function(i){i.required=esc&&['escalamiento_asunto','escalamiento_fecha','escalamiento_destinatario'].includes(i.name);});}
document.getElementById('accion_id').addEventListener('change',actualizarAccion);actualizarAccion();
</script></body></html>


