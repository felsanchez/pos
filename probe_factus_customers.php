<?php
require_once "modelos/factus.modelo.php";

echo "=== PROBING /v1/customers ===\n";
$token = ModeloFactus::mdlObtenerAccessToken();

if (!$token) {
    echo "Error: No Access Token.\n";
    exit;
}

$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];
$url = $baseUrl . '/v1/customers';

echo "Target: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));

$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response Code: $code\n";
echo "Response Body Start: " . substr($res, 0, 500) . "...\n";

if ($code == 200) {
    echo "Endpoint EXISTS and returns data.\n";
    file_put_contents("probe_customers_list.txt", $res);
} else {
    echo "Endpoint failed or does not support GET list.\n";
}
?>