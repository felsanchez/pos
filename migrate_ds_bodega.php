<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("UPDATE documentos_soporte SET id_bodega = 1 WHERE id_bodega IS NULL OR id_bodega = 0");
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "Success: Updated $count records to Bodega Principal (ID 1).";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
