<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $id_registro=validar_input(base64_decode($_GET['reg']));

    $consulta_string_malla="SELECT `ccmm_id`, `ccmm_mes`, `ccmm_segmento`, `ccmm_usuario`, `ccmm_usuario_segmento`, `ccmm_coordinador`, `ccmm_lunes`, `ccmm_martes`, `ccmm_miercoles`, `ccmm_jueves`, `ccmm_viernes`, `ccmm_sabado`, `ccmm_domingo`, `ccmm_monitoreo_lunes`, `ccmm_monitoreo_martes`, `ccmm_monitoreo_miercoles`, `ccmm_monitoreo_jueves`, `ccmm_monitoreo_viernes`, `ccmm_monitoreo_sabado`, `ccmm_monitoreo_domingo`, `ccmm_actualiza_usuario`, `ccmm_actualiza_fecha`, `ccmm_registro_usuario`, `ccmm_registro_fecha`, TU.`usu_nombres_apellidos`, TCAM.`ac_nombre_campania`, `ccmm_monitor` FROM `tb_gestion_calidad_cmuestral_malla` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario`=TU.`usu_id` LEFT JOIN `tb_administrador_campania` AS TCAM ON `tb_gestion_calidad_cmuestral_malla`.`ccmm_usuario_segmento`=TCAM.`ac_id` WHERE `ccmm_id`=? ORDER BY TU.`usu_nombres_apellidos` ASC";
    $consulta_registros_malla = $enlace_db->prepare($consulta_string_malla);
    $consulta_registros_malla->bind_param("s", $id_registro);
    $consulta_registros_malla->execute();
    $resultado_registros_malla = $consulta_registros_malla->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_malla); $i++) {
        $turno_seg_usuario[$resultado_registros_malla[$i][2]][]=$resultado_registros_malla[$i][3];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['id']=$resultado_registros_malla[$i][0];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['nombre']=$resultado_registros_malla[$i][24];
        $turno_mostrar_datos[$resultado_registros_malla[$i][3]]['muestra']=$resultado_registros_malla[$i][13].$resultado_registros_malla[$i][14].$resultado_registros_malla[$i][15].$resultado_registros_malla[$i][16].$resultado_registros_malla[$i][17].$resultado_registros_malla[$i][18].$resultado_registros_malla[$i][19];
    }

    $consulta_string_muestra="SELECT `cmm_id`, `cmm_calculadora`, `cmm_mes`, `cmm_segmento`, `cmm_usuario`, `cmm_monitor`, `cmm_muestra_auditoria`, TU.`usu_nombres_apellidos` FROM `tb_gestion_calidad_cmuestral_muestras` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_calidad_cmuestral_muestras`.`cmm_monitor`=TU.`usu_id` WHERE `cmm_mes`=? AND  `cmm_segmento`=? AND `cmm_usuario`=?";
    $consulta_registros_muestra = $enlace_db->prepare($consulta_string_muestra);
    $consulta_registros_muestra->bind_param("sss", $resultado_registros_malla[0][1], $resultado_registros_malla[0][2], $resultado_registros_malla[0][3]);
    $consulta_registros_muestra->execute();
    $resultado_registros_muestra = $consulta_registros_muestra->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_muestra); $i++) { 
        $array_muestra[]=$resultado_registros_muestra[$i][6].' | '.$resultado_registros_muestra[$i][7];
    }
?>
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th class="align-middle text-left">Fecha</th>
                        <td class="align-middle"><?php echo $resultado_registros_malla[0][1]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Id Usuario</th>
                        <td class="align-middle"><?php echo $resultado_registros_malla[0][3]; ?></td>
                    </tr>
                    <tr>
                        <th class="align-middle text-left">Nombres y Apellidos</th>
                        <td class="align-middle"><?php echo $resultado_registros_malla[0][24]; ?></td>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="col-md-12">
        <div class="fondo-corporativo color-blanco p-1 font-size-11"><span class="fas fa-random"></span> Muestra Aleatoria</div>
        <div class="alert alert-warning p-1 font-size-11">
            <?php if(isset($array_muestra)): ?>
                <span class="fas fa-check-circle"></span> <?php echo implode('<br><span class="fas fa-check-circle"></span> ', $array_muestra); ?>
            <?php endif; ?>
            
        </div>
    </div>
</div>