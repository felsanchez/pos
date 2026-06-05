<?php
require_once __DIR__ . "/../modelos/conexion.php";

$db = Conexion::conectar();

$stmt = $db->prepare("SHOW CREATE TABLE ventas");
$stmt->execute();
$res = $stmt->fetch();
echo "Schema for 'ventas':\n";
echo $res[1] . "\n";
