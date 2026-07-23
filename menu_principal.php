<div class="sidebar">
    <h2 class="m-0">Menú Principal</h2>
    <div class="menu_configuracion_lateral"><a href="../logout.php" class="menu_conf_lateral" id="tour-6"><span class="fas fa-power-off"></span></a></div>
    <ul>
        <li class="submenu"><a href="../contenido.php"><div class="opcion_icon"><span class="fas fa-chart-pie"></span></div><p class="texto_menu">Dashboard</p></a></li>
        
        <?php if (isset($_SESSION['modulos_acceso_permisos']['Encuestas']) AND $_SESSION['modulos_acceso_permisos']['Encuestas']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-check-double"></span></div><p class="texto_menu">Gestión Encuestas<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Encuestas']) AND $_SESSION['modulos_acceso_permisos']['Encuestas']!=""): ?>
            <ul>
                <li><a href="../gestion_encuestas/gestion_encuestas.php?pagina=1&id=null&est=Pendientes"><div class="opcion_icon"><span class="fas fa-tasks"></span></div><p class="texto_menu">Encuestas</p></a></li>
            </ul>
            <?php endif; ?>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Encuestas-Matriz']) AND $_SESSION['modulos_acceso_permisos']['Encuestas-Matriz']!=""): ?>
            <ul>
                <li><a href="../gestion_encuestas/gestion_encuestas_matriz.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-cogs"></span></div><p class="texto_menu">Matriz Encuestas</p></a></li>
            </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Alertas']) AND $_SESSION['modulos_acceso_permisos']['Gestión Alertas']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-exclamation-triangle"></span></div><p class="texto_menu">Gestión Alertas<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Alertas']) AND $_SESSION['modulos_acceso_permisos']['Encuestas']!=""): ?>
            <ul>
                <li><a href="../gestion_alertas/gestion_alertas.php?pagina=1&id=null&est=Pendientes"><div class="opcion_icon"><span class="fas fa-exclamation-triangle"></span></div><p class="texto_menu">Alertas</p></a></li>
            </ul>
            <?php endif; ?>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Alertas-Configuración']) AND $_SESSION['modulos_acceso_permisos']['Encuestas']!=""): ?>
            <ul>
                <li><a href="../gestion_alertas/gestion_alertas_regional.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-cogs"></span></div><p class="texto_menu">Configuración</p></a></li>
            </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['modulos_acceso_permisos']['Calidad']) AND $_SESSION['modulos_acceso_permisos']['Calidad']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-user-check"></span></div><p class="texto_menu">Calidad<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Calidad-Monitoreos']) AND $_SESSION['modulos_acceso_permisos']['Calidad-Monitoreos']!=""): ?>
            <ul>
                <li><a href="../gestion_calidad/gestion_calidad_monitoreo.php?pagina=1&id=null&bandeja=<?php echo base64_encode('Mes Actual'); ?>"><div class="opcion_icon"><span class="fas fa-clipboard-list"></span></div><p class="texto_menu">Monitoreos</p></a></li>
            </ul>
            <?php endif; ?>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Calidad-Matriz Calidad']) AND $_SESSION['modulos_acceso_permisos']['Calidad-Matriz Calidad']!=""): ?>
            <ul>
                <li><a href="../gestion_calidad/gestion_calidad_matriz.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-file-contract"></span></div><p class="texto_menu">Matriz de Calidad</p></a></li>
            </ul>
            <?php endif; ?>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Calidad-Calculadora Muestral']) AND $_SESSION['modulos_acceso_permisos']['Calidad-Calculadora Muestral']!=""): ?>
            <ul>
                <li><a href="../gestion_calidad/gestion_cmuestral.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-calculator"></span></div><p class="texto_menu">Calculadora Muestral</p></a></li>
            </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['modulos_acceso_permisos']['Calibración-Monitoreos']) AND $_SESSION['modulos_acceso_permisos']['Calibración-Monitoreos']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-check-double"></span></div><p class="texto_menu">Calibración<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Calibración-Monitoreos']) AND $_SESSION['modulos_acceso_permisos']['Calibración-Monitoreos']!=""): ?>
            <ul>
                <li><a href="../gestion_calibracion/gestion_calidad_monitoreo.php?pagina=1&id=null&bandeja=<?php echo base64_encode('Mes Actual'); ?>"><div class="opcion_icon"><span class="fas fa-clipboard-list"></span></div><p class="texto_menu">Monitoreos</p></a></li>
            </ul>
            <?php endif; ?>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Calibración-Matriz Calidad']) AND $_SESSION['modulos_acceso_permisos']['Calibración-Matriz Calidad']!=""): ?>
            <ul>
                <li><a href="../gestion_calibracion/gestion_calidad_matriz.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-file-contract"></span></div><p class="texto_menu">Matriz de Calidad</p></a></li>
            </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['modulos_acceso_permisos']['Auditoría-Monitoreos']) AND $_SESSION['modulos_acceso_permisos']['Auditoría-Monitoreos']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-user-check"></span></div><p class="texto_menu">Auditoría<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Auditoría-Monitoreos']) AND $_SESSION['modulos_acceso_permisos']['Auditoría-Monitoreos']!=""): ?>
            <ul>
                <li><a href="../gestion_auditoria/gestion_calidad_monitoreo.php?pagina=1&id=null&bandeja=<?php echo base64_encode('Mes Actual'); ?>"><div class="opcion_icon"><span class="fas fa-clipboard-list"></span></div><p class="texto_menu">Monitoreos</p></a></li>
            </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['modulos_acceso_permisos']['Coaching']) AND $_SESSION['modulos_acceso_permisos']['Coaching']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-graduation-cap"></span></div><p class="texto_menu">Coaching<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <ul>
                <li><a href="../gestion_coaching/gestion_coaching.php?pagina=1&id=null&est=Pendientes"><div class="opcion_icon"><span class="fas fa-inbox"></span></div><p class="texto_menu">Mis paquetes</p></a></li>
                <li><a href="../gestion_coaching/gestion_coaching_acompanamientos.php"><div class="opcion_icon"><span class="fas fa-clipboard-check"></span></div><p class="texto_menu">Acompañamientos</p></a></li>
                <?php if (in_array($_SESSION['modulos_acceso_permisos']['Coaching'], ['Supervisor','Gestor','Administrador'], true)): ?>
                <li><a href="../gestion_coaching/gestion_coaching_acompanamiento_crear.php"><div class="opcion_icon"><span class="fas fa-user-plus"></span></div><p class="texto_menu">Nuevo acompañamiento</p></a></li>
                <?php endif; ?>
            </ul>
            <?php if (isset($_SESSION['modulos_acceso_permisos']['Coaching-Reportes']) AND $_SESSION['modulos_acceso_permisos']['Coaching-Reportes']!=""): ?>
            <ul>
                <li><a href="../gestion_coaching/gestion_coaching_estadisticas.php"><div class="opcion_icon"><span class="fas fa-chart-pie"></span></div><p class="texto_menu">Estadísticas</p></a></li>
                <li><a href="../gestion_coaching/gestion_coaching_reporte.php"><div class="opcion_icon"><span class="fas fa-file-alt"></span></div><p class="texto_menu">Reporte</p></a></li>
            </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-calendar-alt"></span></div><p class="texto_menu">Gestión Turnos<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <ul>
                <li><a href="../gestion_turnos/control_turnos.php?fechainicio=<?php echo base64_encode(date('Y-m-d'));?>&operacion=<?php echo base64_encode('Todas'); ?>&id=null"><div class="opcion_icon"><span class="fas fa-user-clock"></span></div><p class="texto_menu">Turno Realizado</p></a></li>
                
                <?php if (isset($_SESSION['modulos_acceso_permisos']['Gestión Turnos-Malla']) AND $_SESSION['modulos_acceso_permisos']['Gestión Turnos-Malla']!=""): ?>
                    <li><a href="../gestion_turnos/malla_turnos.php?fechainicio=<?php echo base64_encode(date('Y')."-W".date('W'));?>&operacion=<?php echo base64_encode('Todas'); ?>&id=null"><div class="opcion_icon"><span class="fas fa-calendar-alt"></span></div><p class="texto_menu">Malla Turnos</p></a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['modulos_acceso_permisos']['Administrador']) AND $_SESSION['modulos_acceso_permisos']['Administrador']!=""): ?>
        <li class="submenu"><a href="#"><div class="opcion_icon"><span class="fas fa-cog"></span></div><p class="texto_menu">Administrador<span class="fas fa-chevron-down submenu_icon"></span></p></a>
            <ul>
                <li><a href="../administrador/gestion_usuarios.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-users"></span></div><p class="texto_menu">Usuarios</p></a></li>
                <li><a href="../administrador/gestion_campanias.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-bullhorn"></span></div><p class="texto_menu">Campañas</p></a></li>
                <li><a href="../administrador/gestion_ubicaciones.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-map-marker-alt"></span></div><p class="texto_menu">Ubicaciones</p></a></li>
                <li><a href="../administrador/configuracion_log_eventos.php?pagina=1&id=null"><div class="opcion_icon"><span class="fas fa-history"></span></div><p class="texto_menu">Log de Eventos</p></a></li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</div>



