<?php

require_once "conexion.php";

class ModeloGastos
{

	/*=============================================
	MOSTRAR GASTOS
	=============================================*/

	static public function mdlMostrarGastos($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::conectar()->prepare("SELECT g.*,
													c.nombre as categoria_nombre,
													c.color as categoria_color,
													u.nombre as usuario_nombre,
													p.nombre as proveedor_nombre
												FROM $tabla g
												LEFT JOIN categorias_gastos c ON g.id_categoria_gasto = c.id
												LEFT JOIN usuarios u ON g.id_usuario = u.id
												LEFT JOIN proveedores p ON g.id_proveedor = p.id
												WHERE g.$item = :$item
												ORDER BY g.id DESC");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT g.*,
													c.nombre as categoria_nombre,
													c.color as categoria_color,
													u.nombre as usuario_nombre,
													p.nombre as proveedor_nombre
												FROM $tabla g
												LEFT JOIN categorias_gastos c ON g.id_categoria_gasto = c.id
												LEFT JOIN usuarios u ON g.id_usuario = u.id
												LEFT JOIN proveedores p ON g.id_proveedor = p.id
												ORDER BY g.id DESC");

			$stmt->execute();

			return $stmt->fetchAll();

		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR GASTOS CON FILTROS
	=============================================*/

	static public function mdlMostrarGastosFiltrados($fechaInicio, $fechaFin, $categoria, $proveedor)
	{

		$sql = "SELECT g.*,
				c.nombre as categoria_nombre,
				c.color as categoria_color,
				u.nombre as usuario_nombre,
				p.nombre as proveedor_nombre
				FROM gastos g
				LEFT JOIN categorias_gastos c ON g.id_categoria_gasto = c.id
				LEFT JOIN usuarios u ON g.id_usuario = u.id
				LEFT JOIN proveedores p ON g.id_proveedor = p.id
				WHERE 1=1";

		if (!empty($fechaInicio) && !empty($fechaFin)) {
			$sql .= " AND g.fecha BETWEEN :fechaInicio AND :fechaFin";
		}

		if (!empty($categoria)) {
			$sql .= " AND g.id_categoria_gasto = :categoria";
		}

		if (!empty($proveedor)) {
			$sql .= " AND g.id_proveedor = :proveedor";
		}

		$sql .= " ORDER BY g.id DESC";

		$stmt = Conexion::conectar()->prepare($sql);

		if (!empty($fechaInicio) && !empty($fechaFin)) {
			$stmt->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
			$stmt->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);
		}

		if (!empty($categoria)) {
			$stmt->bindParam(":categoria", $categoria, PDO::PARAM_INT);
		}

		if (!empty($proveedor)) {
			$stmt->bindParam(":proveedor", $proveedor, PDO::PARAM_INT);
		}

		$stmt->execute();

		return $stmt->fetchAll();

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	CREAR GASTO
	=============================================*/

	static public function mdlIngresarGasto($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, concepto, monto, fecha, id_categoria_gasto, id_usuario, id_proveedor, metodo_pago, numero_comprobante, imagen_comprobante, estado, notas) VALUES (:codigo, :concepto, :monto, :fecha, :id_categoria_gasto, :id_usuario, :id_proveedor, :metodo_pago, :numero_comprobante, :imagen_comprobante, :estado, :notas)");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":concepto", $datos["concepto"], PDO::PARAM_STR);
		$stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":id_categoria_gasto", $datos["id_categoria_gasto"], PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":numero_comprobante", $datos["numero_comprobante"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen_comprobante", $datos["imagen_comprobante"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR GASTO
	=============================================*/

	static public function mdlEditarGasto($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET concepto = :concepto, monto = :monto, fecha = :fecha, id_categoria_gasto = :id_categoria_gasto, id_proveedor = :id_proveedor, metodo_pago = :metodo_pago, numero_comprobante = :numero_comprobante, imagen_comprobante = :imagen_comprobante, estado = :estado, notas = :notas WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":concepto", $datos["concepto"], PDO::PARAM_STR);
		$stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":id_categoria_gasto", $datos["id_categoria_gasto"], PDO::PARAM_INT);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":numero_comprobante", $datos["numero_comprobante"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen_comprobante", $datos["imagen_comprobante"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR GASTO
	=============================================*/

	static public function mdlEliminarGasto($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt->bindParam(":id", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";

		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	OBTENER ÚLTIMO CÓDIGO DE GASTO
	=============================================*/

	static public function mdlObtenerUltimoCodigo($tabla)
	{

		$stmt = Conexion::conectar()->prepare("SELECT codigo FROM $tabla ORDER BY id DESC LIMIT 1");

		$stmt->execute();

		$resultado = $stmt->fetch();

		if ($resultado) {
			// Extraer el número del código (ej: GAS-001 -> 001)
			$numero = (int) substr($resultado["codigo"], 4);
			return $numero;
		} else {
			return 0;
		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	SUMA TOTAL DE GASTOS
	=============================================*/

	static public function mdlSumarTotalGastos()
	{

		$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as total FROM gastos WHERE estado = 'aprobado'");

		$stmt->execute();

		$resultado = $stmt->fetch();

		return $resultado["total"];

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	SUMA TOTAL DE GASTOS POR RANGO DE FECHAS
	=============================================*/

	static public function mdlSumarGastosPorFecha($fechaInicio, $fechaFin)
	{

		$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as total FROM gastos WHERE fecha BETWEEN :fechaInicio AND :fechaFin AND estado = 'aprobado'");

		$stmt->bindParam(":fechaInicio", $fechaInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaFin", $fechaFin, PDO::PARAM_STR);

		$stmt->execute();

		$resultado = $stmt->fetch();

		return $resultado["total"];

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	GASTOS POR CATEGORÍA
	=============================================*/

	static public function mdlGastosPorCategoria()
	{

		$stmt = Conexion::conectar()->prepare("SELECT c.nombre, c.color, SUM(g.monto) as total
												FROM gastos g
												INNER JOIN categorias_gastos c ON g.id_categoria_gasto = c.id
												WHERE g.estado = 'aprobado'
												GROUP BY g.id_categoria_gasto
												ORDER BY total DESC");

		$stmt->execute();
		return $stmt->fetchAll();
		$stmt->close();
		$stmt = null;
	}


	/*=============================================
	ACTUALIZAR IMAGEN DE COMPROBANTE
	=============================================*/

	static public function mdlActualizarImagenGasto($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET imagen_comprobante = :imagen_comprobante WHERE id = :id");

		$stmt->bindParam(":imagen_comprobante", $datos["imagen_comprobante"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR NOTA DE GASTO
	=============================================*/

	static public function mdlActualizarNotaGasto($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET notas = :notas WHERE id = :id");

		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

}