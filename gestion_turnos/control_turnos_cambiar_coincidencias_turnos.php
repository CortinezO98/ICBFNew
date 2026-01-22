<?php 
    //Se realiza la consulta por id y se imprimen los resultados
	$modulo_plataforma="Gestión Turnos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
    $usuario=validar_input($_GET['usuario']);
	$turno_origen=validar_input($_GET['turno_origen']);
    $turno_origen=substr($turno_origen, -10);
	$fecha_minimo=date("Y-m-d", strtotime("+1 day"));
    $fecha_minimo=$fecha_minimo.' 00:00:00';
	if ($usuario!='') {
        $consulta_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha` FROM `tb_control_turno_malla` WHERE `cotm_usuario`=? AND `cotm_inicio`>=? AND (`cotm_tipo`='turno' OR `cotm_tipo`='DESC') AND `cotm_inicio` LIKE '%".$turno_origen."%' AND `cotm_estado`='' ORDER BY `cotm_usuario`, `cotm_inicio`";
        $consulta_registros = $enlace_db->prepare($consulta_string);
        $consulta_registros->bind_param("ss", $usuario, $fecha_minimo);
        $consulta_registros->execute();
        $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

        $resultado_data='<option value="" class="font-size-11">Seleccione</option>';

        if (count($resultado_registros)>0) {
            $resultado_control=1;
            for ($i=0; $i < count($resultado_registros); $i++) {
                if ($resultado_registros[$i][2]=='turno') {
                    $resultado_data.='<option value="'.$resultado_registros[$i][0].'" class="font-size-11">'.$resultado_registros[$i][6].' | '.$resultado_registros[$i][3].' A '.$resultado_registros[$i][4].'</option>';
                } else {
                    $resultado_data.='<option value="'.$resultado_registros[$i][0].'" class="font-size-11">'.$array_convenciones[$resultado_registros[$i][2]].' | '.date('Y-m-d', strtotime($resultado_registros[$i][3])).'</option>';
                }
            }
        } else {
            $resultado_control=0;
            $resultado_data='<p class="alert alert-warning p-1 font-size-11">¡No se encontraron turnos disponibles para cambio!</p>';
        }
    }

    $data = array(
        "resultado" => $resultado_data,
        "resultado_control" => $resultado_control
    );

    echo json_encode($data);
?>
