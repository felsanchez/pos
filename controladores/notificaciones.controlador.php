<?php

class ControladorNotificaciones
{

	/*=============================================
	CREAR NOTIFICACIÓN
	=============================================*/

	static public function ctrCrearNotificacion($tipo, $titulo, $mensaje, $referenciaTipo = null, $referenciaId = null)
	{

		$datos = array(
			"tipo" => $tipo,
			"titulo" => $titulo,
			"mensaje" => $mensaje,
			"referencia_tipo" => $referenciaTipo,
			"referencia_id" => $referenciaId
		);

		$respuesta = ModeloNotificaciones::mdlCrearNotificacion($datos);

		return $respuesta;

	}

	/*=============================================
	OBTENER NOTIFICACIONES
	=============================================*/

	static public function ctrObtenerNotificaciones($cantidad = null, $soloNoLeidas = false)
	{

		$idUsuario = $_SESSION["id"];
		$respuesta = ModeloNotificaciones::mdlObtenerNotificaciones($cantidad, $soloNoLeidas, $idUsuario);

		return $respuesta;

	}

	/*=============================================
	CONTAR NOTIFICACIONES NO LEÍDAS
	=============================================*/

	static public function ctrContarNoLeidas()
	{

		$idUsuario = $_SESSION["id"];
		$respuesta = ModeloNotificaciones::mdlContarNoLeidas($idUsuario);

		return $respuesta;

	}

	/*=============================================
	MARCAR COMO LEÍDA
	=============================================*/

	static public function ctrMarcarComoLeida()
	{

		if (isset($_POST["idNotificacion"])) {

			$id = $_POST["idNotificacion"];
			$idUsuario = $_SESSION["id"];

			$respuesta = ModeloNotificaciones::mdlMarcarComoLeida($id, $idUsuario);

			return $respuesta;

		}

	}

	/*=============================================
	MARCAR TODAS COMO LEÍDAS
	=============================================*/

	static public function ctrMarcarTodasComoLeidas()
	{

		if (isset($_POST["marcarTodasLeidas"])) {

			$idUsuario = $_SESSION["id"];
			$respuesta = ModeloNotificaciones::mdlMarcarTodasComoLeidas($idUsuario);

			return $respuesta;

		}

	}

	/*=============================================
	VERIFICAR STOCK DE PRODUCTOS Y GENERAR NOTIFICACIONES
	=============================================*/

	static public function ctrVerificarStockProductos()
	{

		// Obtener configuración
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();

		$alertaStockBajo = isset($configuracion["alerta_stock_bajo"]) ? $configuracion["alerta_stock_bajo"] : 1;
		$umbralStockMinimo = isset($configuracion["umbral_stock_minimo"]) ? $configuracion["umbral_stock_minimo"] : 5;
		$alertaStockAgotado = isset($configuracion["alerta_stock_agotado"]) ? $configuracion["alerta_stock_agotado"] : 1;

		// Si ninguna alerta está activa, no hacer nada
		if (!$alertaStockBajo && !$alertaStockAgotado) {
			return;
		}

		// Obtener todos los productos
		$productos = ControladorProductos::ctrMostrarProductos(null, null, "id");

		foreach ($productos as $producto) {

			// Verificar stock agotado
			if ($alertaStockAgotado && $producto["stock"] == 0) {

				// Verificar si ya existe una notificación no leída para este producto
				$existe = ModeloNotificaciones::mdlExisteNotificacionStock("stock_agotado", $producto["id"]);

				if (!$existe) {
					// Crear notificación
					ControladorNotificaciones::ctrCrearNotificacion(
						"stock_agotado",
						"Stock Agotado",
						"El producto \"" . $producto["descripcion"] . "\" (Código: " . $producto["codigo"] . ") se ha agotado.",
						"producto",
						$producto["id"]
					);
				}

			}
			// Verificar stock bajo (pero no agotado)
			else if ($alertaStockBajo && $producto["stock"] > 0 && $producto["stock"] <= $umbralStockMinimo) {

				// Verificar si ya existe una notificación no leída para este producto
				$existe = ModeloNotificaciones::mdlExisteNotificacionStock("stock_bajo", $producto["id"]);

				if (!$existe) {
					// Crear notificación
					ControladorNotificaciones::ctrCrearNotificacion(
						"stock_bajo",
						"Stock Bajo",
						"El producto \"" . $producto["descripcion"] . "\" (Código: " . $producto["codigo"] . ") tiene stock bajo: " . $producto["stock"] . " unidades.",
						"producto",
						$producto["id"]
					);
				}

			}

		}

	}

