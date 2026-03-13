<?php
require_once 'modelos/ventas.modelo.php';
require_once 'modelos/conexion.php';

$tabla = "ventas";
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, numero_factura, estado_dian, estado FROM $tabla WHERE estado = 'venta' AND estado_dian NOT IN ('enviada', 'aceptada') ORDER BY id DESC LIMIT 10");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('tmp/blocking_sales.txt', print_r($results, true));
echo "RESULTS WRITTEN TO tmp/blocking_sales.txt\n";
?>