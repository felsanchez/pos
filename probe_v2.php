<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
// Cambiamos v1 por v2 en el baseUrl para probar
$baseUrl = str_replace('/v1', '/v2', $config['api_url']);

$tests = [
    '/v2/debit-notes/validate',
    '/v2/bills/validate'
];

foreach ($tests as $path) {
    if (strpos($path, 'https') === 0) {
        $url = $path;
    } else {
        $url = $baseUrl . $path; // Esto depende de si baseUrl ya tiene v1 o no.
        // Re-ajuste
        $url = str_replace('.co/v2/v2', '.co/v2', $baseUrl . $path);
        if (strpos($url, '/v1/v2') !== false) {
            $url = str_replace('/v1/v2', '/v2', $url);
        }
    }

    echo "POST $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["test" => "data"]));
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
