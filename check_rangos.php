<?php
require 'modelos/conexion.php';
$stmt = Conexion::conectar()->query('SELECT id, id_factus, prefijo, documento, estado FROM factus_rangos ORDER BY id');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['id_factus'] . ' | ' . $r['prefijo'] . ' | ' . $r['documento'] . ' | estado:' . $r['estado'] . "\n";
}
