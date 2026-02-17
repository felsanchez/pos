<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$tributos = ModeloFactus::mdlObtenerTributos();
$file = fopen("dump_tributos_full.txt", "w");
foreach ($tributos as $t) {
    fwrite($file, "ID: " . $t['id'] . " | Codigo: " . $t['codigo'] . " | Nombre: " . $t['nombre'] . "\n");
}
fclose($file);
echo "Dumped " . count($tributos) . " tributes to dump_tributos_full.txt";
?>