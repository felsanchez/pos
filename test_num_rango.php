<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT id, id_factus, documento, prefijo, numero_actual, estado FROM factus_rangos WHERE id_factus = 1193");
$stmt->execute();
$rango = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Rango 1193:\n";
print_r($rango);

$stmt2 = Conexion::conectar()->prepare("SELECT id, numero_nota_ajuste, estado_dian FROM notas_ajuste_ds ORDER BY id DESC LIMIT 5");
$stmt2->execute();
$notas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo "\nUltimas Notas:\n";
foreach ($notas as $n) {
    echo $n['id'] . " - " . $n['numero_nota_ajuste'] . " - " . $n['estado_dian'] . "\n";
}
?>