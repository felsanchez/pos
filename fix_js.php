<?php
$file = __DIR__ . '/vistas/js/producto-detalle.js';
$content = file_get_contents($file);

// Replace the static evaluation with dynamic ones or evaluation inside $(document).ready
$search = 'var modoEdicion = $(\'input[name="idProducto"]\').length > 0 && $(\'input[name="idProducto"]\').val() !== "";';
$replace = 'var modoEdicion = false;
$(document).ready(function() {
    modoEdicion = $(\'input[name="idProducto"]\').length > 0 && $(\'input[name="idProducto"]\').val() !== "";
});';

if (strpos($content, $search) !== false) {
    $newContent = str_replace($search, $replace, $content);
    file_put_contents($file, $newContent);
    echo "✅ JavaScript actualizado.\n";
} else {
    echo "❌ No se encontró la cadena.\n";
}
