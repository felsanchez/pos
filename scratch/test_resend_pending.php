<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Resend same reference code TEST-BAGS-FIXED-1780569678 but with tribute_id = 1 (IVA 19%) to see if it clears
$payload = [
    "numbering_range_id" => 1190,
    "reference_code" => "TEST-BAGS-FIXED-1780569678", // The pending draft ID
    "observation" => "Prueba de liberar deadlock enviando IVA 19",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "operation_type" => 10,
    "send_email" => false,
    "establishment" => [
        "name" => "kontrol pos",
        "address" => "Cll 50 #12 34",
        "phone_number" => "+573001234567",
        "email" => "kontrol.info@gmail.com",
        "municipality_id" => "169",
        "legal_organization_id" => "2"
    ],
    "customer" => [
        "identification" => "12345678",
        "names" => "Juan Perez exc",
        "address" => "Calle Falsa 123",
        "email" => "juan@ejemplo.com",
        "phone" => "3001234567",
        "legal_organization_id" => "2",
        "tribute_id" => "21",
        "identification_document_id" => 3,
        "municipality_id" => 169,
        "fiscal_responsibilities" => [
            [
                "code" => "R-99-PN"
            ]
        ]
    ],
    "items" => [
        [
            "scheme_id" => "1",
            "note" => "",
            "code_reference" => "ITEM-1",
            "name" => "camisa polo",
            "quantity" => 1,
            "discount_rate" => "0.00",
            "price" => "119.000000",
            "tax_rate" => "19.00",
            "unit_measure_id" => 70, // UNIDAD
            "standard_code_id" => 1,
            "is_excluded" => 0,
            "tribute_id" => 1, // IVA
            "withholding_taxes" => []
        ]
    ]
];

echo "=== RESENDING PENDING REF CODE WITH IVA 19% ===\n";
$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

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
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "=== API RESPONSE ===\n";
echo "HTTP CODE: " . $httpCode . "\n";
if ($curlError) {
    echo "CURL Error: " . $curlError . "\n";
} else {
    echo "Raw: " . $respuesta . "\n";
}
