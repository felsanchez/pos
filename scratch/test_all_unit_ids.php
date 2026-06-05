<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

// 1. Autenticar
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// 2. Obtener venta id 12
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = 12");
$stmt->execute();
$venta = $stmt->fetch();

if (!$venta) {
    die("No se encontro la venta 12");
}

$datosFactura = ControladorFactus::prepararDatosFactura($venta);

// Vamos a probar diferentes unit_measure_id
$idsAProbar = [70, 414, 449, 499, 512, 874, 880];

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

foreach ($idsAProbar as $id) {
    $datosFactura['items'][0]['unit_measure_id'] = $id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosFactura));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $parsed = json_decode($respuesta, true);
    $status = $parsed['status'] ?? 'N/A';
    $message = $parsed['message'] ?? 'N/A';
    
    $unitError = "";
    if (isset($parsed['errors']['items.0.unit_measure_id'])) {
        $unitError = implode(", ", $parsed['errors']['items.0.unit_measure_id']);
    } elseif (isset($parsed['data']['errors']['items.0.unit_measure_id'])) {
        $unitError = implode(", ", $parsed['data']['errors']['items.0.unit_measure_id']);
    }
    
    echo "ID: $id | HTTP Code: $httpCode | Status: $status | Message: $message | Unit Error: " . ($unitError ?: "NINGUNO") . "\n";
}
