<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
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