	/*=============================================
	VERIFICAR ACTIVIDADES PRÓXIMAS Y GENERAR NOTIFICACIONES
	=============================================*/

	static public function ctrVerificarActividadesProximas()
	{

		// Obtener configuración
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();

		$alertaActividades = isset($configuracion["alerta_actividades_pendientes"]) ? $configuracion["alerta_actividades_pendientes"] : 1;
		$diasAntes = isset($configuracion["dias_antes_actividad"]) ? $configuracion["dias_antes_actividad"] : 3;

		// Si la alerta está desactivada, no hacer nada
		if (!$alertaActividades) {
			return;
		}

		// Calcular fecha objetivo (hoy + días antes)
		$fechaObjetivo = date('Y-m-d', strtotime("+$diasAntes days"));
		$fechaHoy = date('Y-m-d');


		// Obtener actividades que vencen dentro del rango
		// Usamos ctrMostrarActividadesConCliente para obtener también el nombre del usuario
		$actividades = ModeloActividades::mdlMostrarActividadesConCliente("actividades", null, null);

		if (!$actividades) {
			return;
		}

		foreach ($actividades as $actividad) {

			// Solo alertar sobre actividades pendientes (no completadas)
			if (isset($actividad["fecha"]) && !empty($actividad["fecha"])) {

				$fechaActividad = date('Y-m-d', strtotime($actividad["fecha"]));

				// Si la fecha de la actividad está dentro del rango de alerta
				if ($fechaActividad >= $fechaHoy && $fechaActividad <= $fechaObjetivo) {

					// Verificar si ya existe una notificación no leída para esta actividad
					$existe = ModeloNotificaciones::mdlExisteNotificacion("actividad_proxima", $actividad["id"], "actividad");

					if (!$existe) {
						// Calcular días faltantes
						$diasFaltantes = (strtotime($fechaActividad) - strtotime($fechaHoy)) / 86400;
						$diasFaltantes = round($diasFaltantes);

						$mensajeDias = $diasFaltantes == 0 ? "hoy" : ($diasFaltantes == 1 ? "mañana" : "en $diasFaltantes días");

						// Obtener nombre de usuario (puede venir como nombre_usuario o nombre si el join es diferente)
						$nombreUsuario = isset($actividad["nombre_usuario"]) ? $actividad["nombre_usuario"] : "Desconocido";

						// Crear notificación
						ControladorNotificaciones::ctrCrearNotificacion(
							"actividad_proxima",
							"Actividad Próxima",
							"La actividad \"" . $actividad["descripcion"] . "\" de " . $nombreUsuario . " está programada para $diasFaltantes días (" . $actividad["fecha"] . ").",
							"actividad",
							$actividad["id"]
						);
					}

				}

			}

		}

	}

	/*=============================================
	VERIFICAR GASTOS PRÓXIMOS A VENCER Y GENERAR NOTIFICACIONES
	=============================================*/

