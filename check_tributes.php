<?php
require_once "modelos/conexion.php";

try {
    $stmt = Conexion::conectar()->prepare("SELECT id, nombre, codigo, porcentaje_defecto FROM factus_tributos ORDER BY id");
    $stmt->execute();
    $tributos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== LISTA DE TRIBUTOS ===\n";
    foreach ($tributos as $t) {
        echo "ID: " . $t['id'] .
            " | Nombre: " . str_pad($t['nombre'], 20) .
            " | Código: " . str_pad($t['codigo'], 5) .
            " | %: " . $t['porcentaje_defecto'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
