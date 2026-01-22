<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas-Matriz";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");

    /*DEFINICIÓN DE VARIABLES*/

    $titulo_header = "Matriz Encuestas | Diagrama de Flujo";

    $id_seccion_mostrar=$_SESSION['configuracion_encuesta_navegacion'][count($_SESSION['configuracion_encuesta_navegacion'])-1];

    if(isset($_GET["regresar"]) AND $_GET["regresar"]=="on"){
        unset($_SESSION['configuracion_encuesta_navegacion'][count($_SESSION['configuracion_encuesta_navegacion'])-1]);
        header('Location: gestion_encuestas_matriz_configurar_vista.php');
    }

    if(isset($_POST["guardar_seccion"])){
        $id_seccion_segun_respuesta="";
        for ($i=0; $i < count($_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar]); $i++) { 
            $id_pregunta=$_SESSION['configuracion_encuesta_secciones_preguntas'][$id_seccion_mostrar][$i];
            $tipo_pregunta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['tipo'];
            $segun_respuesta=$_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['segun_respuesta'];
            $respuesta_formulario=validar_input($_POST[$id_pregunta]);

            $_SESSION['configuracion_encuesta_detalle'][$id_seccion_mostrar]['preguntas'][$id_pregunta]['respuesta']=$respuesta_formulario;
            if (($tipo_pregunta=="Desplegable" OR $tipo_pregunta=="Casillas" OR $tipo_pregunta=="Varias opciones") AND $segun_respuesta=="Si") {
                $id_seccion_segun_respuesta=$_SESSION['configuracion_encuesta_opciones_destino'][$respuesta_formulario];

            }
        }

        if ($id_seccion_segun_respuesta!="") {
            $_SESSION['configuracion_encuesta_navegacion'][]=$id_seccion_segun_respuesta;
        } else {
            $indice_seccion_siguiente=array_search($id_seccion_mostrar, $_SESSION['configuracion_encuesta_secciones'])+1;

            if ($indice_seccion_siguiente<=count($_SESSION['configuracion_encuesta_secciones'])) {
                $id_seccion_siguiente=$_SESSION['configuracion_encuesta_secciones'][$indice_seccion_siguiente];

                $_SESSION['configuracion_encuesta_navegacion'][]=$id_seccion_siguiente;
                
            }

        }


        header('Location: gestion_encuestas_matriz_configurar_vista.php');

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
    <style type="text/css">
        #container h4 {
            font-size: 12px;
        }
    </style>
    <script src="../Highcharts/code/highcharts.js"></script>
    <script src="../Highcharts/code/modules/sankey.js"></script>
    <script src="../Highcharts/code/modules/organization.js"></script>
    <script src="../Highcharts/code/modules/exporting.js"></script>
    <div class="contenido" style="background-color: #ede7f6;">
        <?php
        // echo $id_seccion_mostrar;
        echo "<pre>";
        // print_r($_SESSION['configuracion_encuesta_navegacion']);
        // print_r($_SESSION['configuracion_encuesta_detalle']);
        // print_r($_SESSION['configuracion_encuesta_secciones']);
        echo "</pre>";
        ?>
        <div class="row justify-content-center">
            <div class="col-md-8" id="container"></div>
        </div>
            
    </div>
    <?php
        include("../footer.php");
        include("../config/configuracion_js.php");
    ?>
    <script type="text/javascript">
        Highcharts.chart('container', {

            chart: {
                height: 1000,
                inverted: true
            },

            title: {
                text: 'Highcharts Org Chart'
            },

            series: [{
                type: 'organization',
                name: 'Sección',
                keys: ['from', 'to'],
                data: [
                    ['Shareholders', 'Board'],
                    ['Board', 'CEO'],
                    ['CEO', 'CTO'],
                    ['CEO', 'CPO'],
                    ['CEO', 'CMO'],
                    ['CEO', 'CSO'],
                    ['CEO', 'HR'],
                    ['CTO', 'Product'],
                    ['CTO', 'Web'],
                    ['CSO', 'Sales'],
                    ['CMO', 'Market']
                ],
                colorByPoint: true,
                // color: '#007ad0',
                dataLabels: {
                    color: 'white'
                },
                borderColor: 'white',
                nodeWidth: 40
            }],
            tooltip: {
                outside: true
            },
            exporting: {
                allowHTML: true,
                sourceWidth: 800,
                sourceHeight: 600
            }

        });
    </script>
</body>
</html>