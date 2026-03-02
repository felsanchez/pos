<?php
require 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
require 'c:/xampp/htdocs/pos/modelos/factus.modelo.php';
$res = ControladorFactus::ctrAutenticar();
$tk = $res['token'];

// Fetch available payment methods from Factus
$ch = curl_init('https://api-sandbox.factus.com.co/v1/payment-methods');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tk, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close($ch);

$decoded = json_decode($output, true);
file_put_contents('c:/xampp/htdocs/pos/factus_payment_methods.json', json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "DONE\n";
?>