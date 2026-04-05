<?php

class ControladorClientes
{

	/*=============================================
	CREAR CLIENTES
	=============================================*/

	static public function ctrCrearCliente()
	{

		if (isset($_POST["nuevoCliente"])) {

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
						window.location = "clientes";
					})
				</script>';
				return;
			}

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
						title: "¡El cliente ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {

							   window.location = "' . $redireccion . '";
						})
			     	</script>';
				}
			} else {

				// GUARDAR DATOS EN SESION PARA PERSISTENCIA
				$_SESSION["datos_cliente_error"] = $_POST;

				// Determinar a dónde redirigir según la URL actual o el origen
				$redireccion = isset($_POST["urlActual"]) ? $_POST["urlActual"] : (isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes");

				echo '<script>
					swal({
						type: "error",
						title: "¡El cliente no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {

								window.location = "' . $redireccion . '";
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
							window.location = "clientes";
						}
					})
				</script>';
				return;
			}


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



				$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					// Determinar a dónde redirigir según el origen
					$redireccion = isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes";

					echo '<script>
					swal({
						type: "success",
						title: "¡El cliente ha sido cambiado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {

							   window.location = "' . $redireccion . '";
						})
			     	</script>';
				}
			} else {

				// GUARDAR DATOS EN SESION PARA PERSISTENCIA
				$_SESSION["datos_cliente_error"] = $_POST;

				// Determinar a dónde redirigir según la URL actual o el origen
				$redireccion = isset($_POST["urlActual"]) ? $_POST["urlActual"] : (isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes");

				echo '<script>
					swal({
						type: "error",
						title: "¡El cliente no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {

								window.location = "' . $redireccion . '";
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

		if (isset($_GET["idCliente"]) || isset($_POST["idClienteEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST, GET se mantendrá por compatibilidad temporal pero se marcará como DEPRECATED)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idClienteEliminar"])) {
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
							window.location = "clientes";
						}
					})
				</script>';
				return;
			}

			$tabla = "clientes";
			$datos = isset($_GET["idCliente"]) ? $_GET["idCliente"] : $_POST["idClienteEliminar"];

			// Detectar de dónde viene la eliminación
			$ruta = isset($_GET["ruta"]) ? $_GET["ruta"] : (isset($_POST["ruta"]) ? $_POST["ruta"] : "clientes");

			// Verificar si hay actividades asociados
			$actividadesAsociados = ModeloActividades::mdlMostrarActividades("actividades", "id_cliente", $datos);

			if (!empty($actividadesAsociados)) {
				if (isset($_POST["idClienteEliminar"])) {
					return "error_actividades";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El cliente tiene actividades asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "' . $ruta . '";
					});
				</script>';
				return;
			}


			// Verificar si hay ventas asociados
			$ventasAsociados = ModeloVentas::mdlMostrarVentas("ventas", "id_cliente", $datos);

			if (!empty($ventasAsociados)) {
				if (isset($_POST["idClienteEliminar"])) {
					return "error_ventas";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El cliente tiene ventas asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "' . $ruta . '";
					});
				</script>';
				return;
			}

			// Verificar si hay notas crédito asociadas
			// Usamos ModeloFactus que es el que gestiona notas_credito en este proyecto
			$notasAsociadas = ModeloFactus::mdlMostrarNotasCredito("notas_credito", "id_cliente", $datos);

			if (!empty($notasAsociadas)) {
				if (isset($_POST["idClienteEliminar"])) {
					return "error_notas_credito";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El cliente tiene notas crédito asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "' . $ruta . '";
					});
				</script>';
				return;
			}


			$respuesta = ModeloClientes::mdlEliminarCliente($tabla, $datos);

			if ($respuesta == "ok") {
				if (isset($_POST["idClienteEliminar"])) {
					return "ok";
				}
				echo '<script>
					swal({
						type: "success",
						title: "¡El cliente ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {

								window.location = "' . $ruta . '";
						});
				</script>';
			}

		}
	}


	/*=============================================
	IMPORTAR CLIENTES DESDE CSV
	=============================================*/
	static public function ctrImportarClientes()
	{

		if (isset($_FILES["archivoCSV"])) {

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
							window.location = "clientes";
						}
					})
				</script>';
				return;
			}

			$archivo = $_FILES["archivoCSV"]["tmp_name"];
			$errores = array();
			$clientesImportar = array();

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

				$numeroFila = 1; // Contador de fila

				// Leer cada línea del CSV
				while (($datos = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
					$numeroFila++;

					// Saltar filas vacías
					if (empty(array_filter($datos))) {
						continue;
					}

					// Validar que la fila tenga al menos 11 columnas
					if (count($datos) < 11) {
						$errores[] = "Fila $numeroFila: Faltan columnas (se requieren 11, encontradas " . count($datos) . ")";
						continue;
					}

					$stringTipoPersona = trim($datos[0]);
					$stringTipoDoc = trim($datos[1]);
					$dv = trim($datos[2]);
					$documento = trim($datos[3]);
					$nombre = trim($datos[4]);
					$email = trim($datos[5]);
					$telefono = trim($datos[6]);
					$stringMunicipio = trim($datos[7]);
					$direccion = trim($datos[8]);
					$fechaNacimiento = trim($datos[9]);
					$notas = trim($datos[10]);

					// Validar campos obligatorios
					if (empty($documento) || empty($nombre) || empty($stringTipoPersona) || empty($stringTipoDoc)) {
						$errores[] = "Fila $numeroFila: Campos obligatorios vacíos (tipo persona, documento, nombre, tipo doc)";
						continue;
					}

					// Mapear tipo de persona
					$tipoPersona = self::mapearTipoPersona($stringTipoPersona);
					if (!$tipoPersona) {
						$errores[] = "Fila $numeroFila: Tipo de persona '$stringTipoPersona' no reconocido. Use 'Persona natural' o 'Persona juridica'.";
						continue;
					}

					// Mapear tipo de documento
					$tipoDocId = self::mapearTipoDocumento($stringTipoDoc);
					if (!$tipoDocId) {
						$errores[] = "Fila $numeroFila: Tipo de documento '$stringTipoDoc' no reconocido. Use CC, CE, DE, NIT, NUIP, PA.";
						continue;
					}

					// Validación: Juridica debe ser NIT
					if ($tipoPersona == "juridica" && $tipoDocId != 6) {
						$errores[] = "Fila $numeroFila: Para Persona jurídica el tipo de documento debe ser NIT.";
						continue;
					}

					// Cálculo de DV para NIT si está vacío
					if ($tipoDocId == 6 && empty($dv)) {
						require_once "modelos/factus.modelo.php";
						$dv = ModeloFactus::mdlCalcularDV($documento);
					}

					// Mapear municipio
					$idMunicipio = null;
					if (!empty($stringMunicipio)) {
						$idMunicipio = self::mapearMunicipio($stringMunicipio);
						if (!$idMunicipio) {
							$errores[] = "Fila $numeroFila: Municipio '$stringMunicipio' no encontrado. Use el formato 'Municipio - Departamento'.";
							continue;
						}
					}

					// Valores predeterminados y lógica condicional
					$estatus = "nuevo";
					$regimen = "simplificado";
					$responsabilidades = ($tipoPersona == "natural") ? "R-99-PN" : "ZY";

					$clientesImportar[] = array(
						"nombre" => $nombre,
						"documento" => $documento,
						"email" => $email,
						"telefono" => $telefono,
						"departamento" => null,
						"ciudad" => null,
						"direccion" => $direccion,
						"estatus" => $estatus,
						"notas" => $notas,
						"fecha_nacimiento" => !empty($fechaNacimiento) ? self::normalizarFecha($fechaNacimiento) : null,
						"tipo_documento_id" => $tipoDocId,
						"digito_verificacion" => $dv,
						"tipo_persona" => $tipoPersona,
						"regimen_tributario" => $regimen,
						"responsabilidades_fiscales" => $responsabilidades,
						"municipio_id" => $idMunicipio,
						"codigo_postal" => null,
						"nombre_comercial" => ($tipoPersona == "juridica") ? $nombre : null,
						"razon_social" => ($tipoPersona == "juridica") ? $nombre : null
					);
				}

				fclose($handle);
			}

			// Procesa la importación si no hay errores críticos de formato
			if (count($errores) > 0) {

				$mensajeErrores = '<ul><li>' . implode('</li><li>', $errores) . '</li></ul>';

				echo '<script>
					swal({
						type: "error",
						title: "Error en la importación",
						html: "' . $mensajeErrores . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "clientes";
					});
				</script>';

			} elseif (count($clientesImportar) > 0) {

				$respuesta = ModeloClientes::mdlImportarClientesMasivos("clientes", $clientesImportar);

				if ($respuesta["estado"] == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡Importación Exitosa!",
							text: "Se han importado ' . $respuesta["exitos"] . ' clientes correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "clientes";
						});
					</script>';

				} else {

					$mensajeParcial = "Se importaron " . $respuesta["exitos"] . " clientes, pero hubo errores en algunos.";
					if (!empty($respuesta["errores"])) {
						$mensajeParcial .= '<ul><li>' . implode('</li><li>', $respuesta["errores"]) . '</li></ul>';
					}

					echo '<script>
						swal({
							type: "warning",
							title: "Importación parcial",
							html: "' . $mensajeParcial . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "clientes";
						});
					</script>';
				}

			} else {
				echo '<script>
					swal({
						type: "warning",
						title: "Archivo vacío",
						text: "No se encontraron datos válidos para importar.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "clientes";
					});
				</script>';
			}
		}
	}

	/*=============================================
	MÉTODOS AUXILIARES DE MAPEO
	=============================================*/

	static private function mapearTipoPersona($string)
	{
		$string = strtolower(self::normalizarTextoImport($string));
		if (strpos($string, 'natural') !== false || $string == 'n') return "natural";
		if (strpos($string, 'juridica') !== false || $string == 'j' || strpos($string, 'empresa') !== false) return "juridica";
		return null;
	}

	static private function mapearTipoDocumento($string)
	{
		$string = strtoupper(trim($string));
		$map = [
			"CC" => 3,
			"CE" => 5,
			"DE" => 8,
			"NIT" => 6,
			"NUIP" => 9,
			"PA" => 7
		];
		return $map[$string] ?? null;
	}

	static private function mapearMunicipio($string)
	{
		// Formato esperado: Medellin - Antioquia
		$partes = explode('-', $string);
		if (count($partes) < 2) {
			$partes = explode(',', $string);
		}

		if (count($partes) < 2) return null;

		$nombreMun = trim(self::normalizarTextoImport($partes[0]));
		$nombreDep = trim(self::normalizarTextoImport($partes[1]));

		$db = Conexion::conectar();
		$stmt = $db->prepare("SELECT id_factus FROM factus_municipios WHERE (nombre LIKE :nombreMun) AND (departamento LIKE :nombreDep) LIMIT 1");
		$stmt->execute([
			':nombreMun' => '%' . $nombreMun . '%',
			':nombreDep' => '%' . $nombreDep . '%'
		]);
		$res = $stmt->fetch();
		return $res ? $res["id_factus"] : null;
	}

	static private function normalizarFecha($fecha)
	{
		if (empty($fecha)) return null;

		$fecha = trim($fecha);

		// Intentar YYYY-MM-DD
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
			return $fecha . " 00:00:00";
		}

		// Intentar DD/MM/YYYY
		if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) {
			return $matches[3] . "-" . $matches[2] . "-" . $matches[1] . " 00:00:00";
		}

		// Intentar DD-MM-YYYY
		if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $fecha, $matches)) {
			return $matches[3] . "-" . $matches[2] . "-" . $matches[1] . " 00:00:00";
		}

		// Si ya tiene el formato completo con hora YYYY-MM-DD HH:MM:SS
		if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
			return $fecha;
		}

		// Intentar con strtotime como último recurso
		$timestamp = strtotime($fecha);
		if ($timestamp) {
			return date("Y-m-d 00:00:00", $timestamp);
		}

		return null;
	}

	static private function normalizarTextoImport($texto)
	{
		$texto = strtolower($texto);
		$texto = str_replace(
			array('á', 'é', 'í', 'ó', 'ú', 'ñ'),
			array('a', 'e', 'i', 'o', 'u', 'n'),
			$texto
		);
		return $texto;
	}
}
