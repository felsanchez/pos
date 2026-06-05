<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$token = $config['access_token'];
$apiUrl = $config['api_url'];

$endpoints = [
    '/v1/tribute',
    '/v1/tributes',
    '/v1/tribute-type',
    '/v1/tribute-types',
    '/v1/tribute_type',
    '/v1/tribute_types',
    '/v1/tax',
    '/v1/taxes',
    '/v1/tax-type',
    '/v1/tax-types',
    '/v1/tax_type',
    '/v1/tax_types',
    '/v1/reference-data/tribute',
    '/v1/reference-data/tributes',
    '/v1/reference-data/tax',
    '/v1/reference-data/taxes',
    '/v1/catalogs/tribute',
    '/v1/catalogs/tributes',
    '/v1/catalogs/tax',
    '/v1/catalogs/taxes',
    '/v1/tributes-products',
    '/v1/tributes_products',
    '/v1/tribute-products',
    '/v1/tribute_products',
    '/v1/document-types',
    '/v1/document_types',
    '/v1/document-type',
    '/v1/document_type',
    '/v1/tributos',
    '/v1/tributo',
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
    }
}
echo "Search complete.\n";
