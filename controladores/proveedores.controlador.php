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
						icon: "error",
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
						icon: "success",
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
						icon: "error",
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
						icon: "error",
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
						icon: "success",
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
						icon: "error",
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
						icon: "error",
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

			// Verificar si hay productos asociados a esta proveedores
			$productosAsociados = ModeloProductos::mdlMostrarProductos("productos", "id_proveedor", $idProveedor, "id");

			if (!empty($productosAsociados)) {
				if (isset($_POST["idProveedorEliminar"])) {
					return "error_productos_asociados";
				}
				echo '<script>
					swal({
						icon: "error",
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
						icon: "success",
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
