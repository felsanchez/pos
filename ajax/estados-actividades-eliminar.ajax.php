<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/estados-actividades.controlador.php";

require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once "../modelos/estados-actividades.modelo.php";

class AjaxEliminarEstadoActividad {

	public $idEstado;
	public $nombreEstado;

	public function ajaxEliminarEstadoActividad() {

		$tabla = "estados_actividades";

		// Verificar si el estado está en uso
		$enUso = ModeloEstadosActividades::mdlVerificarEstadoEnUso($this->nombreEstado);

		if ($enUso > 0) {
			echo json_encode(array(
				"status" => "error",
				"type" => "en_uso",
				"message" => "Este estado está siendo usado por " . $enUso . " actividad(es)."
			));
			return;
		}

		// Eliminar el estado
		$respuesta = ModeloEstadosActividades::mdlEliminarEstado($tabla, $this->idEstado);

		if ($respuesta == "ok") {
			echo json_encode(array(
				"status" => "success",
				"message" => "El estado ha sido eliminado correctamente"
			));
		} else {
			echo json_encode(array(
				"status" => "error",
				"type" => "general",
				"message" => "Error al eliminar el estado"
			));
		}
	}
}

if (isset($_POST["idEstado"])) {
	$eliminar = new AjaxEliminarEstadoActividad();
	$eliminar->idEstado = $_POST["idEstado"];
	$eliminar->nombreEstado = $_POST["nombreEstado"];
	$eliminar->ajaxEliminarEstadoActividad();
}
