<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Vista Previa";

    $id_seccion_mostrar=$_SESSION['configuracion_encuesta_navegacion'][count($_SESSION['configuracion_encuesta_navegacion'])-1];

    if(isset($_GET["regresar"]) AND $_GET["regresar"]=="on"){
        unset($_SESSION['configuracion_encuesta_navegacion'][count($_SESSION['configuracion_encuesta_navegacion'])-1]);
        header('Location: gestion_encuestas_matriz_configurar_vista.php');
    }

    if(isset($_POST["guardar_seccion"])){
        $id_seccion_segun_seccion="";
        $id_seccion_segun_seccion=$_SESSION['configuracion_encuesta_opciones_destino'][$id_seccion_mostrar];
        $id_seccion_segun_respuesta="";
        for ($i=0; $i < count($_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]); $i++) { 
            $id_pregunta=$_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar][$i];
            $tipo_pregunta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo'];
            $segun_respuesta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['segun_respuesta'];
            $respuesta_formulario=$_POST[$id_pregunta];

            $_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['respuesta']=$respuesta_formulario;
            if (($tipo_pregunta=="Desplegable" OR $tipo_pregunta=="Casillas" OR $tipo_pregunta=="Varias opciones") AND $segun_respuesta=="Si") {
                $id_seccion_segun_respuesta=$_SESSION['configuracion_encuesta_opciones_destino'][$respuesta_formulario];

            }
        }

        if ($id_seccion_segun_respuesta!="") {
            $_SESSION['configuracion_encuesta_navegacion'][]=$id_seccion_segun_respuesta;
        } elseif($id_seccion_segun_seccion!="") {
            $_SESSION['configuracion_encuesta_navegacion'][]=$id_seccion_segun_seccion;
        } else {
            $indice_seccion_siguiente=array_search($id_seccion_mostrar, $_SESSION['configuracion_encuesta_secciones'])+1;

            if ($indice_seccion_siguiente<=count($_SESSION['configuracion_encuesta_secciones'])) {
                $id_seccion_siguiente=$_SESSION['configuracion_encuesta_secciones'][$indice_seccion_siguiente];

                $_SESSION['configuracion_encuesta_navegacion'][]=$id_seccion_siguiente;
                
            }

        }


        header('Location: gestion_encuestas_matriz_configurar_vista.php');

    }

    if(isset($_POST["enviar_encuesta"])){
        $id_seccion_segun_respuesta="";
        for ($i=0; $i < count($_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]); $i++) { 
            $id_pregunta=$_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar][$i];
            $tipo_pregunta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo'];
            $segun_respuesta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['segun_respuesta'];
            $respuesta_formulario=validar_input($_POST[$id_pregunta]);

            
        }

        $_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['respuesta']=$respuesta_formulario;

        header('Location: gestion_encuestas_matriz_configurar_vista_guardar.php');

    }

    $ruta_cancelar_finalizar="gestion_encuestas_matriz_configurar_vista_generar.php?reg=".base64_encode($_SESSION['detalle_encuesta']['id']);
    
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
        <?php
        // echo $id_seccion_mostrar;
        echo "<pre>";
        // print_r($_SESSION['configuracion_encuesta_navegacion']);
        // print_r($_SESSION['configuracion_encuesta_secciones']);
        // print_r($_SESSION['configuracion_encuesta_opciones_destino']);
        // print_r($_SESSION['configuracion_encuesta_detalle']);
        echo "</pre>";
        ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
            <form name="guardar_seccion" action="" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                        <div class="titulo-encuesta"><?php echo ($_SESSION['detalle_encuesta']['estado']=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $_SESSION['detalle_encuesta']['nombre']; ?></div>
                        <div class="descripcion-seccion"><?php echo $_SESSION['detalle_encuesta']['descripcion']; ?></div>
                    </div>
                    <?php if(count($_SESSION['configuracion_encuesta_secciones'])>0): ?>
                        <div class="col-md-3 titulo-seccion-conteo py-1 my-0">
                            Sección <?php echo array_search($id_seccion_mostrar, $_SESSION['configuracion_encuesta_secciones'])+1; ?> de <?php echo count($_SESSION['configuracion_encuesta_secciones']); ?>
                        </div>
                        <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                            <div class="row p-0">
                                <div class="col-md-12">
                                    <div class="py-2 px-3">
                                        <b><?php echo $_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['nombre']; ?></b>
                                    </div>
                                    <?php if($_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['descripcion']!=""): ?>
                                        <div class="descripcion-seccion py-1 px-3"><?php echo nl2br($_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['descripcion']); ?><hr class="py-0 my-1"></div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            <?php for ($j=0; $j < count($_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]); $j++): ?>
                                <?php
                                    $id_pregunta=$_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar][$j];
                                    $id_seccion=$id_seccion_mostrar;
                                    $nombre=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['nombre'];
                                    $descripcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['descripcion'];
                                    $tipo=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo'];
                                    $obligatorio=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['obligatorio'];
                                    $segun_respuesta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['segun_respuesta'];
                                    $conteo_opciones=count($_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones']);
                                    $respuesta_ingresada=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['respuesta'];
                                ?>
                                <div class="row px-3 py-1 m-0">
                                    <div class="col-md-12">
                                        <div class="pb-1">
                                            <?php echo $nombre; ?>
                                            <?php echo ($descripcion!="") ? "<div class='descripcion-seccion'>$descripcion</div>" : ''; ?>
                                        </div>
                                        <?php if($tipo=="Respuesta corta"): ?>
                                            <div class="form-group m-0">
                                                <input type="text" class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" placeholder="Respuesta" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                            </div>
                                        <?php elseif($tipo=="Párrafo"): ?>
                                            <div class="form-group m-0">
                                                <textarea class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" placeholder="Respuesta" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>><?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?></textarea>
                                            </div>
                                        <?php elseif($tipo=="Fecha"): ?>
                                            <div class="form-group m-0 col-md-3 p-0">
                                                <input type="date" class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                            </div>
                                        <?php elseif($tipo=="Hora"): ?>
                                            <div class="form-group m-0 col-md-3 p-0">
                                                <input type="time" class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                            </div>
                                        <?php elseif($tipo=="Fecha y hora"): ?>
                                            <div class="form-group m-0 col-md-4 p-0">
                                                <input type="datetime-local" class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                            </div>
                                        <?php elseif($tipo=="Varias opciones"): ?>
                                            <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                <?php
                                                    $id_opcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['id'];
                                                    $nombre_opcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['nombre'];
                                                ?>
                                                <div class="form-group custom-control custom-radio m-0">
                                                    <input type="radio" class="custom-control-input" id="customCheck_<?php echo $id_opcion; ?>" name="<?php echo $id_pregunta; ?>" value="<?php echo $id_opcion; ?>" <?php echo ($respuesta_ingresada==$id_opcion) ? 'checked' : ''; ?> <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                                    <label class="custom-control-label" for="customCheck_<?php echo $id_opcion; ?>"><?php echo $nombre_opcion; ?></label>
                                                </div>
                                            <?php endfor; ?>
                                        <?php elseif($tipo=="Casillas"): ?>
                                            <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                <?php
                                                    $id_opcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['id'];
                                                    $nombre_opcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['nombre'];
                                                ?>
                                                <div class="form-group custom-control custom-checkbox m-0">
                                                    <input type="checkbox" class="custom-control-input" id="customCheck_<?php echo $id_opcion; ?>" name="<?php echo $id_pregunta; ?>[]" value="<?php echo $id_opcion; ?>" <?php echo (in_array($id_opcion, $respuesta_ingresada)) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="customCheck_<?php echo $id_opcion; ?>"><?php echo $nombre_opcion; ?></label>
                                                </div>
                                            <?php endfor; ?>
                                        <?php elseif($tipo=="Desplegable"): ?>
                                            <div class="form-group m-0">
                                                <select class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" id="<?php echo $id_pregunta; ?>" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                                    <option value="">Seleccione</option>
                                                    <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                        <?php
                                                            $id_opcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['id'];
                                                            $nombre_opcion=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['nombre'];
                                                        ?>
                                                        <option value="<?php echo $id_opcion; ?>" <?php echo ($respuesta_ingresada==$id_opcion) ? 'selected' : ''; ?>><?php echo $nombre_opcion; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <hr class="mx-3 my-1">
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <div class="col-md-12 fondo-blanco p-0 mb-2">
                            <p class="alert alert-warning text-left p-1 m-0"><span class="fas fa-exclamation-triangle"></span> No se encontraron registros</p>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-12 p-0">
                        <div class="form-group">
                            <?php if(count($_SESSION['configuracion_encuesta_secciones'])==(array_search($id_seccion_mostrar, $_SESSION['configuracion_encuesta_secciones'])+1)): ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="enviar_encuesta">Enviar Encuesta</button>
                            <?php else: ?>
                                <button class="btn btn-success float-right ml-1" type="submit" name="guardar_seccion">Siguiente</button>
                            <?php endif; ?>
                            <?php if(count($_SESSION['configuracion_encuesta_navegacion'])>1): ?>
                                <a href="gestion_encuestas_matriz_configurar_vista.php?regresar=on" class="btn btn-warning float-right ml-1">Regresar</a>
                            <?php endif; ?>
                            <button class="btn btn-danger float-right" type="button" onclick="guardar_cancelar();">Cancelar</button>
                            <?php if(isset($_POST["enviar_encuesta"])): ?>
                                <a href="<?php echo $ruta_cancelar_finalizar; ?>" class="btn btn-dark float-right">Finalizar</a>
                            <?php endif; ?>
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
</body>
</html>