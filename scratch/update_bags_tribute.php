<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    // 1. Find products with tribute 4 (INC Bolsas)
    $stmt = $db->prepare("SELECT id, descripcion, codigo, tributo_id FROM productos WHERE tributo_id = 4");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($products) . " products with INC Bolsas tax:\n";
    foreach ($products as $p) {
        echo "ID: {$p['id']} | Código: {$p['codigo']} | Desc: {$p['descripcion']}\n";
    }
    
    if (count($products) > 0) {
        // 2. Update them to tribute 5 (IVA Excluido)
        $stmtUp = $db->prepare("UPDATE productos SET tributo_id = 5 WHERE tributo_id = 4");
        $stmtUp->execute();
        echo "\nUpdated " . $stmtUp->rowCount() . " products to IVA Excluido (tributo_id = 5) successfully.\n";
    } else {
        echo "\nNo products needed updating.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
