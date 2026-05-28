<?php

class ControladorProductos
{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/

	static public function ctrMostrarProductos($item, $valor, $orden, $idBodega = null)
	{

		$tabla = "productos";

		if ($idBodega === null && isset($_SESSION["id_bodega"])) {
			$idBodega = $_SESSION["id_bodega"];
		}

		$respuesta = ModeloProductos::mdlMostrarProductos($tabla, $item, $valor, $orden, $idBodega);

		return $respuesta;
	}

	/*=============================================
	MOSTRAR PRODUCTOS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarProductosServerSide($params)
	{
		$tabla = "productos";

		// Columnas para ordenar (deben coincidir con el índice enviado por DataTables)
		$columnsMap = array(
			0 => 'p.id',
			1 => 'p.codigo',
			2 => 'p.descripcion',
			3 => 'c.categoria',
			4 => 'p.stock',
			5 => 't.nombre',
			6 => 'p.precio_venta',
			7 => 'prov.nombre',
			8 => 'p.fecha',
			9 => 'p.id'
		);

		$where = " WHERE 1=1 ";

		// Filtro de búsqueda (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (p.codigo LIKE '%$searchValue%' OR p.descripcion LIKE '%$searchValue%' OR c.categoria LIKE '%$searchValue%' OR p.stock LIKE '%$searchValue%' OR t.nombre LIKE '%$searchValue%' OR p.precio_venta LIKE '%$searchValue%' OR prov.nombre LIKE '%$searchValue%') ";
		}

		// Filtros personalizados (Categoría y Proveedor)
		if (!empty($params['categoriaFiltro'])) {
			$categoriaFiltro = $params['categoriaFiltro'];
			// Buscar ID de categoría por nombre
			$cat = ModeloCategorias::mdlMostrarCategorias("categorias", "categoria", $categoriaFiltro);
			if($cat){
				$where .= " AND p.id_categoria = " . $cat["id"];
			}
		}

		if (!empty($params['proveedorFiltro'])) {
			$proveedorFiltro = $params['proveedorFiltro'];
			// Buscar ID de proveedor por nombre
			$prov = ModeloProveedores::mdlMostrarProveedores("proveedores", "nombre", $proveedorFiltro);
			if($prov){
				$where .= " AND p.id_proveedor = " . $prov["id"];
			}
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'p.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY p.id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
		$productos = ModeloProductos::mdlMostrarProductosServerSide($tabla, $where, $order, $limit, $idBodegaActiva);
		$totalData = ModeloProductos::mdlGetTotalProductos($tabla, " WHERE 1=1 ", $idBodegaActiva);
		$totalFiltered = ModeloProductos::mdlGetTotalProductos($tabla, $where, $idBodegaActiva);

		// Obtener tributos para mapeo rápido de porcentajes (aunque ya tenemos el nombre por el JOIN)
		if (file_exists("modelos/factus.modelo.php")) {
			require_once "modelos/factus.modelo.php";
		} else {
			require_once "../modelos/factus.modelo.php";
		}
		$tributos = ModeloFactus::mdlObtenerTributos();
		$tributosMap = array();
		foreach ($tributos as $t) {
			$tributosMap[$t['id']] = $t;
		}

		$data = array();

		foreach ($productos as $key => $value) {
			
			$nestedData = array();

			// 0: ID
			$nestedData[] = $value["id"];

			// 1: Imagen
			$nestedData[] = $value["imagen"] ? $value["imagen"] : "vistas/img/productos/default/anonymous.png";

			// 2: Código
			$nestedData[] = e($value["codigo"]);

			// 3: Descripción
			$nestedData[] = e($value["descripcion"]);

			// 4: Categoría (Usando JOIN)
			$nestedData[] = !empty($value["nombre_categoria"]) ? e($value["nombre_categoria"]) : "Sin categoría";

			// 5: Stock (con badges)
			if ($value["stock"] <= 10) {
				$stockHtml = "<button class='btn btn-danger'>" . $value["stock"] . "</button>";
			} else if ($value["stock"] >= 11 && $value["stock"] <= 15) {
				$stockHtml = "<button class='btn btn-warning'>" . $value["stock"] . "</button>";
			} else {
				$stockHtml = "<button class='btn btn-success'>" . $value["stock"] . "</button>";
			}
			$nestedData[] = $stockHtml;

			// 6: Impuesto (Usando JOIN y el map para el porcentaje)
			$impuesto = "Sin Tributo";
			if (!empty($value["nombre_tributo"])) {
				$impuesto = $value["nombre_tributo"];
				if ($value["tributo_id"] && isset($tributosMap[$value["tributo_id"]])) {
					if (isset($tributosMap[$value["tributo_id"]]["porcentaje"])) {
						$impuesto .= " - " . $tributosMap[$value["tributo_id"]]["porcentaje"] . "%";
					}
				}
			}
			$nestedData[] = $impuesto;

			// 7: Precio Venta
			$nestedData[] = "$ " . number_format($value["precio_venta"]);

			// 8: Proveedor (Usando JOIN)
			$nestedData[] = !empty($value["nombre_proveedor"]) ? e($value["nombre_proveedor"]) : "Sin proveedor";

			// 9: Fecha
			$nestedData[] = $value["fecha"];

			// 10: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('productos', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarProducto" idProducto="' . $value["id"] . '" title="Editar Producto"><i class="fa fa-pencil"></i></button>';
				if ($value["tiene_variantes"] == 1) {
					$botonesAcciones .= '<button class="btn btn-primary btnAjusteStock" disabled title="No se puede ajustar de forma rápida: este producto tiene variantes"><i class="fa fa-cubes"></i></button>';
				} else {
					$botonesAcciones .= '<button class="btn btn-primary btnAjusteStock" idProducto="' . $value["id"] . '" data-toggle="modal" data-target="#modalAjusteStock" title="Ajustar Stock"><i class="fa fa-cubes"></i></button>';
				}
			}
			if (puedeAccion('productos', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarProducto" idProducto="' . $value["id"] . '" codigo="' . $value["codigo"] . '" imagen="' . $value["imagen"] . '" title="Eliminar Producto"><i class="fa fa-times"></i></button>';
			}
			if ($value["tiene_variantes"] == 1 && puedeAccion('variantes', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-info btnExpandirVariantes" data-id-producto="' . $value["id"] . '" title="Ver variantes"><i class="fa fa-plus"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

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

	/*=============================================
	MOSTRAR PRODUCTOS PARA VENTAS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarProductosVentasServerSide($params)
	{
		$tabla = "productos";

		// Columnas para ordenar (coincidentes con vistas/js/ventas.js)
		$columnsMap = array(
			0 => 'p.id',
			1 => 'p.id', // Imagen
			2 => 'p.codigo',
			3 => 'p.descripcion',
			4 => 'p.stock',
			5 => 'p.id'  // Acciones
		);

		$where = " WHERE 1=1 ";

		// Filtro de búsqueda (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (p.codigo LIKE '%$searchValue%' OR p.descripcion LIKE '%$searchValue%') ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'p.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY p.id DESC";
		}

		// Paginación
		$limit = "";
		if (isset($params['length']) && $params['length'] != -1) {
			$limit = " LIMIT " . intval($params['start']) . ", " . intval($params['length']);
		}

		// Obtener datos
		$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
		$productos = ModeloProductos::mdlMostrarProductosServerSide($tabla, $where, $order, $limit, $idBodegaActiva);
		$totalData = ModeloProductos::mdlGetTotalProductos($tabla, " WHERE 1=1 ", $idBodegaActiva);
		$totalFiltered = ModeloProductos::mdlGetTotalProductos($tabla, $where, $idBodegaActiva);

		$data = array();
		$start = isset($params['start']) ? intval($params['start']) : 0;

		foreach ($productos as $key => $value) {
			$nestedData = array();

			// 0: # (Índice visual)
			$nestedData[] = ($start + $key + 1);

			// 1: Imagen
			$nestedData[] = $value["imagen"] ? $value["imagen"] : "vistas/img/productos/default/anonymous.png";

			// 2: Código
			$nestedData[] = e($value["codigo"]);

			// 3: Descripción
			$nestedData[] = e($value["descripcion"]);

			// 4: Stock
			$nestedData[] = $value["stock"];

			// 5: ID (para botón de acciones)
			$nestedData[] = $value["id"];

			// 6: Tiene Variantes (Oculto)
			$nestedData[] = $value["tiene_variantes"] > 0 ? "1" : "0";

			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => isset($params['draw']) ? intval($params['draw']) : 0,
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		return $json_data;
	}


	/*=============================================
	CREAR PRODUCTO
	=============================================*/

