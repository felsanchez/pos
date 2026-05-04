<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE id = 935");
$stmt->execute();
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
?>
