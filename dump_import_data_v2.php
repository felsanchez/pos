<?php
require_once "modelos/factus.modelo.php";

$tributos = ModeloFactus::mdlObtenerTributos();
echo "TRIBUTOS:\n";
foreach ($tributos as $t) {
    echo "ID: {$t['id']} | Nombre: '{$t['nombre']}' | Codigo: '{$t['codigo']}'\n";
}

$unidades = ModeloFactus::mdlObtenerUnidadesMedida();
echo "\nUNIDADES:\n";
foreach ($unidades as $u) {
    echo "ID: {$u['id']} | Nombre: '{$u['nombre']}' | Codigo DIAN: '{$u['codigo_dian']}'\n";
}
