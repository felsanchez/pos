<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM factus_rangos");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
