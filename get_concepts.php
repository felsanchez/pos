<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$url = $baseUrl . "/v1/reference/correction-concepts?type=adjustment-note"; // Guessing the type name
echo "GET $url ...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $httpCode Response: $response\n";

// Fallback search in all correction concepts
$url2 = $baseUrl . "/v1/reference/correction-concepts";
echo "GET $url2 ...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
$response = curl_exec($ch);
echo "HTTP " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . " Response size: " . strlen($response) . "\n";
file_put_contents('correction_concepts.json', $response);
