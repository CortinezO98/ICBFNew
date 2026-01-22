<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos-Malla";
    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    require_once("../config/validar_festivos.php");
    require_once('../PHPOffice/vendor/autoload.php');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

    //array columnas
        $array_columnas[]="A";
        $array_columnas[]="B";
        $array_columnas[]="C";
        $array_columnas[]="D";
        $array_columnas[]="E";
        $array_columnas[]="F";
        $array_columnas[]="G";
        $array_columnas[]="H";
        $array_columnas[]="I";
        $array_columnas[]="J";
        $array_columnas[]="K";
        $array_columnas[]="L";
        $array_columnas[]="M";
        $array_columnas[]="N";
        $array_columnas[]="O";
        $array_columnas[]="P";
        $array_columnas[]="Q";
        $array_columnas[]="R";
        $array_columnas[]="S";
        $array_columnas[]="T";
        $array_columnas[]="U";
        $array_columnas[]="V";
        $array_columnas[]="W";
        $array_columnas[]="X";
        $array_columnas[]="Y";
        $array_columnas[]="Z";
        $array_columnas[]="AA";
        $array_columnas[]="AB";
        $array_columnas[]="AC";
        $array_columnas[]="AD";
        $array_columnas[]="AE";
        $array_columnas[]="AF";
        $array_columnas[]="AG";
        $array_columnas[]="AH";
        $array_columnas[]="AI";
        $array_columnas[]="AJ";
        $array_columnas[]="AK";
        $array_columnas[]="AL";
        $array_columnas[]="AM";
        $array_columnas[]="AN";
        $array_columnas[]="AO";
        $array_columnas[]="AP";
        $array_columnas[]="AQ";
        $array_columnas[]="AR";
        $array_columnas[]="AS";
        $array_columnas[]="AT";
        $array_columnas[]="AU";
        $array_columnas[]="AV";
        $array_columnas[]="AW";
        $array_columnas[]="AX";
        $array_columnas[]="AY";
        $array_columnas[]="AZ";
        $array_columnas[]="BA";
        $array_columnas[]="BB";
        $array_columnas[]="BC";
        $array_columnas[]="BD";
        $array_columnas[]="BE";
        $array_columnas[]="BF";
        $array_columnas[]="BG";
        $array_columnas[]="BH";
        $array_columnas[]="BI";
        $array_columnas[]="BJ";
        $array_columnas[]="BK";
        $array_columnas[]="BL";
        $array_columnas[]="BM";
        $array_columnas[]="BN";
        $array_columnas[]="BO";
        $array_columnas[]="BP";
        $array_columnas[]="BQ";
        $array_columnas[]="BR";
        $array_columnas[]="BS";
        $array_columnas[]="BT";
        $array_columnas[]="BU";
        $array_columnas[]="BV";
        $array_columnas[]="BW";
        $array_columnas[]="BX";
        $array_columnas[]="BY";
        $array_columnas[]="BZ";
        $array_columnas[]="CA";
        $array_columnas[]="CB";
        $array_columnas[]="CC";
        $array_columnas[]="CD";
        $array_columnas[]="CE";
        $array_columnas[]="CF";
        $array_columnas[]="CG";
        $array_columnas[]="CH";
        $array_columnas[]="CI";
        $array_columnas[]="CJ";
        $array_columnas[]="CK";
        $array_columnas[]="CL";
        $array_columnas[]="CM";
        $array_columnas[]="CN";
        $array_columnas[]="CO";
        $array_columnas[]="CP";
        $array_columnas[]="CQ";
        $array_columnas[]="CR";
        $array_columnas[]="CS";
        $array_columnas[]="CT";
        $array_columnas[]="CU";
        $array_columnas[]="CV";
        $array_columnas[]="CW";
        $array_columnas[]="CX";
        $array_columnas[]="CY";
        $array_columnas[]="CZ";
    //array columnas

    if(isset($_POST["exportar_reporte"])){
        $tipo_reporte=validar_input($_POST['tipo_reporte']);
        $campania=validar_input($_POST['campania']);
        $fecha_inicio=validar_input($_POST['fecha_inicio']);
        $fecha_fin=validar_input($_POST['fecha_fin']).' 23:59:59';
        $titulo_reporte="Gestión Turnos-".$tipo_reporte." ".date('Y-m-d H_i_s').".xlsx";
        
        // Inicializa variable tipo array
        $data_consulta=array();
        array_push($data_consulta, $fecha_inicio);
        array_push($data_consulta, $fecha_fin);

        $filtro_campania="";
        if ($campania!="Todas") {
            $filtro_campania=" AND TU.`usu_campania`=?";
            array_push($data_consulta, $campania);
        }

        if ($tipo_reporte=='Malla') {
            $consulta_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TU.`usu_usuario_red`, TC.`ac_nombre_campania`, TR.`usu_nombres_apellidos` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TU.`usu_supervisor`=TR.`usu_id` WHERE `cotm_inicio`>=? AND `cotm_inicio`<=? ".$filtro_campania." ORDER BY `cotm_inicio`";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            if (count($data_consulta)>0) {
                // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
                $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
            }
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
        } elseif ($tipo_reporte=='Conexión') {
            $consulta_string="SELECT `cot_id`, `cot_turno_malla`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion`, `cot_fuente`, `cot_observaciones_inicio`, `cot_observaciones_fin`, `cot_registro_fecha`, TM.`cotm_id`, TM.`cotm_usuario`, TM.`cotm_tipo`, TM.`cotm_inicio`, TM.`cotm_fin`, TM.`cotm_duracion`, TM.`cotm_jornada`, TM.`cotm_observaciones_inicio`, TM.`cotm_observaciones_fin`, TM.`cotm_registro_fecha`, TU.`usu_nombres_apellidos`, TU.`usu_usuario_red` FROM `tb_control_turno` LEFT JOIN `tb_control_turno_malla` AS TM ON `tb_control_turno`.`cot_turno_malla`=TM.`cotm_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno`.`cot_usuario`=TU.`usu_id` WHERE `cot_inicio`>=? AND `cot_inicio`<=? ".$filtro_campania." ORDER BY `cot_inicio`";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            if (count($data_consulta)>0) {
                // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
                $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
                
            }
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
        } elseif ($tipo_reporte=='Recargos-Interno') {
            $consulta_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, `cotm_estado`, TU.`usu_nombres_apellidos`, TU.`usu_cargo_rol`, TU.`usu_estado`, TC.`ac_nombre_campania`, TR.`usu_nombres_apellidos`, TTR.`cot_inicio`, TTR.`cot_fin` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TU.`usu_supervisor`=TR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` LEFT JOIN `tb_control_turno` AS TTR ON `tb_control_turno_malla`.`cotm_id`=TTR.`cot_turno_malla` WHERE `cotm_tipo`='turno' AND `cotm_inicio`>=? AND `cotm_inicio`<=? ".$filtro_campania." ORDER BY `cotm_usuario`, `cotm_inicio`";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            if (count($data_consulta)>0) {
                // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
                $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
            }
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
            
            for ($i=0; $i < count($resultado_registros); $i++) {
                $array_usuarios_id[]=$resultado_registros[$i][1];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['doc_usuario']=$resultado_registros[$i][1];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['nombres_apellidos']=$resultado_registros[$i][11];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['segmento']=$resultado_registros[$i][14];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['cargo']=$resultado_registros[$i][12];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['estado']=$resultado_registros[$i][13];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['responsable']=$resultado_registros[$i][15];
                if ($resultado_registros[$i][16]!="") {
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['conexion'][$resultado_registros[$i][0]]=$resultado_registros[$i][16].' A '.$resultado_registros[$i][17];
                } else {
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['conexion'][$resultado_registros[$i][0]]="";
                }

                $fecha_inicio_turno=$resultado_registros[$i][3];
                $fecha_fin_turno=str_replace('24:00', '00:00', $resultado_registros[$i][4]);
                $dia_inicio=date('Y-m-d', strtotime($fecha_inicio_turno));
                $dia_fin=date('Y-m-d', strtotime($fecha_fin_turno));
                $tipo_recargo=validarTipoTurno($dia_inicio);
                $array_usuarios_detalle[$resultado_registros[$i][1]]['festivos']+=0;
                if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['festivos']+=1;
                }

                $array_temp=validaRecargos($fecha_inicio_turno, $fecha_fin_turno, $array_horarios, $array_horarios_recargo);
                
                if ($dia_fin==$dia_inicio) {
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    }
                } elseif ($dia_fin>$dia_inicio) {
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    }

                    $tipo_recargo=validarTipoTurno($dia_fin);
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_fin][$tipo_recargo]['diurno']+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_fin][$tipo_recargo]['nocturno']+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                    }
                }
            }
            
            $array_usuarios_id=array_values(array_unique($array_usuarios_id));
        } elseif ($tipo_reporte=='Recargos-Cliente') {
            $consulta_string_recargo="SELECT `ctmcr_id`, `ctmcr_mes`, `ctmcr_usuario`, `ctmcr_registro_usuario`, `ctmcr_registro_fecha`, TU.`usu_nombres_apellidos`, TUR.`usu_nombres_apellidos`, TC.`ac_nombre_campania` FROM `tb_control_turno_malla_configuracion_recargos` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` WHERE `ctmcr_mes`=?";

            $consulta_registros_recargo = $enlace_db->prepare($consulta_string_recargo);
            $consulta_registros_recargo->bind_param("s", date('Y-m', strtotime($fecha_inicio)));
            $consulta_registros_recargo->execute();
            $resultado_registros_recargo = $consulta_registros_recargo->get_result()->fetch_all(MYSQLI_NUM);

            if (count($resultado_registros_recargo)) {
                $string_filtro_recargo="";
                for ($i=0; $i < count($resultado_registros_recargo); $i++) { 
                    $string_filtro_recargo.="`cotm_usuario`=? OR ";
                    array_push($data_consulta, $resultado_registros_recargo[$i][2]);
                }

                $string_filtro_recargo="AND (".substr($string_filtro_recargo, 0, -4).")";

                $consulta_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, `cotm_estado`, TU.`usu_nombres_apellidos`, TU.`usu_cargo_rol`, TU.`usu_estado`, TC.`ac_nombre_campania`, TR.`usu_nombres_apellidos`, TTR.`cot_inicio`, TTR.`cot_fin` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TU.`usu_supervisor`=TR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` LEFT JOIN `tb_control_turno` AS TTR ON `tb_control_turno_malla`.`cotm_id`=TTR.`cot_turno_malla` WHERE `cotm_tipo`='turno' AND `cotm_inicio`>=? AND `cotm_inicio`<=? ".$filtro_campania." ".$string_filtro_recargo." ORDER BY `cotm_usuario`, `cotm_inicio`";

                $consulta_registros = $enlace_db->prepare($consulta_string);
                if (count($data_consulta)>0) {
                    // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
                    $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
                }
                $consulta_registros->execute();
                $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

                
                for ($i=0; $i < count($resultado_registros); $i++) {
                    $array_usuarios_id[]=$resultado_registros[$i][1];
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['doc_usuario']=$resultado_registros[$i][1];
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['nombres_apellidos']=$resultado_registros[$i][11];
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['segmento']=$resultado_registros[$i][14];
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['cargo']=$resultado_registros[$i][12];
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['estado']=$resultado_registros[$i][13];
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['responsable']=$resultado_registros[$i][15];

                    $fecha_inicio_turno=$resultado_registros[$i][3];
                    $fecha_fin_turno=str_replace('24:00', '00:00', $resultado_registros[$i][4]);
                    $dia_inicio=date('Y-m-d', strtotime($fecha_inicio_turno));
                    $dia_fin=date('Y-m-d', strtotime($fecha_fin_turno));
                    $tipo_recargo=validarTipoTurno($dia_inicio);
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['festivos']+=0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_usuarios_detalle[$resultado_registros[$i][1]]['festivos']+=1;
                    }

                    $array_temp=validaRecargos($fecha_inicio_turno, $fecha_fin_turno, $array_horarios, $array_horarios_recargo);
                    
                    if ($dia_fin==$dia_inicio) {
                        $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                        $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                        $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                        $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                        if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                            $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                        }
                    } elseif ($dia_fin>$dia_inicio) {
                        $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                        $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                        $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                        $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                        if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                            $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                        }

                        $tipo_recargo=validarTipoTurno($dia_fin);
                        $array_control_recargos[$resultado_registros[$i][1]][$dia_fin][$tipo_recargo]['diurno']+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                        $array_control_recargos[$resultado_registros[$i][1]][$dia_fin][$tipo_recargo]['nocturno']+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;
                        $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                        $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;

                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;
                        if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                            $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                        }
                    }
                }
                
                $array_usuarios_id=array_values(array_unique($array_usuarios_id));
            }
        } elseif ($tipo_reporte=='Recargos-Nómina') {
            $consulta_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, `cotm_estado`, TU.`usu_nombres_apellidos`, TU.`usu_cargo_rol`, TU.`usu_estado`, TC.`ac_nombre_campania`, TR.`usu_nombres_apellidos`, TTR.`cot_inicio`, TTR.`cot_fin` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TR ON TU.`usu_supervisor`=TR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TU.`usu_campania`=TC.`ac_id` LEFT JOIN `tb_control_turno` AS TTR ON `tb_control_turno_malla`.`cotm_id`=TTR.`cot_turno_malla` WHERE `cotm_tipo`='turno' AND `cotm_inicio`>=? AND `cotm_inicio`<=? ".$filtro_campania." ORDER BY `cotm_usuario`, `cotm_inicio`";

            $consulta_registros = $enlace_db->prepare($consulta_string);
            if (count($data_consulta)>0) {
                // Agrega variables a sentencia preparada según cantidad de variables agregadas a array data_consulta en el orden específico de los parámetros de la sentencia preparada
                $consulta_registros->bind_param(str_repeat("s", count($data_consulta)), ...$data_consulta);
            }
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

            
            for ($i=0; $i < count($resultado_registros); $i++) {
                $array_usuarios_id[]=$resultado_registros[$i][1];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['doc_usuario']=$resultado_registros[$i][1];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['nombres_apellidos']=$resultado_registros[$i][11];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['segmento']=$resultado_registros[$i][14];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['cargo']=$resultado_registros[$i][12];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['estado']=$resultado_registros[$i][13];
                $array_usuarios_detalle[$resultado_registros[$i][1]]['responsable']=$resultado_registros[$i][15];

                $fecha_inicio_turno=$resultado_registros[$i][3];
                $fecha_fin_turno=str_replace('24:00', '00:00', $resultado_registros[$i][4]);
                $dia_inicio=date('Y-m-d', strtotime($fecha_inicio_turno));
                $dia_fin=date('Y-m-d', strtotime($fecha_fin_turno));
                $tipo_recargo=validarTipoTurno($dia_inicio);
                $array_usuarios_detalle[$resultado_registros[$i][1]]['festivos']+=0;
                if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                    $array_usuarios_detalle[$resultado_registros[$i][1]]['festivos']+=1;
                }

                $array_temp=validaRecargos($fecha_inicio_turno, $fecha_fin_turno, $array_horarios, $array_horarios_recargo);
                
                if ($dia_fin==$dia_inicio) {
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    }
                } elseif ($dia_fin>$dia_inicio) {
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_inicio][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['nocturno']+0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_inicio][$tipo_recargo]['diurno']+0;
                    }

                    $tipo_recargo=validarTipoTurno($dia_fin);
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_fin][$tipo_recargo]['diurno']+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                    $array_control_recargos[$resultado_registros[$i][1]][$dia_fin][$tipo_recargo]['nocturno']+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['diurno']+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                    $array_control_recargos_total_detalle[$resultado_registros[$i][1]][$tipo_recargo]['nocturno']+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;

                    $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_fin][$tipo_recargo]['nocturno']+0;
                    if ($tipo_recargo=="domingo" OR $tipo_recargo=="festivo") {
                        $array_control_recargos_total[$resultado_registros[$i][1]]+=$array_temp[$dia_fin][$tipo_recargo]['diurno']+0;
                    }
                }
            }
            
            $array_usuarios_id=array_values(array_unique($array_usuarios_id));
        }
    }

    // //llamamos la clase festivos
    // $dias_festivos = new festivos();

    // Creamos nueva instancia de PHPExcel 
    $spreadsheet = new Spreadsheet();

    // Establecer propiedades
    $spreadsheet->getProperties()
    ->setCreator("IQ-ICBF Gestión Integrada de Servicios")
    ->setLastModifiedBy($_SESSION['usu_nombre_completo'])
    ->setTitle("IQ-ICBF Gestión Integrada de Servicios")
    ->setSubject("IQ-ICBF Gestión Integrada de Servicios")
    ->setDescription("IQ-ICBF Gestión Integrada de Servicios")
    ->setKeywords("IQ-ICBF Gestión Integrada de Servicios")
    ->setCategory("Reporte");

    //Estilos para todo el libro de excel
        $spreadsheet->getDefaultStyle()->getfont()->setName("Calibri");
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        //Estilos para todo el libro de excel
        $styleArrayBordes = [
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayTitulos = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '72BF44',
                ]
            ],
        ];

        $styleArrayTitulosVerde = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '92D050',
                ]
            ],
        ];

        $styleArrayTitulosGris = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '7F7F7F',
                ]
            ],
        ];

        $styleArrayTitulosNaranja = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'ED7D31',
                ]
            ],
        ];

        $styleArrayTitulosAmarillo = [
            'font' => [
                'bold' => true,
                'size' =>'8',
                'name' =>'Arial',
                'color' => array('rgb' => 'FFFFFF')
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrap'=>true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'FFC000',
                ]
            ],
        ];
        
    //Activar hoja 0
    $sheet = $spreadsheet->getActiveSheet(0);
    
    // Nombramos la hoja 0
    $spreadsheet->getActiveSheet()->setTitle('Reporte Gestión Turnos');

    if ($tipo_reporte=='Malla') {
        //Estilos de la Hoja 0
            $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(80);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
            $spreadsheet->getActiveSheet()->getStyle('A3:M3')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->setAutoFilter('A3:M3');
            $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);

        // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A3','Doc. Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('B3','Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('C3','Segmento');
            $spreadsheet->getActiveSheet()->setCellValue('D3','Responsable');
            $spreadsheet->getActiveSheet()->setCellValue('E3','Fecha Inicio Programada');
            $spreadsheet->getActiveSheet()->setCellValue('F3','Hora Inicio Programada');
            $spreadsheet->getActiveSheet()->setCellValue('G3','Fecha Fin Programada');
            $spreadsheet->getActiveSheet()->setCellValue('H3','Hora Fin Programada');
            $spreadsheet->getActiveSheet()->setCellValue('I3','Tiempo Programado');
            $spreadsheet->getActiveSheet()->setCellValue('J3','Jornada');
            $spreadsheet->getActiveSheet()->setCellValue('K3','Observaciones');

            $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
            $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

        //Ingresar Data consultada a partir de la fila 4
        for ($i=4; $i < count($resultado_registros)+4; $i++) {
            if ($resultado_registros[$i-4][2]=='turno') {
                $turno_inicio_fecha=date('d/m/Y', strtotime($resultado_registros[$i-4][3]));
                $turno_inicio_hora=date('H:i', strtotime($resultado_registros[$i-4][3]));
                $turno_fin_fecha=date('d/m/Y', strtotime($resultado_registros[$i-4][4]));
                $turno_fin_hora=substr($resultado_registros[$i-4][4], 11, 5);
                $duracion_programada=conversorSegundosHoras(dateDiff($resultado_registros[$i-4][3], $resultado_registros[$i-4][4]));
            } else {
                $turno_inicio_fecha=date('d/m/Y', strtotime($resultado_registros[$i-4][3]));
                $turno_inicio_hora=$array_convenciones[$resultado_registros[$i-4][2]];
                $turno_fin_fecha='';
                $turno_fin_hora='';
            }
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-4][1]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-4][10]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-4][12]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$resultado_registros[$i-4][13]);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$turno_inicio_fecha);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$turno_inicio_hora);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,$turno_fin_fecha);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,$turno_fin_hora);
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i,$duracion_programada);
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i,$resultado_registros[$i-4][6]);
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i,$resultado_registros[$i-4][7]);
        }
    } elseif ($tipo_reporte=='Conexión') {
        //Estilos de la Hoja 0
            $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(80);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AD')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
            $spreadsheet->getActiveSheet()->getStyle('A3:M3')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->setAutoFilter('A3:M3');
            $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);

        // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A3','Doc. Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('B3','Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('C3','Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('D3','Fecha Inicio');
            $spreadsheet->getActiveSheet()->setCellValue('E3','Hora Inicio');
            $spreadsheet->getActiveSheet()->setCellValue('F3','Fecha Fin');
            $spreadsheet->getActiveSheet()->setCellValue('G3','Hora Fin');
            $spreadsheet->getActiveSheet()->setCellValue('H3','Tiempo Conexión');
            $spreadsheet->getActiveSheet()->setCellValue('I3','Hora Inicio Programada');
            $spreadsheet->getActiveSheet()->setCellValue('J3','Hora Fin Programada');
            $spreadsheet->getActiveSheet()->setCellValue('K3','Tiempo Programado');
            $spreadsheet->getActiveSheet()->setCellValue('L3','Tiempo Recargo');
            $spreadsheet->getActiveSheet()->setCellValue('M3','Observaciones');

            $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
            $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

        //Ingresar Data consultada a partir de la fila 4
        for ($i=4; $i < count($resultado_registros)+4; $i++) {
            if ($resultado_registros[$i-4][5]!="") {
                $fin_turno=$resultado_registros[$i-4][5];
            } else {
                $fin_turno=date('Y-m-d H:i:s');
            }
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$resultado_registros[$i-4][2]);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$resultado_registros[$i-4][21]);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$resultado_registros[$i-4][22]);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,date('d/m/Y', strtotime($resultado_registros[$i-4][4])));
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,date('H:i', strtotime($resultado_registros[$i-4][4])));
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,date('d/m/Y', strtotime($resultado_registros[$i-4][5])));
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i,substr($resultado_registros[$i-4][5], 11, 5));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i,conversorSegundosHoras(dateDiff($resultado_registros[$i-4][4], $fin_turno)));
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i,date('H:i', strtotime($resultado_registros[$i-4][14])));
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i,date('H:i', strtotime($resultado_registros[$i-4][15])));
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i,conversorSegundosHoras(dateDiff($resultado_registros[$i-4][14], $resultado_registros[$i-4][15])));
            $spreadsheet->getActiveSheet()->setCellValue('L'.$i,'');
            $spreadsheet->getActiveSheet()->setCellValue('M'.$i,$resultado_registros[$i-4][8]);
        }
    } elseif ($tipo_reporte=='Recargos-Interno') {
        //Estilos de la Hoja 0
            $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(80);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            
            $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);
            $spreadsheet->getActiveSheet()->getStyle('4')->getAlignment()->setWrapText(true);

        // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A4','Doc. Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('B4','Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('C4','Segmento');
            $spreadsheet->getActiveSheet()->setCellValue('D4','Cargo');
            $spreadsheet->getActiveSheet()->setCellValue('E4','Estado');
            $spreadsheet->getActiveSheet()->setCellValue('F4','Responsable');

            $spreadsheet->getActiveSheet()->getStyle('A4:A4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('B4:B4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('C4:C4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('D4:D4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('E4:E4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('F4:F4')->applyFromArray($styleArrayBordes);
            
            $control_columna=6;
            $fecha_inicio_control=$fecha_inicio;
            while ($fecha_inicio_control<$fecha_fin) {
                $array_malla_fechas[]=$fecha_inicio_control;
                if (validarTipoTurno($fecha_inicio_control)=='ordinario') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='ordinario';
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','35%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosGris);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','Conexión');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosGris);
                    $control_columna++;
                } elseif (validarTipoTurno($fecha_inicio_control)=='domingo') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='domingo';
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','0,75%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','210%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','Conexión');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                } elseif (validarTipoTurno($fecha_inicio_control)=='festivo') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='festivo';
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','0,75%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','210%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','Conexión');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                }

                $fecha_inicio_control = date("Y-m-d", strtotime("+ 1 day", strtotime($fecha_inicio_control)));
            }

            $spreadsheet->getActiveSheet()->getStyle('A4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->getStyle('A4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4','TOTAL');
            $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);
            $control_columna++;
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','35%');
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4','ORDINARIO');
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosGris);
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayTitulosGris);
            $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);
            $control_columna++;
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','0,75%');
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4','DOMINICAL DIURNO');
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayTitulosNaranja);
            $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);
            $control_columna++;
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','175%');
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4','DOMINICAL DIURNO');
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayTitulosNaranja);
            $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);
            $control_columna++;
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','210%');
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4','DOMINICAL/FESTIVO NOCTURNO');
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosAmarillo);
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayTitulosAmarillo);
            $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);

            $spreadsheet->getActiveSheet()->setAutoFilter('A4:'.$array_columnas[$control_columna].'4');

            $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
            $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

        //Ingresar Data consultada a partir de la fila 5
        for ($i=5; $i < count($array_usuarios_id)+5; $i++) {
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['doc_usuario']);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['nombres_apellidos']);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['segmento']);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['cargo']);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['estado']);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['responsable']);
            
            $spreadsheet->getActiveSheet()->getStyle('A'.$i.':A'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':B'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('C'.$i.':C'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('D'.$i.':D'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('E'.$i.':E'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('F'.$i.':F'.$i.'')->applyFromArray($styleArrayBordes);

            $control_columna=6;
            for ($j=0; $j < count($array_malla_fechas); $j++) { 
                $tipo_fecha=$array_malla_fechas_tipo[$array_malla_fechas[$j]]['tipo'];
                $valor_celda_diurno=$array_control_recargos[$array_usuarios_id[$i-5]][$array_malla_fechas[$j]][$tipo_fecha]['diurno'];
                $valor_celda_nocturno=$array_control_recargos[$array_usuarios_id[$i-5]][$array_malla_fechas[$j]][$tipo_fecha]['nocturno'];
                $id_turno_malla=$array_usuarios_id[$i-5].$array_malla_fechas[$j];
                if ($valor_celda_diurno>0) {
                    $valor_celda_diurno=conversorSegundosHorasMS_sn($valor_celda_diurno*60);
                } else {
                    $valor_celda_diurno='';
                }

                if ($valor_celda_nocturno>0) {
                    $valor_celda_nocturno=conversorSegundosHorasMS_sn($valor_celda_nocturno*60);
                } else {
                    $valor_celda_nocturno='';
                }

                if ($tipo_fecha=='ordinario') {
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_nocturno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayBordes);
                    $control_columna++;

                    if ($valor_celda_nocturno!='') {
                        $valor_conexion=$array_usuarios_detalle[$array_usuarios_id[$i-5]]['conexion'][$id_turno_malla];
                    } else {
                        $valor_conexion="";
                    }

                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_conexion);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayBordes);
                    $control_columna++;
                } elseif ($tipo_fecha=='domingo') {
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_diurno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_nocturno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;

                    if ($valor_celda_diurno!='' OR $valor_celda_nocturno!='') {
                        $valor_conexion=$array_usuarios_detalle[$array_usuarios_id[$i-5]]['conexion'][$id_turno_malla];
                    } else {
                        $valor_conexion="";
                    }

                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_conexion);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                } elseif ($tipo_fecha=='festivo') {
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_diurno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_nocturno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;

                    if ($valor_celda_diurno!='' OR $valor_celda_nocturno!='') {
                        $valor_conexion=$array_usuarios_detalle[$array_usuarios_id[$i-5]]['conexion'][$id_turno_malla];
                    } else {
                        $valor_conexion="";
                    }

                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_conexion);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,conversorSegundosHorasMS_sn($array_control_recargos_total[$array_usuarios_id[$i-5]]*60));
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayBordes);
            $control_columna++;
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,conversorSegundosHorasMS_sn($array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['ordinario']['nocturno']*60));
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosGris);
            
            if ($array_usuarios_detalle[$array_usuarios_id[$i-5]]['festivos']>2) {
                $control_columna++;
                $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,'00:00:00');
                $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                $control_columna++;
                $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,conversorSegundosHorasMS_sn(($array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['domingo']['diurno']+$array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['festivo']['diurno'])*60));
                $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
            } else {
                $control_columna++;
                $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,conversorSegundosHorasMS_sn(($array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['domingo']['diurno']+$array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['festivo']['diurno'])*60));
                $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                $control_columna++;
                $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,'00:00:00');
                $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
            }


            $control_columna++;
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,conversorSegundosHorasMS_sn(($array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['domingo']['nocturno']+$array_control_recargos_total_detalle[$array_usuarios_id[$i-5]]['festivo']['nocturno'])*60));
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosAmarillo);
        }
    } elseif ($tipo_reporte=='Recargos-Cliente') {
        //Estilos de la Hoja 0
            $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(80);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            
            $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);
            $spreadsheet->getActiveSheet()->getStyle('4')->getAlignment()->setWrapText(true);

        // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A4','Doc. Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('B4','Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('C4','Segmento');
            $spreadsheet->getActiveSheet()->setCellValue('D4','Cargo');
            $spreadsheet->getActiveSheet()->setCellValue('E4','Estado');
            $spreadsheet->getActiveSheet()->setCellValue('F4','Responsable');

            $spreadsheet->getActiveSheet()->getStyle('A4:A4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('B4:B4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('C4:C4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('D4:D4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('E4:E4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('F4:F4')->applyFromArray($styleArrayBordes);
            
            $control_columna=6;
            $fecha_inicio_control=$fecha_inicio;
            while ($fecha_inicio_control<$fecha_fin) {
                $array_malla_fechas[]=$fecha_inicio_control;
                if (validarTipoTurno($fecha_inicio_control)=='ordinario') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='ordinario';
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','35%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosGris);
                    $control_columna++;
                } elseif (validarTipoTurno($fecha_inicio_control)=='domingo') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='domingo';
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','0,75%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','210%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;
                } elseif (validarTipoTurno($fecha_inicio_control)=='festivo') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='festivo';
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','0,75%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'3','210%');
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4',$fecha_inicio_control);
                    $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(10);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].'3:'.$array_columnas[$control_columna].'3')->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;
                }

                $fecha_inicio_control = date("Y-m-d", strtotime("+ 1 day", strtotime($fecha_inicio_control)));
            }

            $spreadsheet->getActiveSheet()->getStyle('A4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->getStyle('A4:'.$array_columnas[$control_columna].'4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].'4','TOTAL');
            $spreadsheet->getActiveSheet()->getColumnDimension($array_columnas[$control_columna])->setWidth(20);

            $spreadsheet->getActiveSheet()->setAutoFilter('A4:'.$array_columnas[$control_columna].'4');

            $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
            $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

        //Ingresar Data consultada a partir de la fila 5
        for ($i=5; $i < count($array_usuarios_id)+5; $i++) {
            $spreadsheet->getActiveSheet()->setCellValue('A'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['doc_usuario']);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['nombres_apellidos']);
            $spreadsheet->getActiveSheet()->setCellValue('C'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['segmento']);
            $spreadsheet->getActiveSheet()->setCellValue('D'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['cargo']);
            $spreadsheet->getActiveSheet()->setCellValue('E'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['estado']);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i,$array_usuarios_detalle[$array_usuarios_id[$i-5]]['responsable']);
            
            $spreadsheet->getActiveSheet()->getStyle('A'.$i.':A'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':B'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('C'.$i.':C'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('D'.$i.':D'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('E'.$i.':E'.$i.'')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('F'.$i.':F'.$i.'')->applyFromArray($styleArrayBordes);

            $control_columna=6;
            for ($j=0; $j < count($array_malla_fechas); $j++) { 
                $tipo_fecha=$array_malla_fechas_tipo[$array_malla_fechas[$j]]['tipo'];
                $valor_celda_diurno=$array_control_recargos[$array_usuarios_id[$i-5]][$array_malla_fechas[$j]][$tipo_fecha]['diurno'];
                $valor_celda_nocturno=$array_control_recargos[$array_usuarios_id[$i-5]][$array_malla_fechas[$j]][$tipo_fecha]['nocturno'];

                if ($valor_celda_diurno>0) {
                    $valor_celda_diurno=conversorSegundosHorasMS_sn($valor_celda_diurno*60);
                } else {
                    $valor_celda_diurno='';
                }

                if ($valor_celda_nocturno>0) {
                    $valor_celda_nocturno=conversorSegundosHorasMS_sn($valor_celda_nocturno*60);
                } else {
                    $valor_celda_nocturno='';
                }

                if ($tipo_fecha=='ordinario') {
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_nocturno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayBordes);
                    $control_columna++;
                } elseif ($tipo_fecha=='domingo') {
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_diurno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_nocturno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;
                } elseif ($tipo_fecha=='festivo') {
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_diurno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosNaranja);
                    $control_columna++;
                    $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,$valor_celda_nocturno);
                    $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayTitulosAmarillo);
                    $control_columna++;
                }
            }

            $spreadsheet->getActiveSheet()->setCellValue($array_columnas[$control_columna].$i,conversorSegundosHorasMS_sn($array_control_recargos_total[$array_usuarios_id[$i-5]]*60));
            $spreadsheet->getActiveSheet()->getStyle($array_columnas[$control_columna].$i.':'.$array_columnas[$control_columna].$i)->applyFromArray($styleArrayBordes);
        }
    } elseif ($tipo_reporte=='Recargos-Nómina') {
        //Estilos de la Hoja 0
            $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(80);
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            
            $spreadsheet->getActiveSheet()->getStyle('3')->getAlignment()->setWrapText(true);
            $spreadsheet->getActiveSheet()->getStyle('4')->getAlignment()->setWrapText(true);

        // Escribiendo los titulos
            $spreadsheet->getActiveSheet()->setCellValue('A4','Doc. Usuario');
            $spreadsheet->getActiveSheet()->setCellValue('B4','Nombres y Apellidos');
            $spreadsheet->getActiveSheet()->setCellValue('C4','Descripción Recargo');
            $spreadsheet->getActiveSheet()->setCellValue('D4','Código Concepto');
            $spreadsheet->getActiveSheet()->setCellValue('E4','Cantidad de horas causadas');
            $spreadsheet->getActiveSheet()->setCellValue('F4','Fecha en que se causó');
            $spreadsheet->getActiveSheet()->setCellValue('G4','Cargo');
            $spreadsheet->getActiveSheet()->setCellValue('H4','Responsable');
            $spreadsheet->getActiveSheet()->setCellValue('I4','Estado');

            $spreadsheet->getActiveSheet()->getStyle('A4:I4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('B4:B4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('C4:C4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('D4:D4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('E4:E4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('F4:F4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('G4:G4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('H4:H4')->applyFromArray($styleArrayBordes);
            $spreadsheet->getActiveSheet()->getStyle('I4:I4')->applyFromArray($styleArrayBordes);

            $fecha_inicio_control=$fecha_inicio;
            while ($fecha_inicio_control<$fecha_fin) {
                $array_malla_fechas[]=$fecha_inicio_control;
                if (validarTipoTurno($fecha_inicio_control)=='ordinario') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='ordinario';
                } elseif (validarTipoTurno($fecha_inicio_control)=='domingo') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='domingo';
                } elseif (validarTipoTurno($fecha_inicio_control)=='festivo') {
                    $array_malla_fechas_tipo[$fecha_inicio_control]['tipo']='festivo';
                }

                $fecha_inicio_control = date("Y-m-d", strtotime("+ 1 day", strtotime($fecha_inicio_control)));
            }
            
            $spreadsheet->getActiveSheet()->getStyle('A4:I4')->applyFromArray($styleArrayTitulos);
            $spreadsheet->getActiveSheet()->setAutoFilter('A4:I4');

            $spreadsheet->getActiveSheet()->setCellValue('A1','Tipo reporte: '.$tipo_reporte);
            $spreadsheet->getActiveSheet()->setCellValue('A2','Fecha filtro: '.$fecha_inicio.' A '.$fecha_fin);

        //Ingresar Data consultada a partir de la fila 5
        $control_fila=5;
        for ($i=0; $i < count($array_usuarios_id); $i++) {
            for ($j=0; $j < count($array_malla_fechas); $j++) {
                $tipo_fecha=$array_malla_fechas_tipo[$array_malla_fechas[$j]]['tipo'];
                $recargos_dia_diurno_valor=$array_control_recargos[$array_usuarios_id[$i]][$array_malla_fechas[$j]][$tipo_fecha]['diurno'];
                $recargos_dia_diurno=$recargos_dia_diurno_valor/60;
                $recargos_dia_nocturno_valor=$array_control_recargos[$array_usuarios_id[$i]][$array_malla_fechas[$j]][$tipo_fecha]['nocturno'];
                $recargos_dia_nocturno=$recargos_dia_nocturno_valor/60;

                if ($tipo_fecha=='ordinario') {
                    // echo "Ingreso 1";
                    if ($recargos_dia_nocturno_valor>0) {
                        $descripcion_recargo="Recargo Nocturno 0,35";
                        $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['doc_usuario']);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['nombres_apellidos']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$descripcion_recargo);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,'');
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$recargos_dia_nocturno);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_malla_fechas[$j]);
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['cargo']);
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['responsable']);
                        $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['estado']);
                        
                        $spreadsheet->getActiveSheet()->getStyle('A'.$control_fila.':A'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$control_fila.':B'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$control_fila.':C'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$control_fila.':D'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$control_fila.':E'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$control_fila.':F'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$control_fila.':G'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('H'.$control_fila.':H'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('I'.$control_fila.':I'.$control_fila.'')->applyFromArray($styleArrayBordes);

                        $control_fila++;
                    }
                } elseif ($tipo_fecha=='domingo') {
                    if ($recargos_dia_diurno_valor>0) {
                        if ($array_usuarios_detalle[$array_usuarios_id[$i]]['festivos']>2) {
                            $descripcion_recargo="Recargo Dominical Diurno 175";
                        } else {
                            $descripcion_recargo="Recargo Dominical Diurno 0,75";
                        }
                        $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['doc_usuario']);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['nombres_apellidos']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$descripcion_recargo);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,'');
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$recargos_dia_diurno);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_malla_fechas[$j]);
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['cargo']);
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['responsable']);
                        $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['estado']);
                        
                        $spreadsheet->getActiveSheet()->getStyle('A'.$control_fila.':A'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$control_fila.':B'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$control_fila.':C'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$control_fila.':D'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$control_fila.':E'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$control_fila.':F'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$control_fila.':G'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('H'.$control_fila.':H'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('I'.$control_fila.':I'.$control_fila.'')->applyFromArray($styleArrayBordes);

                        $control_fila++;
                    }

                    if ($recargos_dia_nocturno_valor>0) {
                        $descripcion_recargo="Recargo Dominical/Festivo Nocturno 210";
                        $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['doc_usuario']);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['nombres_apellidos']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$descripcion_recargo);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,'');
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$recargos_dia_nocturno);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_malla_fechas[$j]);
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['cargo']);
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['responsable']);
                        $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['estado']);
                        
                        $spreadsheet->getActiveSheet()->getStyle('A'.$control_fila.':A'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$control_fila.':B'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$control_fila.':C'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$control_fila.':D'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$control_fila.':E'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$control_fila.':F'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$control_fila.':G'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('H'.$control_fila.':H'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('I'.$control_fila.':I'.$control_fila.'')->applyFromArray($styleArrayBordes);

                        $control_fila++;
                    }
                } elseif ($tipo_fecha=='festivo') {
                    if ($recargos_dia_diurno_valor>0) {
                        if ($array_usuarios_detalle[$array_usuarios_id[$i]]['festivos']>2) {
                            $descripcion_recargo="Recargo Dominical Diurno 175";
                        } else {
                            $descripcion_recargo="Recargo Dominical Diurno 0,75";
                        }
                        $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['doc_usuario']);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['nombres_apellidos']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$descripcion_recargo);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,'');
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$recargos_dia_diurno);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_malla_fechas[$j]);
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['cargo']);
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['responsable']);
                        $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['estado']);
                        
                        $spreadsheet->getActiveSheet()->getStyle('A'.$control_fila.':A'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$control_fila.':B'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$control_fila.':C'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$control_fila.':D'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$control_fila.':E'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$control_fila.':F'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$control_fila.':G'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('H'.$control_fila.':H'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('I'.$control_fila.':I'.$control_fila.'')->applyFromArray($styleArrayBordes);

                        $control_fila++;
                    }

                    if ($recargos_dia_nocturno_valor>0) {
                        $descripcion_recargo="Recargo Dominical/Festivo Nocturno 210";
                        $spreadsheet->getActiveSheet()->setCellValue('A'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['doc_usuario']);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['nombres_apellidos']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$control_fila,$descripcion_recargo);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$control_fila,'');
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$control_fila,$recargos_dia_nocturno);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$control_fila,$array_malla_fechas[$j]);
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['cargo']);
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['responsable']);
                        $spreadsheet->getActiveSheet()->setCellValue('I'.$control_fila,$array_usuarios_detalle[$array_usuarios_id[$i]]['estado']);
                        
                        $spreadsheet->getActiveSheet()->getStyle('A'.$control_fila.':A'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$control_fila.':B'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$control_fila.':C'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$control_fila.':D'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$control_fila.':E'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$control_fila.':F'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$control_fila.':G'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('H'.$control_fila.':H'.$control_fila.'')->applyFromArray($styleArrayBordes);
                        $spreadsheet->getActiveSheet()->getStyle('I'.$control_fila.':I'.$control_fila.'')->applyFromArray($styleArrayBordes);

                        $control_fila++;
                    }
                }
            }
        }
    }

    //Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$titulo_reporte.'"');
    header('Cache-Control: max-age=0');

    // Guardamos el archivo, en este caso lo guarda con el mismo nombre del php
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
?>