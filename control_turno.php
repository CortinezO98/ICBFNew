<?php
    //validacion de break y almuerzos cerrados en caso de haber inciado, para poder mostrar cierre de turno
    if ($_SESSION['session_actividad_inicio']!="" AND $_SESSION['session_actividad_fin']=="") {
        $control_actividad_activa=1;
    } else {
        $control_actividad_activa=0;
    }

    if ($_SESSION['session_turno_inicio']!="") {
        $control_turno_iniciado=1;
    } else {
        $control_turno_iniciado=0;
    }
?>
<div class="menu_control_turno">
	<ul>
		<!-- Duración turno -->
		<?php
            //calculo de duracion turno
            if ($control_turno_iniciado) {
        ?>
            <li>
            	<a class="resultado_turno">
            		<span class="fas fa-user-clock icon_turno" style="float: left; color: #FFF; padding-top: 3px;"></span>
					<div id='cronometro_turno' style="float: left; color: #FFF;"></div>
            	</a>
           	</li>
        <?php
            }
        ?>

		<!--boton incio de turno -->
		<?php if (!$control_turno_iniciado): ?>
		  <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('inicio'); ?>&tipo=<?php echo base64_encode('turno'); ?>" class="menu_turno"><span class="fas fa-play icon_turno"></span>Turno</a></li>
		<?php endif; ?>
        <!--boton fin de turno -->
        <?php if ($control_turno_iniciado AND !$control_actividad_activa): ?>
            <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('cierre'); ?>&tipo=<?php echo base64_encode('turno'); ?>" class="menu_turno"><span class="fas fa-stop icon_turno"></span>Turno</a></li>
        <?php endif; ?>

        <!-- Duración ACTIVIDAD -->
        <?php
            //calculo de duracion actividad
            if ($control_turno_iniciado AND $control_actividad_activa) {
                if ($_SESSION['session_actividad_tipo']=='break') {
                    $icono='fa-coffee';
                } elseif ($_SESSION['session_actividad_tipo']=='almuerzo') {
                    $icono='fa-utensils';
                } elseif ($_SESSION['session_actividad_tipo']=='pausaactiva') {
                    $icono='fa-walking';
                } elseif ($_SESSION['session_actividad_tipo']=='capacitacion') {
                    $icono='fa-chalkboard-teacher';
                } elseif ($_SESSION['session_actividad_tipo']=='retroalimentacion') {
                    $icono='fa-retweet';
                }
        ?>
            <li>
                <a class="resultado_turno">
                    <span class="fas <?php echo $icono; ?> icon_turno" style="float: left; color: #FFF; padding-top: 3px;"></span>
                    <div id='cronometro_actividad' style="float: left; color: #FFF;"></div>
                </a>
            </li>
        <?php
            }
        ?>

        <!--botones de break -->
        <?php if ($control_turno_iniciado AND !$control_actividad_activa): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('inicio'); ?>&tipo=<?php echo base64_encode('break'); ?>" class="menu_break"><span class="fas fa-play icon_turno"></span>Break</a></li> -->
        <?php endif; ?>
        <?php if ($control_turno_iniciado AND $control_actividad_activa AND $_SESSION['session_actividad_tipo']=="break"): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('cierre'); ?>&tipo=<?php echo base64_encode('break'); ?>" class="menu_break"><span class="fas fa-stop icon_turno"></span>Break</a></li> -->
        <?php endif; ?>
        
        <!--botones de almuerzo -->
        <?php if ($control_turno_iniciado AND !$control_actividad_activa): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('inicio'); ?>&tipo=<?php echo base64_encode('almuerzo'); ?>" class="menu_almuerzo"><span class="fas fa-play icon_turno"></span>Almuerzo</a></li> -->
        <?php endif; ?>
        <?php if ($control_turno_iniciado AND $control_actividad_activa AND $_SESSION['session_actividad_tipo']=="almuerzo"): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('cierre'); ?>&tipo=<?php echo base64_encode('almuerzo'); ?>" class="menu_almuerzo"><span class="fas fa-stop icon_turno"></span>Almuerzo</a></li> -->
        <?php endif; ?>

        <!--botones de PAUSA ACTIVA -->
        <?php if ($control_turno_iniciado AND !$control_actividad_activa): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('inicio'); ?>&tipo=<?php echo base64_encode('pausaactiva'); ?>" class="menu_pausa"><span class="fas fa-play icon_turno"></span>Pausa Activa</a></li> -->
        <?php endif; ?>
        <?php if ($control_turno_iniciado AND $control_actividad_activa AND $_SESSION['session_actividad_tipo']=="pausaactiva"): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('cierre'); ?>&tipo=<?php echo base64_encode('pausaactiva'); ?>" class="menu_pausa"><span class="fas fa-stop icon_turno"></span>Pausa Activa</a></li> -->
        <?php endif; ?>

        <!--botones de CAPACITACIÓN -->
        <?php if ($control_turno_iniciado AND !$control_actividad_activa): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('inicio'); ?>&tipo=<?php echo base64_encode('capacitacion'); ?>" class="menu_capacitacion"><span class="fas fa-play icon_turno"></span>Capacitación</a></li> -->
        <?php endif; ?>
        <?php if ($control_turno_iniciado AND $control_actividad_activa AND $_SESSION['session_actividad_tipo']=="capacitacion"): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('cierre'); ?>&tipo=<?php echo base64_encode('capacitacion'); ?>" class="menu_capacitacion"><span class="fas fa-stop icon_turno"></span>Capacitación</a></li> -->
        <?php endif; ?>

        <!--botones de RETROALIMENTACIÓN -->
        <?php if ($control_turno_iniciado AND !$control_actividad_activa): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('inicio'); ?>&tipo=<?php echo base64_encode('retroalimentacion'); ?>" class="menu_retro"><span class="fas fa-play icon_turno"></span>Retroalimentación</a></li> -->
        <?php endif; ?>
        <?php if ($control_turno_iniciado AND $control_actividad_activa AND $_SESSION['session_actividad_tipo']=="retroalimentacion"): ?>
            <!-- <li><a href="control_turno_procesar.php?accion=<?php echo base64_encode('cierre'); ?>&tipo=<?php echo base64_encode('retroalimentacion'); ?>" class="menu_retro"><span class="fas fa-stop icon_turno"></span>Retroalimentación</a></li> -->
        <?php endif; ?>

        <!--botones observaciones -->
        <?php if ($_SESSION['session_observaciones_inicio_turno']=="" AND $control_turno_iniciado): ?>
            <li><a href="control_turno_observacion.php" class="menu_observacion"><span class="fas fa-exclamation-triangle icon_turno"></span>Observaciones</a></li>
        <?php endif; ?>


	</ul>
