<?php

// Mostrar errores en pantalla (solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


	require_once "controladores/plantilla.controlador.php";
	require_once "controladores/usuarios.controlador.php";
	require_once "controladores/categorias.controlador.php";
	require_once "controladores/productos.controlador.php";
	require_once "controladores/clientes.controlador.php";
	require_once "controladores/ventas.controlador.php";
	require_once "controladores/actividades.controlador.php";

	require_once "modelos/usuarios.modelo.php";
	require_once "modelos/categorias.modelo.php";
	require_once "modelos/productos.modelo.php";
	require_once "modelos/clientes.modelo.php";
	require_once "modelos/ventas.modelo.php";
	require_once "modelos/actividades.modelo.php";


	$plantilla = new ControladorPlantilla();
	$plantilla -> ctrPlantilla();
