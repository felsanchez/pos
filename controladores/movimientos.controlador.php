<?php

class ControladorMovimientos{

	/*=============================================
	REGISTRAR MOVIMIENTO DE STOCK
	=============================================*/
	static public function ctrRegistrarMovimiento($tipo, $idProducto, $idVariante, $nombreProducto, $tipoMovimiento, $cantidad, $stockAnterior, $stockNuevo, $referencia, $notas = ""){

		// DEBUG: Log de que se está llamando la función

		file_put_contents("debug_movimientos.txt", "=== REGISTRAR MOVIMIENTO ===\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Fecha: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Tipo: $tipo\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Producto ID: $idProducto\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Nombre: $nombreProducto\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Tipo Movimiento: $tipoMovimiento\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Cantidad: $cantidad\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Stock Anterior: $stockAnterior\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Stock Nuevo: $stockNuevo\n", FILE_APPEND);

		file_put_contents("debug_movimientos.txt", "Referencia: $referencia\n", FILE_APPEND);

 
		// Obtener usuario actual de la sesión
		$idUsuario = isset($_SESSION["id"]) ? $_SESSION["id"] : null;
		$nombreUsuario = isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : "Sistema"; 

		file_put_contents("debug_movimientos.txt", "Usuario: $nombreUsuario (ID: $idUsuario)\n", FILE_APPEND); 

		$datos = array(
			"tipo_producto" => $tipo,
			"id_producto" => $idProducto,
			"id_variante" => $idVariante,
			"nombre_producto" => $nombreProducto,
			"tipo_movimiento" => $tipoMovimiento,
			"cantidad" => $cantidad,
			"stock_anterior" => $stockAnterior,
			"stock_nuevo" => $stockNuevo,
			"id_usuario" => $idUsuario,
			"nombre_usuario" => $nombreUsuario,
			"referencia" => $referencia,
			"notas" => $notas
		); 

		$respuesta = ModeloMovimientos::mdlRegistrarMovimiento($datos); 

		file_put_contents("debug_movimientos.txt", "Respuesta: $respuesta\n\n", FILE_APPEND);
		return $respuesta;
	}

	/*=============================================
	MOSTRAR MOVIMIENTOS CON FILTROS
	=============================================*/
	static public function ctrMostrarMovimientos(){

		// Limpieza automática de registros más antiguos de 3 meses
		ModeloMovimientos::mdlLimpiarHistorialAntiguo("movimientos_stock");

		$filtros = array();

		if(isset($_POST["id_producto"]) && !empty($_POST["id_producto"])){
			$filtros["id_producto"] = $_POST["id_producto"];
		}

		if(isset($_POST["tipo_movimiento"]) && !empty($_POST["tipo_movimiento"])){
			$filtros["tipo_movimiento"] = $_POST["tipo_movimiento"];
		}

		if(isset($_POST["fecha_desde"]) && !empty($_POST["fecha_desde"])){
			$filtros["fecha_desde"] = $_POST["fecha_desde"];
		}

		if(isset($_POST["fecha_hasta"]) && !empty($_POST["fecha_hasta"])){
			$filtros["fecha_hasta"] = $_POST["fecha_hasta"];
		}

		if(isset($_POST["usuario"]) && !empty($_POST["usuario"])){
			$filtros["usuario"] = $_POST["usuario"];
		}

		$respuesta = ModeloMovimientos::mdlMostrarMovimientos($filtros);

		return $respuesta;
	}

	/*=============================================
	OBTENER RESUMEN
	=============================================*/
	static public function ctrObtenerResumen(){

		$filtros = array();

		if(isset($_POST["fecha_desde"]) && !empty($_POST["fecha_desde"])){
			$filtros["fecha_desde"] = $_POST["fecha_desde"];
		}

		if(isset($_POST["fecha_hasta"]) && !empty($_POST["fecha_hasta"])){
			$filtros["fecha_hasta"] = $_POST["fecha_hasta"];
		}

		$respuesta = ModeloMovimientos::mdlObtenerResumen($filtros);

		return $respuesta;
	}

	/*=============================================
	EXPORTAR A EXCEL
	=============================================*/
	static public function ctrExportarExcel(){

		if(isset($_GET["exportarMovimientos"])){

			// Obtener filtros
			$filtros = array();

			if(isset($_GET["producto"]) && !empty($_GET["producto"])){
				$filtros["id_producto"] = $_GET["producto"];
			}

			if(isset($_GET["tipo"]) && !empty($_GET["tipo"])){
				$filtros["tipo_movimiento"] = $_GET["tipo"];
			}

			if(isset($_GET["desde"]) && !empty($_GET["desde"])){
				$filtros["fecha_desde"] = $_GET["desde"];
			}

			if(isset($_GET["hasta"]) && !empty($_GET["hasta"])){
				$filtros["fecha_hasta"] = $_GET["hasta"];
			}

			if(isset($_GET["usuario"]) && !empty($_GET["usuario"])){
				$filtros["usuario"] = $_GET["usuario"];
			}

			$movimientos = ModeloMovimientos::mdlMostrarMovimientos($filtros);

			exit;
		}
	}

