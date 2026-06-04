<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT id, descripcion, tributo_id FROM productos LIMIT 15");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $tributoId = $row['tributo_id'] === null ? "NULL" : $row['tributo_id'];
        echo "ID: {$row['id']} | Desc: {$row['descripcion']} | tributo_id: {$tributoId}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
