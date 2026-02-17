<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM factus_tributos WHERE id = 2");
    $stmt->execute();
    $tributo = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "--- DATA START ---\n";
    print_r($tributo);
    echo "--- DATA END ---\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
