<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("DESCRIBE notas_ajuste_ds");
$stmt->execute();
$schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($schema, JSON_PRETTY_PRINT);
