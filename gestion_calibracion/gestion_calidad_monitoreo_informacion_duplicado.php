<?php 
    //Se realiza la consulta por id y se imprimen los resultados
	$modulo_plataforma="Calibración-Monitoreos";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
	$id=validar_input($_GET['id']);
    if ($id!="") {
    	$consulta_string="SELECT `gcm_id`, `gcm_id_sim` FROM `tb_gestion_calibracion_monitoreo` WHERE `gcm_id_sim`=?";
	    $consulta_registros = $enlace_db->prepare($consulta_string);
	    $consulta_registros->bind_param("s", $id);
	    $consulta_registros->execute();
	    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);

	    if (count($resultado_registros)>0) {
    	    echo "<p class='alert alert-danger p-1 text-center font-size-11'>Por favor valide las siguientes coincidencias:<br>";
		    for ($i=0; $i < count($resultado_registros); $i++) {
 ?>
 				<?php echo $resultado_registros[$i][0]." | ".$resultado_registros[$i][1]."<br>"; ?>
<?php 
 			}
 			echo "</p>";
 		}
    }
?>
