<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$variants = [
    '/v1/notas-ajuste/validate',
    '/v1/notas-ajuste-ds/validate',
    '/v1/notas_ajuste/validate',
    '/v1/ajustes/validate',
    '/v1/ajuste-documento-soporte/validate',
    '/v1/notas-ajuste-documento-soporte/validate'
];

foreach ($variants as $path) {
    $url = $baseUrl . $path;
    echo "Testing POST to $url ... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    if ($httpCode == 422) {
        echo "   >>> POSSIBLE MATCH: $path\n";
    }
}
?>