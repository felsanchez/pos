<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT id, numero_factura, estado_dian, factus_bill_id FROM ventas WHERE estado_dian IN ('enviada', 'aceptada') ORDER BY id DESC LIMIT 10");
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($ventas);
?>