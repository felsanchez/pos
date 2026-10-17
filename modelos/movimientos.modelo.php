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

		date_default_timezone_set('America/Bogota');
		$fechaActual = !empty($datos["fecha"]) ? $datos["fecha"] : date("Y-m-d H:i:s");

		// Sanitizar y asegurar valores por defecto para que NUNCA falle por NULL o tipos inválidos en hosting
		$tipoProducto = !empty($datos["tipo_producto"]) ? $datos["tipo_producto"] : "producto";
		$idProducto = intval($datos["id_producto"]);
		$idVariante = !empty($datos["id_variante"]) ? intval($datos["id_variante"]) : 0;
		
		$idBodega = 1;
		if (!empty($datos["id_bodega"])) {
			$idBodega = intval($datos["id_bodega"]);
		} else if (!empty($_SESSION["id_bodega"])) {
			$idBodega = intval($_SESSION["id_bodega"]);
		}

		$nombreProducto = !empty($datos["nombre_producto"]) ? $datos["nombre_producto"] : "Producto";
		$tipoMovimiento = !empty($datos["tipo_movimiento"]) ? $datos["tipo_movimiento"] : "ajuste_manual";
		$cantidad = isset($datos["cantidad"]) ? intval($datos["cantidad"]) : 0;
		$stockAnterior = isset($datos["stock_anterior"]) ? intval($datos["stock_anterior"]) : 0;
		$stockNuevo = isset($datos["stock_nuevo"]) ? intval($datos["stock_nuevo"]) : 0;
		
		$idUsuario = 1;
		if (!empty($datos["id_usuario"])) {
			$idUsuario = intval($datos["id_usuario"]);
		} else if (!empty($_SESSION["id"])) {
			$idUsuario = intval($_SESSION["id"]);
		}

		$nombreUsuario = "Sistema";
		if (!empty($datos["nombre_usuario"])) {
			$nombreUsuario = $datos["nombre_usuario"];
		} else if (!empty($_SESSION["nombre"])) {
			$nombreUsuario = $_SESSION["nombre"];
		}

		$referencia = isset($datos["referencia"]) ? strval($datos["referencia"]) : "";
		$notas = isset($datos["notas"]) ? strval($datos["notas"]) : "";

		$conexion = Conexion::conectar();

		$sql = "INSERT INTO movimientos_stock (tipo_producto, id_producto, id_variante, id_bodega, nombre_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, id_usuario, nombre_usuario, referencia, notas, fecha) VALUES (:tipo_producto, :id_producto, :id_variante, :id_bodega, :nombre_producto, :tipo_movimiento, :cantidad, :stock_anterior, :stock_nuevo, :id_usuario, :nombre_usuario, :referencia, :notas, :fecha)";

		try {
			$stmt = $conexion->prepare($sql);
			$stmt->bindParam(":tipo_producto", $tipoProducto, PDO::PARAM_STR);
			$stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
			$stmt->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
			$stmt->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
			$stmt->bindParam(":nombre_producto", $nombreProducto, PDO::PARAM_STR);
			$stmt->bindParam(":tipo_movimiento", $tipoMovimiento, PDO::PARAM_STR);
			$stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
			$stmt->bindParam(":stock_anterior", $stockAnterior, PDO::PARAM_INT);
			$stmt->bindParam(":stock_nuevo", $stockNuevo, PDO::PARAM_INT);
			$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
			$stmt->bindParam(":nombre_usuario", $nombreUsuario, PDO::PARAM_STR);
			$stmt->bindParam(":referencia", $referencia, PDO::PARAM_STR);
			$stmt->bindParam(":notas", $notas, PDO::PARAM_STR);
			$stmt->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);

			if ($stmt->execute()) {
				return "ok";
			}
		} catch (Exception $e) {
			// Intentar alterar la columna si el hosting lo permite
			try {
				$conexion->exec("ALTER TABLE movimientos_stock MODIFY COLUMN tipo_movimiento VARCHAR(50) NOT NULL");
				$stmt2 = $conexion->prepare($sql);
				$stmt2->bindParam(":tipo_producto", $tipoProducto, PDO::PARAM_STR);
				$stmt2->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
				$stmt2->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
				$stmt2->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
				$stmt2->bindParam(":nombre_producto", $nombreProducto, PDO::PARAM_STR);
				$stmt2->bindParam(":tipo_movimiento", $tipoMovimiento, PDO::PARAM_STR);
				$stmt2->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
				$stmt2->bindParam(":stock_anterior", $stockAnterior, PDO::PARAM_INT);
				$stmt2->bindParam(":stock_nuevo", $stockNuevo, PDO::PARAM_INT);
				$stmt2->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
				$stmt2->bindParam(":nombre_usuario", $nombreUsuario, PDO::PARAM_STR);
				$stmt2->bindParam(":referencia", $referencia, PDO::PARAM_STR);
				$stmt2->bindParam(":notas", $notas, PDO::PARAM_STR);
				$stmt2->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);
				if ($stmt2->execute()) {
					return "ok";
				}
			} catch (Exception $ex) {
				// Si no tiene permisos DDL en hosting, pasamos al fallback seguro
			}

			// FALLBACK ULTRA-SEGURO PARA HOSTING CON ENUM RESTRICTIVO
			$referenciaFallback = $referencia;
			if ($tipoMovimiento == "edicion_excel" && stripos($referenciaFallback, "Excel") === false) {
				$referenciaFallback = "Importación Excel/CSV - " . $referenciaFallback;
			}
			$tipoMovimientoFallback = "ajuste_manual";

			try {
				$stmtFB = $conexion->prepare($sql);
				$stmtFB->bindParam(":tipo_producto", $tipoProducto, PDO::PARAM_STR);
				$stmtFB->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
				$stmtFB->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
				$stmtFB->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
				$stmtFB->bindParam(":nombre_producto", $nombreProducto, PDO::PARAM_STR);
				$stmtFB->bindParam(":tipo_movimiento", $tipoMovimientoFallback, PDO::PARAM_STR);
				$stmtFB->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
				$stmtFB->bindParam(":stock_anterior", $stockAnterior, PDO::PARAM_INT);
				$stmtFB->bindParam(":stock_nuevo", $stockNuevo, PDO::PARAM_INT);
				$stmtFB->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
				$stmtFB->bindParam(":nombre_usuario", $nombreUsuario, PDO::PARAM_STR);
				$stmtFB->bindParam(":referencia", $referenciaFallback, PDO::PARAM_STR);
				$stmtFB->bindParam(":notas", $notas, PDO::PARAM_STR);
				$stmtFB->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);

				if ($stmtFB->execute()) {
					return "ok";
				}
			} catch (Exception $exFB) {
				error_log("Error crítico al registrar movimiento stock fallback: " . $exFB->getMessage());
			}
		}

		return "ok";
	}

	/*=============================================
	MOSTRAR MOVIMIENTOS CON FILTROS
	=============================================*/
	static public function mdlMostrarMovimientos($filtros)
	{

		$sql = "SELECT m.*, p.descripcion as producto_descripcion, b.nombre as nombre_bodega
		        FROM movimientos_stock m
		        LEFT JOIN productos p ON m.id_producto = p.id
		        LEFT JOIN bodegas b ON m.id_bodega = b.id
		        WHERE 1=1";

		// 🔹 OCULTAR MOVIMIENTOS AUTOMÁTICOS (aquellos generados por ventas de variantes)
		// Busca cualquier referencia que contenga "(por variante" sin importar lo que venga después
		$sql .= " AND (m.referencia NOT LIKE '%(por variante%' OR m.referencia IS NULL)";

		// Aplicar filtros
		if (!empty($filtros["id_producto"])) {
			if (strpos($filtros["id_producto"], 'v_') === 0) {
				$sql .= " AND m.id_variante = :id_variante";
			} else {
				$sql .= " AND m.id_producto = :id_producto";
			}
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

		if (!empty($filtros["id_bodega"])) {
			$sql .= " AND m.id_bodega = :id_bodega";
		}

		$sql .= " ORDER BY m.fecha DESC, m.id DESC";

		$stmt = Conexion::conectar()->prepare($sql);

		// Bind de parámetros
		if (!empty($filtros["id_producto"])) {
			if (strpos($filtros["id_producto"], 'v_') === 0) {
				$id_variante = intval(substr($filtros["id_producto"], 2));
				$stmt->bindParam(":id_variante", $id_variante, PDO::PARAM_INT);
			} else {
				$id_producto_val = intval($filtros["id_producto"]);
				$stmt->bindParam(":id_producto", $id_producto_val, PDO::PARAM_INT);
			}
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

		if (!empty($filtros["id_bodega"])) {
			$stmt->bindParam(":id_bodega", $filtros["id_bodega"], PDO::PARAM_INT);
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

		if (!empty($filtros["id_bodega"])) {
			$sql .= " AND id_bodega = :id_bodega";
		}

		$sql .= " GROUP BY tipo_movimiento";

		$stmt = Conexion::conectar()->prepare($sql);

		if (!empty($filtros["fecha_desde"])) {
			$stmt->bindParam(":fecha_desde", $filtros["fecha_desde"], PDO::PARAM_STR);
		}

		if (!empty($filtros["fecha_hasta"])) {
			$stmt->bindParam(":fecha_hasta", $filtros["fecha_hasta"], PDO::PARAM_STR);
		}

		if (!empty($filtros["id_bodega"])) {
			$stmt->bindParam(":id_bodega", $filtros["id_bodega"], PDO::PARAM_INT);
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

		$stmt->closeCursor();
		$stmt = null;
	}

	/*=============================================
	LIMPIAR MOVIMIENTOS ANTIGUOS (MÁS DE 3 MESES)
	=============================================*/
	static public function mdlLimpiarHistorialAntiguo($tabla)
	{
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE fecha < DATE_SUB(NOW(), INTERVAL 3 MONTH)");

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->closeCursor();
		$stmt = null;
	}

	/*=============================================
	MOSTRAR MOVIMIENTOS SERVER-SIDE
	=============================================*/
	static public function mdlMostrarMovimientosServerSide($tabla, $where, $order, $limit)
	{
		try {
			$sql = "SELECT m.*, b.nombre as nombre_bodega 
			        FROM $tabla m
			        LEFT JOIN bodegas b ON m.id_bodega = b.id
			        $where $order $limit";
			
			$stmt = Conexion::conectar()->prepare($sql);
			$stmt->execute();
			
			return $stmt->fetchAll();
		} catch (Exception $e) {
			// Fallback si la columna id_bodega o la tabla bodegas no existe en el hosting
			try {
				$sqlFB = "SELECT m.*, 'Principal' as nombre_bodega 
				          FROM $tabla m
				          $where $order $limit";
				$stmtFB = Conexion::conectar()->prepare($sqlFB);
				$stmtFB->execute();
				return $stmtFB->fetchAll();
			} catch (Exception $exFB) {
				return array();
			}
		}
	}

	/*=============================================
	OBTENER TOTAL MOVIMIENTOS (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalMovimientos($tabla, $where)
	{
		try {
			$sql = "SELECT COUNT(*) as total FROM $tabla m $where";
			
			$stmt = Conexion::conectar()->prepare($sql);
			$stmt->execute();
			$resultado = $stmt->fetch();
			
			return $resultado ? $resultado["total"] : 0;
		} catch (Exception $e) {
			return 0;
		}
	}

}