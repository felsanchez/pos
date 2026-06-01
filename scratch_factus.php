<?php
require 'modelos/conexion.php'; 
$stmt = Conexion::conectar()->prepare('SELECT * FROM factus_config'); 
$stmt->execute(); 
echo "CONFIG:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = Conexion::conectar()->prepare('SELECT * FROM factus_rangos'); 
$stmt->execute(); 
echo "\nRANGOS:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
