<?php

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

// Solo permitir si el usuario es _SystemMaster_
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok" || $_SESSION["perfil"] !== "_SystemMaster_") {
	http_response_code(403);
	die(json_encode(['error' => 'No autorizado']));
}

require_once "../controladores/tenants.controlador.php";
require_once "../modelos/tenants.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!CSRF::validateToken()) {
		http_response_code(403);
		die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
	}
}

class AjaxTenants {

	public $idTenant;

	public function ajaxEditarTenant() {
		$item = "id";
		$valor = $this->idTenant;

		$respuesta = ControladorTenants::ctrMostrarTenants($item, $valor);

		echo json_encode($respuesta);
	}
}

if (isset($_POST["idTenant"])) {
	$val = new AjaxTenants();
	$val->idTenant = $_POST["idTenant"];
	$val->ajaxEditarTenant();
}
