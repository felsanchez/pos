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
    $foundOptions = [];
    foreach ($data['data']['data'] as $bill) {
        // Find bills that are NOT status 1 (validated)
        if ($bill['status'] != 1) {
            $foundOptions[] = [
                'id' => $bill['id'],
                'number' => $bill['number'],
                'status' => $bill['status'],
            ];
        }
    }

    if (count($foundOptions) > 0) {
        echo "PENDING / UNVALIDATED BILLS FOUND:\n";
        print_r($foundOptions);
    } else {
        echo "No pending bills found on the first page.\n";
    }
} else {
    print_r($data);
}
?>