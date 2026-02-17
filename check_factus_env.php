<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$config = ModeloFactus::mdlObtenerConfiguracion();

echo "Ambiente: " . $config['ambiente'] . "\n";
echo "API URL: " . $config['api_url'] . "\n";
