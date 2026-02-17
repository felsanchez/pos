<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir ruta base si es necesario, o usar rutas relativas correctas
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM factus_tributos");
    $stmt->execute();
    $tributos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "--- START DATA ---\n";
    print_r($tributos);
    echo "--- END DATA ---\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
