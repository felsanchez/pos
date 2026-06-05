<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT id, codigo, numero_factura, resolucion_id, estado_dian, total, fecha FROM ventas WHERE codigo >= 990000300 ORDER BY codigo ASC");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $sale) {
        echo "ID: {$sale['id']} | Codigo: {$sale['codigo']} | NumFactura: " . ($sale['numero_factura'] ?? 'NULL') . " | ResolucionID: " . ($sale['resolucion_id'] ?? 'NULL') . " | EstadoDIAN: " . ($sale['estado_dian'] ?? 'NULL') . " | Total: {$sale['total']} | Fecha: {$sale['fecha']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
