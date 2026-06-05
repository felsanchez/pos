<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM ventas WHERE id = 61");
    $stmt->execute();
    $v = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($v) {
        print_r($v);
    } else {
        echo "No sale found with ID 61\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
