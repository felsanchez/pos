<?php
require_once "modelos/conexion.php";

$sql = "ALTER TABLE `documentos_soporte` MODIFY `metodo_pago` VARCHAR(50) NOT NULL;";

try {
    $stmt = Conexion::conectar()->prepare($sql);
    if ($stmt->execute()) {
        echo "Tabla documentos_soporte actualizada exitosamente: columna metodo_pago ampliada a 50 caracteres.\n";
    } else {
        echo "Error al actualizar la tabla.\n";
    }
} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
}
