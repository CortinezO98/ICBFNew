<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Encuestas | Cargar Base - Usuarios";

    if(isset($_POST["guardar_registro"])){
        unset($_SESSION['usuarios_asignacion']);
        $_SESSION['usuarios_asignacion']=$_POST['usuarios'];

        if (count($_SESSION['usuarios_asignacion'])>0) {
            header('Location: gestion_encuestas_base_cargar.php');
        } else {
            $respuesta_accion = "<script type='text/javascript'>alertify.warning('¡Problemas al asignar usuarios, por favor verifique e intente nuevamente!', 0);</script>";
        }
    }

    if (!isset($_SESSION['usuarios_asignacion'])) {
        $_SESSION['usuarios_asignacion']=array();
    }

    $consulta_string_usuarios="SELECT `usu_id`, `usu_nombres_apellidos` FROM `tb_administrador_usuario` WHERE `usu_estado`='Activo' AND `usu_cargo_rol` LIKE '%Agente%' ORDER BY `usu_nombres_apellidos` ASC";
    $consulta_registros_usuarios = $enlace_db->prepare($consulta_string_usuarios);
    $consulta_registros_usuarios->execute();
    $resultado_registros_usuarios = $consulta_registros_usuarios->get_result()->fetch_all(MYSQLI_NUM);

    $ruta_cancelar_finalizar="gestion_encuestas.php?pagina=1&id=null&est=Pendientes";
    
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
        <div class="row">
            <div class="col-md-3 py-2">
                
            </div>
            <div class="col-md-9 conten-justify-center py-2">
                
            </div>
        </div>
        <form name="guardar_registro" action="" method="POST" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <?php if (!empty($respuesta_accion)) {echo $respuesta_accion;} ?>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="alert alert-warning p-1">Seleccione los usuarios para asignación de radicados</p>
                            <?php for ($i=0; $i < count($resultado_registros_usuarios); $i++): ?>
                            <div class="form-group custom-control custom-checkbox m-0">
                                <input type="checkbox" class="custom-control-input" id="customCheck_<?php echo $resultado_registros_usuarios[$i][0]; ?>" name="usuarios[]" value="<?php echo $resultado_registros_usuarios[$i][0]; ?>" <?php if(in_array($resultado_registros_usuarios[$i][0], $_SESSION['usuarios_asignacion'])){ echo "checked"; } ?>>
                                <label class="custom-control-label" for="customCheck_<?php echo $resultado_registros_usuarios[$i][0]; ?>"><?php echo $resultado_registros_usuarios[$i][1]; ?></label>
                            </div>
                            <?php endfor; ?>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_registro">Guardar y continuar</button>
                                <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
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
        $("#inputGroupFile01").change(function(){
            var valor_opcion = document.getElementById("inputGroupFile01").files[0].name;

            if (valor_opcion!="") {
                document.getElementById('inputGroupFile01label').innerHTML=valor_opcion.substring(0, 25)+"...";
                $("#inputGroupFile01label").addClass("color-verde");
            }
        });
    </script>
</body>
</html>