<?php
    //Validación de permisos del usuario para el módulo
    $modulo_plataforma="Encuestas";
    
	require_once("../config/validaciones_seguridad.php");

    /*DEFINICIÓN DE VARIABLES*/
    $id_registro=$_GET['ruta'];

    

    $Url=$id_registro;
    $NombreDoc=$id_registro;
    header("Content-disposition: attachment; filename=".$NombreDoc);
    header("Content-type: MIME");
    header('Cache-Control: max-age=0');
    readfile($Url);

        
?>
