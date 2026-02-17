<?php
// Script SIMPLIFICADO de corrección
require_once "config.php";
require_once "modelos/conexion.php";

echo "<h2>Corrección de Anomalía de Consecutivos (Simple)</h2>";

try {
    $db = Conexion::conectar();

    // 1. Contar anómalos
    echo "<h3>1. Contando registros > 100000...</h3>";
    $stmt = $db->query("SELECT count(*) as total FROM ventas WHERE codigo > 100000");
    $total = $stmt->fetch()['total'];

    echo "Encontrados: $total <br>";

    if ($total > 0) {
        // 2. Eliminar
        echo "<h3>2. Eliminando...</h3>";
        $del = $db->exec("DELETE FROM ventas WHERE codigo > 100000");
        echo "✅ Eliminados: $del <br>";
    } else {
        echo "Nada que eliminar.<br>";
    }

    // 3. Ver Máximo Actual
    $stmtMax = $db->query("SELECT MAX(codigo) as maximo FROM ventas");
    $max = $stmtMax->fetch()['maximo'];
    echo "<h3>3. Nuevo Máximo: $max</h3>";
    echo "Próximo consecutivo será: " . ($max + 1);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>