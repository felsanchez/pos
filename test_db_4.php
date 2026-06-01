<?php
require_once "c:/xampp/htdocs/pos/modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM bodegas");
$bodegas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Bodegas:\n";
print_r($bodegas);

$stmt = $db->query("SELECT p.id, p.descripcion, p.id_proveedor, p.eliminado, pb.id_bodega, pb.estado, pb.stock FROM productos p LEFT JOIN productos_bodegas pb ON p.id = pb.id_producto WHERE p.eliminado = 0");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nProductos activos:\n";
print_r($productos);

$stmt = $db->query("SELECT p.id, p.descripcion, p.id_proveedor, p.eliminado, pb.id_bodega, pb.estado, pb.stock FROM productos p LEFT JOIN productos_bodegas pb ON p.id = pb.id_producto");
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nTodos los productos:\n";
print_r($todos);
