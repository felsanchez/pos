<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "<h2>Estructura de la tabla clientes</h2>";
$stmt = $db->query("DESCRIBE clientes");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td><strong>{$row['Field']}</strong></td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Campos relacionados con IVA o responsabilidad:</h2>";
$stmt = $db->query("DESCRIBE clientes");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (
        stripos($row['Field'], 'iva') !== false ||
        stripos($row['Field'], 'responsab') !== false ||
        stripos($row['Field'], 'tribut') !== false ||
        stripos($row['Field'], 'regimen') !== false
    ) {
        echo "<p><strong>{$row['Field']}</strong>: {$row['Type']}</p>";
    }
}
?>