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
$payload = ControladorFactus::prepararDatosFactura($venta);

// Simulate Combination 4 (Juridica, tribute_id=18, O-23)
$payload['reference_code'] = "TEST-COMB-4-SINGLE-" . time();
$payload['customer']['legal_organization_id'] = "1";
$payload['customer']['tribute_id'] = "18";
$payload['customer']['fiscal_responsibilities'] = [["code" => "O-23"]];
// Juridica needs company name
$payload['customer']['company'] = "Empresa de Prueba S.A.S.";

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

echo "Sending payload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

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
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP CODE: $httpCode\n";
echo "Curl Error: $curlError\n";
echo "Response: $respuesta\n";
