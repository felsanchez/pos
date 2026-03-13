<?php
require_once 'modelos/ventas.modelo.php';
require_once 'modelos/conexion.php';

$tabla = "ventas";
$limit = 5;

$stmt = Conexion::conectar()->prepare("SELECT id, codigo, numero_factura, estado_dian, estado FROM $tabla ORDER BY id DESC LIMIT $limit");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "LAST $limit SALES:\n";
print_r($results);
?>