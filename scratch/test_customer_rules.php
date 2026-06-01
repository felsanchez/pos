<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = 12");
$stmt->execute();
$venta = $stmt->fetch();
$baseFactura = ControladorFactus::prepararDatosFactura($venta);

$tests = [
    [
        "desc" => "Natural with company name filled (normally invalid under DIAN rules, let's see if Factus API rejects it)",
        "legal_organization_id" => "2",
        "company" => "Luisa Fernanda SAS",
        "names" => "Luisa Fernanda",
        "tribute_id" => "21",
        "fiscal_responsibilities" => [["code" => "R-99-PN"]]
    ],
    [
        "desc" => "Natural with empty company name (correct under DIAN rules)",
        "legal_organization_id" => "2",
        "company" => "",
        "names" => "Luisa Fernanda",
        "tribute_id" => "21",
        "fiscal_responsibilities" => [["code" => "R-99-PN"]]
    ],
    [
        "desc" => "Juridica with empty company name (normally invalid under DIAN rules, company is required for Juridica)",
        "legal_organization_id" => "1",
        "company" => "",
        "names" => "Luisa Fernanda",
        "tribute_id" => "18",
        "fiscal_responsibilities" => [["code" => "O-23"]]
    ],
    [
        "desc" => "Juridica with company name filled (correct under DIAN rules)",
        "legal_organization_id" => "1",
        "company" => "Luisa Fernanda SAS",
        "names" => "Luisa Fernanda",
        "tribute_id" => "18",
        "fiscal_responsibilities" => [["code" => "O-23"]]
    ]
];

foreach ($tests as $i => $t) {
    echo "=== Test " . ($i + 1) . ": " . $t['desc'] . " ===\n";
    $payload = $baseFactura;
    $payload['reference_code'] = "TEST-RULES-" . $i . "-" . time();
    $payload['customer']['legal_organization_id'] = $t['legal_organization_id'];
    $payload['customer']['company'] = $t['company'];
    $payload['customer']['names'] = $t['names'];
    $payload['customer']['tribute_id'] = $t['tribute_id'];
    $payload['customer']['fiscal_responsibilities'] = $t['fiscal_responsibilities'];

    $apiUrl = "https://api-sandbox.factus.com.co";
    $url = $apiUrl . '/v1/bills/validate';

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    $res = json_decode($response, true);
    if ($res) {
        echo "Status: " . ($res['status'] ?? 'N/A') . "\n";
        echo "Message: " . ($res['message'] ?? 'N/A') . "\n";
        if (isset($res['errors'])) {
            print_r($res['errors']);
        }
        if (isset($res['data']['errors'])) {
            print_r($res['data']['errors']);
        }
    } else {
        echo "Raw: " . substr($response, 0, 300) . "\n";
    }
    echo "\n";
}