	/*=============================================
	DESCARGAR HISTORIAL DE STOCK EN EXCEL
	=============================================*/
	public function ctrDescargarHistorialStock()
	{
		$tabla = "movimientos_stock";

		// Verificar si hay filtro de fechas
		if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])) {
			$movimientos = ModeloMovimientos::mdlObtenerMovimientosPorFecha($tabla, $_GET["fechaInicial"], $_GET["fechaFinal"]);
		} else {
			// Obtener todos los movimientos
			$movimientos = ModeloMovimientos::mdlMostrarMovimientos($tabla, null, null);
		}

		/*=============================================
		CREAMOS EL ARCHIVO DE EXCEL
		=============================================*/
		$fechaDescarga = date('Y-m-d_H-i-s');
		$Name = 'historial_stock_' . $fechaDescarga . '.xls';

		header('Expires: 0');
		header('Cache-control: private');
		header("Content-type: application/vnd.ms-excel");
		header("Cache-Control: cache, must-revalidate");
		header('Content-Description: File Transfer');
		header('Last-Modified: ' . date('D, d M Y H:i:s'));
		header("Pragma: public");
		header('Content-Disposition:; filename="' . $Name . '"');
		header("Content-Transfer-Encoding: binary");

		exit;
	}

	/*=============================================
	MOSTRAR MOVIMIENTOS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarMovimientosServerSide($params)
	{
		$tabla = "movimientos_stock";

		// Columnas para ordenar (Sincronizado con JS: 0=btn, 1=prod, 2=mov, 3=tipo, 4=fecha...)
		$columns = array(
			0 => 'id', 
			1 => 'nombre_producto',
			2 => 'tipo_movimiento',
			3 => 'tipo_producto',
			4 => 'fecha',
			5 => 'cantidad',
			6 => 'stock_anterior',
			7 => 'stock_nuevo',
			8 => 'nombre_usuario',
			9 => 'referencia',
			10 => 'notas'
		);

		$where = " WHERE (referencia NOT LIKE '%(por variante%' OR referencia IS NULL) ";

		// Filtros personalizados
		if (!empty($params["id_producto"])) {
			$where .= " AND id_producto = " . intval($params["id_producto"]);
		}

		if (!empty($params["tipo_movimiento"])) {
			$where .= " AND tipo_movimiento = '" . $params["tipo_movimiento"] . "'";
		}

		if (!empty($params["fecha_desde"])) {
			$where .= " AND DATE(`fecha`) >= '" . $params["fecha_desde"] . "'";
		}

		if (!empty($params["fecha_hasta"])) {
			$where .= " AND DATE(`fecha`) <= '" . $params["fecha_hasta"] . "'";
		}

		if (!empty($params["usuario"])) {
			$where .= " AND id_usuario = " . intval($params["usuario"]);
		}

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (nombre_producto LIKE '%$searchValue%' OR tipo_movimiento LIKE '%$searchValue%' OR nombre_usuario LIKE '%$searchValue%' OR referencia LIKE '%$searchValue%' OR notas LIKE '%$searchValue%') ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$order = " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'] . ", id DESC";
		} else {
			$order = " ORDER BY fecha DESC, id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$movimientos = ModeloMovimientos::mdlMostrarMovimientosServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloMovimientos::mdlGetTotalMovimientos($tabla, " WHERE (referencia NOT LIKE '%(por variante%' OR referencia IS NULL) ");
		$totalFiltered = ModeloMovimientos::mdlGetTotalMovimientos($tabla, $where);

		$data = array();

		foreach ($movimientos as $key => $value) {
			
			$nestedData = array();

			// 0: Producto
			$nestedData[] = e($value["nombre_producto"]);

			// 1: Tipo Movimiento (Badge)
			$badges = [
				"venta" => '<span class="label label-success">Venta</span>',
				"devolucion" => '<span class="label label-warning">Devolución</span>',
				"eliminacion_venta" => '<span class="label label-danger">Eliminación Venta</span>',
				"ajuste_manual" => '<span class="label label-default">Ajuste Manual</span>',
				"creacion_producto" => '<span class="label label-primary">Creación</span>',
				"creacion_variante" => '<span class="label label-info">Creación Variante</span>',
				"edicion_stock" => '<span class="label label-default">Edición Stock</span>'
			];
			$nestedData[] = isset($badges[$value["tipo_movimiento"]]) ? $badges[$value["tipo_movimiento"]] : $value["tipo_movimiento"];

			// 2: Tipo Producto
			$nestedData[] = ($value["tipo_producto"] == "producto") ? '<span class="label label-primary">Producto</span>' : '<span class="label label-info">Variante</span>';

			// 3: Fecha
			$nestedData[] = $value["fecha"]; // El JS se encargará del formato display

			// 4: Cantidad
			$cantidad = intval($value["cantidad"]);
			$nestedData[] = ($cantidad > 0) ? '<span class="text-green"><i class="fa fa-arrow-up"></i> +' . $cantidad . '</span>' : '<span class="text-red"><i class="fa fa-arrow-down"></i> ' . $cantidad . '</span>';

			// 5: Stock Anterior
			$nestedData[] = $value["stock_anterior"];

			// 6: Stock Nuevo
			$cambio = intval($value["stock_nuevo"]) - intval($value["stock_anterior"]);
			$stockNuevoHtml = $value["stock_nuevo"];
			if ($cambio > 0) $stockNuevoHtml = '<strong class="text-green">' . $value["stock_nuevo"] . '</strong>';
			if ($cambio < 0) $stockNuevoHtml = '<strong class="text-red">' . $value["stock_nuevo"] . '</strong>';
			$nestedData[] = $stockNuevoHtml;

			// 7: Usuario
			$nestedData[] = e($value["nombre_usuario"]);

			// 8: Referencia
			$nestedData[] = e($value["referencia"]);

			// 9: Notas
			$nestedData[] = e($value["notas"]);
			
			// Meta-dato para el JS (ID para contenteditable)
			$nestedData["DT_RowId"] = "row_" . $value["id"];
			$nestedData["id"] = $value["id"];

			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		return $json_data;
	}

}