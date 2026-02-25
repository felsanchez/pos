<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION['id'] = 14;

echo "=== SEGUNDA PRUEBA DE ENDPOINTS NOTA DÉBITO ===\n";

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$tests = [
    ['url' => '/v1/debit-notes', 'method' => 'GET'],
    ['url' => '/v1/debit-note/validate', 'method' => 'POST'],
    ['url' => '/v1/debit-notes', 'method' => 'POST'],
    ['url' => '/v1/bills/debit-notes', 'method' => 'POST'],
    ['url' => '/v1/bills/debit-notes/validate', 'method' => 'POST']
];

foreach ($tests as $test) {
    $url = $baseUrl . $test['url'];
    $method = $test['method'];
    echo "Probando [$method] $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    echo "RESPONSE: " . substr($response, 0, 500) . "...\n";
    echo "------------------------------------------\n";
}
