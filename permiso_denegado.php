<?php
	require_once("config/validaciones_seguridad_raiz.php");
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
        header("Location:index.php");
    }
    require_once("config/conexion_db.php");

    $titulo_header = "Permiso Denegado";
?>
<!DOCTYPE html>
<html lang="ES">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie-edge">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css?v=1">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=1">
    <link href="fonts/css/all.css?v=1" rel="stylesheet">
    <!-- favicon link-->
    <link rel="shortcut icon" type="image/icon" href="images/favicon.ico?v=1"/>
    <title>IQ-ICBF | Gestión Integrada de Servicios</title>
</head>
<body>
    <?php
        $menu_principal=includeFileContent("menu_principal.php");
        echo str_replace('../', '', $menu_principal);
        include("menu_header_raiz.php");
    ?>
    <div class="contenido" style="background-color: #2E2E2E;">
        <div style="width: 100%; color: #FFFFFF;">
            <center><img src="images/error_500.png" style="width: 30%;"></center>
            <div class="alert alert-danger">El usuario no tiene permisos suficientes para acceder a este módulo de la plataforma, por favor verifique con el administrador!</div>
        </div>
    </div>
    <?php
        include("footer.php");
        include("config/configuracion_js_raiz.php");
    ?>
</body>
</html>