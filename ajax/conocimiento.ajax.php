<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

// Restricción de seguridad: Solo Administrador y _SystemMaster_
if (($_SESSION["perfil"] ?? '') !== "Administrador" && ($_SESSION["perfil"] ?? '') !== "_SystemMaster_") {
	http_response_code(403);
	die(json_encode(['error' => 'No autorizado', 'success' => false]));
}

require_once "../controladores/conocimiento.controlador.php";
require_once "../modelos/conocimiento.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!CSRF::validateToken()) {
		http_response_code(403);
		die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
	}
}

/*=============================================
TABLA ARTICULOS SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
	require_once "../modelos/sanitizer.php";
	require_once "../modelos/helpers.php";
	$respuesta = ControladorConocimiento::ctrMostrarArticulosServerSide($_POST);
	echo json_encode($respuesta);
	exit;
}

class AjaxConocimiento
{
	/*=============================================
	OBTENER CATEGORIA PARA EDITAR
	=============================================*/
	public $idCategoria;

	public function ajaxEditarCategoria()
	{
		$item = "id";
		$valor = $this->idCategoria;
		$respuesta = ControladorConocimiento::ctrMostrarCategorias($item, $valor);
		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER ARTICULO PARA EDITAR / VER
	=============================================*/
	public $idArticulo;

	public function ajaxEditarArticulo()
	{
		$item = "id";
		$valor = $this->idArticulo;
		$respuesta = ControladorConocimiento::ctrMostrarArticulos($item, $valor);
		echo json_encode($respuesta);
	}

	/*=============================================
	ACTIVAR ARTICULO
	=============================================*/
	public $activarArticulo;
	public $activarId;

	public function ajaxActivarArticulo()
	{
		$tabla = "empresa_conocimiento";
		$item1 = "estado";
		$valor1 = $this->activarArticulo;
		$item2 = "id";
		$valor2 = $this->activarId;

		$respuesta = ModeloConocimiento::mdlActualizarArticulo($tabla, $item1, $valor1, $item2, $valor2);
		echo json_encode($respuesta);
	}

	/*=============================================
	ACTIVAR CATEGORIA
	=============================================*/
	public $activarCategoria;
	public $activarCatId;

	public function ajaxActivarCategoria()
	{
		$tabla = "empresa_conocimiento_categorias";
		$item1 = "estado";
		$valor1 = $this->activarCategoria;
		$item2 = "id";
		$valor2 = $this->activarCatId;

		$respuesta = ModeloConocimiento::mdlActualizarCategoria($tabla, $item1, $valor1, $item2, $valor2);
		echo json_encode($respuesta);
	}
}

/*=============================================
EDITAR CATEGORIA
=============================================*/
if (isset($_POST["idCategoria"])) {
	$valCat = new AjaxConocimiento();
	$valCat->idCategoria = $_POST["idCategoria"];
	$valCat->ajaxEditarCategoria();
}

/*=============================================
EDITAR ARTICULO
=============================================*/
if (isset($_POST["idArticulo"])) {
	$valArt = new AjaxConocimiento();
	$valArt->idArticulo = $_POST["idArticulo"];
	$valArt->ajaxEditarArticulo();
}

/*=============================================
ACTIVAR ARTICULO
=============================================*/
if (isset($_POST["activarArticulo"])) {
	$actArt = new AjaxConocimiento();
	$actArt->activarArticulo = $_POST["activarArticulo"];
	$actArt->activarId = $_POST["activarId"];
	$actArt->ajaxActivarArticulo();
}

/*=============================================
ACTIVAR CATEGORIA
=============================================*/
if (isset($_POST["activarCategoria"])) {
	$actCat = new AjaxConocimiento();
	$actCat->activarCategoria = $_POST["activarCategoria"];
	$actCat->activarCatId = $_POST["activarCatId"];
	$actCat->ajaxActivarCategoria();
}

/*=============================================
ELIMINAR ARTICULO
=============================================*/
if (isset($_POST["idArticuloEliminar"])) {
	$id = $_POST["idArticuloEliminar"];
	$tabla = "empresa_conocimiento";
	$respuesta = ModeloConocimiento::mdlEliminarArticulo($tabla, $id);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
ELIMINAR CATEGORIA
=============================================*/
if (isset($_POST["idCategoriaEliminar"])) {
	$id = $_POST["idCategoriaEliminar"];
	$tablaArticulos = "empresa_conocimiento";
	$articulosAsociados = ModeloConocimiento::mdlMostrarArticulos($tablaArticulos, "id_categoria", $id);

	if ($articulosAsociados) {
		echo json_encode("tiene_articulos");
		exit;
	}

	$tabla = "empresa_conocimiento_categorias";
	$respuesta = ModeloConocimiento::mdlEliminarCategoria($tabla, $id);
	echo json_encode($respuesta);
	exit;
}
