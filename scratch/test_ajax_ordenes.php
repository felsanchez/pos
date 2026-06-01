<?php
if (!isset($_SESSION)) {
    session_start();
}
$_SESSION["perfil"] = "Administrador";
$_SESSION["id_bodega"] = 1;

require_once __DIR__ . "/../modelos/sanitizer.php";
require_once __DIR__ . "/../modelos/helpers.php";
require_once __DIR__ . "/../controladores/ventas.controlador.php";
require_once __DIR__ . "/../modelos/ventas.modelo.php";
require_once __DIR__ . "/../controladores/productos.controlador.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";
require_once __DIR__ . "/../controladores/clientes.controlador.php";
require_once __DIR__ . "/../modelos/clientes.modelo.php";
require_once __DIR__ . "/../controladores/usuarios.controlador.php";
require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../controladores/notificaciones.controlador.php";
require_once __DIR__ . "/../modelos/notificaciones.modelo.php";
require_once __DIR__ . "/../controladores/configuracion.controlador.php";
require_once __DIR__ . "/../modelos/configuracion.modelo.php";
require_once __DIR__ . "/../controladores/factus.controlador.php";
require_once __DIR__ . "/../modelos/factus.modelo.php";
require_once __DIR__ . "/../controladores/movimientos.controlador.php";
require_once __DIR__ . "/../modelos/movimientos.modelo.php";

$params = [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
    'search' => ['value' => ''],
    'order' => [
        0 => ['column' => 8, 'dir' => 'desc']
    ]
];

try {
    $res = ControladorVentas::ctrMostrarOrdenesServerSide($params);
    echo "SUCCESS:\n";
    print_r($res);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " L" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