	static public function ctrVerificarGastosProximos()
	{

		// Obtener configuración
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();

		$alertaGastos = isset($configuracion["alerta_gastos_proximos"]) ? $configuracion["alerta_gastos_proximos"] : 1;
		$diasAntes = isset($configuracion["dias_antes_gasto"]) ? $configuracion["dias_antes_gasto"] : 5;

		// Si la alerta está desactivada, no hacer nada
		if (!$alertaGastos) {
			return;
		}

		// Calcular fecha objetivo (hoy + días antes)
		$fechaObjetivo = date('Y-m-d', strtotime("+$diasAntes days"));
		$fechaHoy = date('Y-m-d');

		// Obtener gastos que vencen dentro del rango
		$gastos = ControladorGastos::ctrMostrarGastos(null, null);

		if (!$gastos) {
			return;
		}

		foreach ($gastos as $gasto) {

			// Solo alertar sobre gastos que tienen fecha de vencimiento
			if (isset($gasto["fecha"]) && !empty($gasto["fecha"])) {

				$fechaVencimiento = date('Y-m-d', strtotime($gasto["fecha"]));

				// Si la fecha de vencimiento está dentro del rango de alerta
				if ($fechaVencimiento >= $fechaHoy && $fechaVencimiento <= $fechaObjetivo) {

					// Verificar si ya existe una notificación no leída para este gasto
					$existe = ModeloNotificaciones::mdlExisteNotificacion("gasto_proximo", $gasto["id"], "gasto");

					if (!$existe) {
						// Calcular días faltantes
						$diasFaltantes = (strtotime($fechaVencimiento) - strtotime($fechaHoy)) / 86400;
						$diasFaltantes = round($diasFaltantes);

						$mensajeDias = $diasFaltantes == 0 ? "hoy" : ($diasFaltantes == 1 ? "mañana" : "en $diasFaltantes días");

						// Crear notificación
						ControladorNotificaciones::ctrCrearNotificacion(
							"gasto_proximo",
							"Gasto Próximo a Vencer",
							"El gasto \"" . $gasto["concepto"] . "\" vence $mensajeDias (" . $gasto["fecha"] . "). Monto: $" . number_format($gasto["monto"], 2) . ".",
							"gasto",
							$gasto["id"]
						);
					}

				}

			}

		}

	}


	/*=============================================
	VERIFICAR SI ORDEN PROVIENE DE AGENTE IA Y GENERAR NOTIFICACIÓN
	=============================================*/

	static public function ctrVerificarOrdenAgenteIA($codigoVenta = null)
	{

		// Obtener configuración
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();

		$alertaAgenteIA = isset($configuracion["alerta_agente_ia"]) ? $configuracion["alerta_agente_ia"] : 1;

		// Si la alerta está desactivada, no hacer nada
		if (!$alertaAgenteIA) {
			return;
		}

		$tabla = "ventas";

		// Si se proporciona un código específico, verificar solo esa orden
		if ($codigoVenta !== null) {

			$item = "codigo";
			$venta = ModeloVentas::mdlMostrarVentas($tabla, $item, $codigoVenta);

			if (!$venta) {
				return;
			}

			// Verificar si el campo 'notas' contiene 'Agente' (case-insensitive)
			if (isset($venta["notas"]) && !empty($venta["notas"]) && stripos($venta["notas"], "Agente") !== false) {

				// Verificar si YA fue notificado previamente (persistencia)
				if (stripos($venta["notas"], "[Notificado]") === false) {

					// Verificar si ya existe una notificación (para evitar doble insert en la misma ejecución)
					$existe = ModeloNotificaciones::mdlExisteNotificacion("orden_agente_ia", $venta["id"], "venta");

					if (!$existe) {
						// Crear notificación
						ControladorNotificaciones::ctrCrearNotificacion(
							"orden_agente_ia",
							"Orden desde Agente IA",
							"La orden #" . $venta["codigo"] . " fue creada desde el Agente IA.",
							"venta",
							$venta["id"]
						);
					}

					// MARCAR como notificado en la venta para evitar que vuelva a aparecer si se borra la notificación
					$nuevasNotas = $venta["notas"] . " [Notificado]";
					$datosActualizacion = array(
						"id" => $venta["id"],
						"notas" => $nuevasNotas
					);
					ModeloVentas::mdlActualizarNotaVenta("ventas", $datosActualizacion);
				}
			}

		} else {
			// Si no se proporciona código, verificar todas las órdenes
			$ordenes = ModeloVentas::mdlMostrarVentas($tabla, null, null);

			if (!$ordenes) {
				return;
			}

			foreach ($ordenes as $venta) {

				// Solo verificar órdenes (no ventas)
				if ($venta["estado"] != "orden") {
					continue;
				}

				// Verificar si el campo 'notas' contiene 'Agente' (case-insensitive)
				if (isset($venta["notas"]) && !empty($venta["notas"]) && stripos($venta["notas"], "Agente") !== false) {

					// Verificar si YA fue notificado previamente (persistencia)
					if (stripos($venta["notas"], "[Notificado]") === false) {

						// Verificar si ya existe una notificación (para evitar doble insert en la misma ejecución)
						$existe = ModeloNotificaciones::mdlExisteNotificacion("orden_agente_ia", $venta["id"], "venta");

						if (!$existe) {

							// Crear notificación
							ControladorNotificaciones::ctrCrearNotificacion(
								"orden_agente_ia",
								"Orden desde Agente IA",
								"La orden #" . $venta["codigo"] . " fue creada desde el Agente IA.",
								"venta",
								$venta["id"]
							);
						}

						// MARCAR como notificado en la venta para evitar que vuelva a aparecer si se borra la notificación
						$nuevasNotas = $venta["notas"] . " [Notificado]";
						$datosActualizacion = array(
							"id" => $venta["id"],
							"notas" => $nuevasNotas
						);
						ModeloVentas::mdlActualizarNotaVenta("ventas", $datosActualizacion);
					}
				}

			}

		}
	}

