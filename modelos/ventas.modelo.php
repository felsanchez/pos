<?php

require_once __DIR__ . "/conexion.php";

if (!class_exists('ModeloVentas')) {

class ModeloVentas
{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/

	static public function mdlMostrarVentas($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");

			$stmt->execute();

			return $stmt->fetchAll();

		}
	}

	/*=============================================
	REGISTRO DE VENTA
	=============================================*/

	static public function mdlIngresarVenta($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, numero_factura, id_cliente, id_vendedor, productos, impuesto, neto, total, metodo_pago, notas, estado, imagen, fecha, tipo_descuento, valor_descuento, monto_descuento, recibe, extra, retenciones, resolucion_id, fecha_vencimiento, orden_compra, forma_pago_dian, metodo_pago_dian_id, estado_dian, cufe, qr_data, xml_dian, pdf_dian, mensaje_dian, fecha_envio_dian) VALUES (:codigo, :numero_factura, :id_cliente, :id_vendedor, :productos, :impuesto, :neto, :total, :metodo_pago, :notas, :estado, :imagen, :fecha, :tipo_descuento, :valor_descuento, :monto_descuento, :recibe, :extra, :retenciones, :resolucion_id, :fecha_vencimiento, :orden_compra, :forma_pago_dian, :metodo_pago_dian_id, :estado_dian, :cufe, :qr_data, :xml_dian, :pdf_dian, :mensaje_dian, :fecha_envio_dian)");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":numero_factura", $datos["numero_factura"], PDO::PARAM_STR); // Se inicia como NULL
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
		$stmt->bindParam(":retenciones", $datos["retenciones"], PDO::PARAM_STR);

		// Campos Facturación Electrónica
		$stmt->bindParam(":resolucion_id", $datos["resolucion_id"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha_vencimiento", $datos["fecha_vencimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":orden_compra", $datos["orden_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":forma_pago_dian", $datos["forma_pago_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago_dian_id", $datos["metodo_pago_dian_id"], PDO::PARAM_STR);
		$stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":cufe", $datos["cufe"], PDO::PARAM_STR);
		$stmt->bindParam(":qr_data", $datos["qr_data"], PDO::PARAM_STR);
		$stmt->bindParam(":xml_dian", $datos["xml_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":pdf_dian", $datos["pdf_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_envio_dian", $datos["fecha_envio_dian"], PDO::PARAM_STR);

		if ($stmt->execute()) {

			return Conexion::conectar()->lastInsertId();

		} else {

			return "error";

		}
	}

	/*=============================================
	EDITAR VENTAS
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET id_cliente = :id_cliente, numero_factura = :numero_factura, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total = :total, metodo_pago = :metodo_pago, notas = :notas, estado = :estado, fecha = :fecha, tipo_descuento = :tipo_descuento, valor_descuento = :valor_descuento, monto_descuento = :monto_descuento, recibe = :recibe, extra = :extra, retenciones = :retenciones, resolucion_id = :resolucion_id, fecha_vencimiento = :fecha_vencimiento, orden_compra = :orden_compra, forma_pago_dian = :forma_pago_dian, metodo_pago_dian_id = :metodo_pago_dian_id, estado_dian = :estado_dian, cufe = :cufe, qr_data = :qr_data, xml_dian = :xml_dian, pdf_dian = :pdf_dian, mensaje_dian = :mensaje_dian, fecha_envio_dian = :fecha_envio_dian WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":numero_factura", $datos["numero_factura"], PDO::PARAM_STR);
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
		$stmt->bindParam(":retenciones", $datos["retenciones"], PDO::PARAM_STR);

		// Campos Facturación Electrónica
		$stmt->bindParam(":resolucion_id", $datos["resolucion_id"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha_vencimiento", $datos["fecha_vencimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":orden_compra", $datos["orden_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":forma_pago_dian", $datos["forma_pago_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago_dian_id", $datos["metodo_pago_dian_id"], PDO::PARAM_STR);
		$stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":cufe", $datos["cufe"], PDO::PARAM_STR);
		$stmt->bindParam(":qr_data", $datos["qr_data"], PDO::PARAM_STR);
		$stmt->bindParam(":xml_dian", $datos["xml_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":pdf_dian", $datos["pdf_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_envio_dian", $datos["fecha_envio_dian"], PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";

		}
	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/

	static public function mdlEliminarVenta($tabla, $id)
	{

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			if ($stmt->rowCount() > 0) {
				return "ok";
			} else {
				return "no_affected_rows";
			}
		} else {
			$error = $stmt->errorInfo();
			return "error_db: " . ($error[2] ?? "Unknown");
		}
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/

	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal)
	{

		if ($fechaInicial == null) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");

			$stmt->execute();

			return $stmt->fetchAll();


		} else if ($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like :fecha");

			$fechaBusqueda = '%' . $fechaInicial . '%';
			$stmt->bindParam(":fecha", $fechaBusqueda, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();

		} else {

			$fechaActual = new DateTime();
			$fechaActual->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if ($fechaFinalMasUno == $fechaActualMasUno) {

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			} else {


				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}

			$stmt->execute();

			return $stmt->fetchAll();

		}

	}

	/*=============================================
	DESCARGAR XML
	=============================================*/

	static public function mdlDescargarXML($codigo)
	{

		$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $codigo, PDO::PARAM_INT);

		$stmt->execute();

		return $stmt->fetch();
	}

	/*=============================================
	SUMA TOTAL VENTAS
	=============================================*/

	static public function mdlSumaTotalVentas($tabla)
	{

		$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM $tabla WHERE estado = 'venta'");

		$stmt->execute();

		return $stmt->fetch();
	}

	/*=============================================
	CONTAR VENTAS POR ESTADO
	=============================================*/
	static public function mdlContarVentasPorEstado($tabla, $estado)
	{
		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla WHERE estado = :estado");

		$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

		$stmt->execute();

		return $stmt->fetch();
	}


	//Obtener el siguiente código de venta real desde la tabla de consecutivos
	static public function mdlObtenerSiguienteConsecutivo($tabla)
	{
		$stmt = Conexion::conectar()->prepare("SELECT ultimo_numero FROM consecutivos WHERE tabla = :tabla");
		$stmt->bindParam(":tabla", $tabla, PDO::PARAM_STR);
		$stmt->execute();
		$resultado = $stmt->fetch();
		$stmt = null;

		if ($resultado) {
			return $resultado["ultimo_numero"] + 1;
		} else {
			// Si no existe el registro en consecutivos, intentar recuperar el max de ventas regulares
			$stmt = Conexion::conectar()->prepare("SELECT MAX(codigo) as max_codigo FROM ventas WHERE (numero_factura IS NULL OR numero_factura = '')");
			$stmt->execute();
			$resultadoMax = $stmt->fetch();

			if ($resultadoMax && $resultadoMax["max_codigo"] != null) {
				return $resultadoMax["max_codigo"] + 1;
			}
			return 10001;
		}
	}

	//Actualizar el consecutivo después de guardar la venta/orden
	static public function mdlActualizarConsecutivo($tabla, $codigo)
	{
		// Incrementar el último número en la tabla de consecutivos
		$stmt = Conexion::conectar()->prepare("UPDATE consecutivos SET ultimo_numero = ultimo_numero + 1 WHERE tabla = 'ventas'");

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	}


	static public function mdlRangoFechasVentasPorEstado($tabla, $fechaInicial, $fechaFinal, $estado)
	{

		if ($fechaInicial == null) {

			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													c.email AS email_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.estado = :estado
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();


		} else if ($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													c.email AS email_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.fecha like '%$fechaInicial%' AND v.estado = :estado
													AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL)
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();

		} else {

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			// Usamos un solo BETWEEN que cubre todo el rango incluyendo el fin del día final
			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													c.email AS email_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.fecha BETWEEN '$fechaInicial 00:00:00' AND '$fechaFinal 23:59:59' AND v.estado = :estado
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();

		}

	}


	//Para los reportes
	public static function mdlMostrarVentasAsociativo($tabla, $item, $valor)
	{
		$stmt = Conexion::conectar()->prepare("SELECT v.*,
												c.nombre AS nombre_cliente,
												c.email AS email_cliente,
												u.nombre AS nombre_vendedor
												FROM $tabla v
												LEFT JOIN clientes c ON v.id_cliente = c.id
												LEFT JOIN usuarios u ON v.id_vendedor = u.id
												ORDER BY v.id DESC");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	static public function mdlActualizarNotaVenta($tabla, $datos)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET notas = :notas WHERE id = :id");

		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}
	}


	static public function mdlActualizarObservacionVenta($tabla, $datos)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET observacion = :observacion WHERE id = :id");

		$stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}
	}



	/*=============================================
	EDITAR IMAGEN DE VENTA
	=============================================*/
	static public function mdlEditarImagenVenta($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET imagen = :imagen WHERE id = :id");

		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";
		}
	}

	/*=============================================
	ACTUALIZAR SEGUIMIENTO
	=============================================*/
	static public function mdlActualizarSeguimiento($tabla, $datos)
	{
		$columna = $datos["columna"];
		$valor = $datos["valor"];
		$id = $datos["id"];

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $columna = :valor WHERE id = :id");

		$stmt->bindParam(":valor", $valor, PDO::PARAM_INT);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	}



	/*=============================================
	MOSTRAR ULTIMA VENTA
	=============================================*/
	static public function mdlMostrarUltimaVenta($tabla)
	{

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 1");

		$stmt->execute();

		return $stmt->fetch();

	}

	/*=============================================
	MOSTRAR ULTIMA VENTA POR ESTADO
	=============================================*/
	/*=============================================
	MOSTRAR ULTIMA VENTA POR ESTADO
	=============================================*/
	static public function mdlMostrarUltimaVentaPorEstado($tabla, $estado)
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = :estado ORDER BY id DESC LIMIT 1");
		$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetch();
	}

	/*=============================================
	CONTAR FACTURAS ELECTRÓNICAS POR CLIENTE
	=============================================*/
	static public function mdlContarFacturasElectronicasPorCliente($tabla)
	{
		// Retorna un array [ id_cliente => cantidad ] para todos los clientes con FE
		$stmt = Conexion::conectar()->prepare(
			"SELECT id_cliente, COUNT(*) as total
			 FROM $tabla
			 WHERE estado = 'venta'
			   AND (numero_factura IS NOT NULL AND numero_factura != ''
			        OR (resolucion_id IS NOT NULL AND resolucion_id != 0))
			 GROUP BY id_cliente"
		);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$resultado = [];
		foreach ($rows as $row) {
			$resultado[$row['id_cliente']] = (int) $row['total'];
		}
		return $resultado;
	}

	/*=============================================
	MOSTRAR ULTIMA FACTURA ELECTRÓNICA
	=============================================*/
	static public function mdlMostrarUltimaFacturaElectronica($tabla)
	{
		// Buscamos la última venta que tenga una resolución ID (lo que la identifica como FE)
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 'venta' AND resolucion_id IS NOT NULL AND resolucion_id != 0 ORDER BY id DESC LIMIT 1");
		$stmt->execute();
		return $stmt->fetch();
	}

	/*=============================================
	RANGO FECHAS FACTURAS ELECTRONICAS
	=============================================*/
	static public function mdlRangoFechasFacturasElectronicas($tabla, $fechaInicial, $fechaFinal, $estado)
	{

		if ($fechaInicial == null) {

			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													c.email AS email_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.estado = :estado 
													AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL)
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();


		} else if ($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT v.*,
													c.nombre AS nombre_cliente,
													c.email AS email_cliente,
													u.nombre AS nombre_vendedor
													FROM $tabla v
													LEFT JOIN clientes c ON v.id_cliente = c.id
													LEFT JOIN usuarios u ON v.id_vendedor = u.id
													WHERE v.fecha like '%$fechaInicial%' AND v.estado = :estado
													AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL)
													ORDER BY v.id DESC");

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();

		} else {

			$fechaActual = new DateTime();
			$fechaActual->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if ($fechaFinalMasUno == $fechaActualMasUno) {

				$stmt = Conexion::conectar()->prepare("SELECT v.*,
														c.nombre AS nombre_cliente,
														c.email AS email_cliente,
														u.nombre AS nombre_vendedor
														FROM $tabla v
														LEFT JOIN clientes c ON v.id_cliente = c.id
														LEFT JOIN usuarios u ON v.id_vendedor = u.id
														WHERE v.fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' AND v.estado = :estado
														AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL)
														ORDER BY v.id DESC");

			} else {


				$stmt = Conexion::conectar()->prepare("SELECT v.*,
														c.nombre AS nombre_cliente,
														c.email AS email_cliente,
														u.nombre AS nombre_vendedor
														FROM $tabla v
														LEFT JOIN clientes c ON v.id_cliente = c.id
														LEFT JOIN usuarios u ON v.id_vendedor = u.id
														WHERE v.fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND v.estado = :estado
														AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL)
														ORDER BY v.id DESC");

			}

			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetchAll();

		}

	}

}

}
