<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$targets = [
    '/v1/reference/types-documents',
    '/v1/reference/correction-concepts',
    '/v1/reference/operations-types'
];

foreach ($targets as $path) {
    $url = $baseUrl . $path;
    echo "GET $url ... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        file_put_contents(basename($path) . ".json", $response);
        echo "   Saved " . count($data['data'] ?? []) . " items to " . basename($path) . ".json\n";
    } else {
        echo "   Response: $response\n";
    }
}
?>