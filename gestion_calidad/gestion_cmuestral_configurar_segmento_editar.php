<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Calculadora Muestral";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Calculadora Muestral | Segmento - Editar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $id_segmento=validar_input(base64_decode($_GET['seg']));


    if(isset($_POST["guardar_registro"])){
        $nombre_segmento=validar_input($_POST['nombre_segmento']);
        $peso=validar_input($_POST['peso']);
        
        $canales=$_POST['canales'];

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_calidad_cmuestral_segmento` SET `cms_nombre_segmento`=?, `cms_peso`=? WHERE `cms_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('sss', $nombre_segmento, $peso, $id_segmento);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();

        if (comprobarSentencia($enlace_db->info)) {
            $sentencia_delete_canales = $enlace_db->prepare("DELETE FROM `tb_gestion_calidad_cmuestral_canales` WHERE `cmc_segmento`=?");
            // Agrega variables a sentencia preparada
            $sentencia_delete_canales->bind_param('s', $id_segmento);

            if ($sentencia_delete_canales->execute()) {
                // Prepara la sentencia
                $sentencia_insert_canales = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_cmuestral_canales`(`cmc_segmento`, `cmc_canal_campania`) VALUES (?, ?)");

                // Agrega variables a sentencia preparada
                $sentencia_insert_canales->bind_param('ss', $id_segmento, $opciones_valor_insert);
                $control_insert_canales=0;
                for ($i=0; $i < count($canales); $i++) { 
                    $opciones_valor_insert=$canales[$i];

                    if ($sentencia_insert_canales->execute()) {
                        $control_insert_canales++;
                    }
                }
                if ($control_insert_canales==count($canales)) {
                    $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
                } else {
                    $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
                }
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `cms_id`, `cms_calculadora`, `cms_nombre_segmento`, `cms_peso` FROM `tb_gestion_calidad_cmuestral_segmento` WHERE `cms_id`=?";
    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_segmento);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_canales="SELECT `cmc_id`, `cmc_segmento`, `cmc_canal_campania` FROM `tb_gestion_calidad_cmuestral_canales` WHERE `cmc_segmento`=? ORDER BY `cmc_canal_campania` ASC";
    $consulta_registros_canales = $enlace_db->prepare($consulta_string_canales);
    $consulta_registros_canales->bind_param("s", $id_segmento);
    $consulta_registros_canales->execute();
    $resultado_registros_canales = $consulta_registros_canales->get_result()->fetch_all(MYSQLI_NUM);

    unset($canales);

    for ($i=0; $i < count($resultado_registros_canales); $i++) {
        $canales[]=$resultado_registros_canales[$i][2];
    }

    $consulta_string_campania="SELECT `ac_id`, `ac_nombre_campania`, `ac_observaciones` FROM `tb_administrador_campania` ORDER BY `ac_nombre_campania` ASC";
    $consulta_registros_campania = $enlace_db->prepare($consulta_string_campania);
    $consulta_registros_campania->execute();
    $resultado_registros_campania = $consulta_registros_campania->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_cmuestral_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro);

?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<?php
        include("../config/configuracion_estilos.php");
    ?>
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
            <div class="col-md-6 pt-2 background-blanco">
                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                          <label for="nombre_segmento">Nombre segmento</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_segmento" id="nombre_segmento" maxlength="100" value="<?php echo $resultado_registros[0][2]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="peso">Peso</label>
                          <input type="number" class="form-control form-control-sm" name="peso" id="peso" min="0" max="100" step="0.01" value="<?php echo $resultado_registros[0][3]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12 mb-2">
                            <label for="canales" class="">Canales</label>
                        <div class="row" id="opciones_respuestas">
                            <?php for ($i=0; $i < count($canales); $i++): ?>
                                <div class="form-group col-md-12 mb-1">
                                    <select class="form-control form-control-sm float-left" name="canales[]" style="width: 90%;" required>
                                        <option value="" <?php if($canales[$i]==""){ echo "selected"; } ?>>Seleccione</option>
                                        <?php for ($l=0; $l < count($resultado_registros_campania); $l++): ?>
                                            <option value="<?php echo $resultado_registros_campania[$l][0]; ?>" <?php if($canales[$i]==$resultado_registros_campania[$l][0]){ echo "selected"; } ?>><?php echo $resultado_registros_campania[$l][1]; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <a href="#" class="color-rojo font-size-11 pt-2 pl-1 float-left" id="del_field" title="Quitar canal"><span class="fas fa-trash-alt"></span></a>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <a href="#" class="btn btn-primary font-size-11 p-0 mt-1 float-right" style="display: block; width: 120px;" id="add_field" title="Añadir tema"><span class="fas fa-plus"></span> Añadir canal</a>
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
    <script type="text/javascript">
        var campos_max = 50;

        var x = 0;
        $('#add_field').click (function(e) {
                e.preventDefault();     //prevenir novos clicks
                if (x < campos_max) {
                    $('#opciones_respuestas').append('<div class="form-group col-md-12 mb-1">\
                            <select class="form-control form-control-sm float-left" name="canales[]" style="width: 90%;" required>\
                                <option value="">Seleccione</option>\
                                <?php for ($l=0; $l < count($resultado_registros_campania); $l++): ?>
                                    <option value="<?php echo $resultado_registros_campania[$l][0]; ?>"><?php echo $resultado_registros_campania[$l][1]; ?></option>\
                                <?php endfor; ?>
                            </select>\
                            <a href="#" class="color-rojo font-size-11 pt-2 pl-1 float-left" id="del_field" title="Quitar canal"><span class="fas fa-trash-alt"></span></a>\
                        </div>');
                    x++;
                }
        });
        // Remover o div anterior
        $('#opciones_respuestas').on("click","#del_field",function(e) {
                e.preventDefault();
                $(this).parent('div').remove();
                x--;
        });
    </script>
</body>
</html>