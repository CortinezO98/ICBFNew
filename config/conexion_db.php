<?php
declare(strict_types=1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
/*
|--------------------------------------------------------------------------
| Detección de entorno
|--------------------------------------------------------------------------
| En Windows/XAMPP utiliza la configuración local (este equipo de desarrollo).
| En Linux/producción sigue utilizando exactamente el mismo mecanismo de
| /var/www/icbf/.env que ya usa el sistema real — NO se modifica ese
| comportamiento, solo se agrega la rama Windows antes de llegar a él.
*/
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if ($isWindows) {
    // Entorno local de desarrollo: XAMPP en C:\xampp\htdocs\ICBFNew
    // (repo real en C:\Users\JCortinezO\Documents\GitHub\ICBFNew, enlazado por túnel/symlink)
    $dbServer   = '127.0.0.1';
    $dbUser     = 'root';
    $dbPassword = '';
    $dbName     = 'icbf-iqgis';
    $dbPort     = 3307;
} else {
    $envFile = '/var/www/icbf/.env';
    if (!is_readable($envFile)) {
        error_log("No se puede leer el archivo .env en {$envFile}");
        die('Error de configuración de la base de datos.');
    }
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (
        $env === false ||
        !isset(
            $env['DB_SERVER'],
            $env['DB_USER'],
            $env['DB_PASSWORD'],
            $env['DB_NAME']
        )
    ) {
        error_log('Variables de base de datos faltantes en el archivo .env.');
        die('Error de configuración de la base de datos.');
    }
    $dbServer   = $env['DB_SERVER'];
    $dbUser     = $env['DB_USER'];
    $dbPassword = $env['DB_PASSWORD'];
    $dbName     = $env['DB_NAME'];
    $dbPort     = isset($env['DB_PORT'])
        ? (int) $env['DB_PORT']
        : 3306;
}

try {
    $enlace_db = new mysqli(
        $dbServer,
        $dbUser,
        $dbPassword,
        $dbName,
        $dbPort
    );
    $enlace_db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log(
        'Error de conexión a la base de datos: ' .
        $exception->getMessage()
    );
    die('Error de conexión a la base de datos.');
}
