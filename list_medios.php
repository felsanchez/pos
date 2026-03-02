<?php
require 'c:/xampp/htdocs/pos/modelos/conexion.php';
$rows = Conexion::conectar()->query('SELECT id, codigo, nombre FROM factus_medios_pago ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['codigo'] . ' | ' . $r['nombre'] . "\n";
}
?>