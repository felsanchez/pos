<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../controladores/configuracion.controlador.php";
require_once "../modelos/configuracion.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        if (ob_get_length()) ob_clean();
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";
require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

class tablaVentas
{

	/*=============================================
	MOSTRAR LA TABLA DE VENTAS - SOLO CON BÚSQUEDA
	=============================================*/
	public function mostrarTabla()
	{
		$params = $_POST;
		$respuesta = ControladorVentas::ctrMostrarConsultaVentasServerSide($params);
		if (ob_get_length()) ob_clean();
		header('Content-Type: application/json');
		echo json_encode($respuesta);
	}


	/*=============================================
	EDITAR IMAGEN DE VENTA
	=============================================*/
	public $idVentaImagen;
	public $nuevaImagenVenta;

	public function ajaxEditarImagenVenta()
	{

		if (isset($_FILES["nuevaImagenVenta"]["tmp_name"]) && !empty($_FILES["nuevaImagenVenta"]["tmp_name"])) {

			list($ancho, $alto) = getimagesize($_FILES["nuevaImagenVenta"]["tmp_name"]);

			$nuevoAncho = 500;
			$nuevoAlto = 500;

			/*=============================================
			CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA IMAGEN
			=============================================*/
			$directorio = "../vistas/img/ventas/" . $this->idVentaImagen;

			if (!file_exists($directorio)) {
				mkdir($directorio, 0755);
			}

			/*=============================================
			DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
			=============================================*/
			if ($_FILES["nuevaImagenVenta"]["type"] == "image/jpeg") {

				$aleatorio = mt_rand(100, 999);
				$ruta = $directorio . "/" . $aleatorio . ".jpg";
				$origen = imagecreatefromjpeg($_FILES["nuevaImagenVenta"]["tmp_name"]);
				$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

				imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
				imagejpeg($destino, $ruta);

			}

			if ($_FILES["nuevaImagenVenta"]["type"] == "image/png") {

				$aleatorio = mt_rand(100, 999);
				$ruta = $directorio . "/" . $aleatorio . ".png";
				$origen = imagecreatefrompng($_FILES["nuevaImagenVenta"]["tmp_name"]);
				$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

				imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
				imagepng($destino, $ruta);

			}

		} else {
			$ruta = "";
		}

		$datos = array(
			"id" => $this->idVentaImagen,
			"imagen" => $ruta
		);

		$respuesta = ControladorVentas::ctrEditarImagenVenta($datos);

		echo json_encode($respuesta);

	}


}


/*=============================================
EDITAR IMAGEN DE VENTA
=============================================*/
if (isset($_POST["idVentaImagen"])) {
	$editarImagen = new TablaVentas();
	$editarImagen->idVentaImagen = $_POST["idVentaImagen"];
	$editarImagen->nuevaImagenVenta = $_FILES["nuevaImagenVenta"];
	$editarImagen->ajaxEditarImagenVenta();
}
//Guardar Notas
else if (isset($_POST["idVentaNota"])) {
	$datos = [
		"id" => $_POST["idVentaNota"],
		"notas" => $_POST["nuevaNota"]
	];

	$respuesta = ControladorVentas::ctrActualizarNotaVenta($datos);
	echo json_encode($respuesta);
}
/*=============================================
	ACTIVAR TABLA DE VENTAS
=============================================*/ else {
	$activar = new TablaVentas();
	$activar->mostrarTabla();
}