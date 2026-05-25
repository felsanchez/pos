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

// Let's modify ventas.modelo.php temporarily or just look at ventas.controlador.php
echo "Test";
