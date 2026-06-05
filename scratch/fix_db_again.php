<?php
require_once __DIR__ . "/../modelos/conexion.php";

$db = Conexion::conectar();

// 1. Update id 86 to 990000314
$stmt1 = $db->prepare("UPDATE ventas SET codigo = '990000314' WHERE id = 86");
if ($stmt1->execute()) {
    echo "Successfully updated invoice ID 86 code to 990000314\n";
} else {
    echo "Failed to update ID 86\n";
}

// 2. Update consecutivos to 10004
$stmt2 = $db->prepare("UPDATE consecutivos SET ultimo_numero = 10004 WHERE tabla = 'ventas'");
if ($stmt2->execute()) {
    echo "Successfully restored POS consecutive to 10004\n";
} else {
    echo "Failed to restore POS consecutive\n";
}
