<?php
require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/productos.controlador.php";

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
$_POST["nuevaVenta"] = 20000;
$_POST["editarVenta"] = "99999"; // Fake original order code
$_POST["listaProductos"] = '[{"id":"' . $prod['id'] . '","descripcion":"' . $prod['descripcion'] . '","cantidad":"1","stock":"99","precio":"' . $prod['precio_venta'] . '","total":"' . $prod['precio_venta'] . '"}]';
$_POST["estado"] = "venta";
$_POST["idVendedor"] = $idVendedor;
$_POST["seleccionarCliente"] = $idCliente;
$_POST["nuevoPrecioImpuesto"] = 0;
$_POST["nuevoPrecioNeto"] = $prod['precio_venta'];
$_POST["totalVenta"] = $prod['precio_venta'];
$_POST["listaMetodoPago"] = "Efectivo";

$guardarFE = new ControladorVentas();
// We can't easily run ctrCrearVentaFactus because it uses $_SERVER and might trigger JS redirects, but let's try.
ob_start();
$guardarFE->ctrCrearVentaFactus();
$output = ob_get_clean();

echo "Ejecución finalizada.\n";

// Check DB
$stmt = Conexion::conectar()->prepare("SELECT orden_compra FROM ventas WHERE codigo = 20000 ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->fetch();
echo "orden_compra in DB: " . ($result ? $result["orden_compra"] : "NO INSERTED") . "\n";

// cleanup
$stmt = Conexion::conectar()->prepare("DELETE FROM ventas WHERE codigo = 20000");
$stmt->execute();
?>
