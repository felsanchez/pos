<?php

 /*=============================================
DESCARGAR PLANTILLA CSV PARA IMPORTAR CLIENTES
=============================================*/ 

$nombreArchivo = 'plantilla_clientes_' . date('Y-m-d') . '.csv'; 

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear el archivo CSV
$output = fopen('php://output', 'w'); 

// BOM para UTF-8 (ayuda con caracteres especiales en Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Indicador para Excel del separador que estamos utilizando
fwrite($output, "sep=;\r\n");

// Encabezados del CSV
$encabezados = array(
    'tipo de persona',
    'tipo documento',
    'digito de verificacion',
    'numero de documento',
    'nombre',
    'correo',
    'telefono',
    'municipio',
    'direccion',
    'fecha nacimiento',
    'notas adicionales'
); 

// Usar punto y coma como delimitador (compatible con Excel en español)
fputcsv($output, $encabezados, ';'); 

// Agregar filas de ejemplo
$ejemplos = array(
    array('Persona natural', 'CC', '', '12345678', 'Juan Perez', 'juan@ejemplo.com', '3001234567', 'Medellin - Antioquia', 'Calle 10 # 5-20', '1990-05-15', 'Cliente frecuente'),
    array('Persona juridica', 'NIT', '9', '900123456', 'Empresa Ejemplo SAS', 'contacto@empresa.com', '3109876543', 'Bogota, D.C. - Bogota', 'Avenida Siempre Viva 123', '', 'Proveedor y cliente'),
    array('Persona natural', 'CE', '', '87654321', 'John Doe', 'john@example.com', '3201112233', 'Cali - Valle del Cauca', 'Carrera 5 # 10-15', '1985-12-24', '')
); 

foreach ($ejemplos as $fila) {
    fputcsv($output, $fila, ';');
}
 
fclose($output);

exit;
