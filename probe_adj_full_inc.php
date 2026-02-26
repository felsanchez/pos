<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$url = $baseUrl . "/v1/adjustment-notes/validate";

$payload = [
    "numbering_range_id" => 1193,
    "support_document_id" => 498,
    "correction_concept_code" => "2",
    "billing_reference" => [
        "number" => "SEDS984000028",
        "uuid" => "4fed7d240941c93cd3c4d4c251f96b340c70db8f3a976de4b283593fd3600b610072e480ff133a1df7b5a91199e1a659",
        "issue_date" => "2026-02-24"
    ],
    "reference_code" => "NA-INC-" . time(),
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "items" => [
        [
            "code_reference" => "117",
            "name" => "prueba",
            "quantity" => 1,
            "discount_rate" => 0,
            "price" => 100,
            "tax_rate" => "0.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 1,
            "tribute_id" => 7
        ]
    ]
];

echo "Testing Full Payload ... ";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json', 'Accept: application/json']);
$resp = curl_exec($ch);
$data = json_decode($resp, true);

if (isset($data['data']['errors']['numbering_range_id'])) {
    echo "FAILED (Range Invalid)\n";
} else {
    echo "OK\n";
}
echo "Full Response: " . $resp . "\n";
curl_close($ch);
?>