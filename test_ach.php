<?php
require 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
require 'c:/xampp/htdocs/pos/modelos/factus.modelo.php';
$res = ControladorFactus::ctrAutenticar();
$tk = $res['token'];

$ch = curl_init('https://api-sandbox.factus.com.co/v1/municipalities?name=Achi');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close($ch);
echo "Achi: " . $output . "\n\n";

$ch2 = curl_init('https://api-sandbox.factus.com.co/v1/municipalities?name=Pedrera');
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$output2 = curl_exec($ch2);
curl_close($ch2);
echo "Pedrera: " . $output2 . "\n\n";

$ch3 = curl_init('https://api-sandbox.factus.com.co/v1/municipalities?name=Calamar');
curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
$output3 = curl_exec($ch3);
curl_close($ch3);
echo "Calamar: " . $output3 . "\n\n";
?>