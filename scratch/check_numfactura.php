<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT id, codigo, numero_factura FROM ventas WHERE numero_factura LIKE '%10741%' OR numero_factura LIKE '%10740%'");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
