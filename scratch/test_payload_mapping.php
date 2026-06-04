<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$idVenta = 54;

try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM ventas WHERE id = :id");
    $stmt->execute([':id' => $idVenta]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        die("No se encontro la venta $idVenta\n");
    }

    echo "=== Venta ID: {$venta['id']} | Código: {$venta['codigo']} ===\n";
    echo "Productos original en venta (JSON):\n" . $venta['productos'] . "\n\n";

    $datosFactura = ControladorFactus::prepararDatosFactura($venta);
    
    echo "=== Items mapeados para Factus API ===\n";
    foreach ($datosFactura['items'] as $index => $item) {
        echo "Item #" . ($index + 1) . ":\n";
        echo "  Nombre: " . $item['name'] . "\n";
        echo "  Precio Unitario: " . $item['price'] . "\n";
        echo "  Cantidad: " . $item['quantity'] . "\n";
        echo "  Tasa Impuesto (tax_rate): " . $item['tax_rate'] . "\n";
        echo "  Excluido (is_excluded): " . $item['is_excluded'] . "\n";
        echo "  ID Tributo (tribute_id): " . $item['tribute_id'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
