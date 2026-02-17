<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$config = ModeloFactus::mdlObtenerConfiguracion();
$token = ModeloFactus::mdlObtenerAccessToken();

if (!$token) {
    die("Error: No access token available.\n");
}

$url = $config['api_url'] . '/v1/tributes?name='; // Fetch all

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));

echo "Fetching tributes from: $url\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    $tributes = $data['data'] ?? [];

    echo "Found " . count($tributes) . " tributes.\n";
    foreach ($tributes as $t) {
        echo "ID: " . $t['id'] . " | Code: " . $t['code'] . " | Name: " . $t['name'] . "\n";
    }
} else {
    echo "Error fetching tributes. HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}
?>