<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";

class TablaProductosTraslados
{
	public function mostrarTabla()
	{
		if (isset($_POST["draw"])) {
			// El idBodega viene en $_POST enviado por el JS de traslados.
			// Lo pasamos limpiamente en $params sin tocar la sesión.
			$respuesta = ControladorProductos::ctrMostrarProductosVentasServerSide($_POST);
			echo json_encode($respuesta);
		} else {
			echo json_encode(array("data" => []));
		}
	}
}

$activar = new TablaProductosTraslados();
$activar->mostrarTabla();
