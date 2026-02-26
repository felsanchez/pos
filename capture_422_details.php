<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

// Obtenemos el ID del Documento Soporte desde el request (pasado por CLI)
// Uso: php capture_422_details.php --idDS=28
$options = getopt("", ["idDS:"]);
$idDS = $options['idDS'] ?? 28;

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];

// Obtenemos los datos tal cual los enviaría el controlador
$originalDS = ModeloFactus::mdlMostrarDocumentosSoporte("id", $idDS);
$motivo = "2"; // Concepto por defecto para prueba
$motivoDescripcion = "Pruebas de depuración 422";
$productosAjuste = json_decode($originalDS["productos"], true);
$metodoPago = $originalDS["metodo_pago"];

$datosNota = ControladorFactus::prepararDatosNotaAjusteDS($originalDS, $motivo, $motivoDescripcion, $productosAjuste, $metodoPago);

echo "<h2>Depuración de Nota de Ajuste</h2>";
echo "<h3>Payload enviado:</h3>";
echo "<pre>" . json_encode($datosNota, JSON_PRETTY_PRINT) . "</pre>";

$resultado = ModeloFactus::mdlCrearNotaAjusteDS($token, $datosNota);

echo "<h3>Código HTTP: " . $resultado['http_code'] . "</h3>";
echo "<h3>Respuesta API:</h3>";
echo "<pre>" . $resultado['respuesta'] . "</pre>";
?>