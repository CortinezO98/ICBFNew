<?php
$modulo_plataforma='Coaching';
require_once '../config/validaciones_seguridad.php';
require_once '../config/conexion_db.php';
require_once 'lib/coaching_seguridad.php';
require_once 'lib/coaching_acompanamiento.php';
$titulo_header='Coaching | Acompañamientos';
$perfil=coachingPerfilUsuarioActual();
if($perfil===null){header('Location:../permiso_denegado.php');exit;}
$cond='A.`gca_activo`=1';$params=[];$types='';
if($perfil==='Agente'){$cond.=' AND A.`gca_colaborador_id`=?';$params[]=$_SESSION['usu_id'];$types.='s';}
elseif($perfil==='Supervisor'){$cond.=' AND A.`gca_lider_id`=?';$params[]=$_SESSION['usu_id'];$types.='s';}
$desde=validar_input($_GET['desde']??date('Y-m-01'));$hasta=validar_input($_GET['hasta']??date('Y-m-t'));
$cond.=' AND DATE(A.`gca_registro_fecha`) BETWEEN ? AND ?';$params[]=$desde;$params[]=$hasta;$types.='ss';
$sql="SELECT A.`gca_id`,A.`gca_paquete_id`,A.`gca_empresa`,A.`gca_anio`,A.`gca_mes`,A.`gca_estado`,A.`gca_registro_fecha`,
U.`usu_nombres_apellidos` colaborador,L.`usu_nombres_apellidos` lider,S.`gcsg_nombre` segmento,X.`gcas_nombre` accion,
COUNT(I.`gcai_id`) total_indicadores
FROM `tb_gestion_coaching_acompanamiento` A
LEFT JOIN `tb_administrador_usuario` U ON U.`usu_id`=A.`gca_colaborador_id`
LEFT JOIN `tb_administrador_usuario` L ON L.`usu_id`=A.`gca_lider_id`
LEFT JOIN `tb_gestion_coaching_segmento` S ON S.`gcsg_id`=A.`gca_segmento_id`
LEFT JOIN `tb_gestion_coaching_accion_seguimiento` X ON X.`gcas_id`=A.`gca_accion_id`
LEFT JOIN `tb_gestion_coaching_acompanamiento_indicador` I ON I.`gcai_acompanamiento_id`=A.`gca_id`
WHERE $cond GROUP BY A.`gca_id` ORDER BY A.`gca_registro_fecha` DESC LIMIT 500";
$st=$enlace_db->prepare($sql);$st->bind_param($types,...$params);$st->execute();$rows=$st->get_result()->fetch_all(MYSQLI_ASSOC);
$meses=[1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
?>
<!DOCTYPE html><html lang="ES"><head><?php include '../config/configuracion_estilos.php'; ?><style>.badge-soft{background:#e9f4f8;color:#176486;padding:4px 7px;border-radius:12px;font-size:10px}.table td,.table th{font-size:11px;vertical-align:middle}</style></head><body>
<?php include '../menu_principal.php';include '../menu_header.php'; ?>
<div class="contenido fondo-gris"><div class="container-fluid py-2"><div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0"><span class="fas fa-clipboard-check"></span> Acompañamientos del líder</h5><?php if(in_array($perfil,['Supervisor','Gestor','Administrador'],true)): ?><a class="btn btn-success" href="gestion_coaching_acompanamiento_crear.php"><span class="fas fa-plus"></span> Nuevo acompañamiento</a><?php endif; ?></div>
<form class="form-inline mb-2" method="get"><label class="mr-1">Desde</label><input class="form-control form-control-sm mr-2" type="date" name="desde" value="<?php echo htmlspecialchars($desde); ?>"><label class="mr-1">Hasta</label><input class="form-control form-control-sm mr-2" type="date" name="hasta" value="<?php echo htmlspecialchars($hasta); ?>"><button class="btn btn-corp btn-sm">Filtrar</button></form>
<div class="table-responsive bg-white"><table class="table table-bordered table-hover table-sm mb-0"><thead class="thead-light"><tr><th>Código</th><th>Colaborador</th><th>Líder</th><th>Segmento</th><th>Empresa</th><th>Periodo</th><th>Acción</th><th>Indicadores</th><th>Estado</th><th>Fecha</th><th></th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="11" class="text-center py-4">No se encontraron acompañamientos.</td></tr><?php endif; ?>
<?php foreach($rows as $r): ?><tr><td><strong><?php echo htmlspecialchars($r['gca_id']); ?></strong><?php if($r['gca_paquete_id']): ?><br><span class="badge-soft"><?php echo htmlspecialchars($r['gca_paquete_id']); ?></span><?php endif; ?></td><td><?php echo htmlspecialchars($r['colaborador']); ?></td><td><?php echo htmlspecialchars($r['lider']); ?></td><td><?php echo htmlspecialchars($r['segmento']); ?></td><td><?php echo htmlspecialchars($r['gca_empresa']); ?></td><td><?php echo $meses[(int)$r['gca_mes']].' '.$r['gca_anio']; ?></td><td><?php echo htmlspecialchars($r['accion']); ?></td><td class="text-center"><?php echo (int)$r['total_indicadores']; ?></td><td><span class="badge-soft"><?php echo htmlspecialchars($r['gca_estado']); ?></span></td><td><?php echo htmlspecialchars($r['gca_registro_fecha']); ?></td><td><a class="btn btn-corp btn-sm" href="gestion_coaching_acompanamiento_ver.php?reg=<?php echo base64_encode($r['gca_id']); ?>"><span class="fas fa-eye"></span></a></td></tr><?php endforeach; ?>
</tbody></table></div></div></div><?php include '../footer.php';include '../config/configuracion_js.php'; ?></body></html>
