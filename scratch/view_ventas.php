<?php
require_once __DIR__ . "/../modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("
    SELECT id, codigo, numero_factura, estado, estado_dian, resolucion_id, fecha
    FROM ventas
    ORDER BY id DESC
    LIMIT 15
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Latest 15 Sales in DB:\n";
echo str_pad("ID", 6) . str_pad("Codigo", 15) . str_pad("Num Factura", 20) . str_pad("Estado", 12) . str_pad("Estado DIAN", 15) . str_pad("Resol ID", 10) . "Fecha\n";
echo str_repeat("-", 85) . "\n";
foreach ($results as $r) {
    echo str_pad($r['id'], 6) . 
         str_pad($r['codigo'] ?? 'NULL', 15) . 
         str_pad($r['numero_factura'] ?? 'NULL', 20) . 
         str_pad($r['estado'] ?? 'NULL', 12) . 
         str_pad($r['estado_dian'] ?? 'NULL', 15) . 
         str_pad($r['resolucion_id'] ?? 'NULL', 10) . 
         $r['fecha'] . "\n";
}

echo "\nAll Rows in consecutivos:\n";
$stmt2 = Conexion::conectar()->prepare("SELECT * FROM consecutivos");
$stmt2->execute();
$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($results2 as $r) {
    print_r($r);
}
