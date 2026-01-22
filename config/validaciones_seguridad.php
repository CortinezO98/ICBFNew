<?php
	session_start();
    error_reporting(0);
    //Si sesion esta iniciada se redirige al contenido, sino muestra index de logueo//
    if(!isset($_SESSION["usu_id"]) OR $_SESSION["usu_id"]==null OR $_SESSION["usu_id"]==""){
    	header("Location:../index.php");
    } else {
        if (isset($_SESSION['modulos_acceso_permisos'][$modulo_plataforma]) AND $_SESSION['modulos_acceso_permisos'][$modulo_plataforma]!="") {
        	$perfil_modulo=$_SESSION['modulos_acceso_permisos'][$modulo_plataforma];
        } else {
        	header("Location:../permiso_denegado.php");
        }
    }

	//validaciones de seguridad
	function validar_input($variable) {
	  $variable = trim($variable);
	  $variable = strip_tags($variable);
	  $variable = stripslashes($variable);
	  $variable = htmlspecialchars($variable);
	  $variable = str_replace("'", "", $variable);
      $variable = preg_replace('/\s+/', ' ', $variable);
      $variable = trim($variable);
	  return $variable;
	}

	function validar_output($variable) {
	  $variable = trim($variable);
	  $variable = strip_tags($variable);
	  $variable = stripslashes($variable);
	  $variable = htmlspecialchars($variable);
	  $variable = str_replace("'", "", $variable);
	  return $variable;
	}

	function comprobarSentencia ($valor) {
        preg_match_all('/(\S[^:]+): (\d+)/', $valor, $matches); 
        $array_info = array_combine ($matches[1], $matches[2]);

        if ($array_info['Rows matched']==1 AND $array_info['Warnings']==0) {
            return true;
        } else {
            return false;
        }
    }

    function includeFileContent($fileName) {
        ob_start();
        ob_implicit_flush(false);
        include($fileName);
        return ob_get_clean();
    }

    define('URL', 'https://portal-calidad.iqdigital.com.co/');

    require_once("validaciones_funciones.php");
?>