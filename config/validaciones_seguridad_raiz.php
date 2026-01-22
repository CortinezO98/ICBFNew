<?php
	session_start();
    error_reporting(0);
    
	//validaciones de seguridad
	function validar_input($variable) {
	  $variable = trim($variable);
	  $variable = strip_tags($variable);
	  $variable = stripslashes($variable);
	  $variable = htmlspecialchars($variable);
	  $variable = str_replace("'", "", $variable);
	  return $variable;
	}

	function validar_output($variable) {
	  $variable = trim($variable);
	  $variable = strip_tags($variable);
	  $variable = stripslashes($variable);
	  $variable = htmlspecialchars($variable);
	  $variable = str_replace("'", "", $variable);
	  $variable = trim($variable);
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

    require_once("validaciones_funciones.php");
?>