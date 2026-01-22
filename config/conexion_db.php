<?php
$env_file = '/var/www/icbf/.env';

if (!is_readable($env_file)) {
    error_log("No se puede leer el archivo .env en $env_file");
    die("Error de configuración de la base de datos.");
}

$env = parse_ini_file($env_file);

if (!isset($env['DB_SERVER'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME'])) {
    error_log("Variables de DB faltantes en .env");
    die("Error de configuración de la base de datos.");
}

// Conectar a la base de datos
$enlace_db = new mysqli($env['DB_SERVER'], $env['DB_USER'], $env['DB_PASSWORD'], $env['DB_NAME']);
if ($enlace_db->connect_errno) {
    error_log("Fallo al conectar BD ({$enlace_db->connect_errno}): {$enlace_db->connect_error}");
    die("Error de conexión a la base de datos.");
}
$enlace_db->set_charset("utf8mb4");
?>
