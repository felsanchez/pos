<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE codigo = 10739");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
