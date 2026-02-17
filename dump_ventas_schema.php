<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();
echo "<h2>DESCRIBE ventas</h2>";
try {
    $stmt = $db->query("DESCRIBE ventas");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>