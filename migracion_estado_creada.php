<?php
// Script para agregar 'creada' al ENUM de estado_dian
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "<h2>Migración: Agregar 'creada' al ENUM de estado_dian</h2>";

try {
    // Modificar la columna para incluir 'creada'
    $sql = "ALTER TABLE ventas 
            MODIFY COLUMN estado_dian ENUM('pendiente','creada','enviada','rechazada','aceptada') 
            DEFAULT 'pendiente'";

    echo "<p>Ejecutando: <code>" . htmlspecialchars($sql) . "</code></p>";

    $resultado = $db->exec($sql);

    echo "<p style='color: green;'><strong>✓ Migración exitosa!</strong></p>";

    // Verificar el cambio
    echo "<h3>Verificación - Nueva estructura:</h3>";
    $stmt = $db->query("DESCRIBE ventas estado_dian");
    $columna = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($columna, true) . "</pre>";

    echo "<p><a href='diagnostico_borradores.php'>← Volver al diagnóstico</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>✗ Error:</strong> " . $e->getMessage() . "</p>";
}
?>