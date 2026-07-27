<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

/*=============================================
TABLA CATEGORIAS SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
    require_once "../modelos/sanitizer.php";
    require_once "../modelos/helpers.php";
    $respuesta = ControladorCategorias::ctrMostrarCategoriasServerSide($_POST);
    echo json_encode($respuesta);
    exit;
}

class AjaxCategorias{

	/*=============================================
	EDITAR CATEGORIA
	=============================================*/

	public $idCategoria;

	public function ajaxEditarCategoria(){

		$item = "id";
		$valor = $this->idCategoria;

		$respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);

		echo json_encode($respuesta);

	}


	/*=============================================
	HPM VALIDAR NO REPETIR CATEGORIA
	=============================================*/

	public $validarCategoria;
	public function ajaxValidarCategoria(){

		$item = "categoria";
		$valor = $this->validarCategoria;

		$respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);

		echo json_encode($respuesta);
	}

	/*=============================================
	VALIDAR NO REPETIR PREFIJO
	=============================================*/

	public $validarPrefijo;
	public $idCategoriaActual;
	public function ajaxValidarPrefijo(){

		$item = "prefijo";
		$valor = $this->validarPrefijo;

		$respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);

		if ($respuesta && (!$this->idCategoriaActual || $respuesta["id"] != $this->idCategoriaActual)) {
			echo json_encode($respuesta);
		} else {
			echo json_encode(false);
		}
	}
	
}

/*=============================================
VERIFICAR RELACIONES DE LA CATEGORIA ANTES DE ELIMINAR
=============================================*/
if (isset($_POST["idCategoriaVerificarRelaciones"])) {
	$idCategoria = $_POST["idCategoriaVerificarRelaciones"];
	
	$totalProductosGlobales = ModeloCategorias::mdlContarProductosActivosGlobales($idCategoria);
	
	if ($totalProductosGlobales > 0) {
		$productosLocal = ModeloCategorias::mdlContarProductosPorCategoria($idCategoria);
		if ($productosLocal == 0) {
			echo json_encode(["status" => "success", "tieneProductosActivos" => true, "tipo" => "otra_sucursal"]);
		} else {
			echo json_encode(["status" => "success", "tieneProductosActivos" => true, "tipo" => "local"]);
		}
	} else {
		echo json_encode(["status" => "success", "tieneProductosActivos" => false]);
	}
	exit;
}

/*=============================================
ELIMINAR CATEGORIA
=============================================*/
if (isset($_POST["idCategoriaEliminar"])) {
    $eliminar = new ControladorCategorias();
    $respuesta = $eliminar->ctrBorrarCategoria();
    echo $respuesta;
    exit;
}

/*=============================================
OBTENER CATEGORIA PARA MODAL EDITAR
=============================================*/
if (isset($_POST["idCategoria"]) && !isset($_POST["guardarEditarCategoria"])) {
	$categoria = new AjaxCategorias();
	$categoria->idCategoria = $_POST["idCategoria"];
	$categoria->ajaxEditarCategoria();
	exit;
}

/*=============================================
HPM VALIDAR NO REPETIR CATEGORIA
=============================================*/
if(isset($_POST["validarCategoria"])){
	$valCategoria = new AjaxCategorias();
	$valCategoria->validarCategoria = $_POST["validarCategoria"];
	$valCategoria->ajaxValidarCategoria();
	exit;
}

/*=============================================
VALIDAR NO REPETIR PREFIJO
=============================================*/
if(isset($_POST["validarPrefijo"])){
	$valPrefijo = new AjaxCategorias();
	$valPrefijo->validarPrefijo = $_POST["validarPrefijo"];
	$valPrefijo->idCategoriaActual = isset($_POST["idCategoriaActual"]) ? $_POST["idCategoriaActual"] : null;
	$valPrefijo->ajaxValidarPrefijo();
	exit;
}

/*=============================================
GUARDAR CREAR CATEGORIA
=============================================*/
if (isset($_POST["guardarCrearCategoria"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevaCategoria"])) {
		if (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaCategoria"])) {
			echo json_encode(["status" => "error", "mensaje" => "La categoría no puede ir vacía ni llevar caracteres especiales."]);
			exit;
		}

		$tabla = "categorias";
		$prefijo = isset($_POST["nuevoPrefijo"]) && $_POST["nuevoPrefijo"] !== "" ? $_POST["nuevoPrefijo"] : null;

		if ($prefijo !== null) {
			$prefijoExistente = ModeloCategorias::mdlMostrarCategorias($tabla, "prefijo", $prefijo);
			if ($prefijoExistente) {
				echo json_encode(["status" => "error", "mensaje" => "El prefijo ya está siendo usado por otra categoría."]);
				exit;
			}
		}

		$datos = array(
			"categoria" => $_POST["nuevaCategoria"],
			"prefijo" => $prefijo
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloCategorias::mdlIngresarCategoria($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al guardar la categoría.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La categoría ha sido guardada correctamente!"]);
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
GUARDAR EDITAR CATEGORIA
=============================================*/
if (isset($_POST["guardarEditarCategoria"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarCategoria"])) {
		if (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCategoria"])) {
			echo json_encode(["status" => "error", "mensaje" => "La categoría no puede ir vacía ni llevar caracteres especiales."]);
			exit;
		}

		$tabla = "categorias";
		$prefijo = isset($_POST["editarPrefijo"]) && $_POST["editarPrefijo"] !== "" ? $_POST["editarPrefijo"] : null;
		$idCategoria = $_POST["idCategoria"];

		if ($prefijo !== null) {
			$prefijoExistente = ModeloCategorias::mdlMostrarCategorias($tabla, "prefijo", $prefijo);
			if ($prefijoExistente && $prefijoExistente["id"] != $idCategoria) {
				echo json_encode(["status" => "error", "mensaje" => "El prefijo ya está siendo usado por otra categoría."]);
				exit;
			}
		}

		$datos = array(
			"categoria" => $_POST["editarCategoria"],
			"prefijo" => $prefijo,
			"id" => $idCategoria
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloCategorias::mdlEditarCategoria($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al editar la categoría.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La categoría ha sido editada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la categoría es obligatorio."]);
	}
	exit;
}