<?php

require_once __DIR__ . '/../models/producto.model.php';
require_once __DIR__ . '/../response.php';

$consulta = trim($params['consulta'] ?? '');

if ($consulta === '') {

    successResponse([
        'tipo' => 'solicitar_producto',
        'mensaje' => '¿Qué producto o categoría deseas buscar?'
    ]);

}

$consultaNormalizada = mb_strtolower($consulta);

if (in_array($consultaNormalizada, [
    'todos',
    'todo',
    'productos',
    'producto',
    'catalogo',
    'catálogo',
    'articulos',
    'artículos'
])) {

    $resultado = ProductoModel::masVendidos(20);

} else {

    $resultado = ProductoModel::buscar($consulta);

}

/*
|--------------------------------------------------------------------------
| Solo conservar la imagen cuando exista un único producto
|--------------------------------------------------------------------------
*/

if (count($resultado) !== 1) {

    foreach ($resultado as &$producto) {

        $producto["imagen"] = null;
        $producto["tiene_imagen"] = false;

    }

    unset($producto);

}

successResponse($resultado);