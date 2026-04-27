<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/categorias.modelo.php";
require_once "../modelos/proveedores.modelo.php";
require_once "../modelos/usuarios.modelo.php"; // Necesario para mdlGetTotalUsuarios
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";

class TablaProductos
{
	public function mostrarTabla()
	{
		// Si es una petición Server-Side de DataTables
		if (isset($_POST["draw"])) {
			$respuesta = ControladorProductos::ctrMostrarProductosServerSide($_POST);
			echo json_encode($respuesta);
		} else {
			// Mantener compatibilidad si se llama sin parámetros (aunque JS ya usará Server-Side)
			$item = null;
			$valor = null;
			$orden = "id";
			$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
			echo json_encode(array("data" => $productos));
		}
	}
}

$activar = new TablaProductos();
$activar->mostrarTabla();
