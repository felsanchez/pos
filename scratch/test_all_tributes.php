<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Loop through tribute IDs from 1 to 30
for ($tId = 1; $tId <= 30; $tId++) {
    echo "Testing tribute_id = $tId... ";
    
    $payload = [
        "numbering_range_id" => 1190,
        "reference_code" => "TEST-TRIB-" . $tId . "-" . time(),
        "observation" => "Prueba de tribute_id " . $tId,
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
                "name" => "ppal bolsas",
                "quantity" => 1,
                "discount_rate" => "0.00",
                "price" => "100.000000",
                "tax_rate" => "73.00",
                "unit_measure_id" => 70, // UNIDAD
                "standard_code_id" => 1,
                "is_excluded" => 0,
                "tribute_id" => $tId,
                "withholding_taxes" => []
            ]
        ]
    ];

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode | ";
    $parsed = json_decode($respuesta, true);
    if ($parsed) {
        if ($httpCode == 201) {
            echo "SUCCESS!\n";
        } else {
            $msg = $parsed['message'] ?? '';
            $errs = $parsed['errors'] ?? $parsed['data']['errors'] ?? [];
            echo "Msg: $msg | Errs: " . json_encode($errs) . "\n";
        }
    } else {
        echo "Raw: " . substr($respuesta, 0, 200) . "\n";
    }
    
    // Sleep a tiny bit to be gentle on the sandbox API
    usleep(200000);
}
