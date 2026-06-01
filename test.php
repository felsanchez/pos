<?php
require 'c:/xampp/htdocs/pos/modelos/conexion.php';
$db = Conexion::conectar();
$stmt = $db->query('SHOW COLUMNS FROM productos_variantes');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
