<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->prepare("SHOW TABLES LIKE 'notas_debito'");
$stmt->execute();
$res = $stmt->fetch();

if ($res) {
    echo "EXISTS: notas_debito table found.\n";
    $stmt = $db->prepare("DESCRIBE notas_debito");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    echo "NOT FOUND: notas_debito table does not exist.\n";
}

$stmt = $db->prepare("SHOW TABLES LIKE 'notas_credito'");
$stmt->execute();
$res = $stmt->fetch();
if ($res) {
    echo "EXISTS: notas_credito table found (as reference).\n";
}
