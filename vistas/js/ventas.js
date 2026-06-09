$(document).ready(function () {
	// Función para quitar el loader de Facturas Electrónicas (Hacerla global)
	window.quitarLoaderGlobal = function () {
		if ($("#loader-table").length > 0) {
			$("#loader-table").fadeOut(200, function () {
				$(this).remove();
			});
		}
	}

	// 1. Escuchar el evento de inicialización de DataTables de la tabla de Facturas Electrónicas
	$(document).on('init.dt', '#tablaFacturasElectronicas', function () {
		console.log("DataTables inicializado (evento delegado) para Facturas Electrónicas");
		window.quitarLoaderGlobal();
	});

	// 2. Respaldo: Si la tabla ya tiene la clase 'datatable-ready', quitar loader
	if ($('#tablaFacturasElectronicas').hasClass('datatable-ready')) {
		window.quitarLoaderGlobal();
	}

	// 3. Respaldo adicional: Si por alguna razón pasan 5 segundos y sigue el spinner, quitarlo
	setTimeout(window.quitarLoaderGlobal, 5000);

	// Inicializar Select2 para los filtros de Cliente y Usuario
	if (typeof $.fn.select2 !== 'undefined') {
		$('.select-cliente').select2({
			placeholder: "Seleccionar cliente...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
		$('.select-usuario').select2({
			placeholder: "Seleccionar usuario...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
		$('.select-bodega').select2({
			placeholder: "Todas las sucursales...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
	}

	/*=============================================
	RANGO DE FECHAS (dentro de document.ready para que el DOM exista)
	=============================================*/
	/*=============================================
	RANGO DE FECHAS (Desactivado aquí para Administrar Ventas, se maneja en ventas.php)
	=============================================*/
	// Si no estamos en la vista de administrar ventas (ej: reportes), inicializarlo normalmente
	if ($('#daterange-btn').length > 0 && !$('#tablaListaVentas').length) {
		$('#daterange-btn').daterangepicker(
			{
				ranges: {
					'Mostrar todas': [moment('2000-01-01'), moment()],
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
				if (start.format('YYYY-MM-DD') === '2000-01-01') {
					$('#daterange-btn span').html('<i class="fa fa-calendar"></i> Mostrar todas');
					$('#fechaInicial').val('');
					$('#fechaFinal').val('');
				} else {
					$('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
					$('#fechaInicial').val(start.format('YYYY-MM-DD'));
					$('#fechaFinal').val(end.format('YYYY-MM-DD'));
				}
			}
		);

		$('#daterange-btn').on('cancel.daterangepicker', function () {
			$(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar todas');
			$('#fechaInicial').val('');
			$('#fechaFinal').val('');
		});
	}
});

/*=============================================
VARIABLE LOCAL STORAGE (solo el texto visual del botón)
=============================================*/
// Nota: La restauración de fechas en inputs se hace dentro de document.ready más abajo


/*=============================================  
CARGAR TABLA DINAMICA
=============================================*/

var table2 = $("table.tablaVentas").DataTable({
	"responsive": false,
	"scrollX": true,
	"processing": true,
	"serverSide": true,
	"ajax": {
		"url": "ajax/datatable-ventas.ajax.php",
		"type": "POST",
		"data": function (d) {
			d.csrf_token = $('meta[name="csrf-token"]').attr('content');
			d.fechaInicial = $('#fechaInicial').val();
			d.fechaFinal = $('#fechaFinal').val();
			d.clienteId = $('.select-cliente').length ? ($('.select-cliente').val() || '') : '';
			d.usuarioId = $('.select-usuario').length ? ($('.select-usuario').val() || '') : '';
			d.bodegaId = $('.select-bodega').length ? ($('.select-bodega').val() || '') : '';

			var urlParams = new URLSearchParams(window.location.search);
			if (!d.fechaInicial) d.fechaInicial = urlParams.get('fechaInicial');
			if (!d.fechaFinal) d.fechaFinal = urlParams.get('fechaFinal');
			if (!d.clienteId) d.clienteId = urlParams.get('cliente');
			if (!d.usuarioId) d.usuarioId = urlParams.get('usuario');
			if (!d.bodegaId) d.bodegaId = urlParams.get('bodega');
		}
	},
	"columnDefs": [
		{
			"targets": 1, // Imagen
			"render": function (data, type, row) {
				return '<img class="img-thumbnail imgTablaVenta" src="' + row[1] + '" width="40px">';
			}
		},
		{
			"targets": 3, // Descripción
			"width": "250px"
		},
		{
			"targets": 5, // Acciones
			"render": function (data, type, row) {
				// row[6] contiene si tiene variantes (1) o no (0)
				// row[5] contiene el ID del producto
				if (row[6] == "1") {
					// Producto con variantes - mostrar botón Variantes
					return '<div class="btn-group"><button class="btn btn-warning btnVariantesVenta recuperarBoton" data-id-producto="' + row[5] + '"><i class="fa fa-list"></i> Variantes</button></div>';

				} else {
					// Producto sin variantes - mostrar botón Agregar
					return '<div class="btn-group"><button class="btn btn-primary agregarProducto recuperarBoton" idProducto="' + row[5] + '">Agregar</button></div>';
				}
			}
		},
		{
			"targets": 4, // Stock
			"render": function (data, type, row) {
				var stock = row[4];
				var btnClass = "btn-success";
				if (stock <= 10) btnClass = "btn-danger";
				else if (stock >= 11 && stock <= 15) btnClass = "btn-warning";
				return '<div class="btn-group"><button class="btn ' + btnClass + ' limiteStock">' + stock + '</button></div>';
			}
		},
		{
			"targets": 2, // Código
		},
		{
			"targets": 0, // #
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
	"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>'



})


/*=============================================
ACTIVAR LOS BOTONES CON LOS ID CORRESPONDIENTES
=============================================*/





/*=============================================
EXPANDIR VARIANTES EN VENTAS
=============================================*/

// Función para formatear la tabla de variantes en ventas
function formatearTablaVariantesVenta(variantes) {

	if (!variantes || variantes.length === 0) {
		return '<div class="alert alert-info">No hay variantes para este producto</div>';
	}

	// Función auxiliar para formatear precios
	function formatearPrecio(numero) {
		return Math.round(numero).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
	}

	var html = '<table class="table table-condensed table-bordered table-striped" style="background-color: white; margin-bottom: 0;">';
	html += '<thead>';
	html += '<tr>';
	html += '<th>Variante</th>';
	html += '<th width="120px">Precio</th>';
	html += '<th width="80px">Stock</th>';
	html += '<th width="100px">Acción</th>';
	html += '</tr>';
	html += '</thead>';
	html += '<tbody>';

	for (var i = 0; i < variantes.length; i++) {
		var variante = variantes[i];

		if (variante.estado != 1) continue;

		var stockBadge = '';
		if (variante.stock <= 0) {
			stockBadge = '<span class="badge bg-red">' + variante.stock + '</span>';
		} else if (variante.stock <= 10) {
			stockBadge = '<span class="badge bg-yellow">' + variante.stock + '</span>';
		} else {
			stockBadge = '<span class="badge bg-green">' + variante.stock + '</span>';
		}

		var botonAgregar = '';

		if (variante.stock > 0) {
			botonAgregar = '<button class="btn btn-primary btn-xs agregarVarianteVenta" ' +
				'idVariante="' + variante.id + '" ' +
				'idProductoBase="' + variante.id_producto + '" ' +
				'nombreVariante="' + variante.nombre + '" ' +
				'precioVariante="' + variante.precio_final + '" ' +
				'stockVariante="' + variante.stock + '" ' +
				'skuVariante="' + variante.sku + '" ' +
				'impuestoPorcentaje="' + variante.impuesto_porcentaje + '" ' +
				'impuestoNombre="' + variante.impuesto_nombre + '">Agregar</button>';
		} else {
			botonAgregar = '<button class="btn btn-default btn-xs" disabled>Sin stock</button>';
		}

		html += '<tr>';
		html += '<td>' + variante.nombre + '</td>';
		html += '<td><strong>$' + formatearPrecio(variante.precio_final) + '</strong></td>';
		html += '<td class="text-center">' + stockBadge + '</td>';
		html += '<td class="text-center">' + botonAgregar + '</td>';
		html += '</tr>';
	}

	html += '</tbody>';
	html += '</table>';
	return html;
}

// Evento click en botón de expandir variantes
$(document).on('click', '.btnVariantesVenta', function (e) {

	e.stopPropagation();

	var boton = $(this);
	var tr = boton.closest('tr');

	// Si el botón está en una fila hija (responsive), obtenemos la fila padre (la anterior)
	if (tr.hasClass('child')) {
		tr = tr.prev();
	}

	var row = table2.row(tr);
	var idProducto = boton.attr('data-id-producto');
	var icono = boton.find('i');

	// Si la fila ya está expandida, colapsarla
	if (row.child.isShown()) {
		row.child.hide();
		tr.removeClass('shown');
		icono.removeClass('fa-minus').addClass('fa-list');
		boton.removeClass('btn-danger').addClass('btn-warning');
	} else {
		// Expandir la fila

		// Deshabilitar botón y mostrar loading
		boton.prop('disabled', true);
		icono.removeClass('fa-list').addClass('fa-spinner fa-spin');

		// Solicitar variantes por AJAX
		var datos = new FormData();
		datos.append("obtenerVariantesProducto", idProducto);

		$.ajax({
			url: "ajax/productos.ajax.php",
			method: "POST",
			data: datos,
			cache: false,
			contentType: false,
			processData: false,
			dataType: "json",
			success: function (variantes) {

				// Formatear tabla de variantes
				var tablaVariantes = formatearTablaVariantesVenta(variantes);

				// Mostrar fila expandida
				row.child(tablaVariantes).show();
				tr.addClass('shown');

				// Cambiar icono del botón
				icono.removeClass('fa-spinner fa-spin fa-list').addClass('fa-minus');
				boton.removeClass('btn-warning').addClass('btn-danger');
				boton.prop('disabled', false);
			},

			error: function (jqXHR, textStatus, errorThrown) {
				console.error("Error al cargar variantes:", textStatus, errorThrown);
				swal({
					type: "error",
					title: "Error al cargar las variantes",
					text: "Por favor, intenta nuevamente"
				});
				icono.removeClass('fa-spinner fa-spin').addClass('fa-list');
				boton.prop('disabled', false);
			}
		});
	}

});




/*=============================================
FUNCION PARA CARGAR CON EL PAGINADOR Y CON EL FILTRO
=============================================*/





/*=============================================
AGREGANDO PRODUCTOS A LA VENTA DESDE A TABLA
=============================================*/
$('.tablaVentas tbody').on("click", "button.agregarProducto", function () {

	var idProducto = $(this).attr("idProducto");
	var boton = $(this);

	$(this).removeClass("btn-primary agregarProducto");

	$(this).addClass("btn-default");

	var datos = new FormData();
	datos.append("idProducto", idProducto);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url: "ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			var descripcion = respuesta["descripcion"];
			var stock = respuesta["stock"];
			var precio = respuesta["precio_venta"];

			if (Number(stock) <= 0) {
				swal({
					title: "No hay stock disponible",
					text: "El stock de este producto es 0",
					type: "error",
					confirmButtonText: "¡Cerrar!"
				});

				boton.removeClass("btn-default").addClass("btn-primary agregarProducto");
				return;
			}

			// Calcular impuesto del producto (Precio incluye impuesto)
			var impuestoPorcentaje = respuesta["impuesto_porcentaje"] ? Number(respuesta["impuesto_porcentaje"]) : 0;
			var impuestoNombre = respuesta["impuesto_nombre"] ? respuesta["impuesto_nombre"] : "Exento";

			// Formula: Impuesto = Precio * (Porcentaje / (100 + Porcentaje))
			var impuestoValor = precio * (impuestoPorcentaje / (100 + impuestoPorcentaje));

			if (impuestoPorcentaje == 0) impuestoValor = 0;

			// Limpiar nombre del impuesto para evitar redundancia (ej: "IVA 19%" -> "IVA")
			var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();

			$(".nuevoProducto").append(

				'<div class="row" style="padding:5px 15px">' +

				'<!--Descripcion del producto-->' +

				'<div class="col-xs-5" style="padding-right:0px">' +

				'<div class="input-group">' +

				'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="' + idProducto + '"><i class="fa fa-times"></i></button></span>' +

				'<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProducto + '" name="agregarProducto" value="' + descripcion + '" readonly required>' +

				'</div>' +

				'</div>' +

				'<!--Impuesto del producto (col-xs-2)-->' +

				'<div class="col-xs-2 ingresoImpuesto">' +

				'<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' + nombreCorto + ' ' + impuestoPorcentaje + '%" porcentaje="' + impuestoPorcentaje + '" impuestoNombre="' + impuestoNombre + '" readonly required>' +

				'</div>' +


				'<!--Cantidad del producto-->' +

				'<div class="col-xs-2">' +

				'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock="' + stock + '" nuevoStock="' + Number(stock - 1) + '" required>' +

				'</div>' +

				'<!--Precio del producto-->' +

				'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +

				'<div class="input-group">' +

				'<input type="text" class="form-control nuevoPrecioProducto" precioReal="' + precio + '" name="nuevoPrecioProducto" value="' + precio + '" readonly required>' +

				'</div>' +

				'</div>' +

				'</div>')

			//Sumar total de precios
			sumarTotalPrecios()

			//Sumar total de impuestos (FUNCION NUEVA)
			sumarTotalImpuestos()

			//Agregar impuesto
			aplicarDescuento()

			//Agrupar productos en formato Json
			listarProductos()

			//Poner formato number al precio de los productos
			$(".nuevoPrecioProducto").number(true, 0);
		}

	})


});


/*=============================================
AGREGANDO VARIANTES A LA VENTA
=============================================*/

$(document).on("click", ".agregarVarianteVenta", function () {

	var idVariante = $(this).attr("idVariante");
	var idProductoBase = $(this).attr("idProductoBase");
	var nombreVariante = $(this).attr("nombreVariante");
	var precioVariante = $(this).attr("precioVariante");
	var stockVariante = $(this).attr("stockVariante");
	var skuVariante = $(this).attr("skuVariante");

	if (Number(stockVariante) <= 0) {
		swal({
			title: "No hay stock disponible",
			text: "El stock de esta variante es 0",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
		return;
	}

	// Cambiar apariencia del botón
	$(this).removeClass("btn-primary");
	$(this).addClass("btn-default");
	$(this).prop("disabled", true);

	var impuestoPorcentaje = $(this).attr("impuestoPorcentaje") ? Number($(this).attr("impuestoPorcentaje")) : 0;
	var impuestoNombre = $(this).attr("impuestoNombre") ? $(this).attr("impuestoNombre") : "Exento";

	// Limpiar nombre del impuesto para evitar redundancia
	var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();

	// Agregar la variante al carrito
	$(".nuevoProducto").append(

		'<div class="row" style="padding:5px 15px">' +

		'<!--Descripcion de la variante-->' +

		'<div class="col-xs-5" style="padding-right:0px">' +

		'<div class="input-group">' +

		'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarVariante" idVariante="' + idVariante + '"><i class="fa fa-times"></i></button></span>' +
		'<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProductoBase + '" esVariante="1" idVariante="' + idVariante + '" skuVariante="' + skuVariante + '" name="agregarProducto" value="' + nombreVariante + '" readonly required>' +
		'</div>' +

		'</div>' +

		'<!--Impuesto de la variante-->' +

		'<div class="col-xs-2 ingresoImpuesto">' +

		'<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' + nombreCorto + ' ' + impuestoPorcentaje + '%" porcentaje="' + impuestoPorcentaje + '" impuestoNombre="' + impuestoNombre + '" readonly required>' +

		'</div>' +

		'<!--Cantidad de la variante-->' +

		'<div class="col-xs-2">' +

		'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock="' + stockVariante + '" nuevoStock="' + Number(stockVariante - 1) + '" required>' +

		'</div>' +

		'<!--Precio de la variante-->' +

		'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +

		'<div class="input-group">' +

		'<input type="text" class="form-control nuevoPrecioProducto" precioReal="' + precioVariante + '" name="nuevoPrecioProducto" value="' + precioVariante + '" readonly required>' +

		'</div>' +

		'</div>' +

		'</div>')

	// Cerrar el modal
	$("#modalVariantesVenta").modal("hide");

	// Cambiar apariencia del botón principal en la tabla
	$(".btnVariantesVenta[data-id-producto='" + idProductoBase + "']").removeClass("btn-warning").addClass("btn-default");

	//Sumar total de precios
	sumarTotalPrecios()

	//Sumar total de impuestos (PARA VARIANTES)
	sumarTotalImpuestos()

	//Agregar impuesto
	aplicarDescuento()

	//Agrupar productos en formato Json
	listarProductos()

	//Poner formato number al precio de los productos
	$(".nuevoPrecioProducto").number(true, 0);

});


/*=============================================
QUITAR VARIANTES DE LA VENTA Y RECUPERAR BOTON
=============================================*/

$(document).on("click", ".quitarVariante", function () {

	$(this).parent().parent().parent().parent().remove();

	var idVariante = $(this).attr("idVariante");
	var idProductoBase = $(this).closest('.row').find('.nuevaDescripcionProducto').attr('idProducto');

	// Habilitar nuevamente el botón de la variante específica (si el modal se vuelve a abrir)
	$("button.agregarVarianteVenta[idVariante='" + idVariante + "']").removeClass('btn-default').addClass('btn-primary').prop("disabled", false);

	// Verificar si quedan más variantes del mismo producto base en la lista
	var hayMasVariantes = false;
	$(".nuevaDescripcionProducto").each(function () {
		var idProd = $(this).attr("idProducto");
		var esVariante = $(this).attr("esVariante");
		if (idProd == idProductoBase && esVariante == "1") {
			hayMasVariantes = true;
		}
	});

	// Si no hay más variantes de este producto, restauramos el botón principal
	if (!hayMasVariantes) {
		$(".btnVariantesVenta[data-id-producto='" + idProductoBase + "']").removeClass("btn-default").addClass("btn-warning");
	}

	if ($(".nuevoProducto").children().length == 0) {

		$("#nuevoImpuestoVenta").val(0);

		$("#nuevoTotalVenta").val(0);

		$("#nuevoPrecioNeto").val(0);

		$("#nuevoPrecioTotal").val(0);

		$("#totalVenta").val(0);

		$("#nuevoCambioEfectivo").val(0);

		$("#nuevoValorEfectivo").val(0);

	} else {

		//Sumar total de precios
		sumarTotalPrecios()

		//Sumar total de impuestos
		sumarTotalImpuestos()

		//Agregar impuesto
		aplicarDescuento()

		//Agrupar productos en formato Json
		listarProductos()
	}

});


/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTON
=============================================*/

$(".formularioVenta").on("click", "button.quitarProducto", function () {

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");
	var idVariante = $(this).attr("idVariante");

	if (idVariante && idVariante !== "" && idVariante !== "undefined") {
		// Restablecer botón de variantes si aplica
		$("button.agregarVarianteVenta[idVariante='" + idVariante + "']").removeClass('btn-default').addClass('btn-primary').prop("disabled", false);
		
		// Verificar si quedan más variantes de este producto
		var hayMasVariantes = false;
		$(".nuevaDescripcionProducto").each(function () {
			var idProd = $(this).attr("idProducto");
			var esVar = $(this).attr("esVariante");
			if (idProd == idProducto && esVar == "1") {
				hayMasVariantes = true;
			}
		});

		// Si no hay más variantes, habilitar el botón "Variantes" principal
		if (!hayMasVariantes) {
			$(".btnVariantesVenta[data-id-producto='" + idProducto + "']").removeClass("btn-default").addClass("btn-warning");
		}
	} else {
		$("button.recuperarBoton[idProducto='" + idProducto + "']").removeClass('btn-default');
		$("button.recuperarBoton[idProducto='" + idProducto + "']").addClass('btn-primary agregarProducto');
	}


	if ($(".nuevoProducto").children().length == 0) {

		$("#nuevoImpuestoVenta").val(0);
		$("#nuevoTotalVenta").val(0);
		$("#totalVenta").val(0);
		$("#nuevoTotalVenta").attr("total", 0);
	}
	else {
		//Sumar total de precios
		sumarTotalPrecios()

		//Sumar total de impuestos
		sumarTotalImpuestos()

		//Agregar impuesto
		aplicarDescuento()

		//Agrupar productos en formato Json
		listarProductos()
	}

})


/*==========================================================================================
AGREGANDO PRODUCTO DESDE EL BOTON PARA DISPOSITIVOS
==========================================================================================*/

$(".btnAgregarProducto").click(function () {

	var datos = new FormData();
	datos.append("traerProductos", "ok");
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url: "ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			var optionsHtml = '<option value="">Seleccione el producto</option>';
			if (respuesta && respuesta.length > 0) {
				respuesta.forEach(function(item) {
					var optionAttrs = 'idProducto="' + item.id + '"';
					optionAttrs += ' esVariante="' + (item.es_variante || 0) + '"';
					if (item.es_variante == 1) {
						optionAttrs += ' idVariante="' + item.id_variante + '" skuVariante="' + item.sku + '"';
					}
					optionAttrs += ' stock="' + item.stock + '"';
					optionAttrs += ' precio="' + item.precio_venta + '"';
					optionAttrs += ' impuestoPorcentaje="' + (item.impuesto_porcentaje || 0) + '"';
					optionAttrs += ' impuestoNombre="' + (item.impuesto_nombre || 'Exento') + '"';

					var label = item.descripcion;
					if (item.es_variante == 1) {
						label = '&nbsp;&nbsp;&nbsp;&nbsp;└─ ' + item.descripcion;
					}

					var disabledAttr = (item.deshabilitar == 1) ? 'disabled' : '';

					optionsHtml += '<option ' + optionAttrs + ' ' + disabledAttr + ' value="' + item.descripcion + '">' + label + '</option>';
				});
			}

			$(".nuevoProducto").append(

				'<div class="row" style="padding:5px 15px">' +

				'<!--Descripcion del producto-->' +

				'<div class="col-xs-4" style="padding-right:0px">' +

				'<input type="text" class="form-control buscarProductoMovil" placeholder="🔍 Buscar..." style="margin-bottom: 4px; padding: 4px 8px; height: 28px; font-size: 11px; border-radius: 4px; border: 1px solid #ccc; width: 100%;">' +

				'<div class="input-group">' +

				'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto><i class="fa fa-times"></i></button></span>' +

				'<select class="form-control nuevaDescripcionProducto" idProducto name="nuevaDescripcionProducto" required>' +

				optionsHtml +

				'</select>' +

				'</div>' +

				'</div>' +

				'<!--Impuesto del producto-->' +
				'<div class="col-xs-2 ingresoImpuesto">' +
				'<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="" porcentaje="0" impuestoNombre="" readonly required>' +
				'</div>' +

				'<!--Cantidad del producto-->' +

				'<div class="col-xs-3 ingresoCantidad">' +

				'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock nuevoStock required>' +

				'</div>' +


				'<!--Precio del producto-->' +

				'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +

				'<div class="input-group">' +

				'<input type="text" class="form-control nuevoPrecioProducto" precioReal="" name="nuevoPrecioProducto" readonly required>' +

				'</div>' +

				'</div>' +

				'</div>');

			//Agregar impuesto
			aplicarDescuento()

			//Poner formato number al precio de los productos
			$(".nuevoPrecioProducto").number(true, 0);


		}

	})

})


/*=============================================
SELECCIONAR PRODUCTOS (dispositivos)
=============================================*/

$(".formularioVenta").on("change", "select.nuevaDescripcionProducto", function () {

	var select = $(this);
	var optionSelected = select.find("option:selected");
	var row = select.closest(".row");

	if (!optionSelected.length || !optionSelected.attr("idProducto")) {
		// Limpiar si se selecciona la opción vacía
		select.removeAttr("idProducto");
		select.removeAttr("esVariante");
		select.removeAttr("idVariante");
		select.removeAttr("skuVariante");
		
		row.find(".quitarProducto").removeAttr("idProducto");
		row.find(".quitarProducto").removeAttr("idVariante");
		
		row.find(".nuevoPrecioProducto").val(0).attr("precioReal", 0);
		row.find(".nuevaCantidadProducto").val(1).attr("stock", 0).attr("nuevoStock", 0);
		row.find(".nuevoImpuestoProducto").val("").attr("porcentaje", 0).attr("impuestoNombre", "");
		
		listarProductos();
		sumarTotalPrecios();
		sumarTotalImpuestos();
		aplicarDescuento();
		return;
	}

	var idProducto = optionSelected.attr("idProducto");
	var esVariante = optionSelected.attr("esVariante") || "0";
	var idVariante = optionSelected.attr("idVariante") || "";
	var skuVariante = optionSelected.attr("skuVariante") || "";
	var stock = Number(optionSelected.attr("stock") || 0);
	var precio = Number(optionSelected.attr("precio") || 0);
	var impuestoPorcentaje = optionSelected.attr("impuestoPorcentaje") ? Number(optionSelected.attr("impuestoPorcentaje")) : 0;
	var impuestoNombre = optionSelected.attr("impuestoNombre") ? optionSelected.attr("impuestoNombre") : "Exento";

	if (stock <= 0) {
		swal({
			title: "No hay stock disponible",
			text: "El stock de este producto es 0",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
		select.val("");
		// Limpiar fila
		select.removeAttr("idProducto");
		select.removeAttr("esVariante");
		select.removeAttr("idVariante");
		select.removeAttr("skuVariante");
		
		row.find(".quitarProducto").removeAttr("idProducto");
		row.find(".quitarProducto").removeAttr("idVariante");
		
		row.find(".nuevoPrecioProducto").val(0).attr("precioReal", 0);
		row.find(".nuevaCantidadProducto").val(1).attr("stock", 0).attr("nuevoStock", 0);
		row.find(".nuevoImpuestoProducto").val("").attr("porcentaje", 0).attr("impuestoNombre", "");
		
		listarProductos();
		sumarTotalPrecios();
		sumarTotalImpuestos();
		aplicarDescuento();
		return;
	}

	// Actualizar ID del producto en el select y en el botón de quitar
	select.attr("idProducto", idProducto);
	select.attr("esVariante", esVariante);
	select.attr("idVariante", idVariante);
	select.attr("skuVariante", skuVariante);

	row.find(".quitarProducto").attr("idProducto", idProducto);
	row.find(".quitarProducto").attr("idVariante", idVariante);

	var nuevoPrecioProducto = row.find(".nuevoPrecioProducto");
	var nuevaCantidadProducto = row.find(".nuevaCantidadProducto");
	var nuevoImpuestoProducto = row.find(".nuevoImpuestoProducto");

	// Actualizar Stock
	$(nuevaCantidadProducto).attr("stock", stock);
	$(nuevaCantidadProducto).attr("nuevoStock", stock - 1);
	$(nuevaCantidadProducto).val(1);

	// Actualizar Precio
	$(nuevoPrecioProducto).val(precio);
	$(nuevoPrecioProducto).attr("precioReal", precio);

	// Actualizar Impuestos
	var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();

	$(nuevoImpuestoProducto).val(nombreCorto + " " + impuestoPorcentaje + "%");
	$(nuevoImpuestoProducto).attr("porcentaje", impuestoPorcentaje);
	$(nuevoImpuestoProducto).attr("impuestoNombre", impuestoNombre);

	// Agrupar productos en formato Json
	listarProductos();

	// Sumar totales
	sumarTotalPrecios();
	sumarTotalImpuestos();
	aplicarDescuento();

})


/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/

$(".formularioVenta").on("change", "input.nuevaCantidadProducto", function () {

	var precio = $(this).parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var precioFinal = $(this).val() * precio.attr("precioReal");

	precio.val(precioFinal);


	var nuevoStock = Number($(this).attr("stock")) - $(this).val();

	$(this).attr("nuevoStock", nuevoStock);

	if (Number($(this).val()) > Number($(this).attr("stock"))) {

		$(this).val(0);

		swal({
			title: "La cantidad supera el Stock",
			text: "¡Solo hay " + $(this).attr("stock") + " unidades!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});

		//$("#nuevaCantidadProducto").val(2);

	}

	//Sumar total de precios
	sumarTotalPrecios()

	//Sumar total de impuestos
	sumarTotalImpuestos()

	//Agregar impuesto
	aplicarDescuento()

	//Agrupar productos en formato Json
	listarProductos()

})


/*=============================================
SUMAR TODOS LOS PRECIOS
=============================================*/

function sumarTotalPrecios() {

	var precioItem = $(".nuevoPrecioProducto");
	var arraySumaPrecio = [];

	for (var i = 0; i < precioItem.length; i++) {

		arraySumaPrecio.push(Number($(precioItem[i]).val()));
	}

	function sumaArrayPrecios(total, numero) {
		return total + numero;
	}

	var sumaTotalPrecio = arraySumaPrecio.reduce(sumaArrayPrecios);

	// Actualizar subtotal (total de productos antes de descuentos e impuestos)
	$("#nuevoSubtotalVenta").val(sumaTotalPrecio);
	$("#nuevoTotalVenta").val(sumaTotalPrecio);
	$("#totalVenta").val(sumaTotalPrecio);
	$("#nuevoTotalVenta").attr("total", sumaTotalPrecio);
}

/*=============================================
SUMAR TODOS LOS IMPUESTOS
=============================================*/
/*=============================================
SUMAR TODOS LOS IMPUESTOS
=============================================*/
function sumarTotalImpuestos() {

	var impuestoItem = $(".nuevoImpuestoProducto");
	var precioItem = $(".nuevoPrecioProducto");
	var sumaImpuestosGeneral = 0;
	var sumaImpuestosINC = 0;

	for (var i = 0; i < impuestoItem.length; i++) {

		var porcentaje = Number($(impuestoItem[i]).attr("porcentaje"));
		var totalProducto = Number($(precioItem[i]).val());
		var impuestoNombre = $(impuestoItem[i]).attr("impuestoNombre");

		// Formula: Impuesto = Total - (Total / (1 + Porcentaje/100))
		if (porcentaje > 0) {
			var base = totalProducto / (1 + (porcentaje / 100));
			var impuesto = totalProducto - base;

			// Clasificar impuesto
			if (impuestoNombre && impuestoNombre.toUpperCase().includes("INC")) {
				sumaImpuestosINC += impuesto;
			} else {
				sumaImpuestosGeneral += impuesto;
			}
		}
	}

	// Mostrar totales en los campos visibles
	$("#nuevoImpuestoVenta").val(sumaImpuestosGeneral);
	$("#nuevoImpuestoINCVenta").val(sumaImpuestosINC);

	// Total combinado para el backend
	var sumaTotalImpuestos = sumaImpuestosGeneral + sumaImpuestosINC;
	$("#nuevoPrecioImpuesto").val(sumaTotalImpuestos);

	// Obtener el total con impuestos (suma de productos)
	var totalConImpuestos = Number($("#nuevoTotalVenta").attr("total")) || 0;
	var valorBrutoSinImpuestos = totalConImpuestos - sumaTotalImpuestos;

	// Actualizar el Valor Bruto (precio neto sin impuestos)
	$("#nuevoValorBruto").val(valorBrutoSinImpuestos);
	$("#nuevoPrecioNeto").val(valorBrutoSinImpuestos);

	// Calcular Subtotal basado en si hay descuento o no
	calcularSubtotal();

	// Formatear los campos
	$("#nuevoImpuestoVenta").number(true, 2);
	$("#nuevoImpuestoINCVenta").number(true, 2);
	$("#nuevoValorBruto").number(true, 2);
	$("#nuevoSubtotalVenta").number(true, 2);

	// Actualizar retenciones si la función existe
	if (typeof actualizarVisualizacionRetenciones === 'function') {
		actualizarVisualizacionRetenciones();
	}
}

/*=============================================
FUNCION CALCULAR SUBTOTAL
=============================================*/

function calcularSubtotal() {

	var tipoDescuento = $("#tipoDescuento").val();
	var valorDescuento = Number($("#valorDescuento").val());
	var totalOriginal = Number($("#nuevoTotalVenta").attr("total")) || 0;

	var subtotalFinal = 0;
	var impuestosGeneralFinal = 0;
	var impuestosINCFinal = 0;

	var precioItem = $(".nuevoPrecioProducto");
	var impuestoItem = $(".nuevoImpuestoProducto");

	// Si no hay productos, resetear y salir
	if (precioItem.length === 0) {
		$("#nuevoSubtotalVenta").val(0);
		$("#nuevoImpuestoVenta").val(0);
		$("#nuevoImpuestoINCVenta").val(0);
		$("#nuevoPrecioImpuesto").val(0);
		return;
	}

	// Iterar productos para calcular prorrateo
	for (var i = 0; i < precioItem.length; i++) {

		var precioConImpuesto = Number($(precioItem[i]).val());
		var porcentajeImpuesto = Number($(impuestoItem[i]).attr("porcentaje")) || 0;
		var impuestoNombre = $(impuestoItem[i]).attr("impuestoNombre");

		var descuentoItem = 0;

		// Calcular descuento proporcional para este item
		if (tipoDescuento === "porcentaje") {
			descuentoItem = precioConImpuesto * (valorDescuento / 100);
		} else if (tipoDescuento === "fijo" && totalOriginal > 0) {
			// Prorratear descuento fijo basado en el peso del producto sobre el total
			descuentoItem = valorDescuento * (precioConImpuesto / totalOriginal);
		}

		var precioConDescuento = precioConImpuesto - descuentoItem;

		// Fórmula solicitada: Subtotal = (Precio con IVA - Descuento) / (1 + Tasa IVA)
		var baseItem = precioConDescuento / (1 + (porcentajeImpuesto / 100));

		// Impuesto del item = Precio final - Base
		var impuestoItemCalc = precioConDescuento - baseItem;

		subtotalFinal += baseItem;

		// Clasificar impuesto
		if (impuestoNombre && impuestoNombre.toUpperCase().includes("INC")) {
			impuestosINCFinal += impuestoItemCalc;
		} else {
			impuestosGeneralFinal += impuestoItemCalc;
		}
	}

	// Actualizar campos
	$("#nuevoSubtotalVenta").val(subtotalFinal);
	$("#nuevoImpuestoVenta").val(impuestosGeneralFinal);
	$("#nuevoImpuestoINCVenta").val(impuestosINCFinal);

	// Actualizar campos hidden para backend (Impuestos Totales)
	$("#nuevoPrecioImpuesto").val(impuestosGeneralFinal + impuestosINCFinal);

	// Formatear
	$("#nuevoSubtotalVenta").number(true, 2);
	$("#nuevoImpuestoVenta").number(true, 2);
	$("#nuevoImpuestoINCVenta").number(true, 2);

	// Actualizar retenciones si la función existe
	if (typeof actualizarVisualizacionRetenciones === 'function') {
		actualizarVisualizacionRetenciones();
	}
}

/*=============================================
FUNCION APLICAR DESCUENTO
=============================================*/

function aplicarDescuento() {

	var tipoDescuento = $("#tipoDescuento").val();
	var valorDescuento = Number($("#valorDescuento").val());
	var precioTotal = $("#nuevoTotalVenta").attr("total"); // Total sin descuento
	var montoDescuento = 0;
	var totalConDescuento = Number(precioTotal);

	// Solo aplicar descuento si hay un tipo de descuento activo
	if (tipoDescuento === "porcentaje") {
		// Calcular descuento por porcentaje
		montoDescuento = Number(precioTotal * valorDescuento / 100);
		totalConDescuento = Number(precioTotal) - montoDescuento;

		// Guardar el monto del descuento
		$("#montoDescuento").val(montoDescuento);

		// Actualizar el total con descuento
		$("#nuevoTotalVenta").val(totalConDescuento);
		$("#totalVenta").val(totalConDescuento);

		// Aplicar impuesto sobre el total con descuento
		agregarImpuesto();

		// Recalcular el subtotal con descuento
		calcularSubtotal();

	} else if (tipoDescuento === "fijo") {
		// Aplicar descuento fijo
		montoDescuento = valorDescuento;
		totalConDescuento = Number(precioTotal) - montoDescuento;

		// Validar que el descuento no sea mayor al total
		if (totalConDescuento < 0) {
			totalConDescuento = 0;
			montoDescuento = precioTotal;
		}

		// Guardar el monto del descuento
		$("#montoDescuento").val(montoDescuento);

		// Actualizar el total con descuento
		$("#nuevoTotalVenta").val(totalConDescuento);
		$("#totalVenta").val(totalConDescuento);

		// Aplicar impuesto sobre el total con descuento
		agregarImpuesto();

		// Recalcular el subtotal con descuento
		calcularSubtotal();
	} else {
		// No hay descuento activo, solo asegurar que el total esté sincronizado
		// El total ya fue calculado por sumarTotalPrecios()
		$("#montoDescuento").val(0);

		// Recalcular el subtotal sin descuento (igual a valor bruto)
		calcularSubtotal();
	}
}


/*=============================================
FUNCION AGREGAR IMPUESTO
=============================================*/

function agregarImpuesto() {

	// Esta función mantiene los valores sincronizados cuando hay descuentos
	// Los impuestos ya fueron calculados por sumarTotalImpuestos()

	var precioTotal;

	// Si hay descuento activo, usar el valor actual del campo (que ya tiene el descuento aplicado)
	// Si no hay descuento, usar el atributo total (subtotal original)

	if ($("#tipoDescuento").val() !== "") {
		precioTotal = $("#nuevoTotalVenta").val();

	} else {
		precioTotal = $("#nuevoTotalVenta").attr("total");
	}

	// Si no hay valor, intentar obtener del subtotal
	if (!precioTotal || precioTotal == 0) {
		precioTotal = $("#nuevoSubtotalVenta").val() || 0;
	}

	// Mantener los campos hidden sincronizados
	$("#totalVenta").val(precioTotal);

	// Los impuestos ya están calculados en sumarTotalImpuestos()
	// Solo mantenemos el campo hidden sincronizado
	var impuestosCalculados = $("#nuevoImpuestoVenta").val() || 0;
	$("#nuevoPrecioImpuesto").val(impuestosCalculados);

	// El precio neto es el total menos los impuestos
	var precioNeto = Number(precioTotal) - Number(impuestosCalculados);
	$("#nuevoPrecioNeto").val(precioNeto);

	// Actualizar retenciones si la función existe
	if (typeof actualizarVisualizacionRetenciones === 'function') {
		actualizarVisualizacionRetenciones();
	}
}



/*=============================================
CUANDO CAMBIA EL IMPUESTO
=============================================*/

$("#nuevoImpuestoVenta").change(function () {

	// Si ya existe un descuento, asegurarse de aplicarlo
	if ($("#tipoDescuento").val() !== "") {
		aplicarDescuento();
	} else {
		agregarImpuesto();
	}
})


//Poner formato number al precio final
$("#nuevoTotalVenta").number(true, 0);

//Poner formato number al subtotal
$("#nuevoSubtotalVenta").number(true, 0);


/*=============================================
SELECCIONAR METODO DE PAGO
=============================================*/

$("#nuevoMetodoPago").change(function () {

	var metodo = $(this).val();

	//Hecho por mi else if
	if (metodo == "") {

		$(this).parent().parent().removeClass("col-xs-4");

		$(this).parent().parent().addClass("col-xs-6");

		$(this).parent().parent().parent().children(".cajasMetodoPago").html("")

	}

	else {

		$(this).parent().parent().removeClass("col-xs-4");

		$(this).parent().parent().addClass("col-xs-6");

		$(this).parent().parent().parent().children(".cajasMetodoPago").html(

			'<div class="col-xs-6" style="padding-left:0px">' +

			'<div class="input-group">' +

			'<input type="text" class="form-control" id="nuevoCodigoTransaccion" name="nuevoCodigoTransaccion" placeholder="Ingrese el valor o código de transacción">' +

			'<span class="input-group-addon"><i class="fa fa-lock"></i></span>' +

			'</div>' +

			'</div>'
		)

		// Llamar listarMetodos() inmediatamente para que no quede vacío si el código es opcional
		listarMetodos()

	}

})


/*=============================================
CAMBIO EN EFECTIVO
=============================================*/

$(".formularioVenta").on("change", "input#nuevoValorEfectivo", function () {

	var efectivo = $(this).val();

	var cambio = Number(efectivo) - Number($('#nuevoTotalVenta').val());

	var nuevoCambioEfectivo = $(this).parent().parent().parent().children('#capturarCambioEfectivo').children().children('#nuevoCambioEfectivo');

	nuevoCambioEfectivo.val(cambio);

})


/*=============================================
CAMBIO TRANSACCION
=============================================*/

$(".formularioVenta").on("change", "input#nuevoCodigoTransaccion", function () {

	//Listar metodo en la entrada
	listarMetodos()

})


/*=============================================
AGRUPAR TODOS LOS PRODUCTOS
=============================================*/

function listarProductos() {

	var listaProductos = [];

	// Buscamos todos los elementos de descripción (uno por fila)
	var descripciones = $(".nuevaDescripcionProducto");

	descripciones.each(function() {

		var inputDesc = $(this);
		var row = inputDesc.closest(".row");

		// Si no está en una fila (caso raro), buscamos en el padre inmediato
		if (row.length == 0) {
			row = inputDesc.parent().parent();
		}

		var idProducto = "";

		// Detectar si el campo es un <select> (crear) o un <input> (editar/orden)
		if (inputDesc.is("select") || inputDesc.prop("tagName").toLowerCase() === "select") {
			idProducto = inputDesc.find("option:selected").attr("idProducto");
		} else {
			idProducto = inputDesc.attr("idProducto");
		}

		// VALIDACIÓN ESTRICTA: Solo permitir IDs numéricos válidos
		if (!idProducto || idProducto == "" || idProducto == "0" || idProducto == "undefined" || isNaN(idProducto)) {
			return; // Siguiente iteración de each
		}

		// Buscar elementos hermanos en la misma fila (Más robusto)
		var inputCant = row.find(".nuevaCantidadProducto");
		var inputPrecio = row.find(".nuevoPrecioProducto");
		var inputImpuesto = row.find(".nuevoImpuestoProducto");

		// Obtener metadatos de variante directamente de los atributos del input (Método más robusto)
		var esVariante = inputDesc.attr("esVariante") || "0";
		var idVariante = inputDesc.attr("idVariante");
		var skuVariante = inputDesc.attr("skuVariante");

		var producto = {
			"id": idProducto,
			"descripcion": inputDesc.val(),
			"cantidad": inputCant.val() || 1,
			"stock": inputCant.attr("nuevoStock") || 0,
			"precio": inputPrecio.attr("precioReal") || 0,
			"total": inputPrecio.val() || 0,
			"impuesto": inputImpuesto.attr("porcentaje") || 0
		};

		// Si es variante, agregar los campos adicionales (Validación estricta)
		if (esVariante == "1" && idVariante && idVariante != "" && idVariante != "undefined") {
			producto.esVariante = "1";
			producto.idVariante = idVariante;
			producto.skuVariante = skuVariante;
		} else if (esVariante == "1") {
			// Si dice que es variante pero no tiene ID, intentar buscarlo en la descripción como último recurso
			// o simplemente loguear el error para depuración
			console.warn("Se detectó variante sin ID:", inputDesc.val());
		}

		listaProductos.push(producto);
	});

	$("#listaProductos").val(JSON.stringify(listaProductos));
}

$(document).on("submit", ".formularioVenta", function (e) {

	// VALIDACIÓN INICIAL: Verificar si la lista de productos está vacía
	listarProductos();
	var listaProductos = $("#listaProductos").val();

	if (!listaProductos || listaProductos.trim() == "" || listaProductos.trim() == "[]") {

		e.preventDefault();
		e.stopImmediatePropagation();

		swal({
			title: "No se puede guardar la venta",
			text: "Debes seleccionar al menos un producto válido antes de guardar.",
			type: "error",
			confirmButtonText: "¡Entendido!"
		});

		return false;
	}

	var form = this;

	// Si el formulario ya ha sido confirmado, permitimos el envío
	if ($(form).data('confirmado')) {
		return true;
	}

	e.preventDefault();

	// Primero listamos productos y métodos para asegurar que los campos ocultos estén listos
	listarProductos();
	listarMetodos();

	swal({
		title: '¿Está seguro de guardar este documento?',
		text: "Se guardará en el sistema y podrá enviarla a la DIAN después.",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, guardar'
	}).then((result) => {
		if (result.value) {
			$(form).data('confirmado', true);

			swal({
				title: 'Guardando Venta',
				text: 'Por favor espere mientras se procesa la información...',
				type: 'info',
				allowOutsideClick: false,
				showConfirmButton: false,
				onBeforeOpen: () => {
					swal.showLoading()
				}
			});

			// Enviar por AJAX
			var datos = new FormData(form);
			datos.append("ajax", true);

			$.ajax({
				url: "ajax/ventas.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				dataType: "json",
				success: function (respuesta) {
					if (respuesta.status == "success") {

						localStorage.removeItem("rango");

						swal({
							type: "success",
							title: respuesta.titulo,
							text: respuesta.mensaje,
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location = respuesta.ruta;
							}
						});
					} else {
						swal({
							type: "error",
							title: respuesta.titulo || "Error",
							html: respuesta.mensaje || "Ocurrió un error al guardar",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.error("Error AJAX Status:", jqXHR.status);
					console.error("Error AJAX Status Text:", jqXHR.statusText);
					console.error("Error AJAX Response:", jqXHR.responseText);
					console.error("Error Thrown:", errorThrown);

					swal({
						type: "error",
						title: "Error de Sistema",
						html: "No se pudo guardar la venta vía AJAX.<br><br><b>Status:</b> " + jqXHR.status + " " + jqXHR.statusText + "<br><b>Error:</b> " + errorThrown + "<br><br>Revisa la consola para más detalles."
					});
				}
			});
		}
	});
});


/*=============================================
LISTAR METODO DE PAGO
=============================================*/

function listarMetodos() {

	var metodo = $("#nuevoMetodoPago").val();

	var transaccion = $("#nuevoCodigoTransaccion").val();
	if (transaccion && transaccion.trim() !== "") {
		$("#listaMetodoPago").val(metodo + "-" + transaccion);
	} else {
		$("#listaMetodoPago").val(metodo);
	}

}


/*=============================================
BOTON DETALLE VENTA
=============================================*/

$(document).on("click", ".btnDetalleVenta", function () {

	var idVenta = $(this).attr("idVenta");

	window.location = "index.php?ruta=detalle-venta&idVenta=" + idVenta;
})


/*=============================================
BORRAR VENTA
=============================================*/

$(document).on("click", ".btnEliminarVenta", function () {

	var idVenta = $(this).attr("idVenta");

	// Primero intentar con el parámetro 'ruta'
	let ruta = new URLSearchParams(window.location.search).get('ruta');

	// Si no existe 'ruta', obtener el nombre del archivo
	if (!ruta) {
		const path = window.location.pathname;
		const archivo = path.substring(path.lastIndexOf("/") + 1);
		ruta = archivo.split(".php")[0]; // ejemplo: ordenes.php -> ordenes
	}

	var titulo = '¿Está seguro de anular esta venta?';
	var texto = 'Al anular la venta los productos regresarán al stock y ya no sumará en los totales ni reportes de ingresos.';
	var confirmText = 'Sí, anular documento';

	if (ruta === "ordenes") {
		titulo = '¿Está seguro de anular esta orden?';
		texto = 'Al anular la orden esta ya no sumará en los totales.';
		confirmText = 'Sí, anular orden';
	}

	swal({
		title: titulo,
		text: texto,
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: confirmText
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idVentaEliminar", idVenta);
			// csrf_token removido - manejado por csrf-helper.js

			if (ruta === "ordenes") {
				datos.append("estado", "orden");
			}

			$.ajax({
				url: "ajax/ventas.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							type: "success",
							title: ruta === "ordenes" ? "¡Orden anulada correctamente!" : "¡Venta anulada correctamente!",
							text: ruta === "ordenes" ? "La orden ha sido anulada exitosamente del sistema." : "El documento ha sido anulado exitosamente del sistema.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								if (ruta === "ordenes") {
									window.location = "ordenes";
								} else {
									window.location.reload();
								}
							}
						});
					} else {
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo eliminar. " + respuesta,
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					}
				}
			})
		}

	})


})


/*=============================================
IMPRIMIR FACTURA
=============================================*/
/*
$(".tablas").on("click", ".btnImprimirFactura", function(){

	var codigoVenta = $(this).attr("codigoVenta");

	window.open("extensiones/tcpdf/pdf/factura.php?codigo="+codigoVenta, "_blank");
})
	*/

$(document).off("click", ".btnImprimirFactura").on("click", ".btnImprimirFactura", function () {

	var codigoVenta = $(this).attr("codigoVenta");

	window.open("extensiones/tcpdf/pdf/factura.php?codigo=" + codigoVenta, "_blank");
});



/*=============================================
RANGO DE FECHAS - inicializado dentro de document.ready (ver inicio del archivo)
=============================================*/

/*=============================================
CANCELAR RANGO DE FECHAS
=============================================*/

/*$(".daterangepicker.opensleft .range_inputs .cancelBtn").on("click", function(){

	localStorage.removeItem("capturarRango");
	localStorage.clear();
	//window.location = "ventas";
	window.location = "index.php?ruta=" + getRutaActual();
})*/

$(".daterangepicker.opensright .range_inputs .cancelBtn").on("click", function () {
	// Solo redirigir si estamos en la página de ventas
	var rutaActual = window.location.href;
	if (rutaActual.indexOf("ruta=ventas") !== -1) {
		localStorage.removeItem("capturarRango");
		localStorage.clear();
		window.location = "ventas";
	}
})


/*=============================================
CAPTURAR HOY este bloque no sirvio
=============================================*/

/*$(".daterangepicker.opensleft .ranges li").on("click", function(){

	var textoHoy = $(this).attr("data-range-key");

	if(textoHoy == "Hoy"){

		var d = new Date();
		
		var dia = d.getDate();
		var mes = d.getMonth()+1;
		var año = d.getFullYear();

		var fechaInicial = año+"-"+mes+"-"+dia;
		var fechaFinal = año+"-"+mes+"-"+dia;	

		localStorage.setItem("capturarRango", "Hoy");

		window.location = "index.php?ruta=ventas&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
	}
})*/


/*=============================================
ABRIR ARCHIVO XML EN NUEVA PESTAÑA
=============================================*/

$(".abrirXML").click(function () {

	var archivo = $(this).attr("archivo");
	window.open(archivo, "_blank");

})


/*HPM Boton de cancelar en ventas
$(".btnCancelarVenta").click(function(){

	window.location = "index.php?ruta=ventas";
}*/


//FILTRO DE VENTAS
/*
let minDate, maxDate; 
// Custom filtering function which will search data in column four between two values
DataTable.ext.search.push(function (settings, data, dataIndex) {
	// Solo aplicar este filtro a las tablas de ventas u ordenes
	if (!$(settings.nTable).hasClass('tablaVentas') && !$(settings.nTable).hasClass('tablaOrdenes')) {
		return true;
	}
	let min = minDate.val();
	let max = maxDate.val();
	let date = new Date(data[4]);
 
	if (
		(min === null && max === null) ||
		(min === null && date <= max) ||
		(min <= date && max === null) ||
		(min <= date && date <= max)
	) {
		return true;
	}
	return false;
});
 
// Create date inputs
minDate = new DateTime('#min', {
	format: 'MMMM Do YYYY'
});
maxDate = new DateTime('#max', {
	format: 'MMMM Do YYYY'
});
 
// DataTables initialisation
// let table = new DataTable('#example');
 
// Refilter the table
document.querySelectorAll('#min, #max').forEach((el) => {
	el.addEventListener('change', () => table.draw());
});
*/


/*=============================================
FIRMAR FACTURA ELECTRÓNICA
=============================================*/
$(document).on("click", ".btnFirmarFactura", function () {

	var idVenta = $(this).attr("idVenta");
	var boton = $(this);

	swal({
		title: '¿Está seguro de firmar y emitir esta Factura Electrónica?',
		text: 'Este proceso enviará el documento a la DIAN y no se podrá revertir.',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, firmar documento'
	}).then((result) => {

		if (result.value) {

			// Loading state
			boton.prop('disabled', true);
			var htmlOriginal = boton.html();
			boton.html('<i class="fa fa-spinner fa-spin"></i>');

			swal({
				title: 'Firmando Factura Electrónica',
				text: 'Por favor espere mientras se procesa la información...',
				type: 'info',
				allowOutsideClick: false,
				showConfirmButton: false,
				onBeforeOpen: () => {
					swal.showLoading()
				}
			});

			var datos = new FormData();
			datos.append("accion", "generarFactura");
			datos.append("idVenta", idVenta);
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/factus.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				dataType: "json",
				success: function (respuesta) {

					if (!respuesta.error) {

						swal({
							type: "success",
							title: "¡Factura Electrónica firmada y enviada correctamente!",
							text: "El documento ha sido procesado por la DIAN exitosamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function (result) {
							if (result.value) {
								window.location.reload();
							}
						});

					} else {

						var mensajeError = respuesta.mensaje;
						if (respuesta.errores && respuesta.errores.length > 0) {
							mensajeError += ": " + respuesta.errores.join(", ");
						}

						swal({
							type: "error",
							title: "Error al firmar",
							text: mensajeError,
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});

						boton.prop('disabled', false);
						boton.html(htmlOriginal);
					}

				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.error("=== AJAX ERROR ===");
					console.error("Status:", textStatus);
					console.error("Error:", errorThrown);
					console.error("Response Text:", jqXHR.responseText);
					console.error("Status Code:", jqXHR.status);

					var mensajeDetallado = "Error: " + textStatus;
					if (jqXHR.responseText) {
						mensajeDetallado += "\nRespuesta: " + jqXHR.responseText.substring(0, 200);
					}

					swal({
						type: "error",
						title: "Error de comunicación",
						text: mensajeDetallado,
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});

					boton.prop('disabled', false);
					boton.html(htmlOriginal);
				}
			});

		}

	})

});

/*=============================================
MANEJAR CAMBIO DE TIPO DE RETENCION
=============================================*/
$('#nuevoTipoRetencionNuevo').change(function () {
	var tipo = $(this).val();
	var selectPorcentaje = $('#nuevoPorcentajeRetencionNuevo');

	// Limpiar opciones actuales
	selectPorcentaje.html('<option value="">Seleccionar porcentaje</option>');

	if (tipo === 'ReteIVA') {
		// Opciones para ReteIVA
		selectPorcentaje.append('<option value="15.00">15.00%</option>');
		selectPorcentaje.append('<option value="100.00">100.00%</option>');
	} else if (tipo === 'ReteRenta') {
		// Opciones para ReteRenta
		var porcentajes = ['0.10', '0.50', '1.00', '1.50', '2.00', '2.50', '3.00', '3.50', '4.00', '6.00', '7.00', '10.00', '11.00', '20.00'];
		porcentajes.forEach(function (porcentaje) {
			selectPorcentaje.append('<option value="' + porcentaje + '">' + porcentaje + '%</option>');
		});
	}
});

/*=============================================
FIX PARA MODAL DE RETENCION AL CERRAR
=============================================*/
$('#modalAgregarRetencionNuevo').on('hidden.bs.modal', function () {
	$('body').removeClass('modal-open');
	$('body').css('padding-right', '0');
	$('.modal-backdrop').remove();
});

/*=============================================
GUARDAR RETENCION
=============================================*/
var retencionesAplicadas = [];

$('#guardarRetencionNuevo').click(function () {
	var tipoRetencion = $('#nuevoTipoRetencionNuevo').val();
	var porcentajeRetencion = $('#nuevoPorcentajeRetencionNuevo').val();

	if (!tipoRetencion || !porcentajeRetencion) {
		swal({
			type: "error",
			title: "Debe seleccionar el tipo y porcentaje de retención",
			showConfirmButton: true,
			confirmButtonText: "Cerrar"
		});
		return;
	}

	var montoRetencion = 0;
	var baseCalculo = 0;

	if (tipoRetencion === 'ReteIVA') {
		// Para ReteIVA, se calcula sobre el valor del campo "Impuestos IVA"
		var impuestoIVA = parseFloat($('#nuevoImpuestoVenta').val()) || 0;
		baseCalculo = impuestoIVA;
		montoRetencion = (baseCalculo * parseFloat(porcentajeRetencion)) / 100;
	} else if (tipoRetencion === 'ReteRenta') {
		// Para ReteRenta, se calcula sobre el subtotal
		var subtotal = parseFloat($('#nuevoSubtotalVenta').val()) || 0;
		baseCalculo = subtotal;
		montoRetencion = (baseCalculo * parseFloat(porcentajeRetencion)) / 100;
	}

	// Agregar a la lista de retenciones
	var retencion = {
		tipo: tipoRetencion,
		porcentaje: porcentajeRetencion,
		base: baseCalculo,
		monto: montoRetencion
	};

	retencionesAplicadas.push(retencion);

	// Actualizar la visualización
	actualizarVisualizacionRetenciones();

	// Limpiar el modal
	$('#nuevoTipoRetencionNuevo').val('');
	$('#nuevoPorcentajeRetencionNuevo').html('<option value="">Seleccionar porcentaje</option>');
});

/*=============================================
ACTUALIZAR VISUALIZACIÓN DE RETENCIONES
=============================================*/
function actualizarVisualizacionRetenciones() {
	var html = '';
	var totalRetenciones = 0;

	// Obtener bases actuales para recalculo dinámico
	var impuestoIVA = parseFloat($('#nuevoImpuestoVenta').val()) || 0;

	// La base para ReteRenta debe ser el Subtotal - Descuentos (Valor Neto antes de IVA)
	// Usamos nuevoPrecioNeto si existe, sino calculamos desde Total - Impuestos
	var precioNeto = parseFloat($('#nuevoPrecioNeto').val());
	if (isNaN(precioNeto) || precioNeto === 0) {
		var totalVenta = parseFloat($('#nuevoTotalVenta').val()) || 0;
		precioNeto = totalVenta - impuestoIVA;
	}

	if (retencionesAplicadas.length > 0) {
		html += '<table class="table table-condensed">';
		html += '<thead><tr><th>Tipo</th><th>Porcentaje</th><th>Base</th><th>Monto</th><th>Acción</th></tr></thead>';
		html += '<tbody>';

		retencionesAplicadas.forEach(function (ret, index) {

			// 🟢 RECALCULAR VALORES DINÁMICAMENTE
			if (ret.tipo === 'ReteIVA') {
				ret.base = impuestoIVA;
				ret.monto = (ret.base * parseFloat(ret.porcentaje)) / 100;
			} else if (ret.tipo === 'ReteRenta') {
				ret.base = precioNeto;
				ret.monto = (ret.base * parseFloat(ret.porcentaje)) / 100;
			}

			html += '<tr>';
			html += '<td>' + ret.tipo + '</td>';
			html += '<td>' + ret.porcentaje + '%</td>';
			html += '<td>$' + Number(ret.base).toFixed(2) + '</td>';
			html += '<td><strong>$' + Number(ret.monto).toFixed(2) + '</strong></td>';
			html += '<td><button type="button" class="btn btn-danger btn-xs eliminarRetencion" data-index="' + index + '"><i class="fa fa-trash"></i></button></td>';
			html += '</tr>';
			totalRetenciones += ret.monto;
		});

		html += '<tr class="info"><td colspan="3"><strong style="color: #333;">Total Retenciones</strong></td><td colspan="2"><strong style="color: #333;">$' + Number(totalRetenciones).toFixed(2) + '</strong></td></tr>';

		// Calcular Total Neto a Pagar (Total Venta - Total Retenciones)
		var totalVenta = parseFloat($('#nuevoTotalVenta').val()) || 0;
		var totalPagar = totalVenta - totalRetenciones;
		html += '<tr class="success"><td colspan="3"><strong style="color: #333;">Total a Pagar</strong></td><td colspan="2"><strong style="color: #333;">$' + Number(totalPagar).toFixed(2) + '</strong></td></tr>';

		html += '</tbody></table>';

		$('#listaRetenciones').html(html);
		$('#seccionRetenciones').show();

		// Guardar en campo oculto para enviar al backend (con valores actualizados)
		$('#datosRetenciones').val(JSON.stringify(retencionesAplicadas));
	} else {
		$('#seccionRetenciones').hide();
		$('#datosRetenciones').val('');
	}
}

/*=============================================
ELIMINAR RETENCION
=============================================*/
$(document).on('click', '.eliminarRetencion', function () {
	var index = $(this).data('index');
	retencionesAplicadas.splice(index, 1);
	actualizarVisualizacionRetenciones();
});

/*=============================================
EDITAR CLIENTE DESDE VENTAS Y ORDENES
=============================================*/
$(document).on("click", ".btnEditarCliente, .btnVerClienteDesdeVenta", function () {

	var idCliente = $(this).attr("idCliente");

	var datos = new FormData();
	datos.append("idCliente", idCliente);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url: "ajax/clientes.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			$("#idCliente").val(respuesta["id"]);
			$("#editarCliente").val(respuesta["nombre"]);
			$("#editarDocumentoId").val(respuesta["documento"]);
			$("#editarEmail").val(respuesta["email"]);
			$("#editarTelefono").val(respuesta["telefono"]);
			$("#editarDireccion").val(respuesta["direccion"]);
			$("#editarFechaNacimiento").val(respuesta["fecha_nacimiento"]);

			// Campos redesign
			$("#editarCiudad").val(respuesta["ciudad"]); // Municipio
			$("#editarNota").val(respuesta["notas"]);    // Notas
			$("#editarEstado").val(respuesta["estatus"]); // Estado

			// Campos Facturacion Electronica
			$("#editarTipoDocumento").val(respuesta["tipo_documento_id"]);
			$("#editarMunicipio").val(respuesta["municipio_id"]);
			$("#editarDigitoVerificacion").val(respuesta["digito_verificacion"]);
			$("#editarTipoPersona").val(respuesta["tipo_persona"]);
			$("#editarRegimenTributario").val(respuesta["regimen_tributario"]);
			$("#editarResponsabilidades").val(respuesta["responsabilidades_fiscales"]);
			$("#editarCodigoPostal").val(respuesta["codigo_postal"]);
			$("#editarNombreComercial").val(respuesta["nombre_comercial"]);
			$("#editarRazonSocial").val(respuesta["razon_social"]);
		}

	})

})

/*=============================================
ABRIR MODAL ENVIAR EMAIL
=============================================*/
$(document).on("click", ".btnEnviarEmail", function () {
	var idVenta = $(this).attr("idVenta");
	var nombreCliente = $(this).attr("nombreCliente");
	var emailCliente = $(this).attr("emailCliente");

	$("#emailIdVenta").val(idVenta);
	$("#emailNombreCliente").val(nombreCliente);
	$("#emailDestino").val(emailCliente);

	$("#modalEnviarEmail").fadeIn();
});

/*=============================================
ENVIAR EMAIL POR AJAX
=============================================*/
$("#formEnviarEmail").submit(function (e) {
	e.preventDefault();

	var idVenta = $("#emailIdVenta").val();
	var emailDestino = $("#emailDestino").val();

	swal({
		title: "Enviando Correo...",
		text: "Por favor espere mientras se genera el PDF y se envía el correo.",
		allowOutsideClick: false,
		onBeforeOpen: () => {
			swal.showLoading();
		}
	});

	var datos = new FormData();
	datos.append("idVenta", idVenta);
	datos.append("emailDestino", emailDestino);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({
		url: "ajax/facturacion.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			if (respuesta.status == "success") {
				swal({
					type: "success",
					title: "¡Enviado!",
					text: respuesta.mensaje,
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				}).then(function (result) {
					if (result.value) {
						$("#modalEnviarEmail").fadeOut();
					}
				});
			} else {
				swal({
					type: "error",
					title: "Error",
					text: respuesta.mensaje,
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				});
			}
		},
		error: function (xhr, status, error) {
			console.error("AJAX Error:", error);
			swal({
				type: "error",
				title: "Error de comunicación",
				text: "No se pudo conectar con el servidor para enviar el correo.",
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			});
		}
	});
});

