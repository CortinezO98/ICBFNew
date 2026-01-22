<?php
    session_start();
    error_reporting(0);
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if (!isset($_SESSION["usu_id"])) {
        header("Location:../index.php");
    } else {
        if (in_array("Control Turnos", $_SESSION['modulos_acceso'])) {
            require_once("../conexion_configuracion.php");
            require_once("../PHPExcel/Classes/PHPExcel.php");
            require_once("../validaciones_seguridad.php");
            $clave_modulo = array_search("Control Turnos", $_SESSION['modulos_acceso']);
            $perfil_modulo_plataforma=$_SESSION['modulos_acceso_permisos'][$clave_modulo][3];
            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
            //porcentaje
            $_SESSION["progress_bar"] = 0;
            session_write_close();
            session_start();
            /**
             * Función para calcular la diferencia en segundos de dos fechas
             * @param  [String] $fecha_1 [Fecha inicio]
             * @param  [String] $fecha_2 [Fecha fin]
             * @return [Integer]         [Diferencia en segundos]
             */
            function diffSegundosFechas($fecha_1, $fecha_2) {
                $diffSegundos = strtotime($fecha_2)-strtotime($fecha_1);
                return intval($diffSegundos);
            }
            /**
             * Función para calcular tiempo en segundos de un turno sin contar el almuerzo
             * @param  [String] $turno_inicio    [Turno inicio]
             * @param  [String] $turno_fin       [Turno fin]
             * @param  [String] $almuerzo_inicio [Almuerzo inicio]
             * @param  [String] $almuerzo_fin    [Almuerzo fin]
             * @return [Integer]                 [Diferencia en segundos]
             */
            function calcularTotal($turno_inicio, $turno_fin, $almuerzo_inicio, $almuerzo_fin) {
                $segundos_turno = diffSegundosFechas($turno_inicio, $turno_fin);
                $segundos_almuerzo = 0;
                if ($almuerzo_inicio!=""&&$almuerzo_fin!="") {
                    $segundos_almuerzo = diffSegundosFechas($almuerzo_inicio, $almuerzo_fin);
                }
                return intval($segundos_turno-$segundos_almuerzo);
            }
            /**
             * Función para calcular la diferencia en segundos.
             * Se determinan los turnos entre programados y realizados
             * @param  [String] $fecha_programada_1 [Fecha programada inicio]
             * @param  [String] $fecha_programada_2 [Fecha programada fin]
             * @param  [String] $fecha_real_1       [Fecha realizada inicio]
             * @param  [String] $fecha_real_2       [Fecha realizada fin]
             * @return [Integer]                    [Diferencia en segundos]
             */
            function validarCalcularFechas($fecha_programada_1, $fecha_programada_2, $fecha_real_1, $fecha_real_2) {
                $segundos = 0;
                if (($fecha_programada_1!=""&&$fecha_programada_2!="")||($fecha_real_1!=""&&$fecha_real_2!="")) {
                    if ($fecha_programada_1!=""&&$fecha_programada_2!=""&&$fecha_real_1!=""&&$fecha_real_2!="") {
                        $segundos = diffSegundosFechas($fecha_real_1, $fecha_real_2);
                    } elseif ($fecha_programada_1!=""&&$fecha_programada_2!=""&&$fecha_real_1==""&&$fecha_real_2=="") {
                        $segundos = diffSegundosFechas($fecha_programada_1, $fecha_programada_2);
                    } elseif ($fecha_programada_1==""&&$fecha_programada_2==""&&$fecha_real_1!=""&&$fecha_real_2!="") {
                        $segundos = diffSegundosFechas($fecha_real_1, $fecha_real_2);
                    }
                }
                return intval($segundos);
            }
            /**
             * Función para calcular el tiempo en segundos de la adherencia, entre el turno programado y el realizado. 
             * @param  [String] $turno_inicio_programado    [Turno programado inicio]
             * @param  [String] $turno_fin_programado       [Turno programado fin]
             * @param  [String] $break_1_inicio_programado  [Break 1 programado inicio]
             * @param  [String] $break_1_fin_programado     [Break 1 programado fin]
             * @param  [String] $almuerzo_inicio_programado [Almuerzo programado inicio]
             * @param  [String] $almuerzo_fin_programado    [Almuerzo programado fin]
             * @param  [String] $break_2_inicio_programado  [Break 2 programado inicio]
             * @param  [String] $break_2_fin_programado     [Break 2 programado fin]
             * @param  [String] $turno_inicio_real          [Turno realizado inicio]
             * @param  [String] $turno_fin_real             [Turno realizado fin]
             * @param  [String] $break_1_inicio_real        [Break 1 realizado inicio]
             * @param  [String] $break_1_fin_real           [Break 1 realizado fin]
             * @param  [String] $almuerzo_inicio_real       [Almuerzo realizado inicio]
             * @param  [String] $almuerzo_fin_real          [Almuerzo realizado fin]
             * @param  [String] $break_2_inicio_real        [Break 2 realizado inicio]
             * @param  [String] $break_2_fin_real           [Break 2 realizado fin]
             * @param  [String] $deslogueo_auto             [Respuesta de deslogueo automático (Si|No)]
             * @return [Integer]                            [Adherencia en segundos]
             */
            function calcularAdherencia($turno_inicio_programado, $turno_fin_programado, $break_1_inicio_programado, $break_1_fin_programado, $almuerzo_inicio_programado, $almuerzo_fin_programado, $break_2_inicio_programado, $break_2_fin_programado, $turno_inicio_real, $turno_fin_real, $break_1_inicio_real, $break_1_fin_real, $almuerzo_inicio_real, $almuerzo_fin_real, $break_2_inicio_real, $break_2_fin_real, $deslogueo_auto) {
                $segundos_adherencia = 0;
                if ($turno_inicio_real<$turno_fin_programado&&$turno_fin_real>$turno_inicio_programado) {
                    if ($turno_inicio_real<$turno_inicio_programado) {//si se logueó antes del turno programado
                        $turno_inicio_real = $turno_inicio_programado;
                    }
                    if ($turno_fin_real>$turno_fin_programado) {
                        $turno_fin_real = $turno_fin_programado;
                    }
                    $segundos_turno = diffSegundosFechas($turno_inicio_real, $turno_fin_real);
                    $segundos_almuerzo = validarCalcularFechas($almuerzo_inicio_programado, $almuerzo_fin_programado, $almuerzo_inicio_real, $almuerzo_fin_real);
                    $segundos_break_1 = validarCalcularFechas($break_1_inicio_programado, $break_1_fin_programado, $break_1_inicio_real, $break_1_fin_real);
                    $segundos_break_2 = validarCalcularFechas($break_2_inicio_programado, $break_2_fin_programado, $break_2_inicio_real, $break_2_fin_real);
                    $segundos_adherencia = $segundos_turno-$segundos_almuerzo-$segundos_break_1-$segundos_break_2;
                    if ($deslogueo_auto=="Si") {
                        $segundos_adherencia-=$segundos_adherencia*0.1;//penalización
                    }
                }
                return intval($segundos_adherencia);
            }
            /**
             * Función para definir las fecha a utilizar. Se utiliza en los turnos vampiro.
             * @param  [String] $fecha_1 [Fecha 1]
             * @param  [String] $fecha_2 [Fecha 2]
             * @return [String]          [Fecha a utilizar]
             */
            function definirFecha($fecha_1, $fecha_2) {
                if ($fecha_1!="") {
                    $retornar = $fecha_1;
                } elseif ($fecha_2!="") {
                    $retornar = $fecha_2;
                } else {
                    $retornar = "";
                }
                return $retornar;
            }
            /**
             * Función para validar si el turno realizado fue deslogueado automáticamente
             * @param  [String] $turno_fin_programado [Turno programado fin]
             * @param  [String] $turno_fin_real       [Turno realizado fin]
             * @param  [String] $turno_fin_real       [Hora formato H:is]
             * @return [String]                       [Validación Si|No]
             */
            function deslogueoAutomatico($turno_fin_programado, $turno_fin_real, $hora) {
                if (date("H:i:s", strtotime($turno_fin_programado))!=$hora&&(date("H:i:s", strtotime($turno_fin_real))==$hora)) {
                    return "Si";
                } else {
                    return "No";
                }
            }
            if (isset($_POST["exportar_reporte"])) {
                $tipo_reporte=validar_input($_POST['tipo_reporte']);
                $operacion=validar_input($_POST['operacion']);
                $fechas = validar_input($_POST["fechas"]);
                $fecha_inicio = substr($fechas, 0, 10);
                $fecha_fin = substr($fechas, 13)." 23:59:59";
                //validar permisos
                if ($perfil_modulo_plataforma=="Administrador"||$perfil_modulo_plataforma=="Gerente") {
                    $filtro_operacion_usuarios = "AND TOP.`ope_proyecto`='".$_SESSION['pro_id']."'";
                } else {
                    if (strpos($_SESSION['ope_nombre_operacion'], "CGP")!==false) {
                        if ($perfil_modulo_plataforma=="Gestor") {
                            $filtro_operacion_usuarios = "AND ((TOP.`ope_proyecto`='".$_SESSION['pro_id']."' AND TOP.`ope_nombre_operacion` LIKE '%CGP%') OR (TU.`usu_jefe_directo`='".$_SESSION['usu_id']."' OR TU.`usu_jefe_directo_2`='".$_SESSION['usu_id']."'))";
                        } elseif ($perfil_modulo_plataforma=="Líder") {
                            $filtro_operacion_usuarios = "AND (TU.`usu_jefe_directo`='".$_SESSION['usu_id']."' OR TU.`usu_jefe_directo_2`='".$_SESSION['usu_id']."')";
                        } elseif ($perfil_modulo_plataforma=="Líder-CGP") {
                            $filtro_operacion_usuarios = $_SESSION['ope_nombre_operacion']=="CGP-Fija" ? "AND ((TOP.`ope_proyecto`='".$_SESSION['pro_id']."' AND (TOP.`ope_nombre_operacion`='CGP-Fija' OR TOP.`ope_nombre_operacion`='CGP-Avianca')) OR (TU.`usu_jefe_directo`='".$_SESSION['usu_id']."' OR TU.`usu_jefe_directo_2`='".$_SESSION['usu_id']."'))" : "AND ((TOP.`ope_proyecto`='".$_SESSION['pro_id']."' AND TOP.`ope_nombre_operacion`='".$_SESSION['ope_nombre_operacion']."') OR (TU.`usu_jefe_directo`='".$_SESSION['usu_id']."' OR TU.`usu_jefe_directo_2`='".$_SESSION['usu_id']."'))";
                        }
                    } else {
                        if ($perfil_modulo_plataforma=="Gestor") {
                            $filtro_operacion_usuarios = "AND ((TOP.`ope_proyecto`='".$_SESSION['pro_id']."' AND TOP.`ope_nombre_operacion`='".$_SESSION["ope_nombre_operacion"]."') OR (TU.`usu_jefe_directo`='".$_SESSION['usu_id']."' OR TU.`usu_jefe_directo_2`='".$_SESSION['usu_id']."'))";
                        } elseif ($perfil_modulo_plataforma=="Líder") {
                            $filtro_operacion_usuarios = "AND TOP.`ope_proyecto`='".$_SESSION['pro_id']."' AND (TU.`usu_jefe_directo`='".$_SESSION['usu_id']."' OR TU.`usu_jefe_directo_2`='".$_SESSION['usu_id']."')";
                        }
                    }
                }
                if ($operacion=="Todos") {
                    $filtro_area_operacion=$filtro_operacion_usuarios;
                    $titulo_reporte="Todos_".$fecha_inicio."_".substr($fechas, 13);
                } else {
                    $filtro_operacion_parte=explode(" | ", $operacion);
                    $filtro_area_operacion="AND TOP.`ope_nombre_operacion`='".$filtro_operacion_parte[0]."' AND TOP.`ope_nombre_area`='".$filtro_operacion_parte[1]."'";
                    $titulo_reporte=$operacion."_".$fecha_inicio."_".substr($fechas, 13);
                }
                // Consultas a la BD
                if ($tipo_reporte=="Control Turnos") {
                    $consulta_turnos = mysqli_query($enlace_db, "SELECT `cot_id`, `cot_usuario`, `cot_turno_inicio`, `cot_turno_fin`, `cot_turno_duracion`, `cot_bk1_inicio`, `cot_bk1_fin`, `cot_bk1_duracion`, `cot_bk2_inicio`, `cot_bk2_fin`, `cot_bk2_duracion`, `cot_almuerzo_inicio`, `cot_almuerzo_fin`, `cot_almuerzo_duracion`, `cot_fuente`, `cot_fuente_nombre`, `cot_observaciones_inicio_turno`, `cot_logueo_inicial`, TU.`usu_nombres` AS USU_NOM, TU.`usu_apellidos` AS USU_APE, `ope_nombre_operacion`, `ope_nombre_area`, TR.`usu_nombres` AS NOM_RES, TR.`usu_apellidos` AS APE_RES, TU.`usu_estado_usuario` AS estado FROM `tb_control_turno` LEFT JOIN `db_configuracion_proyecto`.`tb_usuario` AS TU ON `tb_control_turno`.`cot_usuario`=TU.`usu_id` LEFT JOIN `db_configuracion_proyecto`.`tb_usuario` AS TR ON TU.`usu_jefe_directo`=TR.`usu_id` LEFT JOIN `db_configuracion_proyecto`.`tb_operacion_proyecto` AS TOP ON TU.`usu_cargo`=TOP.`ope_id` WHERE `cot_logueo_inicial`>='".$fecha_inicio."' AND `cot_logueo_inicial`<='".$fecha_fin."' AND TU.`usu_estado_usuario`='Activo' ".$filtro_area_operacion." ORDER BY TU.`usu_nombres`, TU.`usu_apellidos`, `cot_turno_inicio`, `cot_turno_fin`");
                    $resultado_turnos = mysqli_fetch_all($consulta_turnos);
                    $campos_turnos = count($resultado_turnos);
                } elseif ($tipo_reporte=="Control Adherencia") {
                    $consulta_turnos = mysqli_query($enlace_db, "SELECT TP.`cot_id`, TP.`cot_usuario`, TP.`cot_turno_inicio`, TP.`cot_turno_fin`, TP.`cot_turno_duracion`, TP.`cot_bk1_inicio`, TP.`cot_bk1_fin`, TP.`cot_bk1_duracion`, TP.`cot_bk2_inicio`, TP.`cot_bk2_fin`, TP.`cot_bk2_duracion`, TP.`cot_almuerzo_inicio`, TP.`cot_almuerzo_fin`, TP.`cot_almuerzo_duracion`, TP.`cot_tipo`, TP.`cot_fuente_nombre`, TP.`cot_observaciones_inicio_turno`, TP.`cot_fecha_registro`, TP.`cot_pk`, TU.`usu_nombres` AS USU_NOM, TU.`usu_apellidos` AS USU_APE, TOP.`ope_nombre_operacion`, TOP.`ope_nombre_area`, TUR.`usu_nombres` AS NOM_RES, TUR.`usu_apellidos` AS APE_RES, TU.`usu_estado_usuario` AS estado, TR.`cot_id`, TR.`cot_usuario`, TR.`cot_turno_inicio`, TR.`cot_turno_fin`, TR.`cot_turno_duracion`, TR.`cot_bk1_inicio`, TR.`cot_bk1_fin`, TR.`cot_bk1_duracion`, TR.`cot_bk2_inicio`, TR.`cot_bk2_fin`, TR.`cot_bk2_duracion`, TR.`cot_almuerzo_inicio`, TR.`cot_almuerzo_fin`, TR.`cot_almuerzo_duracion`, TR.`cot_fuente`, TR.`cot_fuente_nombre`, TR.`cot_observaciones_inicio_turno`, TR.`cot_logueo_inicial`, TR.`cot_pk` FROM `tb_control_turno_programado` AS TP LEFT JOIN `tb_control_turno` AS TR ON TP.`cot_pk`=TR.`cot_pk` LEFT JOIN `db_configuracion_proyecto`.`tb_usuario` AS TU ON TP.`cot_usuario`=TU.`usu_id` LEFT JOIN `db_configuracion_proyecto`.`tb_usuario` AS TUR ON TU.`usu_jefe_directo`=TUR.`usu_id` LEFT JOIN `db_configuracion_proyecto`.`tb_operacion_proyecto` AS TOP ON TU.`usu_cargo`=TOP.`ope_id` WHERE TP.`cot_turno_inicio`>='".$fecha_inicio."' AND TP.`cot_turno_inicio`<='".$fecha_fin."' AND TU.`usu_estado_usuario`='Activo' ".$filtro_area_operacion." AND TP.`cot_turno_inicio` NOT LIKE '%00:00:00' AND TP.`cot_turno_fin` NOT LIKE '%23:59:59' AND (TP.`cot_tipo`='Turno' OR TP.`cot_tipo`='Programar Turno') ORDER BY TOP.`ope_nombre_operacion`, TOP.`ope_nombre_area`, TU.`usu_nombres`, TU.`usu_apellidos`, TP.`cot_turno_inicio`, TP.`cot_turno_fin`");
                    $resultado_turnos = mysqli_fetch_all($consulta_turnos);
                    $consulta_turnos_vampiro = mysqli_query($enlace_db, "SELECT TP.`cot_id`, TP.`cot_usuario`, TP.`cot_turno_inicio`, TP.`cot_turno_fin`, TP.`cot_bk1_inicio`, TP.`cot_bk1_fin`, TP.`cot_bk2_inicio`, TP.`cot_bk2_fin`, TP.`cot_almuerzo_inicio`, TP.`cot_almuerzo_fin`, TP.`cot_tipo`, TP.`cot_observaciones_inicio_turno`, TP.`cot_fecha_registro`, TP.`cot_pk`, TU.`usu_nombres` AS USU_NOM, TU.`usu_apellidos` AS USU_APE, TOP.`ope_nombre_operacion`, TOP.`ope_nombre_area`, TUR.`usu_nombres` AS NOM_RES, TUR.`usu_apellidos` AS APE_RES, (SELECT TP2.`cot_turno_inicio` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_INICIO, (SELECT TP2.`cot_turno_fin` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_FIN, (SELECT TP2.`cot_bk1_inicio` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_BK1_INICIO, (SELECT TP2.`cot_bk1_fin` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_BK1_FIN, (SELECT TP2.`cot_bk2_inicio` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_BK2_INICIO, (SELECT TP2.`cot_bk2_fin` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_BK2_FIN, (SELECT TP2.`cot_almuerzo_inicio` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_ALMUERZO_INICIO, (SELECT TP2.`cot_almuerzo_fin` FROM `tb_control_turno_programado` AS TP2 WHERE TP2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TP2.`cot_turno_inicio` LIKE '%00:00:00') AS TP2_ALMUERZO_FIN, (SELECT TR.`cot_turno_inicio` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_INICIO, (SELECT TR.`cot_turno_fin` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_FIN, (SELECT TR.`cot_bk1_inicio` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_BK1_INICIO, (SELECT TR.`cot_bk1_fin` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_BK1_FIN, (SELECT TR.`cot_bk2_inicio` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_BK2_INICIO, (SELECT TR.`cot_bk2_fin` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_BK2_FIN, (SELECT TR.`cot_almuerzo_inicio` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_ALMUERZO_INICIO, (SELECT TR.`cot_almuerzo_fin` FROM `tb_control_turno` AS TR WHERE TR.`cot_pk`=TP.`cot_pk` AND TR.`cot_turno_fin` LIKE '%23:59:59') AS TR_ALMUERZO_FIN, (SELECT TR2.`cot_turno_inicio` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_INICIO, (SELECT TR2.`cot_turno_fin` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_FIN, (SELECT TR2.`cot_bk1_inicio` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_BK1_INICIO, (SELECT TR2.`cot_bk1_fin` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_BK1_FIN, (SELECT TR2.`cot_bk2_inicio` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_BK2_INICIO, (SELECT TR2.`cot_bk2_fin` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_BK2_FIN, (SELECT TR2.`cot_almuerzo_inicio` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_ALMUERZO_INICIO, (SELECT TR2.`cot_almuerzo_fin` FROM `tb_control_turno` AS TR2 WHERE TR2.`cot_pk`=CONCAT(TP.`cot_usuario`, SUBSTR(DATE_ADD(TP.`cot_turno_inicio`, INTERVAL 1 DAY), 1, 10)) AND TR2.`cot_turno_inicio` LIKE '%00:00:00') AS TR2_ALMUERZO_FIN FROM `tb_control_turno_programado` AS TP LEFT JOIN `db_configuracion_proyecto`.`tb_usuario` AS TU ON TP.`cot_usuario`=TU.`usu_id` LEFT JOIN `db_configuracion_proyecto`.`tb_usuario` AS TUR ON TU.`usu_jefe_directo`=TUR.`usu_id` LEFT JOIN `db_configuracion_proyecto`.`tb_operacion_proyecto` AS TOP ON TU.`usu_cargo`=TOP.`ope_id` WHERE TP.`cot_turno_inicio`>='".$fecha_inicio."' AND TP.`cot_turno_inicio`<='".$fecha_fin."' AND (TU.`usu_estado_usuario`='Activo' OR TU.`usu_estado_usuario`='Vacaciones') ".$filtro_area_operacion." AND TP.`cot_turno_fin` LIKE '%23:59:59' ORDER BY TOP.`ope_nombre_operacion`, TOP.`ope_nombre_area`, TU.`usu_nombres`, TU.`usu_apellidos`, TP.`cot_turno_inicio`, TP.`cot_turno_fin`");
                    $resultado_turnos_vampiro = mysqli_fetch_all($consulta_turnos_vampiro);
                    $matrizTotal = [];
                    //para turnos jornada diurna
                    if (count($resultado_turnos)>0) {
                        for ($i=0; $i<count($resultado_turnos); $i++) {
                            //programado
                            $id_registro_programado = $resultado_turnos[$i][0];
                            $id_usuario = $resultado_turnos[$i][1];
                            $turno_programado_inicio = $resultado_turnos[$i][2];
                            $turno_programado_fin = $resultado_turnos[$i][3];
                            $turno_programado_duracion = $resultado_turnos[$i][4];
                            $break_1_programado_inicio = $resultado_turnos[$i][5];
                            $break_1_programado_fin = $resultado_turnos[$i][6];
                            $break_1_programado_duracion = $resultado_turnos[$i][7];
                            $break_2_programado_inicio = $resultado_turnos[$i][8];
                            $break_2_programado_fin = $resultado_turnos[$i][9];
                            $break_2_programado_duracion = $resultado_turnos[$i][10];
                            $almuerzo_programado_inicio = $resultado_turnos[$i][11];
                            $almuerzo_programado_fin = $resultado_turnos[$i][12];
                            $almuerzo_programado_duracion = $resultado_turnos[$i][13];
                            $tipo_turno = $resultado_turnos[$i][14];
                            $pk_programado = $resultado_turnos[$i][18];
                            //registrado
                            $id_registro_registrado = $resultado_turnos[$i][26];
                            $turno_registrado_inicio = $resultado_turnos[$i][28];
                            $turno_registrado_fin = $resultado_turnos[$i][29];
                            $turno_registrado_duracion = $resultado_turnos[$i][30];
                            $break_1_registrado_inicio = $resultado_turnos[$i][31];
                            $break_1_registrado_fin = $resultado_turnos[$i][32];
                            $break_1_registrado_duracion = $resultado_turnos[$i][33];
                            $break_2_registrado_inicio = $resultado_turnos[$i][34];
                            $break_2_registrado_fin = $resultado_turnos[$i][35];
                            $break_2_registrado_duracion = $resultado_turnos[$i][36];
                            $almuerzo_registrado_inicio = $resultado_turnos[$i][37];
                            $almuerzo_registrado_fin = $resultado_turnos[$i][38];
                            $almuerzo_registrado_duracion = $resultado_turnos[$i][39];
                            $pk_registrado = $resultado_turnos[$i][44];
                            //otras
                            $fecha_turno = date("Y-m-d", strtotime($turno_programado_inicio));
                            $nombre_usuario = $resultado_turnos[$i][19]." ".$resultado_turnos[$i][20];
                            $operacion = $resultado_turnos[$i][21];
                            $area = $resultado_turnos[$i][22];
                            $nombre_usuario_responsable = $resultado_turnos[$i][23]." ".$resultado_turnos[$i][24];
                            //cálculos
                            $total_real = 0;
                            $total_adherencia = 0;
                            $deslogueo_auto = "";
                            $total_programado = calcularTotal($turno_programado_inicio, $turno_programado_fin, $almuerzo_programado_inicio, $almuerzo_programado_fin);//total programado
                            if ($id_registro_registrado!="" && $id_registro_registrado!=null) {//validar que haya registro del turno relizado
                                if ($turno_registrado_fin!="") {//validar que se haya finalizado el turno
                                    $deslogueo_auto = deslogueoAutomatico($turno_programado_fin, $turno_registrado_fin, "23:59:59");//validar deslogueo
                                    $total_real = calcularTotal($turno_registrado_inicio, ($deslogueo_auto=="Si" ? $turno_programado_fin : $turno_registrado_fin), $almuerzo_registrado_inicio, $almuerzo_registrado_fin);//total realizado
                                    $total_adherencia = calcularAdherencia($turno_programado_inicio, $turno_programado_fin, $break_1_programado_inicio, $break_1_programado_fin, $almuerzo_programado_inicio, $almuerzo_programado_fin, $break_2_programado_inicio, $break_2_programado_fin, $turno_registrado_inicio, $turno_registrado_fin, $break_1_registrado_inicio, $break_1_registrado_fin, $almuerzo_registrado_inicio, $almuerzo_registrado_fin, $break_2_registrado_inicio, $break_2_registrado_fin, $deslogueo_auto);//total adherencia
                                }
                            }
                            //agregar fila a la matriz
                            $matrizTotal[] = [$fecha_turno, $id_usuario, $nombre_usuario, $turno_programado_inicio, $turno_programado_fin, $break_1_programado_inicio, $break_1_programado_fin, $almuerzo_programado_inicio, $almuerzo_programado_fin, $break_2_programado_inicio, $break_2_programado_fin, $total_programado, $turno_registrado_inicio, $turno_registrado_fin, $break_1_registrado_inicio, $break_1_registrado_fin, $almuerzo_registrado_inicio, $almuerzo_registrado_fin, $break_2_registrado_inicio, $break_2_registrado_fin, $total_real, $total_adherencia, $deslogueo_auto, $operacion, $area, $nombre_usuario_responsable];
                        }
                    }
                    //para turnos jornada nocturna (vampiros)
                    if (count($resultado_turnos_vampiro)>0) {
                        for ($i=0; $i<count($resultado_turnos_vampiro); $i++) {
                            //programado 1
                            $tp1_id_registro_programado = $resultado_turnos_vampiro[$i][0];
                            $tp1_id_usuario = $resultado_turnos_vampiro[$i][1];
                            $tp1_turno_programado_inicio = $resultado_turnos_vampiro[$i][2];
                            $tp1_turno_programado_fin = $resultado_turnos_vampiro[$i][3];
                            $tp1_break_1_programado_inicio = $resultado_turnos_vampiro[$i][4];
                            $tp1_break_1_programado_fin = $resultado_turnos_vampiro[$i][5];
                            $tp1_break_2_programado_inicio = $resultado_turnos_vampiro[$i][6];
                            $tp1_break_2_programado_fin = $resultado_turnos_vampiro[$i][7];
                            $tp1_almuerzo_programado_inicio = $resultado_turnos_vampiro[$i][8];
                            $tp1_almuerzo_programado_fin = $resultado_turnos_vampiro[$i][9];
                            $tp1_tipo_turno = $resultado_turnos_vampiro[$i][10];
                            $tp1_pk_programado = $resultado_turnos_vampiro[$i][13];
                            //programado 2
                            $tp2_turno_programado_inicio = $resultado_turnos_vampiro[$i][20];
                            $tp2_turno_programado_fin = $resultado_turnos_vampiro[$i][21];
                            $tp2_break_1_programado_inicio = $resultado_turnos_vampiro[$i][22];
                            $tp2_break_1_programado_fin = $resultado_turnos_vampiro[$i][23];
                            $tp2_break_2_programado_inicio = $resultado_turnos_vampiro[$i][24];
                            $tp2_break_2_programado_fin = $resultado_turnos_vampiro[$i][25];
                            $tp2_almuerzo_programado_inicio = $resultado_turnos_vampiro[$i][26];
                            $tp2_almuerzo_programado_fin = $resultado_turnos_vampiro[$i][27];
                            //registrado
                            $tr1_turno_registrado_inicio = $resultado_turnos_vampiro[$i][28];
                            $tr1_turno_registrado_fin = $resultado_turnos_vampiro[$i][29];
                            $tr1_break_1_registrado_inicio = $resultado_turnos_vampiro[$i][30];
                            $tr1_break_1_registrado_fin = $resultado_turnos_vampiro[$i][31];
                            $tr1_break_2_registrado_inicio = $resultado_turnos_vampiro[$i][32];
                            $tr1_break_2_registrado_fin = $resultado_turnos_vampiro[$i][33];
                            $tr1_almuerzo_registrado_inicio = $resultado_turnos_vampiro[$i][34];
                            $tr1_almuerzo_registrado_fin = $resultado_turnos_vampiro[$i][35];
                            //registrado 2
                            $tr2_turno_registrado_inicio = $resultado_turnos_vampiro[$i][36];
                            $tr2_turno_registrado_fin = $resultado_turnos_vampiro[$i][37];
                            $tr2_break_1_registrado_inicio = $resultado_turnos_vampiro[$i][38];
                            $tr2_break_1_registrado_fin = $resultado_turnos_vampiro[$i][39];
                            $tr2_break_2_registrado_inicio = $resultado_turnos_vampiro[$i][40];
                            $tr2_break_2_registrado_fin = $resultado_turnos_vampiro[$i][41];
                            $tr2_almuerzo_registrado_inicio = $resultado_turnos_vampiro[$i][42];
                            $tr2_almuerzo_registrado_fin = $resultado_turnos_vampiro[$i][43];
                            //otros
                            $fecha_turno = date("Y-m-d", strtotime($tp1_turno_programado_inicio));
                            $nombre_usuario = $resultado_turnos_vampiro[$i][14]." ".$resultado_turnos_vampiro[$i][15];
                            $operacion = $resultado_turnos_vampiro[$i][16];
                            $area = $resultado_turnos_vampiro[$i][17];
                            $nombre_usuario_responsable = $resultado_turnos_vampiro[$i][18]." ".$resultado_turnos_vampiro[$i][19];
                            //Definir datos para realizar cálculos turno programado
                            $fecha_break_1_inicio_programado = definirFecha($tp1_break_1_programado_inicio, $tp2_break_1_programado_inicio);
                            $fecha_break_1_fin_programado = definirFecha($tp1_break_1_programado_fin, $tp2_break_1_programado_fin);
                            $fecha_almuerzo_inicio_programado = definirFecha($tp1_almuerzo_programado_inicio, $tp2_almuerzo_programado_inicio);
                            $fecha_almuerzo_fin_programado = definirFecha($tp1_almuerzo_programado_fin, $tp2_almuerzo_programado_fin);
                            $fecha_break_2_inicio_programado = definirFecha($tp1_break_2_programado_inicio, $tp2_break_2_programado_inicio);
                            $fecha_break_2_fin_programado = definirFecha($tp1_break_2_programado_fin, $tp2_break_2_programado_fin);
                            //Definir datos para realizar cálculos turno realizado
                            $fecha_break_1_inicio_registrado = definirFecha($tr1_break_1_registrado_inicio, $tr2_break_1_registrado_inicio);
                            $fecha_break_1_fin_registrado = definirFecha($tr1_break_1_registrado_fin, $tr2_break_1_registrado_fin);
                            $fecha_almuerzo_inicio_registrado = definirFecha($tr1_almuerzo_registrado_inicio, $tr2_almuerzo_registrado_inicio);
                            $fecha_almuerzo_fin_registrado = definirFecha($tr1_almuerzo_registrado_fin, $tr2_almuerzo_registrado_fin);
                            $fecha_break_2_inicio_registrado = definirFecha($tr1_break_2_registrado_inicio, $tr2_break_2_registrado_inicio);
                            $fecha_break_2_fin_registrado = definirFecha($tr1_almuerzo_registrado_fin, $tr2_almuerzo_registrado_fin);
                            //cálculos
                            $total_real = 0;
                            $total_adherencia = 0;
                            $deslogueo_auto_vampiro = "";
                            if ($tr1_turno_registrado_inicio!="" && $tr1_turno_registrado_inicio!=null) {//validar que haya registro del turno relizado
                                if ($tr2_turno_registrado_fin!="") {//validar que se haya finalizado el turno
                                    $deslogueo_auto_vampiro = deslogueoAutomatico($tp2_turno_programado_fin, $tr2_turno_registrado_fin, "12:00:00");//validar deslogueo
                                    $total_real = calcularTotal($tr1_turno_registrado_inicio, ($deslogueo_auto_vampiro=="Si" ? $tp2_turno_programado_fin : $tr2_turno_registrado_fin), $fecha_almuerzo_inicio_registrado, $fecha_almuerzo_fin_registrado);//total realizado
                                    $total_adherencia = calcularAdherencia($tp1_turno_programado_inicio, $tp2_turno_programado_fin, $fecha_break_1_inicio_programado, $fecha_break_1_fin_programado, $fecha_almuerzo_inicio_programado, $fecha_almuerzo_fin_programado, $fecha_break_2_inicio_programado, $fecha_break_2_fin_programado, $tr1_turno_registrado_inicio, $tr2_turno_registrado_fin, $fecha_break_1_inicio_registrado, $fecha_break_1_fin_registrado, $fecha_almuerzo_inicio_registrado, $fecha_almuerzo_fin_registrado, $fecha_break_2_inicio_registrado, $fecha_break_2_fin_registrado, $deslogueo_auto_vampiro);//total adherencia
                                }
                            }
                            $total_programado = calcularTotal($tp1_turno_programado_inicio, $tp2_turno_programado_fin, $fecha_almuerzo_inicio_programado, $fecha_almuerzo_fin_programado);//total programado
                            //agregar fila a la matriz
                            $matrizTotal[] = [$fecha_turno, $tp1_id_usuario, $nombre_usuario, $tp1_turno_programado_inicio, $tp2_turno_programado_fin, $fecha_break_1_inicio_programado, $fecha_break_1_fin_programado, $fecha_almuerzo_inicio_programado, $fecha_almuerzo_fin_programado, $fecha_break_2_inicio_programado, $fecha_break_2_fin_programado, $total_programado, $tr1_turno_registrado_inicio, $tr2_turno_registrado_fin, $fecha_break_1_inicio_registrado, $fecha_break_1_fin_registrado, $fecha_almuerzo_inicio_registrado, $fecha_almuerzo_fin_registrado, $fecha_break_2_inicio_registrado, $fecha_break_2_fin_registrado, $total_real, $total_adherencia, $deslogueo_auto_vampiro, $operacion, $area, $nombre_usuario_responsable];
                        }
                        // ORDER BY OPERACIÓN, ÁREA, NOMBRE USUARIO, TURNO INICIO, TURNO FIN
                        $sort = array();
                        foreach ($matrizTotal as $key=>$valor) {
                            $sort[23][$key] = $valor[23];
                            $sort[24][$key] = $valor[24];
                            $sort[2][$key] = $valor[2];
                            $sort[3][$key] = $valor[3];
                            $sort[4][$key] = $valor[4];
                        }
                        array_multisort($sort[23], SORT_ASC, $sort[24], SORT_ASC, $sort[2], SORT_ASC, $sort[3], SORT_ASC, $sort[4], SORT_ASC, $matrizTotal);
                    }
                    $campos_turnos = count($matrizTotal);
                } else {
                    // cuando hay error
                    session_start();
                    $_SESSION["progress_bar"] = 100;
                    header_remove('Set-Cookie');
                    session_write_close();
                    header("Location:../index.php");
                }
// Creamos nueva instancia de PHPExcel 
$objPHPExcel = new PHPExcel();
// Establecer propiedades
$objPHPExcel->getProperties()
->setCreator("Oesía Group | Outsourcing IV")
->setLastModifiedBy($_SESSION['usu_nombre_completo'])
->setTitle("Reporte ".$tipo_reporte)
->setSubject("Reporte ".$tipo_reporte)
->setDescription("Reporte ".$tipo_reporte)
->setKeywords("Reporte ".$tipo_reporte)
->setCategory("Reporte");
//Estilos para todo el libro de excel
$objPHPExcel->getDefaultStyle()->getfont()->setName("Calibri");
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
$styleArrayTitulos = array(
'font' => array(
'bold' => true,
'size' =>'8',
'name' =>'Arial',
'color' => array('rgb' => 'FFFFFF')
),
'alignment' => array(
'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
'wrap'=>true,
),
'fill' => array(
'type' => PHPExcel_Style_Fill::FILL_SOLID,
'color' => array('rgb' => '7A1F5E')
)
);
$styleArrayTitulos_2 = array(
'font' => array(
'bold' => true,
'size' =>'8',
'name' =>'Arial',
'color' => array('rgb' => 'FFFFFF')
),

'alignment' => array(
'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
'wrap'=>true,
),
'fill' => array(
'type' => PHPExcel_Style_Fill::FILL_SOLID,
'color' => array('rgb' => '76933C')
)
);
$styleArrayTitulos_3 = array(
'font' => array(
'bold' => true,
'size' =>'8',
'name' =>'Arial',
'color' => array('rgb' => 'FFFFFF')
),

'alignment' => array(
'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
'wrap'=>true,
),
'fill' => array(
'type' => PHPExcel_Style_Fill::FILL_SOLID,
'color' => array('rgb' => '0F243E')
)
);
$styleArrayTitulos_4 = array(
'font' => array(
'bold' => true,
'size' =>'8',
'name' =>'Arial',
'color' => array('rgb' => 'FFFFFF')
),

'alignment' => array(
'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
'wrap'=>true,
),
'fill' => array(
'type' => PHPExcel_Style_Fill::FILL_SOLID,
'color' => array('rgb' => '00B050')
)
);
$styleArrayContenido = array(
'alignment' => array(
'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
'wrap'=>true,
),
);
    //Activar Hoja 0 Datos Cliente
    $objPHPExcel->setActiveSheetIndex(0);
    //Estilos de la Hoja 0
    $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(21);
    if ($tipo_reporte=="Control Turnos") {
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(23);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
    } else {
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);    
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(11);
    }
    $objPHPExcel->getActiveSheet()->getStyle('A1:W1')->applyFromArray($styleArrayTitulos);
    $objPHPExcel->getActiveSheet()->setAutoFilter('A1:W1');
    // Congelar fila de títulos
    $objPHPExcel->getActiveSheet()->freezePane('A2');

    // Escribiendo los titulos
    if ($tipo_reporte=="Control Turnos") {
        $objPHPExcel->getActiveSheet()->setCellValue('A1','Doc. Identidad');
        $objPHPExcel->getActiveSheet()->setCellValue('B1','Nombre(s) y Apellidos');
        $objPHPExcel->getActiveSheet()->setCellValue('C1','Área');
        $objPHPExcel->getActiveSheet()->setCellValue('D1','Cargo');
        $objPHPExcel->getActiveSheet()->setCellValue('E1','Estado');
        $objPHPExcel->getActiveSheet()->setCellValue('F1','Responsable');
        $objPHPExcel->getActiveSheet()->setCellValue('G1','Inicio Sesión (Fecha)');
        $objPHPExcel->getActiveSheet()->setCellValue('H1','Inicio Sesión (Hora)');
        $objPHPExcel->getActiveSheet()->setCellValue('I1','Inicio Turno');
        $objPHPExcel->getActiveSheet()->setCellValue('J1','Fin Turno');
        $objPHPExcel->getActiveSheet()->setCellValue('K1','Duración Turno (Segundos)');
        $objPHPExcel->getActiveSheet()->setCellValue('L1','Inicio Break 1');
        $objPHPExcel->getActiveSheet()->setCellValue('M1','Fin Break 1');
        $objPHPExcel->getActiveSheet()->setCellValue('N1','Duración Break 1 (Segundos)');
        $objPHPExcel->getActiveSheet()->setCellValue('O1','Inicio Almuerzo');
        $objPHPExcel->getActiveSheet()->setCellValue('P1','Fin Almuerzo');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1','Duración Almuerzo (Segundos)');
        $objPHPExcel->getActiveSheet()->setCellValue('R1','Inicio Break 2');
        $objPHPExcel->getActiveSheet()->setCellValue('S1','Fin Break 2');
        $objPHPExcel->getActiveSheet()->setCellValue('T1','Duración Break 2 (Segundos)');
        $objPHPExcel->getActiveSheet()->setCellValue('U1','Observaciones');
        $objPHPExcel->getActiveSheet()->setCellValue('V1','IP');
        $objPHPExcel->getActiveSheet()->setCellValue('W1','IP Nombre');
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A1','Fecha Turno');
        $objPHPExcel->getActiveSheet()->setCellValue('B1','No. Identificación');
        $objPHPExcel->getActiveSheet()->setCellValue('C1','Nombre y Apellido');
        $objPHPExcel->getActiveSheet()->setCellValue('D1','Inicio Turno Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('E1','Fin Turno Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('F1','Inicio Break 1 Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('G1','Fin Break 1 Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('H1','Inicio Almuerzo Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('I1','Fin Almuerzo Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('J1','Inicio Break 2 Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('K1','Fin Break 2 Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('L1','Total Programado');
        $objPHPExcel->getActiveSheet()->setCellValue('M1','Inicio Turno Real');
        $objPHPExcel->getActiveSheet()->setCellValue('N1','Fin Turno Real');
        $objPHPExcel->getActiveSheet()->setCellValue('O1','Inicio Break 1 Real');
        $objPHPExcel->getActiveSheet()->setCellValue('P1','Fin Break 1 Real');
        $objPHPExcel->getActiveSheet()->setCellValue('Q1','Inicio Almuerzo Real');
        $objPHPExcel->getActiveSheet()->setCellValue('R1','Fin Almuerzo Real');
        $objPHPExcel->getActiveSheet()->setCellValue('S1','Inicio Break 2 Real');
        $objPHPExcel->getActiveSheet()->setCellValue('T1','Fin Break 2 Real');
        $objPHPExcel->getActiveSheet()->setCellValue('U1','Total Real');
        $objPHPExcel->getActiveSheet()->setCellValue('V1','Total Adherencia');
        $objPHPExcel->getActiveSheet()->setCellValue('W1','Deslogueo Automático');
    }
    //Ingresar Data consultada a partir de la fila 2
    if ($tipo_reporte=="Control Turnos") {
        for ($i=2; $i < $campos_turnos+2; $i++) {
            $fecha_inicio_sesion=explode(" ", $resultado_turnos[$i-2][17]);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$resultado_turnos[$i-2][1]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$resultado_turnos[$i-2][18]." ".$resultado_turnos[$i-2][19]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$resultado_turnos[$i-2][20]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$resultado_turnos[$i-2][21]);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$resultado_turnos[$i-2][24]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$resultado_turnos[$i-2][22]." ".$resultado_turnos[$i-2][23]);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$fecha_inicio_sesion[0]);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$fecha_inicio_sesion[1]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$resultado_turnos[$i-2][2]);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,$resultado_turnos[$i-2][3]);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,$resultado_turnos[$i-2][4]);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,$resultado_turnos[$i-2][5]);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,$resultado_turnos[$i-2][6]);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$resultado_turnos[$i-2][7]);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$i,$resultado_turnos[$i-2][11]);
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$i,$resultado_turnos[$i-2][12]);
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,$resultado_turnos[$i-2][13]);
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$i,$resultado_turnos[$i-2][8]);
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$i,$resultado_turnos[$i-2][9]);
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$i,$resultado_turnos[$i-2][10]);
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$i,$resultado_turnos[$i-2][16]);
            $objPHPExcel->getActiveSheet()->setCellValue('V'.$i,$resultado_turnos[$i-2][14]);
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$i,$resultado_turnos[$i-2][15]);
            //porcentaje
            session_start();
            $porcentaje = intval(bcdiv(bcmul($i-2, 100), ($campos_turnos)));
            $_SESSION["progress_bar"] = ($porcentaje==100 ? 99 : $porcentaje);
            header_remove('Set-Cookie');
            session_write_close();
        }
    } else {
        for ($i=2; $i < $campos_turnos+2; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$matrizTotal[$i-2][0]);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$matrizTotal[$i-2][1]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$matrizTotal[$i-2][2]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$matrizTotal[$i-2][3]);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$matrizTotal[$i-2][4]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$matrizTotal[$i-2][5]);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$matrizTotal[$i-2][6]);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$matrizTotal[$i-2][7]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$matrizTotal[$i-2][8]);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,$matrizTotal[$i-2][9]);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,$matrizTotal[$i-2][10]);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,$matrizTotal[$i-2][11]);
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,$matrizTotal[$i-2][12]);
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$matrizTotal[$i-2][13]);
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$i,$matrizTotal[$i-2][14]);
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$i,$matrizTotal[$i-2][15]);
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,$matrizTotal[$i-2][16]);
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$i,$matrizTotal[$i-2][17]);
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$i,$matrizTotal[$i-2][18]);
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$i,$matrizTotal[$i-2][19]);
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$i,$matrizTotal[$i-2][20]);
            $objPHPExcel->getActiveSheet()->setCellValue('V'.$i,$matrizTotal[$i-2][21]);
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$i,$matrizTotal[$i-2][22]);
            //porcentaje
            session_start();
            $porcentaje = intval(bcdiv(bcmul($i-2, 100), ($campos_turnos)));
            $_SESSION["progress_bar"] = ($porcentaje==100 ? 99 : $porcentaje);
            header_remove('Set-Cookie');
            session_write_close();
        }
    }

// Nombramos la hoja 0
if ($tipo_reporte=="Control Turnos") {
    $objPHPExcel->getActiveSheet()->setTitle('Control Turnos');
    $nombre_cta = "CT";
} else {
    $objPHPExcel->getActiveSheet()->setTitle('Control Adherencia');
    $nombre_cta = "CT-Adherencia";
}

// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename='.$nombre_cta.'-'.$titulo_reporte.'.xlsx');
header('Cache-Control: max-age=0');
// Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
//porcentaje
session_start();
$_SESSION["progress_bar"] = 100;
session_write_close();
// reiniciar variable a 0
sleep(1);
session_start();
$_SESSION["progress_bar"] = 0;
            }
        }
    }
?>