<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = 22");
    $stmt->execute();
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($p) {
        print_r($p);
    } else {
        echo "No product found with ID 22\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
