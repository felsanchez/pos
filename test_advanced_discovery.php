<?php
/**
 * Script Avanzado para descubrir rutas de API Factus
 */

require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

echo "<h1>🕵️ Test de Descubrimiento de Rutas Factus</h1>";
echo "<hr>";

// 1. Obtener Token
echo "<h2>1. Token</h2>";
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("<p style='color:red'>❌ Error Token: {$auth['mensaje']}</p>");
}
$token = $auth['token'];
echo "<p style='color:green'>✅ Token OK. Length: " . strlen($token) . "</p>";

// 2. Variaciones de Rutas a Probar
$base_paths = [
    '',                 // Directo: https://api.factus.com.co/v1/...
    '/api',             // https://api.factus.com.co/api/v1/...
    '/public',          // https://api.factus.com.co/public/v1/...
    '/rest',
    '/ws'
];

$resources = [
    'municipalities' => ['GET', 'municipality_id'], // Recurso común
    'numbering-ranges' => ['GET', 'id'],            // Rangos (crítico)
    'bills/validate' => ['POST', '']                // Creación
];

$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = rtrim($config['api_url'], '/');

echo "<h2>2. Probando Prefijos y Rutas</h2>";
echo "<p>Base URL Configurada: <strong>$baseUrl</strong></p>";

echo "<table border='1' cellpadding='5' style='width:100%'>";
echo "<tr style='background:#ddd'><th>Ruta Completa</th><th>Code</th><th>Respuesta</th></tr>";

foreach ($base_paths as $prefix) {
    echo "<tr><td colspan='3' style='background:#eee'><strong>Prefijo: $prefix</strong></td></tr>";

    foreach ($resources as $res => $info) {
        // Construir URL: Base + Prefijo + /v1/ + Recurso
        // Ej: https://api.factus.com.co/api/v1/municipalities
        $path = $prefix . '/v1/' . $res;
        $url = $baseUrl . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Headers estándar
        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Método
        if ($info[0] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{}'); // Body vacío para validar auth
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $color = ($httpCode >= 200 && $httpCode < 300) ? 'green' : (($httpCode == 401) ? 'orange' : 'red');
        $shortResp = substr(htmlspecialchars($response), 0, 100);

        echo "<tr>";
        echo "<td>$path</td>";
        echo "<td style='color:$color'><b>$httpCode</b></td>";
        echo "<td style='font-size:11px'>$shortResp</td>";
        echo "</tr>";
    }
}
echo "</table>";
?>