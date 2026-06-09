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
				$redireccionCsrf = puedeVer("clientes") ? "clientes" : "cliente-detalle";
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "' . $redireccionCsrf . '";
					})
				</script>';
				return;
			}

			$erroresValidacion = array();

			// Validaciones principales
			if (!isset($_POST["nuevoCliente"]) || !preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoCliente"])) {
				$erroresValidacion[] = "El campo <b>Nombre</b> es obligatorio y no debe llevar caracteres especiales.";
			}
			if (!isset($_POST["nuevoDocumentoId"]) || !preg_match('/^[0-9]+$/', $_POST["nuevoDocumentoId"])) {
				$erroresValidacion[] = "El campo <b>Número de Documento</b> es obligatorio y debe ser numérico.";
			}
			if (isset($_POST["nuevoEmail"]) && !empty($_POST["nuevoEmail"]) && !preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"])) {
				$erroresValidacion[] = "El campo <b>Email</b> no tiene un formato válido.";
			}
			if (!isset($_POST["nuevoTelefono"]) || !preg_match('/^[()\-0-9 ]+$/', $_POST["nuevoTelefono"])) {
				$erroresValidacion[] = "El campo <b>Teléfono</b> es obligatorio y solo debe llevar números, paréntesis o guiones.";
			}
			if (!isset($_POST["nuevaDireccion"]) || !preg_match('/^[#\.\-a-zA-Z0-9 ,]+$/', $_POST["nuevaDireccion"])) {
				$erroresValidacion[] = "El campo <b>Dirección</b> es obligatorio y no debe llevar caracteres especiales.";
			}

			// Validaciones adicionales (Factus / Detalle)
			if (isset($_POST["nuevoTipoPersona"]) && !in_array($_POST["nuevoTipoPersona"], ["natural", "juridica"])) {
				$erroresValidacion[] = "El campo <b>Tipo de Persona</b> seleccionado no es válido.";
			}

			$tipoPersona = isset($_POST["nuevoTipoPersona"]) ? $_POST["nuevoTipoPersona"] : "natural";
			if ($tipoPersona === "juridica") {
				if (!isset($_POST["nuevaRazonSocial"]) || empty(trim($_POST["nuevaRazonSocial"]))) {
					$erroresValidacion[] = "El campo <b>Razón Social</b> es obligatorio para personas jurídicas.";
				} elseif (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \.\-&,]+$/', $_POST["nuevaRazonSocial"])) {
					$erroresValidacion[] = "El campo <b>Razón Social</b> no debe llevar caracteres especiales.";
				}
			}

			if (isset($_POST["nuevoNombreComercial"]) && !empty($_POST["nuevoNombreComercial"]) && !preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \.\-&,]+$/', $_POST["nuevoNombreComercial"])) {
				$erroresValidacion[] = "El campo <b>Nombre Comercial</b> no debe llevar caracteres especiales.";
			}

			$tipoDocId = isset($_POST["nuevoTipoDocumento"]) ? $_POST["nuevoTipoDocumento"] : null;
			if ($tipoDocId == 6) {
				if (!isset($_POST["nuevoDigitoVerificacion"]) || $_POST["nuevoDigitoVerificacion"] === "") {
					$erroresValidacion[] = "El campo <b>Dígito de Verificación</b> es obligatorio cuando el tipo de documento es NIT.";
				} elseif (!preg_match('/^[0-9]$/', $_POST["nuevoDigitoVerificacion"])) {
					$erroresValidacion[] = "El campo <b>Dígito de Verificación</b> debe ser un único número de 0 a 9.";
				}
			}

			if (isset($_POST["nuevoMunicipio"]) && !empty($_POST["nuevoMunicipio"]) && !preg_match('/^[0-9]+$/', $_POST["nuevoMunicipio"])) {
				$erroresValidacion[] = "El campo <b>Municipio</b> seleccionado no es válido.";
			}

			if (isset($_POST["nuevoCodigoPostal"]) && !empty($_POST["nuevoCodigoPostal"]) && !preg_match('/^[0-9]+$/', $_POST["nuevoCodigoPostal"])) {
				$erroresValidacion[] = "El campo <b>Código Postal</b> debe ser numérico.";
			}

			if (isset($_POST["nuevasResponsabilidades"]) && !empty($_POST["nuevasResponsabilidades"])) {
				$validResp = ["R-99-PN", "O-13", "O-15", "O-23", "O-47", "ZY"];
				if (!in_array($_POST["nuevasResponsabilidades"], $validResp)) {
					$erroresValidacion[] = "El campo <b>Responsabilidades Fiscales</b> seleccionado no es válido.";
				}
			}

			if (empty($erroresValidacion)) {

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
					"responsabilidad_tributaria" => isset($_POST["nuevoResponsabilidadTributaria"]) ? $_POST["nuevoResponsabilidadTributaria"] : 'no_responsable',
					"codigo_postal" => isset($_POST["nuevoCodigoPostal"]) ? $_POST["nuevoCodigoPostal"] : null,
					"nombre_comercial" => isset($_POST["nuevoNombreComercial"]) ? $_POST["nuevoNombreComercial"] : null,
					"razon_social" => isset($_POST["nuevaRazonSocial"]) ? $_POST["nuevaRazonSocial"] : null
				);

				$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					// Determinar a dónde redirigir según el origen
					$redireccion = isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes";
					if ($redireccion === "clientes" && !puedeVer("clientes")) {
						$redireccion = "cliente-detalle";
					}

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
				} else {

					// GUARDAR DATOS EN SESION PARA PERSISTENCIA
					$_SESSION["datos_cliente_error"] = $_POST;

					// Determinar a dónde redirigir según la URL actual o el origen
					$redireccion = isset($_POST["urlActual"]) ? $_POST["urlActual"] : (isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes");
					if ($redireccion === "clientes" && !puedeVer("clientes")) {
						$redireccion = "cliente-detalle";
					}

					$mensajeErrorDB = "Ocurrió un error inesperado al guardar el cliente en la base de datos.";
					if ($respuesta == "error_duplicado") {
						$mensajeErrorDB = "El número de documento o el teléfono ingresado ya se encuentra registrado.";
					}

					echo '<script>
						swal({
							type: "error",
							title: "Error al guardar",
							text: "' . $mensajeErrorDB . '",
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
				if ($redireccion === "clientes" && !puedeVer("clientes")) {
					$redireccion = "cliente-detalle";
				}

				$mensajeErrores = '<ul><li>' . implode('</li><li>', $erroresValidacion) . '</li></ul>';

				echo '<script>
					swal({
						type: "error",
						title: "Error de validación",
						html: "' . $mensajeErrores . '",
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

		if ($item === "id" && $respuesta === false) {
			return array(
				"id" => null,
				"nombre" => "Cliente Eliminado",
				"documento" => "00000000",
				"email" => "",
				"telefono" => "",
				"direccion" => "",
				"estatus" => "Ninguno"
			);
		}

		return $respuesta;
	}

	/*=============================================
	MOSTRAR CLIENTES SERVER-SIDE
	=============================================*/
	static public function ctrMostrarClientesServerSide($params)
	{
		$tabla = "clientes";

		// Columnas para ordenar (coinciden con la vista)
		$columnsMap = array(
			0 => 'nombre',
			1 => 'documento',
			2 => 'email',
			3 => 'telefono',
			4 => 'direccion',
			5 => 'estatus',
			6 => 'notas',
			7 => 'id'
		);

		$where = " WHERE 1=1 ";

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (c.nombre LIKE '%$searchValue%' OR c.documento LIKE '%$searchValue%' OR c.email LIKE '%$searchValue%' OR c.telefono LIKE '%$searchValue%' OR c.direccion LIKE '%$searchValue%' OR c.estatus LIKE '%$searchValue%' OR c.notas LIKE '%$searchValue%') ";
		}

		// Filtro por Estado (filtroEstatus1) pasado desde el request adicional si existe
		if (isset($_POST['filtroEstatus1']) && !empty($_POST['filtroEstatus1'])) {
			$estatusFilter = $_POST['filtroEstatus1'];
			$where .= " AND c.estatus = '$estatusFilter' ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? 'c.' . $columnsMap[$colIdx] : 'c.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY c.id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$clientes = ModeloClientes::mdlMostrarClientesServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloClientes::mdlGetTotalClientes($tabla, " WHERE 1=1 ");
		$totalFiltered = ModeloClientes::mdlGetTotalClientes($tabla, $where);

		$data = array();

		// Pre-fetch estados para los badges de colores
		require_once "../controladores/estados-clientes.controlador.php";
		require_once "../modelos/estados-clientes.modelo.php";
		$estadosDisponibles = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);

		foreach ($clientes as $key => $value) {
			
			$nestedData = array();
			
			// Añadir ID como atributo TR es necesario para la vista actual
			$nestedData['DT_RowAttr'] = array(
				'data-cliente-id' => $value['id']
			);

			// 0: Nombre
			$nestedData[] = e($value["nombre"]);

			// 1: Documento (con DV si es NIT)
			$documentoDisplay = e($value["documento"]);
			if (!empty($value["tipo_documento_id"]) && $value["tipo_documento_id"] == 6
				&& isset($value["digito_verificacion"]) && $value["digito_verificacion"] !== '') {
				$documentoDisplay .= '-' . e($value["digito_verificacion"]);
			}
			$nestedData[] = $documentoDisplay;

			// 2: Email
			$nestedData[] = e($value["email"]);

			// 3: Teléfono
			$nestedData[] = e($value["telefono"]);

			// 4: Dirección
			$nestedData[] = e($value["direccion"]);

			// 5: Estado (Badge color)
			$estatus = $value["estatus"] ?? "";
			$colorEstado = "#999"; 
			foreach ($estadosDisponibles as $estado) {
				if (strcasecmp($estado["nombre"], $estatus) == 0) {
					$colorEstado = $estado["color"];
					break;
				}
			}
			if (!empty($estatus)) {
				$nestedData[] = '<span class="badge" style="background-color: ' . $colorEstado . '">' . ucfirst($estatus) . '</span>';
			} else {
				$nestedData[] = '<span class="text-muted">Sin estado</span>';
			}

			// 6: Notas (Editable)
			$notas = trim($value["notas"] ?? "");
			$nestedData[] = '<div contenteditable="true" class="celda-notas" tabindex="0" data-id="' . $value['id'] . '">' . e($notas) . '</div>';

			// 7: Última compra
			$nestedData[] = e($value["ultima_compra"]);

			// 8: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('clientes', 'editar')) {
				$botonesAcciones .= '<a href="cliente-detalle?id=' . $value["id"] . '" class="btn btn-warning" title="Editar cliente"><i class="fa fa-pencil"></i></a>';
			}
			if (puedeAccion('clientes', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarCliente" idCliente="' . $value["id"] . '" title="Eliminar cliente"><i class="fa fa-times"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			// 9: Ingreso al sistema
			$nestedData[] = e($value["fecha"]);

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
	EDITAR CLIENTES
	=============================================*/

	static public function ctrEditarCliente()
	{

		if (isset($_POST["editarCliente"])) {

			/*=============================================
			VALIDAR CSRF
			=============================================*/
			if (!CSRF::validateToken()) {
				$redireccionCsrf = puedeVer("clientes") ? "clientes" : "cliente-detalle";
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "' . $redireccionCsrf . '";
					})
				</script>';
				return;
			}


			$erroresValidacion = array();

			// Validaciones principales
			if (!isset($_POST["editarCliente"]) || !preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCliente"])) {
				$erroresValidacion[] = "El campo <b>Nombre</b> es obligatorio y no debe llevar caracteres especiales.";
			}
			if (!isset($_POST["editarDocumentoId"]) || !preg_match('/^[0-9]+$/', $_POST["editarDocumentoId"])) {
				$erroresValidacion[] = "El campo <b>Número de Documento</b> es obligatorio y debe ser numérico.";
			}
			if (isset($_POST["editarEmail"]) && !empty($_POST["editarEmail"]) && !preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"])) {
				$erroresValidacion[] = "El campo <b>Email</b> no tiene un formato válido.";
			}
			if (!isset($_POST["editarTelefono"]) || !preg_match('/^[()\-0-9 ]+$/', $_POST["editarTelefono"])) {
				$erroresValidacion[] = "El campo <b>Teléfono</b> es obligatorio y solo debe llevar números, paréntesis o guiones.";
			}
			if (!isset($_POST["editarDireccion"]) || !preg_match('/^[#\.\-a-zA-Z0-9 ,]+$/', $_POST["editarDireccion"])) {
				$erroresValidacion[] = "El campo <b>Dirección</b> es obligatorio y no debe llevar caracteres especiales.";
			}

			// Validaciones adicionales (Factus / Detalle)
			if (isset($_POST["editarTipoPersona"]) && !in_array($_POST["editarTipoPersona"], ["natural", "juridica"])) {
				$erroresValidacion[] = "El campo <b>Tipo de Persona</b> seleccionado no es válido.";
			}

			$tipoPersona = isset($_POST["editarTipoPersona"]) ? $_POST["editarTipoPersona"] : "natural";
			if ($tipoPersona === "juridica") {
				if (!isset($_POST["editarRazonSocial"]) || empty(trim($_POST["editarRazonSocial"]))) {
					$erroresValidacion[] = "El campo <b>Razón Social</b> es obligatorio para personas jurídicas.";
				} elseif (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \.\-&,]+$/', $_POST["editarRazonSocial"])) {
					$erroresValidacion[] = "El campo <b>Razón Social</b> no debe llevar caracteres especiales.";
				}
			}

			if (isset($_POST["editarNombreComercial"]) && !empty($_POST["editarNombreComercial"]) && !preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ \.\-&,]+$/', $_POST["editarNombreComercial"])) {
				$erroresValidacion[] = "El campo <b>Nombre Comercial</b> no debe llevar caracteres especiales.";
			}

			$tipoDocId = isset($_POST["editarTipoDocumento"]) ? $_POST["editarTipoDocumento"] : null;
			if ($tipoDocId == 6) {
				if (!isset($_POST["editarDigitoVerificacion"]) || $_POST["editarDigitoVerificacion"] === "") {
					$erroresValidacion[] = "El campo <b>Dígito de Verificación</b> es obligatorio cuando el tipo de documento es NIT.";
				} elseif (!preg_match('/^[0-9]$/', $_POST["editarDigitoVerificacion"])) {
					$erroresValidacion[] = "El campo <b>Dígito de Verificación</b> debe ser un único número de 0 a 9.";
				}
			}

			if (isset($_POST["editarMunicipio"]) && !empty($_POST["editarMunicipio"]) && !preg_match('/^[0-9]+$/', $_POST["editarMunicipio"])) {
				$erroresValidacion[] = "El campo <b>Municipio</b> seleccionado no es válido.";
			}

			if (isset($_POST["editarCodigoPostal"]) && !empty($_POST["editarCodigoPostal"]) && !preg_match('/^[0-9]+$/', $_POST["editarCodigoPostal"])) {
				$erroresValidacion[] = "El campo <b>Código Postal</b> debe ser numérico.";
			}

			if (isset($_POST["editarResponsabilidades"]) && !empty($_POST["editarResponsabilidades"])) {
				$validResp = ["R-99-PN", "O-13", "O-15", "O-23", "O-47", "ZY"];
				if (!in_array($_POST["editarResponsabilidades"], $validResp)) {
					$erroresValidacion[] = "El campo <b>Responsabilidades Fiscales</b> seleccionado no es válido.";
				}
			}

			if (empty($erroresValidacion)) {

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
					"responsabilidad_tributaria" => isset($_POST["editarResponsabilidadTributaria"]) ? $_POST["editarResponsabilidadTributaria"] : 'no_responsable',
					"codigo_postal" => isset($_POST["editarCodigoPostal"]) ? $_POST["editarCodigoPostal"] : null,
					"nombre_comercial" => isset($_POST["editarNombreComercial"]) ? $_POST["editarNombreComercial"] : null,
					"razon_social" => isset($_POST["editarRazonSocial"]) ? $_POST["editarRazonSocial"] : null
				);

				$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					// Determinar a dónde redirigir según el origen
					$redireccion = isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes";
					if ($redireccion === "clientes" && !puedeVer("clientes")) {
						$redireccion = "cliente-detalle";
					}

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
				} else {

					// GUARDAR DATOS EN SESION PARA PERSISTENCIA
					$_SESSION["datos_cliente_error"] = $_POST;

					// Determinar a dónde redirigir según la URL actual o el origen
					$redireccion = isset($_POST["urlActual"]) ? $_POST["urlActual"] : (isset($_POST["vistaOrigen"]) ? $_POST["vistaOrigen"] : "clientes");
					if ($redireccion === "clientes" && !puedeVer("clientes")) {
						$redireccion = "cliente-detalle";
					}

					$mensajeErrorDB = "Ocurrió un error inesperado al guardar el cliente en la base de datos.";
					if ($respuesta == "error_duplicado") {
						$mensajeErrorDB = "El número de documento o el teléfono ingresado ya se encuentra registrado.";
					}

					echo '<script>
						swal({
							type: "error",
							title: "Error al guardar",
							text: "' . $mensajeErrorDB . '",
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
				if ($redireccion === "clientes" && !puedeVer("clientes")) {
					$redireccion = "cliente-detalle";
				}

				$mensajeErrores = '<ul><li>' . implode('</li><li>', $erroresValidacion) . '</li></ul>';

				echo '<script>
					swal({
						type: "error",
						title: "Error de validación",
						html: "' . $mensajeErrores . '",
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
			$idCliente = isset($_GET["idCliente"]) ? $_GET["idCliente"] : $_POST["idClienteEliminar"];

			// Detectar de dónde viene la eliminación
			$ruta = isset($_GET["ruta"]) ? $_GET["ruta"] : (isset($_POST["ruta"]) ? $_POST["ruta"] : "clientes");

			// ── Validaciones pre-transacción (solo lectura) ──────────────────



			// ── Eliminación atómica ──────────────────────────────────────────
			$db = Conexion::conectar();

			try {
				$db->beginTransaction();

				$respuesta = ModeloClientes::mdlEliminarCliente($tabla, $idCliente);

				if ($respuesta !== "ok") {
					throw new Exception("Error al eliminar el cliente de la base de datos.");
				}

				$db->commit();

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

			} catch (Exception $e) {
				$db->rollBack();
				Logger::error("Error en ctrEliminarCliente ID $idCliente: " . $e->getMessage());

				if (isset($_POST["idClienteEliminar"])) {
					return "error";
				}

				echo '<script>
					swal({
						type: "error",
						title: "Error al eliminar",
						text: "No se pudo eliminar el cliente. Intenta de nuevo.",
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
			$clientesInsertar = array();
			$clientesActualizar = array();

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

					// Formatear teléfono a (XXX) XXX-XXXX si tiene 10 dígitos (o 12 con prefijo 57)
					$telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
					if (strlen($telefonoLimpio) === 12 && substr($telefonoLimpio, 0, 2) === '57') {
						$telefonoLimpio = substr($telefonoLimpio, 2);
					}
					if (strlen($telefonoLimpio) === 10) {
						$telefono = '(' . substr($telefonoLimpio, 0, 3) . ') ' . substr($telefonoLimpio, 3, 3) . '-' . substr($telefonoLimpio, 6);
					}
					$stringMunicipio = trim($datos[7]);
					$direccion = trim($datos[8]);
					$fechaNacimiento = trim($datos[9]);
					$notas = trim($datos[10]);

					// Validar campos obligatorios
					if (empty($documento) || empty($nombre) || empty($stringTipoPersona) || empty($stringTipoDoc) || $direccion === "" || $stringMunicipio === "" || $telefono === "") {
						$errores[] = "Fila $numeroFila: Campos obligatorios vacíos (tipo persona, tipo doc, documento, nombre, teléfono, dirección, municipio)";
						continue;
					}

					// Validar formato de correo electrónico si se proporciona
					if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$errores[] = "Fila $numeroFila: El correo electrónico '$email' no tiene un formato válido.";
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
						$errores[] = "Fila $numeroFila: Tipo de documento '$stringTipoDoc' no reconocido. Use CC, CE, DE, NIT, NUIP, PA, PASAPORTE, PEP, RC, TE, TI.";
						continue;
					}

					// Validación: Juridica debe ser NIT
					if ($tipoPersona == "juridica" && $tipoDocId != 6) {
						$errores[] = "Fila $numeroFila: Para Persona jurídica el tipo de documento debe ser NIT.";
						continue;
					}

					// Validación: Dígito de verificación obligatorio para NIT y debe ser un único número (0-9)
					if ($tipoDocId == 6 && $dv === "") {
						$errores[] = "Fila $numeroFila: El dígito de verificación es obligatorio cuando el tipo de documento es NIT.";
						continue;
					}
					if ($dv !== "" && !preg_match('/^[0-9]$/', $dv)) {
						$errores[] = "Fila $numeroFila: El dígito de verificación debe ser un único número de 0 a 9.";
						continue;
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

					$datosCliente = array(
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

					// Verificar si el cliente ya existe por documento
					$clienteExiste = ModeloClientes::mdlMostrarClientes("clientes", "documento", $documento);

					if ($clienteExiste) {
						$datosCliente["id"] = $clienteExiste["id"];
						$clientesActualizar[$documento] = $datosCliente;
						if (isset($clientesInsertar[$documento])) {
							unset($clientesInsertar[$documento]);
						}
					} else {
						$clientesInsertar[$documento] = $datosCliente;
						if (isset($clientesActualizar[$documento])) {
							unset($clientesActualizar[$documento]);
						}
					}
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

			} elseif (count($clientesInsertar) > 0 || count($clientesActualizar) > 0) {

				// Convertir a arreglos indexados numéricamente
				$clientesInsertar = array_values($clientesInsertar);
				$clientesActualizar = array_values($clientesActualizar);

				$respuesta = ModeloClientes::mdlImportarClientesMasivos("clientes", $clientesInsertar, $clientesActualizar);

				if ($respuesta["estado"] == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡Importación Exitosa!",
							text: "Se han ingresado ' . $respuesta["ingresados"] . ' clientes y actualizado ' . $respuesta["actualizados"] . ' correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "clientes";
						});
					</script>';

				} else {

					$mensajeParcial = "Se han ingresado " . $respuesta["ingresados"] . " clientes y actualizado " . $respuesta["actualizados"] . " correctamente, pero se presentaron algunos errores.";
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
			"NUIP" => 11,
			"PA" => 7,
			"PASAPORTE" => 7,
			"PEP" => 9,
			"RC" => 1,
			"TE" => 4,
			"TI" => 2
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
