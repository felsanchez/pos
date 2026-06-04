<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$token = $config['access_token'];
$apiUrl = $config['api_url'];

$id = 54684;
$endpoint = '/v1/bills/' . $id;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $apiUrl . $endpoint,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_TIMEOUT => 10,
  CURLOPT_CUSTOMREQUEST => 'DELETE',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
  ),
));

$response = curl_exec($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "Endpoint: $endpoint | HTTP Code: $httpcode\n";
echo "Response: \n" . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";
