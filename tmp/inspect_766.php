<?php
require_once 'modelos/ventas.modelo.php';
require_once 'modelos/conexion.php';

$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE id = 766");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "SALE 766 DETAILS:\n";
print_r($result);
?>