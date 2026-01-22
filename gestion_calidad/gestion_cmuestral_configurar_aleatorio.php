    <?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    $titulo_header = "Calculadora Muestral | Aleatorio - Generar";
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_segmento=validar_input(base64_decode($_GET['seg']));
    $fecha_calculadora=validar_input($_GET['date']);

    function generar_horario ($dia, $horario) {
        $horario_explode=explode('-', $horario);
        $hora_1=date('H:i', strtotime(trim($horario_explode[0])));
        $hora_2=date('H:i', strtotime(trim($horario_explode[1])));
        $ultima_hora = $hora_inicio = $hora_1;
        $hora_fin=$hora_2;
        
        if ($hora_1>$hora_2) {
            $hora_media=date('H:i', strtotime('23:00'));
            $hora_nuevo=date('H:i', strtotime('00:00'));

            while ($ultima_hora < $hora_media) {
                $array_horario[]=$dia.'-'.$ultima_hora;
                $ultima_hora=date('H:i', strtotime('+3 minutes', strtotime($ultima_hora)));
            }

            $array_horario[]=$dia.'-23:00';
            $dia++;
            if ($dia==8) {
                $dia=1;
            }

            while ($hora_nuevo < $hora_fin) {
                $array_horario[]=$dia.'-'.$hora_nuevo;
                $hora_nuevo=date('H:i', strtotime('+3 minutes', strtotime($hora_nuevo)));
            }
        } else {
            while ($ultima_hora < $hora_fin) {
                $array_horario[]=$dia.'-'.$ultima_hora;
                $ultima_hora=date('H:i', strtotime('+3 minutes', strtotime($ultima_hora)));
            }
        }

        return $array_horario;
    }

    function generar_horario_lider ($dia, $horario, $usuario) {
        $horario_explode=explode('-', $horario);
        $hora_1=date('H:i', strtotime(trim($horario_explode[0])));
        $hora_2=date('H:i', strtotime(trim($horario_explode[1])));
        $ultima_hora = $hora_inicio = $hora_1;
        $hora_fin=$hora_2;
        $control_fila=0;
        if ($hora_1>$hora_2) {
            $hora_media=date('H:i', strtotime('23:00'));
            $hora_nuevo=date('H:i', strtotime('00:00'));

            while ($ultima_hora < $hora_media) {
                $array_horario[$control_fila]['turno']=$dia.'-'.$ultima_hora;
                $array_horario[$control_fila]['usuario']=$usuario;
                $ultima_hora=date('H:i', strtotime('+3 minutes', strtotime($ultima_hora)));
                $control_fila++;
            }

            $array_horario[$control_fila]['turno']=$dia.'-23:00';
            $array_horario[$control_fila]['usuario']=$usuario;
            $control_fila++;
            $dia++;
            if ($dia==8) {
                $dia=1;
            }

            while ($hora_nuevo < $hora_fin) {
                $array_horario[$control_fila]['turno']=$dia.'-'.$ultima_hora;
                $array_horario[$control_fila]['usuario']=$usuario;
                $hora_nuevo=date('H:i', strtotime('+3 minutes', strtotime($hora_nuevo)));
                $control_fila++;
            }
        } else {
            while ($ultima_hora < $hora_fin) {
                $array_horario[$control_fila]['turno']=$dia.'-'.$ultima_hora;
                $array_horario[$control_fila]['usuario']=$usuario;
                $ultima_hora=date('H:i', strtotime('+3 minutes', strtotime($ultima_hora)));
                $control_fila++;
            }
        }

        return $array_horario;
    }

    function validar_dia($muestra) {
        $muestra=str_replace('1-', 'Lunes-', $muestra);
        $muestra=str_replace('2-', 'Martes-', $muestra);
        $muestra=str_replace('3-', 'Miércoles-', $muestra);
        $muestra=str_replace('4-', 'Jueves-', $muestra);
        $muestra=str_replace('5-', 'Viernes-', $muestra);
        $muestra=str_replace('6-', 'Sábado-', $muestra);
        $muestra=str_replace('7-', 'Domingo-', $muestra);
        return $muestra;
    }

    // function eliminar_lider($lider, $array_lider, $array_turno) {
    //     $array_inicial_lider=$array_lider;
        
    //     for ($i=0; $i < count($array_inicial_lider); $i++) { 
    //         if ($array_inicial_lider[$i]==$lider) {
    //             unset($array_lider[$i]);
    //             unset($array_turno[$i]);
    //         }
    //     }

        // array_values($array_lider);
        // array_values($array_turno);

    //     return $muestra;
    // }

    $consulta_string_fechas="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_total_mes`, `cmm_muestra_calculada`, `cmm_muestra_auditoria`, `cmm_numero_agentes`, `cmm_muestras_agente_mes`, `cmm_muestras_agente_semana`, `cmm_semana_dias`, `cmm_semana_peso`, `cmm_semana_porcentaje`, `cmm_semana_muestras`, TS.`cms_nombre_segmento`, `cmm_muestra_realizada`, `cmm_muestra_recalculada` FROM `tb_gestion_calidad_cmuestral_mensual` LEFT JOIN `tb_gestion_calidad_cmuestral_segmento` AS TS ON `tb_gestion_calidad_cmuestral_mensual`.`cmm_segmento`=TS.`cms_id` WHERE `cmm_calculadora`=? AND `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros_fechas = $enlace_db->prepare($consulta_string_fechas);
    $consulta_registros_fechas->bind_param("sss", $id_registro, $fecha_calculadora, $id_segmento);
    $consulta_registros_fechas->execute();
    $resultado_registros_fechas = $consulta_registros_fechas->get_result()->fetch_all(MYSQLI_NUM);

    if(isset($_POST["guardar_registro"])){
        if($_SESSION['registro_creado_random']!=1){
            $consulta_string_malla="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo` FROM `tb_gestion_calidad_cmuestral_malla` WHERE `ccmm_mes`=? AND `ccmm_segmento`=?";
            $consulta_registros_malla = $enlace_db->prepare($consulta_string_malla);
            $consulta_registros_malla->bind_param("ss", $fecha_calculadora, $id_segmento);
            $consulta_registros_malla->execute();
            $resultado_registros_malla = $consulta_registros_malla->get_result()->fetch_all(MYSQLI_NUM);

            $array_usuario_turnos_dias_cantidad[1]=0;
            $array_usuario_turnos_dias_cantidad[2]=0;
            $array_usuario_turnos_dias_cantidad[3]=0;
            $array_usuario_turnos_dias_cantidad[4]=0;
            $array_usuario_turnos_dias_cantidad[5]=0;
            $array_usuario_turnos_dias_cantidad[6]=0;
            $array_usuario_turnos_dias_cantidad[7]=0;

            for ($i=0; $i < count($resultado_registros_malla); $i++) { 
                $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]=0;
                $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array();
                if ($resultado_registros_malla[$i][6]!="" AND strpos($resultado_registros_malla[$i][6], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('1', $resultado_registros_malla[$i][6]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[1]+=1;
                }

                if ($resultado_registros_malla[$i][7]!="" AND strpos($resultado_registros_malla[$i][7], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('2', $resultado_registros_malla[$i][7]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[2]+=1;
                }

                if ($resultado_registros_malla[$i][8]!="" AND strpos($resultado_registros_malla[$i][8], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('3', $resultado_registros_malla[$i][8]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[3]+=1;
                }

                if ($resultado_registros_malla[$i][9]!="" AND strpos($resultado_registros_malla[$i][9], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('4', $resultado_registros_malla[$i][9]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[4]+=1;
                }

                if ($resultado_registros_malla[$i][10]!="" AND strpos($resultado_registros_malla[$i][10], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('5', $resultado_registros_malla[$i][10]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[5]+=1;
                }

                if ($resultado_registros_malla[$i][11]!="" AND strpos($resultado_registros_malla[$i][11], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('6', $resultado_registros_malla[$i][11]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[6]+=1;
                }

                if ($resultado_registros_malla[$i][12]!="" AND strpos($resultado_registros_malla[$i][12], '-')!="") {
                    $array_usuario_turnos[$resultado_registros_malla[$i][3]]=array_merge(generar_horario('7', $resultado_registros_malla[$i][12]), $array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                    $array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]+=1;
                    $array_usuario_turnos_dias_cantidad[7]+=1;
                }
            }

            $consulta_string_malla_lider="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo` FROM `tb_gestion_calidad_cmuestral_malla_lider` WHERE `ccmm_mes`=? AND `ccmm_segmento`=? AND ((`ccmm_lunes`<>'' AND `ccmm_lunes` LIKE '%-%') OR (`ccmm_martes`<>'' AND `ccmm_martes` LIKE '%-%') OR (`ccmm_miercoles`<>'' AND `ccmm_miercoles` LIKE '%-%') OR (`ccmm_jueves`<>'' AND `ccmm_jueves` LIKE '%-%') OR (`ccmm_viernes`<>'' AND `ccmm_viernes` LIKE '%-%') OR (`ccmm_sabado`<>'' AND `ccmm_sabado` LIKE '%-%') OR (`ccmm_domingo`<>'' AND `ccmm_domingo` LIKE '%-%'))";
            $consulta_registros_malla_lider = $enlace_db->prepare($consulta_string_malla_lider);
            $consulta_registros_malla_lider->bind_param("ss", $fecha_calculadora, $id_segmento);
            $consulta_registros_malla_lider->execute();
            $resultado_registros_malla_lider = $consulta_registros_malla_lider->get_result()->fetch_all(MYSQLI_NUM);

            $cantidad_muestra_lider_real=($resultado_registros_fechas[0][16]/$resultado_registros_fechas[0][11])/count($resultado_registros_malla_lider);
            $cantidad_muestra_lider=round(($resultado_registros_fechas[0][16]/$resultado_registros_fechas[0][11])/count($resultado_registros_malla_lider));
            if ($cantidad_muestra_lider_real>$cantidad_muestra_lider) {
                $cantidad_muestra_lider+=1;
            }
            $cantidad_muestra_lider+=2;

            $total_turnos_programado=$array_usuario_turnos_dias_cantidad[1]+$array_usuario_turnos_dias_cantidad[2]+$array_usuario_turnos_dias_cantidad[3]+$array_usuario_turnos_dias_cantidad[4]+$array_usuario_turnos_dias_cantidad[5]+$array_usuario_turnos_dias_cantidad[6]+$array_usuario_turnos_dias_cantidad[7];

            for ($i=1; $i <= 7; $i++) {
                if ($array_usuario_turnos_dias_cantidad[$i]>0) {
                    $porcentaje_dia=($array_usuario_turnos_dias_cantidad[$i]/$total_turnos_programado)*100;
                    $cantidad_muestra_limite_diario_real=($resultado_registros_fechas[0][16]*$porcentaje_dia)/100;
                    $cantidad_muestra_limite_diario=round(($resultado_registros_fechas[0][16]*$porcentaje_dia)/100);
                    $cantidad_muestra_limite_diario_array[$i]=$cantidad_muestra_limite_diario;

                    if ($cantidad_muestra_limite_diario_real>$cantidad_muestra_limite_diario) {
                        $cantidad_muestra_limite_diario_array[$i]+=1;
                    }
                    $cantidad_muestra_limite_diario_array[$i]+=10;

                }
            }

            $array_lider_turnos_id=array();
            for ($i=0; $i < count($resultado_registros_malla_lider); $i++) { 
                //Array turnos líderes turno-líder
                    if ($resultado_registros_malla_lider[$i][4]!="" AND strpos($resultado_registros_malla_lider[$i][4], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('1', $resultado_registros_malla_lider[$i][4], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }

                    if ($resultado_registros_malla_lider[$i][5]!="" AND strpos($resultado_registros_malla_lider[$i][5], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('2', $resultado_registros_malla_lider[$i][5], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }

                    if ($resultado_registros_malla_lider[$i][6]!="" AND strpos($resultado_registros_malla_lider[$i][6], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('3', $resultado_registros_malla_lider[$i][6], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }

                    if ($resultado_registros_malla_lider[$i][7]!="" AND strpos($resultado_registros_malla_lider[$i][7], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('4', $resultado_registros_malla_lider[$i][7], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }

                    if ($resultado_registros_malla_lider[$i][8]!="" AND strpos($resultado_registros_malla_lider[$i][8], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('5', $resultado_registros_malla_lider[$i][8], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }

                    if ($resultado_registros_malla_lider[$i][9]!="" AND strpos($resultado_registros_malla_lider[$i][9], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('6', $resultado_registros_malla_lider[$i][9], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }

                    if ($resultado_registros_malla_lider[$i][10]!="" AND strpos($resultado_registros_malla_lider[$i][10], '-')!="") {
                        $array_lider_turnos_id=array_merge(generar_horario_lider('7', $resultado_registros_malla_lider[$i][10], $resultado_registros_malla_lider[$i][3]), $array_lider_turnos_id);
                    }
            }
            shuffle($array_lider_turnos_id);
            $array_lideres=array_column($array_lider_turnos_id, 'usuario');
            $array_lideres_turnos=array_column($array_lider_turnos_id, 'turno');    
            
            // echo "<pre>";
            // print_r($array_lideres_turnos); 
            // echo "</pre>";

            $muestras_agente=$resultado_registros_fechas[0][9];
            for ($i=0; $i < count($resultado_registros_malla); $i++) {
                $limite_rand=count($array_usuario_turnos[$resultado_registros_malla[$i][3]])-1;
                shuffle($array_usuario_turnos[$resultado_registros_malla[$i][3]]);
                unset($array_turno_rand);
                // $random_agente[$resultado_registros_malla[$i][3]]['muestra']=array();
                // echo "<hr><br>";
                // echo "usuario: ".$resultado_registros_malla[$i][3];
                if (count($array_usuario_turnos[$resultado_registros_malla[$i][3]])>0) {
                    // echo "ingreso a turnos|";

                    $rand = range(0, $limite_rand); 
                    shuffle($rand); 
                    foreach ($rand as $val) { 
                        $array_turno_rand[]=$val; 
                    }

                    $muestras_agente_control=0;
                    for ($j=0; $j < count($array_turno_rand); $j++) {
                        $id_rand=$array_turno_rand[$j];
                        if ($muestras_agente_control<$muestras_agente) {
                            if ($array_usuario_turnos[$resultado_registros_malla[$i][3]][$id_rand]!="") {
                                $turno_registro=$array_usuario_turnos[$resultado_registros_malla[$i][3]][$id_rand];
                                $turno_registro_dia=substr($array_usuario_turnos[$resultado_registros_malla[$i][3]][$id_rand], 0, 1);
                                $key_turno_lider=array_search($turno_registro, $array_lideres_turnos);
                                // echo "Turno registro dia: ".$turno_registro_dia."|";
                                // echo "Muestras Agente: ".$muestras_agente_control."|";
                                // echo "Cantidad muestras agente: ".$array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]."|";
                                // echo "Repetido: ".!in_array($turno_registro_dia, $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra'])."|";
                                // echo "Control diario muestra: ".$random_control_dias[$turno_registro_dia]."|";
                                // echo "Cantidad diario muestra: ".$cantidad_muestra_limite_diario_array[$turno_registro_dia]."<br>";
                                // echo "<pre>";
                                // print_r($array_lideres);
                                // echo "</pre>";


                                if ($muestras_agente_control<$array_usuario_turnos_dias[$resultado_registros_malla[$i][3]]) {
                                    // echo "filtro 1<br>";
                                    // echo "Key líder: ".$key_turno_lider."|";
                                    // echo "Control cantidad líder: ".$random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]."|";
                                    // echo "Cantidad líder: ".$cantidad_muestra_lider."<br>";
                                    
                                    if (!isset($random_agente_dias[$resultado_registros_malla[$i][3]]['muestra'])) {
                                        $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra']=array();
                                    }

                                    if (!in_array($turno_registro_dia, $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra']) AND $random_control_dias[$turno_registro_dia]<=$cantidad_muestra_limite_diario_array[$turno_registro_dia] AND $key_turno_lider!='') {

                                        if ($random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]<$cantidad_muestra_lider AND $array_lideres[$key_turno_lider]!="") {
                                            $random_agente[$resultado_registros_malla[$i][3]]['muestra'][]=validar_dia($turno_registro);
                                            $random_agente[$resultado_registros_malla[$i][3]]['lider'][]=$array_lideres[$key_turno_lider];
                                            $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra'][]=$turno_registro_dia;
                                            $random_control_dias[$turno_registro_dia]+=1;
                                            $random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]+=1;
                                            $muestras_agente_control++;
                                            // echo "ingreso filtro 1 ".$muestras_agente_control;
                                            // echo "<br>";
                                            unset($array_lideres_turnos[$key_turno_lider]);
                                            unset($array_lideres[$key_turno_lider]);
                                            // array_values($array_lideres_turnos);
                                                    // array_values($array_lideres);
                                        } else {
                                            // echo "filtro 3<br>";
                                            unset($array_lideres_turnos[$key_turno_lider]);
                                            unset($array_lideres[$key_turno_lider]);
                                            // array_values($array_lideres_turnos);
                                            // array_values($array_lideres);
                                            for ($k=0; $k < 40; $k++) {
                                                $key_turno_lider=array_search($turno_registro, $array_lideres_turnos);
                                                // echo "Key líder: ".$key_turno_lider."|";
                                                // echo "Control cantidad líder: ".$random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]."|";
                                                // echo "Cantidad líder: ".$cantidad_muestra_lider."<br>";
                                                if ($random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]<$cantidad_muestra_lider AND $array_lideres[$key_turno_lider]!="") {
                                                    $random_agente[$resultado_registros_malla[$i][3]]['muestra'][]=validar_dia($turno_registro);
                                                    $random_agente[$resultado_registros_malla[$i][3]]['lider'][]=$array_lideres[$key_turno_lider];
                                                    $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra'][]=$turno_registro_dia;
                                                    $random_control_dias[$turno_registro_dia]+=1;
                                                    $random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]+=1;
                                                    $muestras_agente_control++;
                                                    // echo "ingreso filtro 3 ".$muestras_agente_control;
                                                    // echo "<br>";
                                                    unset($array_lideres_turnos[$key_turno_lider]);
                                                    unset($array_lideres[$key_turno_lider]);
                                                    // array_values($array_lideres_turnos);
                                                    // array_values($array_lideres);
                                                    // $k=0;
                                                    break;
                                                } else {
                                                    unset($array_lideres_turnos[$key_turno_lider]);
                                                    unset($array_lideres[$key_turno_lider]);
                                                    // array_values($array_lideres_turnos);
                                                    // array_values($array_lideres);
                                                    // $k=0;
                                                }
                                            }
                                        }
                                    }
                                } elseif($random_control_dias[$turno_registro_dia]<=$cantidad_muestra_limite_diario_array[$turno_registro_dia]) {
                                    // echo "filtro 2<br>";
                                    // echo "Key líder: ".$key_turno_lider."|";
                                    // echo "Control cantidad líder: ".$random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]."|";
                                    // echo "Cantidad líder: ".$cantidad_muestra_lider."<br>";
                                    if ($key_turno_lider!='' AND $random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]<$cantidad_muestra_lider AND $array_lideres[$key_turno_lider]!="") {
                                        $random_agente[$resultado_registros_malla[$i][3]]['muestra'][]=validar_dia($turno_registro);
                                        $random_agente[$resultado_registros_malla[$i][3]]['lider'][]=$array_lideres[$key_turno_lider];
                                        $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra'][]=$turno_registro_dia;
                                        $random_control_dias[$turno_registro_dia]+=1;
                                        $random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]+=1;
                                        $muestras_agente_control++;
                                        // echo "ingreso filtro 2 ".$muestras_agente_control;
                                        // echo "<br>";
                                        unset($array_lideres_turnos[$key_turno_lider]);
                                        unset($array_lideres[$key_turno_lider]);
                                        // array_values($array_lideres_turnos);
                                                    // array_values($array_lideres);
                                    } else {
                                        unset($array_lideres_turnos[$key_turno_lider]);
                                        unset($array_lideres[$key_turno_lider]);
                                        for ($k=0; $k < 20; $k++) {
                                            $key_turno_lider=array_search($turno_registro, $array_lideres_turnos);
                                            // echo "Key líder: ".$key_turno_lider."|";
                                            // echo "Control cantidad líder: ".$random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]."|";
                                            // echo "Cantidad líder: ".$cantidad_muestra_lider."<br>";
                                            if ($random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]<$cantidad_muestra_lider AND $array_lideres[$key_turno_lider]!="") {
                                                $random_agente[$resultado_registros_malla[$i][3]]['muestra'][]=validar_dia($turno_registro);
                                                $random_agente[$resultado_registros_malla[$i][3]]['lider'][]=$array_lideres[$key_turno_lider];
                                                $random_agente_dias[$resultado_registros_malla[$i][3]]['muestra'][]=$turno_registro_dia;
                                                $random_control_dias[$turno_registro_dia]+=1;
                                                $random_control_lider[$array_lideres[$key_turno_lider]][$turno_registro_dia]+=1;
                                                $muestras_agente_control++;
                                                // echo "ingreso filtro 3 ".$muestras_agente_control;
                                                // echo "<br>";
                                                unset($array_lideres_turnos[$key_turno_lider]);
                                                unset($array_lideres[$key_turno_lider]);
                                                // array_values($array_lideres_turnos);
                                                // array_values($array_lideres);
                                                // $k=0;
                                                break;
                                            } else {
                                                unset($array_lideres_turnos[$key_turno_lider]);
                                                unset($array_lideres[$key_turno_lider]);
                                                // array_values($array_lideres_turnos);
                                                // array_values($array_lideres);
                                                // $k=0;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                        // echo $muestras_agente_control."-".$muestras_agente;
                        // echo "<br>";
                    }
                } else {
                    // echo "turnos no disponibles|";
                }
            }

            // Prepara la sentencia
            $sentencia_insert_muestras = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_muestras`(`cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`) VALUES (?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert_muestras->bind_param('ssssss', $id_registro, $fecha_calculadora, $id_segmento, $cmm_usuario, $cmm_monitor, $cmm_muestra_auditoria);
            
            $control_insert=0;
            $control_registros=0;
            for ($i=0; $i < count($resultado_registros_malla); $i++) {
                $id_usuario=$resultado_registros_malla[$i][3];

                if (!isset($random_agente[$id_usuario]['muestra'])) {
                    $random_agente[$id_usuario]['muestra']=array();
                }


                if (count($random_agente[$id_usuario]['muestra'])>0) {
                    for ($j=0; $j < count($random_agente[$id_usuario]['muestra']); $j++) { 
                        $control_registros++;
                        $cmm_usuario=$id_usuario;
                        $cmm_monitor=$random_agente[$id_usuario]['lider'][$j];
                        $cmm_muestra_auditoria=$random_agente[$id_usuario]['muestra'][$j];
                        
                        if ($sentencia_insert_muestras->execute()) {
                            $control_insert++;
                        } else {
                            echo "INSERT INTO `tb_gestion_calidad_cmuestral_muestras`(`cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`) VALUES ('".$id_registro."','".$fecha_calculadora."','".$id_segmento."','".$cmm_usuario."','".$cmm_monitor."','".$cmm_muestra_auditoria."')<br>";
                        }
                    }
                }
            }

            if ($control_insert==$control_registros) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('Random generado exitosamente!', 0);</script>";
                $_SESSION['registro_creado_random']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al generar random, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Random generado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string_malla="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_martes`, `ccmm_monitoreo_miercoles`, `ccmm_monitoreo_jueves`, `ccmm_monitoreo_viernes`, `ccmm_monitoreo_sabado`, `ccmm_monitoreo_domingo`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos`, TC.`usu_nombres_apellidos`, TCAM.`ac_nombre_campania`, `ccmm_monitor`, TMONITOR.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TC ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_coordinador`=TC.`usu_id` LEFT JOIN `tb_administrador_campania` AS TCAM ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario_segmento`=TCAM.`ac_id` LEFT JOIN `tb_administrador_usuario` AS TMONITOR ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_monitor`=TMONITOR.`usu_id` WHERE `ccmm_mes`=? AND `ccmm_segmento`=? ORDER BY TU.`usu_nombres_apellidos` ASC";
    $consulta_registros_malla = $enlace_db->prepare($consulta_string_malla);
    $consulta_registros_malla->bind_param("ss", $fecha_calculadora, $id_segmento);
    $consulta_registros_malla->execute();
    $resultado_registros_malla = $consulta_registros_malla->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_malla); $i++) {
        $turno_seg_usuario[$resultado_registros_malla[$i][2]][]=$resultado_registros_malla[$i][3];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['id']=$resultado_registros_malla[$i][0];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['nombre']=$resultado_registros_malla[$i][24];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['coordinador']=$resultado_registros_malla[$i][25];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['monitor']=$resultado_registros_malla[$i][28];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['segmento']=$resultado_registros_malla[$i][26];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['muestra']=$resultado_registros_malla[$i][13].$resultado_registros_malla[$i][14].$resultado_registros_malla[$i][15].$resultado_registros_malla[$i][16].$resultado_registros_malla[$i][17].$resultado_registros_malla[$i][18].$resultado_registros_malla[$i][19];
        
        if ($array_convenciones[$resultado_registros_malla[$i][6]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['turno']=$array_convenciones[$resultado_registros_malla[$i][6]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['color']=$array_convenciones_color[$resultado_registros_malla[$i][6]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][0]['turno']=$resultado_registros_malla[$i][6];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][7]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['turno']=$array_convenciones[$resultado_registros_malla[$i][7]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['color']=$array_convenciones_color[$resultado_registros_malla[$i][7]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][1]['turno']=$resultado_registros_malla[$i][7];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][8]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['turno']=$array_convenciones[$resultado_registros_malla[$i][8]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['color']=$array_convenciones_color[$resultado_registros_malla[$i][8]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][2]['turno']=$resultado_registros_malla[$i][8];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][9]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['turno']=$array_convenciones[$resultado_registros_malla[$i][9]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['color']=$array_convenciones_color[$resultado_registros_malla[$i][9]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][3]['turno']=$resultado_registros_malla[$i][9];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][10]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['turno']=$array_convenciones[$resultado_registros_malla[$i][10]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['color']=$array_convenciones_color[$resultado_registros_malla[$i][10]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][4]['turno']=$resultado_registros_malla[$i][10];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][11]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['turno']=$array_convenciones[$resultado_registros_malla[$i][11]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['color']=$array_convenciones_color[$resultado_registros_malla[$i][11]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][5]['turno']=$resultado_registros_malla[$i][11];
        }

        if ($array_convenciones[$resultado_registros_malla[$i][12]]!="") {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['turno']=$array_convenciones[$resultado_registros_malla[$i][12]];
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['color']=$array_convenciones_color[$resultado_registros_malla[$i][12]];
        } else {
            $turno_mostrar[$resultado_registros_malla[$i][2]][$resultado_registros_malla[$i][3]][6]['turno']=$resultado_registros_malla[$i][12];
        }
    }

    $consulta_string_muestra="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_muestras` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_monitor`=TU.`usu_id` WHERE `cmm_mes`=? AND `cmm_segmento`=?";
    $consulta_registros_muestra = $enlace_db->prepare($consulta_string_muestra);
    $consulta_registros_muestra->bind_param("ss", $fecha_calculadora, $id_segmento);
    $consulta_registros_muestra->execute();
    $resultado_registros_muestra = $consulta_registros_muestra->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_muestra); $i++) { 
        $array_muestra[$resultado_registros_muestra[$i][4]][]=$resultado_registros_muestra[$i][6].' | '.$resultado_registros_muestra[$i][7];
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_cmuestral_configurar.php?reg=".base64_encode($id_registro)."&date=".$fecha_calculadora;
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
</head>
<body onresize="tabla_fixed();" onload="tabla_fixed();">
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <div class="row" id="elemento_1">
            <div class="col-md-6 py-2">
                <form name="filtrado" action="" method="POST">
                    <div class="input-group">
                        <input type="text" name="id_filtro" value='<?php echo $fecha_calculadora; ?>' class="form-control" readonly>
                        <input type="text" name="id_filtro" value='<?php echo $resultado_registros_fechas[0][14]; ?>' class="form-control" readonly>
                    </div>
                </form>
            </div>
        </div>
        <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row" id="tabla_fixed">
            <div class="col-md-12 pt-2">
                <?php if (count($turno_seg_usuario[$id_segmento])>0): ?>
                    <div id="table-fixed" class="table-responsive table-fixed">
                        <table class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Doc. Identidad</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Segmento</th>
                                    <th>Coordinador</th>
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miércoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                    <th>Sábado</th>
                                    <th>Domingo</th>
                                    <th>Muestra Aleatoria</th>
                                </tr>
                            </thead>    
                            <tbody>    
                                <?php
                                    for ($j=0; $j < count($turno_seg_usuario[$id_segmento]); $j++) {
                                        $id_usuario_item=$turno_seg_usuario[$id_segmento][$j];
                                ?>
                                <tr>
                                    <td class="align-middle"><?php echo $id_usuario_item; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['nombre']; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['segmento']; ?></td>
                                    <td class="align-middle"><?php echo $turno_mostrar_datos[$id_usuario_item]['coordinador']; ?></td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][0]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][0]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][0]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][1]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][1]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][1]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][2]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][2]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][2]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][3]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][3]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][3]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][4]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][4]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][4]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][5]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][5]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][5]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center p-0" style="background-color: <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][6]['color']; ?>; <?php echo ($turno_mostrar[$id_segmento][$id_usuario_item][6]['color']!="") ? 'color: #FFF;' : ''; ?>">
                                        <?php echo $turno_mostrar[$id_segmento][$id_usuario_item][6]['turno']; ?>
                                    </td>
                                    <td class="align-middle text-center py-0">
                                        <?php if(isset($array_muestra[$id_usuario_item])): ?>
                                            <a href="#" onClick="open_modal_detalle('<?php echo base64_encode($turno_mostrar_datos[$id_usuario_item]['id']); ?>');" class="btn btn-secondary btn-sm btn-width" title="Detalle Muestra"><span class="fas fa-eye"></span></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="alert alert-warning">
                        <span class="fas fa-exclamation-triangle"></span> No se encontraron registros
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 pt-2">
                <div class="form-group">
                    <?php if($_SESSION['registro_creado_random']==1): ?>
                        <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                    <?php else: ?>
                        <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Generar Aleatorio</button>
                        <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </form>
        <!-- Modal DETALLE -->
        <div class="modal fade" id="modal_ver_detalle" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detalle muestra aleatoria</h4>
                        <button type="button" class="close" data-dismiss="modal" onClick="close_modal_detalle();">×</button>
                    </div>
                    <div class="modal-body modal-body-detalle p-1">

                    </div>
                    <div class="modal-footer">
                        <a href="#" onClick="close_modal_detalle();" class="btn btn-corp menu float-right" data-dismiss="modal"><div class="float-left"><span class="fas fa-times"></span></div><div class="pl-2 menu_res float-left">Cerrar</div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#modal_ver_detalle").on('hidden.bs.modal', function () {
                close_modal_detalle();
            });
        });
        
        function open_modal_detalle(id_registro) {
            $('.modal-body-detalle').load('gestion_cmuestral_configurar_malla_editar_calcular_ver.php?reg='+id_registro,function(){
                $('#modal_ver_detalle').modal({show:true});
            });
        }

        function close_modal_detalle() {
            $('.modal-body-detalle').html('');
        }
        function tabla_fixed(){
            var tabla_fixed = document.getElementById("tabla_fixed");
            var elemento = document.getElementById("table-fixed");
            var elemento_1 = document.getElementById("elemento_1");
            alto_ventana=window.outerHeight-elemento_1.clientHeight-100;
            alto_tabla=tabla_fixed.clientHeight+200;

            if (alto_tabla>alto_ventana) {
                alto_elemento=alto_ventana-180;
                elemento.style.height=alto_elemento+"px";
            }
        }
    </script>
</body>
</html>