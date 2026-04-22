/*=============================================
CARGAR DATATABLE DE MOVIMIENTOS
=============================================*/
var tablaMovimientos;

function cargarTablaMovimientos() {

	// Si ya existen datos pre-cargados y no hemos inicializado la tabla, usarlos para saltar el primer AJAX
	var usePreloaded = window.preloadedMovimientos && !$.fn.DataTable.isDataTable('.tablaHistorialStock');
	
	if ($.fn.DataTable.isDataTable('.tablaHistorialStock')) {
		tablaMovimientos.ajax.reload();
		return;
	}

	tablaMovimientos = $(".tablaHistorialStock").DataTable({
		"data": usePreloaded ? window.preloadedMovimientos : null,
		"ajax": usePreloaded ? null : {
			"url": "ajax/movimientos.ajax.php",
			"method": "POST",
			"data": function (d) {
				d.accion = "obtenerMovimientos";
				d.id_producto = $("#filtroProducto").val() || "";
				d.tipo_movimiento = $("#filtroTipo").val() || "";
				d.fecha_desde = $("#filtroFechaDesde").val() || "";
				d.fecha_hasta = $("#filtroFechaHasta").val() || "";
				d.usuario = $("#filtroUsuario").val() || "";
				d.csrf_token = $('meta[name="csrf-token"]').attr('content');
			},
			"dataSrc": ""
		},
		"deferRender": true,
		"responsive": {
			"details": {
				"type": "inline",
				"renderer": function (api, rowIdx, columns) {
					var finalHtml = '';
					var hasHidden = false;

					$.each(columns, function (i, col) {
						if (!col.hidden) return;
						hasHidden = true;

						var label = col.title || ('Columna ' + col.columnIndex);
						
						finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
						finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
						finalHtml += '<span style="color:#333;">' + col.data + '</span>';
						finalHtml += '</div>';
					});

					if (!hasHidden) return false;
					return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
				}
			}
		},
		"autoWidth": false,
		"columns": [
			{ "data": "nombre_producto" },
			{
				"data": "tipo_movimiento",
				"render": function (data) {
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
				"data": "tipo_producto",
				"render": function (data) {
					return data == "producto" ? '<span class="label label-primary">Producto</span>' : '<span class="label label-info">Variante</span>';
				}
			},
			{
				"data": "fecha",
				"render": function (data, type, row) {
					if (type === 'display' || type === 'filter') {
						var fecha = new Date(data);
						return fecha.toLocaleString('es-ES', {
							year: 'numeric', month: '2-digit', day: '2-digit',
							hour: '2-digit', minute: '2-digit'
						});
					}
					return data;
				}
			},
			{
				"data": "cantidad",
				"render": function (data) {
					return data > 0 ? '<span class="text-green"><i class="fa fa-arrow-up"></i> +' + data + '</span>' : '<span class="text-red"><i class="fa fa-arrow-down"></i> ' + data + '</span>';
				}
			},
			{ "data": "stock_anterior" },
			{
				"data": "stock_nuevo",
				"render": function (data, type, row) {
					var cambio = row.stock_nuevo - row.stock_anterior;
					if (cambio > 0) return '<strong class="text-green">' + data + '</strong>';
					if (cambio < 0) return '<strong class="text-red">' + data + '</strong>';
					return data;
				}
			},
			{ "data": "nombre_usuario" },
			{ "data": "referencia" },
			{
				"data": "notas",
				"render": function (data, type, row) {
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
			"sSearch": "Buscar:",
			"oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
		},
		"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
		"columnDefs": [
			{ "targets": 0, "responsivePriority": 1 },
			{ "targets": 1, "responsivePriority": 1 },
			{ "targets": 4, "responsivePriority": 1 },
			{ "targets": 2, "responsivePriority": 2 },
			{ "targets": 3, "responsivePriority": 2 },
			{ "targets": [5, 6, 7, 8, 9], "responsivePriority": 3 }
		],
		"order": [[3, "desc"]],
		"ordering": true,
		"processing": true,
		"pageLength": 25,
		"initComplete": function () {
			this.api().responsive.recalc();
			// Limpiar datos pre-cargados después del primer uso
			window.preloadedMovimientos = null;
		}
	});
}

/*=============================================
CARGAR RESUMEN DE MOVIMIENTOS
=============================================*/
function cargarResumen() {

	if (window.preloadedResumen) {
		// Los valores ya fueron inyectados directamente en el HTML por PHP
		// Solo limpiamos la variable para el siguiente refresco manual
		window.preloadedResumen = null;
		return;
	}

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
