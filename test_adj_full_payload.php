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

// From previous successful DS emission (debug_ds_2026-02-24_193550.txt)
$originalNumber = "SEDS984000008";
$originalUUID = "d60eafc73e3f597dae276552010598864a2a65ddd34fc5fccea83bf9d7d49036f56217d9b2bec56310d7c6153148c3f0";
$originalDate = "2026-02-24";
$originalFactusId = 472;

$payload = [
    "numbering_range_id" => 1193, // Adjustment Note DS range
    "billing_reference" => [
        "number" => $originalNumber,
        "uuid" => $originalUUID,
        "issue_date" => $originalDate
    ],
    "customer" => [ // Provider in DS is Customer in Adjustment?
        "identification" => "454356555",
        "dv" => "1",
        "company" => "pedrito proveed",
        "trade_name" => "pedro comerc",
        "names" => "pedrito proveed",
        "address" => "Calle 36 11 1544",
        "email" => "pedri@gmail.com",
        "phone" => "3565434544",
        "legal_organization_id" => "1",
        "tribute_id" => "21",
        "identification_document_id" => "6",
        "municipality_id" => "170",
        "country_code" => "CO"
    ],
    "correction_concept_id" => 2, // Anulación
    "observation" => "Prueba de anulación desde POS",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "items" => [
        [
            "scheme_id" => "1",
            "name" => "prueba01 Exc GLL",
            "code_reference" => "1403",
            "quantity" => 2,
            "discount_rate" => "0.00",
            "price" => "100.00",
            "tax_rate" => "0.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 1,
            "tribute_id" => 7
        ]
    ]
];

$url = $baseUrl . '/v1/credit-notes/validate';
echo "Testing POST to $url ...\n";

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
echo "RESPONSE: $response\n";
