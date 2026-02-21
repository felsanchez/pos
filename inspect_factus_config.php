<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$config = ControladorFactus::ctrObtenerConfiguracion();

echo "<pre>";
print_r($config);
echo "</pre>";
?>