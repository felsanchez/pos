<?php

class ControladorFactus
{

	/*=============================================
	OBTENER CONFIGURACIÓN DE FACTUS
	=============================================*/
	static public function ctrObtenerConfiguracion()
	{
		$respuesta = ModeloFactus::mdlObtenerConfiguracion();
		return $respuesta;
	}

	/*=============================================
	ACTUALIZAR CONFIGURACIÓN DE FACTUS
	=============================================*/
	static public function ctrActualizarConfiguracion()
	{
		if (isset($_POST["apiUrl"])) {

			// Procesar checkbox de activo
			$activo = isset($_POST["factusActivo"]) ? 1 : 0;

			// 0. Obtener configuración actual para mantener datos de la empresa si no vienen en el POST
			$configConfig = ModeloFactus::mdlObtenerConfiguracion();

			$datos = array(
				"api_url" => $_POST["apiUrl"],
				"client_id" => $_POST["clientId"],
				"client_secret" => $_POST["clientSecret"],
				"username" => isset($_POST["username"]) ? $_POST["username"] : null,
				"password" => isset($_POST["password"]) ? $_POST["password"] : null,
				"ambiente" => $_POST["ambiente"],
				"activo" => $activo,
				"rango_numeracion_id" => !empty($_POST["rangoNumeracionId"]) ? $_POST["rangoNumeracionId"] : null,
				// Preserve existing company data if not in POST (configuracion-factus.php doesn't send them)
				"nombre_empresa" => isset($_POST["nombrefactus"]) ? $_POST["nombrefactus"] : $configConfig['nombre_empresa'],
				"nit_empresa" => isset($_POST["nitfactus"]) ? $_POST["nitfactus"] : $configConfig['nit_empresa'],
				"direccion_empresa" => isset($_POST["direccionfactus"]) ? $_POST["direccionfactus"] : $configConfig['direccion_empresa'],
				"telefono_empresa" => isset($_POST["telefonofactus"]) ? $_POST["telefonofactus"] : $configConfig['telefono_empresa'],
				"email_empresa" => isset($_POST["emailfactus"]) ? $_POST["emailfactus"] : $configConfig['email_empresa'],
				"municipio_id" => isset($_POST["municipiofactus"]) ? $_POST["municipiofactus"] : $configConfig['municipio_id'],

				// Preserve extended fields
				"tributo_emisor" => isset($_POST["tributofactus"]) ? $_POST["tributofactus"] : $configConfig['tributo_emisor'],
				"actividad_economica" => isset($_POST["actividadfactus"]) ? $_POST["actividadfactus"] : $configConfig['actividad_economica'],
				"registro_mercantil" => isset($_POST["registrofactus"]) ? $_POST["registrofactus"] : $configConfig['registro_mercantil'],
				"dv" => isset($_POST["dvfactus"]) ? $_POST["dvfactus"] : $configConfig['dv'],
				"responsabilidades_fiscales" => isset($_POST["responsabilidadesfactus"]) ? json_encode($_POST["responsabilidadesfactus"]) : $configConfig['responsabilidades_fiscales'],
				"tipo_persona" => isset($_POST["tipopersonafactus"]) ? $_POST["tipopersonafactus"] : $configConfig['tipo_persona'],

				"bloqueo_datos_emisor" => isset($_POST["habilitarEdicionFactusGlobal"]) ? 0 : 1 // Checkbox checked = 0 (Desbloqueado)
			);

			$respuesta = ModeloFactus::mdlActualizarConfiguracion($datos);

			if ($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "La configuración de Factus ha sido actualizada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result) {
						if (result.value) {
							window.location = "configuracion-factus";
						}
					})
				</script>';
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "Error al actualizar la configuración",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					})
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR TIPOS DE DOCUMENTO
	=============================================*/
	static public function ctrMostrarTiposDocumento()
	{
		return ModeloFactus::mdlMostrarTiposDocumento();
	}

	/*=============================================
AUTENTICAR CON FACTUS API
=============================================*/
	static public function ctrAutenticar()
	{
		// 🔹 PRIMERO: Intentar usar el token guardado en la BD
		$tokenValido = ModeloFactus::mdlGarantizarTokenValido();

		if ($tokenValido) {
			// Obtener el token real (string) de la BD
			$accessToken = ModeloFactus::mdlObtenerAccessToken();

			return array(
				"error" => false,
				"mensaje" => "Token válido obtenido de la base de datos",
				"token" => $accessToken
			);
		}

		// 🔴 Si no hay token válido, retornar error
		// Ya no intentamos autenticar aquí porque eso debe hacerse manualmente
		// desde la página de configuración usando el botón "Autenticar"
		return array(
			"error" => true,
			"mensaje" => "No hay un token válido. Por favor, autentíquese desde Configuración > Factus."
		);
	}

	/*=============================================
	SINCRONIZAR MUNICIPIOS
	=============================================*/
	static public function ctrSincronizarMunicipios()
	{
		$auth = self::ctrAutenticar();

		if ($auth['error']) {
			return $auth;
		}

		$municipios = ModeloFactus::mdlConsultarMunicipiosAPI($auth['token']);

		if (is_null($municipios)) {
			return array(
				"error" => true,
				"mensaje" => "Error al consultar municipios en la API"
			);
		}

		$resultado = ModeloFactus::mdlGuardarMunicipios($municipios);

		// Registrar log de sincronización
		ModeloFactus::mdlRegistrarSincronizacion(array(
			"tipo_dato" => "municipios",
			"insertados" => $resultado['insertados'],
			"actualizados" => $resultado['actualizados'],
			"estado" => "Exitoso",
			"mensaje" => "Se descargaron " . count($municipios) . " municipios",
			"usuario_id" => $_SESSION['id'] ?? 1
		));

		return array(
			"error" => false,
			"mensaje" => "Municipios sincronizados correctamente",
			"insertados" => $resultado['insertados'],
			"actualizados" => $resultado['actualizados']
		);
	}

	/*=============================================
	SINCRONIZAR UNIDADES DE MEDIDA
	=============================================*/
	static public function ctrSincronizarUnidades()
	{
		$auth = self::ctrAutenticar();

		if ($auth['error']) {
			return $auth;
		}

		$unidades = ModeloFactus::mdlConsultarUnidadesAPI($auth['token']);

		if (is_null($unidades)) {
			return array(
				"error" => true,
				"mensaje" => "Error al consultar unidades de medida en la API"
			);
		}

		try {
			$resultado = ModeloFactus::mdlGuardarUnidadesMedida($unidades);

			// Registrar log de sincronización
			ModeloFactus::mdlRegistrarSincronizacion(array(
				"tipo_dato" => "unidades",
				"insertados" => $resultado['insertados'],
				"actualizados" => $resultado['actualizados'],
				"estado" => "Exitoso",
				"mensaje" => "Se descargaron " . count($unidades) . " unidades",
				"usuario_id" => $_SESSION['id'] ?? 1
			));

			return array(
				"error" => false,
				"mensaje" => "Unidades de medida sincronizadas correctamente",
				"insertados" => $resultado['insertados'],
				"actualizados" => $resultado['actualizados']
			);
		} catch (Exception $e) {
			return array(
				"error" => true,
				"mensaje" => "Error al guardar unidades: " . $e->getMessage()
			);
		}
	}

	/*=============================================
	SINCRONIZAR RANGOS DE NUMERACIÓN
	=============================================*/
	static public function ctrSincronizarRangos()
	{
		$auth = self::ctrAutenticar();

		if ($auth['error']) {
			return $auth;
		}

		$rangos = ModeloFactus::mdlConsultarRangosAPI($auth['token']);

		if (is_null($rangos)) {
			return array(
				"error" => true,
				"mensaje" => "Error al consultar rangos de numeración en la API"
			);
		}

		try {
			$resultado = ModeloFactus::mdlGuardarRangos($rangos);

			// Registrar log e sincronización
			ModeloFactus::mdlRegistrarSincronizacion(array(
				"tipo_dato" => "rangos",
				"insertados" => $resultado['insertados'],
				"actualizados" => $resultado['actualizados'],
				"estado" => "Exitoso",
				"mensaje" => "Se descargaron " . count($rangos) . " rangos",
				"usuario_id" => $_SESSION['id'] ?? 1
			));

			return array(
				"error" => false,
				"mensaje" => "Rangos de numeración sincronizados correctamente",
				"insertados" => $resultado['insertados'],
				"actualizados" => $resultado['actualizados']
			);
		} catch (Exception $e) {
			return array(
				"error" => true,
				"mensaje" => "Error al guardar rangos: " . $e->getMessage()
			);
		}
	}

	/*=============================================
	OBTENER TOKEN VÁLIDO (REFRESCA SI ES NECESARIO)
	=============================================*/
	static public function ctrObtenerToken()
	{
		// Verificar si el token ha expirado
		if (ModeloFactus::mdlTokenExpirado()) {
			// Intentar refrescar el token
			$resultado = self::ctrAutenticar();
			if ($resultado['error']) {
				return null;
			}
			return $resultado['token'];
		}

		return ModeloFactus::mdlObtenerAccessToken();
	}

	/*=============================================
	PROBAR CONEXIÓN CON FACTUS
	=============================================*/
	static public function ctrProbarConexion()
	{
		$resultado = self::ctrAutenticar();
		return $resultado;
	}



	/*=============================================
	SINCRONIZAR TRIBUTOS (LISTA ESTÁTICA DIAN)
	=============================================*/
	static public function ctrSincronizarTributos()
	{
		// Como el endpoint /v1/tributes retorna 404, usamos los códigos estándar DIAN
		$tributos = [
			["codigo" => "01", "nombre" => "IVA", "descripcion" => "Impuesto al Valor Agregado", "porcentaje" => 19.00],
			["codigo" => "04", "nombre" => "INC", "descripcion" => "Impuesto Nacional al Consumo", "porcentaje" => 8.00],
			["codigo" => "03", "nombre" => "ICA", "descripcion" => "Impuesto de Industria y Comercio", "porcentaje" => 0.00],
			["codigo" => "22", "nombre" => "Bolsas", "descripcion" => "Impuesto al consumo de bolsas plásticas", "porcentaje" => 0.00],
			["codigo" => "ZA", "nombre" => "IVA Excluido", "descripcion" => "Bienes o servicios excluidos de IVA", "porcentaje" => 0.00]
		];

		try {
			$resultado = ModeloFactus::mdlGuardarTributos($tributos);

			// Registrar log de sincronización
			ModeloFactus::mdlRegistrarSincronizacion(array(
				"tipo_dato" => "tributos",
				"insertados" => $resultado['insertados'],
				"actualizados" => $resultado['actualizados'],
				"estado" => "Exitoso",
				"mensaje" => "Se insertaron/actualizaron " . count($tributos) . " tributos estándar",
				"usuario_id" => $_SESSION['id'] ?? 1
			));

			return array(
				"error" => false,
				"mensaje" => "Tributos estándar sincronizados correctamente",
				"insertados" => $resultado['insertados'],
				"actualizados" => $resultado['actualizados']
			);

		} catch (Exception $e) {
			return array(
				"error" => true,
				"mensaje" => "Error al guardar tributos: " . $e->getMessage()
			);
		}
	}

	/*=============================================
	OBTENER ESTADO DE SINCRONIZACIONES
	=============================================*/
	static public function ctrObtenerEstadoSincronizaciones()
	{
		return array(
			"municipios" => ModeloFactus::mdlObtenerUltimaSincronizacion("municipios"),
			"tributos" => ModeloFactus::mdlObtenerUltimaSincronizacion("tributos"),
			"unidades" => ModeloFactus::mdlObtenerUltimaSincronizacion("unidades"),
			"rangos" => ModeloFactus::mdlObtenerUltimaSincronizacion("rangos")
		);
	}

	/*=============================================
	VALIDAR DATOS DE FACTURA ANTES DE ENVIAR
	=============================================*/
	static public function ctrValidarDatosFactura($venta)
	{
		$errores = [];

		// 1. Validar cliente
		$erroresCliente = self::validarCliente($venta['id_cliente']);
		$errores = array_merge($errores, $erroresCliente);

		// 2. Validar productos
		$erroresProductos = self::validarProductos($venta['productos']);
		$errores = array_merge($errores, $erroresProductos);

		// 3. Validar configuración
		$erroresConfig = self::validarConfiguracion();
		$errores = array_merge($errores, $erroresConfig);

		return [
			'valido' => empty($errores),
			'errores' => $errores
		];
	}

	/*=============================================
	VALIDAR CLIENTE
	=============================================*/
	static private function validarCliente($idCliente)
	{
		$errores = [];
		require_once __DIR__ . "/../modelos/clientes.modelo.php";
		$cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $idCliente);

		if (!$cliente) {
			$errores[] = "Cliente no encontrado";
			return $errores;
		}

		// Validar campos requeridos
		if (empty($cliente['documento'])) {
			$errores[] = "El cliente no tiene documento de identificación";
		}

		if (empty($cliente['nombre'])) {
			$errores[] = "El cliente no tiene nombre registrado";
		}

		if (empty($cliente['direccion'])) {
			$errores[] = "El cliente no tiene dirección registrada";
		}

		if (empty($cliente['email']) || !filter_var($cliente['email'], FILTER_VALIDATE_EMAIL)) {
			$errores[] = "El cliente no tiene un email válido";
		}

		if (empty($cliente['telefono'])) {
			$errores[] = "El cliente no tiene teléfono registrado";
		}

		// Validar municipio
		if (!empty($cliente['municipio_id'])) {
			// Buscar por id_factus (el cliente guarda id_factus en municipio_id)
			$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_municipios WHERE id_factus = :id_factus LIMIT 1");
			$stmt->execute([':id_factus' => $cliente['municipio_id']]);
			$municipio = $stmt->fetch();
			if (!$municipio) {
				$errores[] = "El municipio del cliente (ID Factus: {$cliente['municipio_id']}) no es válido para facturación electrónica. Actualice el municipio del cliente.";
			}
		} else {
			$errores[] = "El cliente no tiene municipio asignado";
		}

		return $errores;
	}

	/*=============================================
	VALIDAR PRODUCTOS
	=============================================*/
	static private function validarProductos($productosJson)
	{
		$errores = [];
		require_once __DIR__ . "/../modelos/productos.modelo.php";
		$productos = json_decode($productosJson, true);

		if (empty($productos) || !is_array($productos)) {
			$errores[] = "No hay productos en la venta";
			return $errores;
		}

		foreach ($productos as $index => $productoVenta) {
			$productoBD = ModeloProductos::mdlMostrarProductos("productos", "id", $productoVenta['id'], "id");

			if (!$productoBD) {
				$errores[] = "Producto #" . ($index + 1) . " no encontrado en la base de datos";
				continue;
			}

			$nombre = $productoVenta['descripcion'];

			// Validar tributo
			if (empty($productoBD['tributo_id'])) {
				$errores[] = "El producto '$nombre' no tiene tributo asignado. Configure el tributo en la ficha del producto.";
				continue;
			}

			$tributo = ModeloFactus::mdlMostrarTributo($productoBD['tributo_id']);
			if (!$tributo) {
				$errores[] = "El producto '$nombre' tiene un tributo inválido (ID: {$productoBD['tributo_id']})";
				continue;
			}

			// Validar coherencia tributo-impuesto
			$tasaEsperada = floatval($tributo['porcentaje_defecto']);
			$tasaProducto = floatval($productoVenta['impuesto'] ?? 0);

			if (abs($tasaEsperada - $tasaProducto) > 0.01) {
				$errores[] = "El producto '$nombre' tiene tributo '{$tributo['nombre']}' ({$tasaEsperada}%) pero se está cobrando {$tasaProducto}% de impuesto. Corrija el tributo en la ficha del producto.";
			}

			// Validar unidad de medida
			if (empty($productoBD['unidad_medida_codigo'])) {
				$errores[] = "El producto '$nombre' no tiene unidad de medida asignada";
			}

			// Validar precio y cantidad
			if (floatval($productoVenta['precio']) <= 0) {
				$errores[] = "El producto '$nombre' tiene precio inválido (debe ser mayor a 0)";
			}

			if (intval($productoVenta['cantidad']) <= 0) {
				$errores[] = "El producto '$nombre' tiene cantidad inválida (debe ser mayor a 0)";
			}
		}

		return $errores;
	}

	/*=============================================
	VALIDAR CONFIGURACIÓN
	=============================================*/
	static private function validarConfiguracion()
	{
		$errores = [];

		// Validar rango activo
		$rango = ModeloFactus::mdlObtenerRangoActivo();
		if (!$rango) {
			$errores[] = "No hay rango de numeración activo configurado en Factus. Vaya a Configuración > Factus y sincronice los rangos.";
			return $errores;
		}

		// Validar límite del rango
		if (!empty($rango['numero_hasta'])) {
			$numeroActual = intval($rango['numero_actual']);
			$numeroHasta = intval($rango['numero_hasta']);

			if ($numeroActual >= $numeroHasta) {
				$errores[] = "El rango de numeración ha alcanzado su límite ($numeroActual/$numeroHasta). Configure un nuevo rango en Factus.";
			}
		}

		// Validar token
		$config = ModeloFactus::mdlObtenerConfiguracion();
		if (!empty($config['token_expiracion'])) {
			// Verificar si ha expirado (o está próximo a expirar en 5 min)
			if (strtotime($config['token_expiracion']) < (time() + 300)) {
				// Intentar refrescar token automáticamente
				$tokenNuevo = ModeloFactus::mdlGarantizarTokenValido();
				if (!$tokenNuevo) {
					$errores[] = "El token de Factus ha expirado y no se pudo renovar automáticamente. Vaya a Configuración > Factus y haga clic en 'Autenticar' nuevamente.";
				}
			}
		} else {
			// Si no hay fecha de expiración, también intentamos obtener/refrescar
			$tokenNuevo = ModeloFactus::mdlGarantizarTokenValido();
			if (!$tokenNuevo) {
				$errores[] = "No hay un token válido de Factus. Vaya a Configuración > Factus y autentíquese.";
			}
		}

		return $errores;
	}

	/*=============================================
	GENERAR FACTURA ELECTRÓNICA
	=============================================*/
	static public function ctrGenerarFacturaElectronica($idVenta, $firmar = true)
	{
		// 1. Obtener datos de la venta
		require_once __DIR__ . "/../modelos/ventas.modelo.php";
		require_once __DIR__ . "/../modelos/clientes.modelo.php";
		require_once __DIR__ . "/../modelos/productos.modelo.php";

		$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

		if (!$venta) {
			return array(
				"error" => true,
				"mensaje" => "Venta no encontrada"
			);
		}

		// 🔹 VALIDACIÓN PREVENTIVA
		$validacion = self::ctrValidarDatosFactura($venta);

		if (!$validacion['valido']) {
			return array(
				"error" => true,
				"mensaje" => "No se puede generar la factura electrónica. Corrija los siguientes errores:",
				"errores" => $validacion['errores']
			);
		}

		// 2. Obtener token
		$auth = self::ctrAutenticar();
		if ($auth['error']) {
			return array(
				"error" => true,
				"mensaje" => "Error de autenticación: " . $auth['mensaje']
			);
		}

		// 3. Preparar datos de la factura según formato de Factus
		$datosFactura = self::prepararDatosFactura($venta);

		// 🔹 SI NO SE REQUIERE FIRMAR, GUARDAR COMO BORRADOR Y SALIR
		if (!$firmar) {
			$datosActualizar = array(
				"estado_dian" => "creada",
				"cufe" => '',
				"qr_data" => '',
				"xml_dian" => '',
				"pdf_dian" => '',
				"mensaje_dian" => "Factura guardada localmente (Borrador). Pendiente de firma.",
				"fecha_envio_dian" => null,
				"numero_factura" => ''
			);

			// 🟢 DEBUG: Log draft creation
			file_put_contents(
				"debug_draft_creation.txt",
				"=== DRAFT CREATION [" . date('Y-m-d H:i:s') . "] ===\n" .
				"ID Venta: " . $idVenta . "\n" .
				"Datos a actualizar: " . print_r($datosActualizar, true) . "\n",
				FILE_APPEND
			);

			$resultado = ModeloFactus::mdlActualizarDatosFactura($idVenta, $datosActualizar);

			// 🟢 DEBUG: Log update result
			file_put_contents(
				"debug_draft_creation.txt",
				"Resultado UPDATE: " . ($resultado ? "SUCCESS" : "FAILED") . "\n\n",
				FILE_APPEND
			);

			return array(
				"error" => false,
				"mensaje" => "Factura guardada correctamente (Borrador)",
				"datos" => []
			);
		}

		// 🟢 LOGUEAR PAYLOAD ENVIADO
		file_put_contents("debug_factus_request_" . $idVenta . ".txt", print_r($datosFactura, true));

		// 4. Enviar factura a Factus (Con reintento automático si hay conflicto de consecutivos 409)
		$intentos = 0;
		$maxIntentos = 3;
		$resultado = null;
		$exito = false;

		while ($intentos < $maxIntentos && !$exito) {
			$intentos++;

			// Si es reintento, actualizar el consecutivo antes de enviar
			if ($intentos > 1) {
				// Obtener rango y forzar avance del consecutivo
				$rango = ModeloFactus::mdlObtenerRangoActivo();
				if ($rango) {
					$nuevoConsecutivo = intval($rango['numero_actual']) + 1;
					ModeloFactus::mdlActualizarNumeroActualRango($rango['id_factus'], $nuevoConsecutivo);

					// Re-validar y re-preparar datos con el nuevo consecutivo
					$datosFactura = self::prepararDatosFactura($venta);

					file_put_contents("debug_retry_409.txt", "Reintento #$intentos: Forzando consecutivo a $nuevoConsecutivo\n", FILE_APPEND);
				}
			}

			// 🟢 DEBUG: Ver qué datos se envían a Factus (JSON)
			file_put_contents("debug_factus_request.json", json_encode($datosFactura, JSON_PRETTY_PRINT));

			// Enviar solicitud
			$resultado = ModeloFactus::mdlCrearFacturaElectronica($auth['token'], $datosFactura);

			// Verificar si fue exitoso (200 o 201)
			if ($resultado['http_code'] == 201 || $resultado['http_code'] == 200) {
				$exito = true;
			} elseif ($resultado['http_code'] == 409) {
				// 409 Conflict: Probablemente consecutivo ya usado o pendiente
				// Continuar al siguiente intento del bucle para incrementar consecutivo
				$debugMsg = "Error 409 en intento #$intentos. Retrying...\n";
				file_put_contents("debug_retry_409.txt", $debugMsg, FILE_APPEND);
				continue;
			} else {
				// Otro error (400, 422, 500), no tiene sentido reintentar incrementando
				break;
			}
		}

		// 🟢 LOGUEAR RESPUESTA COMPLETA (EXITO O ERROR AL FINAL)
		file_put_contents("debug_factus_response_" . $idVenta . ".txt", print_r($resultado, true));

		if ($exito) {
			$respuestaFactus = json_decode($resultado['respuesta'], true);

			// Actualizar datos en la venta
			$datosActualizar = array(
				"estado_dian" => "enviada", // ENUM: 'pendiente','enviada','rechazada','aceptada'
				"cufe" => $respuestaFactus['data']['cufe'] ?? $respuestaFactus['cufe'] ?? '',
				"qr_data" => $respuestaFactus['data']['bill']['qr'] ?? $respuestaFactus['data']['qr_code'] ?? $respuestaFactus['qr_code'] ?? '',
				"xml_dian" => $respuestaFactus['data']['xml_url'] ?? $respuestaFactus['xml_url'] ?? '',
				"pdf_dian" => $respuestaFactus['data']['pdf_url'] ?? $respuestaFactus['pdf_url'] ?? '',
				"mensaje_dian" => $respuestaFactus['message'] ?? 'Factura generada exitosamente',
				"fecha_envio_dian" => date('Y-m-d H:i:s'),
				// 🔹 CAPTURAR EL NÚMERO OFICIAL DE FACTURA
				"numero_factura" => $respuestaFactus['data']['bill']['number'] ?? $respuestaFactus['data']['number'] ?? $respuestaFactus['number'] ?? '',
				// 🔹 CAPTURAR EL ID INTERNO DE FACTUS (Requerido para Notas Crédito)
				"factus_bill_id" => $respuestaFactus['data']['bill']['id'] ?? $respuestaFactus['data']['id'] ?? null
			);

			ModeloFactus::mdlActualizarDatosFactura($idVenta, $datosActualizar);

			// 🟢 LOGUEAR RESPUESTA COMPLETA PARA DEBUG (TEMPORAL)
			file_put_contents("debug_factus_response_" . $idVenta . ".txt", print_r($respuestaFactus, true));

			// 🟢 ACTUALIZAR EL CONSECUTIVO EN factus_rangos PARA QUE EL SIGUIENTE SEA CORRECTO
			if (!empty($datosActualizar["numero_factura"])) {
				// Extraer solo el número del formato "PREFIJO-NUMERO" o "PREFIJONUMERO"
				$numeroFactura = $datosActualizar["numero_factura"];

				// Intentar con guión primero
				if (strpos($numeroFactura, '-') !== false) {
					$partes = explode('-', $numeroFactura);
					$nuevoNumero = end($partes);
				} else {
					// Si no tiene guión, extraer los dígitos del final
					preg_match('/(\d+)$/', $numeroFactura, $matches);
					$nuevoNumero = isset($matches[1]) ? $matches[1] : null;
				}

				if ($nuevoNumero && is_numeric($nuevoNumero)) {
					// Actualizar con el ID de Factus del rango
					$rangoId = $datosFactura['numbering_range_id'];
					ModeloFactus::mdlActualizarNumeroActualRango($rangoId, intval($nuevoNumero));

					// Log para debug
					file_put_contents(
						"debug_consecutivo_update.txt",
						"Factura: $numeroFactura | Número extraído: $nuevoNumero | Rango ID: $rangoId\n",
						FILE_APPEND
					);
				}
			}

			return array(
				"error" => false,
				"mensaje" => "Factura electrónica generada exitosamente",
				"datos" => $respuestaFactus
			);
		} else {
			// Error al generar factura
			$error = json_decode($resultado['respuesta'], true);

			$datosActualizar = array(
				"estado_dian" => "Error",
				"cufe" => '',
				"qr_data" => '',
				"xml_dian" => '',
				"pdf_dian" => '',
				"mensaje_dian" => $error['message'] ?? $resultado['respuesta'],
				"fecha_envio_dian" => date('Y-m-d H:i:s')
			);

			ModeloFactus::mdlActualizarDatosFactura($idVenta, $datosActualizar);

			return array(
				"error" => true,
				"mensaje" => "Error al generar factura: " . ($error['message'] ?? 'Error desconocido'),
				"codigo_http" => $resultado['http_code'],
				"detalles" => $resultado['respuesta']
			);
		}
	}

	/*=============================================
	PREPARAR DATOS DE FACTURA PARA FACTUS
	=============================================*/
	public static function prepararDatosFactura($venta)
	{
		require_once __DIR__ . "/../modelos/clientes.modelo.php";
		require_once __DIR__ . "/../modelos/productos.modelo.php";
		require_once __DIR__ . "/../modelos/configuracion.modelo.php";

		$cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $venta['id_cliente']);
		$config = ModeloConfiguracion::mdlObtenerConfiguracion();

		// DEBUG CONFIG
		file_put_contents("debug_config_verification.txt", print_r($config, true));

		$productosVenta = json_decode($venta['productos'], true);
		$items = array();

		$tipoDescuento = $venta["tipo_descuento"] ?? ""; // porcentaje o fijo
		$valorDescuentoGlobal = floatval($venta["valor_descuento"] ?? 0);
		$montoDescuentoTotal = floatval($venta["monto_descuento"] ?? 0);
		$totalVenta = floatval($venta["total"]);
		// Estimación del total antes de descuentos para el prorrateo
		$totalBaseProrrateo = $totalVenta + $montoDescuentoTotal;

		// 🟠 LOGICA DE RETENCIONES GLOBAL
		$retencionesVenta = [];
		if (isset($venta['retenciones']) && !empty($venta['retenciones'])) {
			$retencionesVenta = json_decode($venta['retenciones'], true);
		}

		foreach ($productosVenta as $key => $productoVenta) {
			$productoBD = ModeloProductos::mdlMostrarProductos("productos", "id", $productoVenta['id'], "id");

			// 🔴 CORRECCIÓN: Verificar si el producto tiene tributo_id = 0 (sin impuesto)
			// En PHP, empty(0) es true, así que debemos verificar explícitamente
			$tributoIdOriginal = isset($productoBD['tributo_id']) ? intval($productoBD['tributo_id']) : 0;

			// Si el tributo_id es 0, el producto no tiene impuesto
			if ($tributoIdOriginal === 0) {
				$tributo = null;
				$tasaImpuesto = 0.00;
				$codeTributo = 7; // Código 7 = No causa impuesto
			} else {
				// Producto con impuesto - obtener datos del tributo
				$tributo = ModeloFactus::mdlMostrarTributo($tributoIdOriginal);
				$tasaImpuesto = $tributo ? floatval($tributo['porcentaje_defecto']) : 19.00;

				// Usar el código del tributo (1 para IVA, etc.)
				if ($tributo && !empty($tributo['codigo'])) {
					$codeTributo = intval($tributo['codigo']);
				} else {
					$codeTributo = 1; // Fallback a IVA si no se encuentra
				}
			}
			$unidadMedida = isset($productoBD['unidad_medida_id']) && !empty($productoBD['unidad_medida_id']) ? intval($productoBD['unidad_medida_id']) : 70;

			$precioBase = floatval($productoVenta['precio']);

			// 🔹 CALCULO DESCUENTO
			$tasaDescuentoItem = 0;
			if ($tipoDescuento == "porcentaje") {
				// Si es porcentaje, aplicamos ese porcentaje directo
				$tasaDescuentoItem = $valorDescuentoGlobal;
			} elseif ($tipoDescuento == "fijo" && $montoDescuentoTotal > 0 && $totalBaseProrrateo > 0) {
				// Si es fijo, debemos calcular qué porcentaje representa del total de este item
				// Prorrateo: (TotalItem / TotalGeneral) * MontoDescuentoGlobal
				// Pero necesitamos la TASA (Rate) para Factus.
				// Tasa = (MontoDescuentoAsignadoAlItem / TotalItem) * 100
				// Simplificando: (TotalItem * (MontoDesc / TotalGen)) / TotalItem * 100
				// Tasa = (MontoDesc / TotalGen) * 100
				// La tasa es constante para todos los items en un descuento fijo prorrateado proporcionalmente
				$tasaDescuentoItem = ($montoDescuentoTotal / $totalBaseProrrateo) * 100;
			}

			// 🟠 PREPARAR RETENCIONES PARA EL ITEM
			$withholdingTaxesItem = array();
			if (!empty($retencionesVenta)) {
				foreach ($retencionesVenta as $ret) {
					$codigoRetencion = "06"; // Default ReteRenta (Factus usa 06)
					$nombreRetencion = isset($ret['descripcion']) ? $ret['descripcion'] : (isset($ret['tipo']) ? $ret['tipo'] : '');

					// DEBUG RETENCIONES
					$logRet = fopen("debug_retenciones.txt", "a");
					fwrite($logRet, "Procesando: " . $nombreRetencion . " para item: " . $productoVenta['descripcion'] . " (IVA: " . $tasaImpuesto . "%)\n");

					// Mapeo básico basado en nombre
					$esReteIVA = false;
					if (stripos($nombreRetencion, 'IVA') !== false) {
						$codigoRetencion = "05"; // ReteIVA (Factus usa 05)
						$esReteIVA = true;
						fwrite($logRet, "  -> Detectado IVA (Code 05)\n");
					} elseif (stripos($nombreRetencion, 'ICA') !== false) {
						$codigoRetencion = "07"; // ReteICA
						fwrite($logRet, "  -> Detectado ICA (Code 07)\n");
					} elseif (stripos($nombreRetencion, 'Renta') !== false) {
						$codigoRetencion = "06"; // ReteRenta (Factus usa 06)
						fwrite($logRet, "  -> Detectado Renta (Code 06)\n");
					} else {
						fwrite($logRet, "  -> No match, default 06\n");
					}

					// 🔴 VALIDACIÓN: ReteIVA solo se aplica a items con IVA (código tributo 1)
					if ($esReteIVA && $codeTributo != 1) {
						fwrite($logRet, "  -> OMITIDO: ReteIVA solo aplica a IVA (código 1), este item tiene tributo código " . $codeTributo . "\n");
						fclose($logRet);
						continue; // Saltar esta retención para este item
					}

					fclose($logRet);

					$withholdingTaxesItem[] = array(
						"code" => $codigoRetencion,
						"withholding_tax_rate" => number_format(floatval($ret['porcentaje']), 2, '.', '')
					);
				}
			}

			// Validar Unidad de Medida (Fallback manual por seguridad)
			$idUnidadMedida = ModeloFactus::mdlObtenerIdUnidadMedida($unidadMedida);
			$unidadesSoportadas = [70, 414, 449, 499, 512, 874];
			if (!in_array($idUnidadMedida, $unidadesSoportadas)) {
				$idUnidadMedida = 70;
			}

			$items[] = array(
				"scheme_id" => "1",
				"note" => "",
				"code_reference" => !empty($productoVenta['codigo']) ? $productoVenta['codigo'] : ("ITEM-" . ($key + 1)),
				"name" => $productoVenta['descripcion'],
				"quantity" => intval($productoVenta['cantidad']),
				"discount_rate" => number_format($tasaDescuentoItem, 2, '.', ''), // TASA CALCULADA
				"price" => number_format($precioBase, 6, '.', ''), // AUMENTAR PRECISIÓN A 6 DECIMALES
				"tax_rate" => number_format($tasaImpuesto, 2, '.', ''),
				"unit_measure_id" => $idUnidadMedida,
				"standard_code_id" => 1,
				"is_excluded" => 0,
				"tribute_id" => $codeTributo, // ENVIAR CÓDIGO REAL (1 para IVA, 7 para sin impuesto)
				"withholding_taxes" => $withholdingTaxesItem
			);
		}

		$tipoDocumentoId = isset($cliente['tipo_documento_id']) && !empty($cliente['tipo_documento_id']) ? intval($cliente['tipo_documento_id']) : 3;
		$municipioId = isset($cliente['municipio_id']) && !empty($cliente['municipio_id']) ? strval($cliente['municipio_id']) : null;
		$formaPagoDian = isset($venta['forma_pago_dian']) && !empty($venta['forma_pago_dian']) ? strval($venta['forma_pago_dian']) : "1";

		// Determinar método de pago DIAN
		if (isset($venta['metodo_pago_dian_id']) && !empty($venta['metodo_pago_dian_id'])) {
			$metodoPagoDianId = strval($venta['metodo_pago_dian_id']);
		} elseif (isset($venta['metodo_pago']) && !empty($venta['metodo_pago'])) {
			// Intentar mapear desde el nombre del método de pago (ej. "Nequi", "Tarjeta")
			$metodoPagoDianId = ModeloFactus::mdlObtenerCodigoMedioPago($venta['metodo_pago']);
		} else {
			$metodoPagoDianId = "10"; // Default Efectivo
		}
		$fechaVencimiento = isset($venta['fecha_vencimiento']) && !empty($venta['fecha_vencimiento']) ? $venta['fecha_vencimiento'] : date('Y-m-d', strtotime('+30 days'));

		// Obtener Rango Dinámico
		$rango = ModeloFactus::mdlObtenerRangoActivo();
		$rangoId = $rango['id_factus'] ?? 1;


		// Lookup correct Factus Municipality ID
		// Cliente tiene 'municipio_id' que almacena id_factus directamente
		// API necesita 'id_factus' (ID de Factus API)



		$municipioFactusId = "169"; // Default Bogotá (ID Factus 169)
		$db = Conexion::conectar();

		if (!empty($municipioId)) {
			// El municipioId ya es id_factus, solo verificar que existe
			$stmtMun = $db->prepare("SELECT id_factus FROM factus_municipios WHERE id_factus = :id_factus LIMIT 1");
			$stmtMun->execute([':id_factus' => $municipioId]);
			$resMun = $stmtMun->fetch();
			if ($resMun && !empty($resMun['id_factus'])) {
				$municipioFactusId = $resMun['id_factus'];
			}
		}

		// Obtener configuración específica de Factus (si existe)
		$configFactus = ModeloFactus::mdlObtenerConfiguracion();

		// Determinar datos del emisor (Prioridad: Factus > General > Default)
		$nombreEmisor = !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($config['nombre_empresa'] ?? 'Mi Empresa');
		$direccionEmisor = !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($config['direccion'] ?? 'Dirección');
		$telefonoEmisor = !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($config['telefono'] ?? '1234567');
		$emailEmisor = !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($config['correo'] ?? 'empresa@example.com');
		$municipioEmisor = !empty($configFactus['municipio_id']) ? $configFactus['municipio_id'] : '169'; // Default Bogotá (ID 169)

		$factura = array(
			"numbering_range_id" => $rangoId,
			"reference_code" => $rango['prefijo'] . $venta['codigo'],
			"observation" => implode(" | ", array_filter([$venta['notas'] ?? '', $venta['observacion'] ?? ''])),
			"payment_form" => $formaPagoDian,
			"payment_due_date" => $fechaVencimiento,
			"payment_method_code" => $metodoPagoDianId,
			/* "payment_methods" => [ // API V1/Lite no soporta array, volvemos a root
				[
					"code" => $metodoPagoDianId,
					"payment_method_id" => intval($metodoPagoDianId),
					"payment_due_date" => $fechaVencimiento
				]
			], */
			"operation_type" => 10,
			"send_email" => true,
			"establishment" => array(
				"name" => $nombreEmisor,
				"address" => $direccionEmisor,
				"phone_number" => $telefonoEmisor,
				"email" => $emailEmisor,
				"municipality_id" => $municipioEmisor,
				// Campos Extendidos
				"merchant_registration" => $configFactus['registro_mercantil'] ?? '000000-00',
				"economic_activity_code" => $configFactus['actividad_economica'] ?? '',
				// Responsabilidades Fiscales (JSON array -> Array of objects)
				// Responsabilidades Fiscales (JSON array -> Array of objects)
				"fiscal_responsibilities" => array_map(function ($code) {
					return ["code" => $code];
				}, json_decode($configFactus['responsabilidades_fiscales'] ?? '[]', true)),
				// Tipo Persona (1: Juridica, 2: Natural)
				"legal_organization_id" => $configFactus['tipo_persona'] ?? '2'
			),

			"customer" => (function () use ($cliente, $tipoDocumentoId, $municipioFactusId) {
				// 1. Procesar Responsabilidades Fiscales primero
				$inputResp = $cliente['responsabilidades_fiscales'] ?? 'R-99-PN';

				// DEBUG: Registrar input 
				file_put_contents("debug_fiscal_regex.txt", "Input Cliente ID " . ($cliente['id'] ?? '?') . ": " . $inputResp . "\n", FILE_APPEND);

				// Regex mejorada: Busca códigos como O-23, O-47, R-99-PN, ZY
				preg_match_all('/\b([A-Z]{1,2}(?:-[0-9A-Z]+)*)\b/', $inputResp, $matches);
				$rawCodes = array_unique($matches[0] ?? []);

				file_put_contents("debug_fiscal_regex.txt", "Matches: " . print_r($rawCodes, true) . "\n", FILE_APPEND);

				// Filtro de códigos válidos conocidos
				$codigosValidos = ['O-13', 'O-15', 'O-23', 'O-47', 'R-99-PN', 'ZY'];
				$codes = array_filter($rawCodes, function ($c) use ($codigosValidos) {
					return in_array($c, $codigosValidos);
				});

				if (empty($codes)) {
					$codes = ['R-99-PN']; }

				// 2. Determinación inteligente de Persona Jurídica
				$codigosJuridicos = ['O-13', 'O-15', 'O-23', 'O-47'];
				$tieneResponsabilidadJuridica = !empty(array_intersect($codes, $codigosJuridicos));

				// FALLBACK: Si la regex falló pero el string contiene "O-23" explícitamente
				if (!$tieneResponsabilidadJuridica && (strpos($inputResp, 'O-23') !== false || strpos($inputResp, 'O-47') !== false)) {
					$tieneResponsabilidadJuridica = true;
					// Asegurar que el código esté en la lista
					if (strpos($inputResp, 'O-23') !== false && !in_array('O-23', $codes))
						$codes[] = 'O-23';
					if (strpos($inputResp, 'O-47') !== false && !in_array('O-47', $codes))
						$codes[] = 'O-47';

					file_put_contents("debug_fiscal_regex.txt", "FALLBACK TRIGGERED: O-23/O-47 detectado por strpos\n", FILE_APPEND);
				}

				$isJuridica = (trim(strtolower($cliente['tipo_persona'] ?? 'natural')) == 'juridica') || $tieneResponsabilidadJuridica;

				$isJuridica = (trim(strtolower($cliente['tipo_persona'] ?? 'natural')) == 'juridica') || $tieneResponsabilidadJuridica;

				// 3. Calcular DV y Definir IDs según Tipo
				$dv = $cliente['digito_verificacion'] ?? '0';

				// Si es Jurídica o si el documento es NIT (31), recalcular DV siempre por seguridad
				if ($isJuridica || $cliente['tipo_documento_id'] == 31 || $cliente['tipo_documento_id'] == 3) { // 3 or 31 usually NIT
					$dv = ModeloFactus::mdlCalcularDV($cliente['documento']);
				}

				return array(
					"identification" => $cliente['documento'] ?? '',
					"dv" => $dv,
					"company" => $isJuridica ? ($cliente['razon_social'] ?: $cliente['nombre']) : '',
					"trade_name" => $cliente['nombre_comercial'] ?? '',
					"names" => $cliente['nombre'],
					"address" => $cliente['direccion'] ?? 'Dirección no registrada',
					"email" => $cliente['email'] ?? '',
					"phone" => $cliente['telefono'] ?? '',


					// IDs Inferidos
					// 1: Juridica, 2: Natural. Si es Juridica por tipo_persona o por responsabilidades, usamos 1.
					// PERO si es natural con responsabilidades (O-23), Factus a veces requiere NIT. 
					// Sin embargo, si el usuario seleccionó Cédula (3), debemos respetar eso.
					// La lógica anterior forzaba "6" (NIT) si detectaba responsabilidades, lo cual es incorrecto para naturales responsables de IVA.
					"legal_organization_id" => (trim(strtolower($cliente['tipo_persona'] ?? 'natural')) == 'juridica') ? "1" : "2",
					"tribute_id" => $isJuridica ? "18" : "21",

					// Mapeo especial: Si el usuario selecciona NUIP (ID 9), enviar como Registro Civil (ID 1) a Factus
					// Esto soluciona que la DIAN muestre el campo vacío para NUIP
					"identification_document_id" => ($tipoDocumentoId == 9) ? "1" : $tipoDocumentoId,

					"fiscal_responsibilities" => array_map(function ($c) {
						return ['code' => $c];
					}, $codes),

					"municipality_id" => $municipioFactusId
				);
			})(),
			"items" => $items
		);

		return $factura;
	}

	/*=============================================
	GENERAR NOTA CRÉDITO (API FACTUS)
	=============================================*/
	static public function ctrGenerarNotaCredito($idVenta, $motivo, $listaProductos = null, $idCliente = null, $motivoDescripcion = null, $metodoPago = "Efectivo", $observacion = "")
	{
		// 1. Validar venta original
		require_once __DIR__ . "/../modelos/ventas.modelo.php";
		$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

		if (!$venta) {
			return [
				"error" => true,
				"mensaje" => "La venta no existe"
			];
		}

		// Verificar que tenga número de factura (fue enviada a DIAN)
		if (empty($venta["numero_factura"])) {
			return [
				"error" => true,
				"mensaje" => "Esta venta no tiene factura electrónica generada"
			];
		}

		// Verificar estado DIAN
		if (!in_array($venta["estado_dian"], ['enviada', 'aceptada'])) {
			return [
				"error" => true,
				"mensaje" => "Solo se pueden generar NC para facturas enviadas o aceptadas. Estado actual: " . $venta["estado_dian"]
			];
		}

		// Verificar que no tenga ya una NC
		if (ModeloFactus::mdlTieneNotaCredito($idVenta)) {
			return [
				"error" => true,
				"mensaje" => "Esta factura ya tiene una Nota Crédito generada"
			];
		}

		// 2. Autenticar
		$auth = self::ctrAutenticar();
		if ($auth['error']) {
			return [
				"error" => true,
				"mensaje" => $auth['mensaje']
			];
		}
		$token = $auth['token'];

		// 3. Preparar datos de NC
		$datosNC = self::prepararDatosNotaCredito($venta, $motivo, $listaProductos, $idCliente, $motivoDescripcion, $metodoPago, $observacion);

		if (isset($datosNC['error'])) {
			return $datosNC;
		}

		// 4. Enviar a Factus
		$resultado = ModeloFactus::mdlCrearNotaCredito($token, $datosNC);

		// 5. Procesar respuesta
		if ($resultado['http_code'] == 201 || $resultado['http_code'] == 200) {
			$respuestaFactus = json_decode($resultado['respuesta'], true);

			// Extraer datos de la respuesta
			$datosGuardar = [
				"id_venta_original" => $idVenta,
				"numero_factura_original" => $venta["numero_factura"],
				"tipo_nota" => "NC_referencia",
				"motivo" => $motivo,
				"productos" => json_encode($listaProductos), // Guardar productos de la NC
				"monto_total" => $venta["total"],
				"id_cliente" => !empty($idCliente) ? $idCliente : $venta["id_cliente"],
				"estado_dian" => "enviada",
				"numero_nota_credito" => $respuestaFactus['data']['credit_note']['number'] ?? '',
				"cufe_nc" => $respuestaFactus['data']['credit_note']['cude'] ?? $respuestaFactus['data']['cufe'] ?? '',
				"qr_data_nc" => $respuestaFactus['data']['credit_note']['qr'] ?? $respuestaFactus['data']['qr_code'] ?? '',
				"xml_dian_nc" => $respuestaFactus['data']['credit_note']['xml_url'] ?? '',
				"pdf_dian_nc" => $respuestaFactus['data']['credit_note']['public_url'] ?? $respuestaFactus['data']['pdf_url'] ?? '',
				"mensaje_dian" => $respuestaFactus['message'] ?? 'NC generada exitosamente',
				"fecha_envio_dian" => date('Y-m-d H:i:s'),
				"id_usuario" => $_SESSION['id'] ?? 14,
				"observacion" => $observacion,
				"metodo_pago" => $metodoPago
			];

			// Guardar en BD
			$guardado = ModeloFactus::mdlGuardarNotaCredito($datosGuardar);

			if ($guardado == "ok") {
				// Actualizar consecutivo del rango NC
				$rangoNC = ModeloFactus::mdlObtenerRangoNC();
				if ($rangoNC && !empty($datosGuardar["numero_nota_credito"])) {
					// Extraer número del formato "NC1"
					$numeroNC = preg_replace('/[^0-9]/', '', $datosGuardar["numero_nota_credito"]);
					if ($numeroNC && is_numeric($numeroNC)) {
						ModeloFactus::mdlActualizarNumeroActualRangoNC($rangoNC['id_factus'], intval($numeroNC));
					}
				}

				return [
					"error" => false,
					"mensaje" => "Nota Crédito generada exitosamente",
					"numero_nc" => $datosGuardar["numero_nota_credito"],
					"cufe" => $datosGuardar["cufe_nc"],
					"datos" => $respuestaFactus
				];
			} else {
				return [
					"error" => true,
					"mensaje" => "Error al guardar la Nota Crédito en base de datos"
				];
			}
		} else {
			// Error del API
			$error = json_decode($resultado['respuesta'], true);

			// Log detallado para debug
			file_put_contents("debug_nc_api_error.txt", date('Y-m-d H:i:s') . "\nHTTP: " . $resultado['http_code'] . "\nResponse: " . $resultado['respuesta'] . "\n\n", FILE_APPEND);

			// Extraer errores de validación si existen
			$mensajeError = $error['message'] ?? 'Error desconocido';
			if (isset($error['errors']) && is_array($error['errors'])) {
				$detallesError = [];
				foreach ($error['errors'] as $campo => $mensajes) {
					if (is_array($mensajes)) {
						$detallesError[] = $campo . ": " . implode(", ", $mensajes);
					} else {
						$detallesError[] = $campo . ": " . $mensajes;
					}
				}
				$mensajeError .= " | Detalles: " . implode(" | ", $detallesError);
			}

			return [
				"error" => true,
				"mensaje" => "Error al generar NC: " . $mensajeError,
				"codigo_http" => $resultado['http_code'],
				"detalles" => $resultado['respuesta']
			];
		}
	}

	/*=============================================
	PREPARAR DATOS DE NOTA CRÉDITO (JSON PARA FACTUS)
	=============================================*/
	private static function prepararDatosNotaCredito($venta, $motivo, $listaProductos = null, $idCliente = null, $motivoDescripcion = null, $metodoPago = "Efectivo", $observacion = "")
	{
		// Obtener datos relacionados (similar a prepararDatosFactura)
		require_once __DIR__ . "/../modelos/clientes.modelo.php";
		require_once __DIR__ . "/../modelos/productos.modelo.php";
		require_once __DIR__ . "/../modelos/configuracion.modelo.php";

		// Usar cliente editado si se proporcionó, si no, el original de la venta
		$clienteId = !empty($idCliente) ? $idCliente : $venta['id_cliente'];
		$cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $clienteId);
		$config = ModeloConfiguracion::mdlObtenerConfiguracion();
		$configFactus = ModeloFactus::mdlObtenerConfiguracion();
		$rangoNC = ModeloFactus::mdlObtenerRangoNC();

		if (!$rangoNC) {
			return [
				"error" => true,
				"mensaje" => "No hay un rango de Nota Crédito configurado"
			];
		}

		// Mapeo de motivo a códigos DIAN (según documentación Factus API)
		// El motivo ya es el correction_concept_id de Factus (1-6)
		$correctionConceptId = intval($motivo); // Valor directo del motivo
		$discrepancyCode = "2"; // Default

		// discrepancy_response se mapea según el motivo
		switch ($motivo) {
			case '1': // Devolución parcial de los bienes y/o no aceptación parcial del servicio
				$discrepancyCode = "2";
				break;
			case '2': // Anulación de factura electrónica
				$discrepancyCode = "1";
				break;
			case '3': // Rebaja o descuento parcial o total
			case '4': // Ajuste de precio
				$discrepancyCode = "3";
				break;
			case '5': // Descuento comercial por pronto pago
			case '6': // Descuento comercial por volumen de ventas
				$discrepancyCode = "2";
				break;
			default:
				$discrepancyCode = "2";
				break;
		}

		// Preparar items (productos)
		// Si recibimos lista filtrada, usamos esa, si no, la original
		$productosVenta = !empty($listaProductos) ? $listaProductos : json_decode($venta['productos'], true);
		$items = [];

		foreach ($productosVenta as $key => $productoVenta) {
			$productoBD = ModeloProductos::mdlMostrarProductos("productos", "id", $productoVenta['id'], "id");

			if (!$productoBD) {
				continue;
			}

			// Obtener información de tributo
			$tributoIdOriginal = isset($productoBD['tributo_id']) ? intval($productoBD['tributo_id']) : 0;
			$tasaImpuesto = 0.00;
			$codeTributo = 7; // No causa impuesto

			if ($tributoIdOriginal !== 0) {
				$tributo = ModeloFactus::mdlMostrarTributo($tributoIdOriginal);
				$tasaImpuesto = $tributo ? floatval($tributo['porcentaje_defecto']) : 19.00;
				$codeTributo = $tributo ? intval($tributo['codigo']) : 1;
			}

			// Obtener unidad de medida
			$idUnidadMedida = !empty($productoBD['codigo_unidad']) ? intval($productoBD['codigo_unidad']) : 70;

			// Precio base - similar a la lógica de factura (se usa el precio directamente)
			$precioBase = floatval($productoVenta['precio']);

			$items[] = [
				"scheme_id" => "1",
				"note" => "",
				"code_reference" => $productoVenta['codigo'] ?? ("ITEM-" . ($key + 1)),
				"name" => $productoVenta['descripcion'],
				"quantity" => intval($productoVenta['cantidad']),
				"discount_rate" => "0.00",
				"price" => number_format($precioBase, 6, '.', ''),
				"tax_rate" => number_format($tasaImpuesto, 2, '.', ''),
				"unit_measure_id" => $idUnidadMedida,
				"standard_code_id" => 1,
				"is_excluded" => 0,
				"tribute_id" => $codeTributo,
				"withholding_taxes" => [] // Vacío para simplificar en Fase 1
			];
		}

		// Preparar establishment (igual que en factura)
		$municipioEmpresaId = $configFactus['municipio_id'] ?? '';
		$responsabilidadesFiscalesEmpresa = json_decode($configFactus['responsabilidades_fiscales'] ?? '[]', true);
		if (!is_array($responsabilidadesFiscalesEmpresa) || empty($responsabilidadesFiscalesEmpresa)) {
			$responsabilidadesFiscalesEmpresa = [['code' => 'O-23']];
		} else {
			$responsabilidadesFiscalesEmpresa = array_map(function ($r) {
				return is_array($r) ? $r : ['code' => $r];
			}, $responsabilidadesFiscalesEmpresa);
		}

		// Preparar customer (igual que en factura)
		$municipioClienteId = $cliente['municipio_id'] ?? '';
		$tipoDocCliente = !empty($cliente['tipo_documento_id']) ? intval($cliente['tipo_documento_id']) : 6;
		$tipoPersonaCliente = isset($cliente['tipo_persona']) && in_array(intval($cliente['tipo_persona']), [1, 2]) ? intval($cliente['tipo_persona']) : 2;

		// Responsabilidades fiscales del cliente
		$inputRespCliente = $cliente['responsabilidades_fiscales'] ?? 'R-99-PN';
		preg_match_all('/\b([A-Z]{1,2}(?:-[0-9A-Z]+)*)\b/', $inputRespCliente, $matches);
		$rawCodesCliente = array_unique($matches[0] ?? []);
		$codigosValidosCliente = ['O-13', 'O-15', 'O-23', 'O-47', 'R-99-PN', 'ZY'];
		$codesCliente = array_filter($rawCodesCliente, function ($c) use ($codigosValidosCliente) {
			return in_array($c, $codigosValidosCliente);
		});
		if (empty($codesCliente)) {
			$codesCliente = ['R-99-PN'];
		}


		// VALIDACIÓN DE ID FACTUS (Requerido para NC)
		if (empty($venta['factus_bill_id'])) {
			return [
				'status' => 'error',
				'message' => 'Esta factura no tiene un ID de Factus asociado (factus_bill_id). No es posible generar Nota Crédito para facturas procesadas antes de esta actualización.'
			];
		}

		// Generar código de referencia para la NC (ej: "NC-FEFG66")
		$referenciaNC = "NC-" . $venta["numero_factura"];

		// ============================================
		// LÓGICA DE MOTIVO / DESCRIPCIÓN
		// ============================================
		$discrepancyCodes = [
			1 => "Devolución parcial de los bienes y/o no aceptación parcial del servicio",
			2 => "Anulación de factura electrónica",
			3 => "Rebaja o descuento parcial o total",
			4 => "Ajuste de precio",
			5 => "Descuento comercial por pronto pago",
			6 => "Descuento comercial por volumen de ventas"
		];

		// Si el motivo viene como texto (ej desde select), asegurar que es int
		$discrepancyCode = intval($motivo);

		// Determinar descripción final
		// Si hay una descripción personalizada (viene de 'Otros' o input usuario), usarla
		$descripcionDefecto = $discrepancyCodes[$discrepancyCode] ?? "Otros";
		$descripcionFinal = (!empty($motivoDescripcion)) ? $motivoDescripcion : $descripcionDefecto;

		$correctionConceptId = $discrepancyCode;

		$notaCredito = [
			"numbering_range_id" => intval($rangoNC['id_factus']),
			"bill_id" => intval($venta['factus_bill_id']), // REQUERIDO: ID interno de Factus
			"reference_code" => $referenciaNC, // REQUERIDO: Código de referencia de la NC
			"bill_reference" => $venta["numero_factura"], // CAMPO CLAVE: Referencia a factura original
			"discrepancy_response_code" => $discrepancyCode,
			"discrepancy_response_description" => $descripcionFinal,
			"correction_concept_id" => $correctionConceptId,
			"correction_concept_code" => $correctionConceptId, // REQUERIDO: Same as correction_concept_id
			"observation" => (!empty($observacion)) ? $observacion : $descripcionFinal,
			"send_email" => true,

			"establishment" => [
				"name" => $configFactus['nombre_empresa'] ?? $config['nombre_empresa'],
				"address" => $configFactus['direccion_empresa'] ?? $config['direccion'],
				"phone_number" => $configFactus['telefono_empresa'] ?? $config['telefono'],
				"email" => $configFactus['email_empresa'] ?? $config['correo'],
				"municipality_id" => $municipioEmpresaId,
				"merchant_registration" => $configFactus['registro_mercantil'] ?? '',
				"economic_activity_code" => $configFactus['actividad_economica'] ?? '',
				"fiscal_responsibilities" => $responsabilidadesFiscalesEmpresa,
				"legal_organization_id" => $configFactus['tipo_persona'] ?? "1"
			],

			"customer" => [
				"identification" => $cliente['documento'],
				"dv" => $cliente['dv'] ?? '',
				"company" => $cliente['nombre'],
				"trade_name" => $cliente['nombre'],
				"names" => $cliente['nombre'],
				"address" => $cliente['direccion'],
				"email" => $cliente['email'],
				"phone" => $cliente['telefono'],
				"legal_organization_id" => $tipoPersonaCliente,
				"tribute_id" => "18", // Default ZZ para régimen simplificado
				"identification_document_id" => $tipoDocCliente,
				"fiscal_responsibilities" => array_map(function ($c) {
					return ['code' => $c];
				}, $codesCliente),
				"municipality_id" => $municipioClienteId
			],

			"items" => $items
		];

		// Mapeo básico de métodos de pago (Igual que en factura)
		$paymentMethodCode = "10"; // Default Efectivo

		switch ($metodoPago) {
			case "Efectivo":
				$paymentMethodCode = "10";
				break;
			case "TC":
				$paymentMethodCode = "48";
				break;
			case "TD":
				$paymentMethodCode = "49";
				break;
			case "Transf":
				$paymentMethodCode = "47";
				break;
			case "Cheque":
				$paymentMethodCode = "20";
				break;
			case "Consignacion":
				$paymentMethodCode = "42";
				break; // Consignación bancaria
			case "Bonos":
				$paymentMethodCode = "71";
				break;
			case "Vales":
				$paymentMethodCode = "72";
				break;
			case "Otros":
				$paymentMethodCode = "ZZ";
				break; // Mutuo acuerdo / Otros
			case "No Definido":
				$paymentMethodCode = "1";
				break; // Instrumento no definido
			default:
				$paymentMethodCode = "10";
				break;
		}

		$notaCredito["payment_method_code"] = $paymentMethodCode;
		$notaCredito["payment_due_date"] = date('Y-m-d');

		return $notaCredito;
	}
}
