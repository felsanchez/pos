<?php

class ControladorMovimientos{

	/*=============================================
	REGISTRAR MOVIMIENTO DE STOCK
	=============================================*/
	static public function ctrRegistrarMovimiento($tipo, $idProducto, $idVariante, $nombreProducto, $tipoMovimiento, $cantidad, $stockAnterior, $stockNuevo, $referencia, $notas = "", $idBodega = null){

		// Si no se pasa idBodega, intentar obtenerlo de la sesión
		if($idBodega == null){
			$idBodega = (!empty($_SESSION["id_bodega"])) ? $_SESSION["id_bodega"] : 1;
		}

		// Obtener usuario actual de la sesión
		$idUsuario = isset($_SESSION["id"]) ? $_SESSION["id"] : null;
		$nombreUsuario = isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : "Sistema"; 

		$datos = array(
			"tipo_producto" => $tipo,
			"id_producto" => $idProducto,
			"id_variante" => $idVariante,
			"id_bodega" => $idBodega,
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
	static public function ctrObtenerResumen($idBodegaInicial = null){

		$filtros = array();

		if(isset($_POST["fecha_desde"]) && !empty($_POST["fecha_desde"])){
			$filtros["fecha_desde"] = $_POST["fecha_desde"];
		}

		if(isset($_POST["fecha_hasta"]) && !empty($_POST["fecha_hasta"])){
			$filtros["fecha_hasta"] = $_POST["fecha_hasta"];
		}

		// Soporte para filtrado por bodega
		if(isset($_POST["id_bodega"]) && !empty($_POST["id_bodega"]) && $_POST["id_bodega"] !== 'todos'){
			$filtros["id_bodega"] = $_POST["id_bodega"];
		} else if ($idBodegaInicial !== null && $idBodegaInicial !== 'todos') {
			$filtros["id_bodega"] = $idBodegaInicial;
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
		$filtros = array();

		// Verificar si hay filtro de fechas
		if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])) {
			$filtros["fecha_desde"] = $_GET["fechaInicial"];
			$filtros["fecha_hasta"] = $_GET["fechaFinal"];
		}

		// Obtener los movimientos
		$movimientos = ModeloMovimientos::mdlMostrarMovimientos($filtros);

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

		$html = "<table border='0'>
			<tr>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>PRODUCTO</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>SUCURSAL</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>TIPO MOVIMIENTO</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>TIPO PRODUCTO</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>FECHA</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>CANTIDAD</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>STOCK ANTERIOR</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>STOCK NUEVO</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>USUARIO</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>REFERENCIA</td>
				<td style='font-weight:bold; border:1px solid #eee; background-color: #f2f2f2;'>NOTAS</td>
			</tr>";

		foreach ($movimientos as $row => $item) {
			$sucursal = !empty($item["nombre_bodega"]) ? $item["nombre_bodega"] : 'Principal';
			$tipoProducto = ($item["tipo_producto"] == "producto") ? 'Producto' : 'Variante';
			
			// Formatear tipo de movimiento para que sea amigable
			$tipoMov = $item["tipo_movimiento"];
			$badges = [
				"venta" => "Venta",
				"devolucion" => "Devolución",
				"eliminacion_venta" => "Eliminación Venta",
				"eliminacion_producto" => "Eliminación Producto",
				"eliminacion_variante" => "Eliminación Variante",
				"ajuste_manual" => "Ajuste Manual",
				"creacion_producto" => "Creación Producto",
				"creacion_variante" => "Creación Variante",
				"edicion_stock" => "Edición Stock",
				"traslado_salida" => "Traslado (Salida)",
				"traslado_entrada" => "Traslado (Entrada)"
			];
			if (isset($badges[$tipoMov])) {
				$tipoMovFriendly = $badges[$tipoMov];
			} else {
				$tipoMovFriendly = $tipoMov;
			}

			$html .= "<tr>
				<td style='border:1px solid #eee;'>" . ($item["nombre_producto"] ?? '') . "</td>
				<td style='border:1px solid #eee;'>" . $sucursal . "</td>
				<td style='border:1px solid #eee;'>" . $tipoMovFriendly . "</td>
				<td style='border:1px solid #eee;'>" . $tipoProducto . "</td>
				<td style='border:1px solid #eee;'>" . ($item["fecha"] ?? '') . "</td>
				<td style='border:1px solid #eee;'>" . intval($item["cantidad"] ?? 0) . "</td>
				<td style='border:1px solid #eee;'>" . intval($item["stock_anterior"] ?? 0) . "</td>
				<td style='border:1px solid #eee;'>" . intval($item["stock_nuevo"] ?? 0) . "</td>
				<td style='border:1px solid #eee;'>" . ($item["nombre_usuario"] ?? '') . "</td>
				<td style='border:1px solid #eee;'>" . ($item["referencia"] ?? '') . "</td>
				<td style='border:1px solid #eee;'>" . ($item["notas"] ?? '') . "</td>
			</tr>";
		}

		$html .= "</table>";

		if (function_exists('mb_convert_encoding')) {
			echo mb_convert_encoding($html, 'ISO-8859-1', 'UTF-8');
		} else {
			echo utf8_decode($html);
		}

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

		// Filtro por Bodega
		if (!empty($params["id_bodega"]) && $params["id_bodega"] !== 'todos') {
			$where .= " AND id_bodega = " . intval($params["id_bodega"]);
		}

		// Filtros personalizados
		if (!empty($params["id_producto"])) {
			$where .= " AND id_producto = " . intval($params["id_producto"]);
		}

		if (!empty($params["tipo_movimiento"])) {
			
			$tipoBusqueda = $params["tipo_movimiento"];

			if ($tipoBusqueda == "traslado_salida") {
				// Buscar por tipo exacto o por palabra clave en referencia + cantidad negativa
				$where .= " AND (tipo_movimiento = 'traslado_salida' OR (referencia LIKE '%Traslado%' AND cantidad < 0))";
			} else if ($tipoBusqueda == "traslado_entrada") {
				// Buscar por tipo exacto o por palabra clave en referencia + cantidad positiva
				$where .= " AND (tipo_movimiento = 'traslado_entrada' OR (referencia LIKE '%Traslado%' AND cantidad > 0))";
			} else {
				$where .= " AND tipo_movimiento = '" . $tipoBusqueda . "'";
			}

		}

		if (!empty($params["fecha_desde"])) {
			$where .= " AND DATE(m.`fecha`) >= '" . $params["fecha_desde"] . "'";
		}

		if (!empty($params["fecha_hasta"])) {
			$where .= " AND DATE(m.`fecha`) <= '" . $params["fecha_hasta"] . "'";
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
		
		$whereTotal = " WHERE (referencia NOT LIKE '%(por variante%' OR referencia IS NULL) ";
		if (!empty($params["id_bodega"]) && $params["id_bodega"] !== 'todos') {
			$whereTotal .= " AND id_bodega = " . intval($params["id_bodega"]);
		}
		
		$totalData = ModeloMovimientos::mdlGetTotalMovimientos($tabla, $whereTotal);
		$totalFiltered = ModeloMovimientos::mdlGetTotalMovimientos($tabla, $where);

		$data = array();

		foreach ($movimientos as $key => $value) {
			
			$nestedData = array();

			// 0: Producto
			$nombreProducto = e($value["nombre_producto"]);
			$esVistaGlobal = !isset($params["id_bodega"]) || $params["id_bodega"] === "" || $params["id_bodega"] === "todos";
			if ($esVistaGlobal && !empty($value["nombre_bodega"])) {
				$idBodega = intval($value["id_bodega"]);
				$coloresBodega = [
					1 => '#6f42c1', // Morado elegante (predeterminado)
					2 => '#0f766e', // Teal / Verde azulado oscuro
					3 => '#1e3a8a', // Azul marino profundo
					4 => '#c2410c', // Terracota / Naranja quemado
					5 => '#15803d', // Verde bosque
					6 => '#be185d', // Fucsia / Rosa oscuro
					7 => '#4f46e5', // Índigo vibrante
					8 => '#475569'  // Pizarra
				];
				
				$colorFondo = isset($coloresBodega[$idBodega]) 
					? $coloresBodega[$idBodega] 
					: "hsl(" . (($idBodega * 137) % 360) . ", 65%, 40%)";

				$nombreProducto .= ' <span class="label" style="background-color: ' . $colorFondo . '; color: white; margin-left: 5px; font-weight: 500; font-size: 10px; padding: 2px 6px; border-radius: 4px;">' . e($value["nombre_bodega"]) . '</span>';
			}
			$nestedData[] = $nombreProducto;

			// 1: Tipo Movimiento (Badge)
			$badges = [
				"venta" => '<span class="label label-success">Venta</span>',
				"devolucion" => '<span class="label label-warning">Devolución</span>',
				"eliminacion_venta" => '<span class="label label-danger">Eliminación Venta</span>',
				"eliminacion_producto" => '<span class="label label-danger">Eliminación</span>',
				"eliminacion_variante" => '<span class="label label-danger">Eliminación</span>',
				"ajuste_manual" => '<span class="label label-default">Ajuste Manual</span>',
				"creacion_producto" => '<span class="label label-primary">Creación</span>',
				"creacion_variante" => '<span class="label label-info">Creación Variante</span>',
				"edicion_stock" => '<span class="label label-default">Edición Stock</span>',
				"traslado_salida" => '<span class="label label-warning" style="background-color: #ff851b !important;">Traslado (Salida)</span>',
				"traslado_entrada" => '<span class="label label-info" style="background-color: #39cccc !important;">Traslado (Entrada)</span>'
			];
			// Lógica ultra-robusta para traslados y otros tipos
			$tipoActual = trim($value["tipo_movimiento"]);
			$referencia = trim($value["referencia"]);
			
			if (isset($badges[$tipoActual]) && $tipoActual != "") {
				
				$nestedData[] = $badges[$tipoActual];

			} else if (stripos($tipoActual, 'traslado') !== false || stripos($referencia, 'traslado') !== false) {
				
				// DETECCIÓN POR NOMBRE O POR REFERENCIA (Si el tipo falló, la referencia nos salva)
				if (stripos($tipoActual, 'salida') !== false || stripos($referencia, 'salida') !== false || intval($value["cantidad"]) < 0) {
					$nestedData[] = '<span class="label label-warning" style="background-color: #ff851b !important;">Traslado (Salida)</span>';
				} else {
					$nestedData[] = '<span class="label label-info" style="background-color: #39cccc !important;">Traslado (Entrada)</span>';
				}

			} else {

				// Fallback: Si el tipo está vacío, mostrar algo útil
				$nestedData[] = ($tipoActual != "") ? e($tipoActual) : '<span class="label label-default">Movimiento</span>';
				
			}

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
			// Si es un traslado, mostramos la dirección del movimiento con prefijos De/Para para máxima claridad
			if (stripos($tipoActual, 'traslado') !== false || stripos($referencia, 'traslado') !== false) {
				
				$prefijo = (stripos($tipoActual, 'salida') !== false || stripos($referencia, 'salida') !== false || intval($value["cantidad"]) < 0) ? "De: " : "Para: ";
				$nestedData[] = $prefijo . e($value["nombre_bodega"]);

			} else {

				$nestedData[] = e($value["referencia"]);

			}

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