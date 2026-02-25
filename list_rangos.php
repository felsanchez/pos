<?php
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$rangos = ModeloFactus::mdlObtenerRangos();

echo "=== RANGOS DE NUMERACIÓN ===\n";
foreach ($rangos as $rango) {
    echo "ID: " . $rango['id_factus'] . " | Doc: " . $rango['documento'] . " | Prefijo: " . $rango['prefijo'] . " | Estado: " . $rango['estado'] . "\n";
}
