<?php
require_once 'modelos/ventas.modelo.php';
require_once 'modelos/conexion.php';

$tabla = "ventas";
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, numero_factura, estado_dian, estado FROM $tabla ORDER BY id DESC LIMIT 10");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('tmp/last_sales_clean.txt', print_r($results, true));
echo "CLEAN RESULTS WRITTEN TO tmp/last_sales_clean.txt\n";
?>