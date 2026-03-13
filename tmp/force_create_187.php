<?php
require_once 'controladores/ventas.controlador.php';
require_once 'modelos/ventas.modelo.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

if (!isset($_SESSION)) {
    session_start();
}

$idVenta = 765; // The order we want to convert
$venta = ControladorVentas::ctrMostrarVentas("id", $idVenta);
if (!$venta)
    die("No venta 764");

$configuracion = ModeloFactus::mdlObtenerConfiguracion();
// Force creating standard FE directly to unblock 187
try {
    $resultado = ControladorVentas::ctrCrearVentaFactus($venta, $configuracion, false);
    var_dump($resultado);
} catch (Exception $e) {
    echo "Excepcion capturada: " . $e->getMessage();
}
die();
?>