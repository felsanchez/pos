<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "<h2>Agregando columna responsabilidad_iva a tabla clientes</h2>";

try {
    // Verificar si la columna ya existe
    $stmt = $db->query("SHOW COLUMNS FROM clientes LIKE 'responsabilidad_iva'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<p style='color: orange;'>✓ La columna 'responsabilidad_iva' ya existe en la tabla clientes.</p>";
    } else {
        // Agregar la columna
        $sql = "ALTER TABLE `clientes` 
                ADD COLUMN `responsabilidad_iva` VARCHAR(20) DEFAULT 'no_responsable' 
                COMMENT 'Responsabilidad tributaria IVA: responsable o no_responsable'
                AFTER `responsabilidades_fiscales`";

        $db->exec($sql);
        echo "<p style='color: green;'>✓ Columna 'responsabilidad_iva' agregada exitosamente.</p>";
    }

    // Verificar la estructura final
    echo "<h3>Estructura de la columna:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM clientes LIKE 'responsabilidad_iva'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($column) {
        echo "<pre>";
        print_r($column);
        echo "</pre>";
    }

    echo "<p><strong>¡Listo!</strong> Ahora puedes usar el campo 'Responsabilidad Tributaria (IVA)' en el formulario de clientes.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>