<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$targets = [
    $baseUrl . "/",
    $baseUrl . "/v1",
    $baseUrl . "/v1/support-documents",
];

foreach ($targets as $url) {
    echo "Probing $url ...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   GET HTTP $httpCode\n";
    echo "   Response: " . substr($response, 0, 500) . "\n\n";
}
?>