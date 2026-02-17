<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Eliminar Factura Pendiente - Endpoint Correcto</h2>";

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

// 2. Buscar ventas recientes con código 10 y 11
$stmt = $db->query("SELECT DISTINCT codigo FROM ventas WHERE codigo IN (10, 11) ORDER BY codigo");
$codigos = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "<h3>Facturas a Eliminar:</h3>";
echo "<p>Se intentarán eliminar las facturas con los siguientes códigos de referencia:</p>";
echo "<ul>";
foreach ($codigos as $codigo) {
    echo "<li><strong>VENTA-$codigo</strong></li>";
}
echo "</ul>";

echo "<form method='POST'>";
echo "<p><strong>⚠️ ADVERTENCIA:</strong> Esto eliminará las facturas pendientes en Factus.</p>";
echo "<button type='submit' name='eliminar' value='si' style='background:red; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>ELIMINAR Facturas Pendientes</button>";
echo " ";
echo "<button type='button' onclick='window.history.back()' style='background:gray; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>Cancelar</button>";
echo "</form>";

if (isset($_POST['eliminar']) && $_POST['eliminar'] == 'si') {
    echo "<hr>";
    echo "<h3>Ejecutando eliminación...</h3>";
    
    foreach ($codigos as $codigo) {
        $referenceCode = "VENTA-$codigo";
        
        echo "<h4>Eliminando: $referenceCode</h4>";
        
        // ENDPOINT CORRECTO según documentación
        $url = $config['api_url'] . '/v1/bills/destroy/reference/' . urlencode($referenceCode);
        
        echo "<p>URL: $url</p>";
        
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
            echo "<p style='color:green;'>✅ Eliminada exitosamente</p>";
        } elseif ($httpCode == 404) {
            echo "<p style='color:orange;'>⚠️ No existe en Factus (ya fue eliminada o nunca se creó)</p>";
        } else {
            echo "<p style='color:red;'>❌ Error al eliminar</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin:10px 0;'>";
    echo "<h3 style='color:#155724;'>✅ Proceso Completado</h3>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ol>";
    echo "<li>Vaya a <strong>Crear Factura Electrónica</strong></li>";
    echo "<li>Intente crear una nueva factura</li>";
    echo "<li>El error 409 debería estar resuelto</li>";
    echo "</ol>";
    echo "</div>";
}
