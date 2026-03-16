<?php
require_once "modelos/factus.modelo.php";

$data = [
    'tributos' => ModeloFactus::mdlObtenerTributos(),
    'unidades' => ModeloFactus::mdlObtenerUnidadesMedida()
];

file_put_contents("import_debug.json", json_encode($data, JSON_PRETTY_PRINT));
echo "Saved to import_debug.json\n";
