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
	exit;
}

/*=============================================
GUARDAR CREAR LEAD
=============================================*/
if (isset($_POST["guardarCrearLead"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoLeadTitulo"])) {
		$tabla = "crm_leads";
		$datos = array(
			"id_cliente" => $_POST["nuevoLeadCliente"],
			"titulo" => $_POST["nuevoLeadTitulo"],
			"valor_estimado" => $_POST["nuevoLeadValor"],
			"prioridad" => $_POST["nuevoLeadPrioridad"],
			"etapa" => $_POST["nuevoLeadEtapa"],
			"id_vendedor" => $_POST["nuevoLeadVendedor"],
			"fecha_cierre" => !empty($_POST["nuevoLeadFechaCierre"]) ? $_POST["nuevoLeadFechaCierre"] : null,
			"notas" => $_POST["nuevoLeadNotas"],
			"codigo_orden" => null,
			"orden" => 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			ModeloCRM::mdlDesplazarLeadsEnEtapa($tabla, $_POST["nuevoLeadEtapa"]);
			$respuesta = ModeloCRM::mdlCrearLead($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al registrar el lead.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El lead ha sido guardado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El título del negocio es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR LEAD
=============================================*/
if (isset($_POST["guardarEditarLead"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarLeadTitulo"])) {
		$tabla = "crm_leads";
		$datos = array(
			"id" => $_POST["editarLeadId"],
			"titulo" => $_POST["editarLeadTitulo"],
			"id_cliente" => $_POST["editarLeadCliente"],
			"valor" => floatval($_POST["editarLeadValor"]),
			"prioridad" => $_POST["editarLeadPrioridad"],
			"etapa" => $_POST["editarLeadEtapa"],
			"id_vendedor" => $_POST["editarLeadVendedor"],
			"fecha_cierre_estimado" => $_POST["editarLeadFechaCierre"],
			"notas" => $_POST["editarLeadNotas"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloCRM::mdlEditarLead($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el lead.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El lead ha sido editado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El título del negocio es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR ELIMINAR LEAD
=============================================*/
if (isset($_POST["guardarEliminarLead"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	$tabla = "crm_leads";
	$idLead = $_POST["idLeadEliminar"];

	$db = Conexion::conectar();
	try {
		$db->beginTransaction();
		$respuesta = ModeloCRM::mdlEliminarLead($tabla, $idLead);
		if ($respuesta != "ok") {
			throw new Exception("Error al eliminar el lead.");
		}
		$db->commit();
		echo json_encode(["status" => "ok", "mensaje" => "¡El lead ha sido eliminado correctamente!"]);
	} catch (Exception $e) {
		$db->rollBack();
		echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
	}
	exit;
}
