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

$variants = [
    '/v1/support-documents/adjustments/validate',
    '/v1/support-documents/adjustment/validate',
    '/v1/support-documents/adjustment-notes/validate',
    '/v1/support-document-adjustments/validate',
    '/v1/support-documents/validate-adjustment',
    '/v1/support-documents/validation-adjustment',
    '/v1/adjustments/support-documents/validate',
    '/v1/adjustment-notes/support-documents/validate',
    '/v1/support-documents/notes/validate',
    '/v1/support-documents/credits/validate',
    '/v1/support-documents/credit-notes/validate'
];

foreach ($variants as $path) {
    $url = $baseUrl . $path;
    echo "POST $url ... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["test" => "1"]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP $httpCode\n";
    if ($httpCode != 404 && $httpCode != 405) {
        echo "POSSIBLE MATCH FOUND!\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
    }
}
