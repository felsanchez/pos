<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT id_factus, documento, prefijo, estado FROM factus_rangos");
$stmt->execute();
$count = 0;
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['id_factus'] . " | " . $r['documento'] . " | " . $r['prefijo'] . " | Status: " . $r['estado'] . "\n";
    $count++;
}
echo "TOTAL: $count\n";
