<?php
    /**
     * gestion_encuestas_informacion_complementaria_validar_czonal.php
     * Remediación sin cambiar funcionalidad:
     * - SQLi: prepared statement
     * - Validación de entrada (solo ids numéricos)
     * - Salida segura (escape HTML)
     */

    require_once("../config/conexion_db.php");

    // Helper escape (por si no existe en tu proyecto)
    if (!function_exists('e_html')) {
        function e_html($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    // Lee POST de forma segura
    $id_filtro = $_POST['id'] ?? '';

    // Tu campo es un id regional => fuerza a entero (evita SQLi y valores raros)
    $id_filtro_int = (int)$id_filtro;

    $resultado = [];

    // Consulta remediada
    $sql = "SELECT `gercz_id`, `gercz_regional`, `gercz_centro_zonal`, `gercz_registro_fecha`
            FROM `tb_gestion_encuesta_regional_czonal`
            WHERE `gercz_regional`=?
            ORDER BY `gercz_centro_zonal` ASC";

    $stmt = $enlace_db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id_filtro_int);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_NUM);
        $stmt->close();
    } else {
        // Sin romper el flujo: solo devolverá "Seleccione"
        error_log("Prepare failed validar_czonal.php: ".$enlace_db->error);
    }
?>
<option value="">Seleccione</option>
<?php for ($i = 0; $i < count($resultado); $i++): ?>
    <option value="<?php echo e_html($resultado[$i][0]); ?>">
        <?php echo e_html($resultado[$i][2]); ?>
    </option>
<?php endfor; ?>
