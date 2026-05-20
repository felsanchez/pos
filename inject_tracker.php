<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

// Find the exact start of ctrEditarProducto
$pos = strpos($content, 'static public function ctrEditarProducto(');
if ($pos !== false) {
    // Find the start of the next line that contains 'if'
    $ifPos = strpos($content, 'if (isset($_POST["editarDescripcion"]))', $pos);
    
    if ($ifPos !== false) {
        $insertStr = "\n\t\t\tfile_put_contents(\"debug_post.txt\", \"=== CTR EDITAR (ID: \" . (isset(\$_POST['idProducto']) ? \$_POST['idProducto'] : 'none') . \") ===\\n\" . print_r(\$_POST, true) . \"\\n\", FILE_APPEND);\n";
        
        $newContent = substr_replace($content, $insertStr, $ifPos, 0);
        file_put_contents($file, $newContent);
        echo "✅ Rastreador inyectado en ctrEditarProducto.\n";
    } else {
        echo "❌ No se encontro el 'if (isset...)' dentro de ctrEditarProducto.\n";
    }
} else {
    echo "❌ No se encontro ctrEditarProducto.\n";
}
