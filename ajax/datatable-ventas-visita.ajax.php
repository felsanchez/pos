<?php

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
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

		// Obtener el término de búsqueda
		$busqueda = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
		$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

		// SI NO HAY BÚSQUEDA, RETORNAR VACÍO
		if (empty($busqueda) || $busqueda === "NINGUNO") {
			echo json_encode(array(
				"draw" => $draw,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => array()
			));
			return;
		}

		// BÚSQUEDA FLEXIBLE - Buscar por código (alfanumérico)
		// Buscar usando LIKE para permitir búsqueda parcial y exacta
		$busquedaLike = "%" . $busqueda . "%";
		$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE codigo LIKE :codigo ORDER BY id DESC LIMIT 1");
		$stmt->bindParam(":codigo", $busquedaLike, PDO::PARAM_STR);
		$stmt->execute();
		$ventas = $stmt->fetch();

		// Si no encontró con LIKE, intentar búsqueda exacta
		if (!$ventas || $ventas === false) {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE codigo = :codigo ORDER BY id DESC");
			$stmt->bindParam(":codigo", $busqueda, PDO::PARAM_STR);
			$stmt->execute();
			$ventas = $stmt->fetch();
		}

		$stmt = null;

		// Validar respuesta
		if ($ventas === false || $ventas === null || (is_array($ventas) && empty($ventas))) {
			echo json_encode(array(
				"draw" => $draw,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => array()
			));
			return;
		}

		// Si es un registro único, convertir a array
		if (is_array($ventas) && isset($ventas["id"])) {
			$ventas = array($ventas);
		}

		$data = array();
		$contador = 1;

		foreach ($ventas as $venta) {
			if (!is_array($venta)) {
				continue;
			}

			// Obtener nombre del cliente
			$nombreCliente = isset($venta["id_cliente"]) ? $venta["id_cliente"] : "";
			if (!empty($nombreCliente)) {
				$cliente = ControladorClientes::ctrMostrarClientes("id", $nombreCliente);
				if (is_array($cliente) && isset($cliente["nombre"])) {
					$nombreCliente = $cliente["nombre"];
				}
			}

			// Obtener nombre del vendedor
			$nombreVendedor = isset($venta["id_vendedor"]) ? $venta["id_vendedor"] : "";
			if (!empty($nombreVendedor)) {
				$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $nombreVendedor);
				if (is_array($vendedor) && isset($vendedor["nombre"])) {
					$nombreVendedor = $vendedor["nombre"];
				}
			}

			// Generar botones de acciones
			$botones = '<div class="btn-group">';

			// Solo mostrar botón "Imprimir Factura" si el estado es "venta"
			if (isset($venta["estado"]) && $venta["estado"] == "venta") {
				$botones .= '<button class="btn btn-info btnImprimirFactura" codigoVenta="' . (isset($venta["codigo"]) ? $venta["codigo"] : "") . '">';
				$botones .= '<i class="fa fa-print"></i>';
				$botones .= '</button>';
			}

            if(puedeAccion('ordenes-visita', 'editar')){
			    $botones .= '<a href="index.php?ruta=editarordenes-visita&idVenta=' . (isset($venta["id"]) ? $venta["id"] : "") . '" class="btn btn-warning">';
			    $botones .= '<i class="fa fa-line-chart"></i>';
			    $botones .= '</a>';
            }
			$botones .= '</div>';

			$data[] = array(
				isset($venta["codigo"]) ? $venta["codigo"] : "",
				$nombreCliente,
				isset($venta["metodo_pago"]) ? $venta["metodo_pago"] : "",
				isset($venta["total"]) ? $venta["total"] : "",
				isset($venta["fecha"]) ? $venta["fecha"] : "",
				$botones
			);
		}

		echo json_encode(array(
			"draw" => $draw,
			"recordsTotal" => count($data),
			"recordsFiltered" => count($data),
			"data" => $data
		));

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