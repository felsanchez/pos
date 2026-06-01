<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    echo "=== Recent sales in ventas ===\n";
    $stmt = $db->prepare("SELECT id, numero_factura, estado_dian, factus_bill_id, total, fecha FROM ventas ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ventas as $v) {
        echo "ID: {$v['id']} | Factura: {$v['numero_factura']} | Estado DIAN: {$v['estado_dian']} | Bill ID: {$v['factus_bill_id']} | Total: {$v['total']} | Fecha: {$v['fecha']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
