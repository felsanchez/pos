<?php
require_once 'modelos/conexion.php';
$s = Conexion::conectar()->prepare('SELECT * FROM productos_variantes ORDER BY id DESC LIMIT 10');
$s->execute();
print_r($s->fetchAll(PDO::FETCH_ASSOC));
?>
