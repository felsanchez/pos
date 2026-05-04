<?php

require_once __DIR__ . "/conexion.php";

class ModeloProductos
{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/

	static public function mdlMostrarProductos($tabla, $item, $valor, $orden)
	{

		if ($item != null) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();
		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY $orden DESC");

			$stmt->execute();

			return $stmt->fetchAll();

		}


		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR PRODUCTOS SERVER-SIDE
	=============================================*/
	static public function mdlMostrarProductosServerSide($tabla, $where, $order, $limit)
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT p.*, c.categoria AS nombre_categoria, prov.nombre AS nombre_proveedor, t.nombre AS nombre_tributo
			FROM $tabla p
			LEFT JOIN categorias c ON p.id_categoria = c.id
			LEFT JOIN proveedores prov ON p.id_proveedor = prov.id
			LEFT JOIN factus_tributos t ON p.tributo_id = t.id
			$where $order $limit
		");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/*=============================================
	OBTENER TOTAL PRODUCTOS (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalProductos($tabla, $where)
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(*) 
			FROM $tabla p
			LEFT JOIN categorias c ON p.id_categoria = c.id
			LEFT JOIN proveedores prov ON p.id_proveedor = prov.id
			LEFT JOIN factus_tributos t ON p.tributo_id = t.id
			$where
		");
		$stmt->execute();
		return $stmt->fetchColumn();
	}



