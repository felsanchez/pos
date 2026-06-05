<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Rates to test: 50, 60, 66, 70, 73
$rates = [50, 60, 66, 70, 73];

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

foreach ($rates as $rate) {
    $tax_amount = number_format($rate, 2, '.', '');
    $price_inclusive = number_format(100.00 + $rate, 6, '.', '');
    $tax_rate = number_format($rate, 2, '.', '');
    
    echo "=== Testing Bag Tax Rate: $rate COP (Price Inclusive: $price_inclusive, Tax Rate: $tax_rate%) ===\n";
    
    $payload = [
        "numbering_range_id" => 1190,
        "reference_code" => "TEST-BAGS-6a222776d5119", // overwrite same draft to bypass lock
        "observation" => "Testing bag tax rate: $rate COP",
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
                "code_reference" => "ITEM-BAGS",
                "name" => "ppal bolsas",
                "quantity" => 1,
                "discount_rate" => "0.00",
                "price" => $price_inclusive,
                "tax_rate" => $tax_rate,
                "unit_measure_id" => 70, // UNIDAD
                "standard_code_id" => 1,
                "is_excluded" => 0,
                "tribute_id" => 22,
                "per_unit_amount" => $tax_amount,
                "base_unit_measure" => "1.00",
                "is_nominal" => true,
                "is_amount" => true,
                "withholding_taxes" => []
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
    
    // Sleep slightly between requests
    usleep(500000);
}
