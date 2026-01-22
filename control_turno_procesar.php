<?php
    require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    require_once("config/conexion_db.php");
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
    $tipo=base64_decode($_GET['tipo']);
    $accion=base64_decode($_GET['accion']);
    if ($tipo!="" AND $accion!="") {
        if ($accion=="inicio") {
            if ($tipo=='turno') {
                // $consulta_inicio_turnos_programados = mysqli_query($enlace_db, "SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_control_turno_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla`.`cotm_usuario`=TU.`usu_id` WHERE `cotm_usuario`='".$_SESSION['usu_id']."' AND `cotm_inicio` LIKE '".date('Y-m-d')."%' ORDER BY `cotm_usuario`, `cotm_inicio`");
                // $resultado_inicio_turnos_programados = mysqli_fetch_all($consulta_inicio_turnos_programados);

                //validacion de que no existe un turno iniciado
                $consulta_string_duplicado="SELECT COUNT(`cot_usuario`) FROM `tb_control_turno` WHERE `cot_usuario`=? AND `cot_tipo`='turno' AND `cot_inicio` LIKE '".date('Y-m-d')."%' AND `cot_fin`=''";
                $consulta_registros_duplicado = $enlace_db->prepare($consulta_string_duplicado);
                $consulta_registros_duplicado->bind_param('s', $_SESSION['usu_id']);
                $consulta_registros_duplicado->execute();
                $resultado_registros_duplicado = $consulta_registros_duplicado->get_result()->fetch_all(MYSQLI_NUM);

                $control_duplicado=$resultado_registros_duplicado[0][0];
            } else {
                $control_duplicado=0;
            }

            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_control_turno`(`cot_turno_malla`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion`, `cot_fuente`, `cot_observaciones_inicio`, `cot_observaciones_fin`) VALUES (?,?,?,?,'','',?,'','')");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sssss', $_SESSION['session_turnop_id'], $_SESSION['usu_id'], $tipo, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
            if ($control_duplicado==0) {
                if ($sentencia_insert->execute()) {
                    echo "<meta http-equiv='refresh' content='0; url=contenido.php'>";
                } else {
                    echo $message_error="Se ha presentado un error al iniciar el turno, por favor intente nuevamente!";
                }
            } else {
                echo "<meta http-equiv='refresh' content='0; url=contenido.php'>";
            }
        } elseif ($accion=="cierre") {
            $fecha_actual = date("Y-m-d H:i:s");
            $consulta_string="SELECT `cot_id`, `cot_usuario`, `cot_tipo`, `cot_inicio`, `cot_fin`, `cot_duracion`, `cot_fuente`, `cot_observaciones_inicio`, `cot_observaciones_fin`, `cot_registro_fecha` FROM `tb_control_turno` WHERE `cot_usuario`=? AND `cot_tipo`=? AND `cot_turno_malla`=? AND `cot_fin`=''";
            $consulta_registros = $enlace_db->prepare($consulta_string);
            $consulta_registros->bind_param("sss", $_SESSION['usu_id'], $tipo, $_SESSION['session_turnop_id']);
            $consulta_registros->execute();
            $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

            $duracion = dateDiff($resultado_registros[0][3],$fecha_actual);

            // Prepara la sentencia
            $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_control_turno` SET `cot_fin`=?, `cot_duracion`=? WHERE `cot_id`=?");

            // Agrega variables a sentencia preparada
            $consulta_actualizar->bind_param('sss', $fecha_actual, $duracion, $resultado_registros[0][0]);

            // Ejecuta sentencia preparada
            $consulta_actualizar->execute();

            if (comprobarSentencia($enlace_db->info)) {
                echo "<meta http-equiv='refresh' content='0; url=contenido.php'>";
            } else {
                echo $message_error="Se ha presentado un error al iniciar el turno, por favor intente nuevamente!";
            }
        }
    } else {
        echo $message_error="Se ha presentado un error al iniciar el turno, por favor intente nuevamente!";
    }
?>