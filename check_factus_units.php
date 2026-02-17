<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

// Obtener token
$token = ModeloFactus::mdlGarantizarTokenValido();

if (!$token) {
    die("No hay token válido");
}

// Consultar unidades desde API
$unidades = ModeloFactus::mdlConsultarUnidadesAPI($token);

echo "Unidades disponibles en Factus API:\n\n";
echo str_pad("ID", 10) . str_pad("Código DIAN", 15) . "Nombre\n";
echo str_repeat("-", 60) . "\n";

foreach ($unidades as $unidad) {
    echo str_pad($unidad['id'], 10) .
        str_pad($unidad['code'], 15) .
        $unidad['name'] . "\n";
}
?>