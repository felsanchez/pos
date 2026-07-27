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
	exit;
}

/*=============================================
OBTENER ARTICULO PARA MOSTRAR EN MODAL
=============================================*/
if (isset($_POST["idArticulo"]) && !isset($_POST["guardarEditarArticulo"])) {
	$valArt = new AjaxConocimiento();
	$valArt->idArticulo = $_POST["idArticulo"];
	$valArt->ajaxEditarArticulo();
	exit;
}

/*=============================================
ACTIVAR ARTICULO
=============================================*/
if (isset($_POST["activarArticulo"])) {
	$actArt = new AjaxConocimiento();
	$actArt->activarArticulo = $_POST["activarArticulo"];
	$actArt->activarId = $_POST["activarId"];
	$actArt->ajaxActivarArticulo();
	exit;
}

/*=============================================
ACTIVAR CATEGORIA
=============================================*/
if (isset($_POST["activarCategoria"])) {
	$actCat = new AjaxConocimiento();
	$actCat->activarCategoria = $_POST["activarCategoria"];
	$actCat->activarCatId = $_POST["activarCatId"];
	$actCat->ajaxActivarCategoria();
	exit;
}

/*=============================================
ELIMINAR ARTICULO
=============================================*/
if (isset($_POST["idArticuloEliminar"])) {

    $id = $_POST["idArticuloEliminar"];

    $tabla = "empresa_conocimiento";

    $respuesta = ModeloConocimiento::mdlEliminarArticulo($tabla, $id);

    // Si se eliminó correctamente, sincronizar con Qdrant
    if ($respuesta == "ok") {

        ControladorConocimiento::sincronizarQdrant("eliminar", $id);

    }

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

/*=============================================
GUARDAR EDITAR ARTICULO
=============================================*/
if (isset($_POST["guardarEditarArticulo"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarArticuloTitulo"])) {
		$tabla = "empresa_conocimiento";
		$datos = array(
			"id"              => $_POST["idArticulo"],
			"id_categoria"    => $_POST["editarArticuloCategoria"],
			"titulo"          => $_POST["editarArticuloTitulo"],
			"contenido"       => $_POST["editarArticuloContenido"],
			"palabras_clave"  => !empty($_POST["editarArticuloKeywords"]) ? $_POST["editarArticuloKeywords"] : null
		);

		$respuesta = ModeloConocimiento::mdlEditarArticulo($tabla, $datos);

		if ($respuesta == "ok") {
			ControladorConocimiento::sincronizarQdrant("actualizar", $_POST["idArticulo"]);
			echo json_encode(["status" => "ok", "mensaje" => "¡El artículo ha sido editado correctamente!"]);
		} else {
			echo json_encode(["status" => "error", "mensaje" => "No se pudo actualizar el artículo."]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El título no puede ir vacío."]);
	}
	exit;
}

/*=============================================
GUARDAR CREAR ARTICULO
=============================================*/
if (isset($_POST["guardarCrearArticulo"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoArticuloTitulo"])) {
		$tabla = "empresa_conocimiento";
		$datos = array(
			"id_categoria"   => $_POST["nuevoArticuloCategoria"],
			"titulo"         => $_POST["nuevoArticuloTitulo"],
			"contenido"      => $_POST["nuevoArticuloContenido"],
			"palabras_clave" => !empty($_POST["nuevoArticuloKeywords"]) ? $_POST["nuevoArticuloKeywords"] : null
		);

		$respuesta = ModeloConocimiento::mdlIngresarArticulo($tabla, $datos);

		if (isset($respuesta["ok"]) && $respuesta["ok"]) {
			$idArticulo = $respuesta["id"];
			ControladorConocimiento::sincronizarQdrant("crear", $idArticulo);
			echo json_encode(["status" => "ok", "mensaje" => "¡El artículo ha sido guardado correctamente!"]);
		} else {
			echo json_encode(["status" => "error", "mensaje" => "No se pudo guardar el artículo."]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El título no puede ir vacío."]);
	}
	exit;
}
