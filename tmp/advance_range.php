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

// 1. Fetch active range
$url = $apiUrl . "/v1/numbering-ranges";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));
$respuesta = curl_exec($ch);
curl_close($ch);

$data = json_decode($respuesta, true);
echo "RAW RESPONSE WRITTEN TO tmp/ranges_response.txt\n";
file_put_contents('tmp/ranges_response.txt', print_r($data, true));
$rangeId = null;
$currentNumber = null;

if (isset($data['data'])) {
    foreach ($data['data'] as $range) {
        if ($range['is_active'] == 1) {
            $rangeId = $range['id'];
            $currentNumber = $range['current_number'];
            break;
        }
    }
}

if (!$rangeId) {
    die("No active range found.");
}

echo "Active Range ID: " . $rangeId . "\n";
echo "Current Number in API: " . $currentNumber . "\n";

// We know 187 is stuck, so we want the active range to be ready to issue 188.
// The Factus API for ranges doesn't always allow manual advancement via a simple endpoint 
// but we will try updating the range or looking for an undocumented endpoint.
// Actually, since this is a local blockage based on Factus's strict sequence rules for validation,
// another option is to just retry the creation locally with the updated TAX formula.
?>