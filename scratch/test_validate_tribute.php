<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Load sale 12 as base payload
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = 12");
$stmt->execute();
$venta = $stmt->fetch();
$baseFactura = ControladorFactus::prepararDatosFactura($venta);

// Test tribute IDs
$tributeTests = [
    ["tribute_id" => 1, "tax_rate" => "0.00", "is_excluded" => 1, "desc" => "tribute_id = 1, tax_rate = 0.00, is_excluded = 1"],
    ["tribute_id" => 1, "tax_rate" => "0.00", "is_excluded" => 0, "desc" => "tribute_id = 1, tax_rate = 0.00, is_excluded = 0"],
    ["tribute_id" => 5, "tax_rate" => "0.00", "is_excluded" => 0, "desc" => "tribute_id = 5, tax_rate = 0.00, is_excluded = 0"],
];

foreach ($tributeTests as $index => $test) {
    echo "--- Test " . ($index + 1) . ": " . $test['desc'] . " ---\n";
    $payload = $baseFactura;
    $payload['reference_code'] = "TEST-TRIB-EXC-" . $index . "-" . time();
    
    // Modify item tribute
    $payload['items'][0]['tribute_id'] = $test['tribute_id'];
    $payload['items'][0]['tax_rate'] = $test['tax_rate'];
    $payload['items'][0]['is_excluded'] = $test['is_excluded'];
    
    // Call API
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    $parsed = json_decode($respuesta, true);
    if ($parsed) {
        echo "Status: " . ($parsed['status'] ?? 'N/A') . "\n";
        echo "Message: " . ($parsed['message'] ?? 'N/A') . "\n";
        if (isset($parsed['errors'])) {
            print_r($parsed['errors']);
        }
        if (isset($parsed['data']['errors'])) {
            print_r($parsed['data']['errors']);
        }
    } else {
        echo "Raw: " . substr($respuesta, 0, 300) . "\n";
    }
    echo "\n";
}
