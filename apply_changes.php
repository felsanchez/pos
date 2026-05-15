<?php
/**
 * Script para aplicar todos los cambios de la sesion al controlador de productos
 * Usando numeros de linea para ser preciso
 */

$file = __DIR__ . '/controladores/productos.controlador.php';
$lines = file($file); // Lee linea por linea preservando finales de linea

if (!$lines) {
    die("ERROR: No se pudo leer el archivo\n");
}

$changes = 0;
$total = count($lines);
echo "Total de lineas: $total\n";

// CAMBIO 1: Linea 694 - Comentar redirect en error de creacion
$target1 = 694;
if (strpos($lines[$target1 - 1], 'window.location = "productos"') !== false && 
    strpos($lines[$target1 - 1], '//') === false) {
    $lines[$target1 - 1] = str_replace('window.location', '// window.location', $lines[$target1 - 1]);
    $changes++;
    echo "✅ L694: Comentado redirect en error de creacion\n";
}

// CAMBIO 2: Linea 673 - Comentar redirect en error al guardar
$target2 = 673;
if (strpos($lines[$target2 - 1], 'window.location = "productos"') !== false && 
    strpos($lines[$target2 - 1], '//') === false) {
    $lines[$target2 - 1] = str_replace('window.location', '// window.location', $lines[$target2 - 1]);
    $changes++;
    echo "✅ L673: Comentado redirect en error al guardar producto\n";
}

// CAMBIO 3: Linea 930 - Cambiar stock default de variante edicion a 0
$target3 = 930;
if (strpos($lines[$target3 - 1], '$_POST["editarStock"]') !== false) {
    $lines[$target3 - 1] = str_replace('$_POST["editarStock"]', '0', $lines[$target3 - 1]);
    $changes++;
    echo "✅ L930: Stock variante edicion cambiado a 0 por defecto\n";
}

// CAMBIO 4: Linea 1100 - Comentar redirect en error de edicion
$target4 = 1100;
if (strpos($lines[$target4 - 1], 'window.location = "productos"') !== false && 
    strpos($lines[$target4 - 1], '//') === false) {
    $lines[$target4 - 1] = str_replace('window.location', '// window.location', $lines[$target4 - 1]);
    $changes++;
    echo "✅ L1100: Comentado redirect en error de edicion\n";
}

// CAMBIO 5: Linea 872 - Agregar logica de bodega activa en ctrEditarProducto
$target5 = 872;
if (strpos($lines[$target5 - 1], 'if ($respuesta == "ok")') !== false && strpos($lines[$target5], 'ACTUALIZAR STOCK EN BODEGA') === false) {
    $newLines5 = "\t\t\t\t\t// 📦 ACTUALIZAR STOCK EN BODEGA ACTIVA (Para productos simples)\n" .
                 "\t\t\t\t\t\$idBodegaActiva = isset(\$_SESSION[\"id_bodega\"]) ? \$_SESSION[\"id_bodega\"] : 1;\n" .
                 "\t\t\t\t\t\$idProductoReal = isset(\$_POST[\"idProducto\"]) ? \$_POST[\"idProducto\"] : \$productoAnterior[\"id\"];\n" .
                 "\t\t\t\t\t\$tieneVariantes = \$productoAnterior[\"tiene_variantes\"];\n" .
                 "\t\t\t\t\tif(isset(\$_POST[\"form_detalle_producto\"])){\n" .
                 "\t\t\t\t\t\t\$tieneVariantes = isset(\$_POST[\"tieneVariantes\"]) ? 1 : 0;\n" .
                 "\t\t\t\t\t}\n" .
                 "\t\t\t\t\tif (\$tieneVariantes == 0) {\n" .
                 "\t\t\t\t\t\tModeloProductos::mdlActualizarStockBodega(\$idProductoReal, \$idBodegaActiva, \$nuevoStock);\n" .
                 "\t\t\t\t\t\t// Recalcular stock global\n" .
                 "\t\t\t\t\t\t\$stmtSumBodegas = Conexion::conectar()->prepare(\"SELECT SUM(pb.stock) as total FROM productos_bodegas pb WHERE pb.id_producto = :id_producto\");\n" .
                 "\t\t\t\t\t\t\$stmtSumBodegas->bindParam(\":id_producto\", \$idProductoReal, PDO::PARAM_INT);\n" .
                 "\t\t\t\t\t\t\$stmtSumBodegas->execute();\n" .
                 "\t\t\t\t\t\t\$resSum = \$stmtSumBodegas->fetch();\n" .
                 "\t\t\t\t\t\t\$stockGlobal = (\$resSum && \$resSum[\"total\"]) ? \$resSum[\"total\"] : \$nuevoStock;\n" .
                 "\t\t\t\t\t\t\$stmtSumBodegas = null;\n" .
                 "\t\t\t\t\t\tModeloProductos::mdlActualizarProducto(\"productos\", \"stock\", \$stockGlobal, \$idProductoReal);\n" .
                 "\t\t\t\t\t}\n";
    array_splice($lines, $target5, 0, $newLines5);
    $changes++;
    echo "✅ L872+: Agregada logica de bodega activa en ctrEditarProducto\n";
}

