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

$rangeId = 1190; // From previous ranges_response.txt
$newNumber = 189; // We want SETP990000189 to be the next one if possible, or just push it forward

$url = $apiUrl . "/v1/numbering-ranges/" . $rangeId;

// The payload for updating the range. 
// Usually Factus allows updating current_number in sandbox.
$payload = array(
    "current_number" => 990000189
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
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
echo "RESPONSE: " . $respuesta . "\n";
?>