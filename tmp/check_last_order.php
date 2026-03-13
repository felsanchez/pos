<?php
require_once 'modelos/conexion.php';
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, productos, impuesto, neto, total FROM ventas ORDER BY id DESC LIMIT 1");
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($res);
?>