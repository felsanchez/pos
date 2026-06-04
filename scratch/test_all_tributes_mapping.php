<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    // Get a valid category ID from database
    $stmtCat = $db->query("SELECT id FROM categorias LIMIT 1");
    $cat = $stmtCat->fetch();
    $catId = $cat ? intval($cat['id']) : 1;

    // 1. Create mock products in DB
    // Product with ID 9991: tributo_id = 5 (IVA Excluido)
    $stmt = $db->prepare("INSERT INTO productos (id, id_categoria, codigo, descripcion, stock, precio_compra, precio_venta, ventas, imagen, fecha, tributo_id, unidad_medida_id) VALUES (9991, :catId, 'MOCK-EXC', 'Mock IVA Excluido', 10, 50, 100, 0, '', NOW(), 5, 70)");
    $stmt->execute([':catId' => $catId]);

    // Product with ID 9992: tributo_id = 0 (No tax)
    $stmt = $db->prepare("INSERT INTO productos (id, id_categoria, codigo, descripcion, stock, precio_compra, precio_venta, ventas, imagen, fecha, tributo_id, unidad_medida_id) VALUES (9992, :catId, 'MOCK-NOTAX', 'Mock Sin Impuesto', 10, 50, 100, 0, '', NOW(), 0, 70)");
    $stmt->execute([':catId' => $catId]);

    // Product with ID 9993: tributo_id = 6 (IVA 5%)
    $stmt = $db->prepare("INSERT INTO productos (id, id_categoria, codigo, descripcion, stock, precio_compra, precio_venta, ventas, imagen, fecha, tributo_id, unidad_medida_id) VALUES (9993, :catId, 'MOCK-IVA5', 'Mock IVA 5%', 10, 50, 100, 0, '', NOW(), 6, 70)");
    $stmt->execute([':catId' => $catId]);

    // Existing products:
    // ID 2 -> tributo_id 3 (ICA)
    // ID 3 -> tributo_id 4 (INC Bolsas)
    // ID 7 -> tributo_id 2 (INC 8%)
    // ID 11 -> tributo_id 1 (IVA 19%)

    $productosMock = [
        ["id" => "9991", "descripcion" => "Mock IVA Excluido", "cantidad" => "1", "precio" => "100", "total" => "100", "impuesto" => "0", "codigo" => "MOCK-EXC"],
        ["id" => "9992", "descripcion" => "Mock Sin Impuesto", "cantidad" => "2", "precio" => "100", "total" => "200", "impuesto" => "0", "codigo" => "MOCK-NOTAX"],
        ["id" => "9993", "descripcion" => "Mock IVA 5%", "cantidad" => "1", "precio" => "105", "total" => "105", "impuesto" => "5", "codigo" => "MOCK-IVA5"],
        ["id" => "2", "descripcion" => "camisa polo (ICA)", "cantidad" => "1", "precio" => "100", "total" => "100", "impuesto" => "0", "codigo" => "2"],
        ["id" => "3", "descripcion" => "Pollo 11 (INC Bolsas)", "cantidad" => "1", "precio" => "100", "total" => "100", "impuesto" => "0", "codigo" => "3"],
        ["id" => "7", "descripcion" => "Pollo (INC 8%)", "cantidad" => "1", "precio" => "108", "total" => "108", "impuesto" => "8", "codigo" => "7"],
        ["id" => "11", "descripcion" => "medias sur (IVA 19%)", "cantidad" => "1", "precio" => "119", "total" => "119", "impuesto" => "19", "codigo" => "11"]
    ];

    // Mock venta object
    $venta = [
        "id" => 54,
        "codigo" => 990000308,
        "id_cliente" => 7,
        "total" => 832,
        "productos" => json_encode($productosMock),
        "tipo_descuento" => "",
        "valor_descuento" => 0,
        "monto_descuento" => 0,
        "retenciones" => "[]"
    ];

    echo "=============================================\n";
    echo "PROBANDO PREPARAR DATOS FACTURA\n";
    echo "=============================================\n";
    $datosFactura = ControladorFactus::prepararDatosFactura($venta);
    
    foreach ($datosFactura['items'] as $i => $item) {
        echo "Item #" . ($i + 1) . " - " . $item['name'] . ":\n";
        echo "  price:       " . $item['price'] . "\n";
        echo "  tax_rate:    " . $item['tax_rate'] . "\n";
        echo "  is_excluded: " . $item['is_excluded'] . "\n";
        echo "  tribute_id:  " . $item['tribute_id'] . "\n";
    }

    /*
    echo "\n=============================================\n";
    echo "PROBANDO PREPARAR DATOS NOTA CRÉDITO\n";
    echo "=============================================\n";
    $datosNC = ControladorFactus::prepararDatosNotaCredito($venta, "1", $productosMock, 7, "Prueba", "Efectivo");
    
    foreach ($datosNC['items'] as $i => $item) {
        echo "Item #" . ($i + 1) . " - " . $item['name'] . ":\n";
        echo "  price:       " . $item['price'] . "\n";
        echo "  tax_rate:    " . $item['tax_rate'] . "\n";
        echo "  is_excluded: " . $item['is_excluded'] . "\n";
        echo "  tribute_id:  " . $item['tribute_id'] . "\n";
    }
    */

    // Rollback so we don't pollute the DB
    $db->rollBack();
    echo "\nDB Reverted successfully.\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
