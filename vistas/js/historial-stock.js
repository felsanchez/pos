/*=============================================
CARGAR DATATABLE DE MOVIMIENTOS
=============================================*/
var tablaMovimientos;

function cargarTablaMovimientos() {

	if (tablaMovimientos) {
		tablaMovimientos.destroy();
	}

	var filtros = {
		accion: "obtenerMovimientos",
		id_producto: $("#filtroProducto").val() || "",
		tipo_movimiento: $("#filtroTipo").val() || "",
		fecha_desde: $("#filtroFechaDesde").val() || "",
		fecha_hasta: $("#filtroFechaHasta").val() || "",
		usuario: $("#filtroUsuario").val() || "",
		csrf_token: $('meta[name="csrf-token"]').attr('content')
	};

	$.ajax({
		url: "ajax/movimientos.ajax.php",
		method: "POST",
		data: filtros,
		dataType: "json",
		success: function (movimientos) {

			console.log("Movimientos cargados:", movimientos);

			tablaMovimientos = $(".tablaHistorialStock").DataTable({

				data: movimientos,

				responsive: {
					details: {
						type: "column",
						target: 0, // El clic recae estrictamente sobre la Fecha (índice 0)
						renderer: function (api, rowIdx, columns) {
							var data = $.map(columns, function (col, i) {
								return col;
							});

							function getVal(idx) {
								return api.cell(rowIdx, idx).render('display');
							}

							var finalHtml = '';

							// SECCIÓN 1: Producto Afectado (Producto(1), Tipo(2))
							finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
							finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Producto Afectado</h5></div>';

							finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Producto: </span><span class="pull-right">' + getVal(1) + '</span></div>';

							finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Tipo: </span><span class="pull-right">' + getVal(2) + '</span></div>';

							// SECCIÓN 2: Detalle del Movimiento (Tipo Mov(3), Cantidad(4), Stock Ant(5), Stock Nuevo(6))
							finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
							finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Detalle del Movimiento</h5></div>';

							finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Tipo Movimiento: </span><span class="pull-right">' + getVal(3) + '</span></div>';

							finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Cantidad: </span><span class="pull-right">' + getVal(4) + '</span></div>';

							finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Stock Anterior: </span><span class="pull-right">' + getVal(5) + '</span></div>';

							finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Stock Nuevo: </span><span class="pull-right">' + getVal(6) + '</span></div>';

							// SECCIÓN 3: Información Adicional (Usuario(7), Referencia(8), Notas(9))
							finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
							finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Información Adicional</h5></div>';

							finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Usuario: </span><span class="pull-right">' + getVal(7) + '</span></div>';

							finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold">Referencia: </span><span class="pull-right">' + getVal(8) + '</span></div>';

							finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
							finalHtml += '<span class="text-bold" style="display:block; margin-bottom:4px;">Notas:</span>';
							finalHtml += '<span>' + getVal(9) + '</span></div>';

							return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0; text-align: left;">').append(finalHtml) : false;
						}
					}
				},

				autoWidth: false,

				columns: [
					{
						data: "fecha",
						className: "all dtr-control",
						responsivePriority: 1, 
						render: function (data) {
							var fecha = new Date(data);
							return fecha.toLocaleString('es-ES', {
								year: 'numeric',
								month: '2-digit',
								day: '2-digit',
								hour: '2-digit',
								minute: '2-digit'
							});
						}
					},
					{
						data: "nombre_producto",
						className: "all",
						responsivePriority: 1
					},
					{
						data: "tipo_producto",
						responsivePriority: 100,
						render: function (data) {
							if (data == "producto") {
								return '<span class="label label-primary">Producto</span>';
							} else {
								return '<span class="label label-info">Variante</span>';
							}
						}
					},
					{
						data: "tipo_movimiento",
						className: "all",
						responsivePriority: 1,
						render: function (data) {
							var badges = {
								"venta": '<span class="label label-success">Venta</span>',
								"devolucion": '<span class="label label-warning">Devolución</span>',
								"eliminacion_venta": '<span class="label label-danger">Eliminación Venta</span>',
								"ajuste_manual": '<span class="label label-default">Ajuste Manual</span>',
								"creacion_producto": '<span class="label label-primary">Creación</span>',
								"creacion_variante": '<span class="label label-info">Creación Variante</span>',
								"edicion_stock": '<span class="label label-default">Edición Stock</span>'
							};
							return badges[data] || data;
						}
					},
					{
						data: "cantidad",
						responsivePriority: 100,
						render: function (data) {
							if (data > 0) {
								return '<span class="text-green"><i class="fa fa-arrow-up"></i> +' + data + '</span>';
							} else {
								return '<span class="text-red"><i class="fa fa-arrow-down"></i> ' + data + '</span>';
							}
						}
					},
					{
						data: "stock_anterior",
						responsivePriority: 100
					},
					{
						data: "stock_nuevo",
						responsivePriority: 100,
						render: function (data, type, row) {
							var cambio = row.stock_nuevo - row.stock_anterior;
							if (cambio > 0) {
								return '<strong class="text-green">' + data + '</strong>';
							} else if (cambio < 0) {
								return '<strong class="text-red">' + data + '</strong>';
							} else {
								return data;
							}
						}
					},
					{
						data: "nombre_usuario",
						responsivePriority: 100
					},
					{
						data: "referencia",
						responsivePriority: 100
					},
					{
						data: "notas",
						responsivePriority: 100,
						render: function (data, type, row) {
							return '<div contenteditable="true" class="celda-notas-movimiento" data-id="' + row.id + '">' + data + '</div>';
						}
					}
				],

				"language": {
					"sProcessing": "Procesando...",
					"sLengthMenu": "Mostrar _MENU_ registros",
					"sZeroRecords": "No se encontraron resultados",
					"sEmptyTable": "Ningún dato disponible en esta tabla",
					"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
					"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
					"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
					"sSearch": "Buscar:",
					"oPaginate": {
						"sFirst": "Primero",
						"sLast": "Último",
						"sNext": "Siguiente",
						"sPrevious": "Anterior"
					}
				},

				"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
				"order": [],
				"ordering": false,
				"pageLength": 25,
				"preDrawCallback": function () {
					if (!$(this).hasClass('datatable-ready')) {
						$(this).css('visibility', 'hidden');
					}
				},
				"initComplete": function () {
					$(this).addClass('datatable-ready').css('visibility', 'visible');
					this.api().responsive.recalc();
				}

			});

		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.error("Error al cargar movimientos:", textStatus, errorThrown);
		}
	});
}

