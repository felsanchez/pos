<?php
require_once "modelos/session-manager.php";
SessionManager::startSecure();
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();
    // Actualizar el número actual a 24, para que el siguiente sea 25
    $stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = 24 WHERE id_factus = 1040");
    $stmt->execute();
    echo "Rango Actualizado Correctamente a 24.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
