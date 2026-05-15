<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$editarIndex = strpos($content, 'static public function ctrEditarProducto');
$editarBlock = substr($content, $editarIndex);

$pos = strpos($editarBlock, 'mdlGuardarVariante');
if ($pos !== false) {
    $start = max(0, $pos - 100);
    echo substr($editarBlock, $start, 1500);
}
