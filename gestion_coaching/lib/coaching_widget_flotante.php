<?php
declare(strict_types=1);

/**
 * gestion_coaching/lib/coaching_widget_flotante.php
 *
 * Alerta flotante NO intrusiva: aparece en una esquina, no bloquea el
 * contenido. Al cerrarla, se silencia solo por unas horas (no para toda
 * la sesión ni para siempre) — si sigue pendiente, vuelve a aparecer
 * automáticamente sin que el usuario tenga que cerrar sesión.
 *
 * Uso: `include("lib/coaching_widget_flotante.php");` en cualquier
 * pantalla ya autenticada del sistema (no solo dentro de Coaching) — se
 * autoprotege: si el usuario no tiene perfil de Coaching o no tiene
 * pendientes, no imprime nada.
 *
 * Requiere que $enlace_db y $_SESSION['usu_id'] ya existan (igual que el
 * resto del sistema).
 */

if (!function_exists('coachingPerfilUsuarioActual')) {
    require_once __DIR__ . '/coaching_seguridad.php';
}

$coaching_widget_perfil = coachingPerfilUsuarioActual();

if ($coaching_widget_perfil !== null && isset($enlace_db, $_SESSION['usu_id'])) {
    $coaching_widget_detalle = coachingPendientesDetalle($enlace_db, $_SESSION['usu_id'], $coaching_widget_perfil);
    $coaching_widget_pendientes = $coaching_widget_detalle['total'];

    if ($coaching_widget_pendientes > 0) {
        $coaching_widget_texto = $coaching_widget_pendientes === 1
            ? 'Tiene 1 paquete de Coaching pendiente de su acción.'
            : "Tiene {$coaching_widget_pendientes} paquetes de Coaching pendientes de su acción.";
        $coaching_widget_url_directa = $coaching_widget_detalle['unico_gcp_id'] !== null
            ? 'gestion_coaching_ver.php?reg=' . base64_encode($coaching_widget_detalle['unico_gcp_id'])
            : 'gestion_coaching.php?pagina=1&id=null&est=Pendientes';
        $coaching_widget_texto_link = $coaching_widget_detalle['unico_gcp_id'] !== null ? 'Ver paquete →' : 'Ver bandeja →';
        ?>
        <div id="coaching-alerta-flotante" style="
            display:none;
            position:fixed;
            bottom:20px;
            right:20px;
            max-width:300px;
            background:#FFFFFF;
            border:1px solid #4CAF50;
            border-left:4px solid #4CAF50;
            border-radius:6px;
            box-shadow:0 2px 10px rgba(0,0,0,.15);
            padding:12px 14px;
            font-size:12px;
            color:#1A1A1A;
            z-index:99999;
            font-family:'Lato', Arial, sans-serif;
        ">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                <div>
                    <span class="fas fa-graduation-cap" style="color:#4CAF50;"></span>
                    <strong>Coaching</strong>
                    <div style="margin-top:4px; color:#333;"><?php echo htmlspecialchars($coaching_widget_texto); ?></div>
                    <a href="#" id="coaching-alerta-flotante-link" style="display:inline-block; margin-top:8px; color:#4CAF50; font-weight:bold; text-decoration:none;"><?php echo htmlspecialchars($coaching_widget_texto_link); ?></a>
                </div>
                <span id="coaching-alerta-flotante-cerrar" title="Recordarme más tarde" style="cursor:pointer; color:#9aa0a6; font-size:14px; line-height:1;">
                    <span class="fas fa-times"></span>
                </span>
            </div>
        </div>
        <script>
        (function () {
            var CLAVE = 'coaching_alerta_cerrada_hasta';
            var HORAS_SILENCIO = 3; // vuelve a aparecer sola tras este tiempo, sin depender de cerrar sesión

            var cerradaHasta = parseInt(sessionStorage.getItem(CLAVE) || '0', 10);
            if (Date.now() < cerradaHasta) { return; }

            var caja = document.getElementById('coaching-alerta-flotante');
            var enlace = document.getElementById('coaching-alerta-flotante-link');
            var enGestionCoaching = window.location.pathname.indexOf('/gestion_coaching/') !== -1;
            enlace.href = (enGestionCoaching ? '' : 'gestion_coaching/') + <?php echo json_encode($coaching_widget_url_directa); ?>;

            setTimeout(function () { caja.style.display = 'block'; }, 400);

            document.getElementById('coaching-alerta-flotante-cerrar').addEventListener('click', function () {
                caja.style.display = 'none';
                sessionStorage.setItem(CLAVE, String(Date.now() + HORAS_SILENCIO * 60 * 60 * 1000));
            });
        })();
        </script>
        <?php
    }
}
