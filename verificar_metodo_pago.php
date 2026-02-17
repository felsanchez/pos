<?php
require_once 'modelos/conexion.php';
$db = Conexion::conectar();
$stmt = $db->query("SELECT id, codigo, metodo_pago, forma_pago_dian, metodo_pago_dian_id FROM ventas WHERE codigo = 55");
$venta = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($venta);
?>