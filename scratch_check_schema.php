<?php
require_once "modelos/conexion.php";
try {
    $stmt = Conexion::conectar()->prepare("DESCRIBE documentos_soporte");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
