<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT id, codigo, neto, impuesto, total, productos FROM ventas ORDER BY id DESC");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $sale) {
        if (strpos($sale['productos'], 'inc8') !== false || strpos($sale['productos'], '"impuesto":"8"') !== false) {
            echo "Sale ID: {$sale['id']} | Código: {$sale['codigo']} | Neto: {$sale['neto']} | Impuesto: {$sale['impuesto']} | Total: {$sale['total']}\n";
            echo "  Productos JSON: {$sale['productos']}\n\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
