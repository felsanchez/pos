<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$rango = ModeloFactus::mdlObtenerRangoActivo();
print_r($rango);
echo "Rango ID: " . ($rango['id_factus'] ?? 1) . "\n";
