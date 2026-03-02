<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

$token = ModeloFactus::mdlObtenerAccessToken();
$config = ModeloFactus::mdlObtenerConfiguracion();
$url = $config['api_url'] . '/v1/credit-notes';

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

echo "FACTUS API NC LIST:\n";
if (isset($json['data']['data'])) {
    foreach ($json['data']['data'] as $nc) {
        echo "ID: " . $nc['id'] . " | Number: " . $nc['number'] . " | Bill: " . ($nc['bill_number'] ?? 'N/A') . " | Range: " . ($nc['numbering_range_id'] ?? 'N/A') . "\n";
    }
} else {
    print_r($json);
}
?>