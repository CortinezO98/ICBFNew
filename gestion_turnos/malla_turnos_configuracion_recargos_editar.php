<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Gestión Turnos-Malla";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Gestión Turnos | Configuración Recargos | Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $FechaInicio = base64_decode($_GET['fechainicio']);
    if(isset($_POST["guardar_registro"])){
        $usuarios=$_POST['usuarios'];
        if (!isset($usuarios)) {
            $usuarios=array();
        }

        $sentencia_delete_usuarios = $enlace_db->prepare("DELETE FROM `tb_control_turno_malla_configuracion_recargos` WHERE `ctmcr_mes`=?");
        // Agrega variables a sentencia preparada
        $sentencia_delete_usuarios->bind_param('s', $FechaInicio);
        $sentencia_delete_usuarios->execute();

        // Prepara la sentencia
        $sentencia_insert_usuarios = $enlace_db->prepare("INSERT INTO `tb_control_turno_malla_configuracion_recargos`(`ctmcr_id`, `ctmcr_mes`, `ctmcr_usuario`, `ctmcr_registro_usuario`) VALUES (?,?,?,?)");

        // Agrega variables a sentencia preparada
        $sentencia_insert_usuarios->bind_param('ssss', $id_registro_usuario, $FechaInicio, $usuario_valor_insert, $_SESSION['usu_id']);
        $control_insert_usuarios=0;
        if (isset($usuarios)) {
            $total_usuarios=count($usuarios);
            for ($i=0; $i < count($usuarios); $i++) { 
                $usuario_valor_insert=$usuarios[$i];
                $id_registro_usuario=$usuario_valor_insert.$FechaInicio;
                if ($sentencia_insert_usuarios->execute()) {
                    $control_insert_usuarios++;
                }
            }
        } else {
            $total_usuarios=0;
        }

        if ($total_usuarios==$control_insert_usuarios) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string_usuarios_actual="SELECT `ctmcr_id`, `ctmcr_mes`, `ctmcr_usuario`, `ctmcr_registro_usuario`, `ctmcr_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_control_turno_malla_configuracion_recargos` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_control_turno_malla_configuracion_recargos`.`ctmcr_usuario`=TU.`usu_id` WHERE `ctmcr_mes`=? ORDER BY TU.`usu_nombres_apellidos` ASC";
    $consulta_registros_usuarios_actual = $enlace_db->prepare($consulta_string_usuarios_actual);
    $consulta_registros_usuarios_actual->bind_param("s", $FechaInicio);
    $consulta_registros_usuarios_actual->execute();
    $resultado_registros_usuarios_actual = $consulta_registros_usuarios_actual->get_result()->fetch_all(MYSQLI_NUM);
    unset($usuarios);
    $usuarios=array();
    for ($i=0; $i < count($resultado_registros_usuarios_actual); $i++) { 
        $usuarios[]=$resultado_registros_usuarios_actual[$i][2];
    }

    $consulta_string_usuarios="SELECT `usu_id`, `usu_nombres_apellidos`, `usu_estado`, TC.`ac_nombre_campania` FROM `tb_administrador_usuario` LEFT JOIN `tb_administrador_campania` AS TC ON `tb_administrador_usuario`.`usu_campania`=TC.`ac_id` WHERE `usu_estado`<>'Retirado' ORDER BY `usu_nombres_apellidos` ASC";
    $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
    $consulta_registros_usuarios->execute();
    $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="malla_turnos_configuracion_recargos.php?pagina=".$pagina."&id=".$filtro_permanente."&fechainicio=".base64_encode($FechaInicio);
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
    <link rel="stylesheet" href="../js/bootstrap-select/dist/css/bootstrap-select.min.css">
</head>
<body>
    <?php
        include("../menu_principal.php");
        include("../menu_header.php");
    ?>
    <div class="contenido">
        <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
        <div class="row justify-content-center">
            <div class="col-md-5 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-12 mt-1" id="div_usuarios">
                        <div class="form-group my-1">
                            <label for="usuarios" class="font-weight-bold font-size-11 m-0">Usuarios recargos al cliente</label>
                            <div class="row" id="opciones_respuestas_usuarios">
                                <?php if(isset($usuarios)): ?>
                                    <?php for ($i=0; $i < count($usuarios); $i++): ?>
                                        <div class="row lista_usuarios px-4 col-md-12">
                                            <div class="form-group col-11 mb-1 px-2">
                                                <select class="selectpicker form-control form-control-sm font-size-11" data-live-search="true" name="usuarios[]" id="usuarios" required>
                                                    <option value="">Seleccione</option>
                                                    <?php for ($j=0; $j < count($resultado_registros_usuarios); $j++): ?>
                                                        <option value='<?php echo $resultado_registros_usuarios[$j][0]; ?>' class="font-size-11" data-tokens="<?php echo $resultado_registros_usuarios[$i][0].' '.$resultado_registros_usuarios[$i][1]; ?>" <?php if($usuarios[$i]==$resultado_registros_usuarios[$j][0]){ echo "selected"; } ?>><?php echo $resultado_registros_usuarios[$j][1]; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-1 mb-1 ps-1">
                                                <a href="#" class="color-rojo font-size-11 pt-2 ps-0" id="del_field_usuarios" title="Quitar usuario"><span class="fas fa-trash-alt"></span></a>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </div>
                            <a href="#" class="btn btn-primary font-size-11 p-0 mt-1" style="display: block; width: 185px;" id="add_field_usuarios" title="Añadir usuario"><span class="fas fa-plus"></span> Añadir usuario</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"])): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script src="../js/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script type="text/javascript">
        var campos_max = 50;

        var x = 0;
        $('#add_field_usuarios').click (function(e) {
            e.preventDefault();
            if (x < campos_max) {
                $('#opciones_respuestas_usuarios').append('<div class="row lista_usuarios px-4 col-md-12">\
                    <div class="form-group col-11 mb-1 px-2">\
                        <select class="selectpicker d-block form-control form-control-sm font-size-11" data-live-search="true" name="usuarios[]" id="usuarios_'+x+'" required>\
                            <option value="">Seleccione</option>\
                            <?php for ($i=0; $i < count($resultado_registros_usuarios); $i++): ?>
                                <option value="<?php echo $resultado_registros_usuarios[$i][0]; ?>" class="font-size-11" data-tokens="<?php echo $resultado_registros_usuarios[$i][0]." ".$resultado_registros_usuarios[$i][1]; ?>"><?php echo $resultado_registros_usuarios[$i][1]; ?></option>\
                            <?php endfor; ?>
                        </select>\
                    </div>\
                    <div class="col-1 mb-1 ps-1">\
                        <a href="#" class="color-rojo font-size-11 pt-2 ps-0" id="del_field_usuarios" title="Quitar usuario"><span class="fas fa-trash-alt"></span></a>\
                    </div>\
                </div>');
                $('#usuarios_'+x).selectpicker('refresh');
                x++;
            }
        });

        $('#opciones_respuestas_usuarios').on("click","#del_field_usuarios",function(e) {
            e.preventDefault();
            $(this).parents('div.lista_usuarios').remove();
            x--;
        });
    </script>
</body>
</html>