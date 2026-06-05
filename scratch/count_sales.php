<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    echo "=== POS Sales (resolucion_id IS NULL OR 0) ===\n";
    $stmt = $db->prepare("SELECT id, codigo, numero_factura, resolucion_id, total, fecha FROM ventas WHERE resolucion_id IS NULL OR resolucion_id = 0 ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== FE Sales (resolucion_id NOT NULL AND != 0) ===\n";
    $stmt = $db->prepare("SELECT id, codigo, numero_factura, resolucion_id, total, fecha FROM ventas WHERE resolucion_id IS NOT NULL AND resolucion_id != 0 ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
