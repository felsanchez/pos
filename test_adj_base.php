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
$originalUUID = "d60eafc73e3f597dae276552010598864a2a65ddd34fc5fccea83bf9d7d49036f56217d9b2bec56310d7c6153148c3f0";
$originalDate = "2026-02-24";

$payload = [
    "numbering_range_id" => 1193,
    "reference_code" => "NA-BASE-" . time(),
    "billing_reference" => [
        "number" => $originalNumber,
        "uuid" => $originalUUID,
        "issue_date" => $originalDate
    ],
    "correction_concept_id" => 2,
    "items" => [
        [
            "code_reference" => "1403",
            "name" => "prueba",
            "quantity" => 1,
            "price" => 100,
            "tax_rate" => "0.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 1,
            "tribute_id" => 7
        ]
    ]
];

$url = $baseUrl . "/v1/support-documents";
echo "POST $url ...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $httpCode\n";
echo "Response: $response\n";
