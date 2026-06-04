<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = 54");
$stmt->execute();
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die("Sale 54 not found!\n");
}

$payload = ControladorFactus::prepararDatosFactura($venta);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
