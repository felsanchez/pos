<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos");
$stmt->execute();
$rangos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rangos, JSON_PRETTY_PRINT);
?>