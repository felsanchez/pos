<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    // 1. Inicializar/Corregir tabla de consecutivos para ventas POS ordinarias
    echo "=== 1. Initializing/Updating 'consecutivos' for 'ventas' ===\n";
    $stmt = $db->prepare("INSERT INTO consecutivos (tabla, ultimo_numero) VALUES ('ventas', 10004) ON DUPLICATE KEY UPDATE ultimo_numero = 10004");
    if ($stmt->execute()) {
        echo "Successfully set 'ventas' last consecutive to 10004 (next POS sale will be 10005).\n";
    } else {
        echo "Failed to set 'ventas' consecutive.\n";
    }

    // 2. Corregir borradores de facturas electrónicas recientes
    echo "\n=== 2. Correcting existing draft FEs ===\n";
    
    // ID 75: Código 990000314 -> 990000308 (estado_dian -> creada)
    $stmt = $db->prepare("UPDATE ventas SET codigo = 990000308, estado_dian = 'creada', mensaje_dian = 'Factura guardada localmente (Borrador). Pendiente de firma.' WHERE id = 75");
    if ($stmt->execute()) {
        echo "Successfully updated ID 75 to code 990000308 and state 'creada'.\n";
    } else {
        echo "Failed to update ID 75.\n";
    }

    // ID 77: Código 990000315 -> 990000309 (estado_dian -> creada)
    $stmt = $db->prepare("UPDATE ventas SET codigo = 990000309, estado_dian = 'creada' WHERE id = 77");
    if ($stmt->execute()) {
        echo "Successfully updated ID 77 to code 990000309 and state 'creada'.\n";
    } else {
        echo "Failed to update ID 77.\n";
    }

    echo "\n=== Verification of corrected sales ===\n";
    $stmt = $db->prepare("SELECT id, codigo, numero_factura, resolucion_id, estado_dian, total, fecha FROM ventas WHERE id IN (75, 76, 77) ORDER BY id ASC");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sales as $sale) {
        echo "ID: {$sale['id']} | Codigo: {$sale['codigo']} | NumFactura: " . ($sale['numero_factura'] ?? 'NULL') . " | ResolucionID: " . ($sale['resolucion_id'] ?? 'NULL') . " | EstadoDIAN: " . ($sale['estado_dian'] ?? 'NULL') . " | Total: {$sale['total']} | Fecha: {$sale['fecha']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
