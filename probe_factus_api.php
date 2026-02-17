<?php
require_once "modelos/factus.modelo.php";

echo "=== PROBING FACTUS API ===\n";
$token = ModeloFactus::mdlObtenerAccessToken();

if (!$token) {
    echo "Error: No Access Token available.\n";
    exit;
}

// Try to guess the endpoint for fiscal responsibilities
$endpoints = [
    '/v1/fiscal-responsibilities',
    '/v1/responsibilities',
    '/v1/reference-data/fiscal-responsibilities',
    '/v1/municipalities', // Control to see if API works
];

$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

foreach ($endpoints as $ep) {
    $url = $baseUrl . $ep;
    echo "\nTrying: $url\n";

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

    echo "Code: $code\n";
    if ($code == 200) {
        echo "Response excerpt: " . substr($res, 0, 200) . "...\n";
        file_put_contents("probe_response_" . str_replace('/', '_', $ep) . ".txt", $res);
    }
}
?>