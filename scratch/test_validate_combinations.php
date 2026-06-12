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
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = 163");
$stmt->execute();
$venta = $stmt->fetch();
$baseFactura = ControladorFactus::prepararDatosFactura($venta);

// Test combinations
$combinations = [
    // 1. Natural, no responsabilidades, tribute 21 (Simplificado/No responsable)
    [
        "name" => "Natural No Responsable (tribute_id=21, organization=2, resp=R-99-PN)",
        "legal_organization_id" => "2",
        "tribute_id" => "21",
        "fiscal_responsibilities" => [["code" => "R-99-PN"]]
    ],
    // 2. Natural, responsable de IVA (tribute 18, organization=2, resp=O-23)
    [
        "name" => "Natural con O-23 y tribute_id=18 (organization=2)",
        "legal_organization_id" => "2",
        "tribute_id" => "18",
        "fiscal_responsibilities" => [["code" => "O-23"]]
    ],
    // 3. Natural con O-23 y tribute_id=21 (organization=2)
    [
        "name" => "Natural con O-23 y tribute_id=21 (organization=2)",
        "legal_organization_id" => "2",
        "tribute_id" => "21",
        "fiscal_responsibilities" => [["code" => "O-23"]]
    ],
    // 4. Juridica, tribute 18, organization 1, resp O-23 (Juridica normal)
    [
        "name" => "Juridica con O-23 y tribute_id=18 (organization=1)",
        "legal_organization_id" => "1",
        "tribute_id" => "18",
        "fiscal_responsibilities" => [["code" => "O-23"]]
    ],
    // 5. Juridica con O-23 y tribute_id=21 (organization=1)
    [
        "name" => "Juridica con O-23 y tribute_id=21 (organization=1)",
        "legal_organization_id" => "1",
        "tribute_id" => "21",
        "fiscal_responsibilities" => [["code" => "O-23"]]
    ],
];

foreach ($combinations as $index => $comb) {
    echo "--- Test " . ($index + 1) . ": " . $comb['name'] . " ---\n";
    $payload = $baseFactura;
    // Set simulated reference code to avoid 409 conflict
    $payload['reference_code'] = "TEST-COMB-" . $index . "-" . time();
    
    // Modify customer
    $payload['customer']['legal_organization_id'] = $comb['legal_organization_id'];
    $payload['customer']['tribute_id'] = $comb['tribute_id'];
    $payload['customer']['fiscal_responsibilities'] = $comb['fiscal_responsibilities'];
    
    // Call API
    $apiUrl = "https://api-sandbox.factus.com.co";
    $url = $apiUrl . '/v1/bills/validate';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
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
