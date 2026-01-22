<?php
    require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    require_once("config/conexion_db.php");
    require_once("config/validar_festivos.php");
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    unset($_SESSION['contrasena_actualizada']);
    unset($_SESSION['dashboard_registro_creado_dash']);
    unset($_SESSION['dashboard_registro_eliminado_dashboard']);
    unset($_SESSION['session_turnop_id']);
    unset($_SESSION['mturno_cambioturno_crear']);

    //CONSULTA PERMISOS MÓDULOS
        $consulta_string_permisos="SELECT `per_id`, `per_usuario`, `per_modulo`, `per_perfil`, `mod_modulo_nombre` FROM `tb_configuracion_perfil_usu_mod` LEFT JOIN `tb_configuracion_modulo` ON `tb_configuracion_perfil_usu_mod`.`per_modulo`=`tb_configuracion_modulo`.`mod_id` WHERE `per_usuario`=?";

        $consulta_registros_permisos = $enlace_db->prepare($consulta_string_permisos);
        $consulta_registros_permisos->bind_param("s", $_SESSION["usu_id"]);
        $consulta_registros_permisos->execute();
        $resultado_modulos_usuario = $consulta_registros_permisos->get_result()->fetch_all(MYSQLI_NUM);
        
        unset($_SESSION['modulos_acceso_permisos']);
        
        for ($i=0; $i < count($resultado_modulos_usuario); $i++) {
            $_SESSION['modulos_acceso_permisos'][$resultado_modulos_usuario[$i][4]]=$resultado_modulos_usuario[$i][3];
        }
    //CONSULTA PERMISOS MÓDULOS
    if ((isset($_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']) AND $_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']!="") OR (isset($_SESSION['modulos_acceso_permisos']['Encuestas']) AND $_SESSION['modulos_acceso_permisos']['Encuestas']!="")) {
        //Validación de permisos del usuario para el módulo
        $modulo_plataforma="Administrador-Dashboard";

        $perfil_modulo=$_SESSION['modulos_acceso_permisos'][$modulo_plataforma];

        $data_consulta=array();

        if ($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor") {
            $filtro_perfil="";
        } else {
            $filtro_perfil=" AND `gd_campania`=?";
            array_push($data_consulta, $_SESSION['usu_campania']);
        }

        $consulta_dashboard = $enlace_db->prepare("SELECT `gd_id`, `gd_nombre`, `gd_matriz`, `gd_campania`, `gd_orden`, `gd_estado`, `gd_registro_usuario`, `gd_registro_fecha`, TM.`gem_nombre_encuesta`, TU.`usu_nombres_apellidos` FROM `tb_gestion_dashboard` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_dashboard`.`gd_matriz`=TM.`gem_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_dashboard`.`gd_registro_usuario`=TU.`usu_id` WHERE 1=1 ".$filtro_perfil." ORDER BY `gd_orden` ASC");
        if (count($data_consulta)>0) {
            $consulta_dashboard->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
        }
        $consulta_dashboard->execute();
        $resultado_dashboard = $consulta_dashboard->get_result()->fetch_all(MYSQLI_NUM);
    }

    if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos']!="") {
        //PROCESO ACTUALIZA CIERRE TURNOS ABIERTOS VENCIDOS
            $fecha_maximo_cierre=date("Y-m-d H:i:s", strtotime("-2 hours"));

            //CONSULTA TURNO PROGRAMADO
            $consulta_inicio_turnos_cierre = mysqli_query($enlace_db, "SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TR.`cot_id`, TR.`cot_inicio`, TR.`cot_fin` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_control_turno` AS TR ON `tb_control_turno_malla`.`cotm_id`=TR.`cot_turno_malla` WHERE `cotm_fin`<='".$fecha_maximo_cierre."' AND TR.`cot_fin`='' ORDER BY `cotm_usuario`, `cotm_inicio`");
            $resultado_inicio_turnos_cierre = mysqli_fetch_all($consulta_inicio_turnos_cierre);

            // Prepara la sentencia
            $consulta_actualizar_turno_cierre = $enlace_db->prepare("UPDATE `tb_control_turno` SET `cot_fin`=?, `cot_duracion`=?, `cot_observaciones_fin`='Cierre automático' WHERE `cot_id`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar_turno_cierre->bind_param('sss', $fecha_turno_cierre, $duracion_turno_cierre, $id_turno_cierre);

            for ($i=0; $i < count($resultado_inicio_turnos_cierre); $i++) { 
                $fecha_turno_cierre=$resultado_inicio_turnos_cierre[$i][4];
                $duracion_turno_cierre=dateDiff($resultado_inicio_turnos_cierre[$i][12],$fecha_turno_cierre);
                $id_turno_cierre=$resultado_inicio_turnos_cierre[$i][11];
                
                // Ejecuta sentencia preparada
                $consulta_actualizar_turno_cierre->execute();
            }
        //PROCESO ACTUALIZA CIERRE TURNOS ABIERTOS VENCIDOS

        //fecha y hora de servidor actual a variable de sesión para control turno 
        $_SESSION['session_turno_hora_actual']=date("Y-m-d H:i:s");

        //CONSULTA MALLA DE TURNOS
            $fecha_minimo=date("Y-m-d", strtotime("-1 year"));

            //CONSULTA TURNO PROGRAMADO
            $consulta_inicio_turnos_programados = mysqli_query($enlace_db, "SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TR.`cot_id`, TR.`cot_inicio`, TR.`cot_fin` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_control_turno` AS TR ON `tb_control_turno_malla`.`cotm_id`=TR.`cot_turno_malla` WHERE `cotm_usuario`='".$_SESSION['usu_id']."' AND `cotm_inicio`>='".$fecha_minimo."' ORDER BY `cotm_usuario`, `cotm_inicio`");
            $resultado_inicio_turnos_programados = mysqli_fetch_all($consulta_inicio_turnos_programados);

            for ($i=0; $i < count($resultado_inicio_turnos_programados); $i++) { 
                $fecha_turno=date('Y-m-d', strtotime($resultado_inicio_turnos_programados[$i][3]));
                $array_turno_programado[$fecha_turno]['turno_tipo']=$resultado_inicio_turnos_programados[$i][2];
                $array_turno_programado[$fecha_turno]['turno_id']=$resultado_inicio_turnos_programados[$i][0];
                $array_turno_programado[$fecha_turno]['turno_jornada']=$resultado_inicio_turnos_programados[$i][6];
                $array_turno_programado[$fecha_turno]['turno_realizado_inicio']=$resultado_inicio_turnos_programados[$i][12];
                $array_turno_programado[$fecha_turno]['turno_realizado_fin']=$resultado_inicio_turnos_programados[$i][13];
                if ($resultado_inicio_turnos_programados[$i][2]=="turno") {
                    $hora_inicio=date('H:i', strtotime($resultado_inicio_turnos_programados[$i][3]));
                    $hora_fin=date('H:i', strtotime($resultado_inicio_turnos_programados[$i][4]));

                    $array_turno_programado[$fecha_turno]['turno_horario']=$hora_inicio.'-'.$hora_fin;
                    $array_turno_programado[$fecha_turno]['turno_descripcion']=$resultado_inicio_turnos_programados[$i][3].' A '.$resultado_inicio_turnos_programados[$i][4];
                } else {
                    $array_turno_programado[$fecha_turno]['turno_horario']=$array_convenciones[$resultado_inicio_turnos_programados[$i][2]];
                    $array_turno_programado[$fecha_turno]['turno_descripcion']=$array_convenciones[$resultado_inicio_turnos_programados[$i][2]];
                }
            }
        //CONSULTA MALLA DE TURNOS

        //CONSULTA CONTROL TURNOS
            // $consulta_inicio_turno_control = mysqli_query($enlace_db, "SELECT `cot_turno_inicio`, `cot_turno_fin` FROM `tb_control_turno` WHERE `cot_usuario`='".$_SESSION['usu_id']."' AND `cot_logueo_inicial` LIKE '".date('Y-m-d')."%'");
            // $resultado_inicio_turno_control = mysqli_fetch_all($consulta_inicio_turno_control);

            // if ($resultado_inicio_turno_control[0][1]=="") {
            //     $fecha_actual_control = date("Y-m-d H:i:s");
            //     $duracion_turno_control = dateDiff($resultado_inicio_turno_control[0][0],$fecha_actual_control);
            //     if ($duracion_turno_control>28000) {
            //          $notificar_cierre=1;
            //     } else {
            //         $notificar_cierre=0;
            //     }
            // } else {
            //     $notificar_cierre=0;
            // }
            
            $consulta_string_turnos="SELECT `cot_id`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion`, `cot_fuente`, `cot_observaciones_inicio`, `cot_observaciones_fin`, `cot_registro_fecha`, `cot_turno_malla`, TM.`cotm_inicio`, TM.`cotm_fin` FROM `tb_control_turno` LEFT JOIN `tb_control_turno_malla` AS TM ON `tb_control_turno`.`cot_turno_malla`=TM.`cotm_id` WHERE `cot_usuario`='".$_SESSION['usu_id']."' AND `cot_fin`='' ORDER BY `cot_inicio` DESC";
            $consulta_registros_turnos = $enlace_db->prepare($consulta_string_turnos);
            $consulta_registros_turnos->execute();
            $resultado_registros_turnos = $consulta_registros_turnos->get_result()->fetch_all(MYSQLI_NUM);

            $_SESSION['session_turnop_id']='';
            $_SESSION['session_turnop_tipo']='';
            $_SESSION['session_turnop_realizado_fin']='';
            $_SESSION['session_turnop_inicio']='';
            $_SESSION['session_turnop_fin']='';

            if (count($resultado_registros_turnos)>0) {
                for ($i=0; $i < count($resultado_registros_turnos); $i++) { 
                    if ($resultado_registros_turnos[$i][2]=='turno') {
                        $_SESSION['session_turno_inicio']=$resultado_registros_turnos[$i][3];
                        $_SESSION['session_turno_fin']=$resultado_registros_turnos[$i][4];
                        $_SESSION['session_observaciones_inicio_turno']=$resultado_registros_turnos[$i][7];

                        //Datos turno programado fecha actual
                        $_SESSION['session_turnop_id']=$resultado_registros_turnos[$i][10];
                        $_SESSION['session_turnop_tipo']=$resultado_registros_turnos[$i][2];
                        $_SESSION['session_turnop_realizado_fin']=$resultado_registros_turnos[$i][4];
                        $_SESSION['session_turnop_inicio']=$resultado_registros_turnos[$i][11];
                        $_SESSION['session_turnop_fin']=$resultado_registros_turnos[$i][12];
                    } elseif ($resultado_registros_turnos[$i][2]=='break' OR $resultado_registros_turnos[$i][2]=='almuerzo' OR $resultado_registros_turnos[$i][2]=='pausaactiva' OR $resultado_registros_turnos[$i][2]=='capacitacion' OR $resultado_registros_turnos[$i][2]=='retroalimentacion') {
                        $_SESSION['session_actividad_inicio']=$resultado_registros_turnos[$i][3];
                        $_SESSION['session_actividad_fin']=$resultado_registros_turnos[$i][4];
                        $_SESSION['session_actividad_tipo']=$resultado_registros_turnos[$i][2];
                    }
                }
            } else {
                $_SESSION['session_turno_inicio']='';
                $_SESSION['session_turno_fin']='';
                $_SESSION['session_observaciones_inicio_turno']='';
                $_SESSION['session_actividad_inicio']='';
                $_SESSION['session_actividad_fin']='';

                if(isset($array_turno_programado[date('Y-m-d')]['turno_tipo']) AND $array_turno_programado[date('Y-m-d')]['turno_tipo']=='turno' AND $array_turno_programado[date('Y-m-d')]['turno_realizado_fin']=='') {
                    //Datos turno programado fecha actual
                    $_SESSION['session_turnop_id']=$array_turno_programado[date('Y-m-d')]['turno_id'];
                    $_SESSION['session_turnop_tipo']=$array_turno_programado[date('Y-m-d')]['turno_tipo'];
                    $_SESSION['session_turnop_realizado_fin']=$array_turno_programado[date('Y-m-d')]['turno_realizado_fin'];

                    //pendiente definir inicio de turno programado para validar mensajes a mostrar que limite el inicio de turno a 2 horas antes de lo programado
                }
            }

            $consulta_string_usuarios="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_estado`, TC.`ac_nombre_campania` FROM `tb_administrador_usuario` LEFT JOIN `tb_administrador_campania` AS TC ON `tb_administrador_usuario`.`usu_campania`=TC.`ac_id` WHERE `usu_id`=? ORDER BY TC.`ac_nombre_campania` ASC";
            $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
            $consulta_registros_usuarios->bind_param("s", $_SESSION['usu_id']);
            $consulta_registros_usuarios->execute();
            $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

            $consulta_string_turno_realizado="SELECT `cot_id`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion`, `cot_fuente`, `cot_observaciones_inicio`, `cot_observaciones_fin`, `cot_registro_fecha`, TU.`usu_nombres_apellidos`, TPI.`ap_nombre_piloto` FROM `tb_control_turno` LEFT JOIN `tb_administrador_usuario`AS TU ON `tb_control_turno`.`cot_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_piloto` AS TPI ON TU.`usu_piloto`=TPI.`ap_id` WHERE 1=1 AND `cot_turno_malla`=? AND `cot_usuario`=? ORDER BY `cot_id` ASC";

            $consulta_registros_turno_realizado = $enlace_db->prepare($consulta_string_turno_realizado);
            $consulta_registros_turno_realizado->bind_param("ss", $_SESSION['session_turnop_id'], $_SESSION['usu_id']);
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
                    
                    if ($resultado_registros_turno_realizado[$i][4]!='') {
                        $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['fin']=$resultado_registros_turno_realizado[$i][4];
                    } else {
                        $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['fin']=$fecha_actual;
                    }

                    if ($resultado_registros_turno_realizado[$i][5]!="") {
                        $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['duracion']=$resultado_registros_turno_realizado[$i][5];
                        $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][2]]['duracion_total']+=$resultado_registros_turno_realizado[$i][5]+0;
                    } else {
                        $duracion = dateDiff($resultado_registros_turno_realizado[$i][3],$fecha_actual);
                        $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][0]]['duracion']=$duracion;
                        $array_turnos[$resultado_registros_turno_realizado[$i][1]][$resultado_registros_turno_realizado[$i][2]]['duracion_total']+=$duracion+0;
                    }
                }
            } else {

            }
        //CONSULTA CONTROL TURNOS
    }

    if (!isset($resultado_inicio_turnos_programados)) {
        $resultado_inicio_turnos_programados=array();
    }

    /*DEFINICIÓN DE VARIABLES*/
    $titulo_header = "Dashboard";
