<?php

/**
 * Script para configurar las credenciales de Factus en la base de datos
 * Ejecutar una sola vez visitando: http://localhost/pos/configurar_credenciales_factus.php
 */

require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();

    echo "<h2>Configuración de Credenciales Factus</h2>";
    echo "<pre>";

    // Credenciales proporcionadas
    $client_id = 'a06f2b84-d069-41ee-9001-f567cc9ba899';
    $client_secret = 'i4KIDLeVR7vUVvty2kVjp8h0omccjRoFnoLttjUF';
    $api_url = 'https://api-sandbox.factus.com.co';
    $ambiente = 'sandbox';

    echo "Configurando credenciales de Factus...\n\n";
    echo "Client ID: " . substr($client_id, 0, 20) . "...\n";
    echo "Client Secret: " . substr($client_secret, 0, 10) . "...\n";
    echo "API URL: $api_url\n";
    echo "Ambiente: $ambiente\n\n";

    // Actualizar la configuración
    $stmt = $db->prepare("UPDATE factus_config SET
        client_id = :client_id,
        client_secret = :client_secret,
        api_url = :api_url,
        ambiente = :ambiente,
        activo = 1
        WHERE id = 1");

    $stmt->bindParam(':client_id', $client_id, PDO::PARAM_STR);
    $stmt->bindParam(':client_secret', $client_secret, PDO::PARAM_STR);
    $stmt->bindParam(':api_url', $api_url, PDO::PARAM_STR);
    $stmt->bindParam(':ambiente', $ambiente, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "✓ Credenciales configuradas exitosamente\n\n";

        // Verificar que se guardaron correctamente
        $stmt = $db->query("SELECT id, api_url, client_id, ambiente, activo FROM factus_config WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($config) {
            echo "Configuración guardada:\n";
            echo "- ID: " . $config['id'] . "\n";
            echo "- API URL: " . $config['api_url'] . "\n";
            echo "- Client ID: " . substr($config['client_id'], 0, 20) . "...\n";
            echo "- Ambiente: " . $config['ambiente'] . "\n";
            echo "- Activo: " . ($config['activo'] ? 'Sí' : 'No') . "\n\n";
        }

        echo "✓ Configuración completada exitosamente\n";
        echo "\n<strong style='color: green;'>¡Listo! Ahora puedes probar la autenticación con Factus.</strong>\n";
        echo "\n<a href='test_factura_datos_reales.php' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px;'>Ejecutar prueba de facturación</a>";

    } else {
        echo "✗ Error al actualizar la configuración\n";
        print_r($stmt->errorInfo());
    }

    echo "</pre>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>✗ Error de base de datos</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
