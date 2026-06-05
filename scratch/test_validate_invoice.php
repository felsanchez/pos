<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$idVenta = 12;

// 1. Autenticar
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Autenticacion fallida: " . $auth['mensaje']);
}
$token = $auth['token'];

// 2. Obtener venta
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = :id");
$stmt->execute([':id' => $idVenta]);
$venta = $stmt->fetch();

if (!$venta) {
    die("No se encontro la venta $idVenta");
}

// 3. Preparar datos
$datosFactura = ControladorFactus::prepararDatosFactura($venta);

// Let's print the customer block specifically
echo "=== CUSTOMER BLOCK ===\n";
print_r($datosFactura['customer']);
echo "\n";

echo "=== ITEMS BLOCK ===\n";
print_r($datosFactura['items']);
echo "\n";

$apiUrl = "https://api-sandbox.factus.com.co";
$url = $apiUrl . '/v1/bills/validate';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, ''); // Automatically handles decompression
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosFactura));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "=== RESPUESTA DE VALIDACIÓN COMPLETA ===\n";
echo "HTTP CODE: " . $httpCode . "\n";
if ($curlError) {
    echo "CURL Error: " . $curlError . "\n";
} else {
    file_put_contents('scratch/validate_res.json', $respuesta);
    echo "Respuesta guardada en scratch/validate_res.json\n";
    $parsed = json_decode($respuesta, true);
    if ($parsed) {
        echo "Status: " . ($parsed['status'] ?? 'N/A') . "\n";
        echo "Message: " . ($parsed['message'] ?? 'N/A') . "\n";
        if (isset($parsed['errors'])) {
            echo "Errors found:\n";
            print_r($parsed['errors']);
        }
        if (isset($parsed['data']['errors'])) {
            echo "Data Errors found:\n";
            print_r($parsed['data']['errors']);
        }
    } else {
        echo "No se pudo parsear como JSON. Raw: " . substr($respuesta, 0, 500) . "\n";
    }
}
