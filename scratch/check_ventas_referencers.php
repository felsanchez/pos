<?php
require_once __DIR__ . "/../modelos/conexion.php";

$db = Conexion::conectar();

$stmt = $db->prepare("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'ventas'
      AND TABLE_SCHEMA = DATABASE()
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Foreign Keys referencing 'ventas':\n";
echo str_pad("Table Name", 25) . str_pad("Column Name", 20) . "Constraint Name\n";
echo str_repeat("-", 75) . "\n";
foreach ($results as $r) {
    echo str_pad($r['TABLE_NAME'], 25) . str_pad($r['COLUMN_NAME'], 20) . $r['CONSTRAINT_NAME'] . "\n";
}
