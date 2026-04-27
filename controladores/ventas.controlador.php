<?php

//date_default_timezone_set('America/Bogota');

class ControladorVentas
{

	/*=============================================
	 MOSTRAR VENTAS
	 =============================================*/

	static public function ctrMostrarVentas($item, $valor)
	{

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	MOSTRAR VENTAS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarVentasServerSide($params)
	{
		$tabla = "ventas";

		// Mapeo de columnas para ordenamiento
		$columnsMap = array(
			0 => 'v.codigo',
			1 => 'c.nombre',
			2 => 'u.nombre',
			3 => 'v.metodo_pago',
			4 => 'v.id', // Imagen
			5 => 'v.total',
			6 => 'v.notas',
			7 => 'v.observacion',
			8 => 'v.fecha'
		);

		// Obtener configuración para moneda y formato
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
		$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
		$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";

		// Filtros base (Excluir Facturas Electrónicas)
		$where = " WHERE v.estado = 'venta' AND (v.numero_factura IS NULL OR v.numero_factura = '') AND (v.resolucion_id IS NULL OR v.resolucion_id = 0) ";

		// Filtro por Fechas
		if (!empty($params['fechaInicial']) && !empty($params['fechaFinal'])) {
			$where .= " AND DATE(v.fecha) >= '" . $params['fechaInicial'] . "' AND DATE(v.fecha) <= '" . $params['fechaFinal'] . "' ";
		}

		// Filtro por Cliente
		if (isset($params['clienteId']) && $params['clienteId'] !== "" && is_numeric($params['clienteId'])) {
			$where .= " AND v.id_cliente = " . $params['clienteId'];
		}

		// Filtro por Vendedor
		if (isset($params['usuarioId']) && $params['usuarioId'] !== "" && is_numeric($params['usuarioId'])) {
			$where .= " AND v.id_vendedor = " . $params['usuarioId'];
		}

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (v.codigo LIKE '%$searchValue%' OR c.nombre LIKE '%$searchValue%' OR u.nombre LIKE '%$searchValue%' OR v.notas LIKE '%$searchValue%' OR v.observacion LIKE '%$searchValue%') ";
		}

		// Orden
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'v.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY v.id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$ventas = ModeloVentas::mdlMostrarVentasServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloVentas::mdlGetTotalVentas($tabla, " WHERE v.estado = 'venta' AND (v.numero_factura IS NULL OR v.numero_factura = '') AND (v.resolucion_id IS NULL OR v.resolucion_id = 0) ");
		$totalFiltered = ModeloVentas::mdlGetTotalVentas($tabla, $where);

		$data = array();

		foreach ($ventas as $key => $value) {
			
			$nestedData = array();

			// 0: Código
			$codigoHtml = "";
			if (!empty($value["numero_factura"])) {
				$codigoHtml = '<span style="font-weight:bold; font-size:1.1em; color:#605ca8;">' . $value["numero_factura"] . '</span>';
				$codigoHtml .= '<br><span style="font-size:0.85em; color:#999;">Ref: ' . $formatoCodigoVenta . $value["codigo"] . '</span>';
			} else {
				$codigoHtml = $formatoCodigoVenta . $value["codigo"];
			}
			$nestedData[] = $codigoHtml;

			// 1: Cliente
			$nestedData[] = '<span class="btnVerClienteDesdeVenta" data-toggle="modal" data-target="#modalEditarCliente" idCliente="' . $value["id_cliente"] . '" style="cursor: pointer; color: #337ab7; text-decoration: underline;">' . e($value["nombre_cliente"]) . '</span>';

			// 2: Vendedor
			$nestedData[] = e($value["nombre_vendedor"]);

			// 3: Forma de pago
			$nestedData[] = $moneda . ' ' . $value["metodo_pago"];

			// 4: Imagen
			$imgSrc = $value["imagen"] != "" ? $value["imagen"] : "vistas/img/ventas/default/sinventa.png";
			$nestedData[] = '<img src="' . $imgSrc . '" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="' . $imgSrc . '" data-idventa="' . $value["id"] . '">';

			// 5: Total
			$nestedData[] = $moneda . ' ' . number_format($value["total"], 2);

			// 6: Notas
			$nestedData[] = $value['notas'];

			// 7: Observación (Editable)
			$nestedData[] = '<div contenteditable="true" class="celda-observacion" data-id="' . $value['id'] . '">' . $value['observacion'] . '</div>';

			// 8: Fecha
			$nestedData[] = $value["fecha"];

			// 9: Acciones
			$botonesAcciones = '<div class="btn-group col-acciones">';
			if (puedeAccion('ventas', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarVenta" idVenta="' . $value["id"] . '" title="Ver detalle" style="width: auto !important;"><i class="fa fa-eye"></i></button>';
			}
			if (puedeAccion('ventas', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '" title="Eliminar venta"><i class="fa fa-times"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			// Metadatos para JS
			$nestedData['DT_RowAttr'] = array(
				'data-venta-id' => $value['id']
			);

			$data[] = $nestedData;
		}

		return array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);
	}

	/*=============================================
	MOSTRAR ÓRDENES SERVER-SIDE
	=============================================*/
	static public function ctrMostrarOrdenesServerSide($params)
	{
		$tabla = "ventas";

		// Mapeo de columnas para ordenamiento:
		// 0=Código, 1=Cliente, 2=Vendedor, 3=Forma de pago, 4=Imagen, 5=Total, 6=Notas, 7=Observación, 8=Fecha, 9=Seguimiento, 10=Acciones
		$columnsMap = array(
			0 => 'v.codigo',
			1 => 'c.nombre',
			2 => 'u.nombre',
			3 => 'v.metodo_pago',
			4 => 'v.id', // Imagen
			5 => 'v.total',
			6 => 'v.notas',
			7 => 'v.observacion',
			8 => 'v.fecha'
		);

		// Obtener configuración para moneda y formato
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
		$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
		$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";
		$mensajeRecibido = !empty($configuracion["mensaje_recibido"]) ? $configuracion["mensaje_recibido"] : "Su pedido ha sido recibido";
		$mensajeProcesado = !empty($configuracion["mensaje_procesado"]) ? $configuracion["mensaje_procesado"] : "Su pedido ha sido procesado";

		// Filtros base para órdenes
		$where = " WHERE v.estado = 'orden' ";

		// Filtro por Fechas
		if (!empty($params['fechaInicial']) && !empty($params['fechaFinal'])) {
			$where .= " AND DATE(v.fecha) >= '" . $params['fechaInicial'] . "' AND DATE(v.fecha) <= '" . $params['fechaFinal'] . "' ";
		}

		// Filtro por Cliente
		if (!empty($params['clienteId']) && is_numeric($params['clienteId'])) {
			$where .= " AND v.id_cliente = " . $params['clienteId'];
		}

		// Filtro por Vendedor
		if (!empty($params['usuarioId']) && is_numeric($params['usuarioId'])) {
			$where .= " AND v.id_vendedor = " . $params['usuarioId'];
		}

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (v.codigo LIKE '%$searchValue%' OR c.nombre LIKE '%$searchValue%' OR u.nombre LIKE '%$searchValue%' OR v.notas LIKE '%$searchValue%' OR v.observacion LIKE '%$searchValue%') ";
		}

		// Orden
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'v.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY v.id DESC";
		}

		// Paginación
		$limit = "";
		if (isset($params['length']) && $params['length'] != -1) {
			$limit = " LIMIT " . intval($params['start']) . ", " . intval($params['length']);
		}

		// Obtener datos
		$ordenes = ModeloVentas::mdlMostrarVentasServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloVentas::mdlGetTotalVentas($tabla, " WHERE v.estado = 'orden' ");
		$totalFiltered = ModeloVentas::mdlGetTotalVentas($tabla, $where);

		$data = array();

		foreach ($ordenes as $key => $value) {
			
			$nestedData = array();

			// 0: Código
			$nestedData[] = e($formatoCodigoVenta) . e($value["codigo"]);

			// 1: Cliente
			$telefonoCli = isset($value["telefono_cliente"]) ? $value["telefono_cliente"] : "";
			// Si no viene en el query (v.* no lo trae si no se especifica), lo buscamos una vez si es necesario
			// Pero mejor optimizar el query si es posible. Por ahora, si no existe, lo buscamos.
			if ($telefonoCli == "" && !empty($value["id_cliente"])) {
				$clienteInfo = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
				$telefonoCli = $clienteInfo ? $clienteInfo["telefono"] : "";
			}

			$nestedData[] = '<span class="btnVerClienteDesdeVenta" data-toggle="modal" data-target="#modalEditarCliente" idCliente="' . e($value["id_cliente"]) . '" style="cursor: pointer; color: #337ab7; text-decoration: underline;">' . e($value["nombre_cliente"]) . '</span>';

			// 2: Vendedor
			$nestedData[] = e($value["nombre_vendedor"]);

			// 3: Forma de pago
			$nestedData[] = e($moneda) . ' ' . e($value["metodo_pago"]);

			// 4: Imagen
			$imgSrc = ($value["imagen"] != "" && $value["imagen"] != null) ? $value["imagen"] : "vistas/img/ventas/default/sinventa.png";
			$nestedData[] = '<img src="' . $imgSrc . '" class="img-thumbnail img-ampliar-orden" width="40px" style="cursor: pointer;" data-imagen="' . $imgSrc . '" data-idventa="' . $value["id"] . '">';

			// 5: Total
			$nestedData[] = e($moneda) . ' ' . e(number_format($value["total"], 2));

			// 6: Notas (Editable)
			$nestedData[] = '<div contenteditable="true" class="celda-nota" data-id="' . e($value['id']) . '">' . e($value['notas']) . '</div>';

			// 7: Observación (Editable)
			$nestedData[] = '<div contenteditable="true" class="celda-observacion" data-id="' . e($value['id']) . '">' . e($value['observacion']) . '</div>';

			// 8: Fecha
			$nestedData[] = e($value["fecha"]);

			// 9: Seguimiento
			$htmlSeguimiento = '<div style="white-space:nowrap; text-align:center;">';

			// Botón 1: Recibido
			$recibido = isset($value["seguimiento_recibido"]) ? $value["seguimiento_recibido"] : 0;
			if ($recibido == 1) {
				$htmlSeguimiento .= '<span class="label label-success" style="margin-right:5px;">Enviado (R)</span>';
			} else {
				if (puedeAccion('ordenes', 'editar')) {
					$htmlSeguimiento .= '<button class="btn btn-default btn-xs btnSeguimientoRecibido" 
								idOrden="' . e($value["id"]) . '" 
								codigoOrden="' . e($value["codigo"]) . '"
								cliente="' . e($value["nombre_cliente"]) . '"
								telefono="' . e($telefonoCli) . '"
								data-mensaje-recibido="' . e(htmlspecialchars($mensajeRecibido)) . '"
								style="margin-right:5px; border: 1px solid #ccc; color: green; width: auto !important;" 
								title="Enviar mensaje: Pedido Recibido">
								1er mensaje
							</button>';
				}
			}

			// Botón 2: Procesado
			$procesado = isset($value["seguimiento_procesado"]) ? $value["seguimiento_procesado"] : 0;
			if ($procesado == 1) {
				$htmlSeguimiento .= '<span class="label label-success" style="margin-right:5px;">Enviado (P)</span>';
			} else {
				if (puedeAccion('ordenes', 'editar')) {
					$htmlSeguimiento .= '<button class="btn btn-default btn-xs btnSeguimientoProcesado" 
								  idOrden="' . e($value["id"]) . '" 
								  codigoOrden="' . e($value["codigo"]) . '"
								  cliente="' . e($value["nombre_cliente"]) . '"
								  telefono="' . e($telefonoCli) . '"
								  data-mensaje-procesado="' . e(htmlspecialchars($mensajeProcesado)) . '"
								  style="margin-right:5px; border: 1px solid #ccc; color: blue; width: auto !important;" 
								  title="Enviar mensaje: Pedido Procesado">
								  2do mensaje
							   </button>';
				}
			}

			// Botón 3: Alistado / Enviar a Ventas
			$alistado = isset($value["seguimiento_alistado"]) ? $value["seguimiento_alistado"] : 0;
			if (puedeAccion('ordenes', 'editar')) {
				if ($alistado == 1) {
					$htmlSeguimiento .= '<a href="index.php?ruta=editar-orden&idVenta=' . $value["id"] . '" class="btn btn-xs btn-success" title="Pedido Alistado / Editado" style="width: auto !important;">Enviado (A) <i class="fa fa-line-chart"></i></a>';
				} else {
					$htmlSeguimiento .= '<a href="index.php?ruta=editar-orden&idVenta=' . $value["id"] . '" class="btn btn-xs btn-warning" title="Editar Orden" style="width: auto !important;">Enviar a Ventas</a>';
				}
			}

			// Botón 4: Convertir a Factura Electrónica
			if (puedeAccion('ordenes', 'editar')) {
				$htmlSeguimiento .= ' <a href="index.php?ruta=orden-a-factura-electronica&idVenta=' . $value["id"] . '" 
							class="btn btn-xs btn-primary" 
							title="Convertir a Factura Electrónica" 
							style="width: auto !important; margin-left: 3px; background-color: #605ca8; border-color: #605ca8;">
							<i class="fa fa-file-text-o"></i> Enviar a FE
						</a>';
			}

			$htmlSeguimiento .= '</div>';
			$nestedData[] = $htmlSeguimiento;

			// 10: Acciones
			$botonesAcciones = '<div class="btn-group">';
			$botonesAcciones .= '<a class="btn btn-warning" href="index.php?ruta=ver-detalle-orden&idVenta=' . $value["id"] . '" title="Ver Detalle" style="width: auto !important;"><i class="fa fa-eye"></i></a>';
			
			if (puedeAccion('ordenes', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '" title="Eliminar Orden" style="width: auto !important;"><i class="fa fa-times"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			// Metadatos para JS
			$nestedData['DT_RowAttr'] = array(
				'data-orden-id' => $value['id']
			);

			$data[] = $nestedData;
		}

		return array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);
	}

	/*=============================================
	MOSTRAR FACTURAS ELECTRÓNICAS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarFacturasElectronicasServerSide($params)
	{
		$tabla = "ventas";

		// Mapeo de columnas para ordenamiento:
		// 0=Código, 1=Cliente, 2=Vendedor, 3=Forma de pago, 4=Imagen, 5=Total, 6=Estado DIAN, 7=Notas, 8=Observación, 9=Fecha, 10=Acciones
		$columnsMap = array(
			0 => 'v.numero_factura',
			1 => 'c.nombre',
			2 => 'u.nombre',
			3 => 'v.metodo_pago',
			4 => 'v.id', // Imagen
			5 => 'v.total',
			6 => 'v.estado_dian',
			7 => 'v.notas',
			8 => 'v.observacion',
			9 => 'v.fecha'
		);

		// Obtener configuración
		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
		$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
		$prefijoDian = !empty($configuracion["prefijo_dian"]) ? $configuracion["prefijo_dian"] : "FE";

		// Filtros base para Facturas Electrónicas
		$where = " WHERE v.estado = 'venta' AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL) ";

		// Filtro por Fechas
		if (!empty($params['fechaInicial']) && !empty($params['fechaFinal'])) {
			$where .= " AND v.fecha BETWEEN '" . $params['fechaInicial'] . " 00:00:00' AND '" . $params['fechaFinal'] . " 23:59:59' ";
		}

		// Filtro por Cliente
		if (!empty($params['clienteId']) && is_numeric($params['clienteId'])) {
			$where .= " AND v.id_cliente = " . $params['clienteId'];
		}

		// Filtro por Vendedor
		if (!empty($params['usuarioId']) && is_numeric($params['usuarioId'])) {
			$where .= " AND v.id_vendedor = " . $params['usuarioId'];
		}

		// Búsqueda global
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (v.numero_factura LIKE '%$searchValue%' OR v.codigo LIKE '%$searchValue%' OR c.nombre LIKE '%$searchValue%' OR u.nombre LIKE '%$searchValue%' OR v.notas LIKE '%$searchValue%' OR v.observacion LIKE '%$searchValue%') ";
		}

		// Orden
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'v.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY v.fecha DESC";
		}

		// Paginación
		$limit = "";
		if (isset($params['length']) && $params['length'] != -1) {
			$limit = " LIMIT " . intval($params['start']) . ", " . intval($params['length']);
		}

		// Obtener datos
		$facturas = ModeloVentas::mdlMostrarVentasServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloVentas::mdlGetTotalVentas($tabla, " WHERE v.estado = 'venta' AND (v.numero_factura != '' OR v.resolucion_id IS NOT NULL) ");
		$totalFiltered = ModeloVentas::mdlGetTotalVentas($tabla, $where);

		$data = array();

		foreach ($facturas as $key => $value) {
			
			$nestedData = array();

			// 0: Código / Número Factura
			$esBorrador = false;
			if (!empty($value["numero_factura"])) {
				$numeroMostrar = $value["numero_factura"];
			} else {
				// Es borrador o pendiente de número
				if (in_array(($value["estado_dian"] ?? 'pendiente'), ['pendiente', 'creada', 'borrador'])) {
					$numeroMostrar = "Borrador (" . $value["codigo"] . ")";
					$esBorrador = true;
				} else {
					$numeroMostrar = $prefijoDian . $value["codigo"];
				}
			}
			$nestedData[] = '<span' . ($esBorrador ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . e($numeroMostrar) . '</span>';

			// 1: Cliente
			$nestedData[] = '<span class="btnVerClienteDesdeVenta" data-toggle="modal" data-target="#modalEditarCliente" idCliente="' . e($value["id_cliente"]) . '" style="cursor: pointer; color: #337ab7; text-decoration: underline;">' . e($value["nombre_cliente"]) . '</span>';

			// 2: Vendedor
			$nestedData[] = e($value["nombre_vendedor"]);

			// 3: Forma de pago
			$nestedData[] = e($moneda) . ' ' . e($value["metodo_pago"]);

			// 4: Imagen
			$imgSrc = ($value["imagen"] != "" && $value["imagen"] != null) ? $value["imagen"] : "vistas/img/ventas/default/sinventa.png";
			$nestedData[] = '<img src="' . $imgSrc . '" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="' . $imgSrc . '" data-idventa="' . $value["id"] . '">';

			// 5: Total
			$nestedData[] = e($moneda) . ' ' . e(number_format($value["total"], 2));

			// 6: Estado DIAN
			$estadoDian = isset($value["estado_dian"]) ? $value["estado_dian"] : 'pendiente';
			$badgeDian = '';
			if ($estadoDian == 'aceptada' || $estadoDian == 'enviada') {
				$badgeDian = '<button class="btn btn-success btn-xs">Exitosa</button>';
			} elseif ($estadoDian == 'borrador' || $estadoDian == 'creada' || $estadoDian == 'pendiente') {
				$badgeDian = '<button class="btn btn-warning btn-xs">Borrador</button>';
			} elseif ($estadoDian == 'rechazada') {
				$badgeDian = '<button class="btn btn-danger btn-xs">Rechazada</button>';
			} else {
				$badgeDian = '<button class="btn btn-danger btn-xs">Pendiente</button>';
			}
			$nestedData[] = $badgeDian;

			// 7: Notas
			$nestedData[] = e($value['notas']);

			// 8: Observación (Editable si no está enviada/aceptada)
			$esEditable = ($estadoDian != "enviada" && $estadoDian != "aceptada");
			$contentEditableAttr = $esEditable ? 'contenteditable="true"' : '';
			$claseEditable = $esEditable ? 'celda-observacion' : '';
			$nestedData[] = '<div ' . $contentEditableAttr . ' class="' . $claseEditable . '" data-id="' . e($value["id"]) . '">' . e($value["observacion"]) . '</div>';

			// 9: Fecha
			$nestedData[] = e($value["fecha"]);

			// 10: Acciones
			$botonesAcciones = '<div class="btn-group col-acciones" style="display:flex; gap:2px;">';
			
			// Ver Detalle Venta
			$botonesAcciones .= '<button class="btn btn-info btnVerDetalleVenta" idVenta="' . $value["id"] . '" data-toggle="modal" data-target="#modalVerFactura" title="Ver Detalle" style="width: auto !important;"><i class="fa fa-eye"></i></button>';
			
			// Ver en DIAN
			if (!empty($value["qr_data"])) {
				$botonesAcciones .= '<a class="btn btn-success" href="' . $value["qr_data"] . '" target="_blank" data-toggle="tooltip" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>';
			}

			if (puedeAccion('factura_electronica', 'editar')) {
				// Firmar (para borradores)
				if (isset($value["estado_dian"]) && $value["estado_dian"] == "creada") {
					$botonesAcciones .= '<button class="btn btnFirmarFactura" style="background-color: black; color: white; width: auto !important;" idVenta="' . $value["id"] . '" title="Firmar y Enviar a DIAN">
										<i class="fa fa-paper-plane"></i>
									</button>';
				}

				// Editar Borrador
				if (isset($value["estado_dian"]) && in_array($value["estado_dian"], ['creada', 'pendiente'])) {
					$botonesAcciones .= '<a class="btn btn-warning" href="index.php?ruta=editar-factura-electronica&idVenta=' . $value["id"] . '" title="Editar Borrador" style="width: auto !important;">
										<i class="fa fa-pencil"></i>
									</a>';
				}

				// Enviar por Correo
				if ($estadoDian == 'aceptada' || $estadoDian == 'enviada') {
					$botonesAcciones .= ' <button class="btn btn-primary btnEnviarEmail" idVenta="' . $value["id"] . '" nombreCliente="' . e($value["nombre_cliente"]) . '" emailCliente="' . e($value["email_cliente"]) . '" title="Enviar por Correo" style="width: auto !important;">
								<i class="fa fa-envelope"></i>
							</button>';
				}
			}

			if (puedeAccion('factura_electronica', 'eliminar')) {
				// Solo mostrar botón eliminar si la factura NO ha sido firmada/aceptada
				$estadosNoEliminables = ['enviada', 'aceptada'];
				if (!in_array($value["estado_dian"], $estadosNoEliminables)) {
					$botonesAcciones .= ' <button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '" title="Eliminar Borrador" style="width: auto !important;">
										<i class="fa fa-trash"></i>
									</button>';
				}
			}

			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			// Metadatos para JS
			$nestedData['DT_RowAttr'] = array(
				'data-fe-id' => $value['id']
			);

			$data[] = $nestedData;
		}

		return array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);
	}


	/*=============================================
	 CONTAR VENTAS
	 =============================================*/
	static public function ctrContarVentas()
	{
		$tabla = "ventas";
		$estado = "venta";
		$respuesta = ModeloVentas::mdlContarVentasPorEstado($tabla, $estado);
		return $respuesta;
	}

	/*=============================================
	 CONTAR ORDENES
	 =============================================*/
	static public function ctrContarOrdenes()
	{
		$tabla = "ventas";
		$estado = "orden";
		$respuesta = ModeloVentas::mdlContarVentasPorEstado($tabla, $estado);
		return $respuesta;
	}


	/*=============================================
	 CREAR VENTAS
	 =============================================*/

	static public function ctrCrearVenta()
	{

		// 🔹 MANEJAR EDICIÓN DE FACTURA EN BORRADOR
		if (isset($_POST["editarVentaFactus"]) && isset($_POST["idVenta"])) {

			$idVenta = $_POST["idVenta"];

			// Verificar que la factura existe y está en borrador
			$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

			if (!$venta || !in_array($venta["estado_dian"], ['creada', 'pendiente', null])) {
				echo '<script>
					swal({
						type: "error",
						title: "Error",
						text: "Esta factura no puede ser editada",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "facturas-electronicas";
					});
				</script>';
				return;
			}

			// Validar que haya productos
			if (empty($_POST["listaProductos"]) || $_POST["listaProductos"] == "") {
				echo '<script>
					swal({
						type: "error",
						title: "La factura no se puede actualizar sin productos",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "editar-factura-electronica&idVenta=' . $idVenta . '";
					});
				</script>';
				return;
			}

			// Obtener productos antiguos para restaurar stock
			$productosAntiguos = json_decode($venta["productos"], true);

			// Restaurar stock de productos antiguos
			foreach ($productosAntiguos as $producto) {
				$tablaProductos = "productos";
				$item = "id";
				$valor = $producto["id"];
				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, "id");

				$stockAntiguo = $traerProducto["stock"];
				$stockNuevo = $stockAntiguo + $producto["cantidad"];

				ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $stockNuevo, $producto["id"]);
			}

			// Procesar nuevos productos y actualizar stock
			$listaProductos = json_decode($_POST["listaProductos"], true);

			foreach ($listaProductos as $key => $value) {
				$tablaProductos = "productos";
				$item = "id";
				$valor = $value["id"];
				$orden = "id";

				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

				$stockActual = $traerProducto["stock"];
				$stockNuevo = $stockActual - $value["cantidad"];

				ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $stockNuevo, $value["id"]);
			}

