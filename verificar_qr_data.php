<?php
// Verificar si hay facturas con qr_data
require_once "modelos/conexion.php";

$db = Conexion::conectar();

$stmt = $db->prepare("SELECT id, codigo, numero_factura, estado_dian, qr_data, cufe 
                      FROM ventas 
                      WHERE estado_dian IN ('enviada', 'aceptada') 
                      ORDER BY id DESC 
                      LIMIT 10");
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Facturas Enviadas/Aceptadas (últimas 10)</h2>";
echo "<table border='1' cellpadding='5' style='width:100%'>";
echo "<tr><th>ID</th><th>Código</th><th>Número Factura</th><th>Estado DIAN</th><th>CUFE</th><th>QR Data (URL DIAN)</th></tr>";

foreach ($facturas as $factura) {
    echo "<tr>";
    echo "<td>{$factura['id']}</td>";
    echo "<td>{$factura['codigo']}</td>";
    echo "<td>{$factura['numero_factura']}</td>";
    echo "<td>{$factura['estado_dian']}</td>";
    echo "<td style='font-size:10px'>" . substr($factura['cufe'], 0, 50) . "...</td>";

    if (!empty($factura['qr_data'])) {
        echo "<td><a href='{$factura['qr_data']}' target='_blank'>Ver en DIAN</a></td>";
    } else {
        echo "<td style='color:red'>❌ Vacío</td>";
    }
    echo "</tr>";
}

echo "</table>";

echo "<h3>Total de facturas enviadas/aceptadas: " . count($facturas) . "</h3>";
?>