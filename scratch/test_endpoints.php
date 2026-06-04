<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$token = $config['access_token'];
$apiUrl = $config['api_url'];

$endpoints = [
    '/v1/tribute',
    '/v1/tributes/products',
    '/v1/tributes-products',
    '/v1/tributes_products',
    '/v1/tributes/items',
    '/v1/tribute_items',
    '/v1/tribute-items',
    '/v1/tribute/items',
    '/v1/tribute/products',
    '/v1/tributos',
    '/v1/tributos-productos',
    '/v1/tributos/productos',
    '/v1/taxes',
    '/v1/tax-types',
    '/v1/tax_types',
    '/v1/tax-names',
    '/v1/tax_names',
    '/v1/tribute-names',
    '/v1/tributes'
];

foreach ($endpoints as $endpoint) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $apiUrl . $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 5,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
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
        echo "=== $endpoint ===\n";
        echo "HTTP Code: " . $httpcode . "\n";
        echo "Response: " . substr($response, 0, 1000) . "\n\n";
    }
}
echo "Done.\n";
