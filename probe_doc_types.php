<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$url = $baseUrl . '/v1/bills/validate';

$docTypes = ["01", "02", "03", "04", "05", "22", "23", "91", "92"];

foreach ($docTypes as $type) {
    echo "Probando Document Type: $type\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "document" => $type
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP CODE: $httpCode\n";
    if ($httpCode == 422) {
        $data = json_decode($response, true);
        if (isset($data['data']['errors']['document'])) {
            echo "RESULT: INVALID DOCUMENT TYPE\n";
        } else {
            echo "RESULT: VALID DOCUMENT TYPE (missing other fields)\n";
        }
    } else {
        echo "RESPONSE: $response\n";
    }
    echo "------------------------------------------\n";
}
