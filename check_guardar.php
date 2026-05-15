<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$crearIndex = strpos($content, 'function ctrCrearProducto');
$editarIndex = strpos($content, 'function ctrEditarProducto');

$crearBlock = substr($content, $crearIndex, $editarIndex - $crearIndex);
$editarBlock = substr($content, $editarIndex);

echo "En ctrCrearProducto: " . substr_count($crearBlock, 'mdlGuardarVariante') . " veces\n";
echo "En ctrEditarProducto: " . substr_count($editarBlock, 'mdlGuardarVariante') . " veces\n";
