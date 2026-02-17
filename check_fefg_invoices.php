<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "Facturas FEFG en la tabla ventas:\n\n";
$stmt = $db->query("SELECT id, codigo, numero_factura FROM ventas WHERE numero_factura LIKE 'FEFG%' ORDER BY id DESC LIMIT 10");

$count = 0;
while ($v = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$v['id']}, Codigo: {$v['codigo']}, Numero Factura: {$v['numero_factura']}\n";
    $count++;
}

if ($count == 0) {
    echo "No hay facturas FEFG en la tabla ventas\n";
}
