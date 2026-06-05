<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticación fallida: " . $auth['mensaje']);
}
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$apiUrl = $config['api_url'];

$endpoints = [
    '/v1/payment-methods',
    '/v1/payment_methods',
    '/v1/payment-method',
    '/v1/payment_method',
    '/v1/payment-methods-dian',
    '/v1/reference-data/payment-methods',
    '/v1/catalogs/payment-methods',
];

foreach ($endpoints as $endpoint) {
    $url = $apiUrl . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Endpoint: $endpoint | HTTP Code: $httpCode\n";
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "Response data count: " . (isset($data['data']) ? count($data['data']) : 'N/A') . "\n";
        print_r(array_slice($data['data'] ?? $data, 0, 15));
        echo "\n";
    }
}
