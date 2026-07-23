<?php
$modulo_plataforma='Coaching';
require_once('../config/validaciones_seguridad.php');require_once('../config/conexion_db.php');
require_once('lib/coaching_seguridad.php');require_once('lib/coaching_datos.php');require_once('lib/coaching_complementos.php');
$titulo_header='Coaching | Encuesta del espacio';
$perfil=coachingPerfilUsuarioActual();$id=validar_input(base64_decode($_GET['reg']??''));
if(!$perfil||!usuarioPuedeVerPaquete($enlace_db,$_SESSION['usu_id'],$perfil,$id)){header('Location:../permiso_denegado.php');exit;}
$p=obtenerPaqueteConDetalle($enlace_db,$id);if(!$p||$p['gcp_agente_id']!==$_SESSION['usu_id']){header('Location:../permiso_denegado.php');exit;}
$existente=obtenerEncuestaPaquete($enlace_db,$id);$preguntas=obtenerPreguntasEncuestaActivas($enlace_db);
if(empty($_SESSION['_csrf_token']))$_SESSION['_csrf_token']=bin2hex(random_bytes(32));$mensaje='';
if($_SERVER['REQUEST_METHOD']==='POST'&&!$existente){
 if(!isset($_POST['_csrf_token'])||!hash_equals($_SESSION['_csrf_token'],$_POST['_csrf_token'])){$mensaje="<div class='alert alert-danger'>Solicitud inválida.</div>";}
 else try{guardarEncuestaPaquete($enlace_db,$id,$_SESSION['usu_id'],$_POST['respuesta']??[],trim($_POST['observaciones']??'')?:null);header('Location:gestion_coaching_ver.php?reg='.base64_encode($id));exit;}catch(Throwable $e){$mensaje="<div class='alert alert-warning'>".coachingEsc($e->getMessage())."</div>";}
}
?>
<!DOCTYPE html><html lang="ES"><head><?php include('../config/configuracion_estilos.php');?><style>.escala label{margin-right:18px}.pregunta{border-bottom:1px solid #ddd;padding:12px 0}</style></head><body>
<?php include('../menu_principal.php');include('../menu_header.php');?><div class="contenido"><div class="row justify-content-center"><div class="col-md-8"><h4 class="titulo_seccion">Encuesta del espacio</h4><?php echo $mensaje;?>
<?php if($existente):?><div class="alert alert-success">La encuesta ya fue respondida el <?php echo coachingEsc($existente['gce_registro_fecha']);?>.</div>
<?php else:?><form method="post"><input type="hidden" name="_csrf_token" value="<?php echo coachingEsc($_SESSION['_csrf_token']);?>"><p class="alert alert-info">1 significa en desacuerdo y 5 muy de acuerdo.</p>
<?php foreach($preguntas as $q):?><div class="pregunta"><strong><?php echo coachingEsc($q['gcep_pregunta']);?></strong><div class="escala mt-2"><?php for($v=1;$v<=5;$v++):?><label><input type="radio" name="respuesta[<?php echo (int)$q['gcep_id'];?>]" value="<?php echo $v;?>" required> <?php echo $v;?></label><?php endfor;?></div></div><?php endforeach;?>
<div class="form-group mt-3"><label>Observaciones opcionales</label><textarea class="form-control" name="observaciones" maxlength="1000"></textarea></div><button class="btn btn-success">Guardar encuesta</button> <a class="btn btn-dark" href="gestion_coaching_ver.php?reg=<?php echo base64_encode($id);?>">Regresar</a></form><?php endif;?></div></div></div><?php include('../footer.php');include('../config/configuracion_js.php');?></body></html>
