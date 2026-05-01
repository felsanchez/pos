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
					'Todos los documentos': [moment('2000-01-01'), moment()],
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
				$('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
				$('#fechaInicial').val(start.format('YYYY-MM-DD'));
				$('#fechaFinal').val(end.format('YYYY-MM-DD'));
			}
		);
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
	"processing": true,
	"serverSide": true,
	"ajax": {
		"url": "ajax/datatable-ventas.ajax.php",
		"type": "POST",
		"data": function(d) {
			d.csrf_token = $('meta[name="csrf-token"]').attr('content');
		}
	},
	"columnDefs": [
		{
			"targets": 1, // Imagen
			"render": function(data, type, row) {
				return '<img class="img-thumbnail imgTablaVenta" src="'+row[1]+'" width="40px">';
			}
		},
		{
			"targets": 4, // Stock
			"render": function(data, type, row) {
				var stock = row[4];
				var btnClass = "btn-success";
				if(stock <= 10) btnClass = "btn-danger";
				else if(stock >= 11 && stock <= 15) btnClass = "btn-warning";
				return '<div class="btn-group"><button class="btn ' + btnClass + ' limiteStock">' + stock + '</button></div>';
			}
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

$('.tablaVentas tbody').on('click', 'button.agregarProducto', function () {

	var data = table2.row($(this).parents('tr')).data();

	$(this).attr("idProducto", data[5]);
})





/*=============================================
EXPANDIR VARIANTES EN VENTAS
=============================================*/

// Función para formatear la tabla de variantes en ventas
function formatearTablaVariantesVenta(variantes) {

	if (!variantes || variantes.length === 0) {
		return '<div class="alert alert-info">No hay variantes para este producto</div>';
	}

	// Función auxiliar para formatear precios (formato colombiano: $80.000)
	function formatearPrecio(numero) {
		return Math.round(numero).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
	}

	var html = '<div style="padding: 20px; background-color: #f9f9f9;">';
	html += '<h4><i class="fa fa-list"></i> Variantes Disponibles</h4>';
	html += '<table class="table table-condensed table-bordered table-striped" style="background-color: white; margin-bottom: 0;">';
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

		// Solo mostrar variantes activas
		if (variante.estado != 1) continue;

		// Stock con colores
		var stockBadge = '';
		if (variante.stock <= 0) {
			stockBadge = '<span class="badge bg-red">' + variante.stock + '</span>';

		} else if (variante.stock <= 10) {
			stockBadge = '<span class="badge bg-yellow">' + variante.stock + '</span>';

		} else {
			stockBadge = '<span class="badge bg-green">' + variante.stock + '</span>';
		}

		// Botón Agregar (deshabilitado si no hay stock)
		var botonAgregar = '';

		if (variante.stock > 0) {
			botonAgregar = '<button class="btn btn-primary btn-xs agregarVarianteVenta" ' +
				'idVariante="' + variante.id + '" ' +
				'idProductoBase="' + variante.id_producto + '" ' +
				'nombreVariante="' + variante.nombre + '" ' +
				'precioVariante="' + variante.precio_final + '" ' +
				'stockVariante="' + variante.stock + '" ' +
				'skuVariante="' + variante.sku + '">Agregar</button>';
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
	html += '</div>';
	return html;
}

// Evento click en botón de expandir variantes
$(document).on('click', '.btnVariantesVenta', function () {

	var boton = $(this);
	var tr = boton.closest('tr');
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
		// csrf_token removido - manejado por csrf-helper.js

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

				// Restaurar botón
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

	// Cambiar apariencia del botón
	$(this).removeClass("btn-primary");
	$(this).addClass("btn-default");
	$(this).prop("disabled", true);

	// Agregar la variante al carrito
	$(".nuevoProducto").append(

		'<div class="row" style="padding:5px 15px">' +

		'<!--Descripcion de la variante-->' +

		'<div class="col-xs-6" style="padding-right:0px">' +

		'<div class="input-group">' +

		'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarVariante" idVariante="' + idVariante + '"><i class="fa fa-times"></i></button></span>' +

		'<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProductoBase + '" name="agregarProducto" value="' + nombreVariante + '" readonly required>' +

		'<input type="hidden" class="esVariante" value="1">' +

		'<input type="hidden" class="idVarianteProducto" value="' + idVariante + '">' +

		'<input type="hidden" class="skuVariante" value="' + skuVariante + '">' +

		'</div>' +

		'</div>' +

		'<!--Cantidad de la variante-->' +

		'<div class="col-xs-3">' +

		'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock="' + stockVariante + '" nuevoStock="' + Number(stockVariante - 1) + '" required>' +

		'</div>' +

		'<!--Precio de la variante-->' +

		'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +

		'<div class="input-group">' +

		'<input type="text" class="form-control nuevoPrecioProducto" precioReal="' + precioVariante + '" name="nuevoPrecioProducto" value="' + precioVariante + '" readonly required>' +

		'</div>' +

		'</div>' +

		'</div>')

	//Sumar total de precios
	sumarTotalPrecios()

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

	// Habilitar nuevamente el botón de la variante
	$("button.agregarVarianteVenta[idVariante='" + idVariante + "']").removeClass('btn-default');
	$("button.agregarVarianteVenta[idVariante='" + idVariante + "']").addClass('btn-primary');

	$("button.agregarVarianteVenta[idVariante='" + idVariante + "']").prop("disabled", false);

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

	$("button.recuperarBoton[idProducto='" + idProducto + "']").removeClass('btn-default');

	$("button.recuperarBoton[idProducto='" + idProducto + "']").addClass('btn-primary agregarProducto');


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


			$(".nuevoProducto").append(

				'<div class="row" style="padding:5px 15px">' +

				'<!--Descripcion del producto-->' +

				'<div class="col-xs-6" style="padding-right:0px">' +

				'<div class="input-group">' +

				'<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto><i class="fa fa-times"></i></button></span>' +

				'<select class="form-control nuevaDescripcionProducto" idProducto name="nuevaDescripcionProducto" required>' +

				'<option>Seleccione el producto</option>' +

				'</select>' +

				'</div>' +

				'</div>' +


				'<!--Cantidad del producto-->' +

				'<div class="col-xs-3 ingresoCantidad">' +

				'<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="1" stock nuevoStock required>' +

				'</div>' +


				'<!--Precio del producto-->' +

				'<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +

				'<div class="input-group">' +

				/* '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+ */

				'<input type="text" class="form-control nuevoPrecioProducto" precioReal="" name="nuevoPrecioProducto" readonly required>' +

				'</div>' +

				'</div>' +

				'<!--Impuesto del producto (Hidden in mobile view but needed for logic)-->' +
				'<div class="col-xs-12" style="padding:0">' +
				'<input type="hidden" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' + respuesta["impuesto_nombre"] + ' ' + respuesta["impuesto_porcentaje"] + '%" porcentaje="' + respuesta["impuesto_porcentaje"] + '" impuestoNombre="' + respuesta["impuesto_nombre"] + '" required>' +
				'</div>' +

				'</div>');


			/*=============================================
			AGREGAR LOS PRODUCTOS AL SELECT
			=============================================*/

			respuesta.forEach(funcionForEach);

			function funcionForEach(item, index) {

				$(".nuevaDescripcionProducto").append(

					'<option idProducto="' + item.id + '" value="' + item.descripcion + '">' + item.descripcion + '</option>')
			}

			//Sumar total de precios
			//sumarTotalPrecios()

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

	var nombreProducto = $(this).val();

	var nuevoPrecioProducto = $(this).parent().parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var nuevaCantidadProducto = $(this).parent().parent().parent().children(".ingresoCantidad").children(".nuevaCantidadProducto");

	var datos = new FormData();
	datos.append("nombreProducto", nombreProducto);
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

			$(nuevaCantidadProducto).attr("stock", respuesta["stock"]);
			$(nuevaCantidadProducto).attr("nuevoStock", Number(respuesta["stock"]) - 1);
			$(nuevoPrecioProducto).val(respuesta["precio_venta"]);
			$(nuevoPrecioProducto).attr("precioReal", respuesta["precio_venta"]);

			//Agrupar productos en formato Json
			listarProductos()

			//Sumar total de precios
			sumarTotalPrecios()

			//Sumar total de impuestos
			sumarTotalImpuestos()
		}

	})

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

	if (metodo == "Efectivo") {

		$(this).parent().parent().removeClass("col-xs-6");

		$(this).parent().parent().addClass("col-xs-4");

		$(this).parent().parent().parent().children(".cajasMetodoPago").html(

			'<div class="col-xs-4">' +

			'<div class="input-group">' +



			'<input type="text" class="form-control" id="nuevoValorEfectivo" placeholder="00000" required>' +

			'</div>' +

			'</div>' +

			'<div class="col-xs-4" id="capturarCambioEfectivo" style="padding-left:0px">' +

			'<div class="input-group">' +



			'<input type="text" class="form-control" id="nuevoCambioEfectivo" name="nuevoCambioEfectivo" placeholder="00000" readonly required>' +

			'</div>' +

			'</div>'
		)

		//Agregar formato number al precio 
		$("#nuevoValorEfectivo").number(true, 0);
		$("#nuevoCambioEfectivo").number(true, 0);

		//Listar metodo en la entrada
		listarMetodos()
	}


	//Hecho por mi else if
	else if (metodo == "") {

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

	var descripcion = $(".nuevaDescripcionProducto");

	var cantidad = $(".nuevaCantidadProducto");

	var precio = $(".nuevoPrecioProducto");
	var impuesto = $(".nuevoImpuestoProducto");

	for (var i = 0; i < descripcion.length; i++) {

		var idProducto = "";

		// Detectar si el campo es un <select> (crear) o un <input> (editar)
		if ($(descripcion[i]).is("select") || $(descripcion[i]).prop("tagName").toLowerCase() === "select") {
			idProducto = $(descripcion[i]).find("option:selected").attr("idProducto");

		} else {
			idProducto = $(descripcion[i]).attr("idProducto");
		}

		// Verificar si es una variante
		var esVariante = $(descripcion[i]).siblings(".esVariante").val();
		var idVariante = $(descripcion[i]).siblings(".idVarianteProducto").val();
		var skuVariante = $(descripcion[i]).siblings(".skuVariante").val();

		var producto = {
			"id": idProducto,
			"descripcion": $(descripcion[i]).val(),
			"cantidad": $(cantidad[i]).val(),
			"stock": $(cantidad[i]).attr("nuevoStock"),
			"precio": $(precio[i]).attr("precioReal"),
			"total": $(precio[i]).val(),
			"impuesto": $(impuesto[i]).attr("porcentaje")
		};

		// Si es variante, agregar los campos adicionales
		if (esVariante == "1") {
			producto.esVariante = "1";
			producto.idVariante = idVariante;
			producto.skuVariante = skuVariante;
		}

		listaProductos.push(producto);
	}

	$("#listaProductos").val(JSON.stringify(listaProductos));
}

$(".formularioVenta").submit(function (e) {
	var form = this;

	// Si el formulario ya ha sido confirmado, permitimos el envío
	if ($(form).data('confirmado')) {
		return true;
	}

	e.preventDefault();

	// Primero listamos productos para asegurar que el campo oculto esté listo
	listarProductos();

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
				didOpen: () => {
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

	var listarMetodos = "";
	var metodo = $("#nuevoMetodoPago").val();

	if (metodo == "Efectivo") {
		$("#listaMetodoPago").val("Efectivo");
	} else {
		var transaccion = $("#nuevoCodigoTransaccion").val();
		if (transaccion && transaccion.trim() !== "") {
			$("#listaMetodoPago").val(metodo + "-" + transaccion);
		} else {
			$("#listaMetodoPago").val(metodo);
		}
	}

}


/*=============================================
BOTON EDITAR VENTA
=============================================*/

$(document).on("click", ".btnEditarVenta", function () {

	var idVenta = $(this).attr("idVenta");

	window.location = "index.php?ruta=editar-venta&idVenta=" + idVenta;
})


/*=============================================
BORRAR VENTA
=============================================*/

$(document).on("click", ".btnEliminarVenta", function () {

	var idVenta = $(this).attr("idVenta");

	swal({

		title: '¿Está seguro de eliminar esta venta?',
		text: '¡Si no lo está puede cancelar la acción!',
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, eliminar documento'
	}).then((result) => {

		if (result.value) {

			// Primero intentar con el parámetro 'ruta'
			let ruta = new URLSearchParams(window.location.search).get('ruta');

			// Si no existe 'ruta', obtener el nombre del archivo
			if (!ruta) {
				const path = window.location.pathname;
				const archivo = path.substring(path.lastIndexOf("/") + 1);
				ruta = archivo.split(".php")[0]; // ejemplo: ordenes.php -> ordenes
			}

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
							title: "¡Venta eliminada correctamente!",
							text: "El documento ha sido borrado exitosamente del sistema.",
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
let table = new DataTable('#example');
 
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
				title: 'Guardando Factura Electrónica',
				text: 'Por favor espere mientras se procesa la información...',
				type: 'info',
				allowOutsideClick: false,
				showConfirmButton: false,
				didOpen: () => {
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

	$("#modalEnviarEmail").modal("show");
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
		didOpen: () => {
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
						$("#modalEnviarEmail").modal("hide");
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

				window.recargarTablaVentas = function() {
					table.ajax.reload();
				};

				$("select[name='cliente'], select[name='usuario']").on("change", function () {
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

					$('#daterange-btn').daterangepicker({
						ranges: {
							'Todos los documentos': [moment('2000-01-01'), moment()],
							'Hoy': [moment(), moment()],
							'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
							'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
							'Este mes': [moment().startOf('month'), moment().endOf('month')],
							'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						},
						startDate: fechaInicialUrl ? moment(fechaInicialUrl) : moment(),
						endDate: fechaFinalUrl ? moment(fechaFinalUrl) : moment()
					}, function (start, end) {
						$('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
						$('#fechaInicial').val(start.format('YYYY-MM-DD'));
						$('#fechaFinal').val(end.format('YYYY-MM-DD'));
						window.recargarTablaVentas();
					});
				}
			},
			"order": [[8, "desc"]],
			"responsive": {
				"details": {
					"type": "inline",
					"renderer": function (api, rowIdx, columns) {
						var labels = {
							2: 'Vendedor', 3: 'Forma de Pago', 4: 'Imagen', 
							5: 'Total', 6: 'Notas', 7: 'Observación', 8: 'Fecha'
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

							if (colIdx === 7) {
								var obsTexto = $('<div>').html(data).text().trim();
								finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
								finalHtml += '<span class="text-bold" style="block;color:#555;margin-bottom:4px;"> ' + label + ':</span>';
								finalHtml += '<div class="celda-observacion" contenteditable="true" data-id="' + idVenta + '" style="min-height:24px;">' + obsTexto + '</div>';
								finalHtml += '</div>';
								return;
							}

							if (colIdx === 6) {
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
				{ "targets": 9, "responsivePriority": 2, "orderable": false },
				{ "targets": 1, "responsivePriority": 3 },
				{ "targets": 2, "responsivePriority": 4 },
				{ "targets": 3, "responsivePriority": 5 },
				{ "targets": 4, "responsivePriority": 6 },
				{ "targets": 5, "responsivePriority": 7 },
				{ "targets": 6, "responsivePriority": 8 },
				{ "targets": 7, "responsivePriority": 9 },
				{ "targets": 8, "responsivePriority": 10 }
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
	$("#modalAmpliarImagenVenta").modal("show");
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
					$("#modalAmpliarImagenVenta").modal("hide");
					if(window.recargarTablaVentas) window.recargarTablaVentas();
					else window.location.reload();
				});
			}
		}
	});
});