	/*=============================================
	REGISTRO DE PRODUCTOS
	=============================================*/
	static public function mdlIngresarProducto($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_categoria, codigo, descripcion, imagen, stock, precio_compra, precio_venta, id_proveedor, tiene_variantes, unidad_medida_id, codigo_estandar_id, es_excluido, tributo_id, tasa_impuesto, notas_facturacion, scheme_id) VALUES (:id_categoria, :codigo, :descripcion, :imagen, :stock, :precio_compra, :precio_venta, :id_proveedor, :tiene_variantes, :unidad_medida_id, :codigo_estandar_id, :es_excluido, :tributo_id, :tasa_impuesto, :notas_facturacion, :scheme_id)");

		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":tiene_variantes", $datos["tiene_variantes"], PDO::PARAM_INT);

		// Campos de facturación
		$stmt->bindParam(":unidad_medida_id", $datos["unidad_medida_id"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo_estandar_id", $datos["codigo_estandar_id"], PDO::PARAM_INT);
		$stmt->bindParam(":es_excluido", $datos["es_excluido"], PDO::PARAM_INT);
		$stmt->bindParam(":tributo_id", $datos["tributo_id"], PDO::PARAM_INT);
		$stmt->bindParam(":tasa_impuesto", $datos["tasa_impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":notas_facturacion", $datos["notas_facturacion"], PDO::PARAM_STR);
		$stmt->bindParam(":scheme_id", $datos["scheme_id"], PDO::PARAM_STR);

		// Manejar NULL para id_proveedor
		if ($datos["id_proveedor"] === null || $datos["id_proveedor"] === "" || $datos["id_proveedor"] === 0 || $datos["id_proveedor"] === "0") {
			$stmt->bindValue(":id_proveedor", null, PDO::PARAM_NULL);
		} else {
			$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		}

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	EDITAR PRODUCTOS
	=============================================*/
	static public function mdlEditarProducto($tabla, $datos)
	{

		// Usar ID si está disponible, sino usar código
		if (isset($datos["id"]) && !empty($datos["id"])) {
			$whereClause = "WHERE id = :id";
		} else {
			$whereClause = "WHERE codigo = :codigo";
		}

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET id_categoria = :id_categoria, codigo = :codigo, descripcion = :descripcion, imagen = :imagen, stock = :stock, precio_compra = :precio_compra, precio_venta = :precio_venta, id_proveedor = :id_proveedor, unidad_medida_id = :unidad_medida_id, codigo_estandar_id = :codigo_estandar_id, es_excluido = :es_excluido, tributo_id = :tributo_id, tasa_impuesto = :tasa_impuesto, notas_facturacion = :notas_facturacion, scheme_id = :scheme_id $whereClause");

		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);

		// Campos de facturación
		$stmt->bindParam(":unidad_medida_id", $datos["unidad_medida_id"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo_estandar_id", $datos["codigo_estandar_id"], PDO::PARAM_INT);
		$stmt->bindParam(":es_excluido", $datos["es_excluido"], PDO::PARAM_INT);
		$stmt->bindParam(":tributo_id", $datos["tributo_id"], PDO::PARAM_INT);
		$stmt->bindParam(":tasa_impuesto", $datos["tasa_impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":notas_facturacion", $datos["notas_facturacion"], PDO::PARAM_STR);
		$stmt->bindParam(":scheme_id", $datos["scheme_id"], PDO::PARAM_STR);

		// Bind del WHERE clause
		if (isset($datos["id"]) && !empty($datos["id"])) {
			$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		}

		// Manejar NULL para id_proveedor (MISMO CÓDIGO QUE EN CREAR)
		if ($datos["id_proveedor"] === null || $datos["id_proveedor"] === "" || $datos["id_proveedor"] === 0 || $datos["id_proveedor"] === "0") {
			$stmt->bindValue(":id_proveedor", null, PDO::PARAM_NULL);
		} else {
			$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		}

		if ($stmt->execute()) {
			return "ok";
		} else {
			error_log("Error al ejecutar UPDATE en productos: " . print_r($stmt->errorInfo(), true));
			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	ELIMINAR PRODUCTOS
	=============================================*/
	static public function mdlEliminarProducto($tabla, $datos)
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
	ACTUALIZAR PRODUCTO
	=============================================*/

	static public function mdlActualizarProducto($tabla, $item1, $valor1, $valor)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":id", $valor, PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	MOSTRAR SUMA VENTAS
	=============================================*/

	static public function mdlMostrarSumaVentas($tabla)
	{

		$stmt = Conexion::conectar()->prepare("SELECT SUM(ventas) as total FROM $tabla");

		$stmt->execute();

		return $stmt->fetch();

		$stmt->close();

		$stmt = null;
	}


	/*=============================================
	Actualizar Imagen Producto desde DataTable
	=============================================*/
	/*
	public static function mdlActualizarImagenProducto($tabla, $idProducto, $rutaImagen){
		$pdo = Conexion::conectar();
		$stmt = $pdo->prepare("UPDATE {$tabla} SET imagen = :imagen WHERE id = :id");
		$stmt->bindParam(":imagen", $rutaImagen, PDO::PARAM_STR);
		$stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);
		$ok = $stmt->execute();
		$stmt = null;
		return $ok;
	}
		*/




	/*=============================================
	ACTUALIZAR IMAGEN DE PRODUCTO
	=============================================*/
	static public function mdlActualizarImagenProducto($tabla, $datos, $id)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET imagen = :imagen WHERE id = :id");

		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}



	/*=============================================
REGISTRAR PRODUCTO CON VARIANTES - RETORNA ID
=============================================*/

	static public function mdlIngresarProductoConVariantes($tabla, $datos)
	{

		$conexion = Conexion::conectar();

		$stmt = $conexion->prepare("INSERT INTO $tabla(id_categoria, codigo, descripcion, imagen, stock, precio_compra, precio_venta, id_proveedor, tiene_variantes, unidad_medida_id, codigo_estandar_id, es_excluido, tributo_id, tasa_impuesto, notas_facturacion, scheme_id) VALUES (:id_categoria, :codigo, :descripcion, :imagen, :stock, :precio_compra, :precio_venta, :id_proveedor, :tiene_variantes, :unidad_medida_id, :codigo_estandar_id, :es_excluido, :tributo_id, :tasa_impuesto, :notas_facturacion, :scheme_id)");

		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":tiene_variantes", $datos["tiene_variantes"], PDO::PARAM_INT);

		// Campos de facturación
		$stmt->bindParam(":unidad_medida_id", $datos["unidad_medida_id"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo_estandar_id", $datos["codigo_estandar_id"], PDO::PARAM_INT);
		$stmt->bindParam(":es_excluido", $datos["es_excluido"], PDO::PARAM_INT);
		$stmt->bindParam(":tributo_id", $datos["tributo_id"], PDO::PARAM_INT);
		$stmt->bindParam(":tasa_impuesto", $datos["tasa_impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":notas_facturacion", $datos["notas_facturacion"], PDO::PARAM_STR);
		$stmt->bindParam(":scheme_id", $datos["scheme_id"], PDO::PARAM_STR);

		// Manejar NULL para id_proveedor
		if ($datos["id_proveedor"] === null || $datos["id_proveedor"] === "" || $datos["id_proveedor"] === 0 || $datos["id_proveedor"] === "0") {

			$stmt->bindValue(":id_proveedor", null, PDO::PARAM_NULL);

		} else {
			$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		}

		if ($stmt->execute()) {
			// Retornar el ID del producto recién insertado usando la misma conexión
			return $conexion->lastInsertId();
		} else {
			return false;
		}

		$stmt->close();

		$stmt = null;

	}


	/*=============================================
	GUARDAR VARIANTE DE PRODUCTO
	=============================================*/

	static public function mdlGuardarVariante($datos)
	{

		$conexion = Conexion::conectar();
		$stmt = $conexion->prepare("INSERT INTO productos_variantes(id_producto, sku, precio_adicional, stock, imagen, estado) VALUES (:id_producto, :sku, :precio_adicional, :stock, :imagen, :estado)");

		$stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
		$stmt->bindParam(":sku", $datos["sku"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_adicional", $datos["precio_adicional"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			// Retornar el ID de la variante recién insertada usando la misma conexión
			return $conexion->lastInsertId();
		} else {
			return false;
		}

		$stmt->close();

		$stmt = null;
	}


	/*=============================================
	RELACIONAR VARIANTE CON OPCIONES
	=============================================*/

	static public function mdlGuardarVarianteOpcion($datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO productos_variantes_opciones(id_producto_variante, id_opcion_variante) VALUES (:id_producto_variante, :id_opcion_variante)");

		$stmt->bindParam(":id_producto_variante", $datos["id_producto_variante"], PDO::PARAM_INT);

		$stmt->bindParam(":id_opcion_variante", $datos["id_opcion_variante"], PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";

		} else {

			return "error";
		}

		$stmt->close();

		$stmt = null;

	}


	/*=============================================
	GENERAR SKU AUTOMÁTICO PARA VARIANTE
	=============================================*/

	static public function mdlGenerarSKU($codigoProducto, $idsOpciones)
	{

		// Formato: CODIGO-PROD_ID1_ID2_ID3

		// Ejemplo: 101_5_12_8 (producto 101, opciones 5, 12, 8)

		$sku = $codigoProducto . "_" . implode("_", $idsOpciones);

		return $sku;

	}


	/*=============================================
	OBTENER VARIANTES DE UN PRODUCTO
	=============================================*/

	static public function mdlObtenerVariantesProducto($idProducto)
	{

		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos_variantes WHERE id_producto = :id_producto ORDER BY id ASC");
		$stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);

		$stmt->execute();

		return $stmt->fetchAll();

		$stmt->close();

		$stmt = null;
	}


	/*=============================================
	OBTENER OPCIONES DE UNA VARIANTE
	=============================================*/

	static public function mdlObtenerOpcionesVariante($idVariante)
	{

		$stmt = Conexion::conectar()->prepare("

			SELECT ov.nombre, tv.nombre as tipo

			FROM productos_variantes_opciones pvo

			INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id

			INNER JOIN tipos_variantes tv ON ov.id_tipo_variante = tv.id

			WHERE pvo.id_producto_variante = :id_variante

			ORDER BY tv.orden ASC, ov.orden ASC

		");

		$stmt->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);

		$stmt->execute();

		return $stmt->fetchAll();

		$stmt->close();

		$stmt = null;

	}


	/*=============================================
	ACTUALIZAR ESTADO DE VARIANTE
	=============================================*/

	static public function mdlActualizarEstadoVariante($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET estado = :estado WHERE id = :id");

		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
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
	EDITAR VARIANTE
	=============================================*/

	static public function mdlEditarVariante($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET precio_adicional = :precio_adicional, stock = :stock WHERE id = :id");
		$stmt->bindParam(":precio_adicional", $datos["precio_adicional"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
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
	CONTAR VARIANTES DE UN PRODUCTO
	=============================================*/

	static public function mdlContarVariantesProducto($idProducto)
	{

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM productos_variantes WHERE id_producto = :id_producto");
		$stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
		$stmt->execute();
		$resultado = $stmt->fetch();
		//$stmt->close();
		$stmt = null;

		// Asegurar que siempre devuelva un número
		return $resultado && isset($resultado["total"]) ? (int) $resultado["total"] : 0;
	}


	/*============================================
	OBTENER VARIANTE POR ID
	=============================================*/

	static public function mdlObtenerVariantePorId($idVariante)
	{

		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos_variantes WHERE id = :id");
		$stmt->bindParam(":id", $idVariante, PDO::PARAM_INT);
		$stmt->execute();
		$resultado = $stmt->fetch();
		$stmt = null;
		return $resultado;
	}

	/*=============================================
   ACTUALIZAR STOCK DE VARIANTE
   =============================================*/

	static public function mdlActualizarStockVariante($tabla, $stock, $id)
	{


		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET stock = :stock WHERE id = :id");
		$stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			$stmt = null;
			return "ok";

		} else {
			$stmt = null;
			return "error";
		}

	}


	/*=============================================
	IMPORTAR Y ACTUALIZAR PRODUCTOS MASIVOS
	=============================================*/

	static public function mdlImportarProductosMasivos($tabla, $productosInsertar, $productosActualizar = array())
	{

		$conexion = Conexion::conectar();

		try {
			// Iniciar transacción
			$conexion->beginTransaction();

			$id_usuario_sesion = isset($_SESSION["id"]) ? $_SESSION["id"] : 1; // Fallback al admin
			$nombre_usuario_sesion = isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : "Sistema";

			$stmtHistorial = $conexion->prepare("INSERT INTO movimientos_stock (tipo_producto, id_producto, id_variante, nombre_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, id_usuario, nombre_usuario, referencia, notas) VALUES (:tipo_producto, :id_producto, :id_variante, :nombre_producto, :tipo_movimiento, :cantidad, :stock_anterior, :stock_nuevo, :id_usuario, :nombre_usuario, :referencia, :notas)");

			// 1. INSERCIONES DE PRODUCTOS NUEVOS
			if (count($productosInsertar) > 0) {
				$stmtInsert = $conexion->prepare("INSERT INTO $tabla(id_categoria, id_proveedor, codigo, descripcion, imagen, stock, precio_compra, precio_venta, ventas, unidad_medida_id, codigo_estandar_id, es_excluido, tributo_id, tasa_impuesto, notas_facturacion, scheme_id) VALUES (:id_categoria, :id_proveedor, :codigo, :descripcion, :imagen, :stock, :precio_compra, :precio_venta, :ventas, :unidad_medida_id, :codigo_estandar_id, :es_excluido, :tributo_id, :tasa_impuesto, :notas_facturacion, :scheme_id)");

				foreach ($productosInsertar as $producto) {

					$stmtInsert->bindParam(":id_categoria", $producto["id_categoria"], PDO::PARAM_INT);
					$stmtInsert->bindParam(":id_proveedor", $producto["id_proveedor"], PDO::PARAM_INT);
					$stmtInsert->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":descripcion", $producto["descripcion"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":imagen", $producto["imagen"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":stock", $producto["stock"], PDO::PARAM_INT);
					$stmtInsert->bindParam(":precio_compra", $producto["precio_compra"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":precio_venta", $producto["precio_venta"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":ventas", $producto["ventas"], PDO::PARAM_INT);
					
					// Nuevos campos de facturación
					$unidad_medida_id = isset($producto["unidad_medida_id"]) ? $producto["unidad_medida_id"] : 70; // 70 = Unidad
					$tributo_id = isset($producto["tributo_id"]) ? $producto["tributo_id"] : 1; // 1 = IVA
					$codigo_estandar_id = 999;
					$es_excluido = 0;
					$tasa_impuesto = "0.00";
					$notas_facturacion = "";
					$scheme_id = "999";

					$stmtInsert->bindParam(":unidad_medida_id", $unidad_medida_id, PDO::PARAM_INT);
					$stmtInsert->bindParam(":codigo_estandar_id", $codigo_estandar_id, PDO::PARAM_INT);
					$stmtInsert->bindParam(":es_excluido", $es_excluido, PDO::PARAM_INT);
					$stmtInsert->bindParam(":tributo_id", $tributo_id, PDO::PARAM_INT);
					$stmtInsert->bindParam(":tasa_impuesto", $tasa_impuesto, PDO::PARAM_STR);
					$stmtInsert->bindParam(":notas_facturacion", $notas_facturacion, PDO::PARAM_STR);
					$stmtInsert->bindParam(":scheme_id", $scheme_id, PDO::PARAM_STR);

					$stmtInsert->execute();

					$idNuevoProducto = $conexion->lastInsertId();

					// Registrar historial si tiene stock inicial
					if ($producto["stock"] > 0) {
						$tipo_movimiento = "creacion_producto";
						$tipo_producto = "producto";
						$id_variante = 0;
						$referencia = "Importación Excel/CSV";
						$notas = "Carga inicial masiva. (Código: " . $producto["codigo"] . ")";
						$stock_anterior = 0;

						$stmtHistorial->bindParam(":tipo_producto", $tipo_producto, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":id_producto", $idNuevoProducto, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":id_variante", $id_variante, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":nombre_producto", $producto["descripcion"], PDO::PARAM_STR);
						$stmtHistorial->bindParam(":tipo_movimiento", $tipo_movimiento, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":cantidad", $producto["stock"], PDO::PARAM_INT);
						$stmtHistorial->bindParam(":stock_anterior", $stock_anterior, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":stock_nuevo", $producto["stock"], PDO::PARAM_INT);
						$stmtHistorial->bindParam(":id_usuario", $id_usuario_sesion, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":nombre_usuario", $nombre_usuario_sesion, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":referencia", $referencia, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":notas", $notas, PDO::PARAM_STR);

						$stmtHistorial->execute();
					}
				}
			}

			// 2. ACTUALIZACIONES DE PRODUCTOS EXISTENTES
			if (count($productosActualizar) > 0) {
				$stmtUpdate = $conexion->prepare("UPDATE $tabla SET id_categoria = :id_categoria, id_proveedor = :id_proveedor, descripcion = :descripcion, stock = :stock, precio_compra = :precio_compra, precio_venta = :precio_venta, unidad_medida_id = :unidad_medida_id, tributo_id = :tributo_id WHERE id = :id");

				foreach ($productosActualizar as $producto) {
					$stmtUpdate->bindParam(":id_categoria", $producto["id_categoria"], PDO::PARAM_INT);
					$stmtUpdate->bindParam(":id_proveedor", $producto["id_proveedor"], PDO::PARAM_INT);
					$stmtUpdate->bindParam(":descripcion", $producto["descripcion"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":stock", $producto["stock"], PDO::PARAM_INT);
					$stmtUpdate->bindParam(":precio_compra", $producto["precio_compra"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":precio_venta", $producto["precio_venta"], PDO::PARAM_STR);
					
					$unidad_medida_id = isset($producto["unidad_medida_id"]) ? $producto["unidad_medida_id"] : 70;
					$tributo_id = isset($producto["tributo_id"]) ? $producto["tributo_id"] : 1;
					
					$stmtUpdate->bindParam(":unidad_medida_id", $unidad_medida_id, PDO::PARAM_INT);
					$stmtUpdate->bindParam(":tributo_id", $tributo_id, PDO::PARAM_INT);
					$stmtUpdate->bindParam(":id", $producto["id"], PDO::PARAM_INT);

					$stmtUpdate->execute();

					// Registrar historial solo si el stock cambió
					if ($producto["stock"] != $producto["stock_anterior"]) {
						$diferencia = $producto["stock"] - $producto["stock_anterior"];
						$tipo_movimiento = "edicion_stock";
						$tipo_producto = "producto";
						$id_variante = 0;
						$referencia = "Importación Excel/CSV";
						$notas = "Actualización de stock por carga masiva. (Código: " . $producto["codigo"] . ")";

						$stmtHistorial->bindParam(":tipo_producto", $tipo_producto, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":id_producto", $producto["id"], PDO::PARAM_INT);
						$stmtHistorial->bindParam(":id_variante", $id_variante, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":nombre_producto", $producto["descripcion"], PDO::PARAM_STR);
						$stmtHistorial->bindParam(":tipo_movimiento", $tipo_movimiento, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":cantidad", $diferencia, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":stock_anterior", $producto["stock_anterior"], PDO::PARAM_INT);
						$stmtHistorial->bindParam(":stock_nuevo", $producto["stock"], PDO::PARAM_INT);
						$stmtHistorial->bindParam(":id_usuario", $id_usuario_sesion, PDO::PARAM_INT);
						$stmtHistorial->bindParam(":nombre_usuario", $nombre_usuario_sesion, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":referencia", $referencia, PDO::PARAM_STR);
						$stmtHistorial->bindParam(":notas", $notas, PDO::PARAM_STR);

						$stmtHistorial->execute();
					}
				}
			}

			// Confirmar transacción
			$conexion->commit();

			$stmtInsert = null;
			$stmtUpdate = null;
			$stmtHistorial = null;
			return "ok";

		} catch (Exception $e) {

			// Revertir transacción en caso de error
			$conexion->rollBack();

			$stmtInsert = null;
			$stmtUpdate = null;
			$stmtHistorial = null;
			return "error";
		}
	}


}