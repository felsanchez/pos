<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Iniciando debug...\n";

try {
    require_once "controladores/ventas.controlador.php";
    echo "Controlador cargado.\n";

    // Simulate what AJAX does
    // It loads model too
    require_once "modelos/ventas.modelo.php";
    echo "Modelo cargado.\n";

    if (!class_exists('Conexion')) {
        echo "Clase Conexion NO existe (Revisar importación en Modelo).\n";
    } else {
        echo "Clase Conexion existe.\n";
    }

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Error fatal: " . $e->getMessage() . "\n";
}
?>