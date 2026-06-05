<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Field names to test for tax amount override
$fieldsToTest = [
    "tax_amount" => ["tax_amount" => "73.00"],
    "tax_value" => ["tax_value" => "73.00"],
    "tax" => ["tax" => "73.00"],
    "amount" => ["amount" => "73.00"],
    "value" => ["value" => "73.00"],
    "total_tax" => ["total_tax" => "73.00"],
    "tax_val" => ["tax_val" => "73.00"],
    "tax_amount_value" => ["tax_amount_value" => "73.00"],
];

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

foreach ($fieldsToTest as $name => $override) {
    echo "=== Testing field: $name ===\n";
    
    $payload = [
        "numbering_range_id" => 1190,
        "reference_code" => "TEST-BAGS-6a222776d5119", // overwrite same draft to bypass lock
        "observation" => "Testing tax amount field: $name",
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
            array_merge([
                "scheme_id" => "1",
                "note" => "",
                "code_reference" => "ITEM-BAGS",
                "name" => "ppal bolsas",
                "quantity" => 1,
                "discount_rate" => "0.00",
                "price" => "27.000000", // tax-exclusive price
                "tax_rate" => "0.00",
                "unit_measure_id" => 70, // UNIDAD
                "standard_code_id" => 1,
                "is_excluded" => 0,
                "tribute_id" => 22,
                "per_unit_amount" => "73.00",
                "base_unit_measure" => "1.00",
                "withholding_taxes" => []
            ], $override)
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: " . $httpCode . "\n";
    $decoded = json_decode($respuesta, true);
    if ($httpCode === 200 || $httpCode === 201) {
        echo "SUCCESS! Response: " . ($decoded['message'] ?? 'Ok') . "\n";
        break; // stop on success
    } else {
        echo "FAILED. Response: " . $respuesta . "\n\n";
    }
    
    // Sleep slightly
    usleep(500000);
}
