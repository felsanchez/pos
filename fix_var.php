<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$search = 'ModeloProductos::mdlActualizarStockVarianteBodega($idVariante, $idBodegaActiva, $stockVariante);';
$replace = 'ModeloProductos::mdlActualizarStockVarianteBodega($idVarianteNueva, $idBodegaActiva, $stockVariante);';

if (strpos($content, $search) !== false) {
    $newContent = str_replace($search, $replace, $content);
    file_put_contents($file, $newContent);
    echo "✅ Fix de variable ID Variante aplicado correctamente.\n";
} else {
    echo "❌ No se encontró la cadena. Puede que ya este arreglado o el formato sea distinto.\n";
}