?>
<!DOCTYPE html>
<html lang="ES">
<head>
    <?php
        include("config/configuracion_estilos_raiz.php");
        include("config/configuracion_calendario_raiz.php");
    ?>
    <script src="HighchartsGantt/code/highcharts-gantt.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('Calendario1');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'list', 'bootstrap'],
                defaultView: 'timeGridWeek',
                timeZone: 'America/Bogota',
                firstDay: 1,
                themeSystem: 'bootstrap',
                locale: 'es',
                buttonText: {
                              today:    'Hoy',
                              month:    'Mes',
                              week:     'Semana',
                              day:      'Día',
                              list:     'Agenda'
                            },
                weekLabel: "Sm",
                allDayHtml: "Todo<br/>el día",
                eventLimitText: "más",
                noEventsMessage: "No hay eventos para mostrar",
                header: {
                  left: 'title',
                  center: '',
                  right: 'today, dayGridMonth,timeGridWeek,timeGridDay,listMonth, prev,next, prevYear, nextYear'
                },
                weekNumbers: true,
                eventLimit: true,
                events: [
                    <?php for ($i=0; $i < count($resultado_inicio_turnos_programados); $i++): ?>
                        <?php
                            if ($resultado_inicio_turnos_programados[$i][2]=="turno") {
                                $tipo_turno='Turno'."-".$resultado_inicio_turnos_programados[$i][6];
                                $titulo=date('H:i', strtotime($resultado_inicio_turnos_programados[$i][3])).'-'.date('H:i', strtotime($resultado_inicio_turnos_programados[$i][4]));
                                $color_turno=$array_convenciones_color[$resultado_inicio_turnos_programados[$i][6]];
                            } else {
                                $tipo_turno=$array_convenciones[$resultado_inicio_turnos_programados[$i][2]];
                                $titulo=$tipo_turno;
                                $color_turno=$array_convenciones_color[$resultado_inicio_turnos_programados[$i][2]];
                            }
                            $usuario_turno=$resultado_inicio_turnos_programados[$i][10];
                        ?>
                    {
                      title  : '<?php echo $titulo; ?>',
                      start  : '<?php echo date('Y-m-d', strtotime($resultado_inicio_turnos_programados[$i][3])).'T'.date('H:i', strtotime($resultado_inicio_turnos_programados[$i][3])); ?>',
                      end    : '<?php echo date('Y-m-d', strtotime($resultado_inicio_turnos_programados[$i][4])).'T'.date('H:i', strtotime($resultado_inicio_turnos_programados[$i][4])); ?>',
                      tipo: '<?php echo $tipo_turno; ?>',
                      usuario: '<?php echo $usuario_turno; ?>',
                      color: '<?php echo $color_turno; ?>'
                    },
                    <?php endfor; ?>
                ],
                eventClick: function(info) {
                    var dia_evento = calendar.formatDate(info.event.start, {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                    });

                    var hora_inicio = calendar.formatDate(info.event.start, {
                        hour: 'numeric',
                        minute: 'numeric',
                    });

                    var hora_fin = calendar.formatDate(info.event.end, {
                        hour: 'numeric',
                        minute: 'numeric',
                    });

                    $('#modalTitle').html(dia_evento);
                    $('#turno').html(info.event.title);
                    $('#tipo').html(info.event.extendedProps.tipo);
                    $('#usuario').html(info.event.extendedProps.usuario);
                    $('#calendarModal').modal();
                }
            });

            calendar.render();
        });
    </script>
