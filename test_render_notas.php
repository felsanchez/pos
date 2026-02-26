<?php
session_start();
$_SESSION["perfil"] = "Administrador";

require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';
require_once 'controladores/proveedores.controlador.php';
require_once 'modelos/proveedores.modelo.php';

// Capturar salida
ob_start();
include 'vistas/modulos/notas-ajuste-ds.php';
$html = ob_get_clean();

// Buscar filas de la tabla
if (preg_match_all('/<tr>(.*?)<\/tr>/s', $html, $matches)) {
    foreach ($matches[1] as $row) {
        if (strpos($row, '<td>') !== false) {
            echo "ROW: " . strip_tags($row) . "\n";
        }
    }
} else {
    echo "No se encontraron filas TR.\n";
}
