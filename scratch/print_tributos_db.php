<?php
require_once "modelos/conexion.php";
try {
    $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_tributos");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | Código: {$row['codigo']} | Nombre: {$row['nombre']} | Porcentaje: {$row['porcentaje_defecto']} | Activo: {$row['activo']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
