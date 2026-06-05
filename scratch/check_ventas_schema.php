<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SHOW CREATE TABLE ventas");
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $res['Create Table'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
