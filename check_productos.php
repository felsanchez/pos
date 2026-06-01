<?php
require 'config.php';
require 'modelos/conexion.php';
$stmt = Conexion::conectar()->query("SHOW COLUMNS FROM productos");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) { echo $r['Field']."\n"; }
