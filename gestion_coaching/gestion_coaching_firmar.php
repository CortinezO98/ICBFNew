<?php
$modulo_plataforma = "Coaching";

require_once("../config/validaciones_seguridad.php");
require_once("../config/conexion_db.php");
require_once("lib/coaching_seguridad.php");
require_once("lib/coaching_datos.php");
require_once("lib/coaching_firma.php");

$titulo_header = "Coaching | Firmar documento";

$perfil_coaching = coachingPerfilUsuarioActual();
if ($perfil_coaching === null || $perfil_coaching !== "Agente") {
    header("Location:../permiso_denegado.php");
    exit;
}

$reg = isset($_GET["reg"]) ? (string) $_GET["reg"] : "";
$gcp_id_decodificado = base64_decode($reg, true);
$gcp_id = $gcp_id_decodificado !== false ? validar_input($gcp_id_decodificado) : "";

if ($gcp_id === "") {
    header("Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes");
    exit;
}

if (!usuarioPuedeVerPaquete($enlace_db, $_SESSION["usu_id"], $perfil_coaching, $gcp_id)) {
    header("Location:../permiso_denegado.php");
    exit;
}

if (empty($_SESSION["_csrf_token"])) {
    $_SESSION["_csrf_token"] = bin2hex(random_bytes(32));
}

$respuesta_accion = "";

$documento = obtenerDocumentoVigente($enlace_db, $gcp_id, "Retroalimentacion")
    ?? obtenerDocumentoVigente($enlace_db, $gcp_id, "Acta_Compromiso");

if (!$documento) {
    $respuesta_accion = "<div class='alert alert-warning mb-3' role='alert'><span class='fas fa-exclamation-triangle mr-1'></span>No existe un documento vigente disponible para firma.</div>";
}

