<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$url = $baseUrl . '/v1/bills/validate';
echo "POST $url (Range 1192 - NO DOCUMENT FIELD)\n";

$payload = [
    "numbering_range_id" => 1192,
    "reference_code" => "ND-NO-DOC-" . time(),
    "payment_method_code" => "10",
    "customer" => [
        "identification" => "123456789",
        "identification_document_id" => 3,
        "names" => "PRUEBA ND",
        "address" => "CALLE 1",
        "email" => "test@test.com",
        "phone" => "123456",
        "legal_organization_id" => 2,
        "tribute_id" => 21,
        "municipality_id" => 980
    ],
    "items" => [
        [
            "code_reference" => "TEST",
            "name" => "Prueba",
            "quantity" => 1,
            "discount_rate" => 0,
            "price" => 1000,
            "tax_rate" => "19.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 0,
            "tribute_id" => 1
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: $httpCode\n";
echo "RESPONSE: $response\n";
