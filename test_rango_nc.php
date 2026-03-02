<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";
require_once "modelos/configuracion.modelo.php";
require_once "modelos/factus.modelo.php";

$rango = ModeloFactus::mdlObtenerRangoNC();
echo json_encode($rango);
?>