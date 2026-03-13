<?php
require_once 'modelos/conexion.php';
$stmt = Conexion::conectar()->prepare("SELECT id, descripcion, precio_venta, tasa_impuesto FROM productos WHERE id = 117");
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($res);
?>