			// Actualizar la venta
			$datos = array(
				"codigo" => $venta["codigo"], // WHERE clause
				"id_cliente" => $_POST["seleccionarCliente"],
				"id_vendedor" => $_POST["idVendedor"],
				"numero_factura" => $venta["numero_factura"], // Mantener el mismo
				"productos" => $_POST["listaProductos"],
				"impuesto" => $_POST["nuevoPrecioImpuesto"],
				"neto" => $_POST["nuevoPrecioNeto"],
				"total" => $_POST["totalVenta"],
				"metodo_pago" => $_POST["nuevoMetodoPago"],
				"notas" => isset($_POST["notas"]) ? $_POST["notas"] : "",
				"estado" => $venta["estado"], // Mantener el mismo
				"fecha" => $venta["fecha"], // Mantener la misma
				"tipo_descuento" => isset($_POST["tipoDescuento"]) ? $_POST["tipoDescuento"] : "",
				"valor_descuento" => isset($_POST["valorDescuento"]) ? $_POST["valorDescuento"] : 0,
				"monto_descuento" => isset($_POST["montoDescuento"]) ? $_POST["montoDescuento"] : 0,
				"recibe" => isset($_POST["recibe"]) ? $_POST["recibe"] : 0,
				"extra" => isset($_POST["extra"]) ? $_POST["extra"] : 0,
				"retenciones" => isset($_POST["datosRetenciones"]) ? $_POST["datosRetenciones"] : "",
				"resolucion_id" => $venta["resolucion_id"], // Mantener el mismo
				"fecha_vencimiento" => $venta["fecha_vencimiento"],
				"orden_compra" => $venta["orden_compra"],
				"forma_pago_dian" => $venta["forma_pago_dian"],
				"metodo_pago_dian_id" => $venta["metodo_pago_dian_id"],
				"estado_dian" => $venta["estado_dian"], // Mantener en borrador
				"cufe" => $venta["cufe"],
				"qr_data" => $venta["qr_data"],
				"xml_dian" => $venta["xml_dian"],
				"pdf_dian" => $venta["pdf_dian"],
				"mensaje_dian" => $venta["mensaje_dian"],
				"fecha_envio_dian" => $venta["fecha_envio_dian"]
			);

