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
EDITAR CATEGORIA
=============================================*/
if(isset($_POST["idCategoria"])){

	$categoria = new AjaxCategorias();
	$categoria -> idCategoria = $_POST["idCategoria"];
	$categoria -> ajaxEditarCategoria();
}


/*=============================================
HPM VALIDAR NO REPETIR CATEGORIA
=============================================*/

if(isset($_POST["validarCategoria"])){

	$valCategoria = new AjaxCategorias();
	$valCategoria -> validarCategoria = $_POST["validarCategoria"];
	$valCategoria -> ajaxValidarCategoria();
}

/*=============================================
VALIDAR NO REPETIR PREFIJO
=============================================*/
if(isset($_POST["validarPrefijo"])){

	$valPrefijo = new AjaxCategorias();
	$valPrefijo -> validarPrefijo = $_POST["validarPrefijo"];
	$valPrefijo -> idCategoriaActual = isset($_POST["idCategoriaActual"]) ? $_POST["idCategoriaActual"] : null;
	$valPrefijo -> ajaxValidarPrefijo();
}