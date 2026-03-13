<?php
require_once 'modelos/factus.modelo.php';
$config = ModeloFactus::mdlObtenerConfiguracion();
$token = ModeloFactus::mdlObtenerAccessToken();
$rangoId = $config['rango_numeracion_id'];

$url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));
$res = curl_exec($ch);
$json = json_decode($res, true);
curl_close($ch);

echo "API RESPONSE FOR RANGE:\n";
print_r($json['data']);
?>