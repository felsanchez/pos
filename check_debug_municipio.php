<?php
// Leer el último registro del debug
$file = "debug_cliente_save.txt";
if (file_exists($file)) {
    $content = file_get_contents($file);
    echo "<h2>Contenido de debug_cliente_save.txt</h2>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<p>El archivo debug_cliente_save.txt no existe</p>";
}

// Verificar el HTML del select generado
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

echo "<h2>Verificar opciones del select de municipio</h2>";
$municipios = ModeloFactus::mdlObtenerMunicipios();
echo "<p>Total de municipios: " . count($municipios) . "</p>";

echo "<h3>Primeros 5 municipios:</h3>";
foreach (array_slice($municipios, 0, 5) as $mun) {
    echo "<p>ID: {$mun['id']} - {$mun['nombre']} - {$mun['departamento']}</p>";
}

echo "<h3>Buscar 'Tenza':</h3>";
foreach ($municipios as $mun) {
    if (stripos($mun['nombre'], 'Tenza') !== false) {
        echo "<p><strong>ENCONTRADO:</strong> ID: {$mun['id']} - {$mun['nombre']} - {$mun['departamento']}</p>";
    }
}

echo "<h3>Buscar 'Zona Bananera':</h3>";
foreach ($municipios as $mun) {
    if (stripos($mun['nombre'], 'Zona Bananera') !== false) {
        echo "<p><strong>ENCONTRADO:</strong> ID: {$mun['id']} - {$mun['nombre']} - {$mun['departamento']}</p>";
    }
}
?>