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

$billId = 472;
$originalNumber = "SEDS984000008";

$payload1 = [
    "numbering_range_id" => 1193,
    "support_document_id" => $billId,
    "reference_code" => "NA-REF-DS-" . time(),
    "correction_concept_id" => 2,
    "items" => [["code_reference" => "1403", "quantity" => 1, "price" => 100]]
];

$payload2 = [
    "numbering_range_id" => 1193,
    "parent_id" => $billId,
    "reference_code" => "NA-REF-DS-" . time(),
    "correction_concept_id" => 2,
    "items" => [["code_reference" => "1403", "quantity" => 1, "price" => 100]]
];

$url = $baseUrl . "/v1/support-documents/validate";

foreach ([$payload1, $payload2] as $i => $p) {
    echo "Test Case " . ($i + 1) . " ... ";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($p));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json', 'Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP $httpCode Response: " . substr($response, 0, 150) . "\n";
}