</div>
<script>
    //fecha y hora actual obtenida del servidor
    var actual_anio=<?php echo date('Y',strtotime($_SESSION['session_turno_hora_actual']));?>;
    var actual_mes=<?php echo date('m',strtotime($_SESSION['session_turno_hora_actual']));?>;
    var actual_dia=<?php echo date('d',strtotime($_SESSION['session_turno_hora_actual']));?>;
    var actual_hora=<?php echo date('H',strtotime($_SESSION['session_turno_hora_actual']));?>;
    var actual_minuto=<?php echo date('i',strtotime($_SESSION['session_turno_hora_actual']));?>;
    var actual_segundo=<?php echo date('s',strtotime($_SESSION['session_turno_hora_actual']));?>;

    function incremento_hora_actual(){
        actual_segundo++;
        
        if (actual_segundo==60) {
            actual_minuto++;
            actual_segundo=0;
        }
        if (actual_minuto==60) {
            actual_hora++;
            actual_minuto=0;
        }
        //Indicamos que se ejecute esta función nuevamente dentro de 1 segundo
        timeout=setTimeout("incremento_hora_actual()",1000);
    }

    function cronometro_turno(){
        //fecha y hora de turno
        var anio=<?php echo date('Y',strtotime($_SESSION['session_turno_inicio']));?>;
        var mes=<?php echo date('m',strtotime($_SESSION['session_turno_inicio']));?>;
        var dia=<?php echo date('d',strtotime($_SESSION['session_turno_inicio']));?>;
        var hora=<?php echo date('H',strtotime($_SESSION['session_turno_inicio']));?>;
        var minuto=<?php echo date('i',strtotime($_SESSION['session_turno_inicio']));?>;
        var segundo=<?php echo date('s',strtotime($_SESSION['session_turno_inicio']));?>;
        // obtenemos la fecha actual
        var actual = new Date(actual_anio,actual_mes,actual_dia,actual_hora,actual_minuto,actual_segundo);

        //Obtenemos la fecha de inicio
        inicio_turno=new Date(anio,mes,dia,hora,minuto,segundo);
        //Obtenemos la diferencia entre la fecha actual y la de inicio
        var diff=new Date(actual-inicio_turno);
        //Mostramos la diferencia entre la fecha actual y la inicial
        // alert(result);
        var result=""+LeadingZero(diff.getUTCHours())+":"+LeadingZero(diff.getUTCMinutes())+":"+LeadingZero(diff.getUTCSeconds());
        document.getElementById('cronometro_turno').innerHTML = result;
        //Indicamos que se ejecute esta función nuevamente dentro de 1 segundo
        timeout_turno=setTimeout("cronometro_turno()",1000);
    }

    function cronometro_actividad(){
        var anio=<?php echo date('Y',strtotime($_SESSION['session_actividad_inicio']));?>;
        var mes=<?php echo date('m',strtotime($_SESSION['session_actividad_inicio']));?>;
        var dia=<?php echo date('d',strtotime($_SESSION['session_actividad_inicio']));?>;
        var hora=<?php echo date('H',strtotime($_SESSION['session_actividad_inicio']));?>;
        var minuto=<?php echo date('i',strtotime($_SESSION['session_actividad_inicio']));?>;
        var segundo=<?php echo date('s',strtotime($_SESSION['session_actividad_inicio']));?>;
        // obtenemos la fecha actual
        var actual = new Date(actual_anio,actual_mes,actual_dia,actual_hora,actual_minuto,actual_segundo);
        //Obtenemos la fecha de inicio
        inicio=new Date(anio,mes,dia,hora,minuto,segundo);
        //Obtenemos la diferencia entre la fecha actual y la de inicio
        var diff=new Date(actual-inicio);
        //Mostramos la diferencia entre la fecha actual y la inicial
        var result=""+LeadingZero(diff.getUTCHours())+":"+LeadingZero(diff.getUTCMinutes())+":"+LeadingZero(diff.getUTCSeconds());
        document.getElementById('cronometro_actividad').innerHTML = result;
        //Indicamos que se ejecute esta función nuevamente dentro de 1 segundo
        timeout=setTimeout("cronometro_actividad()",1000);
    }

    /* Funcion que pone un 0 delante de un valor si es necesario */
    function LeadingZero(Time) {
        return (Time < 10) ? "0" + Time : + Time;
    }
</script>