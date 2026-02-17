<?php
// Script para actualizar qr_data en facturas existentes
// La URL de DIAN se construye con: https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=[CUFE]

require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Obtener facturas enviadas/aceptadas que no tienen qr_data
$stmt = $db->prepare("SELECT id, codigo, cufe, estado_dian 
                      FROM ventas 
                      WHERE estado_dian IN ('enviada', 'aceptada') 
                      AND cufe IS NOT NULL 
                      AND cufe != ''");
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Actualizando URLs de DIAN para facturas existentes</h2>";
echo "<p>Total de facturas a actualizar: " . count($facturas) . "</p>";

$actualizadas = 0;

foreach ($facturas as $factura) {
    // Construir la URL de DIAN usando el CUFE
    $qr_url = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=" . $factura['cufe'];

    // Actualizar la factura
    $updateStmt = $db->prepare("UPDATE ventas SET qr_data = :qr_data WHERE id = :id");
    $updateStmt->bindParam(":qr_data", $qr_url, PDO::PARAM_STR);
    $updateStmt->bindParam(":id", $factura['id'], PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        $actualizadas++;
        echo "<p>✅ Factura #{$factura['id']} ({$factura['codigo']}) actualizada</p>";
    } else {
        echo "<p>❌ Error al actualizar factura #{$factura['id']}</p>";
    }
}

echo "<hr>";
echo "<h3>Resumen:</h3>";
echo "<p><strong>Total actualizadas:</strong> $actualizadas de " . count($facturas) . "</p>";
echo "<p><a href='verificar_qr_data.php'>Ver facturas actualizadas</a></p>";
?>