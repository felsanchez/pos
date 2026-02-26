<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$endpoints = [
    '/v1/support-documents/validate',
    '/v1/support-documents/adjustment/validate',
    '/v1/support-documents/adjustments/validate',
    '/v1/support-document/validate',
    '/v1/support-document/adjustment/validate',
    '/v1/support-documents/498/adjustment',
    '/v1/support-documents/adjustments',
    '/v1/support-documents/adjustment',
    '/v1/support-documents/notes',
    '/v1/support-documents/notes/validate',
    '/v1/support-document-adjustments/validate'
];

foreach ($endpoints as $path) {
    $url = $baseUrl . $path;
    echo "Checking $path ... ";

    // Probamos OPTIONS para ver métodos permitidos
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_exec($ch);
    $optCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Probamos POST vacío
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json', 'Accept: application/json']);
    $response = curl_exec($ch);
    $postCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "OPTIONS: $optCode | POST: $postCode\n";
    if ($postCode == 422 || $postCode == 200 || $postCode == 201) {
        echo "   >>> POSSIBLE MATCH: $path\n";
        echo "   >>> Response: " . substr($response, 0, 200) . "\n";
    }
}
