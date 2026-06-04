<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SHOW TRIGGERS LIKE 'gastos'");
$stmt->execute();
$triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($triggers, JSON_PRETTY_PRINT);
?>