</head>
<body onload="
    <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos']!="" AND !esMobil() AND $_SESSION['session_turno_inicio']):?>
        incremento_hora_actual();
        <?php
            if ($_SESSION['session_turno_inicio']!="" AND $_SESSION['session_turno_fin']=="") {
                echo 'cronometro_turno();';
            }
        ?>
        <?php
            if ($_SESSION['session_actividad_inicio']!="" AND $_SESSION['session_actividad_fin']=="") {
                echo 'cronometro_actividad();';
            }
        ?>
    <?php endif; ?>
">
    <?php
        $menu_principal=includeFileContent("menu_principal.php");
        echo str_replace('../', '', $menu_principal);
        include("menu_header_raiz.php");
    ?>
    <div class="contenido" style="background-color: #EEEEEE;">
        <?php if (esMobil()):?>
            <div class="row">
                <div class="col-md-12 py-1 px-1">
                    <p class="alert alert-danger p-1 font-weight-bold text-center">¡Recuerde que el reporte de turno solo es válido desde el equipo de escritorio corporativo!</p>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']) AND $_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']!="" AND ($_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']=='Administrador' OR $_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']=='Gestor')): ?>
            <div class="row">
                <div class="col-md-12 py-1 px-1">
                    <a href="administrador/configuracion_dashboard_crear.php" class="btn btn-corp menu float-right ml-1" title="Crear Dashboard"><div class="float-left"><span class="fas fa-plus"></span> Crear Dashboard</div></a>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos']!=""): ?>
            <div class="row px-1 py-1">
                <div class="col-md-5">
                    <div class="row p-1">
                        <div class="col-md-12 p-2 fondo-blanco">
                            <div class="titulo_seccion pb-2">Mis Turnos <a href="gestion_turnos/control_turnos_cambiar.php" class="btn btn-corp menu float-right ml-1" title="Cambiar turno"><div class="float-right"><span class="fas fa-retweet"></span> Cambiar Turno</div></a></div>
                            <div id="Calendario1"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="row p-1">
                        <div class="col-md-12 p-2 fondo-blanco">
                            <div class="titulo_seccion pb-2">Mi Adherencia</div>
                            <div class="scrolling-container">
                                <?php if(!isset($array_turno_programado[date('Y-m-d')]['turno_tipo'])): ?>
                                    <p class="alert alert-warning p-1 font-size-11">¡No hemos encontrado turno programado para hoy, por favor valide con su jefe inmediato!</p>
                                <?php elseif($array_turno_programado[date('Y-m-d')]['turno_tipo']!='turno'): ?>
                                    <p class="alert alert-warning p-1 font-size-11">¡El tipo de turno programado para hoy no requiere reporte de inicio y fin de turno!</p>
                                <?php elseif($array_turno_programado[date('Y-m-d')]['turno_tipo']=='turno' AND $_SESSION['session_turno_inicio']==''): ?>
                                    <p class="alert alert-warning p-1 font-size-11">¡Por favor no olvide iniciar turno!</p>
                                <?php elseif($_SESSION['session_turno_inicio']!=''): ?>
                                    <?php if($_SESSION['session_turno_fin']=='' AND dateDiff(date('Y-m-d H:i:s'), $_SESSION['session_turnop_fin'])<=600): ?>
                                        <p class="alert alert-warning p-1 font-size-11 text-center">¡Por favor no olvide finalizar el turno!</p>
                                    <?php endif; ?>
                                    <div id="container_turno"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ((isset($_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']) AND $_SESSION['modulos_acceso_permisos']['Administrador-Dashboard']!="") OR (isset($_SESSION['modulos_acceso_permisos']['Encuestas']) AND $_SESSION['modulos_acceso_permisos']['Encuestas']!="")): ?>
            <div class="row p-1 justify-content-center">
                <?php if(count($resultado_dashboard)>0): ?>
                    <?php for ($i=0; $i < count($resultado_dashboard); $i++): ?>
                        <div class="col-md-3 py-0 mb-1">
                            <div class="row p-1">
                                <div class="col-md-12 fondo-blanco border-top-corp">
                                    <div class="col-md-12 color-corporativo titulo-dashboard text-center text-uppercase"><?php echo $resultado_dashboard[$i][1]; ?></div>
                                    <div class="col-md-12 text-center"><img src="../images/img_dashboard.svg" class="img-fluid"></div>
                                    <div class="col-md-12 detalle-dashboard"><span class="fas fa-tasks"></span> <?php echo $resultado_dashboard[$i][8]; ?></div>
                                    <div class="col-md-12">
                                        <a href="dashboard.php?reg=<?php echo base64_encode($resultado_dashboard[$i][0]); ?>" class="btn btn-corp menu float-right ml-1 mb-1" target="_blank" title="Abrir Dashboard"><span class="fas fa-external-link-alt"></span> Abrir dashboard</a>
                                        <?php if($perfil_modulo=="Administrador" OR $perfil_modulo=="Gestor"): ?>
                                            <a href="dashboard_configuracion.php?reg=<?php echo base64_encode($resultado_dashboard[$i][0]); ?>" class="btn btn-corp menu float-right ml-1 mb-1" title="Configuración"><span class="fas fa-cogs"></span></a>
                                            <a href="administrador/configuracion_dashboard_eliminar.php?reg=<?php echo base64_encode($resultado_dashboard[$i][0]); ?>" class="btn btn-danger menu float-right ml-1 mb-1 pt-1" title="Eliminar"><span class="fas fa-trash-alt"></span></a>
                                            <a href="administrador/configuracion_dashboard_editar.php?reg=<?php echo base64_encode($resultado_dashboard[$i][0]); ?>" class="btn btn-corp menu float-right ml-1 mb-1" title="Editar"><span class="fas fa-pen"></span></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <p class="col-md-12 alert alert-warning">¡No se encontraron dashboard habilitados!</p>
                <?php endif;?>
            </div>
        <?php endif; ?>
    </div>
    <?php
        if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos']!="" AND !esMobil() AND isset($array_turno_programado[date('Y-m-d')]['turno_tipo']) AND $array_turno_programado[date('Y-m-d')]['turno_tipo']=='turno' AND $array_turno_programado[date('Y-m-d')]['turno_realizado_fin']=='') {
            include("control_turno.php");
        }
        include("footer.php");
        include("config/configuracion_js_raiz.php");
    ?>
    <!-- MODAL CALENDARIO -->
        <div class="modal fade" id="calendarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="contenido_pagina_modal">
                        <div class="row">
                          <div class="col-md-12">
                            <table class="table table-bordered table-striped table-hover table-sm">
                                <tbody>
                                    <tr>
                                        <th class="align-middle text-right">Nombres y apellidos</th>
                                        <td class="align-middle" id="usuario"></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle text-right">Tipo</th>
                                        <td class="align-middle" id="tipo"></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle text-right">Horario</th>
                                        <td class="align-middle" id="turno"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button type="button" class="btn btn-corp-form m-0" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
          </div>
        </div>
    <!-- MODAL CALENDARIO -->
    <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos']!="" AND $_SESSION['session_turno_inicio']!=''): ?>
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
                $fecha_validar=date('Y-m-d');
                $mes_validar=date('m')-1;
                $resultado_fecha=date("Y,", strtotime($fecha_validar)).$mes_validar.",".date("d", strtotime($fecha_validar));
            ?>
            usuarios = [
                <?php for ($j=0; $j < count($resultado_registros_usuarios); $j++): ?>
                    <?php
                        $id_usuario=$resultado_registros_usuarios[$j][0];
                    ?>
                    {
                        nombre: '<?php echo $resultado_registros_usuarios[$j][1]; ?>',
                        duracion_turno: '<?php echo ($array_turnos[$id_usuario]['turno_horario']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['turno_horario']['duracion_total']) : ''; ?>',
                        duracion_break: '<?php echo ($array_turnos[$id_usuario]['break']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['break']['duracion_total']) : ''; ?>',
                        duracion_almuerzo: '<?php echo ($array_turnos[$id_usuario]['almuerzo']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['almuerzo']['duracion_total']) : ''; ?>',
                        duracion_pausa: '<?php echo ($array_turnos[$id_usuario]['pausaactiva']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['pausaactiva']['duracion_total']) : ''; ?>',
                        duracion_capacitacion: '<?php echo ($array_turnos[$id_usuario]['capacitacion']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['capacitacion']['duracion_total']) : ''; ?>',
                        duracion_retroalimentacion: '<?php echo ($array_turnos[$id_usuario]['retroalimentacion']['duracion_total']>0) ? conversorSegundosHoras_ns($array_turnos[$id_usuario]['retroalimentacion']['duracion_total']) : ''; ?>',
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
                                ?>
                                {
                                    tipo: '<?php echo $turno_tipo; ?>',
                                    from: Date.UTC(<?php echo formatear_fecha_grafica($turno_inicio); ?>),
                                    to: Date.UTC(<?php echo formatear_fecha_grafica($turno_fin, $turno_inicio); ?>),
                                    duracion: '<?php echo $turno_duracion; ?>',
                                    color: '<?php echo $turno_color; ?>',
                                    fontSymbol: '<?php echo $turno_icono; ?>'
                                },
                            <?php endfor; ?>
                        <?php else: ?>
                            {
                                tipo: '',
                                from: '',
                                to: '',
                                duracion: '',
                                color: '',
                                fontSymbol: ''
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
                    data: data,
                    dataLabels: [{
                        enabled: true,
                        format: '<div style="width: 15px; height: 15px; overflow: hidden;">' +
                            '<span class="fas fa-{point.fontSymbol} "></span></div>',
                        useHTML: true,
                        align: 'center'
                    }],
                    current: usuario.deals[usuario.current]
                };
            });

            Highcharts.ganttChart('container_turno', {
                series: series,
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '<span><b>{point.tipo}</b></span><br/><span><b>Inicio:</b> {point.start:%H:%M:%S, %e %b %Y}</span><br/><span><b>Fin:</b> {point.end:%H:%M:%S, %e %b %Y}</span>'
                },
                xAxis: [{
                        min: Date.UTC(<?php echo $resultado_fecha; ?>, 0, 0, 0),
                        max: Date.UTC(<?php echo $resultado_fecha; ?>, 23, 59, 59),
                        grid: {
                            cellHeight: 50
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
                            cellHeight: 50
                    }
                }],
                credits: false,
                yAxis: {
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
                    type: 'category',
                    grid: {
                        columns: [{
                            title: {
                                text: 'Usuario',
                                style: {
                                    fontSize: '9px',
                                }
                            },
                            categories: map(series, function (s) {
                                return s.name;
                            })
                        }, {
                            title: {
                                text: 'Turno',
                                align: 'left',
                                style: {
                                    fontSize: '9px'
                                },
                                rotation: -90,
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
    <?php endif; ?>
</body>
</html>