	/*=============================================
	VERIFICAR SI ORDEN CONTIENE 'n8n' EN EL CAMPO EXTRA
	=============================================*/

	static public function ctrVerificarOrdenn8n($codigoVenta = null)
	{

		$tabla = "ventas";

		// Si se proporciona un código específico, verificar solo esa orden
		if ($codigoVenta !== null) {

			$item = "codigo";
			$venta = ModeloVentas::mdlMostrarVentas($tabla, $item, $codigoVenta);

			if (!$venta) {
				return;
			}

			// Verificar si el campo 'extra' contiene 'n8n' (case-insensitive)
			if (isset($venta["extra"]) && !empty($venta["extra"]) && stripos($venta["extra"], "n8n") !== false) {

				// Verificar si YA fue notificado previamente (persistencia)
				if (!isset($venta["notas"]) || stripos($venta["notas"], "[Notificado_n8n]") === false) {

					// Verificar si ya existe una notificación
					$existe = ModeloNotificaciones::mdlExisteNotificacion("orden_creada", $venta["id"], "venta");

					if (!$existe) {
						// Obtener nombre del cliente
						$cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
						$nombreCliente = ($cliente && isset($cliente["nombre"])) ? $cliente["nombre"] : "Cliente Desconocido";

						// Crear notificación
						ControladorNotificaciones::ctrCrearNotificacion(
							"orden_creada",
							"Orden generada automáticamente",
							"Código: " . $venta["codigo"] . " - Cliente: " . $nombreCliente . " - Total: $" . number_format($venta["total"], 2),
							"venta",
							$venta["id"]
						);
					}

					// MARCAR como notificado en la venta para evitar que vuelva a aparecer si se borra la notificación
					$nuevasNotas = (isset($venta["notas"]) ? $venta["notas"] : "") . " [Notificado_n8n]";
					$datosActualizacion = array(
						"id" => $venta["id"],
						"notas" => trim($nuevasNotas)
					);
					ModeloVentas::mdlActualizarNotaVenta("ventas", $datosActualizacion);
				}
			}

		} else {
			// Si no se proporciona código, verificar todas las órdenes
			$ordenes = ModeloVentas::mdlMostrarVentas($tabla, null, null);

			if (!$ordenes) {
				return;
			}

			foreach ($ordenes as $venta) {

				// Solo verificar órdenes (no ventas)
				if ($venta["estado"] != "orden") {
					continue;
				}

				// Verificar si el campo 'extra' contiene 'n8n' (case-insensitive)
				if (isset($venta["extra"]) && !empty($venta["extra"]) && stripos($venta["extra"], "n8n") !== false) {

					// Verificar si YA fue notificado previamente (persistencia)
					if (!isset($venta["notas"]) || stripos($venta["notas"], "[Notificado_n8n]") === false) {

						// Verificar si ya existe una notificación (para evitar doble insert en la misma ejecución)
						$existe = ModeloNotificaciones::mdlExisteNotificacion("orden_creada", $venta["id"], "venta");

						if (!$existe) {

							// Obtener nombre del cliente
							$cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
							$nombreCliente = ($cliente && isset($cliente["nombre"])) ? $cliente["nombre"] : "Cliente Desconocido";

							// Crear notificación
							ControladorNotificaciones::ctrCrearNotificacion(
								"orden_creada",
								"Orden generada automáticamente",
								"Código: " . $venta["codigo"] . " - Cliente: " . $nombreCliente . " - Total: $" . number_format($venta["total"], 2),
								"venta",
								$venta["id"]
							);
						}

						// MARCAR como notificado en la venta para evitar que vuelva a aparecer si se borra la notificación
						$nuevasNotas = (isset($venta["notas"]) ? $venta["notas"] : "") . " [Notificado_n8n]";
						$datosActualizacion = array(
							"id" => $venta["id"],
							"notas" => trim($nuevasNotas)
						);
						ModeloVentas::mdlActualizarNotaVenta("ventas", $datosActualizacion);
					}
				}

			}

		}

	}

