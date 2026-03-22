<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

require_once "../controladores/proveedores.controlador.php";
require_once "../modelos/proveedores.modelo.php";


class TablaProductos
{

	/*=============================================
	 MOSTRAR LA TABLA DE PRODUCTOS
	 =============================================*/

	public function mostrarTabla()
	{

		$item = null;
		$valor = null;
		$orden = "id";

		$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

		if (count($productos) == 0) {

			echo json_encode(array("data" => array()));
			return;
		}


		// Obtener tributos para mapeo rápido
		require_once "../modelos/factus.modelo.php";
		$tributos = ModeloFactus::mdlObtenerTributos();
		$tributosMap = array();
		foreach ($tributos as $t) {
			$tributosMap[$t['id']] = $t; // Mapear por ID ya que en productos.tributo_id se guarda el ID de la tabla factus_tributos
		}

		// Crear array de datos
		$data = array();

		for ($i = 0; $i < count($productos); $i++) {

			/*=============================================
			TRAEMOS LA IMAGEN
			=============================================*/
			$imagen = $productos[$i]["imagen"] ? $productos[$i]["imagen"] : "";

			/*=============================================
			TRAEMOS LA CATEGORÍA
			=============================================*/
			$item = "id";
			$valor = $productos[$i]["id_categoria"];
			$categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
			$nombreCategoria = ($categorias && isset($categorias["categoria"])) ? $categorias["categoria"] : "Sin categoría";

			/*=============================================
			TRAEMOS EL PROVEEDOR
			=============================================*/
			$nombreProveedor = "Sin proveedor";
			if (!empty($productos[$i]["id_proveedor"]) && $productos[$i]["id_proveedor"] != 0) {
				$item = "id";
				$valor = $productos[$i]["id_proveedor"];
				$proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
				if ($proveedores && isset($proveedores["nombre"])) {
					$nombreProveedor = $proveedores["nombre"];
				}
			}

			/*=============================================
			TRAEMOS EL IMPUESTO (FACTUS)
			=============================================*/
			$impuesto = "Sin Tributo";
			$tributoId = isset($productos[$i]["tributo_id"]) ? $productos[$i]["tributo_id"] : null;

			// Buscar en el mapa (el tributo_id guardado en productos corresponde al 'codigo' en factus_tributos)
			if ($tributoId && isset($tributosMap[$tributoId])) {
				$tObj = $tributosMap[$tributoId];
				$impuesto = $tObj["nombre"]; // Ej: IVA
				if (isset($tObj["porcentaje"])) {
					$impuesto .= " - " . $tObj["porcentaje"] . "%";
				}
			}


			/*=============================================
			STOCK
			=============================================*/
			if ($productos[$i]["stock"] <= 10) {
				$stock = "<button class='btn btn-danger'>" . $productos[$i]["stock"] . "</button>";
			} else if ($productos[$i]["stock"] >= 11 && $productos[$i]["stock"] <= 15) {
				$stock = "<button class='btn btn-warning'>" . $productos[$i]["stock"] . "</button>";
			} else {
				$stock = "<button class='btn btn-success'>" . $productos[$i]["stock"] . "</button>";
			}

			/*=============================================
			ACCIONES
			=============================================*/
			$botonesAcciones = '<div class="btn-group">';
			$botonesAcciones .= '<button class="btn btn-warning btnEditarProducto" idProducto="' . $productos[$i]["id"] . '"><i class="fa fa-pencil"></i></button>';
			$botonesAcciones .= '<button class="btn btn-danger btnEliminarProducto" idProducto="' . $productos[$i]["id"] . '" codigo="' . $productos[$i]["codigo"] . '" imagen="' . $productos[$i]["imagen"] . '"><i class="fa fa-times"></i></button>';
			if ($productos[$i]["tiene_variantes"] == 1) {
				$botonesAcciones .= '<button class="btn btn-info btnExpandirVariantes" data-id-producto="' . $productos[$i]["id"] . '" title="Ver variantes"><i class="fa fa-plus"></i></button>';
			}
			$botonesAcciones .= '</div>';

			$descripcionBotones = $productos[$i]["descripcion"];

			$data[] = array(
				$productos[$i]["id"],
				$imagen,
				$productos[$i]["codigo"],
				$descripcionBotones,
				$nombreCategoria,
				$stock,
				$impuesto, // REEMPLAZADO: Antes precio_compra
				"$ " . number_format($productos[$i]["precio_venta"]),
				$nombreProveedor,
				$productos[$i]["fecha"],
				$botonesAcciones
			);
		}

		// Usar json_encode para generar JSON válido
		echo json_encode(array("data" => $data));
	}

}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS
=============================================*/

$activar = new TablaProductos();
$activar->mostrarTabla();

