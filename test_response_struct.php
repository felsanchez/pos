<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$originalDS = ModeloFactus::mdlMostrarDocumentosSoporte('id', 28);
$items = json_decode($originalDS['productos'], true);

$datos = ControladorFactus::prepararDatosNotaAjusteDS($originalDS, '2', 'Prueba depuracion estructura', $items);

echo "ENVIANDO PAYLOAD:\n";
echo json_encode($datos, JSON_PRETTY_PRINT) . "\n\n";

$res = ModeloFactus::mdlCrearNotaAjusteDS($auth['token'], $datos);

echo "HTTP CODE: " . $res['http_code'] . "\n";
echo "RESPUESTA COMPLETA:\n";
echo $res['respuesta'] . "\n";
