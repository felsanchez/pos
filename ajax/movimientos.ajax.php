<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

// Asegurar que no haya output antes del JSON
error_reporting(E_ALL);

require_once "../controladores/movimientos.controlador.php";
require_once "../modelos/movimientos.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

class AjaxMovimientos
{

	/*=============================================
	OBTENER MOVIMIENTOS CON FILTROS
	=============================================*/
	public function ajaxObtenerMovimientos()
	{

		try {
			$filtros = array();

			if (isset($_POST["id_producto"]) && $_POST["id_producto"] != "") {
				$filtros["id_producto"] = $_POST["id_producto"];
			}

			if (isset($_POST["tipo_movimiento"]) && $_POST["tipo_movimiento"] != "") {
				$filtros["tipo_movimiento"] = $_POST["tipo_movimiento"];
			}

			if (isset($_POST["fecha_desde"]) && $_POST["fecha_desde"] != "") {
				$filtros["fecha_desde"] = $_POST["fecha_desde"];
			}

			if (isset($_POST["fecha_hasta"]) && $_POST["fecha_hasta"] != "") {
				$filtros["fecha_hasta"] = $_POST["fecha_hasta"];
			}

			if (isset($_POST["usuario"]) && $_POST["usuario"] != "") {
				$filtros["usuario"] = $_POST["usuario"];
			}

			$movimientos = ModeloMovimientos::mdlMostrarMovimientos($filtros);

			// Asegurar que siempre devolvemos un array
			if ($movimientos === false || $movimientos === null) {
				$movimientos = array();
			}

			header('Content-Type: application/json');
			echo json_encode($movimientos);

		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(array("error" => $e->getMessage()));
		}
	}

	/*=============================================
	OBTENER RESUMEN DE MOVIMIENTOS
	=============================================*/
	public function ajaxObtenerResumen()
	{

		try {
			$filtros = array();

			if (isset($_POST["fecha_desde"]) && $_POST["fecha_desde"] != "") {
				$filtros["fecha_desde"] = $_POST["fecha_desde"];
			}

			if (isset($_POST["fecha_hasta"]) && $_POST["fecha_hasta"] != "") {
				$filtros["fecha_hasta"] = $_POST["fecha_hasta"];
			}

			$resumen = ModeloMovimientos::mdlObtenerResumen($filtros);

			// Asegurar que siempre devolvemos un array
			if ($resumen === false || $resumen === null) {
				$resumen = array();
			}

			header('Content-Type: application/json');
			echo json_encode($resumen);

		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(array("error" => $e->getMessage()));
		}
	}

	/*=============================================
	ELIMINAR MOVIMIENTOS
	=============================================*/
	public function ajaxEliminarMovimientos()
	{

		if (isset($_POST["idsEliminarMovimientos"])) {

			$respuesta = ModeloMovimientos::mdlEliminarMovimientos($_POST["idsEliminarMovimientos"]);
			echo $respuesta;

		}
	}

}

/*=============================================
OBTENER MOVIMIENTOS
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerMovimientos") {
	$obtener = new AjaxMovimientos();
	$obtener->ajaxObtenerMovimientos();
}

/*=============================================
OBTENER RESUMEN
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerResumen") {
	$obtener = new AjaxMovimientos();
	$obtener->ajaxObtenerResumen();
}


/*=============================================
ACTUALIZAR NOTA
=============================================*/

if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarNota") {
	$respuesta = ModeloMovimientos::mdlActualizarNota("movimientos_stock", $_POST["id"], $_POST["notas"]);
	echo json_encode($respuesta);
}

/*=============================================
ELIMINAR MOVIMIENTOS
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "eliminarMovimientos") {
	$eliminar = new AjaxMovimientos();
	$eliminar->ajaxEliminarMovimientos();
}