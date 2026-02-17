<?php

 /*=============================================
DESCARGAR PLANTILLA CSV PARA IMPORTAR PRODUCTOS
=============================================*/ 

$nombreArchivo = 'plantilla_productos_' . date('Y-m-d') . '.csv'; 

header('Content-Type: text/csv; charset=utf-8');

header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

header('Pragma: no-cache');

header('Expires: 0');

 
// Crear el archivo CSV
$output = fopen('php://output', 'w'); 

// BOM para UTF-8 (ayuda con caracteres especiales en Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


// Encabezados del CSV
$encabezados = array(
    'codigo',
    'descripcion',
    'categoria',
    'proveedor (opcional)',
    'stock',
    'precio_compra',
    'precio_venta'
); 

// Usar punto y coma como delimitador (compatible con Excel en español)
fputcsv($output, $encabezados, ';'); 

// Agregar 3 filas de ejemplo
$ejemplos = array(
    array('PROD001', 'Producto de Ejemplo 1', 'Locion', 'ProveedorA', '100', '10000', '15000'),
    array('PROD002', 'Producto de Ejemplo 2', 'Locion', '', '50', '5000', '8000'),
    array('PROD003', 'Producto de Ejemplo 3', 'Locion', 'ProveedorA', '75', '12000', '18000')
); 

foreach ($ejemplos as $fila) {
    fputcsv($output, $fila, ';');
}
 
fclose($output);

exit;