<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT id, codigo, numero_factura, resolucion_id, total, fecha FROM ventas WHERE codigo < 900000000 ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($sales) {
        foreach ($sales as $sale) {
            echo "ID: {$sale['id']} | Codigo: {$sale['codigo']} | NumFactura: " . ($sale['numero_factura'] ?? 'NULL') . " | ResolucionID: " . ($sale['resolucion_id'] ?? 'NULL') . " | Total: {$sale['total']} | Fecha: {$sale['fecha']}\n";
        }
    } else {
        echo "No sales found with codigo < 900000000.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
