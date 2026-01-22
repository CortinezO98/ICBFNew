<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');

    $titulo_header = "Encuestas | Información General";
    
    $pagina=validar_input($_GET['pagina']);
    $id_filtro=validar_input($_GET['id']);
    $estado_bandeja=validar_input($_GET['est']);
    
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if(isset($_POST["guardar_seccion"])){
        $numero_marcado_fijo=validar_input($_POST['numero_marcado_fijo']);
        $numero_marcado_movil=validar_input($_POST['numero_marcado_movil']);
        $numero_cedula=validar_input($_POST['numero_cedula']);
        $nombre_ciudadano=validar_input($_POST['nombre_ciudadano']);

        $complemento_1=validar_input($_POST['complemento_1']);
        $complemento_2=validar_input($_POST['complemento_2']);
        $complemento_3=validar_input($_POST['complemento_3']);
        
        $consulta_string_revisar="SELECT `gera_radicado`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_auxiliar1`, `gera_auxiliar2`, `gera_auxiliar3` FROM `tb_gestion_encuesta_radicado` WHERE `gera_radicado`=?";

        $consulta_registros_revisar = $enlace_db->prepare($consulta_string_revisar);
        $consulta_registros_revisar->bind_param("s", $id_registro);
        $consulta_registros_revisar->execute();
        $resultado_registros_revisar = $consulta_registros_revisar->get_result()->fetch_all(MYSQLI_NUM);

        // Prepara la sentencia
        $consulta_actualizar = $enlace_db->prepare("UPDATE `tb_gestion_encuesta_radicado` SET `gera_tel_fijo`=?, `gera_tel_movil`=?, `gera_peticionario_id`=?, `gera_peticionario_nombre`=?, `gera_auxiliar1`=?, `gera_auxiliar2`=?, `gera_auxiliar3`=? WHERE `gera_radicado`=?");

        // Agrega variables a sentencia preparada
        $consulta_actualizar->bind_param('ssssssss', $numero_marcado_fijo, $numero_marcado_movil, $numero_cedula, $nombre_ciudadano, $complemento_1, $complemento_2, $complemento_3, $id_registro);
        
        // Ejecuta sentencia preparada
        $consulta_actualizar->execute();
        
        if (comprobarSentencia($enlace_db->info)) {
            //Preparar consulta log insertar
            $consulta_string_log = "INSERT INTO `tb_gestion_encuesta_radicado_log`(`gerlog_radicado`, `gerlog_campo`, `gerlog_anterior`, `gerlog_nuevo`, `gerlog_registro_usuario`) VALUES (?,?,?,?,?)";
            $consulta_registros_log = $enlace_db->prepare($consulta_string_log);
            $consulta_registros_log->bind_param("sssss", $id_registro, $log_campo, $log_anterior, $log_nuevo, $_SESSION["usu_id"]);

            //Registro log nombre campos
            $array_log_valor_campos[]="Número fijo";
            $array_log_valor_campos[]="Número móvil";
            $array_log_valor_campos[]="Doc. Peticionario";
            $array_log_valor_campos[]="Nombres y Apellidos Peticionario";
            $array_log_valor_campos[]="Complemento 1";
            $array_log_valor_campos[]="Complemento 2";
            $array_log_valor_campos[]="Complemento 3";

            // Registro log valores anteriores
            $array_log_valor_old[]=$resultado_registros_revisar[0][3];
            $array_log_valor_old[]=$resultado_registros_revisar[0][4];
            $array_log_valor_old[]=$resultado_registros_revisar[0][1];
            $array_log_valor_old[]=$resultado_registros_revisar[0][2];
            $array_log_valor_old[]=$resultado_registros_revisar[0][5];
            $array_log_valor_old[]=$resultado_registros_revisar[0][6];
            $array_log_valor_old[]=$resultado_registros_revisar[0][7];
            
            // Registro log valores nuevos
            $array_log_valor_new[]=$numero_marcado_fijo;
            $array_log_valor_new[]=$numero_marcado_movil;
            $array_log_valor_new[]=$numero_cedula;
            $array_log_valor_new[]=$nombre_ciudadano;
            $array_log_valor_new[]=$compelmento_1;
            $array_log_valor_new[]=$compelmento_2;
            $array_log_valor_new[]=$compelmento_3;
            
            for ($i=0; $i < count($array_log_valor_old); $i++) { 
                if ($array_log_valor_old[$i]!=$array_log_valor_new[$i]) {
                    $log_campo=$array_log_valor_campos[$i];
                    $log_anterior=$array_log_valor_old[$i];
                    $log_nuevo=$array_log_valor_new[$i];
                    $consulta_registros_log->execute();
                }
            }

            header('Location: gestion_encuestas_crear_encuesta_diligenciar.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        } else {
            header('Location: gestion_encuestas_crear_encuesta_informacion_general.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        }
    }

    $consulta_string="SELECT `gera_radicado`, `gera_matriz_id`, TE.`gem_nombre_encuesta`, `gera_fecha_peticion`, `gera_peticionario_id`, `gera_peticionario_nombre`, `gera_tel_fijo`, `gera_tel_movil`, `gera_genero`, `gera_motivo`, `gera_regional`, `gera_centro_zonal`, `gera_estado_gestion`, `gera_intentos`, `gera_contactado`, `gera_efectivo`, `gera_ultima_observacion`, `gera_fecha_actualizacion`, `gera_usuario_gestion`, TU.`usu_nombres_apellidos`, `gera_registro_fecha`, `gera_auxiliar1`, `gera_auxiliar2`, `gera_auxiliar3` FROM `tb_gestion_encuesta_radicado` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuesta_radicado`.`gera_usuario_gestion`=TU.`usu_id` LEFT JOIN `tb_gestion_encuestas_matriz` AS TE ON `tb_gestion_encuesta_radicado`.`gera_matriz_id`=TE.`gem_id` WHERE `gera_radicado`=?";

    $consulta_registros = $enlace_db->prepare($consulta_string);
    $consulta_registros->bind_param("s", $id_registro);
    $consulta_registros->execute();
    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

    $ruta_cancelar_finalizar="gestion_encuestas.php?pagina=".$pagina."&id=".$id_filtro."&est=".$estado_bandeja;
    
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
    <div class="contenido"  style="background-color: #ede7f6;">
        <div class="row justify-content-center" id="elemento_1">
            <div class="col-md-6 py-2">
                
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
            <form name="guardar_seccion" action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                        <div class="titulo-encuesta"><?php echo ($_SESSION['registro_detalle_encuesta']['estado']=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $_SESSION['registro_detalle_encuesta']['nombre']; ?></div>
                        <div class="descripcion-seccion"><?php echo $_SESSION['registro_detalle_encuesta']['descripcion']; ?></div>
                    </div>
                    <div class="col-md-3 titulo-seccion-conteo py-1 my-0">
                        Sección 1 de <?php echo count($_SESSION['registro_configuracion_encuesta_secciones'])+3; ?>
                    </div>
                    <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                        <div class="row p-0">
                            <div class="col-md-12">
                                <div class="py-2 px-3">
                                    <b>Información General</b>
                                </div>
                            </div>
                        </div>
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Número de Radicado"; ?>
                                    <?php echo "<div class='descripcion-seccion'>Número de petición SIM (código del caso)</div>"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="numero_radicado" value="<?php echo $resultado_registros[0][0]; ?>" required disabled>
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Número marcado fijo"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="numero_marcado_fijo" id="numero_marcado_fijo" value="<?php echo $resultado_registros[0][6]; ?>" autocomplete="off" maxlength="7" onkeyup="this.value=validar_campo(this.value)">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Número marcado móvil"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="numero_marcado_movil" value="<?php echo $resultado_registros[0][7]; ?>" required autocomplete="off" maxlength="10" onkeyup="this.value=validar_campo(this.value)">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Número de Cédula del Ciudadano(a)"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="numero_cedula" value="<?php echo $resultado_registros[0][4]; ?>" required autocomplete="off" maxlength="13" onkeyup="this.value=validar_campo(this.value)">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Nombre del Ciudadano(a)"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="nombre_ciudadano" value="<?php echo $resultado_registros[0][5]; ?>" required autocomplete="off" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Complemento 1"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="complemento_1" value="<?php echo $resultado_registros[0][21]; ?>" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Complemento 2"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="complemento_2" value="<?php echo $resultado_registros[0][22]; ?>" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                        <div class="row px-3 py-1 m-0">
                            <div class="col-md-12">
                                <div class="pb-1">
                                    <?php echo "Complemento 3"; ?>
                                </div>
                                <div class="form-group m-0">
                                    <input type="text" class="form-control form-control-sm" name="complemento_3" value="<?php echo $resultado_registros[0][23]; ?>" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <hr class="mx-3 my-1">
                    </div>
                    <div class="col-md-12 p-0">
                        <div class="form-group">
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_seccion">Siguiente</button>
                            <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </div>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script type="text/javascript">
        function validar_campo(string) {
            var out = '';
            var filtro = '1234567890';//Caracteres validos
            
            //Recorrer el texto y verificar si el caracter se encuentra en la lista de validos 
            for (var i=0; i<string.length; i++)
               if (filtro.indexOf(string.charAt(i)) != -1) 
                     //Se añaden a la salida los caracteres validos
                 out += string.charAt(i);
            
            //Retornar valor filtrado
            return out;
        }
    </script>
</body>
</html>