	/*=============================================
	VERIFICAR SOLICITUDES DE AGENTE IA (EDICIÓN Y ELIMINACIÓN)
	=============================================*/

	static public function ctrVerificarSolicitudesAgenteIA()
	{

		// 1. Verificar Solicitudes de Edición
		$solicitudesEdicion = ModeloNotificaciones::mdlObtenerSolicitudesEdicion();

		if ($solicitudesEdicion) {

			foreach ($solicitudesEdicion as $solicitud) {

				// ID único de la solicitud
				$idSolicitud = $solicitud["id"];
				$codigoVenta = $solicitud["codigo_venta"];
				$descripcion = $solicitud["descripcion"];
				$nombreCliente = $solicitud["nombre_cliente"];
				$celularCliente = $solicitud["celular_cliente"];

				// Tipo solicitado: "Edicion de pedido - CODIGO"
				$tipoNotificacion = "Edicion de pedido - " . $codigoVenta;

				// Mensaje solicitado: Descripcion + (Nombre - Celular)
				$mensajeNotificacion = $descripcion . " (" . $nombreCliente . " - " . $celularCliente . ")";

				// Verificar si ya existe notificación para esta solicitud específica
				$existe = ModeloNotificaciones::mdlExisteNotificacion($tipoNotificacion, $idSolicitud, "solicitud_edicion");

				if (!$existe) {
					ControladorNotificaciones::ctrCrearNotificacion(
						$tipoNotificacion,
						"Solicitud del AgenteIA",
						$mensajeNotificacion,
						"solicitud_edicion",
						$idSolicitud
					);
				}
			}
		}

		// 2. Verificar Solicitudes de Eliminación
		$solicitudesEliminacion = ModeloNotificaciones::mdlObtenerSolicitudesEliminacion();

		if ($solicitudesEliminacion) {

			foreach ($solicitudesEliminacion as $solicitud) {

				// ID único de la solicitud
				$idSolicitud = $solicitud["id"];
				$codigoVenta = $solicitud["codigo_venta"];
				$motivo = $solicitud["motivo"];
				$nombreCliente = $solicitud["nombre_cliente"];
				$celularCliente = $solicitud["celular_cliente"];

				// Tipo solicitado: "Eliminacion de pedido - CODIGO"
				$tipoNotificacion = "Eliminacion de pedido - " . $codigoVenta;

				// Mensaje solicitado: Motivo + (Nombre - Celular)
				$mensajeNotificacion = $motivo . " (" . $nombreCliente . " - " . $celularCliente . ")";

				// Verificar si ya existe notificación
				$existe = ModeloNotificaciones::mdlExisteNotificacion($tipoNotificacion, $idSolicitud, "solicitud_eliminacion");

				if (!$existe) {
					ControladorNotificaciones::ctrCrearNotificacion(
						$tipoNotificacion,
						"Solicitud del AgenteIA",
						$mensajeNotificacion,
						"solicitud_eliminacion",
						$idSolicitud
					);
				}
			}
		}

	}

	/*=============================================
	VERIFICAR Y SINCRONIZAR PAGOS BOLD
	=============================================*/
	static public function ctrVerificarPagosBold()
	{
		$respuesta = ModeloNotificaciones::mdlSincronizarPagosBold();
		return $respuesta;
	}

}