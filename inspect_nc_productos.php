<?php
require_once "modelos/conexion.php";
// Check ventas productos structure - last 2 sales with productos
$stmt = Conexion::conectar()->query(
    "SELECT v.id, v.productos FROM ventas v 
     JOIN notas_credito nc ON nc.id_venta_original = v.id 
     ORDER BY nc.id DESC LIMIT 1"
);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $prods = json_decode($row['productos'], true);
    echo "=== Venta ID " . $row['id'] . " productos ===\n";
    // Show just first product to see fields
    if (!empty($prods)) {
        echo "Fields in first product: " . implode(", ", array_keys($prods[0])) . "\n\n";
        print_r($prods[0]);
    }
} else {
    echo "No records found\n";
}
?>