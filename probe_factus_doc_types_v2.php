<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$token = ModeloFactus::mdlGarantizarTokenValido();

if (!$token) {
    die("No hay token válido");
}

$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

// Lista de posibles endpoints para tipos de documentos
$endpoints = [
    '/v1/identification-types',
    '/v1/identification-documents',
    '/v1/document-types',
    '/v1/references/identification-types',
    '/v1/references/identification-documents'
];

foreach ($endpoints as $endpoint) {
    echo "Probing: $endpoint ... ";

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

    echo "HTTP $httpCode\n";

    if ($httpCode == 200) {
        $json = json_decode($response, true);
        print_r($json);
        echo "\n-----------------------------------\n";
        // Si encontramos uno válido, mostramos los datos y terminamos
        if (isset($json['data']) && is_array($json['data'])) {
            break;
        }
    }
}
