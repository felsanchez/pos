<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->query("SELECT id, nombre, codigo FROM factus_tipos_documento");
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "ID: {$row['id']} | Nombre: {$row['nombre']} | Codigo: {$row['codigo']}\n";
    }
} catch(Exception $e) {
    echo $e->getMessage();
}
