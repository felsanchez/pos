<?php
require_once "modelos/conexion.php";
$s = Conexion::conectar()->query('DESCRIBE ventas');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
