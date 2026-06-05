<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    // We search for sales where the JSON 'productos' contains product ID 22 (the bag)
    // and where the sale has been successfully sent/signed (e.g. estado_dian = 'enviada' or has number/cufe)
    $stmt = $db->prepare("SELECT id, codigo, productos, numero_factura, estado_dian, fecha_envio_dian FROM ventas WHERE productos LIKE :prodId AND (estado_dian = 'enviada' OR numero_factura IS NOT NULL)");
    $stmt->execute([':prodId' => '%"id":"22"%']);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($sales) . " signed sales containing bags:\n\n";
    foreach ($sales as $sale) {
        echo "ID: {$sale['id']} | Código: {$sale['codigo']} | Factura: {$sale['numero_factura']} | Estado: {$sale['estado_dian']} | Fecha: {$sale['fecha_envio_dian']}\n";
        echo "Productos: {$sale['productos']}\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
