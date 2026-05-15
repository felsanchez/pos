<?php
require_once "modelos/session-manager.php";
SessionManager::startSecure();
require_once "controladores/productos.controlador.php";
require_once "modelos/productos.modelo.php";

$idProducto = 111; // Usaré uno de los productos de prueba
$idBodegaActiva = 1;
$stock = 50;

echo "Probando insercion manual de bodega para producto $idProducto con stock $stock en bodega $idBodegaActiva\n";

$resultado = ModeloProductos::mdlActualizarStockBodega($idProducto, $idBodegaActiva, $stock);
echo "Resultado de la funcion: $resultado\n";

// Verificar si se guardo
$stmt = Conexion::conectar()->prepare("SELECT * FROM productos_bodegas WHERE id_producto = :id");
$stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Registro en BD:\n";
print_r($row);
