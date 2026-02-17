<?php
/**
 * Script para descubrir los endpoints de datos de referencia (Municipios, Tributos, Unidades, etc.)
 */

require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

echo "<h1>📡 Test de Endpoints de Referencia Factus</h1>";
echo "<hr>";

// 1. Obtener Token
echo "<h2>1. Obteniendo Token</h2>";
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    echo "<p style='color:red'>❌ Error al obtener token: {$auth['mensaje']}</p>";
    exit;
}
$token = $auth['token'];
echo "<p style='color:green'>✅ Token obtenido correctamente</p>";

// 2. Definir endpoints de referencia a probar
$endpointsReferencia = [
    [
        'url' => '/v1/municipalities',
        'desc' => 'Municipios (v1/municipalities)'
    ],
    [
        'url' => '/v1/municipalities?page=1',
        'desc' => 'Municipios paginado'
    ],
    [
        'url' => '/v1/tributes',
        'desc' => 'Tributos (v1/tributes)'
    ],
    [
        'url' => '/v1/products/tributes',
        'desc' => 'Tributos de Productos (v1/products/tributes)'
    ],
    [
        'url' => '/v1/measurement-units',
        'desc' => 'Unidades de Medida (v1/measurement-units)'
    ],
    [
        'url' => '/v1/payment-methods',
        'desc' => 'Métodos de Pago (v1/payment-methods)'
    ],
    [
        'url' => '/v1/identification-types',
        'desc' => 'Tipos de Identificación (v1/identification-types)'
    ],
    [
        'url' => '/v1/countries',
        'desc' => 'Países (v1/countries)'
    ],
    [
        'url' => '/v1/numbering-ranges',
        'desc' => 'Rangos de Numeración (v1/numbering-ranges)'
    ]
];

$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

echo "<h2>2. Probando Endpoints de Referencia</h2>";
echo "<p><strong>Base URL:</strong> $baseUrl</p>";

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Endpoint</th><th>Descripción</th><th>Código HTTP</th><th>Muestra de Respuesta</th></tr>";

foreach ($endpointsReferencia as $ep) {
    $url = $baseUrl . $ep['url'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $color = ($httpCode >= 200 && $httpCode < 300) ? 'green' : (($httpCode == 404) ? 'red' : 'orange');

    // Formatear respuesta JSON si es posible
    $data = json_decode($response, true);
    $responsePreview = "";

    if ($data) {
        // Si hay data, mostrar solo el primer elemento o estructura básica
        if (isset($data['data']) && is_array($data['data'])) {
            $count = count($data['data']);
            $first = $count > 0 ? json_encode($data['data'][0], JSON_UNESCAPED_UNICODE) : '[]';
            $responsePreview = "Total: $count items. Ejemplo: " . substr($first, 0, 150) . "...";
        } else {
            $responsePreview = substr(json_encode($data, JSON_UNESCAPED_UNICODE), 0, 200) . "...";
        }
    } else {
        $responsePreview = substr(htmlspecialchars($response), 0, 100);
    }

    echo "<tr>";
    echo "<td>{$ep['url']}</td>";
    echo "<td>{$ep['desc']}</td>";
    echo "<td style='color:$color; font-weight:bold'>$httpCode</td>";
    echo "<td style='font-family:monospace; font-size:12px'>$responsePreview</td>";
    echo "</tr>";
}

echo "</table>";
?>