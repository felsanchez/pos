<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$billId = 498;
$originalNumber = "SEDS984000028";
$uuid = "4fed7d240941c93cd3c4d4c251f96b340c70db8f3a976de4b283593fd3600b610072e480ff133a1df7b5a91199e1a659";

$payload = [
    "numbering_range_id" => 1193,
    "support_document_id" => $billId, // Instead of bill_id
    "type_document_id" => 12,
    "reference_code" => "NA-NC-DS-" . time(),
    "billing_reference" => [
        "number" => $originalNumber,
        "uuid" => $uuid,
        "issue_date" => "2026-02-24"
    ],
    "correction_concept_id" => 2,
    "correction_concept_code" => "2",
    "observation" => "Prueba NC-DS via credit-notes",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
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
            "tribute_id" => 7,
            "discount_rate" => 0
        ]
    ]
];

$url = $baseUrl . "/v1/credit-notes/validate";
echo "Testing POST to $url with support_document_id ...\n";

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

echo "HTTP CODE: $httpCode\n";
echo "Response: $response\n";
?>