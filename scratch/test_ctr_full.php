<?php
require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/movimientos.modelo.php";

require_once "controladores/ventas.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/notificaciones.controlador.php";
require_once "controladores/factus.controlador.php";
require_once "controladores/movimientos.controlador.php";
require_once "modelos/configuracion.modelo.php";
require_once "controladores/configuracion.controlador.php";

// Get valid IDs
$stmt = Conexion::conectar()->prepare("SELECT id FROM usuarios LIMIT 1");
$stmt->execute();
$idVendedor = $stmt->fetchColumn();

$stmt = Conexion::conectar()->prepare("SELECT id FROM clientes LIMIT 1");
$stmt->execute();
$idCliente = $stmt->fetchColumn();

$stmt = Conexion::conectar()->prepare("SELECT id, descripcion, precio_venta FROM productos WHERE stock > 0 LIMIT 1");
$stmt->execute();
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

// Simulate POST
$_POST["guardarVentaFactus"] = 1;
$_POST["nuevaVenta"] = 30000;
$_POST["editarVenta"] = "20000"; // fake original
$_POST["listaProductos"] = '[{"id":"' . $prod['id'] . '","descripcion":"' . $prod['descripcion'] . '","cantidad":"1","stock":"99","precio":"' . $prod['precio_venta'] . '","total":"' . $prod['precio_venta'] . '"}]';
$_POST["estado"] = "venta";
$_POST["idVendedor"] = $idVendedor;
$_POST["seleccionarCliente"] = $idCliente;
$_POST["nuevoPrecioImpuesto"] = 0;
$_POST["nuevoPrecioNeto"] = $prod['precio_venta'];
$_POST["totalVenta"] = $prod['precio_venta'];
$_POST["listaMetodoPago"] = "Efectivo";

$guardarFE = new ControladorVentas();
ob_start();
$guardarFE->ctrCrearVentaFactus();
$output = ob_get_clean();

echo "Ejecución completada.\n";
?>
