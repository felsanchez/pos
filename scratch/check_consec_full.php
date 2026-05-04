<?php
require_once "controladores/configuracion.controlador.php";
require_once "modelos/configuracion.modelo.php";
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

echo "Consecutivo: " . ModeloFactus::mdlObtenerSiguienteConsecutivoFactus() . "\n";
?>
