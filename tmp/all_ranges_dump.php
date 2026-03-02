<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->query("SELECT * FROM factus_rangos");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents("tmp/all_ranges.json", json_encode($rows, JSON_PRETTY_PRINT));
?>