<?php
require_once "modelos/conexion.php";
$s = Conexion::conectar()->query('SHOW TABLES');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
