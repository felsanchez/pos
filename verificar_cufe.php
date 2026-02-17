<?php
// Diagnóstico específico para las facturas que no se actualizan
require_once "modelos/conexion.php";

$db = Conexion::conectar();

// IDs de las facturas problemáticas según el reporte del usuario
$ids = [654, 653, 652, 651, 650, 649, 648, 647, 646, 645];
$placeholders = implode(',', $ids);

$stmt = $db->prepare("SELECT id, codigo, estado_dian, cufe, LENGTH(cufe) as len_cufe, qr_data 
                      FROM ventas 
                      WHERE id IN ($placeholders)");
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Diagnóstico de CUFE para facturas recientes</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Código</th><th>Estado</th><th>CUFE</th><th>Longitud CUFE</th><th>QR Data</th></tr>";

foreach ($facturas as $factura) {
    echo "<tr>";
    echo "<td>{$factura['id']}</td>";
    echo "<td>{$factura['codigo']}</td>";
    echo "<td>{$factura['estado_dian']}</td>";

    if ($factura['cufe'] === null) {
        echo "<td style='color:red'>NULL</td>";
        echo "<td>-</td>";
    } elseif ($factura['cufe'] === '') {
        echo "<td style='color:orange'>'' (vacío)</td>";
        echo "<td>0</td>";
    } else {
        echo "<td>" . substr($factura['cufe'], 0, 20) . "...</td>";
        echo "<td>{$factura['len_cufe']}</td>";
    }

    echo "<td>{$factura['qr_data']}</td>";
    echo "</tr>";
}
echo "</table>";
?>