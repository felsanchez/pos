<?php
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$token = ModeloFactus::mdlObtenerAccessToken();
$config = ModeloFactus::mdlObtenerConfiguracion();
$url = $config['api_url'] . '/v1/numbering-ranges';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));
$res = curl_exec($ch);
curl_close($ch);

file_put_contents("c:\\xampp\\htdocs\\pos\\tmp\\all_api_ranges.json", $res);
echo "Saved to all_api_ranges.json\n";
?>