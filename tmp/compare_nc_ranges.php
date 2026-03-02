<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

$token = ModeloFactus::mdlObtenerAccessToken();
$config = ModeloFactus::mdlObtenerConfiguracion();

function getRange($id, $token, $apiUrl)
{
    $url = $apiUrl . '/v1/numbering-ranges/' . $id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token, 'Accept: application/json'));
    $res = curl_exec($ch);
    $json = json_decode($res, true);
    curl_close($ch);
    return $json['data'] ?? $json;
}

echo "RANGE 1041:\n";
print_r(getRange(1041, $token, $config['api_url']));
echo "\nRANGE 1191:\n";
print_r(getRange(1191, $token, $config['api_url']));
?>