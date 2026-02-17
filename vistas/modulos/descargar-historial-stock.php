<?php

require_once "../../controladores/movimientos.controlador.php";
require_once "../../modelos/movimientos.modelo.php";
require_once "../../controladores/productos.controlador.php";
require_once "../../modelos/productos.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";

$reporte = new ControladorMovimientos();
$reporte->ctrDescargarHistorialStock();