/*=============================================
CARGAR RESUMEN DE MOVIMIENTOS
=============================================*/
function cargarResumen() {

	var filtros = {
		accion: "obtenerResumen",
		fecha_desde: $("#filtroFechaDesde").val(),
		fecha_hasta: $("#filtroFechaHasta").val(),
		csrf_token: $('meta[name="csrf-token"]').attr('content')
	};

	$.ajax({
		url: "ajax/movimientos.ajax.php",
		method: "POST",
		data: filtros,
		dataType: "json",
		success: function (resumen) {

			// Resetear contadores
			$("#totalVentas").text("0");
			$("#totalCreaciones").text("0");
			$("#totalEdiciones").text("0");
			$("#totalMovimientos").text("0");

			var totalMovimientos = 0;
			var totalCreaciones = 0;

			resumen.forEach(function (item) {
				totalMovimientos += parseInt(item.total_movimientos);

				if (item.tipo_movimiento == "venta") {
					$("#totalVentas").text(item.total_unidades);
				}
				if (item.tipo_movimiento == "creacion_producto" || item.tipo_movimiento == "creacion_variante") {
					totalCreaciones += parseInt(item.total_unidades);
				}
				if (item.tipo_movimiento == "edicion_stock") {
					$("#totalEdiciones").text(item.total_unidades);
				}
			});

			$("#totalCreaciones").text(totalCreaciones);
			$("#totalMovimientos").text(totalMovimientos);

		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.error("Error al cargar resumen:", textStatus, errorThrown);
		}
	});
}

