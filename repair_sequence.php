<?php
require_once 'modelos/conexion.php';
try {
    $stmt = Conexion::conectar()->prepare("UPDATE ventas SET estado = 'archivada' WHERE id = 691");
    if ($stmt->execute()) {
        echo "OK: Registro 691 archivado correctamente.";
    } else {
        echo "ERROR: No se pudo actualizar el registro.";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>