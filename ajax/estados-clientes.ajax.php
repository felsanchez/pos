<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/estados-clientes.controlador.php";

require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once "../modelos/estados-clientes.modelo.php"; 

class AjaxEstadosClientes{ 

	/*=============================================
	EDITAR ESTADO
	=============================================*/ 

	public $idEstado; 

	public function ajaxEditarEstado(){ 

		$item = "id";
		$valor = $this->idEstado; 

		$respuesta = ControladorEstadosClientes::ctrMostrarEstadosClientes($item, $valor); 

		echo json_encode($respuesta);
	}
} 

/*=============================================
OBTENER ESTADO PARA EDITAR
=============================================*/
if(isset($_POST["idEstado"]) && !isset($_POST["guardarEditarEstado"])){ 
	$estado = new AjaxEstadosClientes();
	$estado -> idEstado = $_POST["idEstado"];
	$estado -> ajaxEditarEstado();
	exit;
}

/*=============================================
GUARDAR CREAR ESTADO
=============================================*/
if (isset($_POST["guardarCrearEstado"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoEstadoNombre"])) {
		$tabla = "estados_clientes";

		$estados = ModeloEstadosClientes::mdlMostrarEstadosClientes($tabla, null, null);
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
			$respuesta = ModeloEstadosClientes::mdlCrearEstado($tabla, $datos);
			if ($respuesta == "duplicado") {
				throw new Exception("Ya existe un estado con ese nombre.");
			} else if ($respuesta != "ok") {
				throw new Exception("Error al guardar el estado.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El estado ha sido guardado correctamente!"]);
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
GUARDAR EDITAR ESTADO
=============================================*/
if (isset($_POST["guardarEditarEstado"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarEstadoNombre"])) {
		$tabla = "estados_clientes";
		$datos = array(
			"id" => $_POST["idEstado"],
			"nombre" => $_POST["editarEstadoNombre"],
			"color" => $_POST["editarEstadoColor"],
			"orden" => $_POST["editarEstadoOrden"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloEstadosClientes::mdlEditarEstado($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el estado.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El estado ha sido editado correctamente!"]);
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
ELIMINAR ESTADO
=============================================*/
if (isset($_POST["idEstadoEliminar"])) {
    $eliminar = new ControladorEstadosClientes();
    $respuesta = $eliminar->ctrEliminarEstado();
    echo $respuesta;
    exit;
}