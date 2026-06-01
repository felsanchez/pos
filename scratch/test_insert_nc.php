<?php
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();
    
    echo "=== Last inserted credit note ===\n";
    $stmt = $db->prepare("SELECT * FROM notas_credito ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $nc = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($nc);
    
    // Clean it up so we don't dirty the DB
    $stmtDelete = $db->prepare("DELETE FROM notas_credito WHERE id = :id");
    $stmtDelete->execute([':id' => $nc['id']]);
    echo "Cleaned up test record ID: " . $nc['id'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
