<?php

class ControladorClientes
{

	/*=============================================
	CREAR CLIENTES
	=============================================*/

	static public function ctrCrearCliente()
	{

		if (isset($_POST["nuevoCliente"])) {

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoCliente"]) &&
				preg_match('/^[0-9]+$/', $_POST["nuevoDocumentoId"]) &&
					//preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"]) &&
				(empty($_POST["nuevoEmail"]) || preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"])) &&
				preg_match('/^[()\-0-9 ]+$/', $_POST["nuevoTelefono"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoDepartamento"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoCiudad"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoEstatus"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaNota"]) &&
				preg_match('/^[#\.\-a-zA-Z0-9 ,]+$/', $_POST["nuevaDireccion"])
			) {

				$tabla = "clientes";

				$datos = array(
					"nombre" => $_POST["nuevoCliente"],
					"documento" => $_POST["nuevoDocumentoId"],
					"email" => $_POST["nuevoEmail"],
					"telefono" => $_POST["nuevoTelefono"],
					"departamento" => isset($_POST["nuevoDepartamento"]) ? $_POST["nuevoDepartamento"] : null,
					"ciudad" => isset($_POST["nuevoCiudad"]) ? $_POST["nuevoCiudad"] : null,
					"direccion" => $_POST["nuevaDireccion"],
					"estatus" => $_POST["nuevoEstatus"],
					"fecha_nacimiento" => isset($_POST["nuevaFechaNacimiento"]) && !empty($_POST["nuevaFechaNacimiento"]) ? $_POST["nuevaFechaNacimiento"] . ' 00:00:00' : null,
					"notas" => $_POST["nuevaNota"],
					// Campos de facturación electrónica (DIAN)
					"tipo_documento_id" => isset($_POST["nuevoTipoDocumento"]) && !empty($_POST["nuevoTipoDocumento"]) ? $_POST["nuevoTipoDocumento"] : 3, // Default: NIT
					"municipio_id" => isset($_POST["nuevoMunicipio"]) && !empty($_POST["nuevoMunicipio"]) ? $_POST["nuevoMunicipio"] : null,
					"digito_verificacion" => isset($_POST["nuevoDigitoVerificacion"]) ? $_POST["nuevoDigitoVerificacion"] : null,
					"tipo_persona" => isset($_POST["nuevoTipoPersona"]) ? $_POST["nuevoTipoPersona"] : 'natural',
					"regimen_tributario" => isset($_POST["nuevoRegimenTributario"]) ? $_POST["nuevoRegimenTributario"] : 'simplificado',
					"responsabilidades_fiscales" => isset($_POST["nuevasResponsabilidades"]) ? $_POST["nuevasResponsabilidades"] : null,
					"codigo_postal" => isset($_POST["nuevoCodigoPostal"]) ? $_POST["nuevoCodigoPostal"] : null,
					"nombre_comercial" => isset($_POST["nuevoNombreComercial"]) ? $_POST["nuevoNombreComercial"] : null,
					"razon_social" => isset($_POST["nuevaRazonSocial"]) ? $_POST["nuevaRazonSocial"] : null
				);

				$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					// Determinar a dónde redirigir según el origen
					$redireccion = isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes";

					echo '<script>
					swal({
						type: "success",
						title: "!El cliente ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{
							if(result.value){

							   window.location = "' . $redireccion . '";
							}
						})
			     	</script>';
				}
			} else {

				// Determinar a dónde redirigir según el origen
				$redireccion = isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes";

				echo '<script>
					swal({
						type: "error",
						title: "!El cliente no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{

							if(result.value){

								window.location = "' . $redireccion . '";
							}
						})
				</script>';
			}

		}

	}

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/

	static public function ctrMostrarClientes($item, $valor)
	{

		$tabla = "clientes";

		$respuesta = ModeloClientes::mdlMostrarClientes($tabla, $item, $valor);

		return $respuesta;
	}



	/*=============================================
	EDITAR CLIENTES
	=============================================*/

	static public function ctrEditarCliente()
	{

		if (isset($_POST["editarCliente"])) {

			/*echo "<pre>";
			var_dump($_POST);
			echo "</pre>";
			die();*/

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCliente"]) &&
				preg_match('/^[0-9]+$/', $_POST["editarDocumentoId"]) &&
					//preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"]) &&
				(empty($_POST["editarEmail"]) || preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"])) &&
				preg_match('/^[()\\-0-9 ]+$/', $_POST["editarTelefono"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDepartamento"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCiudad"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEstatus"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNota"]) &&
				preg_match('/^[#\\.\\-a-zA-Z0-9 ]+$/', $_POST["editarDireccion"])
			) {

				$tabla = "clientes";

				$datos = array(
					"id" => $_POST["idCliente"],
					"nombre" => $_POST["editarCliente"],
					"documento" => $_POST["editarDocumentoId"],
					"email" => $_POST["editarEmail"],
					"telefono" => $_POST["editarTelefono"],
					"departamento" => isset($_POST["editarDepartamento"]) ? $_POST["editarDepartamento"] : null,
					"ciudad" => isset($_POST["editarCiudad"]) ? $_POST["editarCiudad"] : null,
					"direccion" => $_POST["editarDireccion"],
					"estatus" => $_POST["editarEstado"],
					"notas" => isset($_POST["editarNota"]) ? $_POST["editarNota"] : null,
					"fecha_nacimiento" => isset($_POST["editarFechaNacimiento"]) && !empty($_POST["editarFechaNacimiento"]) ? $_POST["editarFechaNacimiento"] . ' 00:00:00' : null,
					// Campos de facturación electrónica (DIAN)
					"tipo_documento_id" => isset($_POST["editarTipoDocumento"]) && !empty($_POST["editarTipoDocumento"]) ? $_POST["editarTipoDocumento"] : 3, // Default: NIT
					"municipio_id" => isset($_POST["editarMunicipio"]) && !empty($_POST["editarMunicipio"]) ? $_POST["editarMunicipio"] : null,
					"digito_verificacion" => isset($_POST["editarDigitoVerificacion"]) ? $_POST["editarDigitoVerificacion"] : null,
					"tipo_persona" => isset($_POST["editarTipoPersona"]) ? $_POST["editarTipoPersona"] : 'natural',
					"regimen_tributario" => isset($_POST["editarRegimenTributario"]) ? $_POST["editarRegimenTributario"] : 'simplificado',
					"responsabilidades_fiscales" => isset($_POST["editarResponsabilidades"]) ? $_POST["editarResponsabilidades"] : null,
					"codigo_postal" => isset($_POST["editarCodigoPostal"]) ? $_POST["editarCodigoPostal"] : null,
					"nombre_comercial" => isset($_POST["editarNombreComercial"]) ? $_POST["editarNombreComercial"] : null,
					"razon_social" => isset($_POST["editarRazonSocial"]) ? $_POST["editarRazonSocial"] : null
				);

				// DEBUG: Log para ver qué municipio se está guardando
				file_put_contents(
					"debug_cliente_save.txt",
					"=== EDITAR CLIENTE ===\n" .
					"Cliente ID: " . $_POST["idCliente"] . "\n" .
					"POST editarMunicipio: " . (isset($_POST["editarMunicipio"]) ? $_POST["editarMunicipio"] : 'NO EXISTE') . "\n" .
					"Municipio a guardar: " . ($datos["municipio_id"] ?? 'NULL') . "\n" .
					"Timestamp: " . date('Y-m-d H:i:s') . "\n\n",
					FILE_APPEND
				);

				/*echo "<pre>DATOS A GUARDAR:";
				var_dump($datos);
				echo "</pre>";
				die();*/

				$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
					swal({
						type: "success",
						title: "!El cliente ha sido cambiado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{
							if(result.value){

							   window.location = "clientes";
							}
						})
			     	</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "!El cliente no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{

							if(result.value){

								window.location = "clientes";
							}
						})
				</script>';
			}

		}


	}


	/*=============================================
	ELIMINAR CLIENTES
	=============================================*/

	static public function ctrEliminarCliente()
	{

		if (isset($_GET["idCliente"])) {

			$tabla = "clientes";
			$datos = $_GET["idCliente"];

			// Detectar de dónde viene la eliminación
			$ruta = isset($_GET["ruta"]) ? $_GET["ruta"] : "clientes";

			// Verificar si hay actividades asociados
			$actividadesAsociados = ModeloActividades::mdlMostrarActividades("actividades", "id_cliente", $datos, "id");

			if (!empty($actividadesAsociados)) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El cliente tiene actividades asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "' . $ruta . '";
						}
					});
				</script>';
				return;
			}


			// Verificar si hay ventas asociados
			$ventasAsociados = ModeloVentas::mdlMostrarVentas("ventas", "id_cliente", $datos, "id");

			if (!empty($ventasAsociados)) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El cliente tiene ventas asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "' . $ruta . '";
						}
					});
				</script>';
				return;
			}


			$respuesta = ModeloClientes::mdlEliminarCliente($tabla, $datos);

			if ($respuesta == "ok") {

				echo '<script>
					swal({
						type: "success",
						title: "!El cliente ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "' . $ruta . '";
							}
						});
				</script>';
			}

		}
	}


}
