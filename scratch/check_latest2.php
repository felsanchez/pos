<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, orden_compra, estado, estado_dian, fecha FROM ventas ORDER BY id DESC LIMIT 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
