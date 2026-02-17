<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$tributos = ModeloFactus::mdlObtenerTributos();

foreach ($tributos as $t) {
    if ($t['codigo'] == '01' || $t['codigo'] == 'ZZ' || $t['codigo'] == 'ZA') {
        echo "ID: " . $t['id'] . " | Codigo: " . $t['codigo'] . " | Nombre: " . $t['nombre'] . "\n";
    }
}
?>