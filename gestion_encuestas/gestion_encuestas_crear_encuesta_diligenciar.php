<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

// error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    $titulo_header = "Encuestas | Diligenciar";

    $pagina=validar_input($_GET['pagina']);
    $id_filtro=validar_input($_GET['id']);
    $estado_bandeja=validar_input($_GET['est']);
    $id_registro=validar_input(base64_decode($_GET['reg']));

    if (!isset($_SESSION['registro_configuracion_encuesta_navegacion'])) {
        $_SESSION['registro_configuracion_encuesta_navegacion']=array();
    }


    $id_seccion_mostrar=$_SESSION['registro_configuracion_encuesta_navegacion'][count($_SESSION['registro_configuracion_encuesta_navegacion'])-1];

    if (!isset($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar])) {
        $_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]=array();
    }

    if(isset($_GET["regresar"]) AND $_GET["regresar"]=="on"){
        $id_seccion_borrar=base64_decode($_GET["secc_borrar"]);
        unset($_SESSION['registro_configuracion_encuesta_navegacion'][count($_SESSION['registro_configuracion_encuesta_navegacion'])-1]);
        unset($_SESSION['registro_configuracion_encuesta_estado_cierre_final'][count($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])-1]);
        unset($_SESSION['registro_configuracion_encuesta_estado_contacto_final'][count($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])-1]);
        unset($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][count($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])-1]);

        if (!isset($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_borrar])) {
            $_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_borrar]=array();
        }

        for ($i=0; $i < count($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_borrar]); $i++) { 
            $id_pregunta_borrar=$_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_borrar][$i];
            unset($_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_borrar]['preguntas'][$id_pregunta_borrar]['respuesta']);
        }

        header('Location: gestion_encuestas_crear_encuesta_diligenciar.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
    }

    if(isset($_POST["guardar_seccion"])){
        $id_seccion_segun_seccion="";
        $id_seccion_segun_seccion=$_SESSION['registro_configuracion_encuesta_opciones_destino'][$id_seccion_mostrar];
        $id_seccion_segun_respuesta="";

        if (!isset($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar])) {
            $_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]=array();
        }


        for ($i=0; $i < count($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]); $i++) { 
            $id_pregunta=$_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar][$i];
            $tipo_pregunta=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo'];
            $segun_respuesta=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['segun_respuesta'];
            
            if ($tipo_pregunta=="Casillas") {
                $respuesta_formulario=implode(', ', $_POST[$id_pregunta]);
            } else {
                $respuesta_formulario=$_POST[$id_pregunta];
            }
                
            $_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['respuesta']=$respuesta_formulario;
            if (($tipo_pregunta=="Desplegable" OR $tipo_pregunta=="Varias opciones") AND $segun_respuesta=="Si") {
                $id_seccion_segun_respuesta=$_SESSION['registro_configuracion_encuesta_opciones_destino'][$respuesta_formulario];
            }

            if ($tipo_pregunta=="Desplegable" OR $tipo_pregunta=="Varias opciones") {
                $segun_respuesta_estado_cierre=$_SESSION['registro_configuracion_encuesta_estado_cierre'][$respuesta_formulario];
                $segun_respuesta_estado_contacto=$_SESSION['registro_configuracion_encuesta_estado_contacto'][$respuesta_formulario];
                $segun_respuesta_estado_efectivo=$_SESSION['registro_configuracion_encuesta_estado_efectivo'][$respuesta_formulario];
            }
        }

        if (!isset($_SESSION['registro_configuracion_encuesta_secciones'])) {
            $_SESSION['registro_configuracion_encuesta_secciones']=array();
        }

        if (!isset($_SESSION['registro_configuracion_encuesta_secciones'])) {
            $_SESSION['registro_configuracion_encuesta_secciones']=array();
        }



        if ($id_seccion_segun_respuesta!="") {
            $_SESSION['registro_configuracion_encuesta_navegacion'][]=$id_seccion_segun_respuesta;
            $id_seccion_destino_final=$id_seccion_segun_respuesta;
        } elseif($id_seccion_segun_seccion!="") {
            $_SESSION['registro_configuracion_encuesta_navegacion'][]=$id_seccion_segun_seccion;
            $id_seccion_destino_final=$id_seccion_segun_seccion;
        } else {

            $indice_seccion_siguiente=array_search($id_seccion_mostrar, $_SESSION['registro_configuracion_encuesta_secciones'])+1;

            if ($indice_seccion_siguiente<=count($_SESSION['registro_configuracion_encuesta_secciones'])) {
                $id_seccion_siguiente=$_SESSION['registro_configuracion_encuesta_secciones'][$indice_seccion_siguiente];

                $_SESSION['registro_configuracion_encuesta_navegacion'][]=$id_seccion_siguiente;
                $id_seccion_destino_final=$id_seccion_siguiente;
                
            }
        }

        if (!isset($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])) {
            $_SESSION['registro_configuracion_encuesta_estado_cierre_final']=array();
        }


        if ($segun_respuesta_estado_cierre) {
            $_SESSION['registro_configuracion_encuesta_estado_cierre_final'][]=$segun_respuesta_estado_cierre;
        } elseif($_SESSION['registro_configuracion_encuesta_estado_cierre_final'][count($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])-1]) {
            $_SESSION['registro_configuracion_encuesta_estado_cierre_final'][]=$_SESSION['registro_configuracion_encuesta_estado_cierre_final'][count($_SESSION['registro_configuracion_encuesta_estado_cierre_final'])-1];
        } else {
            $_SESSION['registro_configuracion_encuesta_estado_cierre_final'][]=0;
        }

        if (!isset($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])) {
            $_SESSION['registro_configuracion_encuesta_estado_contacto_final']=array();
        }

        if ($segun_respuesta_estado_contacto) {
            $_SESSION['registro_configuracion_encuesta_estado_contacto_final'][]=$segun_respuesta_estado_contacto;
        } elseif($_SESSION['registro_configuracion_encuesta_estado_contacto_final'][count($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])-1]) {
            $_SESSION['registro_configuracion_encuesta_estado_contacto_final'][]=$_SESSION['registro_configuracion_encuesta_estado_contacto_final'][count($_SESSION['registro_configuracion_encuesta_estado_contacto_final'])-1];
        } else {
            $_SESSION['registro_configuracion_encuesta_estado_contacto_final'][]=0;
        }

        if (!isset($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])) {
            $_SESSION['registro_configuracion_encuesta_estado_efectivo_final']=array();
        }

        if ($segun_respuesta_estado_efectivo) {
            $_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][]=$segun_respuesta_estado_efectivo;
        } elseif($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][count($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])-1]) {
            $_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][]=$_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][count($_SESSION['registro_configuracion_encuesta_estado_efectivo_final'])-1];
        } else {
            $_SESSION['registro_configuracion_encuesta_estado_efectivo_final'][]=0;
        }

        if ($id_seccion_destino_final=="informacion_cierre") {
            header('Location: gestion_encuestas_crear_encuesta_informacion_complementaria.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        } elseif ($id_seccion_destino_final=="observaciones_cierre") {
            header('Location: gestion_encuestas_crear_encuesta_observaciones.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        } else {
            header('Location: gestion_encuestas_crear_encuesta_diligenciar.php?pagina='.$pagina.'&id='.$id_filtro.'&est='.$estado_bandeja.'&reg='.base64_encode($id_registro));
        }
    }

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
                    <?php if(count($_SESSION['registro_configuracion_encuesta_secciones'])>0): ?>
                        <div class="col-md-3 titulo-seccion-conteo py-1 my-0">
                            Sección <?php echo array_search($id_seccion_mostrar, $_SESSION['registro_configuracion_encuesta_secciones'])+2; ?> de <?php echo count($_SESSION['registro_configuracion_encuesta_secciones'])+3; ?>
                        </div>
                        <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                            <div class="row p-0">
                                <div class="col-md-12">
                                    <div class="py-2 px-3">
                                        <b><?php echo $_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['nombre']; ?></b>
                                    </div>
                                    <?php if($_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['descripcion']!=""): ?>
                                        <div class="descripcion-seccion py-1 px-3"><?php echo nl2br($_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['descripcion']); ?><hr class="py-0 my-1"></div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            <?php for ($j=0; $j < count($_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]); $j++): ?>
                                <?php
                                    $id_pregunta=$_SESSION['registro_configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar][$j];
                                    $id_seccion=$id_seccion_mostrar;
                                    $nombre=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['nombre'];
                                    $descripcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['descripcion'];
                                    $tipo=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo'];
                                    $obligatorio=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['obligatorio'];
                                    $tipo_campo=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo_campo'];
                                    $segun_respuesta=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['segun_respuesta'];
                                    
                                    if (!isset($_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'])) {
                                        $_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones']=array();
                                    }


                                    $conteo_opciones=count($_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones']);
                                    $respuesta_ingresada=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['respuesta'];
                                ?>
                                <div class="row px-3 py-1 m-0">
                                    <div class="col-md-12">
                                        <div class="pb-1">
                                            <?php echo $nombre; ?>
                                            <?php echo ($descripcion!="") ? "<div class='descripcion-seccion'>$descripcion</div>" : ''; ?>
                                        </div>
                                        <?php if($tipo=="Respuesta corta"): ?>
                                            <div class="form-group m-0">
                                                <input type="text" class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" maxlength="200" placeholder="Respuesta" <?php echo ($tipo_campo=="Numérico") ? 'onkeyup="this.value=validar_campo(this.value)"' : ''; ?> <?php echo ($obligatorio=="on") ? 'required' : ''; ?> autocomplete="off">
                                            </div>
                                        <?php elseif($tipo=="Párrafo"): ?>
                                            <div class="form-group m-0">
                                                <textarea class="form-control form-control-sm" name="<?php echo $id_pregunta; ?>" value="<?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?>" maxlength="5000" placeholder="Respuesta" <?php echo ($obligatorio=="on") ? 'required' : ''; ?>><?php echo ($respuesta_ingresada!="") ? $respuesta_ingresada : ''; ?></textarea>
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
                                                    $id_opcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['id'];
                                                    $nombre_opcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['nombre'];
                                                ?>
                                                <div class="form-group custom-control custom-radio m-0">
                                                    <input type="radio" class="custom-control-input" id="customCheck_<?php echo $id_opcion; ?>" name="<?php echo $id_pregunta; ?>" value="<?php echo $id_opcion; ?>" <?php echo ($respuesta_ingresada==$id_opcion) ? 'checked' : ''; ?> <?php echo ($obligatorio=="on") ? 'required' : ''; ?>>
                                                    <label class="custom-control-label" for="customCheck_<?php echo $id_opcion; ?>"><?php echo $nombre_opcion; ?></label>
                                                </div>
                                            <?php endfor; ?>
                                        <?php elseif($tipo=="Casillas"): ?>
                                            <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                <?php
                                                    $id_opcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['id'];
                                                    $nombre_opcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['nombre'];
                                                    if (!isset($respuesta_ingresada)) {
                                                        $respuesta_ingresada=array();
                                                    }
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
                                                            $id_opcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['id'];
                                                            $nombre_opcion=$_SESSION['registro_configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['opciones'][$k]['nombre'];
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
                            <button class="btn btn-success float-right ml-1" type="submit" name="guardar_seccion">Siguiente</button>
                            <?php if(count($_SESSION['registro_configuracion_encuesta_navegacion'])>1): ?>
                                <a href="gestion_encuestas_crear_encuesta_diligenciar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $id_filtro; ?>&est=<?php echo $estado_bandeja; ?>&reg=<?php echo base64_encode($id_registro); ?>&regresar=on&secc_borrar=<?php echo base64_encode($id_seccion_mostrar); ?>" class="btn btn-warning float-right ml-1">Regresar</a>
                            <?php endif; ?>
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