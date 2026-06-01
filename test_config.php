<?php
require_once 'modelos/conexion.php';
$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config");
$stmt->execute();
echo "CONFIGURACION:\n";
print_r($stmt->fetch(PDO::FETCH_ASSOC));

$stmt2 = Conexion::conectar()->prepare("SELECT r.* FROM factus_rangos r INNER JOIN factus_config c ON r.id_factus = c.rango_numeracion_id WHERE r.estado = 1");
$stmt2->execute();
echo "\nRANGO CONFIGURADO (INNER JOIN):\n";
print_r($stmt2->fetch(PDO::FETCH_ASSOC));
