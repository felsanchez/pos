<?php
require_once "modelos/conexion.php";
echo "=== DESCRIBE notas_credito ===\n";
$stmt = Conexion::conectar()->query("DESCRIBE notas_credito");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
echo "\n=== Last NC observacion field ===\n";
$stmt2 = Conexion::conectar()->query("SELECT id, mensaje_dian FROM notas_credito ORDER BY id DESC LIMIT 1");
$row = $stmt2->fetch();
print_r($row);
?>