<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SELECT id_factus, prefijo, numero_actual, numero_hasta FROM factus_rangos");

echo "Rangos disponibles:\n\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $disponible = $r['numero_hasta'] - $r['numero_actual'];
    echo "Prefijo: " . $r['prefijo'] . "\n";
    echo "  ID Factus: " . $r['id_factus'] . "\n";
    echo "  Actual: " . $r['numero_actual'] . "\n";
    echo "  Hasta: " . $r['numero_hasta'] . "\n";
    echo "  Disponibles: " . $disponible . "\n\n";
}
