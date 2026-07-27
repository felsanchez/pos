<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/categorias_gastos.controlador.php";
require_once "../modelos/categorias_gastos.modelo.php";
require_once "../controladores/gastos.controlador.php";
require_once "../modelos/gastos.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

class AjaxCategoriasGastos{

	/*=============================================
	EDITAR CATEGORÍA DE GASTO
	=============================================*/

	public $idCategoria;

	public function ajaxEditarCategoriaGasto(){

		$item = "id";
		$valor = $this->idCategoria;

		$respuesta = ControladorCategoriasGastos::ctrMostrarCategoriasGastos($item, $valor); 

		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER TODAS LAS CATEGORÍAS
	=============================================*/ 

	public function ajaxObtenerCategoriasGastos(){ 

		$respuesta = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);

		echo json_encode($respuesta);
	}

}

/*=============================================
ELIMINAR CATEGORÍA DE GASTO
=============================================*/
if (isset($_POST["idCategoriaGastoEliminar"])) {
    $eliminar = new ControladorCategoriasGastos();
    $respuesta = $eliminar->ctrEliminarCategoriaGasto();
    echo $respuesta;
    exit;
}

/*=============================================
OBTENER CATEGORÍA DE GASTO PARA EDITAR
=============================================*/
if(isset($_POST["idCategoria"]) && !isset($_POST["guardarEditarCategoriaGasto"])){
	$categoria = new AjaxCategoriasGastos();
	$categoria -> idCategoria = $_POST["idCategoria"];
	$categoria -> ajaxEditarCategoriaGasto();
	exit;
}

/*=============================================
VALIDAR NO REPETIR CATEGORÍA DE GASTO
=============================================*/
if (isset($_POST["validarCategoriaGasto"])) {
	$tabla = "categorias_gastos";
	$item = "nombre";
	$valor = trim($_POST["validarCategoriaGasto"]);

	$respuesta = ModeloCategoriasGastos::mdlMostrarCategoriasGastos($tabla, $item, $valor);

	echo json_encode($respuesta);
	exit;
}

/*=============================================
GUARDAR CREAR CATEGORÍA DE GASTO
=============================================*/
if (isset($_POST["guardarCrearCategoriaGasto"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nombreCategoriaGasto"])) {
		$tabla = "categorias_gastos";
		$datos = array(
			"nombre" => trim($_POST["nombreCategoriaGasto"]),
			"color" => $_POST["colorCategoriaGasto"],
			"descripcion" => $_POST["descripcionCategoriaGasto"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloCategoriasGastos::mdlIngresarCategoriaGasto($tabla, $datos);
			if ($respuesta == "duplicado") {
				throw new Exception("¡Esta categoría de gasto ya existe en la base de datos!");
			} else if ($respuesta != "ok") {
				throw new Exception("Error al guardar la categoría de gasto.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La categoría de gasto ha sido guardada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la categoría es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR CATEGORÍA DE GASTO
=============================================*/
if (isset($_POST["guardarEditarCategoriaGasto"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarNombreCategoriaGasto"])) {
		$tabla = "categorias_gastos";
		$datos = array(
			"id" => $_POST["idCategoriaGasto"],
			"nombre" => trim($_POST["editarNombreCategoriaGasto"]),
			"color" => $_POST["editarColorCategoriaGasto"],
			"descripcion" => $_POST["editarDescripcionCategoriaGasto"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloCategoriasGastos::mdlEditarCategoriaGasto($tabla, $datos);
			if ($respuesta == "duplicado") {
				throw new Exception("¡Esta categoría de gasto ya existe en la base de datos!");
			} else if ($respuesta != "ok") {
				throw new Exception("Error al actualizar la categoría de gasto.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La categoría de gasto ha sido editada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la categoría es obligatorio."]);
	}
	exit;
}

/*=============================================
OBTENER TODAS LAS CATEGORÍAS
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerCategorias"){

	$categorias = new AjaxCategoriasGastos();
	$categorias -> ajaxObtenerCategoriasGastos();
}