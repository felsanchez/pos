<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION['id'] = 14;

echo "=== PROBANDO ENDPOINTS PARA NOTA DE AJUSTE DS ===\n";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error de autenticación: " . $auth['mensaje'] . "\n");
}

$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$endpoints = [
    '/v1/support-documents/adjustments/validate',
    '/v1/support-documents/adjustment/validate',
    '/v1/support-documents/adjust/validate',
    '/v1/support-documents/adjustments',
    '/v1/support-documents/adjustment',
    '/v1/bills/support-document-adjustment/validate',
    '/v1/bills/support-document-adjustment'
];

foreach ($endpoints as $path) {
    $url = $baseUrl . $path;
    echo "Probando POST en: $url\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([])); // Vacío para ver error de validación vs 404/405
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    echo "RESPONSE: " . substr($response, 0, 500) . "\n";
    echo "------------------------------------------\n";
}
