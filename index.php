<?php

// Mostrar errores en pantalla (solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//error_reporting(E_ALL);

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);


require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/actividades.controlador.php";
require_once "controladores/proveedores.controlador.php";
require_once "controladores/variantes.controlador.php";
require_once "controladores/movimientos.controlador.php";
require_once "controladores/estados-clientes.controlador.php";
require_once "controladores/estados-actividades.controlador.php";
require_once "controladores/tipos-actividades.controlador.php";
require_once "controladores/gastos.controlador.php";
require_once "controladores/categorias_gastos.controlador.php";
require_once "controladores/notificaciones.controlador.php";
require_once "controladores/configuracion.controlador.php";
require_once "controladores/factus.controlador.php";
require_once "controladores/logs.controlador.php";
require_once "controladores/seguimiento.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/actividades.modelo.php";
require_once "modelos/proveedores.modelo.php";
require_once "modelos/variantes.modelo.php";
require_once "modelos/movimientos.modelo.php";
require_once "modelos/estados-clientes.modelo.php";
require_once "modelos/estados-actividades.modelo.php";
require_once "modelos/tipos-actividades.modelo.php";
require_once "modelos/gastos.modelo.php";
require_once "modelos/categorias_gastos.modelo.php";
require_once "modelos/notificaciones.modelo.php";
require_once "modelos/configuracion.modelo.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/seguimiento.modelo.php";

// Exportar Excel de Historial de Stock
ControladorMovimientos::ctrExportarExcel();

$plantilla = new ControladorPlantilla();
$plantilla->ctrPlantilla();