/*=============================================
ADMINISTRAR VENTAS - LISTADO SERVER-SIDE
=============================================*/
$(document).ready(function () {
	if ($("#tablaListaVentas").length > 0) {

		if ($.fn.DataTable.isDataTable('#tablaListaVentas')) {
			$('#tablaListaVentas').DataTable().destroy();
		}

		var table = $("#tablaListaVentas").DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax/ventas-listado.ajax.php",
				"type": "POST",
				"data": function (d) {
					d.csrf_token = $('meta[name="csrf-token"]').attr('content');
					d.fechaInicial = $("#fechaInicial").val();
					d.fechaFinal = $("#fechaFinal").val();
					d.clienteId = $("select[name='cliente']").val();
					d.usuarioId = $("select[name='usuario']").val();
					d.bodegaId = $("select[name='bodega']").val();
				}
			},
			"createdRow": function (row, data, dataIndex) {
				if (data.DT_RowAttr && data.DT_RowAttr['data-venta-id']) {
					$(row).attr('data-venta-id', data.DT_RowAttr['data-venta-id']);
				}
			},
			"initComplete": function (settings, json) {
				$(this.api().table().node()).addClass('datatable-ready');
				if (typeof quitarLoaderGlobal === 'function') {
					quitarLoaderGlobal();
				}

				window.recargarTablaVentas = function () {
					table.ajax.reload();
				};

				$("select[name='cliente'], select[name='usuario'], select[name='bodega']").on("change", function () {
					window.recargarTablaVentas();
				});

				$("#fechaInicial, #fechaFinal").on("change", function () {
					window.recargarTablaVentas();
				});

				$(".btnBuscarFiltros").on("click", function () {
					window.recargarTablaVentas();
				});

				if ($('#daterange-btn').length > 0 && typeof $.fn.daterangepicker !== 'undefined') {
					var urlParams = new URLSearchParams(window.location.search);
					var fechaInicialUrl = urlParams.get('fechaInicial');
					var fechaFinalUrl = urlParams.get('fechaFinal');

					if (fechaInicialUrl && fechaFinalUrl) {
						$('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + moment(fechaInicialUrl).format('MMMM D, YYYY') + ' - ' + moment(fechaFinalUrl).format('MMMM D, YYYY'));
					} else {
						$('#daterange-btn span').html('<i class="fa fa-calendar"></i> Mostrar todas');
					}

					$('#daterange-btn').daterangepicker({
						ranges: {
							'Mostrar todas': [moment('2000-01-01'), moment()],
							'Hoy': [moment(), moment()],
							'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
							'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
							'Este mes': [moment().startOf('month'), moment().endOf('month')],
							'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						},
						startDate: fechaInicialUrl ? moment(fechaInicialUrl) : moment(),
						endDate: fechaFinalUrl ? moment(fechaFinalUrl) : moment()
					}, function (start, end) {
						if (start.format('YYYY-MM-DD') === '2000-01-01') {
							$('#daterange-btn span').html('<i class="fa fa-calendar"></i> Mostrar todas');
							$('#fechaInicial').val('');
							$('#fechaFinal').val('');
						} else {
							$('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
							$('#fechaInicial').val(start.format('YYYY-MM-DD'));
							$('#fechaFinal').val(end.format('YYYY-MM-DD'));
						}
						window.recargarTablaVentas();
					});

					$('#daterange-btn').on('cancel.daterangepicker', function () {
						$(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar todas');
						$('#fechaInicial').val('');
						$('#fechaFinal').val('');
						window.recargarTablaVentas();
					});
				}
			},
			"order": [[7, "desc"]],
			"responsive": {
				"details": {
					"type": "inline",
					"renderer": function (api, rowIdx, columns) {
						var labels = {
							2: 'Vendedor', 3: 'Imagen', 4: 'Total',
							5: 'Notas del cliente', 6: 'Observación', 7: 'Fecha'
						};
						var idVenta = $(api.row(rowIdx).node()).attr('data-venta-id') || '';
						var finalHtml = '';
						var hasHidden = false;

						$.each(columns, function (i, col) {
							if (!col.hidden) return;
							hasHidden = true;
							var colIdx = col.columnIndex;
							var label = labels[colIdx] || col.title || ('Columna ' + colIdx);
							var data = col.data || '';

							if (colIdx === 6) {
								var obsTexto = $('<div>').html(data).text().trim();
								finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
								finalHtml += '<span class="text-bold" style="block;color:#555;margin-bottom:4px;"> ' + label + ':</span>';
								finalHtml += '<div class="celda-observacion" contenteditable="true" data-id="' + idVenta + '" style="min-height:24px;">' + obsTexto + '</div>';
								finalHtml += '</div>';
								return;
							}

							if (colIdx === 5) {
								var notasTexto = $('<div>').html(data).text().trim();
								finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
								finalHtml += '<span class="text-bold" style="color:#555;"><i class="fa fa-magic"></i> ' + label + ': </span>';
								finalHtml += '<span style="color:#333;">' + (notasTexto || '<em style="color:#999;">Sin notas</em>') + '</span>';
								finalHtml += '</div>';
								return;
							}

							finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
							finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
							finalHtml += '<span style="color:#333;">' + data + '</span>';
							finalHtml += '</div>';
						});

						if (!hasHidden) return false;
						return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
					}
				}
			},
			"columnDefs": [
				{ "targets": 0, "responsivePriority": 1 },
				{ "targets": 8, "responsivePriority": 2, "orderable": false },
				{ "targets": 1, "responsivePriority": 3 },
				{ "targets": 2, "responsivePriority": 4 },
				{ "targets": 3, "responsivePriority": 5 },
				{ "targets": 4, "responsivePriority": 6 },
				{ "targets": 5, "responsivePriority": 7 },
				{ "targets": 6, "responsivePriority": 8 },
				{ "targets": 7, "responsivePriority": 9 }
			],
			"language": {
				"sProcessing": "Procesando...",
				"sLengthMenu": "Mostrar _MENU_ registros",
				"sZeroRecords": "No se encontraron resultados",
				"sEmptyTable": "Ningún dato disponible en esta tabla",
				"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
				"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
				"sSearch": "Buscar:",
				"oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
			}
		});
	}
});

/*=============================================
GUARDAR OBSERVACIONES (VENTAS)
=============================================*/
$(document).on('blur', '.celda-observacion', function () {
	const idVenta = $(this).attr('data-id');
	const nuevaObservacion = $(this).text().trim();

	$.ajax({
		url: "ajax/datatable-ventas.ajax.php",
		method: "POST",
		data: {
			csrf_token: $('meta[name="csrf-token"]').attr('content'),
			idVentaObservacion: idVenta,
			nuevaObservacion: nuevaObservacion
		},
		success: function (respuesta) {
			console.log("Observación guardada:", respuesta);
		}
	});
});

/*=============================================
GESTIÓN DE IMÁGENES DE VENTA
=============================================*/
$(document).on("click", ".img-ampliar-venta, .btnVerFotoVenta", function () {
	var rutaImagen = $(this).attr("data-imagen");
	var idVenta = $(this).attr("data-idventa");

	$("#imagenVentaAmpliada").attr("src", rutaImagen);
	$("#idVentaImagen").val(idVenta);
	$(".nuevaImagenVenta").val("");
	$("#modalAmpliarImagenVenta").fadeIn();
});

$(".nuevaImagenVenta").change(function () {
	var imagen = this.files[0];
	if (imagen) {
		if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
			$(".nuevaImagenVenta").val("");
			swal({ title: "Error", text: "¡La imagen debe ser JPG o PNG!", type: "error" });
		} else if (imagen["size"] > 2000000) {
			$(".nuevaImagenVenta").val("");
			swal({ title: "Error", text: "¡La imagen no debe pesar más de 2MB!", type: "error" });
		} else {
			var datosImagen = new FileReader;
			datosImagen.readAsDataURL(imagen);
			$(datosImagen).on("load", function (event) {
				$("#imagenVentaAmpliada").attr("src", event.target.result);
			});
		}
	}
});

