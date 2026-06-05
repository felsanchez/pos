<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    // IDs de borradores antiguos y fallidos
    $ids = [46, 48, 49, 50, 51, 54, 57, 58, 59, 60, 61];
    
    echo "=== Deleting old failed FEs (IDs: " . implode(", ", $ids) . ") ===\n";
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("DELETE FROM ventas WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    echo "Deleted " . $stmt->rowCount() . " rows.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
