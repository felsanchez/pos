<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();

echo "<h2>DESCRIBE factus_tributos</h2>";
$stmt = $db->query("DESCRIBE factus_tributos");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "<h2>DESCRIBE factus_tipos_documentos</h2>";
// Check if table exists first
try {
    $stmt = $db->query("DESCRIBE factus_tipos_documentos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Table factus_tipos_documentos likely does not exist.";
}
?>