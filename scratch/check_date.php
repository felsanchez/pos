<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, orden_compra, fecha FROM ventas WHERE id = 932");
$stmt->execute();
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
