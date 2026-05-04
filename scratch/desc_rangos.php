<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("DESCRIBE factus_rangos");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
