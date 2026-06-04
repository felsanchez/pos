<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$token = $config['access_token'];
$apiUrl = $config['api_url'];

$endpoints = [
    '/v1/tributes',
    '/v1/tribute-types',
    '/v1/tribute_types',
    '/v1/taxes',
    '/v1/tax-types',
    '/v1/catalogs/tributes',
    '/v1/catalogs/taxes',
    '/v1/catalogs',
    '/v1/tributes-products',
    '/v1/reference-data/tributes',
];

foreach ($endpoints as $endpoint) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $apiUrl . $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_TIMEOUT => 5,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
      ),
    ));

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpcode !== 404) {
        echo "FOUND! Endpoint: $endpoint | HTTP Code: $httpcode\n";
        echo "Response: " . substr($response, 0, 500) . "\n\n";
    } else {
        echo "404 for $endpoint\n";
    }
}
