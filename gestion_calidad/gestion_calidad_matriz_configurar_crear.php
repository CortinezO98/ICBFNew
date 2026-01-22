<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Calidad-Matriz Calidad";

    require_once("../config/validaciones_seguridad.php");
	require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz de Calidad | Configurar Crear";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    $tipo_item=validar_input(base64_decode($_GET['tipo']));
    $tipo_error_lista=validar_input(base64_decode($_GET['tierr']));

    $idg_grupo=validar_input(base64_decode($_GET['idg_grupo']));
    $idg_subgrupo=validar_input(base64_decode($_GET['idg_subgrupo']));
    $idg_item=validar_input(base64_decode($_GET['idg_item']));

    if(isset($_POST["guardar_registro"])){
        $tipo=validar_input($_POST['tipo']);
        $consecutivo=validar_input($_POST['consecutivo']);
        $codigo_registro=generar_codigo(10);
        if($tipo=="Grupo"){
            $orden=1;
            $nota_maxima=validar_input($_POST['nota_maxima']);
            $peso=$nota_maxima;
            $calificable="No";
            $grupo_peso="";
            $visible="Si";
            $tipo_error=validar_input($_POST['tipo_error']);
            $nota_sla=validar_input($_POST['nota_sla']);
            
            if ($tipo_error=="ENC") {
                $nombre="ERROR NO CRÍTICO";
            } elseif ($tipo_error=="ECU") {
                $nombre="ERROR CRÍTICO DE USUARIO";
            } elseif ($tipo_error=="ECN") {
                $nombre="ERROR CRÍTICO DE NEGOCIO";
            }

            $idr_grupo=$codigo_registro;
            $idr_subgrupo="";
            $idr_item="";
            $idr_subitem="";

        } elseif($tipo=="Sub-Grupo"){
            $nombre=validar_input($_POST['nombre']);
            $orden=2;
            $peso=validar_input($_POST['peso']);
            $calificable=validar_input($_POST['calificable']);
            $grupo_peso=validar_input($_POST['grupo_peso']);
            $visible=validar_input($_POST['visible']);
            $tipo_error=$tipo_error_lista;
            $nota_maxima='';
            $nota_sla='';

            $idr_grupo=$idg_grupo;
            $idr_subgrupo=$codigo_registro;
            $idr_item="";
            $idr_subitem="";
        } elseif($tipo=="Item"){
            $nombre=validar_input($_POST['nombre']);
            $orden=3;
            $peso=validar_input($_POST['peso']);
            $calificable=validar_input($_POST['calificable']);
            $grupo_peso=validar_input($_POST['grupo_peso']);
            $visible=validar_input($_POST['visible']);
            $tipo_error=$tipo_error_lista;
            $nota_maxima='';
            $nota_sla='';

            $idr_grupo=$idg_grupo;
            $idr_subgrupo=$idg_subgrupo;
            $idr_item=$codigo_registro;
            $idr_subitem="";
        }  elseif($tipo=="Sub-Item"){
            $nombre=validar_input($_POST['nombre']);
            $orden=4;
            $peso=validar_input($_POST['peso']);
            $calificable=validar_input($_POST['calificable']);
            $grupo_peso=validar_input($_POST['grupo_peso']);
            $visible=validar_input($_POST['visible']);
            $tipo_error=$tipo_error_lista;
            $nota_maxima='';
            $nota_sla='';

            $idr_grupo=$idg_grupo;
            $idr_subgrupo=$idg_subgrupo;
            $idr_item=$idg_item;
            $idr_subitem=$codigo_registro;
        }

        if($_SESSION['gcmc_registro_creado']!=1){
            // Prepara la sentencia
            $sentencia_insert = $enlace_db->prepare("INSERT INTO `tb_gestion_calidad_matriz_item`(`gcmi_id`,`gcmi_matriz`, `gcmi_grupo_id`, `gcmi_subgrupo_id`, `gcmi_item_id`, `gcmi_subitem_id`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error`, `gcmi_nota_maxima`, `gcmi_nota_sla`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            // Agrega variables a sentencia preparada
            $sentencia_insert->bind_param('sssssssssssssssss', $codigo_registro, $id_registro, $idr_grupo, $idr_subgrupo, $idr_item, $idr_subitem, $tipo, $consecutivo, $orden, $nombre, $peso, $calificable, $grupo_peso, $visible, $tipo_error, $nota_maxima, $nota_sla);
            
            if ($sentencia_insert->execute()) {
                $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente!', 0);</script>";
                $_SESSION['gcmc_registro_creado']=1;
            } else {
                $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al crear el registro, por favor verifique e intente nuevamente!', 0);</script>";
            }
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.success('¡Registro creado exitosamente, haga clic en <b>Finalizar</b> para salir!', 0);</script>";
        }
    }

    $consulta_string="SELECT `gcmi_id`, `gcmi_matriz`, `gcmi_item_tipo`, `gcmi_item_consecutivo`, `gcmi_item_orden`, `gcmi_descripcion`, `gcmi_peso`, `gcmi_calificable`, `gcmi_grupo_peso`, `gcmi_visible`, `gcmi_tipo_error` FROM `tb_gestion_calidad_matriz_item` WHERE `gcmi_matriz`=?  AND `gcmi_item_tipo`='Grupo'";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $enc_existe=0;
    $ecu_existe=0;
    $ecn_existe=0;

    for ($i=0; $i < count($resultado_registros); $i++) { 
        if ($resultado_registros[$i][10]=="ENC") {
            $enc_existe=1;
        }
        if ($resultado_registros[$i][10]=="ECU") {
            $ecu_existe=1;
        }
        if ($resultado_registros[$i][10]=="ECN") {
            $ecn_existe=1;
        }
    }

    /*Enlace para botón finalizar y cancelar*/
    $ruta_cancelar_finalizar="gestion_calidad_matriz_configurar.php?pagina=".$pagina."&id=".$filtro_permanente."&reg=".base64_encode($id_registro);

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
                    <div class="col-md-3">
                        <div class="form-group">
                          <label for="consecutivo" id="consecutivo">Consecutivo</label>
                          <input type="text" class="form-control form-control-sm" name="consecutivo" id="consecutivo" minlength="1" maxlength="10" value="<?php if(isset($_POST["guardar_registro"])){ echo $consecutivo; } ?>" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'readonly'; } ?> required autocomplete="off">
                        </div>
                    </div>
                    <?php if($tipo_item=="Grupo"): ?>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="tipo_error">Nombre grupo</label>
                                <select class="form-control form-control-sm" name="tipo_error" id="tipo_error" <?php if($_SESSION['gcmc_registro_creado']==1 OR $tipo_error_lista!="") { echo 'disabled'; } ?> required autocomplete="off">
                                    <option value="">Seleccione</option>
                                    <?php if($enc_existe==0 OR $tipo_error=="ENC" OR $tipo_error_lista=="ENC"): ?>
                                    <option value="ENC" <?php if((isset($_POST["guardar_registro"]) AND $tipo_error=="ENC") OR $tipo_error_lista=="ENC"){ echo "selected"; } ?>>ERROR NO CRÍTICO</option>
                                    <?php endif; ?>
                                    <?php if($ecu_existe==0 OR $tipo_error=="ECU" OR $tipo_error_lista=="ECU"): ?>
                                    <option value="ECU" <?php if((isset($_POST["guardar_registro"]) AND $tipo_error=="ECU") OR $tipo_error_lista=="ECU"){ echo "selected"; } ?>>ERROR CRÍTICO DE USUARIO</option>
                                    <?php endif; ?>
                                    <?php if($ecn_existe==0 OR $tipo_error=="ECN" OR $tipo_error_lista=="ECN"): ?>
                                    <option value="ECN" <?php if((isset($_POST["guardar_registro"]) AND $tipo_error=="ECN") OR $tipo_error_lista=="ECN"){ echo "selected"; } ?>>ERROR CRÍTICO DE NEGOCIO</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-9">
                            <div class="form-group">
                              <label for="nombre" id="nombre">Nombre</label>
                              <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" maxlength="200" value="<?php if(isset($_POST["guardar_registro"])){ echo $nombre; } ?>" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">Tipo sección</label>
                            <input type="text" class="form-control form-control-sm" name="tipo" id="tipo" minlength="1" maxlength="10" value="<?php echo $tipo_item; ?>" readonly>
                        </div>
                    </div>

                    <?php if($tipo_item=="Grupo"): ?>
                        <div class="col-md-3">
                            <div class="form-group">
                              <label for="nota_maxima" id="nota_maxima">Nota máxima</label>
                              <input type="number" class="form-control form-control-sm" name="nota_maxima" id="nota_maxima" min="1" step="0.1" max="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nota_maxima; } ?>" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                              <label for="nota_sla" id="nota_sla">Nota SLA</label>
                              <input type="number" class="form-control form-control-sm" name="nota_sla" id="nota_sla" min="1" step="0.1" max="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $nota_sla; } ?>" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if($tipo_item!="Grupo"): ?>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="tipo_error">Tipo de error</label>
                                <select class="form-control form-control-sm" name="tipo_error" id="tipo_error" <?php if($_SESSION['gcmc_registro_creado']==1 OR $tipo_error_lista!="") { echo 'disabled'; } ?> required>
                                    <option value="">Seleccione</option>
                                    <?php if($enc_existe==0 OR $tipo_error=="ENC" OR $tipo_error_lista=="ENC"): ?>
                                    <option value="ENC" <?php if((isset($_POST["guardar_registro"]) AND $tipo_error=="ENC") OR $tipo_error_lista=="ENC"){ echo "selected"; } ?>>ERROR NO CRÍTICO</option>
                                    <?php endif; ?>
                                    <?php if($ecu_existe==0 OR $tipo_error=="ECU" OR $tipo_error_lista=="ECU"): ?>
                                    <option value="ECU" <?php if((isset($_POST["guardar_registro"]) AND $tipo_error=="ECU") OR $tipo_error_lista=="ECU"){ echo "selected"; } ?>>ERROR CRÍTICO DE USUARIO</option>
                                    <?php endif; ?>
                                    <?php if($ecn_existe==0 OR $tipo_error=="ECN" OR $tipo_error_lista=="ECN"): ?>
                                    <option value="ECN" <?php if((isset($_POST["guardar_registro"]) AND $tipo_error=="ECN") OR $tipo_error_lista=="ECN"){ echo "selected"; } ?>>ERROR CRÍTICO DE NEGOCIO</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if($tipo_item!="Grupo"): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="calificable">Calificable</label>
                                <select class="form-control form-control-sm" name="calificable" id="calificable" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'disabled'; } ?> required>
                                  <option value="">Seleccione</option>
                                  <option value="Si" <?php if(isset($_POST["guardar_registro"]) AND $calificable=="Si"){ echo "selected"; } ?>>Si</option>
                                  <option value="No" <?php if(isset($_POST["guardar_registro"]) AND $calificable=="No"){ echo "selected"; } ?>>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                              <label for="peso" id="peso">Peso</label>
                              <input type="number" class="form-control form-control-sm" name="peso" id="peso" min="1" step="0.1" max="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $peso; } ?>" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'readonly'; } ?> required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                              <label for="grupo_peso" id="grupo_peso">Grupo peso</label>
                              <input type="number" class="form-control form-control-sm" name="grupo_peso" id="grupo_peso" min="1" step="1" max="100" value="<?php if(isset($_POST["guardar_registro"])){ echo $grupo_peso; } ?>" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'readonly'; } ?>>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="visible">Visible</label>
                                <select class="form-control form-control-sm" name="visible" id="visible" <?php if($_SESSION['gcmc_registro_creado']==1) { echo 'disabled'; } ?> required>
                                  <option value="">Seleccione</option>
                                  <option value="Si" <?php if(isset($_POST["guardar_registro"]) AND $visible=="Si"){ echo "selected"; } ?>>Si</option>
                                  <option value="No" <?php if(isset($_POST["guardar_registro"]) AND $visible=="No"){ echo "selected"; } ?>>No</option>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php if($_SESSION['gcmc_registro_creado']==1): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php else: ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar</button>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
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