<?php

require_once "../config.php";
require_once "../modelos/session-manager.php";
SessionManager::startSecure();
require_once "../controladores/crm.controlador.php";
require_once "../modelos/crm.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";

class AjaxCRM {

	public $idLead;
	public $nuevaEtapa;

	/*=============================================
	ACTUALIZAR ETAPA LEAD (DRAG & DROP)
	=============================================*/
	public function ajaxActualizarEtapa() {

		// Validar token CSRF
		if (!CSRF::validateToken()) {
			echo json_encode("error_csrf");
			return;
		}

		if (!puedeAccion('crm', 'editar')) {
			echo json_encode("error_permisos");
			return;
		}

		$tabla = "crm_leads";
		$ordenes = isset($_POST["ordenes"]) ? $_POST["ordenes"] : null;

		if (is_array($ordenes)) {
			$ok = true;
			foreach ($ordenes as $idx => $id) {
				$ordenVal = $idx + 1;
				if ($id == $this->idLead) {
					$res = ModeloCRM::mdlActualizarEtapaYOrden($tabla, $id, $this->nuevaEtapa, $ordenVal);
				} else {
					$res = ModeloCRM::mdlActualizarOrdenLead($tabla, $id, $ordenVal);
				}
				if ($res != "ok") {
					$ok = false;
				}
			}
			$respuesta = $ok ? "ok" : "error";
		} else {
			$respuesta = ModeloCRM::mdlActualizarEtapa($tabla, $this->idLead, $this->nuevaEtapa);
		}

		echo json_encode($respuesta);

	}

	/*=============================================
	OBTENER DETALLE DE LEAD
	=============================================*/
	public function ajaxObtenerLead() {

		if (!puedeVer('crm')) {
			echo json_encode("error_permisos");
			return;
		}

		$item = "id";
		$valor = $this->idLead;
		$respuesta = ControladorCRM::ctrMostrarLeads($item, $valor);

		echo json_encode($respuesta);

	}

}

/*=============================================
ACTUALIZAR ETAPA LEAD (DRAG & DROP)
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "actualizarEtapa") {

	$actualizar = new AjaxCRM();
	$actualizar->idLead = $_POST["idLead"];
	$actualizar->nuevaEtapa = $_POST["nuevaEtapa"];
	$actualizar->ajaxActualizarEtapa();

}

/*=============================================
OBTENER DETALLE DE LEAD
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerLead") {

	$obtener = new AjaxCRM();
	$obtener->idLead = $_POST["idLead"];
	$obtener->ajaxObtenerLead();

}
