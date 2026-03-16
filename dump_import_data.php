<?php
require_once "modelos/factus.modelo.php";

echo "--- TRIBUTOS ---\n";
$tributos = ModeloFactus::mdlObtenerTributos();
foreach ($tributos as $t) {
    echo "ID: {$t['id']} | Nombre: '{$t['nombre']}' | Codigo: '{$t['codigo']}'\n";
}

echo "\n--- UNIDADES ---\n";
$unidades = ModeloFactus::mdlObtenerUnidadesMedida();
foreach ($unidades as $u) {
    echo "ID: {$u['id']} | Nombre: '{$u['nombre']}' | Codigo DIAN: '{$u['codigo_dian']}'\n";
}
