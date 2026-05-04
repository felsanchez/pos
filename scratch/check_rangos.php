<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE activo = 1 LIMIT 1");
$stmt->execute();
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
?>