	static public function ctrCrearProducto()
	{

		if (isset($_POST["nuevaDescripcion"])) {

			/*=============================================
			VALIDAR CSRF
			=============================================*/
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "productos";
					})
				</script>';
				return;
			}

			// Verificar si el producto tiene variantes

			$tieneVariantes = isset($_POST["tieneVariantes"]) ? 1 : 0;

			// Si tiene variantes, los campos de precio y stock pueden ser opcionales

			$validarStock = $tieneVariantes ? true : preg_match('/^[0-9]+$/', $_POST["nuevoStock"]);

			$validarPrecioCompra = $tieneVariantes ? true : preg_match('/^[0-9]+$/', $_POST["nuevoPrecioCompra"]);

			$validarPrecioVenta = $tieneVariantes ? true : preg_match('/^[0-9,.]+$/', $_POST["nuevoPrecioVenta"]);


			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaDescripcion"]) &&

				$validarStock && $validarPrecioCompra && $validarPrecioVenta
			) {


				/*=============================================
				VALIDAR QUE EL CÓDIGO NO EXISTA
				=============================================*/

				$tabla = "productos";
				$item = "codigo";
				$valor = $_POST["nuevoCodigo"];

				$codigoExistente = ModeloProductos::mdlMostrarProductos($tabla, $item, $valor, "id");

				if ($codigoExistente) {

					echo '<script> 

						swal({ 
							type: "error",
							title: "El código del producto ya existe",
							text: "Por favor ingrese un código diferente. El código ' . $_POST["nuevoCodigo"] . ' ya está siendo utilizado.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar" 

						}).then(() => {
							window.location = "productos";
						});
					</script>';
					return;
				}

				/*=============================================
				VALIDAR IMAGEN
				=============================================*/

				$ruta = "vistas/img/productos/default/anonymous.png";

				if (isset($_FILES["nuevaImagen"]["tmp_name"]) && !empty($_FILES["nuevaImagen"]["tmp_name"])) {

					list($ancho, $alto) = getimagesize($_FILES["nuevaImagen"]["tmp_name"]);

					$nuevoAncho = 500;

					$nuevoAlto = 500;

					$directorio = "vistas/img/productos/" . $_POST["nuevoCodigo"];

					mkdir($directorio, 0755);

					if ($_FILES["nuevaImagen"]["type"] == "image/jpeg") {

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/productos/" . $_POST["nuevoCodigo"] . "/" . $aleatorio . ".jpeg";

						$origen = imagecreatefromjpeg($_FILES["nuevaImagen"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagejpeg($destino, $ruta);

					}

					if ($_FILES["nuevaImagen"]["type"] == "image/png") {

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/productos/" . $_POST["nuevoCodigo"] . "/" . $aleatorio . ".png";

						$origen = imagecreatefrompng($_FILES["nuevaImagen"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagepng($destino, $ruta);


					}

				}

				$tabla = "productos";

				// Validar proveedor

				$idProveedor = $_POST["nuevoProveedor"];

				if (empty($idProveedor) || $idProveedor == "0" || $idProveedor == 0) {

					$idProveedor = null;

				}


				// Valores por defecto si están vacíos (para productos con variantes)

				$stock = !empty($_POST["nuevoStock"]) ? $_POST["nuevoStock"] : 0;

				$precioCompra = !empty($_POST["nuevoPrecioCompra"]) ? $_POST["nuevoPrecioCompra"] : 0;

				$precioVenta = !empty($_POST["nuevoPrecioVenta"]) ? $_POST["nuevoPrecioVenta"] : 0;

				$datos = array(

					"id_categoria" => $_POST["nuevaCategoria"],

					"codigo" => $_POST["nuevoCodigo"],

					"descripcion" => $_POST["nuevaDescripcion"],

					"stock" => $stock,

					"precio_compra" => $precioCompra,

					"precio_venta" => $precioVenta,

					"id_proveedor" => $idProveedor,

					"imagen" => $ruta,

					"tiene_variantes" => $tieneVariantes,

					// Campos de facturación electrónica DIAN (Factus)
					"unidad_medida_id" => isset($_POST["nuevaUnidadMedida"]) && !empty($_POST["nuevaUnidadMedida"]) ? $_POST["nuevaUnidadMedida"] : '94',
					"tributo_id" => isset($_POST["nuevoTributo"]) && !empty($_POST["nuevoTributo"]) ? $_POST["nuevoTributo"] : 1
				);


				$db = Conexion::conectar();

				try {
					$db->beginTransaction();

					// Si NO tiene variantes, usar el método normal
					if (!$tieneVariantes) {

						$respuesta = ModeloProductos::mdlIngresarProducto($tabla, $datos);
						if ($respuesta != "ok") {
							throw new Exception("Error al registrar el producto en la base de datos.");
						}

						// Obtener el ID del producto recién creado
						$productoCreado = ModeloProductos::mdlMostrarProductos($tabla, "codigo", $_POST["nuevoCodigo"], "id");
						if (!$productoCreado) {
							throw new Exception("Error al recuperar el ID del producto registrado.");
						}
						$idProducto = $productoCreado["id"];

						// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA
						$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
						$resStock = ModeloProductos::mdlActualizarStockBodega($idProducto, $idBodegaActiva, $stock);
						if ($resStock != "ok") {
							throw new Exception("Error al inicializar el stock en la bodega.");
						}

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE PRODUCTO
						if ($stock > 0) {
							$resMov = ControladorMovimientos::ctrRegistrarMovimiento(
								"producto",
								$idProducto,
								null,
								$_POST["nuevaDescripcion"],
								"creacion_producto",
								$stock,
								0,
								$stock,
								"Producto creado con stock inicial",
								""
							);
							if ($resMov != "ok") {
								throw new Exception("Error al registrar el movimiento de stock inicial.");
							}
						}

						/*=============================================
						CALCULAR STOCK AUTOMÁTICO DEL PRODUCTO BASE
						=============================================*/
						if (isset($_POST["totalCombinaciones"]) && $_POST["totalCombinaciones"] > 0) {
							// Calcular la suma del stock de todas las variantes
							$stmt = Conexion::conectar()->prepare("SELECT SUM(stock) as stock_total FROM productos_variantes WHERE id_producto = :id_producto AND estado = 1");
							$stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
							$stmt->execute();
							$resultado = $stmt->fetch();
							$stmt = null;

							$stockTotal = $resultado["stock_total"] ? $resultado["stock_total"] : 0;

							// Actualizar el stock del producto base
							$resActBase = ModeloProductos::mdlActualizarProducto($tabla, "stock", $stockTotal, $idProducto);
							if ($resActBase != "ok") {
								throw new Exception("Error al actualizar el stock calculado del producto.");
							}
						}

					} else {

						// SI tiene variantes, usar el nuevo método que retorna ID
						$idProducto = ModeloProductos::mdlIngresarProductoConVariantes($tabla, $datos);

						if (!$idProducto) {
							throw new Exception("Error al registrar el producto con variantes.");
						}

						// Procesar variantes
						$totalCombinaciones = isset($_POST["totalCombinaciones"]) ? $_POST["totalCombinaciones"] : 0;
						$variantesCreadas = 0;

						for ($i = 0; $i < $totalCombinaciones; $i++) {

							// Verificar si existe la combinación
							if (isset($_POST["combinacion_" . $i . "_ids"])) {

								$idsCombinacion = $_POST["combinacion_" . $i . "_ids"];
								$nombreCombinacion = $_POST["combinacion_" . $i . "_nombre"];

								// Obtener precio adicional y stock de la variante
								$precioAdicional = isset($_POST["precioAdicional_" . $idsCombinacion]) && $_POST["precioAdicional_" . $idsCombinacion] !== ""
									? $_POST["precioAdicional_" . $idsCombinacion]
									: 0;

								$stockVariante = isset($_POST["stockVariante_" . $idsCombinacion]) && $_POST["stockVariante_" . $idsCombinacion] !== ""
									? $_POST["stockVariante_" . $idsCombinacion]
									: $stock;

								// Generar SKU
								$idsOpcionesArray = explode("_", $idsCombinacion);
								$sku = ModeloProductos::mdlGenerarSKU($_POST["nuevoCodigo"], $idsOpcionesArray);

								// Datos de la variante
								$datosVariante = array(
									"id_producto" => $idProducto,
									"sku" => $sku,
									"precio_adicional" => $precioAdicional,
									"stock" => $stockVariante,
									"imagen" => $ruta,
									"estado" => 1
								);

								// Guardar variante
								$idVariante = ModeloProductos::mdlGuardarVariante($datosVariante);

								if (!$idVariante) {
									throw new Exception("Error al guardar la variante de producto.");
								}

								// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA
								$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
								$resStockVar = ModeloProductos::mdlActualizarStockVarianteBodega($idVariante, $idBodegaActiva, $stockVariante);
								if ($resStockVar != "ok") {
									throw new Exception("Error al asignar stock inicial de variante en bodega.");
								}

								// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE VARIANTE
								if ($stockVariante > 0) {
									$resMovVar = ControladorMovimientos::ctrRegistrarMovimiento(
										"variante",
										$idProducto,
										$idVariante,
										$_POST["nuevaDescripcion"] . " - " . $nombreCombinacion,
										"creacion_variante",
										$stockVariante,
										0,
										$stockVariante,
										"Variante creada con stock inicial: " . $nombreCombinacion,
										""
									);
									if ($resMovVar != "ok") {
										throw new Exception("Error al registrar movimiento de stock de la variante.");
									}
								}

								// Relacionar variante con sus opciones
								foreach ($idsOpcionesArray as $idOpcion) {
									$datosRelacion = array(
										"id_producto_variante" => $idVariante,
										"id_opcion_variante" => $idOpcion
									);

									$resRel = ModeloProductos::mdlGuardarVarianteOpcion($datosRelacion);
									if ($resRel != "ok") {
										throw new Exception("Error al relacionar la variante con su opción.");
									}
								}

								$variantesCreadas++;
							}
						}

						/*=============================================
						CALCULAR STOCK AUTOMÁTICO DEL PRODUCTO BASE
						=============================================*/
						// 1. Sincronizar stock del producto base por bodega (en productos_bodegas)
						$stmtBodegas = Conexion::conectar()->prepare("
							SELECT id_bodega, SUM(pvb.stock) as stock_bodega 
							FROM productos_variantes_bodegas pvb
							INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id
							WHERE pv.id_producto = :id_producto AND pv.estado = 1
							GROUP BY id_bodega
						");
						$stmtBodegas->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
						$stmtBodegas->execute();
						$resultadosBodegas = $stmtBodegas->fetchAll();
						$stmtBodegas = null;

						foreach ($resultadosBodegas as $rowBodega) {
							$resActStockB = ModeloProductos::mdlActualizarStockBodega($idProducto, $rowBodega["id_bodega"], $rowBodega["stock_bodega"]);
							if ($resActStockB != "ok") {
								throw new Exception("Error al sincronizar el stock por bodega del producto base.");
							}
						}

						// 2. Calcular la suma del stock global (todas las variantes)
						$stmt = Conexion::conectar()->prepare("SELECT SUM(stock) as stock_total FROM productos_variantes WHERE id_producto = :id_producto AND estado = 1");
						$stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
						$stmt->execute();
						$resultado = $stmt->fetch();
						$stmt = null;

						$stockTotal = $resultado["stock_total"] ? $resultado["stock_total"] : 0;

						// Actualizar el stock global del producto base
						$resActProdB = ModeloProductos::mdlActualizarProducto($tabla, "stock", $stockTotal, $idProducto);
						if ($resActProdB != "ok") {
							throw new Exception("Error al actualizar el stock global del producto base.");
						}
					}

					$db->commit();

					$tituloSuccess = $tieneVariantes ? "¡Producto guardado!" : "¡El producto ha sido guardado correctamente!";
					$textSuccess = $tieneVariantes ? "Se crearon " . $variantesCreadas . " variantes correctamente" : "";

					echo '<script>
						swal({
							type: "success",
							title: "' . $tituloSuccess . '",
							text: "' . $textSuccess . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "productos";
						})
					</script>';

				} catch (Exception $e) {
					$db->rollBack();
					Logger::error("Error al crear producto: " . $e->getMessage());

					echo '<script>
						swal({
							type: "error",
							title: "Error al guardar el producto",
							text: "' . addslashes($e->getMessage()) . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							// window.location = "productos";
						})
					</script>';
				}
			} else {

				echo '<script>

					swal({
						type: "error",
						title: "¡El producto no puede ir con los campos vacíos o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						// window.location = "productos";
					})




				</script>';
			}

		}

	}


	/*==========================================================================================
	EDITAR PRODUCTO
	==========================================================================================*/

	static public function ctrEditarProducto()
	{

		
			file_put_contents("debug_post.txt", "=== CTR EDITAR (ID: " . (isset($_POST['idProducto']) ? $_POST['idProducto'] : 'none') . ") ===\n" . print_r($_POST, true) . "\n", FILE_APPEND);
if (isset($_POST["editarDescripcion"])) {

			/*=============================================
			VALIDAR CSRF
			=============================================*/
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "productos";
					})
				</script>';
				return;
			}

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDescripcion"]) &&
				preg_match('/^[0-9]+$/', $_POST["editarStock"]) &&
				preg_match('/^[0-9]+$/', $_POST["editarPrecioCompra"]) &&
				preg_match('/^[0-9,.]+$/', $_POST["editarPrecioVenta"])
			) {


				/*=============================================
				VALIDAR IMAGEN
				=============================================*/

				$ruta = $_POST["imagenActual"];

				if (isset($_FILES["editarImagen"]["tmp_name"]) && !empty($_FILES["editarImagen"]["tmp_name"])) {

					list($ancho, $alto) = getimagesize($_FILES["editarImagen"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;

					//CREAMOS DIRECTORIO DE LAS FOTOS DEL USUARIO

					$directorio = "vistas/img/productos/" . $_POST["editarCodigo"];

					//PRIMERO PREGUNTAMOS SI EXISTE OTRA IMAGEN EN LA BD

					if (!empty($_POST["imagenActual"]) && $_POST["imagenActual"] != "vistas/img/productos/default/anonymous.png") {

						unlink($_POST["imagenActual"]);
					} else {

						mkdir($directorio, 0755);
					}


					//DE A CUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES PHP, 1ro EN JPEG

					if ($_FILES["editarImagen"]["type"] == "image/jpeg") {

						//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/productos/" . $_POST["editarCodigo"] . "/" . $aleatorio . ".jpeg";

						$origen = imagecreatefromjpeg($_FILES["editarImagen"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagejpeg($destino, $ruta);

					}

					//FUNCIONES PARA PNG

					if ($_FILES["editarImagen"]["type"] == "image/png") {

						//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/productos/" . $_POST["editarCodigo"] . "/" . $aleatorio . ".png";

						$origen = imagecreatefrompng($_FILES["editarImagen"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagepng($destino, $ruta);

					}
				}


				$db = Conexion::conectar();

				try {
					$db->beginTransaction();

					$idProveedor = $_POST["editarProveedor"];

					// Si viene vacío, "0" o es 0, convertirlo a NULL
					if (empty($idProveedor) || $idProveedor == "0" || $idProveedor == 0) {
						$idProveedor = null;
					}

					$tabla = "productos";

					// 🔹 OBTENER STOCK ANTERIOR antes de editar
					// Buscar por ID si existe, sino por código
					if (isset($_POST["idProducto"]) && !empty($_POST["idProducto"])) {
						$productoAnterior = ModeloProductos::mdlMostrarProductos($tabla, "id", $_POST["idProducto"], "id");
					} else {
						$productoAnterior = ModeloProductos::mdlMostrarProductos($tabla, "codigo", $_POST["editarCodigo"], "id");
					}

					// Validar que el producto existe
					if (!$productoAnterior) {
						throw new Exception("No se pudo encontrar el producto en la base de datos.");
					}

					$stockAnterior = $productoAnterior["stock"];
					$nuevoStock = $_POST["editarStock"];

					// Fallback de compatibilidad de variables JS
					if (!isset($_POST["totalCombinacionesEditar"]) && isset($_POST["totalCombinaciones"])) {
						$_POST["totalCombinacionesEditar"] = $_POST["totalCombinaciones"];
					}

					$datos = array(
						"id" => isset($_POST["idProducto"]) ? $_POST["idProducto"] : null,
						"id_categoria" => $_POST["editarCategoria"],
						"tiene_variantes" => (isset($_POST["totalCombinacionesEditar"]) && $_POST["totalCombinacionesEditar"] > 0) ? 1 : $productoAnterior["tiene_variantes"],
						"codigo" => $_POST["editarCodigo"],
						"descripcion" => $_POST["editarDescripcion"],
						"stock" => $nuevoStock,
						"precio_compra" => $_POST["editarPrecioCompra"],
						"precio_venta" => $_POST["editarPrecioVenta"],
						"id_proveedor" => $idProveedor,
						"imagen" => $ruta,
						// Campos de facturación electrónica DIAN (Factus)
						"unidad_medida_id" => isset($_POST["editarUnidadMedida"]) && !empty($_POST["editarUnidadMedida"]) ? $_POST["editarUnidadMedida"] : 94,
						"codigo_estandar_id" => isset($_POST["editarCodigoEstandar"]) && !empty($_POST["editarCodigoEstandar"]) ? $_POST["editarCodigoEstandar"] : 999,
						"es_excluido" => isset($_POST["editarEsExcluido"]) ? 1 : 0,
						"tributo_id" => isset($_POST["editarTributo"]) && !empty($_POST["editarTributo"]) ? $_POST["editarTributo"] : 1,
						"tasa_impuesto" => isset($_POST["editarTasaImpuesto"]) && !empty($_POST["editarTasaImpuesto"]) ? $_POST["editarTasaImpuesto"] : '0.00',
						"notas_facturacion" => isset($_POST["editarNotasFacturacion"]) ? $_POST["editarNotasFacturacion"] : '',
						"scheme_id" => isset($_POST["editarSchemeId"]) && !empty($_POST["editarSchemeId"]) ? $_POST["editarSchemeId"] : '999'
					);

					$respuesta = ModeloProductos::mdlEditarProducto($tabla, $datos);

					if ($respuesta != "ok") {
						throw new Exception("Error al actualizar la información del producto.");
					}

					// 📦 ACTUALIZAR STOCK EN BODEGA ACTIVA (Para productos simples)
					$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
					$idProductoReal = isset($_POST["idProducto"]) ? $_POST["idProducto"] : $productoAnterior["id"];
					$tieneVariantes = $productoAnterior["tiene_variantes"];
					if(isset($_POST["form_detalle_producto"])){
						$tieneVariantes = isset($_POST["tieneVariantes"]) ? 1 : 0;
					}
					if ($tieneVariantes == 0) {
						$resStockB = ModeloProductos::mdlActualizarStockBodega($idProductoReal, $idBodegaActiva, $nuevoStock);
						if ($resStockB != "ok") {
							throw new Exception("Error al actualizar el stock del producto en la bodega.");
						}
						// Recalcular stock global
						$stmtSumBodegas = Conexion::conectar()->prepare("SELECT SUM(pb.stock) as total FROM productos_bodegas pb WHERE pb.id_producto = :id_producto");
						$stmtSumBodegas->bindParam(":id_producto", $idProductoReal, PDO::PARAM_INT);
						$stmtSumBodegas->execute();
						$resSum = $stmtSumBodegas->fetch();
						$stockGlobal = ($resSum && $resSum["total"]) ? $resSum["total"] : $nuevoStock;
						$stmtSumBodegas = null;
						
						$resActProd = ModeloProductos::mdlActualizarProducto("productos", "stock", $stockGlobal, $idProductoReal);
						if ($resActProd != "ok") {
							throw new Exception("Error al actualizar el stock global del producto.");
						}
					}
					// 🟢 REGISTRAR MOVIMIENTO DE STOCK - EDICIÓN DE PRODUCTO
					if ($stockAnterior != $nuevoStock && $productoAnterior && isset($productoAnterior["id"])) {
						$diferencia = $nuevoStock - $stockAnterior;
						$resMov = ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$productoAnterior["id"],
							null,
							$_POST["editarDescripcion"],
							"edicion_stock",
							$diferencia,
							$stockAnterior,
							$nuevoStock,
							"Stock editado manualmente",
							""
						);
						if ($resMov != "ok") {
							throw new Exception("Error al registrar el movimiento de ajuste de stock.");
						}
					}

					/*=============================================
					PROCESAR NUEVAS VARIANTES (si se agregaron desde editar)
					=============================================*/
					$totalCombinacionesPost = isset($_POST["totalCombinacionesEditar"]) ? $_POST["totalCombinacionesEditar"] : (isset($_POST["totalCombinaciones"]) ? $_POST["totalCombinaciones"] : 0);
					if ($totalCombinacionesPost > 0) {
						$_POST["totalCombinacionesEditar"] = $totalCombinacionesPost; // Para mantener compatibilidad con el resto del codigo

						// DEBUG: Log de inicio
						file_put_contents("debug_editar_variantes.txt", "=== EDITAR PRODUCTO CON VARIANTES ===\n", FILE_APPEND);
						file_put_contents("debug_editar_variantes.txt", "Total combinaciones: " . $_POST["totalCombinacionesEditar"] . "\n", FILE_APPEND);

						// Obtener el ID real del producto de forma directa y segura
						$idProductoReal = isset($_POST["idProducto"]) && !empty($_POST["idProducto"]) ? $_POST["idProducto"] : null;
						if(!$idProductoReal) {
							$idProducto = $_POST["editarCodigo"];
							$productoBase = ModeloProductos::mdlMostrarProductos("productos", "codigo", $idProducto, "id");
							if (!$productoBase) {
								throw new Exception("Error al recuperar el producto base para variante.");
							}
							$idProductoReal = $productoBase["id"];
						}

						file_put_contents("debug_editar_variantes.txt", "ID Producto: " . $idProductoReal . "\n", FILE_APPEND);
						$totalCombinaciones = $_POST["totalCombinacionesEditar"];
						$tablaVariantes = "productos_variantes";

						for ($i = 0; $i < $totalCombinaciones; $i++) {

							file_put_contents("debug_editar_variantes.txt", "\n--- Procesando combinación $i ---\n", FILE_APPEND);

							// Verificar si esta combinación está seleccionada
							$prefixComb = isset($_POST["combinacionEditar_" . $i . "_ids"]) ? "combinacionEditar_" : "combinacion_";
							$prefixPrecio = isset($_POST["precioAdicionalEditar_" . $_POST[$prefixComb . $i . "_ids"]]) ? "precioAdicionalEditar_" : "precioAdicional_";
							$prefixStock = isset($_POST["stockVarianteEditar_" . $_POST[$prefixComb . $i . "_ids"]]) ? "stockVarianteEditar_" : "stockVariante_";

							if (isset($_POST[$prefixComb . $i . "_ids"]) && isset($_POST[$prefixComb . $i . "_nombre"])) {

								$idsCombinacion = $_POST[$prefixComb . $i . "_ids"];
								$nombreCombinacion = $_POST[$prefixComb . $i . "_nombre"];

								file_put_contents("debug_editar_variantes.txt", "IDs Combinación: $idsCombinacion\n", FILE_APPEND);
								file_put_contents("debug_editar_variantes.txt", "Nombre: $nombreCombinacion\n", FILE_APPEND);

								// Obtener precio adicional y stock de esta combinación
								$precioAdicional = isset($_POST[$prefixPrecio . $idsCombinacion]) && $_POST[$prefixPrecio . $idsCombinacion] !== ""
									? $_POST[$prefixPrecio . $idsCombinacion]
									: 0;

								$stockVariante = isset($_POST[$prefixStock . $idsCombinacion]) && $_POST[$prefixStock . $idsCombinacion] !== ""
									? $_POST[$prefixStock . $idsCombinacion]
									: 0;

								file_put_contents("debug_editar_variantes.txt", "Precio Adicional: $precioAdicional\n", FILE_APPEND);
								file_put_contents("debug_editar_variantes.txt", "Stock: $stockVariante\n", FILE_APPEND);

								// Verificar si la variante ya existe (viene el ID de variante existente)
								if (isset($_POST["idVarianteExistente_" . $idsCombinacion]) && !empty($_POST["idVarianteExistente_" . $idsCombinacion])) {

									// ACTUALIZAR variante existente
									$idVarianteExistente = $_POST["idVarianteExistente_" . $idsCombinacion];
									$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;

									// 🔹 OBTENER STOCK ANTERIOR de la variante en la BODEGA ACTIVA para registrar movimiento
									$stmtVarianteAntes = Conexion::conectar()->prepare("SELECT stock FROM productos_variantes_bodegas WHERE id_variante = :id AND id_bodega = :id_bodega");
									$stmtVarianteAntes->bindParam(":id", $idVarianteExistente, PDO::PARAM_INT);
									$stmtVarianteAntes->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
									$stmtVarianteAntes->execute();
									$varianteAntes = $stmtVarianteAntes->fetch();
									$stockAnteriorVariante = $varianteAntes ? $varianteAntes["stock"] : 0;
									$stmtVarianteAntes = null;

									file_put_contents("debug_editar_variantes.txt", ">>> UPDATE variante existente ID: $idVarianteExistente en bodega $idBodegaActiva\n", FILE_APPEND);

									$datosActualizar = array(
										"id" => $idVarianteExistente,
										"precio_adicional" => $precioAdicional,
										"stock" => $stockVariante
									);

									$resultado = ModeloProductos::mdlEditarVariante($tablaVariantes, $datosActualizar);
									if ($resultado !== "ok") {
										throw new Exception("Error al actualizar la variante ID: " . $idVarianteExistente);
									}

									// 📦 ACTUALIZAR STOCK EN LA BODEGA ACTIVA DIRECTAMENTE
									$resStockV = ModeloProductos::mdlActualizarStockVarianteBodega($idVarianteExistente, $idBodegaActiva, $stockVariante);
									if ($resStockV !== "ok") {
										throw new Exception("Error al actualizar el stock de la variante en la bodega.");
									}

									// 🔹 RECALCULAR STOCK TOTAL DE LA VARIANTE (Suma de todas las bodegas)
									$stmtTotalVar = Conexion::conectar()->prepare("SELECT SUM(stock) as total FROM productos_variantes_bodegas WHERE id_variante = :id");
									$stmtTotalVar->bindParam(":id", $idVarianteExistente, PDO::PARAM_INT);
									$stmtTotalVar->execute();
									$resTotalVar = $stmtTotalVar->fetch();
									$stockTotalVariante = $resTotalVar["total"] ? $resTotalVar["total"] : 0;
									$stmtTotalVar = null;

									$resActP = ModeloProductos::mdlActualizarProducto("productos_variantes", "stock", $stockTotalVariante, $idVarianteExistente);
									if ($resActP !== "ok") {
										throw new Exception("Error al actualizar el stock global de la variante.");
									}

									// 🟢 REGISTRAR MOVIMIENTO DE STOCK - EDICIÓN DE VARIANTE EXISTENTE EN LA BODEGA
									if ($stockAnteriorVariante != $stockVariante) {
										$diferenciaStock = $stockVariante - $stockAnteriorVariante;
										$resMovV = ControladorMovimientos::ctrRegistrarMovimiento(
											"variante",
											$idProductoReal,
											$idVarianteExistente,
											$_POST["editarDescripcion"] . " - " . $nombreCombinacion,
											"edicion_stock",
											$diferenciaStock,
											$stockAnteriorVariante,
											$stockVariante,
											"Stock de variante actualizado",
											"",
											$idBodegaActiva
										);
										if ($resMovV !== "ok") {
											throw new Exception("Error al registrar movimiento de stock de la variante.");
										}
									}

								} else {
									// CREAR nueva variante
									$skuBase = $_POST["editarCodigo"];
									$skuVariante = $skuBase . "_" . $idsCombinacion;

									$datosVariante = array(
										"id_producto" => $idProductoReal,
										"sku" => $skuVariante,
										"precio_adicional" => $precioAdicional,
										"stock" => $stockVariante,
										"imagen" => "",
										"estado" => 1
									);

									// Guardar variante y obtener su ID
									$idVarianteNueva = ModeloProductos::mdlGuardarVariante($datosVariante);
									if (!$idVarianteNueva) {
										throw new Exception("Error al insertar la nueva variante.");
									}

									// 📦 ASIGNAR STOCK INICIAL A BODEGA ACTIVA
									$idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
									$resStockVNew = ModeloProductos::mdlActualizarStockVarianteBodega($idVarianteNueva, $idBodegaActiva, $stockVariante);
									if ($resStockVNew !== "ok") {
										throw new Exception("Error al asignar stock inicial a la nueva variante.");
									}

									// 🟢 REGISTRAR MOVIMIENTO DE STOCK - CREACIÓN DE VARIANTE
									if ($stockVariante > 0) {
										$resMovVNew = ControladorMovimientos::ctrRegistrarMovimiento(
											"variante",
											$idProductoReal,
											$idVarianteNueva,
											$_POST["editarDescripcion"] . " - " . $nombreCombinacion,
											"creacion_variante",
											$stockVariante,
											0,
											$stockVariante,
											"Variante creada con stock inicial: " . $nombreCombinacion,
											"",
											$idBodegaActiva
										);
										if ($resMovVNew !== "ok") {
											throw new Exception("Error al registrar el movimiento inicial de la variante.");
										}
									}

									// Guardar las opciones de la variante
									$opcionesArray = explode("_", $idsCombinacion);
									foreach ($opcionesArray as $idOpcion) {
										$datosOpcion = array(
											"id_producto_variante" => $idVarianteNueva,
											"id_opcion_variante" => $idOpcion
										);

										$resRelVarOpt = ModeloProductos::mdlGuardarVarianteOpcion($datosOpcion);
										if ($resRelVarOpt !== "ok") {
											throw new Exception("Error al relacionar variante con opción.");
										}
									}
								}
							}
						}
					}

					/*=============================================
					RECALCULAR STOCK AUTOMÁTICO DEL PRODUCTO BASE
					=============================================*/
					$totalCombinacionesPost = isset($_POST["totalCombinacionesEditar"]) ? $_POST["totalCombinacionesEditar"] : (isset($_POST["totalCombinaciones"]) ? $_POST["totalCombinaciones"] : 0);
					if ($totalCombinacionesPost > 0) {
						$_POST["totalCombinacionesEditar"] = $totalCombinacionesPost; // Para mantener compatibilidad con el resto del codigo

						// 1. Sincronizar stock del producto base por bodega (en productos_bodegas)
						$stmtBodegas = Conexion::conectar()->prepare("
							SELECT id_bodega, SUM(pvb.stock) as stock_bodega 
							FROM productos_variantes_bodegas pvb
							INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id
							WHERE pv.id_producto = :id_producto AND pv.estado = 1
							GROUP BY id_bodega
						");
						$stmtBodegas->bindParam(":id_producto", $idProductoReal, PDO::PARAM_INT);
						$stmtBodegas->execute();
						$resultadosBodegas = $stmtBodegas->fetchAll();
						$stmtBodegas = null;

						foreach ($resultadosBodegas as $rowBodega) {
							$resActStockB = ModeloProductos::mdlActualizarStockBodega($idProductoReal, $rowBodega["id_bodega"], $rowBodega["stock_bodega"]);
							if ($resActStockB !== "ok") {
								throw new Exception("Error al sincronizar el stock por bodega del producto base.");
							}
						}

						// 2. Calcular la suma del stock global (todas las variantes activas)
						$stmt = Conexion::conectar()->prepare("SELECT SUM(stock) as stock_total FROM productos_variantes WHERE id_producto = :id_producto AND estado = 1");
						$stmt->bindParam(":id_producto", $idProductoReal, PDO::PARAM_INT);
						$stmt->execute();
						$resultado = $stmt->fetch();
						$stmt = null;

						$stockTotal = $resultado["stock_total"] ? $resultado["stock_total"] : 0;

						// Actualizar el stock global del producto base
						$resActProdB = ModeloProductos::mdlActualizarProducto("productos", "stock", $stockTotal, $idProductoReal);
						if ($resActProdB !== "ok") {
							throw new Exception("Error al actualizar el stock global del producto base.");
						}
					}

					$db->commit();

					echo '<script>
					swal({
						type: "success",
						title: "¡El producto ha sido editado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						}).then(() => {
							window.location = "productos";
						})
			     	</script>';

				} catch (Exception $e) {
					$db->rollBack();
					Logger::error("Error al editar producto ID " . (isset($idProductoReal) ? $idProductoReal : "desconocido") . ": " . $e->getMessage());

					echo '<script>
						swal({
							type: "error",
							title: "Error al guardar el producto",
							text: "' . addslashes($e->getMessage()) . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							// window.location = "productos";
						})
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El producto no puede ir con los campos vacíos o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {
							// window.location = "productos";
						})
				</script>';
			}

		}

	}


	/*=============================================
	ELIMINAR PRODUCTO
	=============================================*/
	static public function ctrEliminarProducto()
	{

		if (isset($_GET["idProducto"]) || isset($_POST["idProductoEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idProductoEliminar"])) {
					return "error_csrf";
				}
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "productos";
					})
				</script>';
				return;
			}

			$tabla = "productos";
			$idProducto = isset($_GET["idProducto"]) ? $_GET["idProducto"] : $_POST["idProductoEliminar"];

			// Obtener información del producto desde la DB para validar rutas
			$producto = ModeloProductos::mdlMostrarProductos($tabla, "id", $idProducto, null);

			if (!$producto) {
				if (isset($_POST["idProductoEliminar"])) {
					return "error_no_existe";
				}
				return;
			}

			$imagen = $producto["imagen"];
			$codigo = $producto["codigo"];

			// SIEMPRE usar la bodega activa de sesión — NUNCA hacer DELETE global desde aquí.
			// La eliminación de un producto siempre es un soft-delete (estado=0) en la bodega activa.
			$idBodega = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;

			// Log temporal de diagnóstico
			file_put_contents("debug_eliminar_producto.txt",
				date('Y-m-d H:i:s') . " | IdProducto=$idProducto | IdBodega=$idBodega | Method=" . $_SERVER['REQUEST_METHOD'] . "\n",
				FILE_APPEND);

			// NOTA: No borramos imagen/directorio porque la eliminación es siempre por bodega (soft-delete).
			// El producto sigue existiendo en otras bodegas y necesita su imagen.

			$db = Conexion::conectar();
			try {
				$db->beginTransaction();

				$respuesta = ModeloProductos::mdlEliminarProducto($tabla, $idProducto, $idBodega);
				if ($respuesta != "ok") {
					throw new Exception("Error al desactivar el producto en la bodega.");
				}

				$db->commit();
			} catch (Exception $e) {
				$db->rollBack();
				Logger::error("Error al eliminar producto: " . $e->getMessage());
				$respuesta = "error";
			}

			if ($respuesta == "ok") {
				if (isset($_POST["idProductoEliminar"])) {
					return "ok";
				}
				$titulo = $idBodega !== null
					? "¡Producto eliminado de esta sucursal!"
					: "¡El producto ha sido borrado correctamente!";
				$texto = $idBodega !== null
					? "El producto sigue disponible en las demás sucursales."
					: "";
				echo '<script>
					swal({
						type: "success",
						title: "' . $titulo . '",
						text: "' . $texto . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						}).then(() => {
							window.location = "productos";
						})
			     	</script>';
			}
		}
	}


	/*=============================================
	MOSTRAR SUMA VENTAS
	=============================================*/

	static public function ctrMostrarSumaVentas()
	{

		$tabla = "productos";

		$respuesta = ModeloProductos::mdlMostrarSumaVentas($tabla);

		return $respuesta;

	}


	/*=============================================
	Actualizar Imagen Producto desde DataTable
	=============================================*/
	public static function ctrActualizarImagenProducto($idProducto, $rutaImagen)
	{
		$tabla = "productos";
		return ModeloProductos::mdlActualizarImagenProducto($tabla, $idProducto, $rutaImagen);
	}


	/*=============================================
	IMPORTAR PRODUCTOS DESDE CSV
	=============================================*/

	static public function ctrImportarProductos()
	{

		if (isset($_FILES["archivoCSV"])) {

			$idBodegaActiva = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;
			$archivo = $_FILES["archivoCSV"]["tmp_name"];
			$errores = array();
			$productosImportar = array();
			$productosActualizar = array();

			// Abrir archivo CSV
			if (($handle = fopen($archivo, "r")) !== FALSE) {


				// Saltar BOM si existe
				$bom = fread($handle, 3);
				if ($bom != "\xEF\xBB\xBF") {
					rewind($handle);
				}

				// DETECTAR DELIMITADOR AUTOMÁTICAMENTE
				$primeraLinea = trim(fgets($handle));
				rewind($handle);

				// Saltar BOM nuevamente después de rewind
				$bom = fread($handle, 3);
				if ($bom != "\xEF\xBB\xBF") {
					rewind($handle);
				}

				// Verificar si la primera línea es un indicador de separador de Excel (sep=;)
				$delimitador = ";"; // Predeterminado
				if (strpos($primeraLinea, 'sep=') === 0) {
					$delimitador = substr($primeraLinea, 4, 1);
					// Consumir la línea del separador para que no se lea como encabezado
					fgets($handle); 
				} else {
					// Contar delimitadores en la primera línea si no hay 'sep='
					$contadorComa = substr_count($primeraLinea, ',');
					$contadorPuntoYComa = substr_count($primeraLinea, ';');
					$delimitador = ($contadorPuntoYComa > $contadorComa) ? ';' : ',';
				}

				// Leer encabezados
				$encabezados = fgetcsv($handle, 1000, $delimitador);

				$numeroFila = 1; // Contador de fila (empieza en 1 para los datos, 0 es el encabezado) 

				// Leer cada línea del CSV
				while (($datos = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
					$numeroFila++;

					// Saltar filas vacías
					if (empty(array_filter($datos))) {
						continue;
					}

					// Validar que la fila tenga 9 columnas
					if (count($datos) < 9) {

						$errores[] = "Fila $numeroFila: Faltan columnas (se requieren 9, encontradas " . count($datos) . ")";
						continue;
					}

					$codigo = trim($datos[0]);
					$descripcion = trim($datos[1]);
					$categoria = trim($datos[2]);
					$proveedor = trim($datos[3]);
					$stock = trim($datos[4]);
					$precioCompra = trim($datos[5]);
					$precioVenta = trim($datos[6]);
					$unidadMedida = trim($datos[7]);
					$tributo = trim($datos[8]);

					// Validar campos obligatorios (proveedor es OPCIONAL)

					if (empty($codigo) || empty($descripcion) || empty($categoria)) {
						$errores[] = "Fila $numeroFila: Campos obligatorios vacíos (código, descripción, categoría)";
						continue;
					}

					// Validar números
					if (!is_numeric($stock) || !is_numeric($precioCompra) || !is_numeric($precioVenta)) {
						$errores[] = "Fila $numeroFila: Stock y precios deben ser números";
						continue;
					}

					// Normalizar y buscar categoría
					$categoriaNormalizada = self::normalizarTexto($categoria);
					$categoriaEncontrada = self::buscarCategoriaPorNombre($categoriaNormalizada);

					if (!$categoriaEncontrada) {
						$errores[] = "Fila $numeroFila: La categoría '$categoria' no existe o no coincide";
						continue;
					}

					// Normalizar y buscar proveedor (OPCIONAL)
					$idProveedor = null;
					if (!empty($proveedor)) {
						$proveedorNormalizado = self::normalizarTexto($proveedor);
						$proveedorEncontrado = self::buscarProveedorPorNombre($proveedorNormalizado);

						if (!$proveedorEncontrado) {
							$errores[] = "Fila $numeroFila: El proveedor '$proveedor' no existe o no coincide";
							continue;
						}

						$idProveedor = $proveedorEncontrado["id"];
					}

					// --- PROCESAR UNIDAD DE MEDIDA ---
					$idUnidadMedida = 70; // Default: Unidad (ID Factus 70)
					if (!empty($unidadMedida)) {
						if (is_numeric($unidadMedida)) {
							$idUnidadMedida = intval($unidadMedida);
						} else {
							$unidadNormalizada = self::normalizarTexto($unidadMedida);
							$unidadEncontrada = self::buscarUnidadPorNombre($unidadNormalizada);
							if ($unidadEncontrada) {
								$idUnidadMedida = $unidadEncontrada["codigo"];
							} else {
								$errores[] = "Fila $numeroFila: La unidad de medida '$unidadMedida' no fue reconocida";
								continue;
							}
						}
					}

					// --- PROCESAR TRIBUTO ---
					$idTributo = 1; // Default: IVA (ID 1)
					if (!empty($tributo)) {
						if (is_numeric($tributo)) {
							$idTributo = intval($tributo);
						} else {
							$tributoNormalizado = self::normalizarTexto($tributo);
							$tributoEncontrado = self::buscarTributoPorNombre($tributoNormalizado);
							if ($tributoEncontrado) {
								$idTributo = $tributoEncontrado["id"];
							} else {
								$errores[] = "Fila $numeroFila: El tributo '$tributo' no fue reconocido";
								continue;
							}
						}
					}

					// Verificar si el código ya existe
					$item = "codigo";
					$valor = $codigo;
					$productoExiste = ModeloProductos::mdlMostrarProductos("productos", $item, $valor, null, $idBodegaActiva);

					if ($productoExiste) {
						// Agregar a la lista de actualización masiva
						$productosActualizar[] = array(
							"id" => $productoExiste["id"],
							"id_categoria" => $categoriaEncontrada["id"],
							"id_proveedor" => $idProveedor,
							"codigo" => $codigo,
							"descripcion" => $descripcion,
							"stock" => $stock,
							"stock_anterior" => $productoExiste["stock"],
							"precio_compra" => $precioCompra,
							"precio_venta" => $precioVenta,
							"unidad_medida_id" => $idUnidadMedida,
							"tributo_id" => $idTributo
						);
					} else {
						// Agregar producto a la lista de importación
						$productosImportar[] = array(
							"id_categoria" => $categoriaEncontrada["id"],
							"id_proveedor" => $idProveedor,
							"codigo" => $codigo,
							"descripcion" => $descripcion,
							"imagen" => "vistas/img/productos/default/anonymous.png",
							"stock" => $stock,
							"precio_compra" => $precioCompra,
							"precio_venta" => $precioVenta,
							"unidad_medida_id" => $idUnidadMedida,
							"tributo_id" => $idTributo,
							"ventas" => 0
						);
					}
				}

				fclose($handle);

				// Si hay errores, no importar nada
				if (count($errores) > 0) {

					$mensajeError = "⚠️ Error en la importación:\\n\\n";
					foreach ($errores as $error) {
						$mensajeError .= "• " . $error . "\\n";
					}

					$mensajeError .= "\\nPor favor corrige el archivo CSV y vuelve a intentar.";

					echo '<script>
						swal({
							type: "error",
							title: "Error en la importación",
							text: "' . $mensajeError . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "productos";
						});
					</script>';
					return;
				}


				// Si no hay errores, importar y/o actualizar
				if (count($productosImportar) > 0 || count($productosActualizar) > 0) {

					$tabla = "productos";
					$respuesta = ModeloProductos::mdlImportarProductosMasivos($tabla, $productosImportar, $productosActualizar, $idBodegaActiva);

					if ($respuesta == "ok") {

						$totalImportados = count($productosImportar);
						$totalActualizados = count($productosActualizar);

						echo '<script>
							swal({
								type: "success",
								title: "¡Proceso exitoso!",
								text: "Se importaron ' . $totalImportados . ' productos nuevos y se actualizaron ' . $totalActualizados . ' existentes.",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(() => {
								window.location = "productos";
							});
						</script>';

					} else {
						echo '<script>
							swal({
								type: "error",
								title: "Error en el proceso",
								text: "Hubo un error al guardar o actualizar los productos en la base de datos.",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(() => {
								window.location = "productos";
							});
						</script>';
					}

				} else {
					echo '<script>
						swal({
							type: "warning",
							title: "Sin productos válidos",
							text: "No hay productos válidos para procesar en el archivo CSV.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "productos";
						});
					</script>';
				}

			} else {
				echo '<script>
					swal({
						type: "error",
						title: "Error al leer archivo",
						text: "No se pudo abrir el archivo CSV.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "productos";
					});
				</script>';
			}
		}
	}

	/*=============================================
	FUNCIÓN AUXILIAR: NORMALIZAR TEXTO
	=============================================*/
	static private function normalizarTexto($texto)
	{

		// Convertir a minúsculas
		$texto = strtolower($texto);

		// Quitar espacios al inicio y final
		$texto = trim($texto);

		// Quitar acentos
		$texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

		// Quitar espacios múltiples
		$texto = preg_replace('/\s+/', ' ', $texto);

		return $texto;
	}


	/*=============================================
	FUNCIÓN AUXILIAR: BUSCAR CATEGORÍA POR NOMBRE
	=============================================*/

	static private function buscarCategoriaPorNombre($nombreNormalizado)
	{

		$categorias = ControladorCategorias::ctrMostrarCategorias(null, null);

		foreach ($categorias as $categoria) {

			$categoriaNormalizada = self::normalizarTexto($categoria["categoria"]);

			if ($categoriaNormalizada == $nombreNormalizado) {

				return $categoria;
			}

		}

		return false;
	}


	/*=============================================
	FUNCIÓN AUXILIAR: BUSCAR PROVEEDOR POR NOMBRE
	=============================================*/

	static private function buscarProveedorPorNombre($nombreNormalizado)
	{

		$proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);

		foreach ($proveedores as $proveedor) {

			$proveedorNormalizado = self::normalizarTexto($proveedor["nombre"]);

			if ($proveedorNormalizado == $nombreNormalizado) {

				return $proveedor;
			}
		}

		return false;
	}


	/*=============================================
	FUNCIÓN AUXILIAR: BUSCAR UNIDAD POR NOMBRE
	=============================================*/

	static private function buscarUnidadPorNombre($nombreNormalizado)
	{

		require_once "modelos/factus.modelo.php";

		$unidades = ModeloFactus::mdlObtenerUnidadesMedida();

		// Mapeo manual de abreviaturas específicas
		$mapaAbreviaturas = [
			"und" => "unidad"
		];

		$busqueda = isset($mapaAbreviaturas[$nombreNormalizado]) ? $mapaAbreviaturas[$nombreNormalizado] : $nombreNormalizado;

		foreach ($unidades as $unidad) {

			// Normalizar nombre de la unidad
			$nombreUnidadNormalizado = self::normalizarTexto($unidad["nombre"]);

			// También comparar con el código DIAN (ej: GLL, KGM, 94)
			$codigoDianNormalizado = self::normalizarTexto($unidad["codigo_dian"]);

			if (
				$nombreUnidadNormalizado == $busqueda ||
				$codigoDianNormalizado == $nombreNormalizado ||
				$nombreUnidadNormalizado == $nombreNormalizado
			) {

				return $unidad;
			}
		}

		return false;
	}


	/*=============================================
	FUNCIÓN AUXILIAR: BUSCAR TRIBUTO POR NOMBRE
	=============================================*/

	static private function buscarTributoPorNombre($nombreNormalizado)
	{

		require_once "modelos/factus.modelo.php";

		$tributos = ModeloFactus::mdlObtenerTributos();

		// Si es "excluido", buscar directamente
		if ($nombreNormalizado == "excluido") {
			foreach ($tributos as $tributo) {
				if (self::normalizarTexto($tributo["nombre"]) == "excluido") {
					return $tributo;
				}
			}
		}

		// Para formatos tipo "iva 19" o "inc 8"
		$partes = explode(" ", $nombreNormalizado);

		foreach ($tributos as $tributo) {

			$tributoNombreNorm = self::normalizarTexto($tributo["nombre"]);

			// Caso 1: Match exacto (por si acaso)
			if ($tributoNombreNorm == $nombreNormalizado) {
				return $tributo;
			}

			// Caso 2: Contiene todas las partes (ej: si buscas "iva 19" y el DB tiene "IVA 19% (General)")
			$coincidencias = 0;
			foreach ($partes as $parte) {
				if (strpos($tributoNombreNorm, $parte) !== false) {
					$coincidencias++;
				}
			}

			if ($coincidencias == count($partes)) {
				return $tributo;
			}
		}

		return false;
	}

	/*=============================================
	AJAX EDITAR IMAGEN
	=============================================*/
	public static function ctrAjaxEditarImagen()
	{
		try {
			// 1. Validar que los datos necesarios lleguen
			if (!isset($_FILES["nuevaImagenProducto"]) || !isset($_POST["idProductoImagen"]) || !isset($_POST["codigoProductoImagen"])) {
				throw new Exception("Datos incompletos.");
			}

			$idProducto = $_POST["idProductoImagen"];
			$codigoProducto = $_POST["codigoProductoImagen"];
			$archivoImagen = $_FILES["nuevaImagenProducto"];

			// 2. Obtener la ruta de la imagen actual para borrarla después
			$productoActual = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id");
			if (!$productoActual) {
				// Mensaje de error mejorado para depuración
				throw new Exception("Producto no encontrado con ID: " . $idProducto);
			}
			$imagenActual = $productoActual["imagen"];

			// 3. Procesar y guardar la nueva imagen
			$ruta = $imagenActual;

			if (isset($archivoImagen["tmp_name"]) && !empty($archivoImagen["tmp_name"])) {
				list($ancho, $alto) = getimagesize($archivoImagen["tmp_name"]);
				$nuevoAncho = 500;
				$nuevoAlto = 500;
				// CORRECCIÓN DE RUTA: Las rutas de sistema de archivos son relativas al script que se ejecuta (ajax/productos.ajax.php)
				$directorio = "../vistas/img/productos/" . $codigoProducto;

				// Borrar imagen anterior si existe y no es la por defecto
				if (!empty($imagenActual) && $imagenActual != "vistas/img/productos/default/anonymous.png" && file_exists("../" . $imagenActual)) {
					unlink("../" . $imagenActual);
				}

				// Crear directorio si no existe
				if (!file_exists($directorio)) {
					mkdir($directorio, 0755, true);
				}

				// Generar nueva ruta para la base de datos (sin ../)
				$aleatorio = mt_rand(100, 999);
				$nombreArchivo = "";
				if ($archivoImagen["type"] == "image/jpeg") {
					$nombreArchivo = $aleatorio . ".jpeg";
				} else if ($archivoImagen["type"] == "image/png") {
					$nombreArchivo = $aleatorio . ".png";
				} else {
					throw new Exception("Formato de imagen no válido. Solo se permite JPG o PNG.");
				}
				$ruta_db = "vistas/img/productos/" . $codigoProducto . "/" . $nombreArchivo;
				// Ruta para el sistema de archivos (con ../)
				$ruta_fs = "../" . $ruta_db;

				// Crear imagen desde temporal
				$origen = null;
				if ($archivoImagen["type"] == "image/jpeg") {
					$origen = imagecreatefromjpeg($archivoImagen["tmp_name"]);
				} else if ($archivoImagen["type"] == "image/png") {
					$origen = imagecreatefrompng($archivoImagen["tmp_name"]);
				}

				$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
				imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

				// Guardar la nueva imagen en el sistema de archivos
				if ($archivoImagen["type"] == "image/jpeg") {
					imagejpeg($destino, $ruta_fs);
				} else if ($archivoImagen["type"] == "image/png") {
					imagepng($destino, $ruta_fs);
				}

				imagedestroy($origen);
				imagedestroy($destino);

				$ruta = $ruta_db; // Actualizar la variable $ruta con la nueva ruta para la BD
			}

			// 4. Actualizar la base de datos
			$tabla = "productos";
			$datos = array("imagen" => $ruta);
			$respuesta = ModeloProductos::mdlActualizarImagenProducto($tabla, $datos, $idProducto);

			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar la base de datos.");
			}

			// 5. Enviar respuesta de éxito
			header('Content-Type: application/json');
			echo json_encode(array("status" => "ok", "message" => "Imagen actualizada correctamente."));

		} catch (Exception $e) {
			// 6. Enviar respuesta de error
			header('Content-Type: application/json');
			echo json_encode(array("status" => "error", "message" => $e->getMessage()));
		}
	}



	/*=============================================
	AJUSTE RÁPIDO DE STOCK
	=============================================*/
	static public function ctrAjusteStockLocal()
	{
		if (isset($_POST["idProductoAjuste"]) && isset($_POST["tipoAjuste"]) && isset($_POST["cantidadAjuste"])) {

			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(){
						window.location = "productos";
					});
				</script>';
				return;
			}

			$idProducto = $_POST["idProductoAjuste"];
			$tipo = $_POST["tipoAjuste"];
			$cantidad = (int) $_POST["cantidadAjuste"];

			if ($cantidad > 0) {
				
				$idBodegaActiva = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;

				// Obtener el stock actual de la bodega activa
				$producto = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id", $idBodegaActiva);
				
				if ($producto) {
					$stockActual = (int) $producto["stock"];
					
					if ($tipo == "aumentar") {
						$nuevoStock = $stockActual + $cantidad;
					} else {
						$nuevoStock = $stockActual - $cantidad;
						if ($nuevoStock < 0) $nuevoStock = 0;
					}

					$diferencia = $nuevoStock - $stockActual;

					$db = Conexion::conectar();
					try {
						$db->beginTransaction();

						// 1. Actualizar el stock de la bodega activa
						$resB = ModeloProductos::mdlActualizarStockBodega($idProducto, $idBodegaActiva, $nuevoStock);
						if ($resB != "ok") {
							throw new Exception("Error al actualizar el stock en la bodega activa.");
						}

						// 2. Recalcular y actualizar el stock global sumando todas las bodegas
						$stmtSumBodegas = $db->prepare("SELECT SUM(pb.stock) as total FROM productos_bodegas pb WHERE pb.id_producto = :id_producto");
						$stmtSumBodegas->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
						$stmtSumBodegas->execute();
						$resSum = $stmtSumBodegas->fetch();
						$stockGlobal = ($resSum && $resSum["total"]) ? (int) $resSum["total"] : $nuevoStock;
						$stmtSumBodegas = null;

						$resProd = ModeloProductos::mdlActualizarProducto("productos", "stock", $stockGlobal, $idProducto);
						if ($resProd != "ok") {
							throw new Exception("Error al actualizar el stock global del producto.");
						}

						// 3. Registrar el movimiento de stock
						$resMov = ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$idProducto,
							null,
							$producto["descripcion"],
							"ajuste_manual",
							$diferencia,
							$stockActual,
							$nuevoStock,
							"Ajuste rápido",
							"",
							$idBodegaActiva
						);
						if ($resMov != "ok") {
							throw new Exception("Error al registrar el movimiento de stock.");
						}

						$db->commit();

						echo '<script>
							swal({
								type: "success",
								title: "¡Stock modificado correctamente!",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(function(){
								window.location = "productos";
							});
						</script>';

					} catch (Exception $e) {
						$db->rollBack();
						Logger::error("Error en ajuste rápido de stock para producto ID " . $idProducto . ": " . $e->getMessage());

						echo '<script>
							swal({
								type: "error",
								title: "Error al realizar el ajuste de stock",
								text: "' . addslashes($e->getMessage()) . '",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							});
						</script>';
					}
				}
			}
		}
	}

}





