<?php
    $modulo_plataforma="Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");
    require_once("lib/coaching_datos.php");
    require_once("lib/coaching_documentos.php");

    $perfil_coaching = coachingPerfilUsuarioActual();
    if ($perfil_coaching === null) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $gcp_id = validar_input(base64_decode($_GET['reg']));

    // Autorización por recurso: se revalida aquí también, no basta con que
    // el usuario haya llegado desde una vista ya autorizada (defensa en
    // profundidad contra acceso directo por URL a otro gcp_id).
    if (!usuarioPuedeVerPaquete($enlace_db, $_SESSION['usu_id'], $perfil_coaching, $gcp_id)) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    $consulta = $enlace_db->prepare(
        "SELECT P.`gcp_tipo_id`, T.`gct_codigo` FROM `tb_gestion_coaching_paquete` AS P
         LEFT JOIN `tb_gestion_coaching_tipo` AS T ON P.`gcp_tipo_id` = T.`gct_id`
         WHERE P.`gcp_id` = ? LIMIT 1"
    );
    $consulta->bind_param('s', $gcp_id);
    $consulta->execute();
    $paquete = $consulta->get_result()->fetch_assoc();

    if (!$paquete) {
        http_response_code(404);
        die('Paquete no encontrado.');
    }

    $mapa_tipo_documento = [
        'RETROALIMENTACION' => 'Retroalimentacion',
        'ACTA_COMPROMISO'   => 'Acta_Compromiso',
        'FELICITACION'      => 'Felicitacion',
        'RECONOCIMIENTO'    => 'Reconocimiento',
    ];
    $tipo_documento = $mapa_tipo_documento[$paquete['gct_codigo']] ?? null;

    if (!$tipo_documento) {
        http_response_code(404);
        die('Este paquete no tiene un tipo de documento configurado.');
    }

    $documento = obtenerDocumentoVigente($enlace_db, $gcp_id, $tipo_documento);

    if (!$documento) {
        http_response_code(404);
        die('Aún no se ha generado el documento para este paquete.');
    }

    // descargarDocumentoCoaching() valida integridad (hash) antes de servir
    // el archivo — ver coaching_documentos.php.
    descargarDocumentoCoaching($documento);



