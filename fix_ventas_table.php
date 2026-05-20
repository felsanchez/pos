<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$search = '$productos = ModeloProductos::mdlMostrarProductosServerSide($tabla, $where, $order, $limit);';
$replace = '$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
		$productos = ModeloProductos::mdlMostrarProductosServerSide($tabla, $where, $order, $limit, $idBodegaActiva);';

$newContent = str_replace($search, $replace, $content);
file_put_contents($file, $newContent);
echo "✅ Se agregó el parámetro de bodega a la consulta de la tabla de ventas.\n";
