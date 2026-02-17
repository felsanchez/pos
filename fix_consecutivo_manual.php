<?php
// Script para actualizar manualmente el consecutivo y el estado de las facturas exitosas

require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Corrección Manual de Facturas Exitosas</h2>";

$db = Conexion::conectar();

// 1. Actualizar estado_dian de las facturas exitosas que tienen numero_factura
echo "<h3>1. Actualizando estado_dian de facturas con número</h3>";
$stmt = $db->prepare("UPDATE ventas 
                      SET estado_dian = 'enviada' 
                      WHERE numero_factura IS NOT NULL 
                      AND numero_factura != '' 
                      AND (estado_dian IS NULL OR estado_dian = '')");
$result1 = $stmt->execute();
$affected1 = $stmt->rowCount();
echo "<p>Facturas actualizadas: $affected1</p>";

// 2. Actualizar el consecutivo en factus_rangos basado en la última factura exitosa
echo "<h3>2. Actualizando consecutivo en factus_rangos</h3>";
$stmt = $db->query("SELECT MAX(CAST(SUBSTRING_INDEX(numero_factura, '-', -1) AS UNSIGNED)) as ultimo_numero
                    FROM ventas 
                    WHERE numero_factura LIKE 'FEFG%'");
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
$ultimoNumero = $resultado['ultimo_numero'];

if ($ultimoNumero) {
    $stmt2 = $db->prepare("UPDATE factus_rangos SET numero_actual = :numero WHERE id_factus = 1040");
    $stmt2->execute([':numero' => $ultimoNumero]);
    echo "<p>Consecutivo actualizado a: $ultimoNumero</p>";
}

// 3. Verificar resultado
echo "<h3>3. Verificación</h3>";
$stmt = $db->query("SELECT * FROM factus_rangos WHERE id_factus = 1040");
$rango = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p>numero_actual en factus_rangos: " . $rango['numero_actual'] . "</p>";

$stmt = $db->query("SELECT id, codigo, numero_factura, estado_dian FROM ventas WHERE numero_factura LIKE 'FEFG%' ORDER BY id DESC LIMIT 3");
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>Últimas facturas:\n";
print_r($facturas);
echo "</pre>";

echo "<h3>✅ Corrección Completada</h3>";
echo "<p>Ahora puede crear una nueva factura y el consecutivo debería ser " . ($ultimoNumero + 1) . "</p>";
