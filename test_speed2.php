<?php
$t0 = microtime(true);
require_once "controladores/ventas.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/usuarios.modelo.php";

$t1 = microtime(true);
echo "1. Tiempo Includes: " . ($t1 - $t0) . " s\n";

$fechaInicial = null;
$fechaFinal = null;

$respuesta = ControladorVentas::ctrRangoFechasFacturasElectronicas($fechaInicial, $fechaFinal, "venta");
$t2 = microtime(true);
echo "2. Tiempo SQL Facturas (" . count($respuesta) . " filas): " . ($t2 - $t1) . " s\n";

$siguienteConsecutivoBase = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus(true);
$t3 = microtime(true);
echo "3. Tiempo Consecutivo Factus(true): " . ($t3 - $t2) . " s\n";

$idsVentas = array_column($respuesta, 'id');
$ventasConNC = ModeloFactus::mdlObtenerVentasConNotaCredito($idsVentas);
$t4 = microtime(true);
echo "4. Tiempo ObtenerVentasConNotaCredito: " . ($t4 - $t3) . " s\n";

// Filters like in the view
$clientes = ControladorClientes::ctrMostrarClientes(null, null);
$t5 = microtime(true);
echo "5. Tiempo MostrarClientes (combo filtros): " . ($t5 - $t4) . " s\n";

$usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
$t6 = microtime(true);
echo "6. Tiempo MostrarUsuarios (combo filtros): " . ($t6 - $t5) . " s\n";

// Loop simulation
foreach ($respuesta as $key => $value) {
    if (!empty($value["numero_factura"])) {
        $numeroMostrar = $value["numero_factura"];
        $esBorrador = false;
    } else {
        $numeroMostrar = 'FES-' . $value["codigo"];
    }
}
$t7 = microtime(true);
echo "7. Tiempo Bucle Principal PHP: " . ($t7 - $t6) . " s\n";

echo "\n--- TIEMPO TOTAL PHP ---: " . ($t7 - $t0) . " s\n";
?>
