<?php 
    //Se realiza la consulta por id y se imprimen los resultados
	$modulo_plataforma="Gestión Turnos-Malla";

	require_once("../config/validaciones_seguridad.php");
    require_once("../config/conexion_db.php");
	$usuario=validar_input($_GET['usuario']);
	$fecha_inicio=validar_input($_GET['fecha_inicio']);
	$fecha_fin=validar_input($_GET['fecha_fin']).' 23:59:59';
?>

<?php if ($usuario!="" AND $fecha_inicio!="" AND $fecha_fin!=""): ?>
	<?php
    	$consulta_string="SELECT `cotm_id`, `cotm_usuario`, `cotm_tipo`, `cotm_inicio`, `cotm_fin`, `cotm_duracion`, `cotm_jornada`, `cotm_observaciones_inicio`, `cotm_observaciones_fin`, `cotm_registro_fecha` FROM `tb_control_turno_malla` WHERE `cotm_usuario`=? AND `cotm_inicio`>=? AND `cotm_fin`<=? ORDER BY `cotm_usuario`, `cotm_inicio`";
	    $consulta_registros = $enlace_db->prepare($consulta_string);
	    $consulta_registros->bind_param("sss", $usuario, $fecha_inicio, $fecha_fin);
	    $consulta_registros->execute();
	    $resultado_registros = $consulta_registros->get_result()->fetch_all(MYSQLI_NUM);
	?>
    <?php if (count($resultado_registros)>0): ?>
	    <p class='alert alert-danger p-1 text-center font-size-11'>Se encontraron lo siguientes turnos programados, por favor valide antes de continuar:<br>
	    <?php for ($i=0; $i < count($resultado_registros); $i++): ?>
	    	<?php if (isset($array_convenciones[$resultado_registros[$i][2]])): ?>
				<?php echo $array_convenciones[$resultado_registros[$i][2]]." | ".$resultado_registros[$i][3]." A ".$resultado_registros[$i][4]."<br>"; ?>
			<?php else: ?>
				<?php echo "Turno | ".$resultado_registros[$i][3]." A ".$resultado_registros[$i][4]."<br>"; ?>
			<?php endif; ?>
		<?php endfor; ?>
		</p>
	<?php endif; ?>
<?php endif; ?>