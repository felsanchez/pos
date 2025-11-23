
<?php

require_once "conexion.php";

class ModeloVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/

	static public function mdlMostrarVentas($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE VENTA
	=============================================*/

	static public function mdlIngresarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_cliente, id_vendedor, productos, impuesto, neto, total, metodo_pago, notas, estado, imagen, fecha, tipo_descuento, valor_descuento, monto_descuento, recibe, extra) VALUES (:codigo, :id_cliente, :id_vendedor, :productos, :impuesto, :neto, :total, :metodo_pago, :notas, :estado, :imagen, :fecha, :tipo_descuento, :valor_descuento, :monto_descuento, :recibe, :extra)");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_descuento", $datos["tipo_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":valor_descuento", $datos["valor_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":monto_descuento", $datos["monto_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":recibe", $datos["recibe"], PDO::PARAM_STR);
		$stmt->bindParam(":extra", $datos["extra"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt = null;

	}

	/*=============================================
	EDITAR VENTAS
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET codigo = :codigo, id_cliente = :id_cliente, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total = :total, metodo_pago = :metodo_pago, notas = :notas, estado = :estado, fecha = :fecha, tipo_descuento = :tipo_descuento, valor_descuento = :valor_descuento, monto_descuento = :monto_descuento, recibe = :recibe, extra = :extra WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_descuento", $datos["tipo_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":valor_descuento", $datos["valor_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":monto_descuento", $datos["monto_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":recibe", $datos["recibe"], PDO::PARAM_STR);
		$stmt->bindParam(":extra", $datos["extra"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt = null;

	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/

	static public function mdlEliminarVenta($tabla, $id){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $id, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt = null;

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/

	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();


		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like '%$fechaInicial%'");

			$stmt -> bindParam(":fecha", $fechaInicial, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{


				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*=============================================
	DESCARGAR XML
	=============================================*/

	static public function mdlDescargarXML($codigo){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE codigo = :codigo");

		$stmt -> bindParam(":codigo", $codigo, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt = null;
	}

	/*=============================================
	SUMA TOTAL VENTAS
	=============================================*/

	static public function mdlSumaTotalVentas($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM $tabla WHERE estado = 'venta'");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt = null;
	}


	//Obtener el siguiente código de venta
	static public function mdlObtenerSiguienteConsecutivo($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT codigo FROM $tabla WHERE estado IN ('venta', 'orden') ORDER BY codigo DESC LIMIT 1");

		$stmt -> execute();

		$resultado = $stmt -> fetch();

		$stmt = null;

		if($resultado){
			return $resultado["codigo"] + 1;
		}else{
			return 10001; // Primer consecutivo
		}

	}

	//Actualizar el consecutivo después de guardar la venta/orden
	static public function mdlActualizarConsecutivo($tabla, $codigo){
		// Este método ya no es necesario porque el consecutivo se maneja al obtener el siguiente
		return "ok";
	}


	static public function mdlRangoFechasVentasPorEstado($tabla, $fechaInicial, $fechaFinal, $estado){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.estado = :estado
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();


		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.fecha like '%$fechaInicial%' AND v.estado = :estado
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT v.*,
														c.nombre AS nombre_cliente,
														u.nombre AS nombre_vendedor
														FROM $tabla v
														LEFT JOIN clientes c ON v.id_cliente = c.id
														LEFT JOIN usuarios u ON v.id_vendedor = u.id
														WHERE v.fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' AND v.estado = :estado
														ORDER BY v.id DESC");

			}else{


				$stmt = Conexion::conectar()->prepare("SELECT v.*,
														c.nombre AS nombre_cliente,
														u.nombre AS nombre_vendedor
														FROM $tabla v
														LEFT JOIN clientes c ON v.id_cliente = c.id
														LEFT JOIN usuarios u ON v.id_vendedor = u.id
														WHERE v.fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND v.estado = :estado
														ORDER BY v.id DESC");

			}

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}


	//Para los reportes
	public static function mdlMostrarVentasAsociativo($tabla, $item, $valor)
	{
		$stmt = Conexion::conectar()->prepare("SELECT v.*,
												c.nombre AS nombre_cliente,
												u.nombre AS nombre_vendedor
												FROM $tabla v
												LEFT JOIN clientes c ON v.id_cliente = c.id
												LEFT JOIN usuarios u ON v.id_vendedor = u.id
												ORDER BY v.id DESC");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	static public function mdlActualizarNotaVenta($tabla, $datos) {
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET notas = :notas WHERE id = :id"); 

		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT); 

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}

		$stmt = null;
	}

 
	static public function mdlActualizarObservacionVenta($tabla, $datos) {
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET observacion = :observacion WHERE id = :id"); 

		$stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
			
		} else {
			return "error";
		}

		$stmt = null;
	}


	/*=============================================
	EDITAR IMAGEN DE VENTA
	=============================================*/
	static public function mdlEditarImagenVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET imagen = :imagen WHERE id = :id"); 

		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt = null;

	}


}
