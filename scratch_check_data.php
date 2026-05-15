<?php
require_once "modelos/conexion.php";
try {
    $stmt = Conexion::conectar()->prepare("SELECT id, id_bodega, numero_ds FROM documentos_soporte ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
