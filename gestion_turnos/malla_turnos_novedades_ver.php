<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos-Malla";

    require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $id_registro=validar_input(base64_decode($_GET['reg']));

    $consulta_string="SELECT `ctmn_id`, `ctm_usuario`, TUT.`usu_nombres_apellidos`, `ctmn_tipo`, `ctmn_fecha_inicio`, `ctmn_fecha_fin`, `ctmn_registro_usuario`, TUR.`usu_nombres_apellidos`, `ctmn_registro_fecha`, `ctmn_observaciones`, TC.`ac_nombre_campania`, `ctmn_sop_ruta` FROM `tb_control_turno_malla_novedades` LEFT JOIN `tb_administrador_usuario` AS TUT ON `tb_control_turno_malla_novedades`.`ctm_usuario`=TUT.`usu_id` LEFT JOIN `tb_administrador_usuario` AS TUR ON `tb_control_turno_malla_novedades`.`ctmn_registro_usuario`=TUR.`usu_id` LEFT JOIN `tb_administrador_campania` AS TC ON TUT.`usu_campania`=TC.`ac_id` WHERE `ctmn_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
?>
<div class="row">
    <div class="col-md-12">
        <embed src="<?php echo $resultado_registros[0][11]; ?>?ran=<?php echo generar_codigo(5); ?>#zoom=100" id="visor" style="width: 100%; min-height: 430px;">
    </div>
</div>