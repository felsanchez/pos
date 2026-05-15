<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$changes = 0;

// FIX 1: Bodega activa en creacion simple (aprox linea 455)
$search1 = '// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE PRODUCTO';
$replace1 = '// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA
						$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
						ModeloProductos::mdlActualizarStockBodega($idProducto, $idBodegaActiva, $stock);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE PRODUCTO';

if (strpos($content, $search1) !== false && strpos($content, 'mdlActualizarStockBodega($idProducto, $idBodegaActiva, $stock)') === false) {
    $content = str_replace($search1, $replace1, $content);
    $changes++;
    echo "✅ Fix 1 (Bodega Creacion Simple) aplicado.\n";
}

// FIX 2: Bodega activa en creacion variante (aprox linea 591)
$search2 = '// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE VARIANTE';
$replace2 = '// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA
									$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
									ModeloProductos::mdlActualizarStockVarianteBodega($idVariante, $idBodegaActiva, $stockVariante);

									// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE VARIANTE';

if (strpos($content, $search2) !== false && strpos($content, 'mdlActualizarStockVarianteBodega') === false) {
    $content = str_replace($search2, $replace2, $content);
    $changes++;
    echo "✅ Fix 2 (Bodega Creacion Variante) aplicado.\n";
}

// FIX 3: tiene_variantes logic in ctrEditarProducto (aprox linea 860)
// This is the tricky one. Let's look for the start of the array
$search3 = '$datos = array(
					"id" => isset($_POST["idProducto"]) ? $_POST["idProducto"] : null,
					"id_categoria" => $_POST["editarCategoria"],';

$replace3 = '// 🔹 Determinar si ahora tiene variantes
				$tieneVariantes = $productoAnterior["tiene_variantes"];
				if (isset($_POST["totalCombinacionesEditar"]) && $_POST["totalCombinacionesEditar"] > 0) {
					$tieneVariantes = 1;
				}

				$datos = array(
					"id" => isset($_POST["idProducto"]) ? $_POST["idProducto"] : null,
					"id_categoria" => $_POST["editarCategoria"],
					"tiene_variantes" => $tieneVariantes,';

// Some files use spaces, some tabs. We will normalize CRLF to LF for strpos if needed
$contentNorm = str_replace("\r\n", "\n", $content);
$search3Norm = str_replace("\r\n", "\n", $search3);

if (strpos($contentNorm, $search3Norm) !== false && strpos($contentNorm, '"tiene_variantes" => $tieneVariantes') === false) {
    // Need to do replace on original content but using normalized search might be tricky
    // Better to use regex
    $pattern = '/\$datos = array\(\s*"id"\s*=>\s*isset\(\$_POST\["idProducto"\]\)\s*\?\s*\$_POST\["idProducto"\]\s*:\s*null,\s*"id_categoria"\s*=>\s*\$_POST\["editarCategoria"\],/is';
    
    $replacement = '// 🔹 Determinar si ahora tiene variantes
				$tieneVariantes = $productoAnterior["tiene_variantes"];
				if (isset($_POST["totalCombinacionesEditar"]) && $_POST["totalCombinacionesEditar"] > 0) {
					$tieneVariantes = 1;
				}

				$datos = array(
					"id" => isset($_POST["idProducto"]) ? $_POST["idProducto"] : null,
					"id_categoria" => $_POST["editarCategoria"],
					"tiene_variantes" => $tieneVariantes,';
                    
    $content = preg_replace($pattern, $replacement, $content);
    $changes++;
    echo "✅ Fix 3 (tiene_variantes logic) aplicado.\n";
}

if ($changes > 0) {
    file_put_contents($file, $content);
    echo "Guardado exitosamente con $changes cambios.\n";
} else {
    echo "No se encontro donde aplicar los cambios.\n";
}
