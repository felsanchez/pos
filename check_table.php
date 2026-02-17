<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_municipios LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
