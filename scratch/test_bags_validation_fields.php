<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Define various configurations to test for plastic bags (tribute_id = 22)
$formatsToTest = [
    "Format C (tax_rate=0, per_unit_amount=73, base_unit_measure=1)" => [
        "tax_rate" => "0.00",
        "tribute_id" => 22,
        "per_unit_amount" => "73.00",
        "base_unit_measure" => "1.00"
    ],
    "Format D (tax_rate=73, per_unit_amount=73, base_unit_measure=1)" => [
        "tax_rate" => "73.00",
        "tribute_id" => 22,
        "per_unit_amount" => "73.00",
        "base_unit_measure" => "1.00"
    ],
    "Format E (tax_rate=0, per_unit_amount=73, base_unit_measure=1, is_nominal=true)" => [
        "tax_rate" => "0.00",
        "tribute_id" => 22,
        "per_unit_amount" => "73.00",
        "base_unit_measure" => "1.00",
        "is_nominal" => true
    ],
    "Format F (tax_rate=73, per_unit_amount=73, base_unit_measure=1, is_nominal=true)" => [
        "tax_rate" => "73.00",
        "tribute_id" => 22,
        "per_unit_amount" => "73.00",
        "base_unit_measure" => "1.00",
        "is_nominal" => true
    ],
    "Format G (tax_rate=0, unit_amount=73)" => [
        "tax_rate" => "0.00",
        "tribute_id" => 22,
        "unit_amount" => "73.00"
    ],
    "Format H (tax_rate=73, unit_amount=73)" => [
        "tax_rate" => "73.00",
        "tribute_id" => 22,
        "unit_amount" => "73.00"
    ],
    "Format I (tax_rate=0, per_unit_amount=73, base_unit_measure=1, is_amount=true)" => [
        "tax_rate" => "0.00",
        "tribute_id" => 22,
        "per_unit_amount" => "73.00",
        "base_unit_measure" => "1.00",
        "is_amount" => true
    ],
    "Format J (tax_rate=73, per_unit_amount=73, base_unit_measure=1, is_amount=true)" => [
        "tax_rate" => "73.00",
        "tribute_id" => 22,
        "per_unit_amount" => "73.00",
        "base_unit_measure" => "1.00",
        "is_amount" => true
    ]
];

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

foreach ($formatsToTest as $name => $fields) {
    echo "=== Testing $name ===\n";
    $payload = [
        "numbering_range_id" => 1190,
        "reference_code" => "TEST-BAGS-6a222776d5119",
        "observation" => "Testing nominal bags tax format: $name",
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
                "price" => "100.000000",
                "unit_measure_id" => 70, // UNIDAD
                "standard_code_id" => 1,
                "is_excluded" => 0,
                "withholding_taxes" => []
            ], $fields)
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
        echo "SUCCESS! Response message: " . ($decoded['message'] ?? 'Ok') . "\n";
    } else {
        echo "FAILED. Response: " . $respuesta . "\n";
    }
    echo "\n";
    
    // If we hit a 409 Conflict, we stop because the account is locked and we cannot proceed
    if ($httpCode === 409) {
        echo "Aborting remaining tests because sandbox account is locked with a pending bill (409 Conflict).\n";
        break;
    }
}
