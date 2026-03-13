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

$url = $apiUrl . "/v1/bills?page=1";

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
// Look for any bill with number containing 186
if (isset($data['data']['items'])) {
    foreach ($data['data']['items'] as $bill) {
        if (strpos($bill['number'], '186') !== false) {
            echo "MATCH FOUND:\n";
            print_r($bill);
        }
    }
} else {
    print_r($data);
}
?>