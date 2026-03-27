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
			console.log("Total de movimientos recibidos:", movimientos.length);
			console.log("Primeros 5 registros:", movimientos.slice(0, 5));
			console.log("Configurando tabla con responsive mode...");

			tablaMovimientos = $(".tablaHistorialStock").DataTable({

				data: movimientos,

				responsive: {
					details: true
				},

				autoWidth: false,

				columns: [

					{
						data: "fecha",
						className: "all",
						responsivePriority: 1, // Mostrar Fecha
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
						responsivePriority: 1 // Mostrar Producto
					},
					{
						data: "tipo_producto",
						responsivePriority: 100, // Ocultar Tipo Producto
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
						responsivePriority: 1, // Mostrar Tipo Movimiento
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
						responsivePriority: 100, // Ocultar Cantidad
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
						responsivePriority: 100 // Ocultar Stock Anterior
					},
					{
						data: "stock_nuevo",
						responsivePriority: 100, // Ocultar Stock Nuevo
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
						responsivePriority: 100 // Ocultar Usuario
					},
					{
						data: "referencia",
						responsivePriority: 100 // Ocultar Referencia
					},
					{
						data: "notas",
						responsivePriority: 100, // Ocultar Notas
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
					"sInfoPostFix": "",
					"sSearch": "Buscar:",
					"sUrl": "",
					"sInfoThousands": ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
						"sFirst": "Primero",
						"sLast": "Último",
						"sNext": "Siguiente",
						"sPrevious": "Anterior"
					},
					"oAria": {
						"sSortAscending": ": Activar para ordenar la columna de manera ascendente",
						"sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}
				},

				"order": [],
				"ordering": false,
				"pageLength": 25,

				"initComplete": function () {
					console.log("Tabla inicializada, recalculando responsive...");
					this.api().responsive.recalc();
				}

			});

			console.log("DataTable creado:", tablaMovimientos);
			console.log("Responsive enabled:", tablaMovimientos.responsive);

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

			console.log("Resumen:", resumen);

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

		// 🔹 LIMPIAR CAMPO FECHA HASTA para evitar filtros residuales del caché
		$("#filtroFechaHasta").val("");

		// Cargar datos al inicio
		cargarTablaMovimientos();
		cargarResumen();
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
	$("#filtroProducto").val("");
	$("#filtroTipo").val("");
	$("#filtroFechaDesde").val("");
	$("#filtroFechaHasta").val("");
	$("#filtroUsuario").val("");

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
		const id = $(this).data('id');
		const nuevaNota = $(this).text().trim();

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
			},
			error: function () {
				alert('Error al actualizar la nota');
			}
		});
	});
}

// Ejecutar al cargar por primera vez
$(document).ready(function () {
	inicializarEdicionNotas();
});
