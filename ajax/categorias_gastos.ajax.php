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
EDITAR CATEGORÍA DE GASTO
=============================================*/
if(isset($_POST["idCategoria"])){

	$categoria = new AjaxCategoriasGastos();
	$categoria -> idCategoria = $_POST["idCategoria"];
	$categoria -> ajaxEditarCategoriaGasto();
}

/*=============================================
OBTENER TODAS LAS CATEGORÍAS
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerCategorias"){

	$categorias = new AjaxCategoriasGastos();
	$categorias -> ajaxObtenerCategoriasGastos();
}