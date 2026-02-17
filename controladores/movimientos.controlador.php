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

			// Crear archivo Excel
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="historial_stock_'.date('Y-m-d_H-i-s').'.xls"');
			header('Cache-Control: max-age=0');

			echo '<html>';
			echo '<meta charset="UTF-8">';
			echo '<body>';
			echo '<table border="1">';
			echo '<tr>';
			echo '<th>ID</th>';
			echo '<th>Fecha</th>';
			echo '<th>Producto</th>';
			echo '<th>Tipo Movimiento</th>';
			echo '<th>Cantidad</th>';
			echo '<th>Stock Anterior</th>';
			echo '<th>Stock Nuevo</th>';
			echo '<th>Usuario</th>';
			echo '<th>Referencia</th>';
			echo '<th>Notas</th>';
			echo '</tr>';

			foreach ($movimientos as $row) {
				echo '<tr>';
				echo '<td>'.$row["id"].'</td>';
				echo '<td>'.$row["fecha"].'</td>';
				echo '<td>'.$row["nombre_producto"].'</td>';
				echo '<td>'.$row["tipo_movimiento"].'</td>';
				echo '<td>'.$row["cantidad"].'</td>';
				echo '<td>'.$row["stock_anterior"].'</td>';
				echo '<td>'.$row["stock_nuevo"].'</td>';
				echo '<td>'.$row["nombre_usuario"].'</td>';
				echo '<td>'.$row["referencia"].'</td>';
				echo '<td>'.$row["notas"].'</td>';
				echo '</tr>';
			}

			echo '</table>';
			echo '</body>';
			echo '</html>';

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

		echo utf8_decode("<table border='1'>
			<tr>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>FECHA</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>PRODUCTO</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>TIPO</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>TIPO MOVIMIENTO</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>CANTIDAD</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>STOCK ANTERIOR</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>STOCK NUEVO</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>USUARIO</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>REFERENCIA</td>
			<td style='font-weight:bold; background-color:#3c8dbc; color:white;'>NOTAS</td>
			</tr>");

		foreach ($movimientos as $row => $item) {

			// Formatear tipo de producto
			$tipoProducto = ($item["tipo_producto"] == "producto") ? "Producto" : "Variante";

			// Formatear tipo de movimiento
			$tipoMovimiento = str_replace("_", " ", ucwords($item["tipo_movimiento"], "_"));

			echo "<tr>";
			echo "<td>" . $item["fecha"] . "</td>";
			echo "<td>" . $item["nombre_producto"] . "</td>";
			echo "<td>" . $tipoProducto . "</td>";
			echo "<td>" . $tipoMovimiento . "</td>";
			echo "<td>" . $item["cantidad"] . "</td>";
			echo "<td>" . $item["stock_anterior"] . "</td>";
			echo "<td>" . $item["stock_nuevo"] . "</td>";
			echo "<td>" . $item["nombre_usuario"] . "</td>";
			echo "<td>" . $item["referencia"] . "</td>";
			echo "<td>" . $item["notas"] . "</td>";
			echo "</tr>";
		}

		echo "</table>";
		exit;
	}

}