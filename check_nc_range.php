<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

$ranges = ModeloFactus::mdlMostrarRangos();

echo "<h1>Rangos de Numeración Disponibles</h1>";
echo "<pre>";
print_r($ranges);
echo "</pre>";

// Filtrar por tipo documento 'CreditNote' o similar (en Factus suele ser document_id 4 o similar)
/*
 Tipos de doc comunes:
 1 = Factura Venta
 4 = Nota Credito
 5 = Nota Debito
*/
?>