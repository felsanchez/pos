<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

if (!isset($_SESSION)) {
    session_start();
}

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error de autenticación: " . $auth['mensaje']);
}

$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$apiUrl = $config['api_url'];

$billId = 33784; // The ID of the pending invoice 187
$url = $apiUrl . "/v1/bills/validate/" . $billId;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Validation requires POST usually
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
));

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: " . $httpCode . "\n";
print_r(json_decode($respuesta, true));
?>