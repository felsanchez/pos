<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, orden_compra FROM ventas WHERE codigo = 30000 ORDER BY id DESC LIMIT 5");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
