<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$idVenta = 58;

// 1. Autenticar
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// 2. Obtener venta
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = :id");
$stmt->execute([':id' => $idVenta]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die("No se encontro la venta $idVenta\n");
}

// 3. Preparar datos
$datosFactura = ControladorFactus::prepararDatosFactura($venta);

// Let's force a unique reference code to avoid 409 conflict if 58 was already sent
$datosFactura['reference_code'] = "TEST-BAGS-NEW-" . time();

echo "=== SENDING PAYLOAD ===\n";
echo json_encode($datosFactura, JSON_PRETTY_PRINT) . "\n\n";

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosFactura));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "=== API RESPONSE ===\n";
echo "HTTP CODE: " . $httpCode . "\n";
if ($curlError) {
    echo "CURL Error: " . $curlError . "\n";
} else {
    echo "Raw: " . $respuesta . "\n";
}
