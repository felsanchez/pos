<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM ventas WHERE id = 58");
    $stmt->execute();
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($venta) {
        $datosFactura = ControladorFactus::prepararDatosFactura($venta);
        print_r($datosFactura);
    } else {
        echo "No sale found with ID 58\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
