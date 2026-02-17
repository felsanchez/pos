<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$q = $db->query("DESCRIBE factus_config");
$cols = $q->fetchAll(PDO::FETCH_COLUMN);
print_r($cols);
