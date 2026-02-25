<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$urls = [
    $baseUrl . '/v1/bills/validate',
    $baseUrl . '/v1/credit-notes/validate',
    $baseUrl . '/v1/debit-notes/validate'
];

foreach ($urls as $url) {
    echo "OPTIONS $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    echo "RESPONSE: $response\n";
    echo "------------------------------------------\n";
}
