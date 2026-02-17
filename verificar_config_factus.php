<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Verificar Configuración de Factus</h2>";

$db = Conexion::conectar();

// Ver estructura de la tabla
$stmt = $db->query("DESCRIBE factus_config");
$columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Columnas de factus_config:</h3>";
echo "<ul>";
foreach ($columnas as $col) {
    echo "<li><strong>{$col['Field']}</strong>: {$col['Type']}</li>";
}
echo "</ul>";

// Ver datos actuales
$stmt = $db->query("SELECT * FROM factus_config LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Configuración Actual:</h3>";
echo "<pre>";
foreach ($config as $key => $value) {
    if (in_array($key, ['client_secret', 'token'])) {
        echo "$key: [OCULTO]\n";
    } else {
        echo "$key: $value\n";
    }
}
echo "</pre>";
