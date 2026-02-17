<?php

/**
 * Script para actualizar la URL de Factus a la correcta
 * Ejecutar visitando: http://localhost/pos/actualizar_url_factus.php
 */

require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Actualizar la URL de sandbox a la correcta
    $stmt = $db->prepare("UPDATE factus_config SET api_url = :api_url WHERE id = 1");
    $stmt->bindParam(":api_url", $nuevaUrl, PDO::PARAM_STR);
    $nuevaUrl = 'https://api-sandbox.factus.com.co';
    $stmt->execute();

    echo "<h2>✓ URL de Factus actualizada correctamente</h2>";
    echo "<p>La URL ha sido cambiada de <code>sandbox-api.factus.com.co</code> a <code>api-sandbox.factus.com.co</code></p>";
    echo "<p><a href='configuracion-factus' style='padding: 10px 20px; background: #3c8dbc; color: white; text-decoration: none; border-radius: 3px; display: inline-block; margin-top: 10px;'>Ir a Configuración de Factus</a></p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>✗ Error</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
