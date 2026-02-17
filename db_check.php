<?php
require_once "modelos/conexion.php";
try {
    $con = Conexion::conectar();
    echo "Conectado exitosamente";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>