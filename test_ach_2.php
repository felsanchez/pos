<?php
require 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
require 'c:/xampp/htdocs/pos/modelos/factus.modelo.php';
$res = ControladorFactus::ctrAutenticar();
$tk = $res['token'];

$ch = curl_init('https://api-sandbox.factus.com.co/v1/municipalities?name=Achi');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$o1 = curl_exec($ch);
curl_close($ch);

$ch2 = curl_init('https://api-sandbox.factus.com.co/v1/municipalities?name=Pedrera');
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$o2 = curl_exec($ch2);
curl_close($ch2);

$ch3 = curl_init('https://api-sandbox.factus.com.co/v1/municipalities?name=Calamar');
curl_setopt($ch3, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
$o3 = curl_exec($ch3);
curl_close($ch3);

file_put_contents('c:/xampp/htdocs/pos/factus_muns.json', json_encode(['Achi' => json_decode($o1), 'Pedrera' => json_decode($o2), 'Calamar' => json_decode($o3)], JSON_PRETTY_PRINT));
echo "DONE\n";
?>