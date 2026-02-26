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

$cases = [
    ["url" => "/v1/bills/validate", "payload" => ["numbering_range_id" => 1193]],
    ["url" => "/v1/bills/validate", "payload" => ["numbering_range_id" => 1193, "document" => "96"]],
    ["url" => "/v1/bills/validate", "payload" => ["numbering_range_id" => 1193, "type_document_id" => 12]]
];

foreach ($cases as $case) {
    $url = $baseUrl . $case['url'];
    echo "POST $url Body: " . json_encode($case['payload']) . " ... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($case['payload']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP $httpCode\n";
    echo "Response: " . substr($response, 0, 300) . "\n";
    echo "------------------------------------------\n";
}
