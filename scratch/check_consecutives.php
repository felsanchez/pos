<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM consecutivos");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
