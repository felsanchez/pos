<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    echo "=== LAST 3 SALES ===\n";
    $stmt = $db->prepare("SELECT id, codigo, neto, impuesto, total, productos FROM ventas ORDER BY id DESC LIMIT 3");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $sale) {
        echo "ID: {$sale['id']} | Codigo: {$sale['codigo']} | Neto: {$sale['neto']} | Impuesto: {$sale['impuesto']} | Total: {$sale['total']}\n";
        echo "Productos: {$sale['productos']}\n\n";
    }

    echo "=== LAST 3 FACTURAS DIAN ===\n";
    // Check if table facturas_dian or similar exists
    $stmt = $db->prepare("SHOW TABLES LIKE '%factur%' OR SHOW TABLES LIKE '%dian%'");
    $stmt = $db->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $factusTable = "";
    foreach ($tables as $t) {
        if (strpos($t, 'factur') !== false || strpos($t, 'dian') !== false) {
            $factusTable = $t;
            break;
        }
    }
    
    if ($factusTable) {
        echo "Table found: $factusTable\n";
        $stmt = $db->prepare("SELECT * FROM `$factusTable` ORDER BY id DESC LIMIT 3");
        $stmt->execute();
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "No factus/dian table found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
