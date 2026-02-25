<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION['id'] = 14;

echo "=== PROBANDO EXISTENCIA DE ENDPOINT NOTA DÉBITO ===\n";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error de autenticación: " . $auth['mensaje'] . "\n");
}

$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

// Probar varios endpoints posibles
$endpoints = [
    '/v1/debit-notes/validate',
    '/v1/bills/debit-note/validate',
    '/v1/bills/debit-note'
];

foreach ($endpoints as $path) {
    $url = $baseUrl . $path;
    echo "Probando URL: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([])); // Cuerpo vacío para forzar error de validación
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
