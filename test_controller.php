<?php
require 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
require 'c:/xampp/htdocs/pos/modelos/factus.modelo.php';
require 'c:/xampp/htdocs/pos/modelos/proveedores.modelo.php';

// Mock DB objects
$originalDS = [
    "id_proveedor" => 2,
    "monto_total" => 100,
    "monto_descuento" => 0,
    "retenciones" => "[]",
    "factus_id" => 484,
    "numero_ds" => "SEDS984000016",
    "cuds" => "d8815b5a771e2579b7b9f2f4290f07ec3d2f4a2fbb86d6dee24834ed1f4733e17060401ca961f31838bb942bb61fa3f4",
    "fecha_emision" => "2026-02-24"
];
$motivo = "1";
$motivoDescripcion = "";
$itemsAjuste = [
    ["id" => "112", "descripcion" => "prueba", "cantidad" => 1, "precio" => 100]
];
$metodoPago = "Bonos";

$res = ControladorFactus::prepararDatosNotaAjusteDS($originalDS, $motivo, $motivoDescripcion, $itemsAjuste, $metodoPago);
echo json_encode($res['provider']);

echo "\n--- LOG FILE ---\n";
if (file_exists('c:/xampp/htdocs/pos/ajax/log_mun.txt')) {
    echo file_get_contents('c:/xampp/htdocs/pos/ajax/log_mun.txt');
} else {
    echo "FILE NOT FOUND\n";
}
?>