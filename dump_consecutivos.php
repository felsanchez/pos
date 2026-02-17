<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();
echo "<h2>SELECT * FROM consecutivos</h2>";
try {
    $stmt = $db->query("SELECT * FROM consecutivos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<h2>SELECT codigo FROM ventas ORDER BY id DESC LIMIT 5</h2>";
try {
    $stmt = $db->query("SELECT codigo FROM ventas ORDER BY id DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>