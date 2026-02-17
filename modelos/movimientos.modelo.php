<?php

require_once "conexion.php";

class ModeloMovimientos
{

	/*=============================================
	REGISTRAR MOVIMIENTO DE STOCK
	=============================================*/
	static public function mdlRegistrarMovimiento($datos)
	{

		// Validar que id_producto no sea NULL
		if (empty($datos["id_producto"]) || $datos["id_producto"] === null) {
			error_log("ERROR: Intento de registrar movimiento con id_producto NULL");
			error_log("Datos recibidos: " . print_r($datos, true));
			return "error";
		}

		$stmt = Conexion::conectar()->prepare("INSERT INTO movimientos_stock (tipo_producto, id_producto, id_variante, nombre_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, id_usuario, nombre_usuario, referencia, notas) VALUES (:tipo_producto, :id_producto, :id_variante, :nombre_producto, :tipo_movimiento, :cantidad, :stock_anterior, :stock_nuevo, :id_usuario, :nombre_usuario, :referencia, :notas)");

		$stmt->bindParam(":tipo_producto", $datos["tipo_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
		$stmt->bindParam(":id_variante", $datos["id_variante"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre_producto", $datos["nombre_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_movimiento", $datos["tipo_movimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
		$stmt->bindParam(":stock_anterior", $datos["stock_anterior"], PDO::PARAM_INT);
		$stmt->bindParam(":stock_nuevo", $datos["stock_nuevo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre_usuario", $datos["nombre_usuario"], PDO::PARAM_STR);
		$stmt->bindParam(":referencia", $datos["referencia"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}

	/*=============================================
	MOSTRAR MOVIMIENTOS CON FILTROS
	=============================================*/
	static public function mdlMostrarMovimientos($filtros)
	{

		$sql = "SELECT m.*, p.descripcion as producto_descripcion
		        FROM movimientos_stock m
		        LEFT JOIN productos p ON m.id_producto = p.id
		        WHERE 1=1";

		// 🔹 OCULTAR MOVIMIENTOS AUTOMÁTICOS (aquellos generados por ventas de variantes)
		// Busca cualquier referencia que contenga "(por variante" sin importar lo que venga después
		$sql .= " AND (m.referencia NOT LIKE '%(por variante%' OR m.referencia IS NULL)";

		// Aplicar filtros
		if (!empty($filtros["id_producto"])) {
			$sql .= " AND m.id_producto = :id_producto";
		}

		if (!empty($filtros["tipo_movimiento"])) {
			$sql .= " AND m.tipo_movimiento = :tipo_movimiento";
		}

		if (!empty($filtros["fecha_desde"])) {
			$sql .= " AND DATE(m.fecha) >= :fecha_desde";
		}

		if (!empty($filtros["fecha_hasta"])) {
			$sql .= " AND DATE(m.fecha) <= :fecha_hasta";
		}

		if (!empty($filtros["usuario"])) {
			$sql .= " AND m.id_usuario = :usuario";
		}

		$sql .= " ORDER BY m.fecha DESC";

		$stmt = Conexion::conectar()->prepare($sql);

		// Bind de parámetros
		if (!empty($filtros["id_producto"])) {
			$stmt->bindParam(":id_producto", $filtros["id_producto"], PDO::PARAM_INT);
		}

		if (!empty($filtros["tipo_movimiento"])) {
			$stmt->bindParam(":tipo_movimiento", $filtros["tipo_movimiento"], PDO::PARAM_STR);
		}

		if (!empty($filtros["fecha_desde"])) {
			$stmt->bindParam(":fecha_desde", $filtros["fecha_desde"], PDO::PARAM_STR);
		}

		if (!empty($filtros["fecha_hasta"])) {
			$stmt->bindParam(":fecha_hasta", $filtros["fecha_hasta"], PDO::PARAM_STR);
		}

		if (!empty($filtros["usuario"])) {
			$stmt->bindParam(":usuario", $filtros["usuario"], PDO::PARAM_INT);
		}

		$stmt->execute();

		return $stmt->fetchAll();

		$stmt = null;
	}

	/*=============================================
	OBTENER RESUMEN DE MOVIMIENTOS
	=============================================*/
	static public function mdlObtenerResumen($filtros)
	{

		$sql = "SELECT
		            tipo_movimiento,
		            COUNT(*) as total_movimientos,
		            SUM(ABS(cantidad)) as total_unidades
		        FROM movimientos_stock
		        WHERE 1=1";

		// 🔹 OCULTAR MOVIMIENTOS AUTOMÁTICOS del resumen también
		$sql .= " AND (referencia NOT LIKE '%(por variante%' OR referencia IS NULL)";

		// Aplicar filtros
		if (!empty($filtros["fecha_desde"])) {
			$sql .= " AND DATE(fecha) >= :fecha_desde";
		}

		if (!empty($filtros["fecha_hasta"])) {
			$sql .= " AND DATE(fecha) <= :fecha_hasta";
		}

		$sql .= " GROUP BY tipo_movimiento";

		$stmt = Conexion::conectar()->prepare($sql);

		if (!empty($filtros["fecha_desde"])) {
			$stmt->bindParam(":fecha_desde", $filtros["fecha_desde"], PDO::PARAM_STR);
		}

		if (!empty($filtros["fecha_hasta"])) {
			$stmt->bindParam(":fecha_hasta", $filtros["fecha_hasta"], PDO::PARAM_STR);
		}

		$stmt->execute();

		return $stmt->fetchAll();

		$stmt = null;
	}


	/*=============================================
	ACTUALIZAR NOTAS
	=============================================*/

	static public function mdlActualizarNota($tabla, $id, $nota)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET notas = :nota WHERE id = :id");
		$stmt->bindParam(":nota", $nota, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	OBTENER MOVIMIENTOS POR RANGO DE FECHAS
	=============================================*/
	static public function mdlObtenerMovimientosPorFecha($tabla, $fechaInicial, $fechaFinal)
	{

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla
												WHERE DATE(fecha) >= :fechaInicial
												AND DATE(fecha) <= :fechaFinal
												ORDER BY fecha DESC");

		$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
		$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);

		$stmt->execute();

		return $stmt->fetchAll();

		$stmt = null;
	}


	/*=============================================
	ELIMINAR MOVIMIENTOS MÚLTIPLES
	=============================================*/
	static public function mdlEliminarMovimientos($idsJson)
	{

		$ids = json_decode($idsJson, true);

		if (empty($ids) || !is_array($ids)) {
			return "error";
		}

		// Crear placeholders para el query
		$placeholders = implode(',', array_fill(0, count($ids), '?'));

		$stmt = Conexion::conectar()->prepare("DELETE FROM movimientos_stock WHERE id IN ($placeholders)");

		// Bind de cada ID
		foreach ($ids as $index => $id) {
			$stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
		}

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

}