$(document).on("click", ".btnGuardarImagenVenta", function () {
	var idVenta = $("#idVentaImagen").val();
	var imagen = $(".nuevaImagenVenta")[0].files[0];

	if (!imagen) {
		swal({ title: "Advertencia", text: "Seleccione una imagen", type: "warning" });
		return;
	}

	var datos = new FormData();
	datos.append("idVentaImagen", idVenta);
	datos.append("nuevaImagenVenta", imagen);
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

	swal({ title: 'Cargando...', allowOutsideClick: false, onBeforeOpen: () => { swal.showLoading() } });

	$.ajax({
		url: "ajax/ventas.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			if (respuesta == "ok") {
				swal({ type: "success", title: "¡Actualizada!", showConfirmButton: true }).then(() => {
					$("#modalAmpliarImagenVenta").fadeOut();
					if (window.recargarTablaVentas) window.recargarTablaVentas();
					else window.location.reload();
				});
			}
		}
	});
});

/*=============================================
FILTRAR PRODUCTOS EN MÓVIL
=============================================*/
$(document).on("keyup", ".buscarProductoMovil", function() {
	var input = $(this);
	var filtro = input.val().toLowerCase();
	var select = input.parent().find("select.nuevaDescripcionProducto");
	
	if (!select.data("original-options")) {
		var options = select.find("option").clone();
		select.data("original-options", options);
	}
	
	var originalOptions = select.data("original-options");
	var valorSeleccionado = select.val();
	
	select.empty();
	select.append('<option value="">Seleccione el producto</option>');
	
	originalOptions.each(function() {
		var option = $(this);
		var texto = option.text().toLowerCase();
		var valor = option.val();
		
		if (valor === "") return;
		
		if (texto.indexOf(filtro) > -1) {
			select.append(option.clone());
		}
	});
	
	if (select.find("option[value='" + valorSeleccionado + "']").length > 0) {
		select.val(valorSeleccionado);
	} else {
		select.val("");
	}
});
