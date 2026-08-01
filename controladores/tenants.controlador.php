<?php

class ControladorTenants {

	/*=============================================
	MOSTRAR INQUILINOS
	=============================================*/
	static public function ctrMostrarTenants($item, $valor) {
		$tabla = "clientes_tenants";
		$respuesta = ModeloTenants::mdlMostrarTenants($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	CREAR INQUILINO
	=============================================*/
	static public function ctrCrearTenant() {

		if (isset($_POST["nuevoSubdominio"])) {

			// Validar caracteres permitidos en el subdominio (letras, números, guiones y guión bajo)
			if (preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["nuevoSubdominio"])) {

				$tabla = "clientes_tenants";

				// Validar que el subdominio no esté repetido
				$validarRepetido = ModeloTenants::mdlMostrarTenants($tabla, "subdominio", $_POST["nuevoSubdominio"]);
				if ($validarRepetido) {
					echo '<script>
						swal({
							type: "error",
							title: "¡Error!",
							text: "El subdominio ya se encuentra registrado por otro inquilino.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';
					return;
				}

				$datos = array(
					"subdominio" => strtolower($_POST["nuevoSubdominio"]),
					"db_name" => $_POST["nuevaDbName"],
					"db_user" => $_POST["nuevoDbUser"],
					"db_pass" => $_POST["nuevoDbPass"],
					"celular" => isset($_POST["nuevoCelular"]) ? trim($_POST["nuevoCelular"]) : "",
					"db_host" => !empty($_POST["nuevoDbHost"]) ? $_POST["nuevoDbHost"] : "127.0.0.1",
					"estado" => $_POST["nuevoEstado"]
				);

				$respuesta = ModeloTenants::mdlCrearTenant($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "¡Creado!",
							text: "El inquilino ha sido guardado correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Ok"
						}).then((result) => {
							if (result.value) {
								window.location = "inquilinos";
							}
						});
					</script>';
				}

			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "El subdominio solo puede contener letras, números y guiones.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	EDITAR INQUILINO
	=============================================*/
	static public function ctrEditarTenant() {

		if (isset($_POST["editarSubdominio"])) {

			// Validar caracteres permitidos
			if (preg_match('/^[a-zA-Z0-9_-]+$/', $_POST["editarSubdominio"])) {

				$tabla = "clientes_tenants";

				// Validar que el subdominio no esté repetido en otro ID
				$validarRepetido = ModeloTenants::mdlMostrarTenants($tabla, "subdominio", $_POST["editarSubdominio"]);
				if ($validarRepetido && $validarRepetido["id"] != $_POST["idTenant"]) {
					echo '<script>
						swal({
							type: "error",
							title: "¡Error!",
							text: "El subdominio ya se encuentra registrado por otro inquilino.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';
					return;
				}

				$datos = array(
					"id" => $_POST["idTenant"],
					"subdominio" => strtolower($_POST["editarSubdominio"]),
					"db_name" => $_POST["editarDbName"],
					"db_user" => $_POST["editarDbUser"],
					"db_pass" => $_POST["editarDbPass"],
					"celular" => isset($_POST["editarCelular"]) ? trim($_POST["editarCelular"]) : "",
					"db_host" => !empty($_POST["editarDbHost"]) ? $_POST["editarDbHost"] : "127.0.0.1",
					"estado" => $_POST["editarEstado"]
				);

				$respuesta = ModeloTenants::mdlEditarTenant($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "¡Editado!",
							text: "El inquilino ha sido modificado correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Ok"
						}).then((result) => {
							if (result.value) {
								window.location = "inquilinos";
							}
						});
					</script>';
				}

			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "El subdominio solo puede contener letras, números y guiones.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR INQUILINO
	=============================================*/
	static public function ctrEliminarTenant() {

		if (isset($_GET["idTenant"])) {

			$tabla = "clientes_tenants";
			$id = $_GET["idTenant"];

			$respuesta = ModeloTenants::mdlEliminarTenant($tabla, $id);

			if ($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "¡Eliminado!",
						text: "El inquilino ha sido eliminado correctamente.",
						showConfirmButton: true,
						confirmButtonText: "Ok"
					}).then((result) => {
						if (result.value) {
							window.location = "inquilinos";
						}
					});
				</script>';
			}
		}
	}
}
