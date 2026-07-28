<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Coaching";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("lib/coaching_seguridad.php");

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Coaching | Bandeja";
    $pagina = validar_input($_GET['pagina']);

    $perfil_coaching = coachingPerfilUsuarioActual();
    if ($perfil_coaching === null) {
        header("Location:../permiso_denegado.php");
        exit;
    }

    // Filtro de estado por pestaña (mismo patrón que gestion_alertas.php: ?est=)
    $estado_bandeja = validar_input($_GET['est']);

    // Es una petición AJAX de "solo la bandeja" (pestañas+tabla+paginación),
    // sin el resto de la página (menú, cabecera, buscador). Esto es lo que
    // hace que cambiar de pestaña/buscar/paginar sea atómico: solo se
    // reemplaza este bloque, nunca se recarga la página completa.
    $es_ajax = ($_GET['ajax'] ?? '') === '1';

    $data_consulta = array();

    // Alcance por rol: nunca se confía solo en lo que el frontend oculte.
    [$filtro_alcance_sql, $parametros_alcance] = coachingFiltroAlcance($perfil_coaching, $_SESSION['usu_id']);
    foreach ($parametros_alcance as $p) {
        array_push($data_consulta, $p);
    }

    // Filtro de estado (pestañas: Pendientes de mi acción / En seguimiento / Cerrados / Todos)
    $filtro_estado_sql = "";
    if ($estado_bandeja === "Pendientes") {
        $filtro_estado_sql = " AND E.`gce_codigo` IN ('ASIGNADO','PENDIENTE_SUPERVISOR','PENDIENTE_AGENTE','RESPONDIDO_AGENTE','PENDIENTE_FIRMA_AGENTE','PENDIENTE_CIERRE','REFUTADO')";
    } elseif ($estado_bandeja === "Seguimiento") {
        $filtro_estado_sql = " AND E.`gce_codigo` = 'EN_SEGUIMIENTO'";
    } elseif ($estado_bandeja === "Cerrados") {
        $filtro_estado_sql = " AND E.`gce_codigo` IN ('CERRADO','RECHAZADO')";
    }
    // "Todos" no agrega filtro de estado.

    // Búsqueda libre (código de paquete, nombre de agente o supervisor).
    // Con la bandeja atómica, la búsqueda ya no depende de un POST — llega
    // como parámetro GET normal (?id=...), igual que las pestañas y la
    // paginación, para que todo se pueda pedir por AJAX de la misma forma.
    $filtro_permanente = validar_input($_GET['id'] ?? 'null');

    $filtro_buscar_sql = "";
    if ($filtro_permanente != "null" && $filtro_permanente != "") {
        $filtro_buscar_sql = "AND (`gcp_id` LIKE ? OR TA.`usu_nombres_apellidos` LIKE ? OR TS.`usu_nombres_apellidos` LIKE ? OR T.`gct_nombre` LIKE ?)";
        for ($i = 0; $i < 4; $i++) {
            array_push($data_consulta, "%$filtro_permanente%");
        }
    }

    // Paginación
    $registros_x_pagina = 50;
    $iniciar_pagina = ($pagina - 1) * $registros_x_pagina;

    $consulta_base_from =
        "FROM `tb_gestion_coaching_paquete`
         LEFT JOIN `tb_gestion_coaching_estado` AS E ON `tb_gestion_coaching_paquete`.`gcp_estado_id` = E.`gce_id`
         LEFT JOIN `tb_gestion_coaching_tipo` AS T ON `tb_gestion_coaching_paquete`.`gcp_tipo_id` = T.`gct_id`
         LEFT JOIN `tb_administrador_usuario` AS TA ON `tb_gestion_coaching_paquete`.`gcp_agente_id` = TA.`usu_id`
         LEFT JOIN `tb_administrador_usuario` AS TS ON `tb_gestion_coaching_paquete`.`gcp_supervisor_id` = TS.`usu_id`
         WHERE `gcp_activo` = 1 " . $filtro_alcance_sql . " " . $filtro_estado_sql . " " . $filtro_buscar_sql;

    // Conteo total para paginación
    $consulta_contar_string = "SELECT COUNT(`gcp_id`) " . $consulta_base_from;
    $consulta_contar = $enlace_db->prepare($consulta_contar_string);
    if (count($data_consulta) > 0) {
        $consulta_contar->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    $consulta_contar->execute();
    $resultado_contar = $consulta_contar->get_result()->fetch_all(MYSQLI_NUM);
    $registros_cantidad_total = $resultado_contar[0][0];
    $numero_paginas = ceil($registros_cantidad_total / $registros_x_pagina);

    if (($pagina > $numero_paginas && $numero_paginas > 0) || $pagina <= 0) {
        if ($es_ajax) {
            $pagina = 1;
            $iniciar_pagina = 0;
        } else {
            header('Location:gestion_coaching.php?pagina=1&id=null&est=Pendientes');
            exit;
        }
    }

    $data_consulta_pagina = $data_consulta;
    array_push($data_consulta_pagina, $iniciar_pagina);
    array_push($data_consulta_pagina, $registros_x_pagina);

    $consulta_string =
        "SELECT `gcp_id`, `gcp_origen_tipo`, T.`gct_nombre`, E.`gce_nombre`, E.`gce_codigo`,
                TA.`usu_nombres_apellidos` AS agente_nombre, TS.`usu_nombres_apellidos` AS supervisor_nombre,
                `gcp_fecha_limite`, `gcp_prioridad`, `gcp_registro_fecha`
         " . $consulta_base_from . "
         ORDER BY `gcp_registro_fecha` DESC LIMIT ?,?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param(str_repeat("s", count($data_consulta_pagina)), ...$data_consulta_pagina);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    // Conteo por pestaña (para mostrar badges), respetando el alcance del usuario
    $consulta_base_from_conteo = str_replace($filtro_estado_sql, "", $consulta_base_from);
    $consulta_conteo_string =
        "SELECT E.`gce_codigo`, COUNT(`gcp_id`)
         " . $consulta_base_from_conteo . "
         GROUP BY E.`gce_codigo`";
    $consulta_conteo = $enlace_db->prepare($consulta_conteo_string);
    if (count($data_consulta) > 0) {
        $consulta_conteo->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
    }
    $consulta_conteo->execute();
    $resultado_conteo = $consulta_conteo->get_result()->fetch_all(MYSQLI_NUM);
    $array_conteo_estado = array();
    foreach ($resultado_conteo as $fila_conteo) {
        $array_conteo_estado[$fila_conteo[0]] = (int) $fila_conteo[1];
    }

    /**
     * Clase de badge por estado — mismo criterio visual que gestion_coaching_ver.php
     * (pastilla: borde de color + fondo tenue + texto de color).
     */
    function claseEstadoCoachingBandeja(string $gce_codigo): string
    {
        $mapa = [
            'BORRADOR'               => 'coaching_estado_gris',
            'ASIGNADO'                => 'coaching_estado_azul',
            'PENDIENTE_SUPERVISOR'    => 'coaching_estado_azul',
            'PENDIENTE_AGENTE'        => 'coaching_estado_naranja',
            'RESPONDIDO_AGENTE'       => 'coaching_estado_azul',
            'PENDIENTE_FIRMA_AGENTE'  => 'coaching_estado_naranja',
            'REFUTADO'                => 'coaching_estado_rojo',
            'EN_SEGUIMIENTO'          => 'coaching_estado_morado',
            'PENDIENTE_CIERRE'        => 'coaching_estado_azul',
            'CERRADO'                 => 'coaching_estado_verde',
            'RECHAZADO'               => 'coaching_estado_rojo',
            'ANULADO'                 => 'coaching_estado_gris',
        ];
        return $mapa[$gce_codigo] ?? 'coaching_estado_gris';
    }

    /** Arma la URL de bandeja con los 3 parámetros que siempre viajan juntos. */
    function coachingUrlBandeja(int $pagina, string $id, string $est, bool $ajax = false): string
    {
        return 'gestion_coaching.php?pagina=' . $pagina . '&id=' . urlencode($id) . '&est=' . urlencode($est) . ($ajax ? '&ajax=1' : '');
    }

    /**
     * Bloque de pestañas + tabla + paginación — se reutiliza tal cual para
     * la respuesta normal (dentro de la página completa) y para la
     * respuesta AJAX (solo este fragmento). Es literalmente la misma
     * función la que arma las dos: no hay 2 copias del HTML que puedan
     * desincronizarse entre sí.
     */
    function pintarBloqueBandeja(
        string $estado_bandeja, string $filtro_permanente, int $pagina, int $numero_paginas,
        array $array_conteo_estado, array $resultado_registros, string $perfil_coaching
    ): void {
?>
        <div class="coaching_pills_tabs" id="bandeja_tabs">
            <a class="coaching_pill coaching_tab_link <?php echo ($estado_bandeja == 'Pendientes' || $estado_bandeja == '') ? 'activa' : ''; ?>" data-est="Pendientes" href="<?php echo coachingUrlBandeja(1, $filtro_permanente, 'Pendientes'); ?>">
                <span class="fas fa-inbox"></span> Pendientes de acción
            </a>
            <a class="coaching_pill coaching_tab_link <?php echo $estado_bandeja == 'Seguimiento' ? 'activa' : ''; ?>" data-est="Seguimiento" href="<?php echo coachingUrlBandeja(1, $filtro_permanente, 'Seguimiento'); ?>">
                <span class="fas fa-chart-line"></span> En seguimiento
                <span class="coaching_pill_contador"><?php echo $array_conteo_estado['EN_SEGUIMIENTO'] ?? 0; ?></span>
            </a>
            <a class="coaching_pill coaching_tab_link <?php echo $estado_bandeja == 'Cerrados' ? 'activa' : ''; ?>" data-est="Cerrados" href="<?php echo coachingUrlBandeja(1, $filtro_permanente, 'Cerrados'); ?>">
                <span class="fas fa-check-circle"></span> Cerrados
            </a>
            <a class="coaching_pill coaching_tab_link <?php echo $estado_bandeja == 'Todos' ? 'activa' : ''; ?>" data-est="Todos" href="<?php echo coachingUrlBandeja(1, $filtro_permanente, 'Todos'); ?>">
                <span class="fas fa-layer-group"></span> Todos
            </a>
        </div>

        <div class="div_tabla mt-3">
            <table class="tabla_list coaching_tabla">
                <thead>
                    <tr>
                        <th class="col-izq">Código</th>
                        <th class="col-centro">Origen</th>
                        <th class="col-centro">Tipo</th>
                        <th class="col-izq">Agente</th>
                        <th class="col-izq">Supervisor</th>
                        <th class="col-centro">Estado</th>
                        <th class="col-centro">Fecha límite</th>
                        <th class="col-centro">Prioridad</th>
                        <th class="col-centro">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($resultado_registros) == 0): ?>
                        <tr><td colspan="9" class="text-center coaching_empty_mini">No hay paquetes de coaching para mostrar en esta vista.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($resultado_registros as $fila):
                        $vencido = $fila[7] && strtotime($fila[7]) < strtotime(date('Y-m-d')) && !in_array($fila[4], ['CERRADO', 'RECHAZADO', 'ANULADO'], true);
                    ?>
                        <tr class="tabla_contenido_1" onclick="window.location='gestion_coaching_ver.php?reg=<?php echo base64_encode($fila[0]); ?>'">
                            <td class="col-izq"><strong><?php echo validar_output($fila[0]); ?></strong></td>
                            <td class="col-centro"><?php echo validar_output(ucfirst($fila[1])); ?></td>
                            <td class="col-centro"><?php echo validar_output($fila[2]); ?></td>
                            <td class="col-izq"><?php echo validar_output($fila[5] ?? '—'); ?></td>
                            <td class="col-izq"><?php echo validar_output($fila[6] ?? '—'); ?></td>
                            <td class="col-centro">
                                <span class="coaching_estado_pill <?php echo claseEstadoCoachingBandeja($fila[4]); ?>"><?php echo validar_output($fila[3]); ?></span>
                            </td>
                            <td class="col-centro">
                                <?php if (!$fila[7]): ?>
                                    —
                                <?php elseif ($vencido): ?>
                                    <span class="coaching_vencido"><span class="fas fa-exclamation-triangle"></span> <?php echo date('d/m/Y', strtotime($fila[7])); ?></span>
                                <?php else: ?>
                                    <?php echo date('d/m/Y', strtotime($fila[7])); ?>
                                <?php endif; ?>
                            </td>
                            <td class="col-centro"><?php echo validar_output($fila[8]); ?></td>
                            <td class="col-centro" onclick="event.stopPropagation();">
                                <a href="gestion_coaching_ver.php?reg=<?php echo base64_encode($fila[0]); ?>" class="btn-corp coaching_btn_ver" title="Ver detalle">
                                    <span class="fas fa-eye"></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($numero_paginas > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $numero_paginas; $p++): ?>
                        <li class="page-item <?php echo $p == $pagina ? 'active' : ''; ?>">
                            <a class="page-link coaching_pagina_link" data-pagina="<?php echo $p; ?>" href="<?php echo coachingUrlBandeja($p, $filtro_permanente, $estado_bandeja); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
<?php
    }

    // ---- Respuesta AJAX: SOLO el fragmento, nada de <html>/menú/etc. ----
    if ($es_ajax) {
        pintarBloqueBandeja($estado_bandeja, $filtro_permanente, (int) $pagina, (int) $numero_paginas, $array_conteo_estado, $resultado_registros, $perfil_coaching);
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php include("../config/configuracion_estilos.php"); ?>
    <style>
        .coaching_estado_pill { display: inline-block; font-size: 11px; padding: 2px 10px 2px 10px; border-radius: 10px; font-weight: normal; white-space: nowrap; }
        .coaching_estado_verde   { color: #00BF6F; border: solid 1px #00BF6F; background-color: rgba(40, 180, 99, 0.15); }
        .coaching_estado_naranja { color: #F39C12; border: solid 1px #F39C12; background-color: rgba(243, 156, 18, 0.15); }
        .coaching_estado_azul    { color: #175E83; border: solid 1px #175E83; background-color: rgba(23, 94, 131, 0.15); }
        .coaching_estado_morado  { color: #6C3483; border: solid 1px #6C3483; background-color: rgba(108, 52, 131, 0.15); }
        .coaching_estado_rojo    { color: #FF0000; border: solid 1px #FF0000; background-color: rgba(255, 0, 0, 0.15); }
        .coaching_estado_gris    { color: #6E6E6E; border: solid 1px #6E6E6E; background-color: rgba(110, 110, 110, 0.15); }

        .coaching_vencido    { color: #FF0000; font-weight: bold; }
        .coaching_por_vencer { color: #F39C12; font-weight: bold; }

        .coaching_empty_mini { color: #6E6E6E; font-size: 12px; padding: 10px; }

        .coaching_tabla th, .coaching_tabla td { vertical-align: middle; padding: 8px 10px; }
        .coaching_tabla td.col-centro, .coaching_tabla th.col-centro { text-align: center; }
        .coaching_tabla td.col-izq, .coaching_tabla th.col-izq { text-align: left; }

        .coaching_tabla tbody tr.tabla_contenido_1:hover { cursor: pointer; }

        .coaching_btn_ver { width: 26px !important; height: 26px !important; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; border-radius: 5px; }

        #bandeja_ajax { position: relative; min-height: 120px; transition: opacity .12s; }
        #bandeja_ajax.cargando { opacity: .45; pointer-events: none; }
        #bandeja_spinner {
            display: none; position: absolute; top: 10px; right: 10px; z-index: 3;
            color: #4CAF50; font-size: 18px;
        }
        #bandeja_ajax.cargando #bandeja_spinner { display: block; }

        .coaching_buscador_caja {
            display: flex; align-items: center; background: #FFFFFF; border: 1px solid #F2F2F2;
            border-radius: 20px; padding: 0 6px 0 14px; transition: border-color .15s, box-shadow .15s;
        }
        .coaching_buscador_caja:focus-within { border-color: #4CAF50; box-shadow: 0 0 0 3px rgba(76,175,80,.12); }
        .coaching_buscador_icono { color: #B0B4B8; font-size: 13px; margin-right: 8px; }
        .coaching_buscador_caja:focus-within .coaching_buscador_icono { color: #4CAF50; }
        .coaching_buscador_caja input {
            flex: 1; border: 0; outline: none; font-size: 12px; padding: 9px 0; background: transparent; color: #1A1A1A;
        }
        .coaching_buscador_caja input::placeholder { color: #B0B4B8; }
        .coaching_buscador_caja button {
            background: none; border: 0; color: #6E6E6E; width: 30px; height: 30px; border-radius: 50%;
            cursor: pointer; transition: background .15s, color .15s;
        }
        .coaching_buscador_caja button:hover { background: #F2F2F2; color: #4CAF50; }

        .coaching_pills_tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
        .coaching_pill {
            display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: bold;
            color: #6E6E6E; background: #F2F2F2; border-radius: 20px; padding: 8px 16px;
            text-decoration: none; transition: background .15s, color .15s;
        }
        .coaching_pill:hover { background: #E3F4E4; color: #4CAF50; text-decoration: none; }
        .coaching_pill.activa { background: #4CAF50; color: #FFFFFF; }
        .coaching_pill_contador {
            background: rgba(255,255,255,.35); border-radius: 10px; padding: 1px 7px; font-size: 10px;
        }
        .coaching_pill:not(.activa) .coaching_pill_contador { background: #FFFFFF; color: #4CAF50; }
    </style>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
        include("lib/coaching_widget_flotante.php");
    ?>
    <div class="contenido">
        <div class="row align-items-center" id="elemento_1">
            <div class="col-md-5 py-2">
                <div class="coaching_buscador_caja">
                    <span class="fas fa-search coaching_buscador_icono"></span>
                    <input type="text" name="id_filtro" id="coaching_buscador" value="<?php echo $filtro_permanente != 'null' ? htmlspecialchars($filtro_permanente) : ''; ?>" placeholder="Buscar por código, agente, supervisor o tipo..." autofocus>
                    <button type="button" id="btn_refrescar_bandeja" title="Limpiar y refrescar"><span class="fas fa-sync-alt"></span></button>
                </div>
                <button type="button" id="btn_buscar_bandeja" class="d-none"></button>
            </div>
            <div class="col-md-7 py-2 text-right">
                <?php if (in_array($perfil_coaching, ['Administrador', 'Gestor', 'Supervisor'], true)): ?>
                    <a href="gestion_coaching_crear.php" class="btn-corp px-3 py-2" style="border-radius:5px;"><span class="fas fa-plus"></span> Nuevo paquete</a>
                <?php endif; ?>
            </div>
        </div>

        <div id="bandeja_ajax" data-est="<?php echo htmlspecialchars($estado_bandeja); ?>" data-id="<?php echo htmlspecialchars($filtro_permanente); ?>" data-pagina="<?php echo (int) $pagina; ?>">
            <span id="bandeja_spinner"><span class="fas fa-spinner fa-spin"></span></span>
            <?php pintarBloqueBandeja($estado_bandeja, $filtro_permanente, (int) $pagina, (int) $numero_paginas, $array_conteo_estado, $resultado_registros, $perfil_coaching); ?>
        </div>
    </div>
    <?php include("../footer.php"); ?>

    <script>
    (function () {
        var contenedor = document.getElementById('bandeja_ajax');
        var campoBuscar = document.getElementById('coaching_buscador');
        var CACHE_URL_BASE = 'gestion_coaching.php';
        var temporizadorBusqueda = null;

        function cargarBandeja(pagina, id, est, actualizarUrl) {
            contenedor.classList.add('cargando');
            var urlDatos = CACHE_URL_BASE + '?pagina=' + encodeURIComponent(pagina) + '&id=' + encodeURIComponent(id || 'null') + '&est=' + encodeURIComponent(est) + '&ajax=1';

            fetch(urlDatos, { credentials: 'same-origin' })
                .then(function (resp) {
                    if (!resp.ok) { throw new Error('No fue posible cargar la bandeja.'); }
                    return resp.text();
                })
                .then(function (html) {
                    contenedor.innerHTML = '<span id="bandeja_spinner"><span class="fas fa-spinner fa-spin"></span></span>' + html;
                    contenedor.dataset.est = est;
                    contenedor.dataset.id = id || 'null';
                    contenedor.dataset.pagina = pagina;
                    contenedor.classList.remove('cargando');
                    vincularEventosBandeja();

                    if (actualizarUrl) {
                        var urlVisible = CACHE_URL_BASE + '?pagina=' + pagina + '&id=' + encodeURIComponent(id || 'null') + '&est=' + encodeURIComponent(est);
                        window.history.pushState({ pagina: pagina, id: id, est: est }, '', urlVisible);
                    }
                })
                .catch(function () {
                    contenedor.classList.remove('cargando');
                    if (window.alertify) { alertify.warning('No fue posible actualizar la bandeja. Intente de nuevo.', 0); }
                });
        }

        function vincularEventosBandeja() {
            contenedor.querySelectorAll('.coaching_tab_link').forEach(function (enlace) {
                enlace.addEventListener('click', function (e) {
                    e.preventDefault();
                    cargarBandeja(1, campoBuscar.value.trim() || 'null', this.dataset.est, true);
                });
            });
            contenedor.querySelectorAll('.coaching_pagina_link').forEach(function (enlace) {
                enlace.addEventListener('click', function (e) {
                    e.preventDefault();
                    cargarBandeja(parseInt(this.dataset.pagina, 10), contenedor.dataset.id, contenedor.dataset.est, true);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        function ejecutarBusqueda() {
            var valor = campoBuscar.value.trim() || 'null';
            cargarBandeja(1, valor, contenedor.dataset.est, true);
        }
        campoBuscar.addEventListener('input', function () {
            clearTimeout(temporizadorBusqueda);
            temporizadorBusqueda = setTimeout(ejecutarBusqueda, 450);
        });
        campoBuscar.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); clearTimeout(temporizadorBusqueda); ejecutarBusqueda(); }
        });
        document.getElementById('btn_buscar_bandeja').addEventListener('click', function () {
            clearTimeout(temporizadorBusqueda);
            ejecutarBusqueda();
        });
        document.getElementById('btn_refrescar_bandeja').addEventListener('click', function () {
            campoBuscar.value = '';
            cargarBandeja(1, 'null', contenedor.dataset.est, true);
        });

        window.addEventListener('popstate', function () {
            var params = new URLSearchParams(window.location.search);
            cargarBandeja(params.get('pagina') || 1, params.get('id') || 'null', params.get('est') || 'Pendientes', false);
        });

        vincularEventosBandeja();
    })();
    </script>
</body>
</html>
