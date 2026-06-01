<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['drawOrdenes'] = 1;
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 1;
$_POST['search'] = ['value' => ''];

require_once "modelos/conexion.php";
require_once "controladores/configuracion.controlador.php";
require_once "modelos/configuracion.modelo.php";
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
require_once "controladores/clientes.controlador.php";
require_once "modelos/clientes.modelo.php";
require_once "controladores/cajas.controlador.php";
require_once "modelos/cajas.modelo.php";
require_once "modelos/helpers.php";
require_once "modelos/sanitizer.php";

session_start();
$_SESSION['perfil'] = "Administrador";

try {
    $respuesta = ControladorVentas::ctrMostrarOrdenesServerSide($_POST);
    echo json_encode($respuesta);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
}
