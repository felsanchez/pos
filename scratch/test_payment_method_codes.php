<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Create a template payload using a valid invoice template
$payloadTemplate = [
    "numbering_range_id" => 1190,
    "reference_code" => "TEST-PAYMENT-" . time() . "-",
    "observation" => "Testing payment method code",
    "payment_form" => "1", // Contado
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10", // Temporary value
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
        "names" => "Juan Perez",
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
            "code_reference" => "ITEM-TEST",
            "name" => "ppal bolsas",
            "quantity" => 1,
            "discount_rate" => "0.00",
            "price" => "100.000000",
            "tax_rate" => "0.00",
            "unit_measure_id" => 70, // UNIDAD
            "standard_code_id" => 1,
            "is_excluded" => 1,
            "tribute_id" => 1,
            "withholding_taxes" => []
        ]
    ]
];

$codesToTest = ["1", "97", "10", "48"]; // 1: Undefined, 97: Mutual, 10: Cash, 48: Credit Card

foreach ($codesToTest as $code) {
    $payload = $payloadTemplate;
    $payload["payment_method_code"] = $code;
    $payload["reference_code"] .= $code;
    
    $url = "https://api-sandbox.factus.com.co/v1/bills/validate";
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Testing payment_method_code: '$code' | HTTP CODE: $httpCode\n";
    $data = json_decode($response, true);
    if ($httpCode === 201) {
        echo "  Result: SUCCESS (Created)\n";
    } else {
        echo "  Result: FAILED - " . ($data['message'] ?? 'No message') . "\n";
        if (isset($data['errors'])) {
            echo "  Errors: " . json_encode($data['errors']) . "\n";
        }
        if (isset($data['data']['errors'])) {
            echo "  Data Errors: " . json_encode($data['data']['errors']) . "\n";
        }
    }
    echo "\n";
}
