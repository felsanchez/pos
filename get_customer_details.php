<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

$id = "1118850555"; // Ana milena
$token = ModeloFactus::mdlObtenerAccessToken();

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api-sandbox.factus.com.co/v1/customers?identification=' . $id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $token, 'Accept: application/json'),
));

$response = curl_exec($curl);
echo "Response: $response\n";
curl_close($curl);
?>