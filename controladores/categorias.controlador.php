<?php

class ControladorCategorias{

	/*=============================================
	CREAR CATEGORIAS
	=============================================*/

	static public function ctrCrearCategoria(){

		if(isset($_POST["nuevaCategoria"])){

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
						window.location = "categorias";
					})
				</script>';
				return;
			}

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaCategoria"])){

				$tabla = "categorias";

				$prefijo = isset($_POST["nuevoPrefijo"]) && $_POST["nuevoPrefijo"] !== "" ? $_POST["nuevoPrefijo"] : null;

				// Validar si el prefijo ya existe
				if ($prefijo !== null) {
					$prefijoExistente = ModeloCategorias::mdlMostrarCategorias($tabla, "prefijo", $prefijo);
					if ($prefijoExistente) {
						echo '<script>
							swal({
								type: "error",
								title: "¡Error!",
								text: "El prefijo ya está siendo usado por otra categoría.",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(() => {
								window.location = "categorias";
							});
						</script>';
						return;
					}
				}

				$datos = array(
					"categoria" => $_POST["nuevaCategoria"],
					"prefijo" => $prefijo
				);

				$db = Conexion::conectar();
				try {
					$db->beginTransaction();
					$respuesta = ModeloCategorias::mdlIngresarCategoria($tabla, $datos);
					
					if($respuesta != "ok"){
						throw new Exception("Error al guardar la categoría.");
					}
					
					$db->commit();

					echo '<script>
					swal({
						type: "success",
						title: "¡La categoría ha sido guardada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "categorias";
						});
					</script>';
				} catch (Exception $e) {
					$db->rollBack();
					echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "Error interno: ' . addslashes($e->getMessage()) . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "categorias";
						});
					</script>';
				}

			}
			else{

				echo '<script>
					swal({
						type: "error",
						title: "!La categoría no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "categorias";
							}
						});
				</script>';
			}
		}

	}

	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/

	static public function ctrMostrarCategorias($item, $valor){

		$tabla = "categorias";

		$respuesta = ModeloCategorias::mdlMostrarCategorias($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	MOSTRAR CATEGORIAS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarCategoriasServerSide($params)
	{
		$tabla = "categorias";

		// Columnas para ordenar
		$columns = array(
			0 => 'categoria',
			1 => 'prefijo',
			2 => 'id' 
		);

		$where = " WHERE 1=1 ";

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND categoria LIKE '%$searchValue%' ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columns[$colIdx]) ? $columns[$colIdx] : 'id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$categorias = ModeloCategorias::mdlMostrarCategoriasServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloCategorias::mdlGetTotalCategorias($tabla, " WHERE 1=1 ");
		$totalFiltered = ModeloCategorias::mdlGetTotalCategorias($tabla, $where);

		$data = array();

		foreach ($categorias as $key => $value) {
			
			$nestedData = array();

			// 0: Categoría
			$nestedData[] = '<span class="text-uppercase">' . e($value["categoria"]) . '</span>';

			// 1: Prefijo
			$nestedData[] = '<span>' . e($value["prefijo"]) . '</span>';

			// 2: Productos
			$totalProductos = ModeloCategorias::mdlContarProductosPorCategoria($value["id"]);
			$nestedData[] = '<span class="badge bg-blue">' . $totalProductos . '</span>';

			// 3: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('categorias', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarCategoria" idCategoria="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarCategoria" title="Editar categoría"><i class="fa fa-pencil"></i></button>';
			}
			if (puedeAccion('categorias', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarCategoria" idCategoria="' . $value["id"] . '" title="Eliminar categoría"><i class="fa fa-times"></i></button>';
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
	EDITAR CATEGORIAS
	=============================================*/

	static public function ctrEditarCategoria(){

		if(isset($_POST["editarCategoria"])){

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
					}).then((result)=>{
						if(result.value){
							window.location = "categorias";
						}
					})
				</script>';
				return;
			}

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCategoria"])){

				$tabla = "categorias";

				$prefijo = isset($_POST["editarPrefijo"]) && $_POST["editarPrefijo"] !== "" ? $_POST["editarPrefijo"] : null;
				$idCategoria = $_POST["idCategoria"];

				// Validar si el prefijo ya existe y no es de la categoría actual
				if ($prefijo !== null) {
					$prefijoExistente = ModeloCategorias::mdlMostrarCategorias($tabla, "prefijo", $prefijo);
					if ($prefijoExistente && $prefijoExistente["id"] != $idCategoria) {
						echo '<script>
							swal({
								type: "error",
								title: "¡Error!",
								text: "El prefijo ya está siendo usado por otra categoría.",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(() => {
								window.location = "categorias";
							});
						</script>';
						return;
					}
				}

				$datos = array(
					"categoria" => $_POST["editarCategoria"],
					"prefijo" => $prefijo,
					"id" => $idCategoria
				);

				$db = Conexion::conectar();
				try {
					$db->beginTransaction();
					
					$respuesta = ModeloCategorias::mdlEditarCategoria($tabla, $datos);

					if($respuesta != "ok"){
						throw new Exception("Error al editar la categoría.");
					}

					$db->commit();

					echo '<script>
					swal({
						type: "success",
						title: "¡La categoría ha sido editada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "categorias";
						});
					</script>';
				} catch (Exception $e) {
					$db->rollBack();
					echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "Error interno: ' . addslashes($e->getMessage()) . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "categorias";
						});
					</script>';
				}

			}
			else{

				echo '<script>
					swal({
						type: "error",
						title: "!La categoría no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "categorias";
							}
						});
				</script>';
			}
		}

	}


	/*=============================================
	BORRAR CATEGORIAS
	=============================================*/

	static public function ctrBorrarCategoria() {

		if (isset($_GET["idCategoria"]) || isset($_POST["idCategoriaEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idCategoriaEliminar"])) {
					return "error_csrf";
				}
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "categorias";
						}
					})
				</script>';
				return;
			}

			$tabla = "categorias";
			$idCategoria = isset($_GET["idCategoria"]) ? $_GET["idCategoria"] : $_POST["idCategoriaEliminar"];

			// Verificar si hay productos asociados a esta categoría (solo activos)
			$totalProductosGlobales = ModeloCategorias::mdlContarProductosActivosGlobales($idCategoria);

			if ($totalProductosGlobales > 0) {
				$productosLocal = ModeloCategorias::mdlContarProductosPorCategoria($idCategoria);

				if ($productosLocal == 0) {
					if (isset($_POST["idCategoriaEliminar"])) {
						return "error_productos_asociados_otra_sucursal";
					}
					echo '<script>
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "categorias";
						});
					</script>';
					return;
				}

				if (isset($_POST["idCategoriaEliminar"])) {
					return "error_productos_asociados";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "La categoría tiene productos asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "categorias";
					});
				</script>';
				return;
			}

			$db = Conexion::conectar();
			try {
				$db->beginTransaction();

				$respuesta = ModeloCategorias::mdlBorrarCategoria($tabla, $idCategoria);

				if ($respuesta != "ok") {
					throw new Exception("Error al borrar la categoría.");
				}

				$db->commit();

				if (isset($_POST["idCategoriaEliminar"])) {
					return "ok";
				}
				echo '<script>
					swal({
						type: "success",
						title: "¡La categoría ha sido borrada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "categorias";
					});
				</script>';
			} catch (Exception $e) {
				$db->rollBack();
				if (isset($_POST["idCategoriaEliminar"])) {
					return "error";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "Error interno: ' . addslashes($e->getMessage()) . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "categorias";
					});
				</script>';
			}
		}
	}


			/*
			if(isset($_GET["idCategoria"])){

				$tabla = "Categorias";
				$datos = $_GET["idCategoria"];

				$respuesta = ModeloCategorias::mdlBorrarCategoria($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
						swal({
							type: "success",
							title: "!La categoría ha sido borrada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar",
							closeOnConfirm: false

							}).then((result)=>{

								if(result.value){

									window.location = "categorias";
								}
							});
					</script>';
				}
			}
				*/

	


}
