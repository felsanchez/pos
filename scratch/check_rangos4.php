<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
