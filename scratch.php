<?php
require 'modelos/conexion.php';
$stmt = Conexion::conectar()->prepare('DESCRIBE movimientos');
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
