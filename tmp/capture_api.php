<?php
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$rango = ModeloFactus::mdlObtenerRangoActivo(); // Using active logic
if (!$rango) {
    echo "No active range";
    exit;
}

$token = ModeloFactus::mdlObtenerAccessToken();
$config = ModeloFactus::mdlObtenerConfiguracion();
$url = $config['api_url'] . '/v1/numbering-ranges/' . $rango["id_factus"];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));
$res = curl_exec($ch);
curl_close($ch);

file_put_contents("c:\\xampp\\htdocs\\pos\\tmp\\raw_api_res.json", $res);
echo "Saved to raw_api_res.json\n";
?>