<?php
// Script para actualizar manualmente el consecutivo

require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Actualización Manual del Consecutivo</h2>";

$db = Conexion::conectar();

// Extraer el número de FEFG8
$numeroFactura = "FEFG8";
preg_match('/(\d+)$/', $numeroFactura, $matches);
$numero = isset($matches[1]) ? intval($matches[1]) : 0;

echo "<p>Número extraído de '$numeroFactura': $numero</p>";

if ($numero > 0) {
    $stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = :numero WHERE id_factus = 1040");
    $result = $stmt->execute([':numero' => $numero]);

    if ($result) {
        echo "<p>✅ Consecutivo actualizado exitosamente a: $numero</p>";
    } else {
        echo "<p>❌ Error al actualizar</p>";
    }

    // Verificar
    $stmt = $db->query("SELECT numero_actual FROM factus_rangos WHERE id_factus = 1040");
    $rango = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Valor actual en BD: " . $rango['numero_actual'] . "</p>";
    echo "<p>Próxima factura será: " . ($rango['numero_actual'] + 1) . "</p>";
}
