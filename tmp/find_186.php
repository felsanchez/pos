<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

if (!isset($_SESSION)) {
    session_start();
}

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error de autenticación: " . $auth['mensaje']);
}

$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$apiUrl = $config['api_url'];

$url = $apiUrl . "/v1/bills?page=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
));

$respuesta = curl_exec($ch);
curl_close($ch);

$data = json_decode($respuesta, true);
if (isset($data['data']['data'])) {
    foreach ($data['data']['data'] as $bill) {
        if (strpos($bill['number'], '186') !== false) {
            echo "BILL 186 DETAILS:\n";
            echo "ID: " . $bill['id'] . "\n";
            echo "Number: " . $bill['number'] . "\n";
            echo "Status: " . $bill['status'] . "\n";
            echo "Total: " . $bill['total'] . "\n";
            echo "Date: " . $bill['date'] . "\n";
            exit;
        }
    }
    echo "Bill 186 not found in first page.";
} else {
    print_r($data);
}
?>