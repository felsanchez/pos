<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Obtener el rango que estás usando (probablemente el que tiene prefijo de producción)
echo "Rangos disponibles:\n";
$stmt = $db->query("SELECT id_factus, prefijo, numero_actual FROM factus_rangos ORDER BY id");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$r['id_factus']}, Prefijo: {$r['prefijo']}, Actual: {$r['numero_actual']}\n";
}

echo "\n¿Cuál es el ID del rango que quieres ajustar? (ingresa el id_factus): ";
