<footer>
	<div class="footer_contenido">
		<div class="col-md-12 text-center">
			<p>IQ-ICBF | Gestión Integrada de Servicios &copy; Copyright 2020-<?php echo date("Y"); ?> Todos los derechos reservados
			<?php if (isset($_SESSION['usu_nombre_completo'])) { echo " | Bienvenido(a), ".$_SESSION['usu_nombre_completo']; } ?>
			</p>
		</div>
	</div>
</footer>