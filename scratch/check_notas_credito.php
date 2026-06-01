<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    echo "=== DESCRIBE notas_credito ===\n";
    $stmt = $db->prepare("DESCRIBE notas_credito");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n=== Content of notas_credito ===\n";
    $stmt = $db->prepare("SELECT id, id_venta_original, numero_factura_original, tipo_nota, monto_total, estado_dian, numero_nota_credito, id_bodega, id_usuario, id_cliente FROM notas_credito ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($notas);

    echo "\n=== Count of notas_credito ===\n";
    $stmt = $db->prepare("SELECT COUNT(*) FROM notas_credito");
    $stmt->execute();
    echo "Total: " . $stmt->fetchColumn() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
