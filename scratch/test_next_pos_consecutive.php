<?php
require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";

$next = ModeloVentas::mdlObtenerSiguienteConsecutivo("ventas");
echo "Next POS consecutive: $next\n";