// CAMBIO 6: ServerSide bodega fix (Lineas 85-87)
$target6 = 85;
if (strpos($lines[$target6 - 1], 'mdlMostrarProductosServerSide') !== false && strpos($lines[$target6 - 1], 'idBodegaActiva') === false) {
    $lines[$target6 - 1] = "\t\t\$idBodegaActiva = isset(\$_SESSION[\"id_bodega\"]) ? \$_SESSION[\"id_bodega\"] : 1;\n\t\t\$productos = ModeloProductos::mdlMostrarProductosServerSide(\$tabla, \$where, \$order, \$limit, \$idBodegaActiva);\n";
    $lines[$target6] = "\t\t\$totalData = ModeloProductos::mdlGetTotalProductos(\$tabla, \" WHERE 1=1 \", \$idBodegaActiva);\n";
    $lines[$target6 + 1] = "\t\t\$totalFiltered = ModeloProductos::mdlGetTotalProductos(\$tabla, \$where, \$idBodegaActiva);\n";
    $changes++;
    echo "✅ ServerSide: Añadido parametro idBodegaActiva\n";
}

// CAMBIO 7: Bodega Sync para NUEVOS Productos Simples (Linea 455)
$target7 = 455;
if (strpos($lines[$target7 - 1], 'REGISTRAR MOVIMIENTO') !== false) {
    $newLines7 = "\t\t\t\t\t\t// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA\n" .
                 "\t\t\t\t\t\t\$idBodegaActiva = isset(\$_SESSION[\"id_bodega\"]) ? \$_SESSION[\"id_bodega\"] : 1;\n" .
                 "\t\t\t\t\t\tModeloProductos::mdlActualizarStockBodega(\$idProducto, \$idBodegaActiva, \$stock);\n\n";
    array_splice($lines, $target7 - 1, 0, $newLines7);
    $changes++;
    echo "✅ Añadido sync de bodega para producto simple nuevo\n";
}

// CAMBIO 8: Bodega Sync para NUEVAS Variantes (Linea ~591)
$content = implode('', $lines);
if (strpos($content, 'mdlActualizarStockVarianteBodega') === false) {
    $oldVariante = "								if (\$idVariante) {\n\n									// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE VARIANTE";
    $newVariante = "								if (\$idVariante) {\n\n									// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA\n									\$idBodegaActiva = isset(\$_SESSION[\"id_bodega\"]) ? \$_SESSION[\"id_bodega\"] : 1;\n									ModeloProductos::mdlActualizarStockVarianteBodega(\$idVariante, \$idBodegaActiva, \$stockVariante);\n\n									// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE VARIANTE";
    
    if (strpos($content, $oldVariante) !== false) {
        $content = str_replace($oldVariante, $newVariante, $content);
        $lines = explode("\n", $content);
        $lines = array_map(fn($l) => $l . "\n", $lines);
        array_pop($lines);
        $changes++;
        echo "✅ Añadido sync de bodega para nueva variante\n";
    }
}

// NUEVO CAMBIO 9: Añadir tiene_variantes a ctrEditarProducto
$content = implode('', $lines);
if (strpos($content, '"tiene_variantes" => $tieneVariantes,') === false) {
    $oldEditar = "				\$stockAnterior = \$productoAnterior[\"stock\"];\n				\$nuevoStock = \$_POST[\"editarStock\"];\n\n				\$datos = array(\n					\"id\" => isset(\$_POST[\"idProducto\"]) ? \$_POST[\"idProducto\"] : null,\n					\"id_categoria\" => \$_POST[\"editarCategoria\"],\n					\"codigo\" => \$_POST[\"editarCodigo\"],\n					\"descripcion\" => \$_POST[\"editarDescripcion\"],\n					\"stock\" => \$nuevoStock,\n					\"precio_compra\" => \$_POST[\"editarPrecioCompra\"],\n					\"precio_venta\" => \$_POST[\"editarPrecioVenta\"],\n					\"id_proveedor\" => \$_POST[\"editarProveedor\"],\n					\"imagen\" => \$ruta,\n					// Campos de facturación electrónica DIAN (Factus)";
    
    $newEditar = "				\$stockAnterior = \$productoAnterior[\"stock\"];\n				\$nuevoStock = \$_POST[\"editarStock\"];\n\n				// 🔹 Determinar si ahora tiene variantes\n				\$tieneVariantes = \$productoAnterior[\"tiene_variantes\"];\n				if (isset(\$_POST[\"totalCombinacionesEditar\"]) && \$_POST[\"totalCombinacionesEditar\"] > 0) {\n					\$tieneVariantes = 1;\n				}\n\n				\$datos = array(\n					\"id\" => isset(\$_POST[\"idProducto\"]) ? \$_POST[\"idProducto\"] : null,\n					\"id_categoria\" => \$_POST[\"editarCategoria\"],\n					\"codigo\" => \$_POST[\"editarCodigo\"],\n					\"descripcion\" => \$_POST[\"editarDescripcion\"],\n					\"stock\" => \$nuevoStock,\n					\"precio_compra\" => \$_POST[\"editarPrecioCompra\"],\n					\"precio_venta\" => \$_POST[\"editarPrecioVenta\"],\n					\"id_proveedor\" => \$_POST[\"editarProveedor\"],\n					\"imagen\" => \$ruta,\n					\"tiene_variantes\" => \$tieneVariantes,\n					// Campos de facturación electrónica DIAN (Factus)";
    
    if (strpos($content, $oldEditar) !== false) {
        $content = str_replace($oldEditar, $newEditar, $content);
        $changes++;
        echo "✅ Lógica tiene_variantes añadida a ctrEditarProducto\n";
    } else {
        echo "⚠️  No se encontró bloque exacto para tiene_variantes\n";
    }
}

file_put_contents($file, $content);
echo "\nTotal cambios aplicados: $changes\n";