			$respuesta = ModeloVentas::mdlEditarVenta("ventas", $datos);

			if ($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "Factura actualizada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "facturas-electronicas";
					});
				</script>';
			}
			else {
				echo '<script>
					swal({
						type: "error",
						title: "Error al actualizar la factura",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}

			return;
		}

		if (isset($_POST["nuevaVenta"])) {

			// 🟢 DEBUG: Ver qué datos llegan al controlador
			file_put_contents("debug_ventas_post.txt", print_r($_POST, true));




			//No permitir ejecutar la venta si no hay productos añadidos
			if ($_POST["listaProductos"] == "") {

				echo '<script>

				swal({
					  type: "error",
					  title: "La venta no se puede ejecutar si no hay productos",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(() => {
								window.location = "ventas";
					  })

				</script>';

				return;
			}

			/*=============================================
			 ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL STOCK Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
			 =============================================*/

			$listaProductos = json_decode($_POST["listaProductos"], true);

			// 🔹 Usar el código que viene del formulario (ya calculado en la vista)
			$codigoVenta = $_POST["nuevaVenta"];
			$tabla = "ventas";

			// DEBUG: Ver qué productos llegan al crear orden/venta

			file_put_contents(
				"debug_crear_orden.txt",

				"=== CREAR ORDEN/VENTA ===\n" .

				"Estado: " . $_POST["estado"] . "\n" .

				"Codigo Venta: " . $codigoVenta . "\n" .

				"Lista Productos RAW: " . $_POST["listaProductos"] . "\n" .

				"Lista Productos DECODED:\n" . print_r($listaProductos, true) . "\n",

				FILE_APPEND

			);

			//exit;

			$totalProductosComprados = array();


			if ($_POST["estado"] == "venta") {

				// 🟢 VALIDACIÓN PREVIA DE FACTURA ELECTRÓNICA
				// Si se va a generar factura, validamos ANTES de guardar la venta y mover stock
				if ((isset($_POST["activarFacturaElectronica"]) && $_POST["activarFacturaElectronica"] == "1") || isset($_POST["guardarVentaFactus"])) {

					// Construir array simulado de venta para validar
					$ventaMock = array(
						"id_cliente" => $_POST["seleccionarCliente"],
						"productos" => $_POST["listaProductos"],
						"metodo_pago" => $_POST["listaMetodoPago"],
						"forma_pago_dian" => $_POST["forma_pago_dian"] ?? "1",
						"metodo_pago_dian_id" => $_POST["metodo_pago_dian_id"] ?? null,
						"fecha_vencimiento" => $_POST["fecha_vencimiento"] ?? null,
						"notas" => $_POST["notas"] ?? ""
					);

					$validacion = ControladorFactus::ctrValidarDatosFactura($ventaMock);

					if (!$validacion['valido']) {
						echo '<script>
							swal({
								type: "error",
								title: "Error de Validación",
								text: "No se puede generar la factura electrónica. Corrija los siguientes errores: ' . implode(", ", $validacion['errores']) . '",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							});
						</script>';
						return; // ⛔ ABORTAR GUARDADO DE VENTA
					}
				}

				foreach ($listaProductos as $key => $value) {

					array_push($totalProductosComprados, $value["cantidad"]);

					// Verificar si es una variante
					if (isset($value["esVariante"]) && $value["esVariante"] == "1") {

						// Es una variante - restar stock de productos_variantes
						$tablaVariantes = "productos_variantes";
						$idVariante = $value["idVariante"];

						// Obtener datos actuales de la variante
						$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);

						// Actualizar stock de la variante
						$nuevoStockVariante = $traerVariante["stock"] - $value["cantidad"];
						ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - VARIANTE
						ControladorMovimientos::ctrRegistrarMovimiento(
							"variante",
							$value["id"],
							$idVariante,
							$value["descripcion"],
							"venta",
							-$value["cantidad"],
							$traerVariante["stock"],
							$nuevoStockVariante,
							"Venta #" . $codigoVenta,
							""
						);

						// Actualizar también el stock del producto base
						$tablaProductos = "productos";
						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");
						$nuevoStockBase = $traerProducto["stock"] - $value["cantidad"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $nuevoStockBase, $value["id"]);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - PRODUCTO BASE
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"venta",
							-$value["cantidad"],
							$traerProducto["stock"],
							$nuevoStockBase,
							"Venta #" . $codigoVenta . " (por variante)",
							""
						);

						// Actualizar ventas del producto base (estadística)
						$nuevasVentas = $value["cantidad"] + $traerProducto["ventas"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $nuevasVentas, $value["id"]);

					}
					else {
						// Es un producto normal - restar stock de productos
						$tablaProductos = "productos";
						$item = "id";
						$valor = $value["id"];
						$orden = "id";

						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

						$item1a = "ventas";
						$valor1a = $value["cantidad"] + $traerProducto["ventas"];

						$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

						$item1b = "stock";
						$valor1b = $value["stock"];

						$nuevoStock = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - PRODUCTO NORMAL
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"venta",
							-$value["cantidad"],
							$traerProducto["stock"],
							$valor1b,
							"Venta #" . $codigoVenta,
							""
						);

					}
				}

			} //CIERRE IF ESTADO VENTA

			$tablaClientes = "clientes";

			$item = "id";
			$valor = $_POST["seleccionarCliente"];

			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);

			$item1a = "compras";
			$valor1a = array_sum($totalProductosComprados) + $traerCliente["compras"];

			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valor);

			$item1b = "ultima_compra";

			date_default_timezone_set('America/Bogota');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$valor1b = $fecha . ' ' . $hora;

			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1b, $valor1b, $valor);


			/*=============================================
			 GUARDAR LA COMPRA
			 =============================================*/

			// Ya se generó $codigoVenta y $tabla al inicio (antes del bucle de productos)

			date_default_timezone_set('America/Bogota');
			$fechaHoraActual = date('Y-m-d H:i:s');

			// 🔹 VALIDACIÓN DE CONSECUTIVO (Corrección Duplicados)
			// Verificar si el código enviado ya existe
			$ventaExistente = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $codigoVenta);

			if ($ventaExistente) {
				// Si ya existe, obtener el verdadero siguiente consecutivo
				$nuevoCodigoReal = ModeloVentas::mdlObtenerSiguienteConsecutivo($tabla);

				// Log de advertencia (opcional)
				// file_put_contents("debug_consecutivos.txt", "COLISIÓN DETECTADA: El usuario envió $codigoVenta pero ya existe. Se cambió a $nuevoCodigoReal\n", FILE_APPEND);

				$codigoVenta = $nuevoCodigoReal;
			}

			$datos = array(
				"id_vendedor" => $_POST["idVendedor"],
				"id_cliente" => $_POST["seleccionarCliente"],
				//"codigo"=>$_POST["nuevaVenta"],
				"codigo" => $codigoVenta,
				"numero_factura" => null, // 🔹 INICIALMENTE NULL PARA NO COPIAR EL CÓDIGO INTERNO
				"productos" => $_POST["listaProductos"],
				"impuesto" => $_POST["nuevoPrecioImpuesto"],
				"neto" => $_POST["nuevoPrecioNeto"],
				"total" => $_POST["totalVenta"],
				"notas" => $_POST["notas"],
				"estado" => $_POST["estado"],
				"imagen" => $_POST["nuevaimagen"],
				"fecha" => $fechaHoraActual,
				"metodo_pago" => $_POST["listaMetodoPago"],
				"tipo_descuento" => isset($_POST["tipoDescuento"]) ? $_POST["tipoDescuento"] : "",
				"valor_descuento" => isset($_POST["valorDescuento"]) ? $_POST["valorDescuento"] : 0,
				"monto_descuento" => isset($_POST["montoDescuento"]) ? $_POST["montoDescuento"] : 0,
				"recibe" => isset($_POST["recibe"]) ? $_POST["recibe"] : null,
				"extra" => null,
				"retenciones" => isset($_POST["datosRetenciones"]) ? $_POST["datosRetenciones"] : null,
				// Campos Facturación Electrónica (Valores por defecto iniciales)
				"resolucion_id" => isset($_POST["resolucion_id"]) ? $_POST["resolucion_id"] : null,
				"fecha_vencimiento" => isset($_POST["fecha_vencimiento"]) ? $_POST["fecha_vencimiento"] : null,
				"orden_compra" => isset($_POST["orden_compra"]) ? $_POST["orden_compra"] : null,
				"forma_pago_dian" => isset($_POST["forma_pago_dian"]) ? $_POST["forma_pago_dian"] : null,
				"metodo_pago_dian_id" => isset($_POST["metodo_pago_dian_id"]) ? $_POST["metodo_pago_dian_id"] : null,
				"estado_dian" => isset($_POST["estado_dian"]) ? $_POST["estado_dian"] : 'pendiente',
				"cufe" => isset($_POST["cufe"]) ? $_POST["cufe"] : null,
				"qr_data" => isset($_POST["qr_data"]) ? $_POST["qr_data"] : null,
				"xml_dian" => isset($_POST["xml_dian"]) ? $_POST["xml_dian"] : null,
				"pdf_dian" => isset($_POST["pdf_dian"]) ? $_POST["pdf_dian"] : null,
				"mensaje_dian" => isset($_POST["mensaje_dian"]) ? $_POST["mensaje_dian"] : null,
				"fecha_envio_dian" => isset($_POST["fecha_envio_dian"]) ? $_POST["fecha_envio_dian"] : null
			);

			$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);

			if (is_numeric($respuesta)) {

				// 🔹 ACTUALIZAR EL CONSECUTIVO en la BD ahora que la venta se guardó correctamente
				ModeloVentas::mdlActualizarConsecutivo($tabla, $codigoVenta);

				// Verificar stock y generar notificaciones si es necesario
				ControladorNotificaciones::ctrVerificarStockProductos();

				// Verificar si la orden proviene de Agente IA (campo extra contiene n8n)
				if ($_POST["estado"] == "orden") {
					ControladorNotificaciones::ctrVerificarOrdenAgenteIA($codigoVenta);
				}

				/*=============================================
				 GENERAR FACTURA ELECTRÓNICA SI ESTÁ ACTIVADA
				 =============================================*/
				if ((isset($_POST["activarFacturaElectronica"]) && $_POST["activarFacturaElectronica"] == "1") || isset($_POST["guardarVentaFactus"])) {
					// Obtener el ID de la venta recién insertada
					$idVenta = $respuesta;
					$ultimaVenta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

					if ($ultimaVenta) {
						require_once __DIR__ . "/factus.controlador.php";

						// Generar factura electrónica
						// Generar factura electrónica (BORRADOR - SIN FIRMAR)
						$resultadoFactura = ControladorFactus::ctrGenerarFacturaElectronica($ultimaVenta['id'], false);

						// SI FALLA, HACEMOS ROLLBACK
						if ($resultadoFactura['error']) {

							// 1. REVERTIR STOCK (Devolver lo restado)
							foreach ($listaProductos as $key => $value) {
								$tablaProductos = "productos";

								// Obtener stock actual BD (ya disminuido)
								$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");

								// Nuevo Stock = Stock Actual + Cantidad Vendida
								$item1b = "stock";
								$valor1b = $traerProducto["stock"] + $value["cantidad"];
								ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $value["id"]);

								// Restar ventas estatisticas
								$item1a = "ventas";
								$valor1a = $traerProducto["ventas"] - $value["cantidad"];
								ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $value["id"]);

								// Revertir Variantes si existen
								if (isset($value["esVariante"]) && $value["esVariante"] == "1") {
									$tablaVariantes = "productos_variantes";
									$idVariante = $value["idVariante"];
									$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);
									$nuevoStockVariante = $traerVariante["stock"] + $value["cantidad"];
									ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);
								}
							}

							// 2. REVERTIR CLIENTE (Restar Compras)
							$tablaClientes = "clientes";
							$item = "id";
							$valor = $_POST["seleccionarCliente"];
							$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);

							$item1a = "compras";
							$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);
							ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valor);

							// 3. ELIMINAR LA VENTA
							ModeloVentas::mdlEliminarVenta($tabla, $ultimaVenta["id"]);

							// ERROR Y REDIRECCIÓN
							$mensajeError = "La factura electrónica falló y la venta NO se guardó. <br>Error: " . $resultadoFactura['mensaje'];

							if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
								if (ob_get_length()) ob_clean();
								echo json_encode([
									"status" => "error",
									"titulo" => "Error en Facturación",
									"mensaje" => $mensajeError
								]);
								return; // ABORTAR TODO
							}

							echo '<script>
								swal({
									type: "error",
									title: "Error en Facturación",
									html: "' . $mensajeError . '",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								}).then(() => {
										window.location = "' . (isset($_POST['rutaOrigen']) ? $_POST['rutaOrigen'] : 'crear-venta') . '";
								})
							</script>';
							return; // ABORTAR TODO
						}
					}
				}


				if ($_POST["estado"] == "orden") {
					if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo json_encode([
							"status" => "success",
							"titulo" => "¡Orden Guardada!",
							"mensaje" => "La orden ha sido guardada correctamente",
							"ruta" => "ordenes"
						]);
						return;
					}

					echo '<script>
						localStorage.removeItem("rango");
						swal({
							type: "success",
							title: "¡La orden ha sido guardada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
								window.location = "ordenes";
						});
					</script>';
				}
				else {

					// MENSAJE PERSONALIZADO SI ES FACTURA ELECTRÓNICA
					$tituloMensaje = "¡Venta guardada correctamente!";
					$textoMensaje = "El documento ha sido registrado exitosamente en el sistema.";
					
					if ((isset($_POST["activarFacturaElectronica"]) && $_POST["activarFacturaElectronica"] == "1") || isset($_POST["guardarVentaFactus"])) {

						$tituloMensaje = "¡Factura Electrónica guardada correctamente!";
						$textoMensaje = "El documento ha sido registrado exitosamente en el sistema.";

							if (isset($resultadoFactura) && $resultadoFactura['error']) {
								// Construir mensaje de error con lista de errores
								$mensajeError = $resultadoFactura['mensaje'];
								if (isset($resultadoFactura['errores']) && !empty($resultadoFactura['errores'])) {
									$mensajeError .= "<ul style='text-align:left; margin-top:10px;'>";
									foreach ($resultadoFactura['errores'] as $error) {
										$mensajeError .= "<li>" . htmlspecialchars($error) . "</li>";
									}
									$mensajeError .= "</ul>";
								}

								if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
									if (ob_get_length()) ob_clean();
									echo json_encode([
										"status" => "error",
										"titulo" => "Error de Validación",
										"mensaje" => $mensajeError
									]);
									return;
								}

								echo '<script>
									swal({
										type: "error",
										title: "Error de Validación",
										html: "' . addslashes($mensajeError) . '",
										showConfirmButton: true,
										confirmButtonText: "Cerrar"
									});
								</script>';
								return; // Detener ejecución para no mostrar mensaje de éxito
							}
					}

					// Determinar ruta de redirección
					$rutaRedireccion = "ventas";
					if ((isset($_POST["activarFacturaElectronica"]) && $_POST["activarFacturaElectronica"] == "1") || isset($_POST["guardarVentaFactus"])) {
						$rutaRedireccion = "facturas-electronicas";
					}

					if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo json_encode([
							"status" => "success",
							"titulo" => $tituloMensaje,
							"mensaje" => $textoMensaje,
							"ruta" => $rutaRedireccion
						]);
						return;
					}

					echo '<script>
						localStorage.removeItem("rango");
						swal({
							type: "success",
							title: "' . $tituloMensaje . '",
							text: "' . $textoMensaje . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar",
							}).then(() => {

								window.location = "' . $rutaRedireccion . '";
							})
						</script>';
				}
			}

		}

	}

	/*=============================================
	 CREAR VENTA API (PARA WEBHOOKS/INTEGRACIONES)
	 =============================================*/
	static public function ctrCrearVentaAPI($datos)
	{
		// Validar datos mínimos
		if (empty($datos["listaProductos"]) || empty($datos["seleccionarCliente"])) {
			return ["status" => "error", "message" => "Faltan datos obligatorios (productos o cliente)"];
		}

		$listaProductos = json_decode($datos["listaProductos"], true);
		$totalProductosComprados = array();

		// 1. Procesar Stock
		foreach ($listaProductos as $key => $value) {
			array_push($totalProductosComprados, $value["cantidad"]);

			if (isset($value["esVariante"]) && $value["esVariante"] == "1") {
				// Lógica para variantes (simplificada, reutilizar lógica de ctrCrearVenta si es posible refactorizar)
				$tablaVariantes = "productos_variantes";
				$idVariante = $value["idVariante"];
				$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);
				$nuevoStockVariante = $traerVariante["stock"] - $value["cantidad"];
				ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

				// Movimiento Stock
				ControladorMovimientos::ctrRegistrarMovimiento("variante", $value["id"], $idVariante, $value["descripcion"], "venta", -$value["cantidad"], $traerVariante["stock"], $nuevoStockVariante, "Venta API #" . $datos["codigo"], "Webhook BOLD");

				// Actualizar producto base
				$tablaProductos = "productos";
				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");
				$nuevoStockBase = $traerProducto["stock"] - $value["cantidad"];
				ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $nuevoStockBase, $value["id"]);
				ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $value["cantidad"] + $traerProducto["ventas"], $value["id"]);

			}
			else {
				// Producto normal
				$tablaProductos = "productos";
				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");

				$nuevoStock = $traerProducto["stock"] - $value["cantidad"];
				ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $nuevoStock, $value["id"]);
				ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $value["cantidad"] + $traerProducto["ventas"], $value["id"]);

				// Movimiento Stock
				ControladorMovimientos::ctrRegistrarMovimiento("producto", $value["id"], null, $traerProducto["descripcion"], "venta", -$value["cantidad"], $traerProducto["stock"], $nuevoStock, "Venta API #" . $datos["codigo"], "Webhook BOLD");
			}
		}

		// 2. Actualizar Cliente
		$tablaClientes = "clientes";
		$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, "id", $datos["seleccionarCliente"]);

		$comprasCliente = array_sum($totalProductosComprados) + $traerCliente["compras"];
		ModeloClientes::mdlActualizarCliente($tablaClientes, "compras", $comprasCliente, $datos["seleccionarCliente"]);

		date_default_timezone_set('America/Bogota');
		ModeloClientes::mdlActualizarCliente($tablaClientes, "ultima_compra", date('Y-m-d H:i:s'), $datos["seleccionarCliente"]);

		// 3. Guardar Venta
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);

		if ($respuesta == "ok") {
			ModeloVentas::mdlActualizarConsecutivo($tabla, $datos["codigo"]);
			return ["status" => "success", "codigo" => $datos["codigo"]];
		}
		else {
			return ["status" => "error", "message" => "Error al guardar en base de datos"];
		}
	}



	/*=============================================
	 EDITAR VENTAS
	 =============================================*/

	static public function ctrEditarVenta()
	{

		if (isset($_POST["editarVenta"])) {

			//No permitir ejecutar la venta si no hay productos añadidos
			if ($_POST["listaProductos"] == "") {

				if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
					if (ob_get_length()) ob_clean();
					echo json_encode([
						"status" => "error",
						"titulo" => "Error de Validación",
						"mensaje" => "Debe modificar los productos para guardar la venta"
					]);
					return;
				}

				echo '<script>
				swal({
					  type: "error",
					  title: "Debe modificar los productos para guardar la  venta",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(() => {
									window.location = "ordenes";
					  })
				</script>';
				return;
			}

			$tabla = "ventas";
			$item = "codigo";
			$valor = $_POST["editarVenta"];

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			/*=============================================
			 SI ERA ORDEN Y PASA A VENTA
			 =============================================*/
			if ($traerVenta["estado"] == "orden" && $_POST["estado"] == "venta") {

				$listaProductos = json_decode($_POST["listaProductos"], true);
				$totalProductosComprados = array();

				// DEBUG: Ver qué datos están llegando

				file_put_contents("debug_orden_a_venta.txt", "=== DATOS RECIBIDOS ===\n" . print_r($listaProductos, true));

				foreach ($listaProductos as $key => $value) {

					array_push($totalProductosComprados, $value["cantidad"]);

					// DEBUG: Ver qué campos tiene cada producto
					file_put_contents(
						"debug_orden_a_venta.txt",
						"\n=== PRODUCTO $key ===\n" .
						"ID: " . (isset($value["id"]) ? $value["id"] : "NO EXISTE") . "\n" .
						"esVariante: " . (isset($value["esVariante"]) ? $value["esVariante"] : "NO EXISTE") . "\n" .
						"idVariante: " . (isset($value["idVariante"]) ? $value["idVariante"] : "NO EXISTE") . "\n" .
						"Descripción: " . $value["descripcion"] . "\n",

						FILE_APPEND
					);

					// Verificar si es una variante
					if (isset($value["esVariante"]) && $value["esVariante"] == "1") {

						file_put_contents("debug_orden_a_venta.txt", ">>> ES VARIANTE - Procesando...\n", FILE_APPEND);

						// Es una variante - descontar stock de productos_variantes
						$tablaVariantes = "productos_variantes";
						$idVariante = $value["idVariante"];

						file_put_contents("debug_orden_a_venta.txt", "ID Variante: $idVariante\n", FILE_APPEND);



						// Obtener datos actuales de la variante

						$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);



						file_put_contents(
							"debug_orden_a_venta.txt",

							"Stock actual variante: " . $traerVariante["stock"] . "\n" .

							"Cantidad a descontar: " . $value["cantidad"] . "\n",

							FILE_APPEND

						);


						// Actualizar stock de la variante
						$nuevoStockVariante = $traerVariante["stock"] - $value["cantidad"];

						file_put_contents("debug_orden_a_venta.txt", "Nuevo stock: $nuevoStockVariante\n", FILE_APPEND);

						$resultadoActualizacion = ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

						file_put_contents("debug_orden_a_venta.txt", "Resultado actualización: $resultadoActualizacion\n\n", FILE_APPEND);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - VARIANTE (ORDEN → VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"variante",
							$value["id"],
							$idVariante,
							$value["descripcion"],
							"venta",
							-$value["cantidad"],
							$traerVariante["stock"],
							$nuevoStockVariante,
							"Venta #" . $_POST["editarVenta"] . " (orden convertida a venta)",
							""
						);

						// Actualizar también el stock del producto base
						$tablaProductos = "productos";
						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");
						$nuevoStockBase = $traerProducto["stock"] - $value["cantidad"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $nuevoStockBase, $value["id"]);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - PRODUCTO BASE (ORDEN → VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"venta",
							-$value["cantidad"],
							$traerProducto["stock"],
							$nuevoStockBase,
							"Venta #" . $_POST["editarVenta"] . " (por variante - orden convertida)",
							""
						);

						// Actualizar ventas del producto base (estadística)
						$nuevasVentas = $value["cantidad"] + $traerProducto["ventas"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $nuevasVentas, $value["id"]);


					}
					else {
						// Es un producto normal - descontar stock de productos
						$tablaProductos = "productos";
						$itemProd = "id";
						$valorProd = $value["id"];
						$orden = "id";

						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $itemProd, $valorProd, $orden);

						// Aumentar ventas
						$item1a = "ventas";
						$valor1a = $value["cantidad"] + $traerProducto["ventas"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valorProd);

						// Disminuir stock
						$item1b = "stock";
						$valor1b = $traerProducto["stock"] - $value["cantidad"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valorProd);

						// 🟢 REGISTRAR MOVIMIENTO DE STOCK - PRODUCTO NORMAL (ORDEN → VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"venta",
							-$value["cantidad"],
							$traerProducto["stock"],
							$valor1b,
							"Venta #" . $_POST["editarVenta"] . " (orden convertida a venta)",
							""
						);
					}
				}

				// Actualizar cliente
				$tablaClientes = "clientes";
				$itemCliente = "id";
				$valorCliente = $_POST["seleccionarCliente"];
				$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);

				$item1a = "compras";
				$valor1a = array_sum($totalProductosComprados) + $traerCliente["compras"];
				ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);

				$item1b = "ultima_compra";
				date_default_timezone_set('America/Bogota');
				$fecha = date('Y-m-d');
				$hora = date('H:i:s');
				$valor1b = $fecha . ' ' . $hora;
				ModeloClientes::mdlActualizarCliente($tablaClientes, $item1b, $valor1b, $valorCliente);
			}

			/*=============================================
			 SI YA ERA VENTA Y SE EDITA
			 =============================================*/
			if ($traerVenta["estado"] == "venta" && $_POST["estado"] == "venta") {

				$productos = json_decode($traerVenta["productos"], true);
				$totalProductosComprados = array();

				// Revertir cantidades viejas
				foreach ($productos as $key => $value) {
					array_push($totalProductosComprados, $value["cantidad"]);

					// Verificar si es una variante
					if (isset($value["esVariante"]) && $value["esVariante"] == "1") {

						// Es una variante - devolver stock a la variante
						$tablaVariantes = "productos_variantes";
						$idVariante = $value["idVariante"];

						$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);

						// Devolver stock a la variante
						$nuevoStockVariante = $traerVariante["stock"] + $value["cantidad"];

						ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

						// 🟢 REGISTRAR MOVIMIENTO - REVERTIR VARIANTE (EDICIÓN VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"variante",
							$value["id"],
							$idVariante,
							$value["descripcion"],
							"edicion_stock",
							$value["cantidad"],
							$traerVariante["stock"],
							$nuevoStockVariante,
							"Edición de Venta #" . $_POST["editarVenta"] . " (revertir productos viejos)",
							"Devolución de stock por edición de venta"
						);

						// Revertir ventas del producto base
						$tablaProductos = "productos";
						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");
						$nuevasVentas = $traerProducto["ventas"] - $value["cantidad"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $nuevasVentas, $value["id"]);

					}
					else {
						// Es un producto normal - devolver stock al producto
						$tablaProductos = "productos";
						$item = "id";
						$valor = $value["id"];
						$orden = "id";

						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

						$item1a = "ventas";
						$valor1a = $traerProducto["ventas"] - $value["cantidad"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

						$item1b = "stock";
						$valor1b = $value["cantidad"] + $traerProducto["stock"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

						// 🟢 REGISTRAR MOVIMIENTO - REVERTIR PRODUCTO NORMAL (EDICIÓN VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"edicion_stock",
							$value["cantidad"],
							$traerProducto["stock"],
							$valor1b,
							"Edición de Venta #" . $_POST["editarVenta"] . " (revertir productos viejos)",
							"Devolución de stock por edición de venta"
						);

					}

				}

				// Revertir compras cliente
				$tablaClientes = "clientes";
				$itemCliente = "id";
				$valorCliente = $_POST["seleccionarCliente"];

				$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);

				$item1a = "compras";
				$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);

				ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);

				// Aplicar nuevas cantidades
				$listaProductos_2 = json_decode($_POST["listaProductos"], true);
				$totalProductosComprados_2 = array();

				foreach ($listaProductos_2 as $key => $value) {
					array_push($totalProductosComprados_2, $value["cantidad"]);

					// Verificar si es una variante
					if (isset($value["esVariante"]) && $value["esVariante"] == "1") {

						// Es una variante - descontar stock de la variante
						$tablaVariantes = "productos_variantes";
						$idVariante = $value["idVariante"];

						$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);

						// Descontar stock de la variante
						$nuevoStockVariante = $traerVariante["stock"] - $value["cantidad"];

						ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

						// 🟢 REGISTRAR MOVIMIENTO - APLICAR VARIANTE (EDICIÓN VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"variante",
							$value["id"],
							$idVariante,
							$value["descripcion"],
							"edicion_stock",
							-$value["cantidad"],
							$traerVariante["stock"],
							$nuevoStockVariante,
							"Edición de Venta #" . $_POST["editarVenta"] . " (aplicar productos nuevos)",
							"Descuento de stock por edición de venta"
						);

						// Aumentar ventas del producto base
						$tablaProductos = "productos";
						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");
						$nuevasVentas = $value["cantidad"] + $traerProducto["ventas"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $nuevasVentas, $value["id"]);

					}
					else {
						// Es un producto normal - descontar stock del producto
						$tablaProductos_2 = "productos";
						$item_2 = "id";
						$valor_2 = $value["id"];
						$orden = "id";

						$traerProducto_2 = ModeloProductos::mdlMostrarProductos($tablaProductos_2, $item_2, $valor_2, $orden);

						$item1a_2 = "ventas";
						$valor1a_2 = $value["cantidad"] + $traerProducto_2["ventas"];

						ModeloProductos::mdlActualizarProducto($tablaProductos_2, $item1a_2, $valor1a_2, $valor_2);

						$item1b_2 = "stock";
						$valor1b_2 = $traerProducto_2["stock"] - $value["cantidad"];

						ModeloProductos::mdlActualizarProducto($tablaProductos_2, $item1b_2, $valor1b_2, $valor_2);

						// 🟢 REGISTRAR MOVIMIENTO - APLICAR PRODUCTO NORMAL (EDICIÓN VENTA)
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto_2["descripcion"],
							"edicion_stock",
							-$value["cantidad"],
							$traerProducto_2["stock"],
							$valor1b_2,
							"Edición de Venta #" . $_POST["editarVenta"] . " (aplicar productos nuevos)",
							"Descuento de stock por edición de venta"
						);
					}
				}

				// Actualizar cliente
				$tablaClientes_2 = "clientes";
				$item_2 = "id";
				$valor_2 = $_POST["seleccionarCliente"];
				$traerCliente_2 = ModeloClientes::mdlMostrarClientes($tablaClientes_2, $item_2, $valor_2);

				$item1a_2 = "compras";
				$valor1a_2 = array_sum($totalProductosComprados_2) + $traerCliente_2["compras"];
				ModeloClientes::mdlActualizarCliente($tablaClientes_2, $item1a_2, $valor1a_2, $valor_2);

				$item1b_2 = "ultima_compra";
				date_default_timezone_set('America/Bogota');
				$fecha = date('Y-m-d');
				$hora = date('H:i:s');
				$valor1b_2 = $fecha . ' ' . $hora;
				ModeloClientes::mdlActualizarCliente($tablaClientes_2, $item1b_2, $valor1b_2, $valor_2);
			}

			/*=============================================
			 GUARDAR CAMBIOS DE LA COMPRA
			 =============================================*/
			date_default_timezone_set('America/Bogota');
			$fechaHoraActual = date('Y-m-d H:i:s');

			// Si era orden y pasa a venta, agregar origen a las notas
			$notasFinales = $_POST["notas"];
			if ($traerVenta["estado"] == "orden" && $_POST["estado"] == "venta") {

				// Verificar si las notas originales contienen "Creado por Agente IA"
				if (!empty($traerVenta["notas"]) && strpos($traerVenta["notas"], 'Creado por Agente IA') !== false) {
					// Preservar el texto original "Creado por Agente IA"
					$origenTexto = "Creado por Agente IA";
				}
				else {
					// Determinar si es orden de Agente IA o manual
					$origenTexto = "Desde orden";
					if (!empty($traerVenta["extra"]) && strpos($traerVenta["extra"], 'n8n') !== false) {
						$origenTexto = "Desde Agente IA";
					}
				}

				if (!empty($notasFinales)) {
					$notasFinales = $notasFinales . " | " . $origenTexto;

				}
				else {
					$notasFinales = $origenTexto;
				}
			}

			// Agregar campo "recibe" a las notas si tiene contenido
			if (!empty($_POST["recibe"])) {
				$textoRecibe = "Recibe: " . $_POST["recibe"];
				if (!empty($notasFinales)) {
					$notasFinales = $notasFinales . " - " . $textoRecibe;
				}
				else {
					$notasFinales = $textoRecibe;
				}
			}

			$datos = array(

				"id_vendedor" => $_POST["idVendedor"],
				"id_cliente" => $_POST["seleccionarCliente"],
				"codigo" => $_POST["editarVenta"],
				"numero_factura" => isset($traerVenta["numero_factura"]) ? $traerVenta["numero_factura"] : null, // 🔹 MANTENER NÚMERO EXISTENTE
				"productos" => $_POST["listaProductos"],
				"impuesto" => $_POST["nuevoPrecioImpuesto"],
				"neto" => $_POST["nuevoPrecioNeto"],
				"total" => $_POST["totalVenta"],
				"notas" => $notasFinales,
				"imagen" => $_POST["nuevaimagen"],
				"estado" => $_POST["estado"],
				"fecha" => $fechaHoraActual,
				"metodo_pago" => $_POST["listaMetodoPago"],
				"tipo_descuento" => isset($_POST["tipoDescuento"]) ? $_POST["tipoDescuento"] : "",
				"valor_descuento" => isset($_POST["valorDescuento"]) ? $_POST["valorDescuento"] : 0,
				"monto_descuento" => isset($_POST["montoDescuento"]) ? $_POST["montoDescuento"] : 0,
				"recibe" => isset($_POST["recibe"]) ? $_POST["recibe"] : null,
				"extra" => $traerVenta["extra"],
				"retenciones" => isset($_POST["datosRetenciones"]) ? $_POST["datosRetenciones"] : null,
				// Campos Facturación Electrónica (Valores por defecto o mantenidos)
				"resolucion_id" => isset($_POST["resolucion_id"]) ? $_POST["resolucion_id"] : $traerVenta["resolucion_id"],
				"fecha_vencimiento" => isset($_POST["fecha_vencimiento"]) ? $_POST["fecha_vencimiento"] : $traerVenta["fecha_vencimiento"],
				"orden_compra" => isset($_POST["orden_compra"]) ? $_POST["orden_compra"] : $traerVenta["orden_compra"],
				"forma_pago_dian" => isset($_POST["forma_pago_dian"]) ? $_POST["forma_pago_dian"] : $traerVenta["forma_pago_dian"],
				"metodo_pago_dian_id" => isset($_POST["metodo_pago_dian_id"]) ? $_POST["metodo_pago_dian_id"] : $traerVenta["metodo_pago_dian_id"],
				"estado_dian" => isset($_POST["estado_dian"]) ? $_POST["estado_dian"] : $traerVenta["estado_dian"],
				"cufe" => isset($_POST["cufe"]) ? $_POST["cufe"] : $traerVenta["cufe"],
				"qr_data" => isset($_POST["qr_data"]) ? $_POST["qr_data"] : $traerVenta["qr_data"],
				"xml_dian" => isset($_POST["xml_dian"]) ? $_POST["xml_dian"] : $traerVenta["xml_dian"],
				"pdf_dian" => isset($_POST["pdf_dian"]) ? $_POST["pdf_dian"] : $traerVenta["pdf_dian"],
				"mensaje_dian" => isset($_POST["mensaje_dian"]) ? $_POST["mensaje_dian"] : $traerVenta["mensaje_dian"],
				"fecha_envio_dian" => isset($_POST["fecha_envio_dian"]) ? $_POST["fecha_envio_dian"] : $traerVenta["fecha_envio_dian"]
			);

			$respuesta = ModeloVentas::mdlEditarVenta($tabla, $datos);

			/*if ($respuesta == "ok") {
			 echo '<script>
			 localStorage.removeItem("rango");
			 swal({
			 type: "success",
			 title: "!La venta ha sigo CREADA correctamente!",
			 showConfirmButton: true,
			 confirmButtonText: "Cerrar",
			 }).then((result)=>{
			 if(result.value){
			 window.location = "ordenes";
			 }
			 })
			 </script>';
			 }
			 */

			if ($respuesta == "ok") {

				// Verificar stock y generar notificaciones si es necesario
				ControladorNotificaciones::ctrVerificarStockProductos();

				//**************************************************
				// ENVIAR WEBHOOK A N8N
				//************************************************ */
				// Obtener datos completos del cliente
				$tablaClientes = "clientes";
				$itemCliente = "id";
				$valorCliente = $_POST["seleccionarCliente"];
				$clienteCompleto = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);
				// Preparar datos para el webhook
				$datosWebhook = array(
					"origen" => "ventas",
					"id_vendedor" => $_POST["idVendedor"],
					// Datos del cliente
					"cliente" => array(
						"id" => $clienteCompleto["id"],
						"nombre" => $clienteCompleto["nombre"],
						"documento" => $clienteCompleto["documento"],
						"email" => $clienteCompleto["email"],
						"telefono" => $clienteCompleto["telefono"],
						"departamento" => $clienteCompleto["departamento"],
						"ciudad" => $clienteCompleto["ciudad"],
						"direccion" => $clienteCompleto["direccion"],
						"estatus" => $clienteCompleto["estatus"],
						"fecha_nacimiento" => $clienteCompleto["fecha_nacimiento"],
						"notas" => $clienteCompleto["notas"]
					),
					// Datos de la venta
					"codigo" => $_POST["editarVenta"],
					"productos" => json_decode($_POST["listaProductos"], true),
					"impuesto" => $_POST["nuevoPrecioImpuesto"],
					"neto" => $_POST["nuevoPrecioNeto"],
					"total" => $_POST["totalVenta"],
					"metodo_pago" => $_POST["listaMetodoPago"],
					"notas_venta" => $_POST["notas"],
					"imagen" => $_POST["nuevaimagen"],
					"estado" => $_POST["estado"],
					"fecha" => $fechaHoraActual
				);
				// Enviar webhook con cURL
				$ch = curl_init('https://dd99f8f867ae.ngrok-free.app/webhook/mipos');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosWebhook));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				$resultado = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				// Log del resultado (opcional)
				if ($httpCode != 200) {
					error_log("Error al enviar webhook: HTTP " . $httpCode . " - " . $resultado);
				}
				else {
					error_log("Webhook enviado exitosamente para venta: " . $_POST["editarVenta"]);
				}



					if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo json_encode([
							"status" => "success",
							"titulo" => "¡Venta guardada correctamente!",
							"mensaje" => "El documento ha sido registrado exitosamente en el sistema.",
							"ruta" => "ordenes"
						]);
						return;
					}

					echo '<script>
					localStorage.removeItem("rango");
					swal({
						type: "success",
						title: "¡Venta guardada correctamente!",
						text: "El documento ha sido registrado exitosamente en el sistema.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						}).then(() => {
								window.location = "ordenes";
						})
				</script>';
			}


		}
	}


	/*=============================================
	 ELIMINAR VENTA
	 =============================================*/

	static public function ctrEliminarVenta()
	{
		$idVenta = isset($_GET["idVenta"]) ? $_GET["idVenta"] : (isset($_POST["idVentaEliminar"]) ? $_POST["idVentaEliminar"] : null);

		if ($idVenta) {

			$tabla = "ventas";

			$item = "id";
			$valor = $idVenta;

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			if (!$traerVenta) {
				$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $valor);
			}

			if ($traerVenta) {
				$valor = $traerVenta["id"]; // Asegurar que usamos el ID físico para el DELETE
			}



			/*=============================================
			 ACTUALIZAR FECHA ULTIMA COMPRA
			 =============================================*/

			$tablaClientes = "clientes";

			$itemVentas = null;
			$valorVentas = null;

			$traerVentas = ModeloVentas::mdlMostrarVentas($tabla, $itemVentas, $valorVentas);

			$guardarFechas = array();

			foreach ($traerVentas as $key => $value) {

				if ($value["id_cliente"] == $traerVenta["id_cliente"]) {

					array_push($guardarFechas, $value["fecha"]);
				}

			}

			if (count($guardarFechas) > 1) {

				if ($traerVenta["fecha"] > $guardarFechas[count($guardarFechas) - 2]) {

					$item = "ultima_compra";
					$valor = $guardarFechas[count($guardarFechas) - 2];
					$valorIdCliente = $traerVenta["id_cliente"];

					$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);
				}
				else {
					$item = "ultima_compra";
					$valor = $guardarFechas[count($guardarFechas) - 1];
					$valorIdCliente = $traerVenta["id_cliente"];

					$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);
				}

			}
			else {

				$item = "ultima_compra";
				$valor = "0000-00-00 00:00:00";
				$valorIdCliente = $traerVenta["id_cliente"];

				$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);
			}


			/*=============================================
			 FORMATEAR LA TABLA DE PRODUCTOS Y CLIENTES
			 =============================================*/

			$productos = json_decode($traerVenta["productos"], true);

			$totalProductosComprados = array();

			foreach ($productos as $key => $value) {

				array_push($totalProductosComprados, $value["cantidad"]);

				// Verificar si es una variante
				if (isset($traerVenta["estado"]) && $traerVenta["estado"] == "orden") {
					continue;
				}

				if (isset($value["esVariante"]) && $value["esVariante"] == "1") {

					// Es una variante - devolver stock a la variante Y al producto base
					$tablaVariantes = "productos_variantes";
					$idVariante = $value["idVariante"];

					// Obtener datos actuales de la variante
					$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);

					if ($traerVariante) {
						// Devolver stock a la variante
						$nuevoStockVariante = $traerVariante["stock"] + $value["cantidad"];
						ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

						// 🟢 REGISTRAR MOVIMIENTO - DEVOLUCIÓN VARIANTE
						if (isset($value["descripcion"]) && !empty($value["descripcion"])) {
							ControladorMovimientos::ctrRegistrarMovimiento(
								"variante",
								$value["id"],
								$idVariante,
								$value["descripcion"],
								"eliminacion_venta",
								$value["cantidad"],
								$traerVariante["stock"],
								$nuevoStockVariante,
								"Eliminación Venta #" . $traerVenta["codigo"],
								""
							);
						}
					}

					// Devolver stock al producto base
					$tablaProductos = "productos";
					$item = "id";
					$valor = $value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					// Devolver stock al producto base
					$item1b = "stock";
					$valor1b = $value["cantidad"] + $traerProducto["stock"];
					$nuevoStock = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

					// 🟢 REGISTRAR MOVIMIENTO - DEVOLUCIÓN PRODUCTO BASE
					if ($traerProducto && isset($traerProducto["descripcion"])) {
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"eliminacion_venta",
							$value["cantidad"],
							$traerProducto["stock"],
							$valor1b,
							"Eliminación Venta #" . $traerVenta["codigo"] . " (por variante)",
							""
						);
					}

					// Restar las ventas del producto base
					$item1a = "ventas";
					$valor1a = $traerProducto["ventas"] - $value["cantidad"];
					$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

				}
				else {

					// Es un producto normal - devolver stock normal
					$tablaProductos = "productos";

					$item = "id";
					$valor = $value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					$item1a = "ventas";
					$valor1a = $traerProducto["ventas"] - $value["cantidad"];

					$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);

					$item1b = "stock";
					$valor1b = $value["cantidad"] + $traerProducto["stock"];

					$nuevoStock = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

					// 🟢 REGISTRAR MOVIMIENTO - DEVOLUCIÓN PRODUCTO NORMAL
					if ($traerProducto && isset($traerProducto["descripcion"])) {
						ControladorMovimientos::ctrRegistrarMovimiento(
							"producto",
							$value["id"],
							null,
							$traerProducto["descripcion"],
							"eliminacion_venta",
							$value["cantidad"],
							$traerProducto["stock"],
							$valor1b,
							"Eliminación Venta #" . $traerVenta["codigo"],
							""
						);
					}

				}
			}

			$tablaClientes = "clientes";

			$itemCliente = "id";
			$valorCliente = $traerVenta["id_cliente"];

			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);

			$item1a = "compras";
			$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);

			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);


			/*=============================================
			 ELIMINAR VENTA
			 =============================================*/

			$idVentaEliminarReal = $traerVenta["id"];
			$respuesta = ModeloVentas::mdlEliminarVenta($tabla, $idVentaEliminarReal);

			if ($respuesta == "ok") {


				if (isset($_GET["estado"]) && $_GET["estado"] == "orden") {
					if (isset($_POST["idVentaEliminar"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo "ok";
						return;
					}

					echo '<script>
						localStorage.removeItem("rango");
						swal({
							type: "success",
							title: "¡La orden ha sido eliminada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
								window.location = "ordenes";
						});
					</script>';
				}
				else {

					$rutaRedireccion = (isset($_GET["ruta"]) && !empty($_GET["ruta"])) ? $_GET["ruta"] : "ventas";

					if (isset($_POST["idVentaEliminar"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo "ok";
						return;
					}

					echo '<script>
						localStorage.removeItem("rango");
						swal({
							type: "success",
							title: "¡La venta ha sido borrada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
							}).then(() => {
									// Limpiar idVenta para evitar ciclo infinito
									if (window.history.replaceState) {
										var url = new URL(window.location.href);
										url.searchParams.delete("idVenta");
										window.history.replaceState(null, "", url.toString());
									}
									// Recargar página para actualizar tabla manteniendo filtros
									window.location.reload();
							})
			     	</script>';
				}

			} else {
				if (isset($_POST["idVentaEliminar"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
					if (ob_get_length()) ob_clean();
					echo $respuesta; // Reportar error real: no_affected_rows o error_db
					return;
				}
			}

		}

	}


	/*=============================================
	 RANGO FECHAS
	 =============================================*/

	static public function ctrRangoFechasVentas($fechaInicial, $fechaFinal)
	{
		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal);

		return $respuesta;
	}


	/*=============================================
	 DESCARGAR EXCEL
	 =============================================*/

	public function ctrDescargarReporte()
	{

		if (isset($_GET["reporte"])) {

			$tabla = "ventas";

			if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])) {

				$ventas = ModeloVentas::mdlRangoFechasVentas($tabla, $_GET["fechaInicial"], $_GET["fechaFinal"]);

			}
			else {

				$item = null;
				$valor = null;

				$ventas = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);
			}




			/*=============================================
			 CREAMOS EL ARCHIVO DE EXCEL
			 =============================================*/
			$Name = $_GET["reporte"] . '.xls';

			header('Expires: 0');
			header('Cache-control: private');
			header("Content-type: application/vnd.ms-excel"); // Archivo de Excel
			header("Cache-Control: cache, must-revalidate");
			header('Content-Description: File Transfer');
			header('Last-Modified: ' . date('D, d M Y H:i:s'));
			header("Pragma: public");
			header('Content-Disposition:; filename="' . $Name . '"');
			header("Content-Transfer-Encoding: binary");

			echo utf8_decode("<table border='0'> 

				<tr> 
				<td style='font-weight:bold; border:1px solid #eee;'>CÓDIGO</td> 
				<td style='font-weight:bold; border:1px solid #eee;'>CLIENTE</td>
				<td style='font-weight:bold; border:1px solid #eee;'>VENDEDOR</td>
				<td style='font-weight:bold; border:1px solid #eee;'>CANTIDAD</td>
				<td style='font-weight:bold; border:1px solid #eee;'>PRODUCTOS</td>
				<td style='font-weight:bold; border:1px solid #eee;'>IMPUESTO</td>
				<td style='font-weight:bold; border:1px solid #eee;'>NETO</td>		
				<td style='font-weight:bold; border:1px solid #eee;'>TOTAL</td>		
				<td style='font-weight:bold; border:1px solid #eee;'>METODO DE PAGO</td	
				<td style='font-weight:bold; border:1px solid #eee;'>FECHA</td>		
				</tr>");

			foreach ($ventas as $row => $item) {

				// Filtrar solo ventas con estado = 'venta'
				if (!isset($item["estado"]) || $item["estado"] != "venta") {
					continue;
				}

				// Filtrar por usuario si existe el parámetro
				if (isset($_GET["usuario"]) && $_GET["usuario"] != "" && (string)$item["id_vendedor"] != (string)$_GET["usuario"]) {
					continue;
				}

				// Filtrar por cliente si existe el parámetro
				if (isset($_GET["cliente"]) && $_GET["cliente"] != "" && $_GET["cliente"] != "todos" && (string)$item["id_cliente"] != (string)$_GET["cliente"]) {
					continue;
				}

				$cliente = ControladorClientes::ctrMostrarClientes("id", $item["id_cliente"]);
				$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

				echo utf8_decode("<tr>
				 			<td style='border:1px solid #eee;'>" . $item["codigo"] . "</td> 
				 			<td style='border:1px solid #eee;'>" . $cliente["nombre"] . "</td>
				 			<td style='border:1px solid #eee;'>" . $vendedor["nombre"] . "</td>
				 			<td style='border:1px solid #eee;'>");

				$productos = json_decode($item["productos"], true);

				foreach ($productos as $key => $valueProductos) {

					echo utf8_decode($valueProductos["cantidad"] . "<br>");
				}

				echo utf8_decode("</td><td style='border:1px solid #eee;'>");

				foreach ($productos as $key => $valueProductos) {

					echo utf8_decode($valueProductos["descripcion"] . "<br>");
				}

				echo utf8_decode("</td>
					<td style='border:1px solid #eee;'>$ " . number_format($item["impuesto"], 2) . "</td>
					<td style='border:1px solid #eee;'>$ " . number_format($item["neto"], 2) . "</td>	
					<td style='border:1px solid #eee;'>$ " . number_format($item["total"], 2) . "</td>
					<td style='border:1px solid #eee;'>" . $item["metodo_pago"] . "</td>
					<td style='border:1px solid #eee;'>" . substr($item["fecha"], 0, 10) . "</td>		
		 			</tr>");

			}

			echo "</table>";

		}

	}


	/*=============================================
	 SUMA TOTAL VENTAS
	 =============================================*/

	static public function ctrSumaTotalVentas()
	{

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlSumaTotalVentas($tabla);

		return $respuesta;

	}


	/*=============================================
	 DESCARGAR XML
	 =============================================*/
	static public function ctrDescargarXML()
	{
		//http://php.net/manual/es/book.xmlwriter.php

		if (isset($_GET["xml"])) {

			$tabla = "ventas";
			$item = "codigo";
			$valor = $_GET["xml"];

			$ventas = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			// PRODUCTOS
			$listaProductos = json_decode($ventas["productos"], true);

			// CLIENTE
			$tablaClientes = "clientes";
			$item = "id";
			$valor = $ventas["id_cliente"];
			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);

			// VENDEDOR
			$tablaVendedor = "usuarios";
			$item = "id";
			$valor = $ventas["id_vendedor"];
			$traerVendedor = ModeloUsuarios::mdlMostrarUsuarios($tablaVendedor, $item, $valor);


			$objetoXML = new XMLWriter();

			$objetoXML->openURI($_GET["xml"] . ".xml"); //Creación del archivo XML

			$objetoXML->setIndent(true); //recibe un valor booleano para establecer si los distintos niveles de nodos XML deben quedar indentados o no.

			$objetoXML->setIndentString("\t"); // carácter \t, que corresponde a una tabulación

			$objetoXML->startDocument('1.0', 'utf-8'); // Inicio del documento


			/*$objetoXML->startElement("etiquetaPrincipal");// Inicio del nodo raíz
			 $objetoXML->writeAttribute("atributoEtiquetaPPal", "valor atributo etiqueta PPal"); // Atributo etiqueta principal
			 $objetoXML->startElement("etiquetaInterna");// Inicio del nodo hijo
			 $objetoXML->writeAttribute("atributoEtiquetaInterna", "valor atributo etiqueta Interna"); // Atributo etiqueta interna
			 $objetoXML->text("Texto interno");
			 $objetoXML->endElement(); // Final del nodo hijo
			 $objetoXML->endElement(); // Final del nodo raíz */


			$objetoXML->writeRaw('<fe:Invoice xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:clm54217="urn:un:unece:uncefact:codelist:specification:54217:2001" xmlns:clm66411="urn:un:unece:uncefact:codelist:specification:66411:2001" xmlns:clmIANAMIMEMediaType="urn:un:unece:uncefact:codelist:specification:IANAMIMEMediaType:2003" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dian.gov.co/contratos/facturaelectronica/v1 ../xsd/DIAN_UBL.xsd urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2 ../../ubl2/common/UnqualifiedDataTypeSchemaModule-2.0.xsd urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2 ../../ubl2/common/UBL-QualifiedDatatypes-2.0.xsd">');

			$objetoXML->writeRaw('<ext:UBLExtensions>');


			foreach ($listaProductos as $key => $value) {

				$objetoXML->text($value["descripcion"] . ", ");
			}

			$objetoXML->text($ventas["codigo"] . "--");

			$objetoXML->text($traerCliente["nombre"] . " ");

			$objetoXML->text(number_format($ventas["impuesto"], 2));


			$objetoXML->writeRaw('</ext:UBLExtensions>');

			$objetoXML->writeRaw('</fe:Invoice>');

			$objetoXML->endDocument(); // Final del documento

			return true;

		}

	}

	//Diferenciar entre venta y orden
	static public function ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, $estado)
	{

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlRangoFechasVentasPorEstado($tabla, $fechaInicial, $fechaFinal, $estado);

		return $respuesta;
	}
	
	//Rango fechas específico para Facturas Electrónicas (Optimizado)
	static public function ctrRangoFechasFacturasElectronicas($fechaInicial, $fechaFinal, $estado)
	{

		$tabla = "ventas";

		$respuesta = ModeloVentas::mdlRangoFechasFacturasElectronicas($tabla, $fechaInicial, $fechaFinal, $estado);

		return $respuesta;
	}


	//Para los reportes
	public static function ctrMostrarVentasAsociativo($tabla, $item, $valor)
	{
		return ModeloVentas::mdlMostrarVentasAsociativo($tabla, $item, $valor);
	}

	// Mostrar última venta
	static public function ctrMostrarUltimaVenta()
	{
		$tabla = "ventas";
		return ModeloVentas::mdlMostrarUltimaVenta($tabla);
	}

	// Mostrar última venta por estado
	static public function ctrMostrarUltimaVentaPorEstado($estado)
	{
		$tabla = "ventas";
		return ModeloVentas::mdlMostrarUltimaVentaPorEstado($tabla, $estado);
	}

	/*=============================================
	 MOSTRAR ULTIMA FACTURA ELECTRÓNICA
	 =============================================*/
	static public function ctrMostrarUltimaFacturaElectronica()
	{
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlMostrarUltimaFacturaElectronica($tabla);
		return $respuesta;
	}


	//Guardar notas
	static public function ctrActualizarNotaVenta($datos)
	{
		return ModeloVentas::mdlActualizarNotaVenta("ventas", $datos);
	}


	//Guardar observaciones
	static public function ctrActualizarObservacionVenta($datos)
	{
		return ModeloVentas::mdlActualizarObservacionVenta("ventas", $datos);
	}


	/*=============================================
	 EDITAR IMAGEN DE VENTA
	 =============================================*/
	static public function ctrEditarImagenVenta($datos)
	{

		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlEditarImagenVenta($tabla, $datos);
		return $respuesta;

	}

	/*=============================================
	 ACTUALIZAR SEGUIMIENTO
	 =============================================*/
	static public function ctrActualizarSeguimiento($datos)
	{
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlActualizarSeguimiento($tabla, $datos);
		return $respuesta;
	}

	/*=============================================
	 CREAR VENTA FACTURACIÓN ELECTRÓNICA
	 =============================================*/
	static public function ctrCrearVentaFactus()
	{
		if (isset($_POST["guardarVentaFactus"])) {

			// 1. Validar productos
			if ($_POST["listaProductos"] == "") {
				if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
					if (ob_get_length()) ob_clean();
					echo json_encode([
						"status" => "error",
						"titulo" => "Error de Validación",
						"mensaje" => "La venta no se puede ejecutar si no hay productos"
					]);
					return;
				}

				echo '<script>
				swal({
					  type: "error",
					  title: "La venta no se puede ejecutar si no hay productos",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(() => {
								window.location = "ventas";
							})
				</script>';
				return;
			}

			/*=============================================
			 2. GESTIÓN DE STOCK Y CLIENTES
			 =============================================*/
			$listaProductos = json_decode($_POST["listaProductos"], true);
			$codigoVenta = $_POST["nuevaVenta"];
			$tabla = "ventas";
			$totalProductosComprados = array();

			if ($_POST["estado"] == "venta") {
				foreach ($listaProductos as $key => $value) {
					array_push($totalProductosComprados, $value["cantidad"]);

					// Verificar si es variante

					// Verificar si es variante
					if (isset($value["esVariante"]) && $value["esVariante"] == "1") {
						$tablaVariantes = "productos_variantes";
						$idVariante = $value["idVariante"];
						$traerVariante = ModeloProductos::mdlObtenerVariantePorId($idVariante);
						$nuevoStockVariante = $traerVariante["stock"] - $value["cantidad"];
						ModeloProductos::mdlActualizarStockVariante($tablaVariantes, $nuevoStockVariante, $idVariante);

						ControladorMovimientos::ctrRegistrarMovimiento("variante", $value["id"], $idVariante, $value["descripcion"], "venta", -$value["cantidad"], $traerVariante["stock"], $nuevoStockVariante, "Venta FE #" . $codigoVenta, "");

						$tablaProductos = "productos";
						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $value["id"], "id");
						$nuevoStockBase = $traerProducto["stock"] - $value["cantidad"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "stock", $nuevoStockBase, $value["id"]);

						ControladorMovimientos::ctrRegistrarMovimiento("producto", $value["id"], null, $traerProducto["descripcion"], "venta", -$value["cantidad"], $traerProducto["stock"], $nuevoStockBase, "Venta FE #" . $codigoVenta, "");

						$nuevasVentas = $value["cantidad"] + $traerProducto["ventas"];
						ModeloProductos::mdlActualizarProducto($tablaProductos, "ventas", $nuevasVentas, $value["id"]);

					}
					else {
						$tablaProductos = "productos";
						$item = "id";
						$valor = $value["id"];
						$orden = "id";
						$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);
						$item1a = "ventas";
						$valor1a = $value["cantidad"] + $traerProducto["ventas"];
						$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor);
						$item1b = "stock";
						$valor1b = $value["stock"];
						$nuevoStock = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor);

						ControladorMovimientos::ctrRegistrarMovimiento("producto", $value["id"], null, $traerProducto["descripcion"], "venta", -$value["cantidad"], $traerProducto["stock"], $valor1b, "Venta FE #" . $codigoVenta, "");
					}
				}
			}

			$tablaClientes = "clientes";
			$item = "id";
			$valor = $_POST["seleccionarCliente"];
			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);
			$item1a = "compras";
			$valor1a = array_sum($totalProductosComprados) + $traerCliente["compras"];
			ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valor);
			$item1b = "ultima_compra";
			date_default_timezone_set('America/Bogota');
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$valor1b = $fecha . ' ' . $hora;
			ModeloClientes::mdlActualizarCliente($tablaClientes, $item1b, $valor1b, $valor);

			/*=============================================
			 3. GUARDAR VENTA (O CONVERTIR SI ES ORDEN)
			 =============================================*/
			date_default_timezone_set('America/Bogota');
			$fechaHoraActual = date('Y-m-d H:i:s');

			$datos = array(
				"id_vendedor" => $_POST["idVendedor"],
				"id_cliente" => $_POST["seleccionarCliente"],
				"codigo" => $_POST["nuevaVenta"], // USAR EL CÓDIGO DE FACTUS COMO CÓDIGO INTERNO
				"productos" => $_POST["listaProductos"],
				"impuesto" => $_POST["nuevoPrecioImpuesto"],
				"neto" => $_POST["nuevoPrecioNeto"],
				"total" => $_POST["totalVenta"],
				"notas" => $_POST["notas"],
				"estado" => $_POST["estado"],
				"imagen" => "",
				"fecha" => $fechaHoraActual,
				"metodo_pago" => $_POST["listaMetodoPago"],
				"tipo_descuento" => isset($_POST["tipoDescuento"]) ? $_POST["tipoDescuento"] : "",
				"valor_descuento" => isset($_POST["valorDescuento"]) ? $_POST["valorDescuento"] : 0,
				"monto_descuento" => isset($_POST["montoDescuento"]) ? $_POST["montoDescuento"] : 0,
				"recibe" => isset($_POST["recibe"]) ? $_POST["recibe"] : null,
				"extra" => null,
				"retenciones" => isset($_POST["datosRetenciones"]) ? $_POST["datosRetenciones"] : null,
				"resolucion_id" => isset($_POST["resolucion_id"]) ? $_POST["resolucion_id"] : null,
				"forma_pago_dian" => isset($_POST["forma_pago_dian"]) ? $_POST["forma_pago_dian"] : "1",
				"fecha_vencimiento" => isset($_POST["fecha_vencimiento"]) && !empty($_POST["fecha_vencimiento"]) ? $_POST["fecha_vencimiento"] : null,
				"orden_compra" => null,
				"metodo_pago_dian_id" => null,
				"estado_dian" => 'pendiente',
				"cufe" => null,
				"qr_data" => null,
				"xml_dian" => null,
				"pdf_dian" => null,
				"mensaje_dian" => "Pendiente de envío",
				"fecha_envio_dian" => null,
				"numero_factura" => null
			);

			// 🔹 SI ES CONVERSIÓN DE ORDEN, ELIMINAR LA ORDEN ORIGINAL E INSERTAR LA FE
			if (isset($_POST["editarVenta"]) && !empty($_POST["editarVenta"])) {
				$ventaOriginal = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $_POST["editarVenta"]);
				if ($ventaOriginal) {
					ModeloVentas::mdlEliminarVenta($tabla, $ventaOriginal["id"]);
				}
			}

			// INSERTAR COMO UNA NUEVA VENTA (TIPO FE)
			$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);

			if (is_numeric($respuesta)) {

				// Verificar stock
				ControladorNotificaciones::ctrVerificarStockProductos();

				/*=============================================
				 4. ENVIAR A FACTUS
				 =============================================*/

				/*=============================================
				 4. ENVIAR A FACTUS (Refactorizado para usar lógica unificada)
				 =============================================*/
				require_once __DIR__ . "/factus.controlador.php";

				// Recuperar ID venta
				$idVenta = $respuesta;

				// Generar factura electrónica utilizando el controlador unificado
				// Se usa false para guardar como borrador (SIN FIRMAR)
				$resultadoFactura = ControladorFactus::ctrGenerarFacturaElectronica($idVenta, false);

				if (!$resultadoFactura["error"]) {
					// EXITO
					if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo json_encode([
							"status" => "success",
							"titulo" => "Factura Electrónica GUARDADA",
							"mensaje" => "La factura ha sido guardada correctamente como borrador",
							"ruta" => "facturas-electronicas"
						]);
						return;
					}

					echo '<script>
						swal({
						  type: "success",
						  title: "Factura Electrónica GUARDADA",
						  text: "La factura ha sido guardada correctamente como borrador",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
									window.location = "facturas-electronicas";
								})
						</script>';
				}
				else {
					// ERROR
					$errorMsg = $resultadoFactura["mensaje"];
					if (isset($resultadoFactura["errores"]) && !empty($resultadoFactura["errores"])) {
						$errorMsg .= " " . implode(", ", $resultadoFactura["errores"]);
					}

					if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						if (ob_get_length()) ob_clean();
						echo json_encode([
							"status" => "success", // Se considera éxito porque la venta se guardó localmente
							"titulo" => "Venta guardada pero Factura RECHAZADA",
							"mensaje" => $errorMsg,
							"ruta" => "facturas-electronicas"
						]);
						return;
					}

					echo '<script>
						swal({
						  type: "warning",
						  title: "Venta guardada pero Factura RECHAZADA",
						  text: "' . str_replace('"', "'", $errorMsg) . '",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
									window.location = "facturas-electronicas";
								})
						</script>';
				}

			}
			else {
				if (isset($_POST["ajax"]) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
					if (ob_get_length()) ob_clean();
					echo json_encode([
						"status" => "error",
						"titulo" => "Error al guardar la venta localmente",
						"mensaje" => "No se pudo realizar el guardado en la base de datos local"
					]);
					return;
				}

				echo '<script>
					swal({
						  type: "error",
						  title: "Error al guardar la venta localmente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';
			}
		}
	}
}