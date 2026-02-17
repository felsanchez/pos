<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SHOW TABLES LIKE 'factus%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

print_r($tables);
