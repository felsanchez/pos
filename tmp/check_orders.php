<?php
require_once 'modelos/conexion.php';
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT id, codigo, numero_factura, estado, estado_dian FROM ventas WHERE codigo LIKE '%196%' OR numero_factura LIKE '%196%' OR id > 780 ORDER BY id DESC LIMIT 20");
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
?>