<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$search = '"id_categoria" => $_POST["editarCategoria"],';
$replace = '"id_categoria" => $_POST["editarCategoria"],
					"tiene_variantes" => (isset($_POST["totalCombinacionesEditar"]) && $_POST["totalCombinacionesEditar"] > 0) ? 1 : $productoAnterior["tiene_variantes"],';

if (strpos($content, $replace) === false) {
    // Only replace the ONE occurrence in ctrEditarProducto, which should be the second one in the file if the first is ctrCrearProducto
    // Wait, ctrCrearProducto has "id_categoria" => $_POST["nuevaCategoria"],
    // So "editarCategoria" only exists in ctrEditarProducto! Safe to replace globally.
    $newContent = str_replace($search, $replace, $content);
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "✅ Exito: tiene_variantes inyectado en ctrEditarProducto.\n";
    } else {
        echo "❌ Fallo: No se encontro la cadena a reemplazar.\n";
    }
} else {
    echo "⚠️  Ya estaba aplicado.\n";
}
