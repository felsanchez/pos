<?php
require_once 'modelos/conexion.php';
$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE estado = 1 AND documento = 'Factura de Venta' ORDER BY id DESC LIMIT 1");
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
