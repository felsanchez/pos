<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Auth error: " . $auth['mensaje']);
}
$token = $auth['token'];

$rango = ModeloFactus::mdlObtenerRangoActivo();
if (!$rango) {
    die("No active range in DB");
}
$rangoId = $rango["id_factus"];

$config = ModeloFactus::mdlObtenerConfiguracion();
$url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));
$res = curl_exec($ch);
curl_close($ch);

echo "API Response for range $rangoId:\n";
echo $res . "\n\n";

$json = json_decode($res, true);
if (isset($json['data'])) {
    echo "Current in API: " . ($json['data']['current'] ?? $json['data']['current_number'] ?? 'N/A') . "\n";
}
