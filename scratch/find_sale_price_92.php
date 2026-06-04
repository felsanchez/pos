<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->query("SELECT id, codigo, total, neto, impuesto, productos FROM ventas");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $sale) {
        if (strpos($sale['productos'], '92.59') !== false || abs(floatval($sale['total']) - 92.59) < 0.05) {
            echo "Sale ID: {$sale['id']} | Código: {$sale['codigo']} | Neto: {$sale['neto']} | Impuesto: {$sale['impuesto']} | Total: {$sale['total']}\n";
            echo "  JSON: {$sale['productos']}\n\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
