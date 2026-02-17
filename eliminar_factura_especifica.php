<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

if (!isset($_GET['ref'])) {
    die("❌ No se especificó reference_code");
}

$referenceCode = $_GET['ref'];

echo "<h2>Eliminar Factura: $referenceCode</h2>";

// 1. Obtener configuración y token
$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_config LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    die("❌ No hay configuración de Factus");
}

// Obtener token
$tokenData = [
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'grant_type' => 'client_credentials'
];

$ch = curl_init($config['api_url'] . '/oauth/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tokenData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$authResponse = json_decode($response, true);
$token = $authResponse['access_token'] ?? null;

if (!$token) {
    die("❌ No se pudo obtener token");
}

echo "<p>✅ Token obtenido</p>";

echo "<h3>⚠️ ADVERTENCIA</h3>";
echo "<p>¿Está seguro de que desea eliminar la factura <strong>$referenceCode</strong>?</p>";

echo "<form method='POST'>";
echo "<button type='submit' name='confirmar' value='si' style='background:red; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>SÍ, Eliminar</button>";
echo " ";
echo "<a href='listar_facturas_factus.php' style='background:gray; color:white; padding:10px 20px; font-size:16px; text-decoration:none; display:inline-block;'>Cancelar</a>";
echo "</form>";

if (isset($_POST['confirmar']) && $_POST['confirmar'] == 'si') {
    echo "<hr>";
    echo "<h3>Ejecutando eliminación...</h3>";

    // Hacer DELETE
    $url = $config['api_url'] . '/v1/bills/' . urlencode($referenceCode);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
    echo "<p><strong>Respuesta:</strong></p>";
    echo "<pre>$response</pre>";

    if ($httpCode == 200) {
        echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin:10px 0;'>";
        echo "<h3 style='color:#155724;'>✅ Factura Eliminada</h3>";
        echo "<p><a href='listar_facturas_factus.php'>Volver al listado</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; border:1px solid #f5c6cb; padding:15px; margin:10px 0;'>";
        echo "<h3 style='color:#721c24;'>❌ Error al Eliminar</h3>";
        echo "<p><a href='listar_facturas_factus.php'>Volver al listado</a></p>";
        echo "</div>";
    }
}
