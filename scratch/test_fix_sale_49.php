<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// Load sale 49
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = 49");
$stmt->execute();
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die("Sale 49 not found!\n");
}

// Temporarily mock the code fix in our test script:
// We will call preparing, then modify tribute_id from 0 to 5, and validate.
$payload = ControladorFactus::prepararDatosFactura($venta);

echo "ORIGINAL Prepared Payload (before fixing controller code):\n";
echo "Item tribute_id: " . $payload['items'][0]['tribute_id'] . "\n\n";

// Fix it in the payload
$payload['items'][0]['tribute_id'] = 5; // IVA Excluido database ID
$payload['reference_code'] = "TEST-FIX-49-" . time();

echo "Sending validate request to Factus Sandbox API with tribute_id = 5...\n";
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
