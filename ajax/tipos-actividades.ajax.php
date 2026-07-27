<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/tipos-actividades.controlador.php";
require_once "../modelos/tipos-actividades.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
 

class AjaxTiposActividades{ 

	/*=============================================
	EDITAR TIPO
	=============================================*/ 

	public $idTipo; 

	public function ajaxEditarTipo(){ 

		$item = "id";
		$valor = $this->idTipo;
		$respuesta = ControladorTiposActividades::ctrMostrarTiposActividades($item, $valor);
		echo json_encode($respuesta);
	}
} 

/*=============================================
OBTENER TIPO DE ACTIVIDAD PARA EDITAR
=============================================*/
if(isset($_POST["idTipo"]) && !isset($_POST["guardarEditarTipoActividad"])){ 
	$editar = new AjaxTiposActividades();
	$editar -> idTipo = $_POST["idTipo"];
	$editar -> ajaxEditarTipo();
	exit;
}

/*=============================================
GUARDAR CREAR TIPO DE ACTIVIDAD
=============================================*/
if (isset($_POST["guardarCrearTipoActividad"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoTipoNombre"])) {
		$tabla = "tipos_actividades";

		$tipos = ModeloTiposActividades::mdlMostrarTiposActividades($tabla, null, null);
		$maxOrden = 0;
		if ($tipos) {
			foreach ($tipos as $tipo) {
				if ($tipo["orden"] > $maxOrden) {
					$maxOrden = $tipo["orden"];
				}
			}
		}

		$datos = array(
			"nombre" => trim($_POST["nuevoTipoNombre"]),
			"orden" => $maxOrden + 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloTiposActividades::mdlCrearTipo($tabla, $datos);
			if ($respuesta == "duplicado") {
				throw new Exception("Ya existe un tipo de actividad con ese nombre.");
			} else if ($respuesta != "ok") {
				throw new Exception("Error al guardar el tipo de actividad.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El tipo de actividad ha sido guardado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del tipo es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR TIPO DE ACTIVIDAD
=============================================*/
if (isset($_POST["guardarEditarTipoActividad"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarTipoNombre"])) {
		$tabla = "tipos_actividades";
		$datos = array(
			"id" => $_POST["idTipo"],
			"nombre" => trim($_POST["editarTipoNombre"]),
			"orden" => (isset($_POST["editarTipoOrden"]) && $_POST["editarTipoOrden"] !== "") ? intval($_POST["editarTipoOrden"]) : 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloTiposActividades::mdlEditarTipo($tabla, $datos);
			if ($respuesta == "duplicado") {
				throw new Exception("Ya existe un tipo de actividad con ese nombre.");
			} else if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el tipo de actividad.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El tipo de actividad ha sido editado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del tipo es obligatorio."]);
	}
	exit;
}

/*=============================================
ELIMINAR TIPO
=============================================*/
if (isset($_POST["idTipoEliminar"])) {
    $eliminar = new ControladorTiposActividades();
    $respuesta = $eliminar->ctrEliminarTipo();
    echo $respuesta;
    exit;
}