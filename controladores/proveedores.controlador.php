<?php

class ControladorProveedores
{

	/*=============================================
	CREAR PROVEEDOR
	=============================================*/

	static public function ctrCrearProveedor()
	{

		if (isset($_POST["nuevoProveedor"])) {

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
						window.location = "proveedores";
					})
				</script>';
				return;
			}

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoProveedor"]) &&
				($_POST["nuevaMarca"] == "" || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaMarca"])) &&
				preg_match('/^[0-9]+$/', $_POST["nuevoCelular"])
			) {

				$tabla = "proveedores";

				$datos = $_POST["nuevoProveedor"];

				$datos = array(
					"nombre" => $_POST["nuevoProveedor"],
					"documento" => $_POST["nuevoDocumento"],
					"tipo_documento_id" => $_POST["nuevoTipoDocumento"],
					"marca" => $_POST["nuevaMarca"],
					"celular" => $_POST["nuevoCelular"],
					"correo" => $_POST["nuevoCorreo"],
					"direccion" => $_POST["nuevaDireccion"],
					"municipio_id" => $_POST["nuevoMunicipio"],
					"organizacion_id" => $_POST["nuevaOrganizacion"]
				);

				$respuesta = ModeloProveedores::mdlIngresarProveedor($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
					swal({
						type: "success",
						title: "¡El proveedor ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
								window.location = "proveedores";
						});
				</script>';
				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡El proveedor no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
								window.location = "proveedores";
						});
				</script>';
			}
		}

	}

	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/

	static public function ctrMostrarProveedores($item, $valor)
	{

		$tabla = "proveedores";

		$respuesta = ModeloProveedores::mdlMostrarProveedores($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	MOSTRAR PROVEEDORES SERVER-SIDE
	=============================================*/
	static public function ctrMostrarProveedoresServerSide($params)
	{
		$tabla = "proveedores";

		// Columnas para ordenar
		$columnsMap = array(
			0 => 'nombre',
			1 => 'marca',
			2 => 'celular',
			3 => 'correo',
			4 => 'direccion',
			5 => 'id', // Productos (calculado)
			6 => 'notas',
			7 => 'id'  // Acciones
		);

		$where = " WHERE 1=1 ";

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (nombre LIKE '%$searchValue%' OR marca LIKE '%$searchValue%' OR celular LIKE '%$searchValue%' OR correo LIKE '%$searchValue%' OR documento LIKE '%$searchValue%' OR direccion LIKE '%$searchValue%' OR notas LIKE '%$searchValue%') ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'id';
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
		$proveedores = ModeloProveedores::mdlMostrarProveedoresServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloProveedores::mdlGetTotalProveedores($tabla, " WHERE 1=1 ");
		$totalFiltered = ModeloProveedores::mdlGetTotalProveedores($tabla, $where);

		$data = array();

		foreach ($proveedores as $key => $value) {
			
			$nestedData = array();

			// 0: Nombre
			$nestedData[] = e($value["nombre"]);

			// 1: Marca
			$nestedData[] = e($value["marca"]);

			// 2: Celular
			$nestedData[] = e($value["celular"]);

			// 3: Correo
			$nestedData[] = e($value["correo"]);

			// 4: Dirección
			$nestedData[] = e($value["direccion"]);

			// 5: Productos
			$totalProductos = ModeloProveedores::mdlContarProductosPorProveedor($value["id"]);
			$nestedData[] = '<span class="badge bg-blue">' . $totalProductos . '</span>';

			// 6: Notas (Editable)
			$notas = isset($value["notas"]) ? e($value["notas"]) : '';
			$nestedData[] = '<div contenteditable="true" class="celda-notas-proveedor" tabindex="0" data-id="' . $value['id'] . '">' . $notas . '</div>';

			// 7: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('proveedores', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarProveedor" idProveedor="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarProveedor" title="Editar proveedor"><i class="fa fa-pencil"></i></button>';
			}
			if (puedeAccion('proveedores', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarProveedor" idProveedor="' . $value["id"] . '" title="Eliminar proveedor"><i class="fa fa-times"></i></button>';
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
	EDITAR PROVEEDORES
	=============================================*/

	static public function ctrEditarProveedor()
	{

		if (isset($_POST["editarProveedor"])) {

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
						window.location = "proveedores";
					})
				</script>';
				return;
			}

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarProveedor"]) &&
				($_POST["editarMarca"] == "" || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarMarca"])) &&
				preg_match('/^[0-9]+$/', $_POST["editarCelular"])
			) {

				$tabla = "proveedores";

				$datos = array(
					"id" => $_POST["idProveedor"],
					"nombre" => $_POST["editarProveedor"],
					"documento" => $_POST["editarDocumento"],
					"tipo_documento_id" => $_POST["editarTipoDocumento"],
					"marca" => $_POST["editarMarca"],
					"celular" => $_POST["editarCelular"],
					"correo" => $_POST["editarCorreo"],
					"direccion" => $_POST["editarDireccion"],
					"municipio_id" => $_POST["editarMunicipio"],
					"organizacion_id" => $_POST["editarOrganizacion"]
				);

				$respuesta = ModeloProveedores::mdlEditarProveedor($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
					swal({
						type: "success",
						title: "¡El Proveedor ha sido editado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
								window.location = "proveedores";
						});
				</script>';
				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡El Proveedor no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
								window.location = "proveedores";
						});
				</script>';
			}
		}

	}


	/*=============================================
	BORRAR PROVEEDORES
	=============================================*/

	static public function ctrBorrarProveedor()
	{

		if (isset($_GET["idProveedor"]) || isset($_POST["idProveedorEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idProveedorEliminar"])) {
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
						window.location = "proveedores";
					})
				</script>';
				return;
			}

			$tabla = "proveedores";
			$idProveedor = isset($_GET["idProveedor"]) ? $_GET["idProveedor"] : $_POST["idProveedorEliminar"];

			// Verificar si hay documentos soporte asociados a este proveedor
			$docsSoporteAsociados = ModeloFactus::mdlMostrarDocumentosSoporte("id_proveedor", $idProveedor);

			if (!empty($docsSoporteAsociados)) {
				if (isset($_POST["idProveedorEliminar"])) {
					return "error_documentos_soporte";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El proveedor tiene documentos soporte asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
							window.location = "proveedores";
					});
				</script>';
				return;
			}

			// Verificar si hay productos asociados a este proveedor
			$productosAsociados = ModeloProductos::mdlMostrarProductos("productos", "id_proveedor", $idProveedor, "id");

			if (!empty($productosAsociados)) {
				if (isset($_POST["idProveedorEliminar"])) {
					return "error_productos_asociados";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El proveedor tiene productos asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
							window.location = "proveedores";
					});
				</script>';
				return;
			}

			$respuesta = ModeloProveedores::mdlBorrarProveedor($tabla, $idProveedor);

			if ($respuesta == "ok") {
				if (isset($_POST["idProveedorEliminar"])) {
					return "ok";
				}
				echo '<script>
					swal({
						type: "success",
						title: "¡El Proveedor ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
							window.location = "proveedores";
					});
				</script>';
			}
		}
	}




}
