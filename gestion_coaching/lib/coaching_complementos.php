<?php
declare(strict_types=1);

/** Funciones complementarias del módulo Coaching: encuesta, soportes y detalles por tipo. */
function coachingEsc(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function obtenerPreguntasEncuestaActivas(mysqli $db): array {
    $r=$db->query("SELECT gcep_id,gcep_pregunta,gcep_orden FROM tb_gestion_coaching_encuesta_pregunta WHERE gcep_activo=1 ORDER BY gcep_orden,gcep_id");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
function obtenerEncuestaPaquete(mysqli $db,string $paquete): ?array {
    $s=$db->prepare("SELECT * FROM tb_gestion_coaching_encuesta WHERE gce_paquete=? LIMIT 1");
    $s->bind_param('s',$paquete); $s->execute(); $cab=$s->get_result()->fetch_assoc();
    if(!$cab) return null;
    $s=$db->prepare("SELECT R.*,P.gcep_pregunta,P.gcep_orden FROM tb_gestion_coaching_encuesta_respuesta R JOIN tb_gestion_coaching_encuesta_pregunta P ON P.gcep_id=R.gcer_pregunta WHERE R.gcer_encuesta=? ORDER BY P.gcep_orden");
    $s->bind_param('i',$cab['gce_id']); $s->execute(); $cab['respuestas']=$s->get_result()->fetch_all(MYSQLI_ASSOC);
    return $cab;
}
function guardarEncuestaPaquete(mysqli $db,string $paquete,string $usuario,array $respuestas,?string $observaciones): void {
    $preguntas=obtenerPreguntasEncuestaActivas($db);
    if(!$preguntas) throw new RuntimeException('No existen preguntas activas para la encuesta.');
    foreach($preguntas as $p){ $v=(int)($respuestas[(string)$p['gcep_id']]??0); if($v<1||$v>5) throw new RuntimeException('Debe responder todas las preguntas con una escala de 1 a 5.'); }
    $db->begin_transaction();
    try{
        $s=$db->prepare("SELECT gce_id FROM tb_gestion_coaching_encuesta WHERE gce_paquete=? FOR UPDATE"); $s->bind_param('s',$paquete);$s->execute();
        if($s->get_result()->fetch_assoc()) throw new RuntimeException('La encuesta de este paquete ya fue respondida.');
        $s=$db->prepare("INSERT INTO tb_gestion_coaching_encuesta(gce_paquete,gce_usuario,gce_observaciones) VALUES(?,?,?)");
        $s->bind_param('sss',$paquete,$usuario,$observaciones); if(!$s->execute()) throw new RuntimeException('No fue posible registrar la encuesta.');
        $id=$db->insert_id;
        $i=$db->prepare("INSERT INTO tb_gestion_coaching_encuesta_respuesta(gcer_encuesta,gcer_pregunta,gcer_valor) VALUES(?,?,?)");
        foreach($preguntas as $p){$pid=(int)$p['gcep_id'];$v=(int)$respuestas[(string)$pid];$i->bind_param('iii',$id,$pid,$v);if(!$i->execute())throw new RuntimeException('No fue posible registrar una respuesta.');}
        $db->commit();
    }catch(Throwable $e){$db->rollback();throw $e;}
}
function encuestaCompleta(mysqli $db,string $paquete): bool { return obtenerEncuestaPaquete($db,$paquete)!==null; }

function obtenerDetalleTipo(mysqli $db,string $paquete): ?array {
    $s=$db->prepare("SELECT * FROM tb_gestion_coaching_detalle_tipo WHERE gcdt_paquete=? LIMIT 1");$s->bind_param('s',$paquete);$s->execute();return $s->get_result()->fetch_assoc()?:null;
}
function guardarDetalleTipo(mysqli $db,string $paquete,array $d,string $usuario): void {
    $sql="INSERT INTO tb_gestion_coaching_detalle_tipo(gcdt_paquete,gcdt_fecha_ocurrencia,gcdt_descripcion_falta,gcdt_impacto,gcdt_evidencias,gcdt_reincidente,gcdt_retroalimentaciones_previas,gcdt_tipo_reconocimiento,gcdt_periodo_reconocido,gcdt_resultado_obtenido,gcdt_meta,gcdt_monitoreo_destacado,gcdt_fecha_monitoreo,gcdt_fortalezas_reconocimiento,gcdt_descripcion_reconocimiento,gcdt_fecha_entrega,gcdt_responsable_entrega,gcdt_actualizado_por) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE gcdt_fecha_ocurrencia=VALUES(gcdt_fecha_ocurrencia),gcdt_descripcion_falta=VALUES(gcdt_descripcion_falta),gcdt_impacto=VALUES(gcdt_impacto),gcdt_evidencias=VALUES(gcdt_evidencias),gcdt_reincidente=VALUES(gcdt_reincidente),gcdt_retroalimentaciones_previas=VALUES(gcdt_retroalimentaciones_previas),gcdt_tipo_reconocimiento=VALUES(gcdt_tipo_reconocimiento),gcdt_periodo_reconocido=VALUES(gcdt_periodo_reconocido),gcdt_resultado_obtenido=VALUES(gcdt_resultado_obtenido),gcdt_meta=VALUES(gcdt_meta),gcdt_monitoreo_destacado=VALUES(gcdt_monitoreo_destacado),gcdt_fecha_monitoreo=VALUES(gcdt_fecha_monitoreo),gcdt_fortalezas_reconocimiento=VALUES(gcdt_fortalezas_reconocimiento),gcdt_descripcion_reconocimiento=VALUES(gcdt_descripcion_reconocimiento),gcdt_fecha_entrega=VALUES(gcdt_fecha_entrega),gcdt_responsable_entrega=VALUES(gcdt_responsable_entrega),gcdt_actualizado_por=VALUES(gcdt_actualizado_por),gcdt_actualizado_fecha=NOW()";
    $s=$db->prepare($sql);
    $vals=[ $paquete,$d['fecha_ocurrencia']??null,$d['descripcion_falta']??null,$d['impacto']??null,$d['evidencias']??null,$d['reincidente']??null,$d['retroalimentaciones_previas']??null,$d['tipo_reconocimiento']??null,$d['periodo_reconocido']??null,$d['resultado_obtenido']??null,$d['meta']??null,$d['monitoreo_destacado']??null,$d['fecha_monitoreo']??null,$d['fortalezas_reconocimiento']??null,$d['descripcion_reconocimiento']??null,$d['fecha_entrega']??null,$d['responsable_entrega']??null,$usuario ];
    $s->bind_param(str_repeat('s',count($vals)),...$vals); if(!$s->execute()) throw new RuntimeException('No fue posible guardar el detalle especializado.');
}

const COACHING_SOPORTES_RUTA = __DIR__.'/../storage/coaching_soportes';
function listarSoportesCoaching(mysqli $db,string $paquete): array {$s=$db->prepare("SELECT * FROM tb_gestion_coaching_soporte WHERE gcs_paquete=? AND gcs_activo=1 ORDER BY gcs_registro_fecha DESC");$s->bind_param('s',$paquete);$s->execute();return $s->get_result()->fetch_all(MYSQLI_ASSOC);}
function guardarSoporteCoaching(mysqli $db,string $paquete,array $archivo,string $tipo,string $usuario): int {
    if(($archivo['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('No fue posible recibir el archivo.');
    $max=10*1024*1024; if((int)$archivo['size']>$max) throw new RuntimeException('El archivo supera 10 MB.');
    $permitidos=['pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
    $ext=strtolower(pathinfo((string)$archivo['name'],PATHINFO_EXTENSION)); if(!isset($permitidos[$ext]))throw new RuntimeException('Tipo de archivo no permitido.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']); if($mime!==$permitidos[$ext])throw new RuntimeException('El contenido del archivo no coincide con su extensión.');
    if(!is_dir(COACHING_SOPORTES_RUTA)&&!mkdir(COACHING_SOPORTES_RUTA,0750,true)&&!is_dir(COACHING_SOPORTES_RUTA))throw new RuntimeException('No fue posible preparar el almacenamiento.');
    $interno=bin2hex(random_bytes(20)).'.'.$ext; $ruta=COACHING_SOPORTES_RUTA.DIRECTORY_SEPARATOR.$interno;
    if(!move_uploaded_file($archivo['tmp_name'],$ruta))throw new RuntimeException('No fue posible almacenar el soporte.');
    chmod($ruta,0640);$hash=hash_file('sha256',$ruta);$nombre=mb_substr(basename((string)$archivo['name']),0,255);
    $s=$db->prepare("INSERT INTO tb_gestion_coaching_soporte(gcs_paquete,gcs_nombre_original,gcs_nombre_interno,gcs_ruta,gcs_extension,gcs_mime,gcs_tamanio,gcs_hash_sha256,gcs_tipo_documental,gcs_registro_usuario) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $size=(int)$archivo['size'];$s->bind_param('ssssssisss',$paquete,$nombre,$interno,$ruta,$ext,$mime,$size,$hash,$tipo,$usuario);
    if(!$s->execute()){ @unlink($ruta); throw new RuntimeException('No fue posible registrar el soporte.'); } return $db->insert_id;
}
function obtenerSoporteCoaching(mysqli $db,int $id): ?array {$s=$db->prepare("SELECT * FROM tb_gestion_coaching_soporte WHERE gcs_id=? AND gcs_activo=1 LIMIT 1");$s->bind_param('i',$id);$s->execute();return $s->get_result()->fetch_assoc()?:null;}
function descargarSoporteCoaching(array $s): void {if(!is_readable($s['gcs_ruta'])||hash_file('sha256',$s['gcs_ruta'])!==$s['gcs_hash_sha256']){http_response_code(409);exit('Soporte no disponible o con integridad comprometida.');}header('Content-Type: '.$s['gcs_mime']);header('Content-Disposition: attachment; filename="'.rawurlencode($s['gcs_nombre_original']).'"');header('X-Content-Type-Options: nosniff');header('Content-Length: '.filesize($s['gcs_ruta']));readfile($s['gcs_ruta']);exit;}
