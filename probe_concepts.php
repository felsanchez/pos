<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$endpoints = [
    '/v1/correction-concepts',
    '/v1/correction_concepts',
    '/v1/adjustment-reasons',
    '/v1/credit-notes/correction-concepts',
    '/v1/debit-notes/correction-concepts'
];

foreach ($endpoints as $path) {
    $url = $baseUrl . $path;
    echo "GET $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    echo "RESPONSE: " . substr($response, 0, 500) . "...\n";
    echo "------------------------------------------\n";
}
