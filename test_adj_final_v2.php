<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$idDS = 28; // Hardcoded for test
$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$originalDS = ModeloFactus::mdlMostrarDocumentosSoporte("id", $idDS);
$rangoId = 1193; // Forcing range 1193

$productosAjuste = json_decode($originalDS["productos"], true);
$items = [];
foreach ($productosAjuste as $p) {
    $items[] = [
        "code_reference" => $p["id"],
        "name" => $p["descripcion"],
        "quantity" => intval($p["cantidad"]),
        "discount_rate" => 0,
        "price" => floatval($p["precio"] ?? $p["precio_venta_ds"] ?? 100),
        "tax_rate" => "0.00",
        "unit_measure_id" => 70,
        "standard_code_id" => 1,
        "is_excluded" => 1,
        "tribute_id" => 7
    ];
}

$payload = [
    "support_document_id" => intval($originalDS["factus_id"]),
    "numbering_range_id" => intval($rango['id_factus']),
    "reference_code" => "NA-" . $originalDS["numero_ds"] . "-" . time(),
    "billing_reference" => [
        "number" => $originalDS["numero_ds"],
        "uuid" => $originalDS["cuds"],
        "issue_date" => date('Y-m-d', strtotime($originalDS["fecha_emision"]))
    ],
    "correction_concept_code" => "2",
    "observation" => "Prueba producción final",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "items" => $items
];

$url = ModeloFactus::mdlObtenerConfiguracion()['api_url'] . "/v1/adjustment-notes/validate";

echo "Enviando a $url ...\n";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: $httpCode\n";
echo "Response: $response\n";
?>