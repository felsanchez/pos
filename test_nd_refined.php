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

// Payload con related_documents corregido
$payload = [
    "document" => "23", // Probamos 23 de nuevo con body completo
    "numbering_range_id" => 1192,
    "reference_code" => "ND-PROBE-V2-" . time(),
    "observation" => "Prueba técnica",
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
            "name" => "Intereses de mora",
            "quantity" => 1,
            "discount_rate" => 0,
            "price" => 1000,
            "tax_rate" => "19.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 0,
            "tribute_id" => 1
        ]
    ],
    "related_documents" => [
        [
            "uuid" => "12345678-1234-1234-1234-123456789012",
            "issue_date" => "2024-01-01",
            "number" => "FEFG1",
            "code" => "3" // Concepto de corrección: Cambio del valor
        ]
    ]
];

echo "POST $url (Document 23 + Full Data)\n";

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

// Si falla 23, probamos 03 con los mismos datos
echo "\n--- Probando con document 03 ---\n";
$payload["document"] = "03";
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
