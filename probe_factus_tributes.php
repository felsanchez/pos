<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$config = ModeloFactus::mdlObtenerConfiguracion();
$token = ModeloFactus::mdlObtenerAccessToken();
$url = $config['api_url'] . '/v1/bills/validate';

// Base payload (valid except for tribute_id)
$basePayload = [
    "numbering_range_id" => 1190,
    "reference_code" => "TESTPROBE" . time(),
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "operation_type" => 10,
    "establishment" => [
        "name" => "Establecimiento Prueba",
        "address" => "Direccion",
        "phone_number" => "1234567890",
        "email" => "test@test.com",
        "municipality_id" => "27",
        "legal_organization_id" => "2",
        "fiscal_responsibilities" => [["code" => "R-99-PN"]]
    ],
    "customer" => [
        "identification" => "4091451",
        "dv" => "0",
        "names" => "Cliente Prueba",
        "address" => "Direccion",
        "email" => "cliente@test.com",
        "phone" => "1234567890",
        "legal_organization_id" => "1", // Juridica
        "municipality_id" => "694",
        "identification_document_id" => 3,
        "fiscal_responsibilities" => [["code" => "O-23"]], // Responsible
        "tribute_id" => "PLACEHOLDER"
    ],
    "items" => [
        [
            "code_reference" => "ITEM-1",
            "name" => "Prueba",
            "quantity" => 1,
            "price" => 1000,
            "tax_rate" => "19.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 0,
            "tribute_id" => 1
        ]
    ]
];

echo "Probing Tribute IDs 1 to 30...\n";

for ($id = 1; $id <= 30; $id++) {
    $basePayload['customer']['tribute_id'] = (string) $id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($basePayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);
    $errorMsg = $json['data']['errors']['customer.tribute_id'][0] ?? null;
    $status = $json['status'] ?? 'Unknown';

    if ($errorMsg == "El campo ID tributo es inválido.") {
        // Invalid, ignore
        // echo "ID $id: Invalid\n";
    } else {
        echo ">>> CANDIDATE FOUND: ID $id <<<\n";
        echo "HTTP: $httpCode | Status: $status\n";
        echo "Response: " . substr($response, 0, 200) . "...\n\n";
    }
}
?>