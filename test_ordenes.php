<?php
require 'modelos/session-manager.php'; 
SessionManager::startSecure(); 
$_SESSION['id_bodega'] = 1; 
$_POST['drawOrdenes'] = 1; 
$_POST['start'] = 0; 
$_POST['length'] = 10; 
$_POST['bodegaId'] = ''; 
require_once 'modelos/helpers.php';
require_once 'controladores/ventas.controlador.php'; 
require_once 'modelos/ventas.modelo.php'; 
require_once 'controladores/configuracion.controlador.php'; 
require_once 'modelos/configuracion.modelo.php'; 

$data = ControladorVentas::ctrMostrarOrdenesServerSide($_POST); 
echo json_encode(array_map(function($row){ return $row[0]; }, $data['data']));
