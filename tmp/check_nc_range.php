<?php
session_start();
require_once '../modelos/factus.modelo.php';
require_once '../modelos/conexion.php';

// Obtener rango NC
$rango = ModeloFactus::mdlObtenerRangoNC();
echo "=== RANGO NC LOCAL ===\n";
print_r($rango);

if (!$rango) {
    die("No hay rango NC configurado.\n");
}

$rangoId = $rango['id_factus'];
$token = ModeloFactus::mdlObtenerAccessToken();

if (!$token) {
    die("No hay token de acceso disponible.\n");
}

$config = ModeloFactus::mdlObtenerConfiguracion();
$url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

echo "\n=== CONSULTANDO API: $url ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode\n";
$json = json_decode($res, true);
echo "=== RESPUESTA API ===\n";
print_r($json);

// Verificar cuál campo tiene el número actual
echo "\n=== ANÁLISIS ===\n";
if (isset($json['data']['current'])) {
    echo "Campo 'current': " . $json['data']['current'] . "\n";
}
if (isset($json['data']['current_number'])) {
    echo "Campo 'current_number': " . $json['data']['current_number'] . "\n";
}

// Lo que calcula mdlObtenerSiguienteConsecutivoNC
$siguienteApiReal = null;
if (isset($json['data']['current'])) {
    $siguienteApiReal = intval($json['data']['current']) + 1;
    echo "\nSiguiente calculado (current + 1): " . $siguienteApiReal . "\n";
} elseif (isset($json['data']['current_number'])) {
    $siguienteApiReal = intval($json['data']['current_number']) + 1;
    echo "\nSiguiente calculado (current_number + 1): " . $siguienteApiReal . "\n";
}
echo "\nNúmero esperado por Factus para la próxima NC: NC" . $siguienteApiReal . "\n";
?>