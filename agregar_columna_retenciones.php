<?php
/**
 * Script para agregar la columna 'retenciones' a la tabla ventas
 * Ejecutar una sola vez
 */

require_once __DIR__ . "/controladores/conexion.php";

try {
    $db = Conexion::conectar();

    // Verificar si la columna ya existe
    $stmt = $db->prepare("SHOW COLUMNS FROM ventas LIKE 'retenciones'");
    $stmt->execute();
    $existe = $stmt->fetch();

    if (!$existe) {
        // Agregar la columna
        $sql = "ALTER TABLE ventas 
                ADD COLUMN retenciones TEXT NULL 
                COMMENT 'Datos de retenciones aplicadas en formato JSON' 
                AFTER descuento";

        $db->exec($sql);
        echo "✓ Columna 'retenciones' agregada exitosamente a la tabla ventas\n";
    } else {
        echo "ℹ La columna 'retenciones' ya existe en la tabla ventas\n";
    }

} catch (PDOException $e) {
    echo "✗ Error al agregar la columna: " . $e->getMessage() . "\n";
}
?>