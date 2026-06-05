<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();

$url = $config['api_url'] . '/v1/measurement-units';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
));

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
$data = json_decode($respuesta, true);
if (isset($data['data'])) {
    foreach ($data['data'] as $unit) {
        echo "ID: {$unit['id']} | Code: {$unit['code']} | Name: {$unit['name']}\n";
    }
} else {
    echo "Response:\n" . $respuesta . "\n";
}
