<?php
// Script para probar qué devuelve el AJAX de movimientos
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "modelos/session-manager.php";
SessionManager::startSecure();

require_once "modelos/conexion.php";
require_once "modelos/movimientos.modelo.php";

// Simular la petición AJAX sin filtros
$filtros = array();

$movimientos = ModeloMovimientos::mdlMostrarMovimientos($filtros);

echo "<h2>Total de registros devueltos: " . count($movimientos) . "</h2>";

echo "<h3>Primeros 10 registros (deberían ser de 2026):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Fecha</th><th>Producto</th><th>Tipo</th></tr>";

for ($i = 0; $i < min(10, count($movimientos)); $i++) {
    $mov = $movimientos[$i];
    echo "<tr>";
    echo "<td>{$mov['id']}</td>";
    echo "<td>{$mov['fecha']}</td>";
    echo "<td>{$mov['nombre_producto']}</td>";
    echo "<td>{$mov['tipo_movimiento']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Últimos 10 registros (deberían ser de 2025):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Fecha</th><th>Producto</th><th>Tipo</th></tr>";

$start = max(0, count($movimientos) - 10);
for ($i = $start; $i < count($movimientos); $i++) {
    $mov = $movimientos[$i];
    echo "<tr>";
    echo "<td>{$mov['id']}</td>";
    echo "<td>{$mov['fecha']}</td>";
    echo "<td>{$mov['nombre_producto']}</td>";
    echo "<td>{$mov['tipo_movimiento']}</td>";
    echo "</tr>";
}

echo "</table>";
?>