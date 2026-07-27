<?php

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);

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

require_once "../controladores/estados-actividades.controlador.php";
require_once "../modelos/estados-actividades.modelo.php";

class AjaxEstadosActividades{

	/*=============================================
	EDITAR ESTADO DE ACTIVIDAD
	=============================================*/

	public $idEstado;

	public function ajaxEditarEstadoActividad(){

		try {
			$item = "id";
			$valor = $this->idEstado;

			$respuesta = ControladorEstadosActividades::ctrMostrarEstadosActividades($item, $valor);

			if($respuesta){
				echo json_encode($respuesta);
			} else {
				echo json_encode(array("error" => "No se encontró el estado"));
			}
		} catch (Exception $e) {
			echo json_encode(array("error" => $e->getMessage()));
		}
	}

}

/*=============================================
OBTENER ESTADO DE ACTIVIDAD PARA EDITAR
=============================================*/
if(isset($_POST["idEstado"]) && !isset($_POST["guardarEditarEstadoActividad"])){
	$estado = new AjaxEstadosActividades();
	$estado -> idEstado = $_POST["idEstado"];
	$estado -> ajaxEditarEstadoActividad();
	exit;
}

/*=============================================
GUARDAR CREAR ESTADO DE ACTIVIDAD
=============================================*/
if (isset($_POST["guardarCrearEstadoActividad"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoEstadoNombre"])) {
		$tabla = "estados_actividades";

		$estados = ModeloEstadosActividades::mdlMostrarEstadosActividades($tabla, null, null);
		$maxOrden = 0;
		if ($estados) {
			foreach ($estados as $estado) {
				if ($estado["orden"] > $maxOrden) {
					$maxOrden = $estado["orden"];
				}
			}
		}

		$datos = array(
			"nombre" => strtolower($_POST["nuevoEstadoNombre"]),
			"color" => $_POST["nuevoEstadoColor"],
			"orden" => $maxOrden + 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloEstadosActividades::mdlCrearEstado($tabla, $datos);
			if ($respuesta == "duplicado") {
				throw new Exception("Ya existe un estado de actividad con ese nombre.");
			} else if ($respuesta != "ok") {
				throw new Exception("Error al guardar el estado de actividad.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El estado de actividad ha sido guardado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del estado es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR ESTADO DE ACTIVIDAD
=============================================*/
if (isset($_POST["guardarEditarEstadoActividad"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarEstadoNombre"])) {
		$tabla = "estados_actividades";
		$datos = array(
			"id" => $_POST["idEstado"],
			"nombre" => $_POST["editarEstadoNombre"],
			"color" => $_POST["editarEstadoColor"],
			"orden" => $_POST["editarEstadoOrden"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloEstadosActividades::mdlEditarEstado($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el estado de actividad.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El estado de actividad ha sido editado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del estado es obligatorio."]);
	}
	exit;
}
