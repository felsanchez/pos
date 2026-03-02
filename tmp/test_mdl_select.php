<?php
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$rango = ModeloFactus::mdlObtenerRangoNC();
echo "MDL SELECT RESULT:\n";
print_r($rango);
?>