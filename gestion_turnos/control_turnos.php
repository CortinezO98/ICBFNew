<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Turnos | Turno Realizado";
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    // Inicializa variable tipo array
    $data_consulta_supervisor=array();
    $data_consulta_usuarios=array();
    $data_consulta_turnos=array();
    
    //AGREGAR NÚMERO AL DÍA, SEGÚN SEMANA SELECCIONADA
    $dias_semana = array();
    for ($i=0; $i < 7; $i++) { 
        array_push($dias_semana, date("Y-m-d", strtotime("first day", strtotime($FechaInicio . $i))));
    }
    $fecha_fin_consulta = date("Y-m-d", strtotime("+ 1 day", strtotime($dias_semana[6])))." 23:59:59";

    if ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor" OR $perfil_modulo=="Formador" OR $perfil_modulo=="Visitante" OR $perfil_modulo=="Cliente" OR $perfil_modulo=="Usuario") {
        $filtro_supervisor="";
    } elseif($perfil_modulo=="Supervisor"){
        $filtro_supervisor=" AND (`usu_supervisor`=?)";
        array_push($data_consulta_supervisor, $_SESSION["usu_id"]);
        array_push($data_consulta_usuarios, $_SESSION["usu_id"]);
    }

    $consulta_string_areas="SELECT DISTINCT `usu_campania`, TC.`ac_nombre_campania` FROM `tb_administrador_usuario` LEFT JOIN `tb_administrador_campania` AS TC ON `tb_administrador_usuario`.`usu_campania`=TC.`ac_id` WHERE `usu_estado`<>'Retirado' ".$filtro_supervisor." ORDER BY TC.`ac_nombre_campania` ASC";
    $consulta_registros_areas = $enlace_db->prepare($consulta_string_areas);
    if (count($data_consulta_supervisor)>0) {
        $consulta_registros_areas->bind_param(str_repeat("s", count($data_consulta_supervisor)), ...$data_consulta_supervisor);
    }
        
    $consulta_registros_areas->execute();
    $resultado_registros_areas = $consulta_registros_areas->get_result()->fetch_all(MYSQLI_NUM);

    // Ejemplo filtro campo buscar
    if (isset($_POST["filtro"])) {
        $filtro_permanente=validar_input($_POST['id_filtro']);
        $FechaInicio = $_POST["filtro_fecha"];
        $filtro_operacion = $_POST["operacion"];
        $filtro_estado = $_POST["estado"];
    } else {
        $filtro_permanente=validar_input($_GET['id']);
        $FechaInicio = base64_decode($_GET['fechainicio']);
        $filtro_operacion = $resultado_registros_areas[0][0];
        $filtro_estado = base64_decode($_GET['estado']);
    }

    if ($filtro_operacion!='Todas') {
        $filtro_usuario_operacion='AND (`usu_campania`=?)';
        array_push($data_consulta_usuarios, $filtro_operacion);
    }

    if ($filtro_estado=='Conectado') {
        $filtro_usuario_estado="AND TT.`cot_inicio`<>''";
    } elseif ($filtro_estado=='Desconectado') {
        $filtro_usuario_estado="AND TT.`cot_inicio` IS NULL";
    } else {
        $filtro_usuario_estado="";
    }

    if (date('Y-m', strtotime($FechaInicio))==date('Y-m')) {
        $filtro_usuario_estado_retiro=" AND `usu_estado`<>'Retirado'";
    } else {
        $filtro_usuario_estado_retiro="";
    }

    // Valida que filtro se deba ejecutar
    if ($filtro_permanente!="null" AND $filtro_permanente!="") {
        $filtro_buscar="AND (`usu_id` LIKE ? OR `usu_nombres_apellidos` LIKE ?)";

        //Contar catidad de variables a filtrar
        $cantidad_filtros=count(explode('?', $filtro_buscar))-1;

        //Agregar catidad de variables a filtrar a data consulta
        for ($i=0; $i < $cantidad_filtros; $i++) { 
            array_push($data_consulta_usuarios, "%$filtro_permanente%");//Se agrega llave por ser variable evaluada en un like
        }
    }

    $consulta_string_usuarios="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_estado`, TC.`ac_nombre_campania`, TT.`cot_inicio` FROM `tb_administrador_usuario` LEFT JOIN `tb_administrador_campania` AS TC ON `tb_administrador_usuario`.`usu_campania`=TC.`ac_id` LEFT JOIN (SELECT `cot_id`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion` FROM `tb_control_turno` WHERE `cot_inicio` LIKE '%".$FechaInicio."%') AS TT ON `tb_administrador_usuario`.`usu_id`=TT.`cot_usuario` WHERE 1=1 ".$filtro_usuario_estado_retiro." ".$filtro_usuario_estado." ".$filtro_supervisor." ".$filtro_usuario_operacion." ".$filtro_buscar." ORDER BY TC.`ac_nombre_campania` ASC";
    $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
    if (count($data_consulta_usuarios)>0) {
        $consulta_registros_usuarios->bind_param(str_repeat("s", count($data_consulta_usuarios)), ...$data_consulta_usuarios);
    }
    $consulta_registros_usuarios->execute();
    $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

    $filtro_usuarios_turno='';
    if (count($resultado_registros_usuarios)>0) {
        for ($i=0; $i < count($resultado_registros_usuarios); $i++) { 
            $filtro_usuarios_turno.="`cotm_usuario`='".$resultado_registros_usuarios[$i][0]."' OR ";
        }
        $filtro_usuarios_turno='AND ('.substr($filtro_usuarios_turno, 0, -4).')';
    }

    array_push($data_consulta_turnos, "$FechaInicio%");

    //CONSULTA TURNO PROGRAMADO
    $consulta_string_turno_programado="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha` FROM `tb_control_turno_malla` WHERE `cotm_inicio` LIKE ? ".$filtro_usuarios_turno." ORDER BY `cotm_usuario`, `cotm_inicio`";

    $consulta_registros_turno_programado = $enlace_db->prepare($consulta_string_turno_programado);
    if (count($data_consulta_turnos)>0) {
        $consulta_registros_turno_programado->bind_param(str_repeat("s", count($data_consulta_turnos)), ...$data_consulta_turnos);
    }
    $consulta_registros_turno_programado->execute();
    $resultado_registros_turno_programado = $consulta_registros_turno_programado->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_turno_programado); $i++) { 
        $fecha_turno=date('Y-m-d', strtotime($resultado_registros_turno_programado[$i][3]));
        if ($resultado_registros_turno_programado[$i][2]=="turno") {
            $hora_inicio=date('H:i', strtotime($resultado_registros_turno_programado[$i][3]));
            $hora_fin=date('H:i', strtotime($resultado_registros_turno_programado[$i][4]));

            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['turno'][$fecha_turno]=$hora_inicio.'-'.$hora_fin;
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['fecha_inicio'][$fecha_turno]=$resultado_registros_turno_programado[$i][3];
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['fecha_fin'][$fecha_turno]=$resultado_registros_turno_programado[$i][4];
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['turno_descripcion'][$fecha_turno]=$resultado_registros_turno_programado[$i][3].' A '.$resultado_registros_turno_programado[$i][4];
            $array_turnos_malla[]=$hora_inicio.'-'.$hora_fin;
            $array_turnos_diario[$fecha_turno][]=$hora_inicio.'-'.$hora_fin;
            $array_turnos_diario_cont[$fecha_turno][$hora_inicio.'-'.$hora_fin]+=1;
            $array_turnos_detalle[$hora_inicio.'-'.$hora_fin]['descripcion']=$hora_inicio.'-'.$hora_fin;

            $duracion_programado = dateDiff($resultado_registros_turno_programado[$i][3],$resultado_registros_turno_programado[$i][4]);
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['duracion'][$fecha_turno]=$duracion_programado;
        } else {
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['turno'][$fecha_turno]=$array_convenciones[$resultado_registros_turno_programado[$i][2]];
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['turno_descripcion'][$fecha_turno]=$array_convenciones[$resultado_registros_turno_programado[$i][2]];
            $array_turnos_malla[]=$array_convenciones[$resultado_registros_turno_programado[$i][2]];
            $array_turnos_diario[$fecha_turno][]=$array_convenciones[$resultado_registros_turno_programado[$i][2]];
            $array_turnos_diario_cont[$fecha_turno][$array_convenciones[$resultado_registros_turno_programado[$i][2]]]+=1;
            $array_turnos_detalle[$array_convenciones[$resultado_registros_turno_programado[$i][2]]]['descripcion']=$array_convenciones[$resultado_registros_turno_programado[$i][2]];
            $array_turno_programado[$resultado_registros_turno_programado[$i][1]]['duracion'][$fecha_turno]=0;
        }

    }
    
    if (!isset($array_turnos_malla)) {
        $array_turnos_malla=array();
    }

    $array_turnos_malla=array_values(array_unique($array_turnos_malla));
    sort($array_turnos_malla);

    $consulta_string_turno_realizado="SELECT `cot_id`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion`, `cot_fuente`, `cot_observaciones_inicio`, `cot_observaciones_fin`, `cot_registro_fecha`, TU.`usu_nombres_apellidos`, TPI.`ap_nombre_piloto` FROM `tb_control_turno` LEFT JOIN `tb_administrador_usuario`AS TU ON `tb_control_turno`.`cot_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TPI ON TU.`usu_piloto`=TPI.`ap_id` WHERE 1=1 AND `cot_inicio` LIKE ? ".str_replace('cotm_usuario', 'cot_usuario', $filtro_usuarios_turno)." ORDER BY `cot_id` ASC";

    $consulta_registros_turno_realizado = $enlace_db->prepare($consulta_string_turno_realizado);
    if (count($data_consulta_turnos)>0) {
        $consulta_registros_turno_realizado->bind_param(str_repeat("s", count($data_consulta_turnos)), ...$data_consulta_turnos);
    }
    $consulta_registros_turno_realizado->execute();
    $resultado_registros_turno_realizado = $consulta_registros_turno_realizado->get_result()->fetch_all(MYSQLI_NUM);

    if (count($resultado_registros_turno_realizado)>0) {
        $fecha_actual=date("Y-m-d H:i:s");
        for ($i=0; $i < count($resultado_registros_turno_realizado); $i++) {

            if ($resultado_registros_turno_realizado[$i][4]=='') {
                $array_turnos[$resultado_registros_turno_realizado[$i][1]]['actual']=$resultado_registros_turno_realizado[$i][2];
            }

            $array_turnos_id[$resultado_registros_turno_realizado[$i][1]][]=$resultado_registros_turno_realizado[$i][0];
            $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['tipo']=$resultado_registros_turno_realizado[$i][2];
            $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['inicio']=$resultado_registros_turno_realizado[$i][3];
            $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['fin']=$resultado_registros_turno_realizado[$i][4];
            if ($resultado_registros_turno_realizado[$i][5]!="") {
                $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['duracion']=$resultado_registros_turno_realizado[$i][5];
                $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][2]]['duracion_total']+=$resultado_registros_turno_realizado[$i][5]+0;
            } else {
                if (date('Y-m-d', strtotime($resultado_registros_turno_realizado[$i][3]))==date("Y-m-d")) {
                    $duracion = dateDiff($resultado_registros_turno_realizado[$i][3],$fecha_actual);
                } else {
                    $fecha_cierre=date('Y-m-d', strtotime($resultado_registros_turno_realizado[$i][3])).' 23:59:59';
                    $duracion = dateDiff($resultado_registros_turno_realizado[$i][3],$fecha_cierre);
                }
                $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['duracion']=$duracion;
                $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][2]]['duracion_total']+=$duracion+0;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php
        include("../config/configuracion_estilos.php");
    ?>
    <script src="../HighchartsGantt/code/highcharts-gantt.js"></script>
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row" id="elemento_1">
            <div class="col-md-10 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                        <label class="pr-1 pt-1">Campaña:</label>
                        <select class="form-control form-control-sm" name="operacion" id="operacion" required style="max-width: 300px;">
                            <option value="Todas" <?php if($filtro_operacion=="Todas"){ echo "selected"; } ?>>Todas</option>
                            <?php for ($i=0; $i < count($resultado_registros_areas); $i++): ?>
                                <option value="<?php echo $resultado_registros_areas[$i][0]; ?>" <?php if($filtro_operacion==$resultado_registros_areas[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_areas[$i][1]; ?></option>
                            <?php endfor; ?>
                        </select>
                        <label class="px-1 pt-1">Estado:</label>
                        <select class="form-control form-control-sm" name="estado" id="estado" required style="max-width: 150px;">
                            <option value="Todos" <?php if($filtro_estado=="Todos"){ echo "selected"; } ?>>Todos</option>
                            <option value="Conectado" <?php if($filtro_estado=='Conectado'){ echo "selected"; } ?>>Conectado</option>
                            <option value="Desconectado" <?php if($filtro_estado=='Desconectado'){ echo "selected"; } ?>>Desconectado</option>
                        </select>
                        <input type="text" name="id_filtro" value='<?php if (isset($_POST["filtro"])) { echo $_POST['id_filtro']; } else {if($filtro_permanente!="null"){echo $filtro_permanente;}} ?>' placeholder="Búsqueda" class="form-control height-31">
                        <input type="date" name="filtro_fecha" id="filtro_fecha" value='<?php echo $FechaInicio; ?>' class="form-control height-31" required autofocus style="max-width: 210px;">
                        <span class="input-group-btn">
                            <button class="btn btn-corp" type="submit" name="filtro"><span class="fas fa-search"></span></button>
                            <a href="control_turnos.php?fechainicio=<?php echo base64_encode($FechaInicio);?>&operacion=<?php echo base64_encode('Todas'); ?>&estado=<?php echo base64_encode('Todos'); ?>&id=null" class="btn btn-corp"><span class="fas fa-sync-alt"></span></a>
                        </span>
                    </div>
                </form>
            </div>
            <div class="col-md-2 py-2">
                <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                    <button type="button" data-toggle="modal" class='btn btn-corp menu float-right' data-target="#dataexport"><span class="fas fa-file-excel float-left"></span></button>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="float-left px-2 py-1 border-radius-5 ml-1 font-size-11" style="background-color: #1E8449; color: #FFF;"><span class="fas fa-user-clock"></span> Turno realizado</div>
                <!-- <div class="float-left px-2 py-1 border-radius-5 ml-1 font-size-11" style="background-color: #2874A6; color: #FFF;"><span class="fas fa-utensils"></span> Almuerzo</div>
                <div class="float-left px-2 py-1 border-radius-5 ml-1 font-size-11" style="background-color: #F1C40F; color: #FFF;"><span class="fas fa-coffee"></span> Break</div>
                <div class="float-left px-2 py-1 border-radius-5 ml-1 font-size-11" style="background-color: #B03A2E; color: #FFF;"><span class="fas fa-walking"></span> Pausa Activa</div>
                <div class="float-left px-2 py-1 border-radius-5 ml-1 font-size-11" style="background-color: #6C3483; color: #FFF;"><span class="fas fa-chalkboard-teacher"></span> Capacitación</div>
                <div class="float-left px-2 py-1 border-radius-5 ml-1 font-size-11" style="background-color: #1ABC9C; color: #FFF;"><span class="fas fa-retweet"></span> Retroalimentación</div> -->
            </div>
            <div class="col-md-12">
                <?php if (count($resultado_registros_usuarios)>0): ?>
                    <div class="scrolling-container">
                        <div id="container"></div>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning p-1 text-center">
                        <span class="fas fa-exclamation-triangle"></span> ¡No se encontraron registros!
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
        include("control_turnos_reporte.php");
    ?>
    <script type="text/javascript">
        var map = Highcharts.map,
        series,
        usuarios;

        Highcharts.setOptions({
            lang: {
                months: [
                    'Enero', 'Febrero', 'Marzo', 'Abril',
                    'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                shortMonths: [
                    'Enero', 'Febrero', 'Marzo', 'Abril',
                    'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                weekdays: [
                    'Domingo', 'Lunes', 'Martes', 'Miércoles',
                    'Jueves', 'Viernes', 'Sábado'
                ],
            }
        });
        <?php
            $fecha_validar=$FechaInicio;
            $mes_validar=date("m", strtotime($fecha_validar))-1;
            $resultado_fecha=date("Y,", strtotime($fecha_validar)).$mes_validar.",".date("d", strtotime($fecha_validar));
        ?>
        usuarios = [
            <?php for ($j=0; $j < count($resultado_registros_usuarios); $j++): ?>
                <?php
                    $id_usuario=$resultado_registros_usuarios[$j][0];
                ?>
                {
                    nombre: '<?php echo $resultado_registros_usuarios[$j][1]; ?>',
                    duracion_turno: '<?php echo ($array_turnos[$id_usuario]['turno']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['turno']['duracion_total']) : ''; ?>',
                    duracion_break: '<?php echo ($array_turnos[$id_usuario]['break']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['break']['duracion_total']) : ''; ?>',
                    duracion_almuerzo: '<?php echo ($array_turnos[$id_usuario]['almuerzo']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['almuerzo']['duracion_total']) : ''; ?>',
                    duracion_pausa: '<?php echo ($array_turnos[$id_usuario]['pausaactiva']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['pausaactiva']['duracion_total']) : ''; ?>',
                    duracion_capacitacion: '<?php echo ($array_turnos[$id_usuario]['capacitacion']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['capacitacion']['duracion_total']) : ''; ?>',
                    duracion_retroalimentacion: '<?php echo ($array_turnos[$id_usuario]['retroalimentacion']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['retroalimentacion']['duracion_total']) : ''; ?>',
                    programado: '<?php echo $array_turno_programado[$id_usuario]['turno'][$fecha_validar]; ?>',
                    current: 0,
                    deals: [
                    <?php if(isset($array_turnos_id[$id_usuario])): ?>
                        <?php for ($k=0; $k < count($array_turnos_id[$id_usuario]); $k++): ?>
                            <?php
                                $turno_id=$array_turnos_id[$id_usuario][$k];
                                $turno_tipo=$array_nombres_turnos[$array_turnos[$id_usuario][$turno_id]['tipo']];
                                $turno_duracion=$array_turnos[$id_usuario][$turno_id]['duracion'];
                                $turno_inicio=$array_turnos[$id_usuario][$turno_id]['inicio'];
                                $turno_fin=$array_turnos[$id_usuario][$turno_id]['fin'];
                                $turno_color=$array_colores_turnos[$array_turnos[$id_usuario][$turno_id]['tipo']];
                                $turno_icono=$array_iconos_turnos[$array_turnos[$id_usuario][$turno_id]['tipo']];

                                if ($turno_tipo=='Turno' AND $array_turno_programado[$id_usuario]['duracion'][$fecha_validar]>0) {
                                    $adherencia=calculaAdherencia($array_turno_programado[$id_usuario]['fecha_inicio'][$fecha_validar], $array_turno_programado[$id_usuario]['fecha_fin'][$fecha_validar], $turno_inicio, $turno_fin, $array_turno_programado[$id_usuario]['duracion'][$fecha_validar]);
                                    $adherencia_mostrar=$adherencia*100;
                                } else {
                                    $adherencia=0;
                                    $adherencia_mostrar=0;
                                }
                            ?>
                            {
                                tipo: '<?php echo $turno_tipo; ?>',
                                from: Date.UTC(<?php echo formatear_fecha_grafica($turno_inicio); ?>),
                                to: Date.UTC(<?php echo formatear_fecha_grafica_fin($turno_fin, $turno_inicio); ?>),
                                duracion: '<?php echo $turno_duracion; ?>',
                                color: '<?php echo $turno_color; ?>',
                                fontSymbol: '<?php echo $turno_icono; ?>',
                                completed: <?php echo $adherencia; ?>,
                                completed_mostrar: '<?php echo number_format($adherencia_mostrar, 2, ',', ''); ?>%',
                            },
                        <?php endfor; ?>
                    <?php else: ?>
                        {
                            tipo: '',
                            from: '',
                            to: '',
                            duracion: '',
                            color: '',
                            fontSymbol: '',
                            completed: 0,
                            completed_mostrar: '',
                        },
                    <?php endif; ?>
                    ]
                },
            <?php endfor; ?>
        ];

        // Parse car data into series.
        series = usuarios.map(function (usuario, i) {
            var data = usuario.deals.map(function (deal) {
                return {
                    id: 'deal-' + i,
                    tipo: deal.tipo,
                    start: deal.from,
                    end: deal.to,
                    color: deal.color,
                    fontSymbol: deal.fontSymbol,
                    completed_mostrar: deal.completed_mostrar,
                    completed: {
                      amount: deal.completed,
                      fill: '#212F3C'
                    },
                    y: i
                };
            });
            return {
                name: usuario.nombre,
                duracion_turno: usuario.duracion_turno,
                duracion_break: usuario.duracion_break,
                duracion_almuerzo: usuario.duracion_almuerzo,
                duracion_pausa: usuario.duracion_pausa,
                duracion_capacitacion: usuario.duracion_capacitacion,
                duracion_retroalimentacion: usuario.duracion_retroalimentacion,
                programado: usuario.programado,
                data: data,
                dataLabels: [{
                    enabled: true,
                    format: '<div style="width: 200px; height: 15px; overflow: hidden;">' +
                        '{point.completed_mostrar} <span class="fas fa-{point.fontSymbol}"></span></div>',
                    useHTML: true,
                    align: 'center'
                }],
                current: usuario.deals[usuario.current]
            };
        });

        Highcharts.ganttChart('container', {
            series: series,
            title: {
                text: null
            },
            tooltip: {
                pointFormat: '<span><b>{point.tipo}</b></span><br/><span><b>Inicio:</b> {point.start:%H:%M:%S, %e %b %Y}</span><br/><span><b>Fin:</b> {point.end:%H:%M:%S, %e %b %Y}</span><br/><span><b>Adherencia:</b> {point.completed_mostrar}</span>'
            },
            xAxis: [{
                    min: Date.UTC(<?php echo $resultado_fecha; ?>, 0, 0, 0),
                    max: Date.UTC(<?php echo $resultado_fecha; ?>, 23, 59, 59),
                    grid: {
                        cellHeight: 30
                    },
                    labels: {
                        align: 'center',
                        style: {
                            fontSize: '10px'
                        }
                    },
                    tickInterval: 1000 * 60 * 60,
                }, {
                    // Set the second axis to have a height of 60px
                    grid: {
                        cellHeight: 30
                }
            }],
            credits: false,
            yAxis: {
                type: 'category',
                staticScale: 25,
                labels: {
                    align: 'left',
                    useHTML: true,
                    style: {
                        fontSize: '10px',
                        width: '200px',
                        padding: '2px'
                    }
                },
                grid: {
                    enabled: true,
                    borderColor: 'rgba(0,0,0,0.3)',
                    borderWidth: 1,
                    columns: [{
                        title: {
                            text: 'Usuario',
                            format: '{point.name}',
                        },
                        labels: {
                            align: 'left',
                            useHTML: true,
                            style: {
                                fontSize: '10px',
                                width: '200px',
                                padding: '2px'
                            }
                        },
                        categories: map(series, function (s) {
                            return s.name;
                        })
                    }, {
                        title: {
                            text: 'Programado',
                            // rotation: -90,
                            // y: -15
                        },
                        labels: {
                            align: 'center',
                            useHTML: true,
                            style: {
                                fontSize: '10px',
                                padding: '2px'
                            }
                        },
                        categories: map(series, function (s) {
                            return s.programado;
                        })
                    }, {
                        title: {
                            text: 'Realizado',
                            // rotation: -90,
                            // y: -15
                        },
                        labels: {
                            align: 'center',
                            useHTML: true,
                            style: {
                                fontSize: '10px',
                                padding: '2px'
                            }
                        },
                        categories: map(series, function (s) {
                            return s.duracion_turno;
                        })
                    }, 
                    // {
                    //     title: {
                    //         text: 'Break',
                    //         align: 'left',
                    //         style: {
                    //             fontSize: '9px'
                    //         },
                    //         rotation: -90,
                    //     },
                    //     categories: map(series, function (s) {
                    //         return s.duracion_break;
                    //     })
                    // }, {
                    //     title: {
                    //         text: 'Almuerzo',
                    //         align: 'left',
                    //         style: {
                    //             fontSize: '9px'
                    //         },
                    //         rotation: -90,
                    //     },
                    //     categories: map(series, function (s) {
                    //         return s.duracion_almuerzo;
                    //     })
                    // }, {
                    //     title: {
                    //         text: 'Pausa Activa',
                    //         align: 'left',
                    //         style: {
                    //             fontSize: '9px'
                    //         },
                    //         rotation: -90,
                    //     },
                    //     categories: map(series, function (s) {
                    //         return s.duracion_pausa;
                    //     })
                    // }, {
                    //     title: {
                    //         text: 'Capacitación',
                    //         align: 'left',
                    //         style: {
                    //             fontSize: '9px'
                    //         },
                    //         rotation: -90,
                    //     },
                    //     categories: map(series, function (s) {
                    //         return s.duracion_capacitacion;
                    //     })
                    // }, {
                    //     title: {
                    //         text: 'Retroalimentación',
                    //         align: 'left',
                    //         style: {
                    //             fontSize: '9px',
                    //         },
                    //         rotation: -90,
                    //     },
                    //     categories: map(series, function (s) {
                    //         return s.duracion_retroalimentacion;
                    //     })
                    // }
                    ]
                }
            }
        });
    </script>
</body>
</html>