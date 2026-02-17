<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "<h2>Eliminando columna responsabilidad_iva de tabla clientes</h2>";

try {
    // Verificar si la columna existe
    $stmt = $db->query("SHOW COLUMNS FROM clientes LIKE 'responsabilidad_iva'");
    $exists = $stmt->fetch();

    if ($exists) {
        // Eliminar la columna
        $sql = "ALTER TABLE `clientes` DROP COLUMN `responsabilidad_iva`";
        $db->exec($sql);
        echo "<p style='color: green;'>✓ Columna 'responsabilidad_iva' eliminada exitosamente.</p>";
        echo "<p><strong>Razón:</strong> Este campo era redundante. La responsabilidad de IVA ya está determinada por el campo 'responsabilidades_fiscales':</p>";
        echo "<ul>";
        echo "<li>Si el cliente tiene <strong>R-99-PN</strong> o <strong>ZY</strong> → No responsable de IVA</li>";
        echo "<li>Si el cliente tiene <strong>O-13, O-15, O-23, O-47</strong> → Responsable de IVA</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>✓ La columna 'responsabilidad_iva' no existe en la tabla clientes (ya fue eliminada o nunca se creó).</p>";
    }

    echo "<p><strong>¡Listo!</strong> El formulario de clientes ahora solo usa el campo 'Responsabilidades Fiscales' que es el oficial de la DIAN.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>