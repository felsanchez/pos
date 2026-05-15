<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$search = '$idProducto = $_POST["editarCodigo"]; // Usamos el código como ID del producto 

						// Obtener el ID real del producto
						$productoBase = ModeloProductos::mdlMostrarProductos("productos", "codigo", $idProducto, "id");
						$idProductoReal = $productoBase["id"];';

$replace = '// Obtener el ID real del producto de forma directa y segura
						$idProductoReal = isset($_POST["idProducto"]) && !empty($_POST["idProducto"]) ? $_POST["idProducto"] : null;
						if(!$idProductoReal) {
							$idProducto = $_POST["editarCodigo"];
							$productoBase = ModeloProductos::mdlMostrarProductos("productos", "codigo", $idProducto, "id");
							$idProductoReal = $productoBase["id"];
						}';

$contentNorm = str_replace("\r\n", "\n", $content);
$searchNorm = str_replace("\r\n", "\n", $search);

if (strpos($contentNorm, $searchNorm) !== false) {
    $newContent = str_replace($searchNorm, $replace, $contentNorm);
    file_put_contents($file, $newContent);
    echo "✅ Fix de ID de producto aplicado correctamente.\n";
} else {
    // Intentar con regex por si hay espacios
    $pattern = '/\$idProducto\s*=\s*\$_POST\["editarCodigo"\];\s*\/\/\s*Usamos el código como ID del producto\s*\/\/\s*Obtener el ID real del producto\s*\$productoBase\s*=\s*ModeloProductos::mdlMostrarProductos\("productos", "codigo", \$idProducto, "id"\);\s*\$idProductoReal\s*=\s*\$productoBase\["id"\];/i';
    if(preg_match($pattern, $content)) {
         $newContent = preg_replace($pattern, $replace, $content);
         file_put_contents($file, $newContent);
         echo "✅ Fix de ID de producto aplicado correctamente (vía regex).\n";
    } else {
         echo "❌ No se encontró el bloque de código para corregir el ID.\n";
    }
}
