<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";
require_once "../controladores/configuracion.controlador.php";
require_once "../modelos/configuracion.modelo.php";
require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";
require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

class AjaxVentasListado
{
	public function ajaxMostrarVentasServerSide()
	{
		$respuesta = ControladorVentas::ctrMostrarVentasServerSide($_POST);
		echo json_encode($respuesta);
	}
}

if (isset($_POST["draw"])) {
	$mostrarVentas = new AjaxVentasListado();
	$mostrarVentas->ajaxMostrarVentasServerSide();
}
