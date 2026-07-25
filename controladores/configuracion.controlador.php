<?php

class ControladorConfiguracion
{

	/*=============================================
	OBTENER CONFIGURACIÓN
	=============================================*/

	static public function ctrObtenerConfiguracion()
	{

		$respuesta = ModeloConfiguracion::mdlObtenerConfiguracion();

		return $respuesta;

	}

	/*=============================================
	ACTUALIZAR CONFIGURACIÓN
	=============================================*/

	static public function ctrActualizarConfiguracion()
	{

		if (isset($_POST["actualizarConfiguracion"])) {

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
						window.location = "configuracion";
					})
				</script>';
				return;
			}

			/*=============================================
			PREPARAR LOGO PRINCIPAL (nuevo archivo físico)
			=============================================*/

			$rutaLogo = $_POST["logoActual"];
			$logoAnteriorAEliminar = null; // Se eliminará DESPUÉS del commit

			if (isset($_FILES["nuevoLogo"]["tmp_name"]) && !empty($_FILES["nuevoLogo"]["tmp_name"])) {

				list($ancho, $alto) = getimagesize($_FILES["nuevoLogo"]["tmp_name"]);

				$nuevoAncho = 500;
				$nuevoAlto = 500;

				$directorio = "vistas/img/configuracion/";

				if (!file_exists($directorio)) {
					mkdir($directorio, 0755, true);
				}

				// Registrar logo anterior para eliminar DESPUÉS del commit exitoso
				if (!empty($_POST["logoActual"]) && file_exists($_POST["logoActual"])) {
					$logoAnteriorAEliminar = $_POST["logoActual"];
				}

				/*=============================================
				PROCESAR IMAGEN CON FALLBACK SI NO EXISTE GD
				=============================================*/

				if (function_exists('imagecreatefromjpeg') && function_exists('imagecreatetruecolor')) {

					if ($_FILES["nuevoLogo"]["type"] == "image/jpeg") {
						$aleatorio = mt_rand(100, 999);
						$rutaLogo = $directorio . $aleatorio . ".jpg";
						$origen = imagecreatefromjpeg($_FILES["nuevoLogo"]["tmp_name"]);
						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
						imagejpeg($destino, $rutaLogo);
					}

					if ($_FILES["nuevoLogo"]["type"] == "image/png") {
						$aleatorio = mt_rand(100, 999);
						$rutaLogo = $directorio . $aleatorio . ".png";
						$origen = imagecreatefrompng($_FILES["nuevoLogo"]["tmp_name"]);
						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
						imagepng($destino, $rutaLogo);
					}

				} else {

					// Fallback: Mover el archivo directamente si GD no está activo
					$aleatorio = mt_rand(100, 999);
					$extension = ($_FILES["nuevoLogo"]["type"] == "image/png") ? ".png" : ".jpg";
					$rutaLogo = $directorio . $aleatorio . $extension;

					move_uploaded_file($_FILES["nuevoLogo"]["tmp_name"], $rutaLogo);

				}

			}

			$tabla = "configuracion";

			// Procesar checkboxes de alertas

			$alertaStockBajo = isset($_POST["alertaStockBajo"]) ? 1 : 0;

			$alertaStockAgotado = isset($_POST["alertaStockAgotado"]) ? 1 : 0;

			$alertaActividadesPendientes = isset($_POST["alertaActividadesPendientes"]) ? 1 : 0;

			$alertaGastosProximos = isset($_POST["alertaGastosProximos"]) ? 1 : 0;

			$alertaAgenteIA = 1; // Siempre activado según requerimiento

			$configActualGlobal = ModeloConfiguracion::mdlObtenerConfiguracion();
			$controlCaja = $configActualGlobal["control_caja"];

			$datos = array(
				"nombre_empresa" => $_POST["nombreEmpresa"],
				"nit" => $_POST["nitEmpresa"],
				"direccion" => $_POST["direccionEmpresa"],
				"telefono" => $_POST["telefonoEmpresa"],
				"correo" => $_POST["correoEmpresa"],
				"logo" => $rutaLogo,
				"impuesto_defecto" => 0,
				"moneda" => $_POST["moneda"],
				"formato_codigo_venta" => $_POST["formatoCodigoVenta"],
				"medios_pago" => $_POST["mediosPago"],
				"tipo_codigo_producto" => $_POST["tipoCodigoProducto"],
				"alerta_stock_bajo" => $alertaStockBajo,
				"umbral_stock_minimo" => $_POST["umbralStockMinimo"],
				"alerta_stock_agotado" => $alertaStockAgotado,
				"alerta_actividades_pendientes" => $alertaActividadesPendientes,
				"dias_antes_actividad" => $_POST["diasAntesActividad"],
				"alerta_gastos_proximos" => $alertaGastosProximos,
				"dias_antes_gasto" => $_POST["diasAntesGasto"],
				"alerta_agente_ia" => $alertaAgenteIA,
				"control_caja" => $controlCaja,
				"mensaje_ticket" => $_POST["mensajeTicket"],
				"color_principal" => $_POST["colorPrincipal"],
				"color_secundario" => $_POST["colorSecundario"],
				"mensaje_recibido" => isset($_POST["mensajeRecibido"]) ? $_POST["mensajeRecibido"] : "",
				"mensaje_procesado" => isset($_POST["mensajeProcesado"]) ? $_POST["mensajeProcesado"] : "",
				"mensaje_confirmado" => isset($_POST["mensajeConfirmado"]) ? $_POST["mensajeConfirmado"] : ""
			);

			/*=============================================
			TRANSACCIÓN PDO: ACTUALIZAR CONFIGURACIÓN
			=============================================*/
			$db = Conexion::conectar();
			try {
				$db->beginTransaction();

				$respuesta = ModeloConfiguracion::mdlActualizarConfiguracion($tabla, $datos);
				if ($respuesta != "ok") {
					throw new Exception("Error al actualizar la configuración principal.");
				}

				// ACTUALIZAR CONFIGURACIÓN DE FACTUS DENTRO DE LA MISMA TRANSACCIÓN
				$logoFactusAnteriorAEliminar = null;
				if (isset($_POST["nombrefactus"])) {
					$configFactus = ModeloFactus::mdlObtenerConfiguracion();

					// Validación de DV obligatorio para Persona Jurídica
					if ($configFactus['bloqueo_datos_emisor'] != 1) {
						$tipoPersona = isset($_POST["tipopersonafactus"]) ? $_POST["tipopersonafactus"] : '2';
						$dv = isset($_POST["dvfactus"]) ? $_POST["dvfactus"] : '';
						if ($tipoPersona == '1' && trim($dv) === '') {
							throw new Exception("El campo DV (Dígito de Verificación) es obligatorio cuando el Tipo de Persona es Persona Jurídica.");
						}
					}

					/*=============================================
					PREPARAR LOGO FACTUS (nuevo archivo físico)
					=============================================*/

					$rutaLogoFactus = isset($configFactus["logo_empresa"]) ? $configFactus["logo_empresa"] : "";

					if (isset($_FILES["nuevoLogoFactus"]["tmp_name"]) && !empty($_FILES["nuevoLogoFactus"]["tmp_name"])) {

						list($ancho, $alto) = getimagesize($_FILES["nuevoLogoFactus"]["tmp_name"]);

						$nuevoAncho = 500;
						$nuevoAlto = 500;

						$directorio = "vistas/img/configuracion/";

						if (!file_exists($directorio)) {
							mkdir($directorio, 0755, true);
						}

						// Registrar logo Factus anterior para eliminar DESPUÉS del commit
						if (!empty($configFactus["logo_empresa"]) && file_exists($configFactus["logo_empresa"])) {
							$logoFactusAnteriorAEliminar = $configFactus["logo_empresa"];
						}

						/*=============================================
						PROCESAR IMAGEN CON FALLBACK SI NO EXISTE GD
						=============================================*/

						if (function_exists('imagecreatefromjpeg') && function_exists('imagecreatetruecolor')) {

							if ($_FILES["nuevoLogoFactus"]["type"] == "image/jpeg") {
								$aleatorio = mt_rand(100, 999);
								$rutaLogoFactus = $directorio . "factus_" . $aleatorio . ".jpg";
								$origen = imagecreatefromjpeg($_FILES["nuevoLogoFactus"]["tmp_name"]);
								$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
								imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
								imagejpeg($destino, $rutaLogoFactus);
							}

							if ($_FILES["nuevoLogoFactus"]["type"] == "image/png") {
								$aleatorio = mt_rand(100, 999);
								$rutaLogoFactus = $directorio . "factus_" . $aleatorio . ".png";
								$origen = imagecreatefrompng($_FILES["nuevoLogoFactus"]["tmp_name"]);
								$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
								imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
								imagepng($destino, $rutaLogoFactus);
							}

						} else {

							// Fallback: Mover el archivo directamente si GD no está activo
							$aleatorio = mt_rand(100, 999);
							$extension = ($_FILES["nuevoLogoFactus"]["type"] == "image/png") ? ".png" : ".jpg";
							$rutaLogoFactus = $directorio . "factus_" . $aleatorio . $extension;

							move_uploaded_file($_FILES["nuevoLogoFactus"]["tmp_name"], $rutaLogoFactus);

						}

					}

					$datosFactus = array(
						"api_url" => $configFactus['api_url'],
						"client_id" => $configFactus['client_id'],
						"client_secret" => $configFactus['client_secret'],
						"username" => $configFactus['username'],
						"password" => $configFactus['password'],
						"ambiente" => $configFactus['ambiente'],
						"activo" => $configFactus['activo'],
						"rango_numeracion_id" => $configFactus['rango_numeracion_id'],
						// Determine if we should update company data based on lock status
						"nombre_empresa" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['nombre_empresa'] : $_POST["nombrefactus"],
						"nombre_comercial" => ($configFactus['bloqueo_datos_emisor'] == 1) ? (isset($configFactus['nombre_comercial']) ? $configFactus['nombre_comercial'] : '') : (isset($_POST["nombrecomercialfactus"]) ? $_POST["nombrecomercialfactus"] : ''),
						"nit_empresa" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['nit_empresa'] : $_POST["nitfactus"],
						"direccion_empresa" => $_POST["direccionfactus"],
						"telefono_empresa" => $_POST["telefonofactus"],
						"email_empresa" => $_POST["emailfactus"],
						"municipio_id" => $_POST["municipiofactus"],
						// Campos extendidos
						"tributo_emisor" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['tributo_emisor'] : (isset($_POST["tributofactus"]) ? $_POST["tributofactus"] : 'no_responsable'),
						"actividad_economica" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['actividad_economica'] : (isset($_POST["actividadfactus"]) ? $_POST["actividadfactus"] : null),
						"registro_mercantil" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['registro_mercantil'] : (isset($_POST["registrofactus"]) ? $_POST["registrofactus"] : null),
						"dv" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['dv'] : (isset($_POST["dvfactus"]) ? $_POST["dvfactus"] : null),
						// Encode checkbox array as JSON
						"responsabilidades_fiscales" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['responsabilidades_fiscales'] : (isset($_POST["responsabilidadesfactus"]) ? json_encode($_POST["responsabilidadesfactus"]) : '[]'),
						"tipo_persona" => ($configFactus['bloqueo_datos_emisor'] == 1) ? $configFactus['tipo_persona'] : (isset($_POST["tipopersonafactus"]) ? $_POST["tipopersonafactus"] : '2'),
						// Mantener bloqueo actual
						"bloqueo_datos_emisor" => $configFactus['bloqueo_datos_emisor'],
						// Nuevo Logo Factus
						"logo_empresa" => $rutaLogoFactus
					);

					$respFactus = ModeloFactus::mdlActualizarConfiguracion($datosFactus);
					if ($respFactus != "ok") {
						throw new Exception("Error al actualizar la configuración de facturación.");
					}
				}

				$db->commit();

				// Eliminar logos anteriores DESPUÉS del commit para preservarlos si la BD falla
				if ($logoAnteriorAEliminar && file_exists($logoAnteriorAEliminar)) {
					unlink($logoAnteriorAEliminar);
				}
				if ($logoFactusAnteriorAEliminar && file_exists($logoFactusAnteriorAEliminar)) {
					unlink($logoFactusAnteriorAEliminar);
				}

				echo '<script>

				swal({
					  type: "success",
					  title: "La configuración ha sido actualizada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(() => {
							window.location = "configuracion";
						})

				</script>';

			} catch (Exception $e) {
				$db->rollBack();
				$mensajeError = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
				echo '<script>
					swal({
						type: "error",
						title: "Error al guardar la configuración",
						text: "' . $mensajeError . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "configuracion";
					})
				</script>';
			}

		}

	}

}