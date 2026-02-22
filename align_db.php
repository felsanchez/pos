<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    // Aligning recent records to release 990000162
    // ID 692: 164 -> 161
    $db->prepare("UPDATE ventas SET codigo = '990000161' WHERE id = 692")->execute();

    // ID 691 (archivada): 163 -> stays 163 (it's fine)

    // ID 690: 162 -> 160
    $db->prepare("UPDATE ventas SET codigo = '990000160' WHERE id = 690")->execute();

    // ID 689: 161 -> 159
    $db->prepare("UPDATE ventas SET codigo = '990000159' WHERE id = 689")->execute();

    // ID 688: 160 -> 158
    $db->prepare("UPDATE ventas SET codigo = '990000158' WHERE id = 688")->execute();

    $db->commit();
    echo "OK: Sequential alignment complete. Internal code 162 is now free.";
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>