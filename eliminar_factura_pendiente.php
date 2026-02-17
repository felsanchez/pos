<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

echo "<h2>Eliminar Factura Pendiente en Factus</h2>";

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

// 2. Identificar el reference_code de la factura pendiente
// Según la documentación, usamos el código de referencia que enviamos
$referenceCode = "VENTA-10"; // La venta que está bloqueada

echo "<h3>Factura a Eliminar:</h3>";
echo "<p><strong>Reference Code:</strong> $referenceCode</p>";

echo "<hr>";
echo "<h3>⚠️ ADVERTENCIA</h3>";
echo "<p>Esta acción eliminará la factura pendiente en Factus.</p>";
echo "<p>Esto liberará el bloqueo y permitirá crear nuevas facturas.</p>";

echo "<form method='POST'>";
echo "<button type='submit' name='eliminar' value='si' style='background:red; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>ELIMINAR Factura Pendiente</button>";
echo " ";
echo "<button type='button' onclick='window.history.back()' style='background:gray; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>Cancelar</button>";
echo "</form>";

if (isset($_POST['eliminar']) && $_POST['eliminar'] == 'si') {
    echo "<hr>";
    echo "<h3>Ejecutando eliminación...</h3>";

    // 3. Hacer la petición DELETE a la API
    $url = $config['api_url'] . '/v1/bills/' . $referenceCode;

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
        echo "<h3 style='color:#155724;'>✅ Factura Eliminada Exitosamente</h3>";
        echo "<p>La factura pendiente ha sido eliminada de Factus.</p>";
        echo "<p>Ahora puede crear nuevas facturas sin el error 409.</p>";
        echo "</div>";

        echo "<h3>Próximos Pasos:</h3>";
        echo "<ol>";
        echo "<li>Vaya a <strong>Crear Factura Electrónica</strong></li>";
        echo "<li>Cree la factura 11 con los productos correctos</li>";
        echo "<li>Verifique que se cree exitosamente en la DIAN</li>";
        echo "</ol>";
    } else {
        echo "<div style='background:#f8d7da; border:1px solid #f5c6cb; padding:15px; margin:10px 0;'>";
        echo "<h3 style='color:#721c24;'>❌ Error al Eliminar</h3>";
        echo "<p>Revise la respuesta arriba para más detalles.</p>";
        echo "</div>";
    }
}
