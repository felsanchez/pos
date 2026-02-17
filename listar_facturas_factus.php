<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Refrescar Token y Listar Facturas</h2>";

// 1. Obtener configuración
$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_config LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    die("❌ No hay configuración de Factus");
}

echo "<h3>1. Obteniendo nuevo token...</h3>";

// 2. Obtener NUEVO token (no usar el guardado)
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo "<p style='color:red;'>❌ Error al obtener token (HTTP $httpCode)</p>";
    echo "<pre>$response</pre>";
    die();
}

$authResponse = json_decode($response, true);
$token = $authResponse['access_token'] ?? null;

if (!$token) {
    die("❌ No se pudo obtener token");
}

echo "<p>✅ Token obtenido exitosamente</p>";

// 3. Guardar el nuevo token en la BD
$expiracion = date('Y-m-d H:i:s', time() + ($authResponse['expires_in'] ?? 3600));
$stmt = $db->prepare("UPDATE factus_config SET access_token = :token, token_expiracion = :expiracion WHERE id = 1");
$stmt->execute([
    ':token' => $token,
    ':expiracion' => $expiracion
]);

echo "<p>✅ Token guardado en BD (expira: $expiracion)</p>";

// 4. Listar facturas
echo "<h3>2. Consultando facturas en Factus...</h3>";

$ch = curl_init($config['api_url'] . '/v1/bills?limit=20');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

if ($httpCode == 200) {
    $resultado = json_decode($response, true);

    if (isset($resultado['data']) && is_array($resultado['data'])) {
        echo "<h3>Últimas 20 Facturas:</h3>";
        echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
        echo "<tr style='background:#f0f0f0;'>";
        echo "<th>Número</th><th>Reference Code</th><th>Estado</th><th>Fecha</th><th>Acción</th>";
        echo "</tr>";

        $hayPendientes = false;

        foreach ($resultado['data'] as $factura) {
            $numero = $factura['number'] ?? 'Sin número';
            $refCode = $factura['reference_code'] ?? 'N/A';
            $estado = $factura['status'] ?? 'N/A';
            $fecha = isset($factura['created_at']) ? date('Y-m-d H:i', strtotime($factura['created_at'])) : 'N/A';

            // Resaltar pendientes o con errores
            $style = '';
            $estadoLower = strtolower($estado);
            if ($estadoLower == 'pending' || $estadoLower == 'pendiente' || $estadoLower == 'draft') {
                $style = "background:#ffcccc; font-weight:bold;";
                $hayPendientes = true;
            }

            echo "<tr style='$style'>";
            echo "<td>$numero</td>";
            echo "<td>$refCode</td>";
            echo "<td>$estado</td>";
            echo "<td>$fecha</td>";
            echo "<td>";
            if ($refCode != 'N/A' && $estadoLower != 'accepted' && $estadoLower != 'aceptada') {
                echo "<a href='eliminar_factura_especifica.php?ref=" . urlencode($refCode) . "' style='color:red; text-decoration:underline;'>Eliminar</a>";
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";

        if (!$hayPendientes) {
            echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin:10px 0;'>";
            echo "<h3 style='color:#155724;'>✅ No hay facturas pendientes</h3>";
            echo "<p>Todas las facturas están en estado final (aceptadas o rechazadas).</p>";
            echo "<p><strong>Intente crear una nueva factura ahora.</strong></p>";
            echo "<p>Si el error 409 persiste, contacte a Factus - el bloqueo está en su servidor.</p>";
            echo "</div>";
        } else {
            echo "<div style='background:#fff3cd; border:1px solid #ffc107; padding:15px; margin:10px 0;'>";
            echo "<h3 style='color:#856404;'>⚠️ Hay facturas pendientes (resaltadas en rojo)</h3>";
            echo "<p>Elimine las facturas pendientes usando el botón 'Eliminar'.</p>";
            echo "</div>";
        }

    } else {
        echo "<p>No se encontraron facturas.</p>";
        echo "<pre>$response</pre>";
    }
} else {
    echo "<p style='color:red;'>❌ Error al consultar facturas (HTTP $httpCode)</p>";
    echo "<pre>$response</pre>";
}
