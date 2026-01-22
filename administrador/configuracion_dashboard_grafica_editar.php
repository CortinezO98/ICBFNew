<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Administrador-Dashboard";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Dashboard Configuración | Gráfica-Editar";
    $id_dashboard=validar_input(base64_decode($_GET['reg']));
    $id_grafica=validar_input(base64_decode($_GET['gra']));

    if(isset($_POST["guardar_registro"])){
        $tipo_grafica=validar_input($_POST['tipo_grafica']);
        $nombre_grafica=validar_input($_POST['nombre_grafica']);
        $variable=validar_input($_POST['variable']);
        $orden=validar_input($_POST['orden']);
        $ancho=validar_input($_POST['ancho']);
        
        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_dashboard_grupos_graficas` SET `gdgg_tipo_grafica`=?,`gdgg_nombre`=?,`gdgg_variable`=?, `gdgg_orden`=?, `gdgg_ancho`=? WHERE `gdgg_id`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('ssssss', $tipo_grafica, $nombre_grafica, $variable, $orden, $ancho, $id_grafica);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro actualizado exitosamente!', 0);</script>";
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al actualizar el registro, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gdgg_id`, `gdgg_grupo`, `gdgg_tipo_grafica`, `gdgg_nombre`, `gdgg_variable`, `gdgg_orden`, `gdgg_ancho` FROM `tb_gestion_dashboard_grupos_graficas` WHERE `gdgg_id`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_grafica);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_dashboard = $enlace_db->prepare("SELECT `gd_id`, `gd_nombre`, `gd_matriz`, `gd_campania`, `gd_orden`, `gd_estado`, `gd_registro_usuario`, `gd_registro_fecha`, TM.`gem_nombre_encuesta`, TU.`usu_nombres_apellidos` FROM `tb_gestion_dashboard` LEFT JOIN `tb_gestion_encuestas_matriz` AS TM ON `tb_gestion_dashboard`.`gd_matriz`=TM.`gem_id` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_dashboard`.`gd_registro_usuario`=TU.`usu_id` WHERE `gd_id`=?");
    $consulta_dashboard->bind_param("s", $id_dashboard);
    $consulta_dashboard->execute();
    $resultado_dashboard = $consulta_dashboard->get_result()->fetch_all(MYSQLI_NUM);

    $consulta_string_preguntas="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE (`gemp_tipo`='Casillas' OR `gemp_tipo`='Varias Opciones' OR `gemp_tipo`='Desplegable') AND `gemp_encuesta`=? ORDER BY `gemp_pregunta_nombre` ASC";
    $consulta_registros_preguntas = $enlace_db->prepare($consulta_string_preguntas);
    $consulta_registros_preguntas->bind_param("s", $resultado_dashboard[0][2]);
    $consulta_registros_preguntas->execute();
    $resultado_registros_preguntas = $consulta_registros_preguntas->get_result()->fetch_all(MYSQLI_NUM);

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="../dashboard_configuracion.php?reg=".base64_encode($id_dashboard);

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
                    <div class="col-md-2">
                        <div class="form-group">
                          <label for="orden">Orden</label>
                          <input type="number" step="0.1" max="999" class="form-control form-control-sm" name="orden" id="orden" maxlength="100" value="<?php echo $resultado_registros[0][5]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ancho">Ancho</label>
                            <select class="form-control form-control-sm" name="ancho" id="ancho" required>
                              <option value="">Seleccione</option>
                              <option value="3" <?php if($resultado_registros[0][6]=="3"){ echo "selected"; } ?>>3 Unidades</option>
                              <option value="6" <?php if($resultado_registros[0][6]=="6"){ echo "selected"; } ?>>6 Unidades</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="tipo_grafica">Tipo</label>
                            <select class="form-control form-control-sm" name="tipo_grafica" id="tipo_grafica" required>
                              <option value="">Seleccione</option>
                              <option value="barra" <?php if($resultado_registros[0][2]=="barra"){ echo "selected"; } ?>>Barras</option>
                              <option value="torta" <?php if($resultado_registros[0][2]=="torta"){ echo "selected"; } ?>>Torta</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="nombre_grafica">Nombre gráfica</label>
                          <input type="text" class="form-control form-control-sm" name="nombre_grafica" id="nombre_grafica" maxlength="100" value="<?php echo $resultado_registros[0][3]; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="variable">Variable</label>
                            <select class="form-control form-control-sm" name="variable" id="variable" required>
                              <option value="">Seleccione</option>
                              <optgroup label="Variables predefinidas">
                                      <option value="variable_edad" <?php if($resultado_registros[0][4]=="variable_edad"){ echo "selected"; } ?>>Edad</option>
                                      <option value="variable_genero" <?php if($resultado_registros[0][4]=="variable_genero"){ echo "selected"; } ?>>Género</option>
                                      <option value="variable_motivo_atencion" <?php if($resultado_registros[0][4]=="variable_motivo_atencion"){ echo "selected"; } ?>>Motivo atención</option>
                                      <option value="variable_mapa_radicados" <?php if($resultado_registros[0][4]=="variable_mapa_radicados"){ echo "selected"; } ?>>Radicados por regional (Mapa-Cantidad)</option>
                                      <option value="variable_mapa_efectivas_cantidad" <?php if($resultado_registros[0][4]=="variable_mapa_efectivas_cantidad"){ echo "selected"; } ?>>Efectivas por regional (Mapa-Cantidad)</option>
                                      <option value="variable_mapa_efectivas_porcentaje" <?php if($resultado_registros[0][4]=="variable_mapa_efectivas_porcentaje"){ echo "selected"; } ?>>Efectivas por regional (Mapa-Porcentaje)</option>
                                      <option value="variable_indicador_general" <?php if($resultado_registros[0][4]=="variable_indicador_general"){ echo "selected"; } ?>>Indicador general</option>
                                      <option value="variable_indicador_agente" <?php if($resultado_registros[0][4]=="variable_indicador_agente"){ echo "selected"; } ?>>Indicador general por agente</option>
                                      <option value="variable_gestionadas_agente" <?php if($resultado_registros[0][4]=="variable_gestionadas_agente"){ echo "selected"; } ?>>Gestionadas por agente</option>
                                      <option value="variable_contactadas_agente" <?php if($resultado_registros[0][4]=="variable_contactadas_agente"){ echo "selected"; } ?>>Contactadas por agente</option>
                                      <option value="variable_efectivas_agente" <?php if($resultado_registros[0][4]=="variable_efectivas_agente"){ echo "selected"; } ?>>Efectivas por agente</option>
                                      <option value="variable_alertas" <?php if($resultado_registros[0][4]=="variable_alertas"){ echo "selected"; } ?>>Alertas generadas</option>
                                    </optgroup>
                                    <optgroup label="Variables encuesta">
                                    <?php for ($i=0; $i < count($resultado_registros_preguntas); $i++): ?>
                                        <option value="<?php echo $resultado_registros_preguntas[$i][0]; ?>" <?php if($resultado_registros[0][4]==$resultado_registros_preguntas[$i][0]){ echo "selected"; } ?>><?php echo $resultado_registros_preguntas[$i][3]; ?></option>
                                    <?php endfor; ?>
                                    </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                            <?php if(!isset($_POST["guardar_registro"]) AND !isset($_POST["reset_contrasena"])): ?>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php endif; ?>
                            <?php if(isset($_POST["guardar_registro"]) OR isset($_POST["reset_contrasena"])): ?>
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
</body>
</html>