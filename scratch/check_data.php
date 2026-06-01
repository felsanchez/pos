<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    echo "=== clientes DESCRIBE ===\n";
    $stmt = $db->prepare("DESCRIBE clientes");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== Sample client from clientes ===\n";
    $stmt = $db->prepare("SELECT * FROM clientes ORDER BY id DESC LIMIT 2");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