if (isset($_POST["confirmar_firma"]) && $documento) {
    $csrf_ok = isset($_POST["_csrf_token"])
        && hash_equals((string) $_SESSION["_csrf_token"], (string) $_POST["_csrf_token"]);

    $aceptacion_ok = isset($_POST["aceptacion_documento"])
        && $_POST["aceptacion_documento"] === "1";

    $gcd_id = filter_input(
        INPUT_POST,
        "gcd_id",
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if (!$csrf_ok) {
        $respuesta_accion = "<div class='alert alert-danger mb-3' role='alert'><span class='fas fa-shield-alt mr-1'></span>La sesión de seguridad venció. Recargue la página e intente nuevamente.</div>";
    } elseif (!$aceptacion_ok) {
        $respuesta_accion = "<div class='alert alert-warning mb-3' role='alert'><span class='fas fa-info-circle mr-1'></span>Debe leer y aceptar el documento antes de firmarlo.</div>";
    } elseif ($gcd_id === false || (int) $gcd_id !== (int) $documento["gcd_id"]) {
        $respuesta_accion = "<div class='alert alert-danger mb-3' role='alert'><span class='fas fa-file mr-1'></span>El documento seleccionado no corresponde con la versión vigente.</div>";
    } else {
        try {
            firmarDocumentoCoaching(
                $enlace_db,
                $gcp_id,
                (int) $gcd_id,
                $_SESSION["usu_id"],
                "Agente",
                $_SERVER["REMOTE_ADDR"] ?? "",
                $_SERVER["HTTP_USER_AGENT"] ?? null
            );

            $_SESSION["_csrf_token"] = bin2hex(random_bytes(32));

            $url_destino = "gestion_coaching_ver.php?reg=" . rawurlencode(base64_encode($gcp_id));

            $respuesta_accion = "<div class='alert alert-success mb-3' role='status'><span class='fas fa-check-circle mr-1'></span>Documento firmado correctamente. Estamos regresando al detalle del paquete.</div><script>window.setTimeout(function(){window.location.href=" . json_encode($url_destino) . ";},1200);</script>";
        } catch (Throwable $e) {
            error_log(
                "Error al firmar documento de Coaching. Paquete: "
                . $gcp_id
                . ". Usuario: "
                . ($_SESSION["usu_id"] ?? "sin_usuario")
                . ". Detalle: "
                . $e->getMessage()
            );

            $respuesta_accion = "<div class='alert alert-danger mb-3' role='alert'><span class='fas fa-exclamation-circle mr-1'></span>No fue posible registrar la firma. Verifique que el documento siga vigente e intente nuevamente.</div>";
        }
    }
}

$paquete_codigo = validar_output($gcp_id);
$documento_version = $documento ? (int) ($documento["gcd_version"] ?? 1) : null;
$pdf_url = "gestion_coaching_documento_descargar.php?reg=" . rawurlencode(base64_encode($gcp_id));
$ruta_cancelar = "gestion_coaching_ver.php?reg=" . rawurlencode(base64_encode($gcp_id));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        :root{--firma-primary:#196f3d;--firma-primary-dark:#12542e;--firma-text:#263238;--firma-muted:#66727c;--firma-border:#dfe5e8;--firma-bg:#f4f7f5;--firma-card:#fff;--firma-focus:rgba(25,111,61,.22)}
        body{background:var(--firma-bg)}
        .firma-page{padding:18px 16px 110px}.firma-container{max-width:1500px;margin:0 auto}
        .firma-breadcrumb{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;color:var(--firma-muted);font-size:13px}
        .firma-breadcrumb a{color:var(--firma-primary);font-weight:600;text-decoration:none}.firma-breadcrumb a:hover,.firma-breadcrumb a:focus{text-decoration:underline}
        .firma-hero{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px;padding:20px;border:1px solid var(--firma-border);border-radius:12px;background:var(--firma-card);box-shadow:0 3px 12px rgba(34,51,45,.06)}
        .firma-hero-main{display:flex;align-items:flex-start;gap:14px}.firma-hero-icon{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;flex:0 0 46px;border-radius:12px;background:rgba(25,111,61,.10);color:var(--firma-primary);font-size:20px}
        .firma-title{margin:0 0 5px;color:var(--firma-text);font-size:24px;font-weight:700;line-height:1.25}.firma-subtitle{margin:0;color:var(--firma-muted);font-size:14px;line-height:1.5}
        .firma-status{display:inline-flex;align-items:center;gap:7px;white-space:nowrap;padding:7px 11px;border:1px solid #bfe2ca;border-radius:999px;background:#edf8f1;color:var(--firma-primary-dark);font-size:12px;font-weight:700}
        .firma-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;align-items:start}
        .firma-card{overflow:hidden;border:1px solid var(--firma-border);border-radius:12px;background:var(--firma-card);box-shadow:0 3px 12px rgba(34,51,45,.06)}
        .firma-card-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid var(--firma-border);background:#fbfcfb}
        .firma-card-title{display:flex;align-items:center;gap:9px;margin:0;color:var(--firma-text);font-size:15px;font-weight:700}.firma-version{color:var(--firma-muted);font-size:12px}
        .firma-document-frame{width:100%;height:calc(100vh - 300px);min-height:560px;border:0;background:#303030}.firma-frame-fallback{padding:12px 16px;border-top:1px solid var(--firma-border);background:#fff;color:var(--firma-muted);font-size:12px}
        .firma-side{position:sticky;top:12px}.firma-side-body{padding:18px}.firma-steps{margin:0 0 18px;padding:0;list-style:none}.firma-step{display:flex;gap:11px;margin-bottom:14px}.firma-step:last-child{margin-bottom:0}
        .firma-step-number{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;flex:0 0 28px;border-radius:50%;background:rgba(25,111,61,.11);color:var(--firma-primary);font-size:12px;font-weight:700}
        .firma-step-title{margin:0 0 2px;color:var(--firma-text);font-size:13px;font-weight:700}.firma-step-text{margin:0;color:var(--firma-muted);font-size:12px;line-height:1.45}.firma-divider{height:1px;margin:17px 0;background:var(--firma-border)}
        .firma-consent{position:relative;display:block;margin:0;padding:14px 14px 14px 47px;border:1px solid var(--firma-border);border-radius:9px;background:#fbfcfb;cursor:pointer;transition:border-color .2s ease,box-shadow .2s ease,background .2s ease}
        .firma-consent:hover{border-color:#a8cdb5;background:#f6fbf8}.firma-consent input{position:absolute;top:17px;left:16px;width:20px;height:20px;margin:0;accent-color:var(--firma-primary)}.firma-consent:focus-within{border-color:var(--firma-primary);box-shadow:0 0 0 3px var(--firma-focus)}
        .firma-consent-title{display:block;margin-bottom:4px;color:var(--firma-text);font-size:13px;font-weight:700}.firma-consent-text{display:block;color:var(--firma-muted);font-size:12px;line-height:1.5}.firma-legal{margin:12px 0 0;color:var(--firma-muted);font-size:11px;line-height:1.45}
        .firma-actions{display:grid;grid-template-columns:1fr;gap:9px;margin-top:16px}.firma-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:9px 15px;border:1px solid transparent;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none!important;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,background .15s ease}
        .firma-btn:focus{outline:none;box-shadow:0 0 0 3px var(--firma-focus)}.firma-btn-primary{background:var(--firma-primary);color:#fff}.firma-btn-primary:hover:not(:disabled){background:var(--firma-primary-dark);color:#fff;transform:translateY(-1px)}.firma-btn-primary:disabled{background:#a8b7ae;color:#eef2ef;cursor:not-allowed}
        .firma-btn-secondary{border-color:var(--firma-border);background:#fff;color:var(--firma-text)}.firma-btn-secondary:hover{border-color:#b8c4c9;background:#f6f8f7;color:var(--firma-text)}.firma-btn-link{background:transparent;color:var(--firma-primary)}.firma-btn-link:hover{background:rgba(25,111,61,.07);color:var(--firma-primary-dark)}
        .firma-security{display:flex;align-items:flex-start;gap:9px;margin-top:14px;padding:11px;border-radius:8px;background:#f3f6f4;color:var(--firma-muted);font-size:11px;line-height:1.45}.firma-security span{margin-top:2px;color:var(--firma-primary)}
        .firma-mobile-actions{display:none}
        @media(max-width:991.98px){.firma-page{padding:12px 10px 125px}.firma-hero{padding:16px}.firma-grid{grid-template-columns:1fr}.firma-side{position:static}.firma-document-frame{height:68vh;min-height:480px}.firma-mobile-actions{position:fixed;z-index:1040;right:0;bottom:0;left:0;display:flex;gap:9px;padding:10px;border-top:1px solid var(--firma-border);background:rgba(255,255,255,.97);box-shadow:0 -4px 14px rgba(27,44,35,.12)}.firma-mobile-actions .firma-btn{flex:1}}
        @media(max-width:575.98px){.firma-hero{flex-direction:column}.firma-title{font-size:20px}.firma-status{white-space:normal}.firma-document-frame{height:62vh;min-height:420px}.firma-card-header{align-items:flex-start;flex-direction:column}}
        @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important;animation:none!important}}
    </style>
</head>
<body>
<?php include("../menu_principal.php"); include("../menu_header.php"); ?>
<main class="contenido firma-page" id="contenido-principal">
    <div class="firma-container">
        <nav class="firma-breadcrumb" aria-label="Migas de pan">
            <a href="gestion_coaching.php?pagina=1&id=null&est=Pendientes">Coaching</a>
            <span aria-hidden="true">/</span>
            <a href="<?php echo validar_output($ruta_cancelar); ?>">Paquete <?php echo $paquete_codigo; ?></a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">Firma del documento</span>
        </nav>

        <section class="firma-hero" aria-labelledby="firma-page-title">
            <div class="firma-hero-main">
                <div class="firma-hero-icon" aria-hidden="true"><span class="fas fa-file-signature"></span></div>
                <div>
                    <h1 class="firma-title" id="firma-page-title">Revisar y firmar documento</h1>
                    <p class="firma-subtitle">Paquete <strong><?php echo $paquete_codigo; ?></strong>. Revise el documento completo antes de registrar su aceptación electrónica.</p>
                </div>
            </div>
            <?php if ($documento): ?><span class="firma-status"><span class="fas fa-clock" aria-hidden="true"></span>Pendiente de su firma</span><?php endif; ?>
        </section>

        <?php echo $respuesta_accion; ?>

        <?php if ($documento): ?>
        <div class="firma-grid">
            <section class="firma-card" aria-labelledby="documento-title">
                <header class="firma-card-header">
                    <h2 class="firma-card-title" id="documento-title"><span class="fas fa-file-pdf text-danger" aria-hidden="true"></span>Documento para revisión</h2>
                    <span class="firma-version">Versión <?php echo $documento_version; ?></span>
                </header>
                <iframe class="firma-document-frame" src="<?php echo validar_output($pdf_url); ?>#toolbar=1&navpanes=0&view=FitH" title="Documento PDF del paquete <?php echo $paquete_codigo; ?>" loading="eager"></iframe>
                <div class="firma-frame-fallback">¿El documento no se muestra correctamente? <a href="<?php echo validar_output($pdf_url); ?>" target="_blank" rel="noopener noreferrer">Abrir el PDF en una pestaña nueva</a>.</div>
            </section>

            <aside class="firma-card firma-side" aria-labelledby="firma-panel-title">
                <header class="firma-card-header"><h2 class="firma-card-title" id="firma-panel-title"><span class="fas fa-signature" aria-hidden="true"></span>Confirmación</h2></header>
                <div class="firma-side-body">
                    <ol class="firma-steps">
                        <li class="firma-step"><span class="firma-step-number">1</span><div><p class="firma-step-title">Revise el documento</p><p class="firma-step-text">Verifique los datos, compromisos y condiciones incluidas.</p></div></li>
                        <li class="firma-step"><span class="firma-step-number">2</span><div><p class="firma-step-title">Confirme su aceptación</p><p class="firma-step-text">Marque la casilla únicamente cuando haya leído todo el contenido.</p></div></li>
                        <li class="firma-step"><span class="firma-step-number">3</span><div><p class="firma-step-title">Registre la firma</p><p class="firma-step-text">El sistema almacenará fecha, hora y trazabilidad técnica.</p></div></li>
                    </ol>
                    <div class="firma-divider"></div>
                    <form id="form-firma" method="POST" action="" novalidate>
                        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION["_csrf_token"], ENT_QUOTES, "UTF-8"); ?>">
                        <input type="hidden" name="gcd_id" value="<?php echo (int) $documento["gcd_id"]; ?>">
                        <label class="firma-consent" for="aceptacion_documento">
                            <input type="checkbox" id="aceptacion_documento" name="aceptacion_documento" value="1" required aria-describedby="texto-aceptacion">
                            <span class="firma-consent-title">He leído y acepto el documento</span>
                            <span class="firma-consent-text" id="texto-aceptacion">Confirmo que revisé el contenido de este documento de Coaching y registro mi aceptación electrónica para este proceso interno.</span>
                        </label>
                        <p class="firma-legal">Esta acción es personal y no puede deshacerse desde esta pantalla. No cierre la ventana durante el procesamiento.</p>
                        <div class="firma-actions">
                            <button type="submit" class="firma-btn firma-btn-primary" id="btn-firmar" name="confirmar_firma" value="1" disabled><span class="fas fa-signature" aria-hidden="true"></span>Firmar documento</button>
                            <a class="firma-btn firma-btn-secondary" href="<?php echo validar_output($ruta_cancelar); ?>"><span class="fas fa-arrow-left" aria-hidden="true"></span>Volver sin firmar</a>
                            <a class="firma-btn firma-btn-link" href="<?php echo validar_output($pdf_url); ?>" target="_blank" rel="noopener noreferrer"><span class="fas fa-external-link-alt" aria-hidden="true"></span>Abrir PDF aparte</a>
                        </div>
                        <div class="firma-security"><span class="fas fa-shield-alt" aria-hidden="true"></span><div>La firma se vinculará a la versión vigente del documento y quedará registrada en el historial del paquete.</div></div>
                    </form>
                </div>
            </aside>
        </div>

        <div class="firma-mobile-actions" aria-label="Acciones de firma en dispositivo móvil">
            <a class="firma-btn firma-btn-secondary" href="<?php echo validar_output($ruta_cancelar); ?>">Cancelar</a>
            <button type="button" class="firma-btn firma-btn-primary" id="btn-firmar-mobile" disabled><span class="fas fa-signature" aria-hidden="true"></span>Firmar</button>
        </div>
        <?php else: ?>
        <section class="firma-card"><div class="firma-side-body text-center py-5"><span class="fas fa-file-excel fa-3x text-warning mb-3" aria-hidden="true"></span><h2 class="h5">Documento no disponible</h2><p class="text-muted">El paquete no tiene un documento vigente que pueda ser firmado.</p><a class="firma-btn firma-btn-secondary d-inline-flex" href="<?php echo validar_output($ruta_cancelar); ?>">Volver al paquete</a></div></section>
        <?php endif; ?>
    </div>
</main>
<?php include("../footer.php"); include("../config/configuracion_js.php"); ?>
<?php if ($documento): ?>
<script>
(function(){"use strict";var checkbox=document.getElementById("aceptacion_documento");var form=document.getElementById("form-firma");var button=document.getElementById("btn-firmar");var mobileButton=document.getElementById("btn-firmar-mobile");var submitting=false;function syncButtons(){var enabled=checkbox.checked&&!submitting;button.disabled=!enabled;if(mobileButton){mobileButton.disabled=!enabled}}checkbox.addEventListener("change",syncButtons);if(mobileButton){mobileButton.addEventListener("click",function(){if(!mobileButton.disabled){button.click()}})}form.addEventListener("submit",function(event){if(!checkbox.checked){event.preventDefault();checkbox.focus();return}if(submitting){event.preventDefault();return}var confirmed=window.confirm("¿Confirma que leyó el documento y desea registrar su firma electrónica?");if(!confirmed){event.preventDefault();return}submitting=true;button.disabled=true;button.innerHTML='<span class="fas fa-spinner fa-spin" aria-hidden="true"></span> Firmando...';if(mobileButton){mobileButton.disabled=true;mobileButton.innerHTML='<span class="fas fa-spinner fa-spin" aria-hidden="true"></span> Procesando...'}});syncButtons()}());
</script>
<?php endif; ?>
</body>
</html>
