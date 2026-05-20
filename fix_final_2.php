<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$search = '$stockAnterior = $productoAnterior["stock"];
				$nuevoStock = $_POST["editarStock"];';

$replace = '$stockAnterior = $productoAnterior["stock"];
				$nuevoStock = $_POST["editarStock"];

				// Fallback de compatibilidad de variables JS
				if (!isset($_POST["totalCombinacionesEditar"]) && isset($_POST["totalCombinaciones"])) {
					$_POST["totalCombinacionesEditar"] = $_POST["totalCombinaciones"];
				}';

$newContent = str_replace($search, $replace, $content);
file_put_contents($file, $newContent);
echo "✅ Parche de compatibilidad movido hacia arriba en ctrEditarProducto.\n";
