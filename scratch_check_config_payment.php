<?php
require_once "modelos/conexion.php";
require_once "controladores/configuracion.controlador.php";
require_once "modelos/configuracion.modelo.php";
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
echo "medios_pago: " . ($configuracion["medios_pago"] ?? "NULL") . "\n";
?>