/*=============================================
CARGAR DATOS AL INICIAR
=============================================*/
$(document).ready(function () {

	// Solo ejecutar si estamos en la página de historial de stock
	if ($(".tablaHistorialStock").length > 0) {

		// Inicializar Select2 para los filtros
		if (typeof $.fn.select2 !== 'undefined') {
			$('#filtroProducto').select2({
				placeholder: "Seleccionar producto...",
				allowClear: true,
				minimumResultsForSearch: 0,
				width: '100%'
			});
			$('#filtroUsuario').select2({
				placeholder: "Seleccionar usuario...",
				allowClear: true,
				minimumResultsForSearch: 0,
				width: '100%'
			});
			$('#filtroTipo').select2({
				placeholder: "Seleccionar tipo...",
				allowClear: true,
				minimumResultsForSearch: 0,
				width: '100%'
			});
		}

		// Cargar datos al inicio
		cargarTablaMovimientos();
		cargarResumen();

		/*=============================================
		RANGO DE FECHAS
		=============================================*/
		if ($('#daterange-btn').length > 0) {
			
			$('#daterange-btn').daterangepicker(
				{
					ranges: {
						'Hoy': [moment(), moment()],
						'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
						'Este mes': [moment().startOf('month'), moment().endOf('month')],
						'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					},
					startDate: moment().subtract(29, 'days'),
					endDate: moment()
				},
				function (start, end) {
					$('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

					var fechaInicial = start.format('YYYY-MM-DD');
					var fechaFinal = end.format('YYYY-MM-DD');

					// Actualizar inputs ocultos
					$("#filtroFechaDesde").val(fechaInicial);
					$("#filtroFechaHasta").val(fechaFinal);
				}
			);
		}

		// Inicializar edición de notas
		inicializarEdicionNotas();
	}

});

/*=============================================
BOTÓN FILTRAR
=============================================*/
$("#btnFiltrar").click(function () {
	cargarTablaMovimientos();
	cargarResumen();
});

/*=============================================
BOTÓN LIMPIAR FILTROS
=============================================*/
$("#btnLimpiar").click(function () {
	$("#filtroProducto").val("").trigger("change");
	$("#filtroTipo").val("").trigger("change");
	$("#filtroFechaDesde").val("");
	$("#filtroFechaHasta").val("");
	$("#filtroUsuario").val("").trigger("change");

	// Resetear texto del botón de rango
	$("#daterange-btn span").html('<i class="fa fa-calendar"></i> Rango de fecha');

	cargarTablaMovimientos();
	cargarResumen();
});

/*=============================================
BOTÓN EXPORTAR A EXCEL
=============================================*/
$("#btnExportarExcel").click(function () {

	var parametros = "?exportarMovimientos=1";
	parametros += "&producto=" + $("#filtroProducto").val();
	parametros += "&tipo=" + $("#filtroTipo").val();
	parametros += "&desde=" + $("#filtroFechaDesde").val();
	parametros += "&hasta=" + $("#filtroFechaHasta").val();
	parametros += "&usuario=" + $("#filtroUsuario").val();

	window.open("index.php" + parametros, '_blank');

});

/*=============================================
EDICIÓN INLINE DE NOTAS
=============================================*/
function inicializarEdicionNotas() {
	$(document).off('blur', '.celda-notas-movimiento').on('blur', '.celda-notas-movimiento', function () {
		const $celda = $(this);
		const id = $celda.data('id');
		const nuevaNota = $celda.text().trim();

		// Feedback visual inicial: NARANJA (Guardando...)
		$celda.css({
			'background-color': '#fff4e6',
			'border-color': '#ffd8a8'
		});

		$.ajax({
			url: 'ajax/movimientos.ajax.php',
			method: 'POST',
			data: {
				id: id,
				notas: nuevaNota,
				accion: 'actualizarNota',
				csrf_token: $('meta[name="csrf-token"]').attr('content')
			},
			success: function (respuesta) {
				console.log('Nota actualizada:', respuesta);

				// Verificar éxito de forma flexible (soporta "ok" o JSON true/success)
				if (respuesta === "ok" || respuesta === "\"ok\"" || respuesta === true || 
					(typeof respuesta === 'object' && (respuesta.success || respuesta.id))) {
					
					// Feedback de éxito: VERDE
					$celda.css({
						'background-color': '#d4edda',
						'border-color': '#c3e6cb',
						'color': '#155724'
					});

					// Volver al estado normal después de un breve destello
					setTimeout(function () {
						$celda.css({
							'background-color': '',
							'border-color': '',
							'color': ''
						});
					}, 800);

				} else {
					// Feedback de error: ROJO
					$celda.css({
						'background-color': '#f8d7da',
						'border-color': '#f5c6cb',
						'color': '#721c24'
					});
					alert('Error: El servidor no confirmó el guardado.');
				}
			},
			error: function () {
				// Feedback de error crítico: ROJO INTENSO
				$celda.css({
					'background-color': '#f8d7da',
					'border-color': '#f5c6cb'
				});
				alert('Error crítico al conectar con el servidor.');
			}
		});
	});
}
