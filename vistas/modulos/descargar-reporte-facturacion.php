<?php

require_once "../../controladores/factus.controlador.php";
require_once "../../modelos/factus.modelo.php";

$reporte = new ControladorFactus();
$reporte->ctrDescargarReporteFacturacion();

?>