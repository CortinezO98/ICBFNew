<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Configurar";
    $pagina=validar_input($_GET['pagina']);
    $filtro_permanente=validar_input($_GET['id']);
    $id_registro=validar_input(base64_decode($_GET['reg']));
    unset($_SESSION['encuesta_seccion_registro_creado']);
    unset($_SESSION['encuesta_pregunta_registro_creado']);
    unset($_SESSION['registro_eliminado_seccion_encuesta']);
    unset($_SESSION['registro_eliminado_pregunta_encuesta']);
    unset($_SESSION['registro_eliminado_opcion_encuesta']);

    $consulta_string_matriz="SELECT `gem_id`, `gem_nombre_encuesta`, `gem_estado`, `gem_descripcion`, `gem_registro_usuario`, `gem_registro_fecha`, TU.`usu_nombres_apellidos` FROM `tb_gestion_encuestas_matriz` LEFT JOIN `tb_administrador_usuario` AS TU ON `tb_gestion_encuestas_matriz`.`gem_registro_usuario`=TU.`usu_id` WHERE `gem_id`=?";

    $consulta_registros_matriz = $enlace_db->prepare($consulta_string_matriz);
    $consulta_registros_matriz->bind_param("s", $id_registro);
    $consulta_registros_matriz->execute();
    $resultado_registros_matriz = $consulta_registros_matriz->get_result()->fetch_all(MYSQLI_NUM);
    
    $consulta_string_seccion="SELECT `gems_id`, `gems_encuesta`, `gems_nombre_seccion`, `gems_descripcion`, `gems_imagen`, `gems_video`, `gems_siguiente_seccion`, `gems_orden_mostrar` FROM `tb_gestion_encuestas_matriz_seccion` WHERE `gems_encuesta`=? ORDER BY `gems_orden_mostrar` ASC";
    $consulta_registros_seccion = $enlace_db->prepare($consulta_string_seccion);
    $consulta_registros_seccion->bind_param("s", $id_registro);
    $consulta_registros_seccion->execute();
    $resultado_registros_seccion = $consulta_registros_seccion->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_seccion); $i++) { 
        $array_secciones[$resultado_registros_seccion[$i][0]]['nombre']=$resultado_registros_seccion[$i][2];
    }

    $consulta_string_preguntas="SELECT `gemp_id`, `gemp_encuesta`, `gemp_seccion`, `gemp_pregunta_nombre`, `gemp_pregunta_descripcion`, `gemp_tipo`, `gemp_obligatorio`, `gemp_seccion_segun_respuesta`, `gemp_orden_mostrar`, `gemp_tipo_campo` FROM `tb_gestion_encuestas_matriz_seccion_pregunta` WHERE `gemp_encuesta`=? ORDER BY `gemp_orden_mostrar` ASC";
    $consulta_registros_preguntas = $enlace_db->prepare($consulta_string_preguntas);
    $consulta_registros_preguntas->bind_param("s", $id_registro);
    $consulta_registros_preguntas->execute();
    $resultado_registros_preguntas = $consulta_registros_preguntas->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_preguntas); $i++) { 
        $array_secciones[$resultado_registros_preguntas[$i][2]]['preguntas'][]=$resultado_registros_preguntas[$i][0];

        $array_preguntas[$resultado_registros_preguntas[$i][0]]['nombre']=$resultado_registros_preguntas[$i][3];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['descripcion']=$resultado_registros_preguntas[$i][4];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['tipo']=$resultado_registros_preguntas[$i][5];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['obligatorio']=$resultado_registros_preguntas[$i][6];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['segun_respuesta']=$resultado_registros_preguntas[$i][7];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['orden']=$resultado_registros_preguntas[$i][8];
        $array_preguntas[$resultado_registros_preguntas[$i][0]]['tipo_campo']=$resultado_registros_preguntas[$i][9];
    }

    $consulta_string_opciones="SELECT `gemo_id`, `gemo_encuesta`, `gemo_seccion`, `gemo_pregunta`, `gemo_opcion_nombre`, `gemo_opcion_siguiente_seccion`, `gemo_orden_mostrar`, `gemo_estado_registro`, `gemo_estado_contacto`, `gemo_estado_efectivo`, `gemo_notificacion` FROM `tb_gestion_encuestas_matriz_seccion_pregunta_opcion` WHERE `gemo_encuesta`=? ORDER BY `gemo_orden_mostrar` ASC";
    $consulta_registros_opciones = $enlace_db->prepare($consulta_string_opciones);
    $consulta_registros_opciones->bind_param("s", $id_registro);
    $consulta_registros_opciones->execute();
    $resultado_registros_opciones = $consulta_registros_opciones->get_result()->fetch_all(MYSQLI_NUM);

    for ($i=0; $i < count($resultado_registros_opciones); $i++) {
        $array_preguntas[$resultado_registros_opciones[$i][3]]['opciones'][]=$resultado_registros_opciones[$i][0];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['nombre']=$resultado_registros_opciones[$i][4];
        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['siguiente_seccion']=$resultado_registros_opciones[$i][5];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_cierre']=$resultado_registros_opciones[$i][7];
        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_contacto']=$resultado_registros_opciones[$i][8];
        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_efectivo']=$resultado_registros_opciones[$i][9];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['estado_notificacion']=$resultado_registros_opciones[$i][10];

        $array_preguntas_opciones[$resultado_registros_opciones[$i][3]][$resultado_registros_opciones[$i][0]]['orden']=$resultado_registros_opciones[$i][6];
    }

    // $consulta_string_conteo_encuestas="SELECT COUNT(`gcm_id`) FROM `tb_gestion_calidad_monitoreo` WHERE `gcm_matriz`=?";

    // $consulta_registros_conteo_encuestas = $enlace_db->prepare($consulta_string_conteo_encuestas);
    // $consulta_registros_conteo_encuestas->bind_param("s", $id_registro);
    // $consulta_registros_conteo_encuestas->execute();
    // $resultado_registros_conteo_encuestas = $consulta_registros_conteo_encuestas->get_result()->fetch_all(MYSQLI_NUM);
    
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
                <a href="gestion_encuestas_matriz_configurar_vista_generar.php?reg=<?php echo base64_encode($id_registro); ?>" target="_blank" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-eye"></span></div><div class="pl-2 menu_res float-left">Vista previa</div></a>
                <!-- <a href="gestion_encuestas_matriz_configurar_vista_diagrama.php?reg=<?php echo base64_encode($id_registro); ?>" target="_blank" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-sitemap"></span></div><div class="pl-2 menu_res float-left">Diagrama de Flujo</div></a> -->
                <a href="gestion_encuestas_matriz.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>" class="btn btn-corp menu float-right"><div class="float-left"><span class="fas fa-arrow-left"></span></div><div class="pl-2 menu_res float-left">Regresar</div></a>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12 fondo-blanco border-gris radius-5 mb-2 border-top-corp py-2">
                        <div class="titulo-encuesta"><?php echo ($resultado_registros_matriz[0][2]=='Activo') ? "<span class='fas fa-check-circle color-verde'></span>" : ""; ?> <?php echo $resultado_registros_matriz[0][1]; ?></div>
                        <div class="descripcion-seccion"><?php echo $resultado_registros_matriz[0][3]; ?></div>
                        <a href="gestion_encuestas_matriz_seccion_crear.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>" class="btn btn-primary btn-width float-right" title="Añadir sección"><span class="fas fa-th-large"></span> Añadir sección</a>
                    </div>
                    <?php if(count($resultado_registros_seccion)>0): ?>
                        <?php for ($i=0; $i < count($resultado_registros_seccion); $i++): ?>
                            <?php
                                $id_seccion_destino_seccion=$resultado_registros_seccion[$i][6];
                                if ($id_seccion_destino_seccion!="") {
                                    if ($id_seccion_destino_seccion=="informacion_cierre") {
                                        $nombre_seccion_destino_seccion="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Información cierre'></span>";
                                    } elseif ($id_seccion_destino_seccion=="observaciones_cierre") {
                                        $nombre_seccion_destino_seccion="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Observaciones cierre'></span>";
                                    } else {
                                        $nombre_seccion_destino_seccion="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a ".$array_secciones[$id_seccion_destino_seccion]['nombre']."'></span>";
                                    }
                                } else {
                                    $nombre_seccion_destino_seccion="";
                                }
                            ?>
                            <div class="col-md-3 titulo-seccion-conteo py-1 my-0">
                                Sección <?php echo $i+1; ?> de <?php echo count($resultado_registros_seccion); ?>
                            </div>
                            <div class="col-md-12 fondo-blanco titulo-seccion border-gris radius-5 border-top-corp pb-2 pt-0 px-0 mb-2">
                                <div class="row p-0">
                                    <div class="col-md-12"> 
                                        <div class="py-2 px-3">
                                            <a href="gestion_encuestas_matriz_seccion_editar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&sec=<?php echo base64_encode($resultado_registros_seccion[$i][0]); ?>" class="opcion-editar" title="Editar sección"><span class="fas fa-pen"></span></a>
                                            <?php
                                                if (!isset($array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'])) {
                                                    $array_secciones[$resultado_registros_seccion[$i][0]]['preguntas']=array();
                                                }
                                            ?>
                                            <?php if(count($array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'])==0): ?>
                                                <a href="gestion_encuestas_matriz_seccion_eliminar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&sec=<?php echo base64_encode($resultado_registros_seccion[$i][0]); ?>" class="opcion-eliminar" title="Eliminar sección"><span class="fas fa-trash-alt"></span></a>
                                            <?php endif; ?>
                                            <b><?php echo $resultado_registros_seccion[$i][2]; ?></b>
                                            <?php echo $nombre_seccion_destino_seccion; ?>
                                        </div>
                                        <?php if($resultado_registros_seccion[$i][3]!=""): ?>
                                            <div class="descripcion-seccion py-1 px-3"><?php echo nl2br($resultado_registros_seccion[$i][3]); ?><hr class="py-0 my-1"></div>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                                <?php for ($j=0; $j < count($array_secciones[$resultado_registros_seccion[$i][0]]['preguntas']); $j++): ?>
                                    <?php
                                        $id_pregunta=$array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'][$j];
                                        $id_seccion=$resultado_registros_seccion[$i][0];
                                        $nombre=$array_preguntas[$id_pregunta]['nombre'];
                                        $descripcion=$array_preguntas[$id_pregunta]['descripcion'];
                                        $tipo=$array_preguntas[$id_pregunta]['tipo'];
                                        $obligatorio=$array_preguntas[$id_pregunta]['obligatorio'];
                                        $segun_respuesta=$array_preguntas[$id_pregunta]['segun_respuesta'];
                                        $orden=$array_preguntas[$id_pregunta]['orden'];
                                        $tipo_campo=$array_preguntas[$id_pregunta]['tipo_campo'];

                                        if (!isset($array_preguntas[$id_pregunta]['opciones'])) {
                                            $array_preguntas[$id_pregunta]['opciones']=array();
                                        }
                                        
                                        $conteo_opciones=count($array_preguntas[$id_pregunta]['opciones']);
                                    ?>
                                    <div class="row px-3 py-1 m-0">
                                        <div class="col-md-12">
                                            <div class="pb-1">
                                                <?php if($tipo=="Respuesta corta"): ?>
                                                    <span class="fas fa-grip-lines color-corporativo"></span>
                                                    <?php if($tipo_campo=="Numérico"): ?>
                                                        <span class="fas fa-sort-numeric-down color-corporativo"></span>
                                                    <?php else: ?>
                                                        <span class="fas fa-spell-check color-corporativo"></span>
                                                    <?php endif; ?>
                                                <?php elseif($tipo=="Párrafo"): ?>
                                                    <span class="fas fa-align-justify color-corporativo"></span>
                                                <?php elseif($tipo=="Fecha"): ?>
                                                    <span class="fas fa-calendar-alt color-corporativo"></span>
                                                <?php elseif($tipo=="Hora"): ?>
                                                    <span class="fas fa-clock color-corporativo"></span>
                                                <?php elseif($tipo=="Fecha y hora"): ?>
                                                    <span class="fas fa-calendar-day color-corporativo"></span>
                                                <?php elseif($tipo=="Varias opciones"): ?>
                                                    <span class="fas fa-dot-circle color-corporativo"></span>
                                                <?php elseif($tipo=="Casillas"): ?>
                                                    <span class="fas fa-check-square color-corporativo"></span>
                                                <?php elseif($tipo=="Desplegable"): ?>
                                                    <span class="fas fa-caret-square-down color-corporativo"></span>
                                                <?php endif; ?>
                                                <a href="gestion_encuestas_matriz_pregunta_editar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&sec=<?php echo base64_encode($id_seccion); ?>&pre=<?php echo base64_encode($id_pregunta); ?>" class="opcion-editar" title="Editar pregunta"><span class="fas fa-pen"></span></a>
                                                <a href="gestion_encuestas_matriz_pregunta_eliminar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&sec=<?php echo base64_encode($id_seccion); ?>&pre=<?php echo base64_encode($id_pregunta); ?>" class="opcion-eliminar" title="Eliminar pregunta"><span class="fas fa-trash-alt"></span></a>
                                                <?php echo $nombre; ?>
                                                <?php echo ($descripcion!="")? "<div class='descripcion-seccion'>$descripcion</div>" : ''; ?>
                                            </div>
                                            <?php if($tipo=="Respuesta corta"): ?>
                                                <div class="form-group m-0">
                                                    <input type="text" class="form-control form-control-sm" name="" placeholder="Respuesta" readonly>
                                                </div>
                                            <?php elseif($tipo=="Párrafo"): ?>
                                                <div class="form-group m-0">
                                                    <textarea class="form-control form-control-sm" name="" placeholder="Respuesta" readonly></textarea>
                                                </div>
                                            <?php elseif($tipo=="Fecha"): ?>
                                                <div class="form-group m-0 col-md-3 p-0">
                                                    <input type="date" class="form-control form-control-sm" name="" readonly>
                                                </div>
                                            <?php elseif($tipo=="Hora"): ?>
                                                <div class="form-group m-0 col-md-3 p-0">
                                                    <input type="time" class="form-control form-control-sm" name="" readonly>
                                                </div>
                                            <?php elseif($tipo=="Fecha y hora"): ?>
                                                <div class="form-group m-0 col-md-4 p-0">
                                                    <input type="datetime-local" class="form-control form-control-sm" name="" readonly>
                                                </div>
                                            <?php elseif($tipo=="Varias opciones"): ?>
                                                <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                    <?php
                                                        $id_opcion=$array_preguntas[$array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'][$j]]['opciones'][$k];
                                                        $id_seccion_destino=$array_preguntas_opciones[$id_pregunta][$id_opcion]['siguiente_seccion'];

                                                        if ($id_seccion_destino!="") {
                                                            if ($id_seccion_destino=="informacion_cierre") {
                                                                $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Información cierre'></span>";
                                                            } elseif ($id_seccion_destino=="observaciones_cierre") {
                                                                $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Observaciones cierre'></span>";
                                                            } else {
                                                                $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a ".$array_secciones[$id_seccion_destino]['nombre']."'></span>";
                                                            }
                                                        } else {
                                                            $nombre_seccion_destino="";
                                                        }

                                                        $opcion_contactado=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_contacto'];
                                                        if ($opcion_contactado) {
                                                            $icono_opcion_contactado="<span class='fas fa-phone-alt font-size-13 color-rojo' title='Contactado'></span>";
                                                        } else {
                                                            $icono_opcion_contactado="";
                                                        }

                                                        $opcion_efectivo=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_efectivo'];
                                                        if ($opcion_efectivo) {
                                                            $icono_opcion_efectivo="<span class='fas fa-user-check font-size-13 color-rojo' title='Efectivo'></span>";
                                                        } else {
                                                            $icono_opcion_efectivo="";
                                                        }

                                                        $opcion_cierre=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_cierre'];
                                                        if ($opcion_cierre) {
                                                            $icono_opcion_cierre="<span class='fas fa-lock font-size-13 color-rojo' title='Cierre'></span>";
                                                        } else {
                                                            $icono_opcion_cierre="";
                                                        }

                                                        $opcion_notificacion=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_notificacion'];
                                                        if ($opcion_notificacion=="Si") {
                                                            $icono_opcion_notificacion="<span class='fas fa-envelope font-size-13 color-rojo' title='Notificar alerta'></span>";
                                                        } else {
                                                            $icono_opcion_notificacion="";
                                                        }
                                                    ?>
                                                    <div class="col-md-12 m-0">
                                                        <label class="label my-0" for="opcion_<?php echo $id_opcion; ?>">
                                                            <a href="gestion_encuestas_matriz_opcion_eliminar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&pre=<?php echo base64_encode($id_pregunta); ?>&opc=<?php echo base64_encode($id_opcion); ?>" class="opcion-eliminar" title="Eliminar opción"><span class="fas fa-trash-alt"></span></a>
                                                            <?php echo $array_preguntas_opciones[$id_pregunta][$id_opcion]['nombre']; ?> <?php echo $nombre_seccion_destino; ?> <?php echo $icono_opcion_contactado; ?> <?php echo $icono_opcion_efectivo; ?> <?php echo $icono_opcion_cierre; ?> <?php echo $icono_opcion_notificacion; ?>
                                                        </label>
                                                    </div>
                                                <?php endfor; ?>
                                            <?php elseif($tipo=="Casillas"): ?>
                                                <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                    <?php
                                                        $id_opcion=$array_preguntas[$array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'][$j]]['opciones'][$k];
                                                        $id_seccion_destino=$array_preguntas_opciones[$id_pregunta][$id_opcion]['siguiente_seccion'];

                                                        if ($id_seccion_destino!="") {
                                                            if ($id_seccion_destino=="informacion_cierre") {
                                                                $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Información cierre'></span>";
                                                            } elseif ($id_seccion_destino=="observaciones_cierre") {
                                                                $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Observaciones cierre'></span>";
                                                            } else {
                                                                $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a ".$array_secciones[$id_seccion_destino]['nombre']."'></span>";
                                                            }
                                                        } else {
                                                            $nombre_seccion_destino="";
                                                        }

                                                        $opcion_contactado=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_contacto'];
                                                        if ($opcion_contactado) {
                                                            $icono_opcion_contactado="<span class='fas fa-phone-alt font-size-13 color-rojo' title='Contactado'></span>";
                                                        } else {
                                                            $icono_opcion_contactado="";
                                                        }

                                                        $opcion_efectivo=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_efectivo'];
                                                        if ($opcion_efectivo) {
                                                            $icono_opcion_efectivo="<span class='fas fa-user-check font-size-13 color-rojo' title='Efectivo'></span>";
                                                        } else {
                                                            $icono_opcion_efectivo="";
                                                        }

                                                        $opcion_cierre=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_cierre'];
                                                        if ($opcion_cierre) {
                                                            $icono_opcion_cierre="<span class='fas fa-lock font-size-13 color-rojo' title='Cierre'></span>";
                                                        } else {
                                                            $icono_opcion_cierre="";
                                                        }

                                                        $opcion_notificacion=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_notificacion'];
                                                        if ($opcion_notificacion=="Si") {
                                                            $icono_opcion_notificacion="<span class='fas fa-envelope font-size-13 color-rojo' title='Notificar alerta'></span>";
                                                        } else {
                                                            $icono_opcion_notificacion="";
                                                        }
                                                    ?>
                                                    <div class="col-md-12 m-0">
                                                        <label class="label my-0" for="opcion_<?php echo $id_opcion; ?>">
                                                            <a href="gestion_encuestas_matriz_opcion_eliminar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&pre=<?php echo base64_encode($id_pregunta); ?>&opc=<?php echo base64_encode($id_opcion); ?>" class="opcion-eliminar" title="Eliminar opción"><span class="fas fa-trash-alt"></span></a>
                                                            <?php echo $array_preguntas_opciones[$id_pregunta][$id_opcion]['nombre']; ?> <?php echo $nombre_seccion_destino; ?> <?php echo $icono_opcion_contactado; ?> <?php echo $icono_opcion_efectivo; ?> <?php echo $icono_opcion_cierre; ?> <?php echo $icono_opcion_notificacion; ?>
                                                        </label>
                                                    </div>
                                                <?php endfor; ?>
                                            <?php elseif($tipo=="Desplegable"): ?>
                                                    <?php for ($k=0; $k < $conteo_opciones; $k++): ?>
                                                            <?php
                                                                $id_opcion_orden=$k+1;
                                                                $id_opcion=$array_preguntas[$array_secciones[$resultado_registros_seccion[$i][0]]['preguntas'][$j]]['opciones'][$k];
                                                                $id_seccion_destino=$array_preguntas_opciones[$id_pregunta][$id_opcion]['siguiente_seccion'];

                                                                if ($id_seccion_destino!="") {
                                                                    if ($id_seccion_destino=="informacion_cierre") {
                                                                        $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Información cierre'></span>";
                                                                    } elseif ($id_seccion_destino=="observaciones_cierre") {
                                                                        $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a Observaciones cierre'></span>";
                                                                    } else {
                                                                        $nombre_seccion_destino="<span class='fas fa-exclamation-circle font-size-13 color-rojo' title='Ir a ".$array_secciones[$id_seccion_destino]['nombre']."'></span>";
                                                                    }
                                                                } else {
                                                                    $nombre_seccion_destino="";
                                                                }

                                                                $opcion_contactado=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_contacto'];
                                                                if ($opcion_contactado) {
                                                                    $icono_opcion_contactado="<span class='fas fa-phone-alt font-size-13 color-rojo' title='Contactado'></span>";
                                                                } else {
                                                                    $icono_opcion_contactado="";
                                                                }

                                                                $opcion_efectivo=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_efectivo'];
                                                                if ($opcion_efectivo) {
                                                                    $icono_opcion_efectivo="<span class='fas fa-user-check font-size-13 color-rojo' title='Efectivo'></span>";
                                                                } else {
                                                                    $icono_opcion_efectivo="";
                                                                }

                                                                $opcion_cierre=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_cierre'];
                                                                if ($opcion_cierre) {
                                                                    $icono_opcion_cierre="<span class='fas fa-lock font-size-13 color-rojo' title='Cierre'></span>";
                                                                } else {
                                                                    $icono_opcion_cierre="";
                                                                }

                                                                $opcion_notificacion=$array_preguntas_opciones[$id_pregunta][$id_opcion]['estado_notificacion'];
                                                                if ($opcion_notificacion=="Si") {
                                                                    $icono_opcion_notificacion="<span class='fas fa-envelope font-size-13 color-rojo' title='Notificar alerta'></span>";
                                                                } else {
                                                                    $icono_opcion_notificacion="";
                                                                }
                                                            ?>
                                                    <div class="col-md-12 m-0">
                                                        <label class="label my-0" for="opcion_<?php echo $id_opcion; ?>">
                                                            <a href="gestion_encuestas_matriz_opcion_eliminar.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&pre=<?php echo base64_encode($id_pregunta); ?>&opc=<?php echo base64_encode($id_opcion); ?>" class="opcion-eliminar" title="Eliminar opción"><span class="fas fa-trash-alt"></span></a>
                                                                <?php echo $array_preguntas_opciones[$id_pregunta][$id_opcion]['nombre']; ?> <?php echo $nombre_seccion_destino; ?> <?php echo $icono_opcion_contactado; ?> <?php echo $icono_opcion_efectivo; ?> <?php echo $icono_opcion_cierre; ?> <?php echo $icono_opcion_notificacion; ?>
                                                            
                                                        </label>
                                                    </div>
                                                    <?php endfor; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <hr class="mx-3 my-1">
                                <?php endfor; ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="py-1 px-3">
                                            <a href="gestion_encuestas_matriz_pregunta_crear.php?pagina=<?php echo $pagina; ?>&id=<?php echo $filtro_permanente; ?>&reg=<?php echo base64_encode($id_registro); ?>&sec=<?php echo base64_encode($resultado_registros_seccion[$i][0]); ?>" class="btn btn-primary btn-width float-right" title="Añadir pregunta"><span class="fas fa-plus"></span> Añadir pregunta</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php else: ?>
                        <div class="col-md-12 fondo-blanco p-0 mb-2">
                            <p class="alert alert-warning text-left p-1 m-0"><span class="fas fa-exclamation-triangle"></span> No se encontraron registros</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script type="text/javascript">
        function tabla_fixed(){
            var tabla_fixed = document.getElementById("tabla_fixed");
            var elemento = document.getElementById("table-fixed");
            var elemento_1 = document.getElementById("elemento_1");
            alto_ventana=window.outerHeight-elemento_1.clientHeight-100;
            alto_tabla=tabla_fixed.clientHeight+200;

            if (alto_tabla>alto_ventana) {
                alto_elemento=alto_ventana-120;
                elemento.style.height=alto_elemento+"px";
            }
        }
    </script>
</body>
</html>