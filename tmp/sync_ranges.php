<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$res = ControladorFactus::ctrSincronizarRangos();
echo json_encode($res, JSON_PRETTY_PRINT);
?>