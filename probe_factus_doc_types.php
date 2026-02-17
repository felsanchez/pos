<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$token = ModeloFactus::mdlObtenerAccessToken();

if (!$token) {
    echo "Error: No se pudo obtener el token.\n";
    exit;
}

// Intentar obtener tipos de documentos
// Factus suele tener endpoints de referencia como /v1/identification-types
$endpoints = [
    '/v1/identification-types',
    '/v1/references/identification-types',
    '/v1/reference/identification-types'
];

$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

foreach ($endpoints as $endpoint) {
    echo "Probing endpoint: $endpoint\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";

    if ($httpCode == 200) {
        $json = json_decode($response, true);
        print_r($json);
        break;
    } else {
        echo "Response: " . substr($response, 0, 100) . "...\n";
    }
    echo "-------------------\n";
}