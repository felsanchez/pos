/*=============================================
CARGAR TABLA DINAMICA
=============================================*/

var table = $("table.tablaProductos").DataTable({
	"processing": true,
	"serverSide": true,
	"ajax": {
		"url": "ajax/datatable-productos.ajax.php",
		"type": "POST",
		"data": function (d) {
			d.categoriaFiltro = $("#filtroCategoria").val();
			d.proveedorFiltro = $("#filtroProveedor").val();
		},
		"error": function (xhr, error, thrown) {
			console.error("Error al cargar productos:", error, thrown);
			console.log("Respuesta del servidor:", xhr.responseText);
		}
	},
	"order": [[2, 'asc']],
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
					var data = col.data || '';

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
	"drawCallback": function (settings) {
		// La generación de botones de stock (success, warning, danger) ya viene procesada 
		// correctamente desde el servidor (ControladorProductos::ctrMostrarProductosServerSide).
		// No es necesario iterar y modificar el DOM manualmente aquí.

		// Asignar eventos a las nuevas imágenes renderizadas para el modal personalizado
		$('.img-ampliar-producto').off('click').on('click', function () {
			var imagen = $(this).attr('data-imagen');
			var idProducto = $(this).attr('data-idproducto');

			$('#imagenProductoAmpliada').attr('src', imagen);
			$('#idProductoImagen').val(idProducto);
			
			// Si hay un atributo data-codigo, podríamos usarlo, pero si no, omitirlo o buscarlo en la fila
			var rowData = table.row($(this).closest('tr')).data();
			if(rowData) {
			    $('#codigoProductoImagen').val(rowData[2]); // Asumiendo que el índice 2 es el código
			}

			$('#modalAmpliarImagenProducto').fadeIn();
		});
	},
	"columnDefs": [
		{
			"targets": 0,  // Imagen
			"data": null,
			"responsivePriority": 1,
			"render": function (data, type, row) {
				return '<img src="' + row[1] + '" class="img-thumbnail imgTabla img-ampliar-producto" width="40px" style="cursor: pointer;" data-imagen="' + row[1] + '" data-idproducto="' + row[0] + '">';
			}
		},
		{
			"targets": 1, // Código
			"data": null,
			"responsivePriority": 4,
			"render": function (data, type, row) { return row[2]; }
		},
		{
			"targets": 2, // Descripcion
			"data": null,
			"responsivePriority": 3,
			"render": function (data, type, row) { return row[3]; }
		},
		{
			"targets": 3, // Categoria
			"responsivePriority": 5,
			"render": function (data, type, row) { return row[4]; }
		},
		{
			"targets": 4, // Stock
			"responsivePriority": 6,
			"render": function (data, type, row) { return row[5]; }
		},
		{
			"targets": 5, // Precio Venta
			"responsivePriority": 7,
			"render": function (data, type, row) { 
				return '<code class="text-green" style="background-color: #f4fdf7;">' + row[7] + '</code>'; 
			}
		},
		{
			"targets": 6, // Proveedor
			"responsivePriority": 9,
			"render": function (data, type, row) { return row[8]; }
		},
		{
			"targets": 7, // Estado
			"responsivePriority": 8,
			"render": function (data, type, row) { return row[11]; }
		},
		{
			"targets": 8, // Acciones
			"data": null,
			"responsivePriority": 2,
			"orderable": false,
			"render": function (data, type, row) { return row[10]; }
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
});

/*=============================================
INICIALIZAR SELECT2 Y FILTROS PERSONALIZADOS
=============================================*/
$(document).ready(function () {

	// Inicializar Select2 para los filtros
	if (typeof $.fn.select2 !== 'undefined') {
		$('#filtroProveedor').select2({
			placeholder: "Seleccionar proveedor...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
		$('#filtroCategoria').select2({
			placeholder: "Seleccionar categoria...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
	}

	// Evento al cambiar los filtros
	$(document).on('change', '#filtroCategoria, #filtroProveedor', function () {
		console.log("Recargando tabla de productos por cambio de filtros...");
		table.ajax.reload();
	});

});

/*=============================================
ACTIVAR LOS BOTONES CON LOS ID CORRESPONDIENTES
=============================================*/
// Los botones ya vienen con los atributos correctos desde PHP, no es necesario sobrescribirlos
/*
$('.tablaProductos tbody').on( 'click', 'button', function () {
var data = table.row( $(this).parents('tr') ).data();
$(this).attr("idProducto", data[10])
$(this).attr("codigo", data[2])
$(this).attr("imagen", data[1]) 
} );
*/


/*=============================================
EVENTOS DEL MODAL PERSONALIZADO DE IMAGEN
=============================================*/
$(document).ready(function() {
	function cerrarModalImagen() {
		$('#modalAmpliarImagenProducto').fadeOut();
		$('.nuevaImagenProducto').val("");
	}

	$('#btnCerrarModalProducto, #btnCancelarModalProducto, #modalProductoBackdrop').on('click', function() {
		cerrarModalImagen();
	});

	// La lógica para btnGuardarImagenProducto puede estar en el archivo, o se agregará si falta.
});


/*=============================================
CAPTURANDO LA CATEGORIA PARA ASIGNAR CODIGO
=============================================*/
$("#nuevaCategoria").change(function () {

	var idCategoria = $(this).val();

	// Solo generar código automáticamente si la configuración es "automatico"

	if (typeof tipoCodigoProducto !== 'undefined' && tipoCodigoProducto === 'manual') {

		// En modo manual, limpiar el campo pero no generar código

		$("#nuevoCodigo").val('');
		return;
	}


	var datos = new FormData();
	datos.append("idCategoria", idCategoria);
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

			if (!respuesta) {
				// No hay códigos numéricos previos en esta categoría
				var nuevoCodigo = idCategoria + "01";
				$("#nuevoCodigo").val(nuevoCodigo);
			}

			else {
				// Existe un código numérico, incrementarlo
				var nuevoCodigo = Number(respuesta["codigo"]) + 1;
				$("#nuevoCodigo").val(nuevoCodigo);
			}
		}
	})
})


/*=============================================
VALIDAR QUE EL CODIGO NO EXISTA
=============================================*/

$("#nuevoCodigo").on("blur change", function () {

	var codigo = $(this).val();

	// Solo validar si hay un código ingresado
	if (codigo == "" || codigo.length < 2) {
		return;
	}

	var datos = new FormData();
	datos.append("validarCodigo", codigo);
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

			if (respuesta) {
				// El código ya existe
				$("#nuevoCodigo").parent().addClass("has-error");

				swal({
					title: "Error",
					text: "El código del producto ya existe. Por favor ingrese uno diferente.",
					type: "error",
					confirmButtonText: "Cerrar"
				});

				$("#nuevoCodigo").val("");
				$("#nuevoCodigo").focus();

			} else {
				// El código no existe, está disponible
				$("#nuevoCodigo").parent().removeClass("has-error");
			}
		}
	})

})




/*=============================================
AGREGANDO PRECIO DE VENTA
=============================================*/

$("#nuevoPrecioCompra, #editarPrecioCompra").change(function () {

	if ($(".porcentaje").prop("checked")) {

		var valorPorcentaje = $(".nuevoPorcentaje").val();

		var porcentaje = Number(($("#nuevoPrecioCompra").val() * valorPorcentaje / 100)) + Number($("#nuevoPrecioCompra").val());

		var editarPorcentaje = Number(($("#editarPrecioCompra").val() * valorPorcentaje / 100)) + Number($("#editarPrecioCompra").val());

		$("#nuevoPrecioVenta").val(porcentaje);
		$("#nuevoPrecioVenta").prop("readonly", true);

		$("#editarPrecioVenta").val(editarPorcentaje);
		$("#editarPrecioVenta").prop("readonly", true);
	}

})


/*=============================================
CAMBIO DE PORCENTAJE
=============================================*/

$(".nuevoPorcentaje").change(function () {

	if ($(".porcentaje").prop("checked")) {

		var valorPorcentaje = $(this).val();

		var porcentaje = Number(($("#nuevoPrecioCompra").val() * valorPorcentaje / 100)) + Number($("#nuevoPrecioCompra").val());

		var editarPorcentaje = Number(($("#editarPrecioCompra").val() * valorPorcentaje / 100)) + Number($("#editarPrecioCompra").val());

		$("#nuevoPrecioVenta").val(porcentaje);
		$("#nuevoPrecioVenta").prop("readonly", true);

		$("#editarPrecioVenta").val(editarPorcentaje);
		$("#editarPrecioVenta").prop("readonly", true);
	}

})

$(".porcentaje").on("ifUnchecked", function () {

	$("#nuevoPrecioVenta").prop("readonly", false);
	$("#editarPrecioVenta").prop("readonly", false);
})


$(".porcentaje").on("ifChecked", function () {

	$("#nuevoPrecioVenta").prop("readonly", true);
	$("#editarPrecioVenta").prop("readonly", true);
})



/*=============================================
SUBIENDO FOTO DEL PRODUCTO
=============================================*/

$(".nuevaImagen").change(function () {

	var imagen = this.files[0];


	/*=============================================
	VALIDAMOS EL FORMATO DE LA IMAGEN QUE SEA JPG O PNG
	=============================================*/

	if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {

		$(".nuevaImagen").val("");

		swal({
			title: "Error al subir la imagenn",
			text: "¡La imagen debe estar en formato jpg o png!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
	}

	else if (imagen["size"] > 2000000) {

		$(".nuevaImagen").val("");

		swal({
			title: "Error al subir la imagen",
			text: "¡La imagen no debe pesar mas de 2MB!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});

	}

	else {

		var datosImagen = new FileReader;
		datosImagen.readAsDataURL(imagen);

		$(datosImagen).on("load", function (event) {

			var rutaImagen = event.target.result;

			$(".previsualizar").attr("src", rutaImagen);
		})
	}


})


/*=============================================
EDITAR PRODUCTO
=============================================*/

/*=============================================
EDITAR PRODUCTO
=============================================*/

$(".tablaProductos tbody").on("click", "button.btnEditarProducto", function () {

	var idProducto = $(this).attr("idProducto");

	window.location = "index.php?ruta=producto-detalle&id=" + idProducto;

})


/*=============================================
AJUSTE RÁPIDO DE STOCK
=============================================*/
$(".tablaProductos tbody").on("click", "button.btnAjusteStock", function () {
	var idProducto = $(this).attr("idProducto");
	$("#idProductoAjuste").val(idProducto);
	$("#cantidadAjuste").val("");
});

/*=============================================
ELIMINAR PRODUCTO
=============================================*/

$(".tablaProductos tbody").on("click", "button.btnEliminarProducto", function () {

	var idProducto = $(this).attr("idProducto");
	var codigo = $(this).attr("codigo");
	var imagen = $(this).attr("imagen");


	swal({

		title: '¿Esta seguro de borrar el producto?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar producto!'
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idProductoEliminar", idProducto);
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/productos.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							type: "success",
							title: "¡El producto ha sido borrado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location = "productos";
							}
						});
					} else {
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo eliminar el producto. " + respuesta,
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
HPM REVISAR SI EL PRODUCTO YA ESTA REGISTRADO
=============================================*/

$("#nuevaDescripcion").change(function () {

	$(".alert").remove();

	var descripcion = $(this).val();

	var datos = new FormData();
	datos.append("validarDescripcion", descripcion);
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

			if (respuesta) {

				$("#nuevaDescripcion").parent().after('<div class="alert alert-warning">Este producto ya existe en la base de datos!</div>');

				$("#nuevaDescripcion").val("");


			}

		}
	})
})


/*=============================================

SISTEMA DE VARIANTES PARA PRODUCTOS

=============================================*/



// Variables globales para manejar el estado de variantes

var tiposVariantesSeleccionados = [];

var opcionesVariantesSeleccionadas = {};

var datosVariantes = {};



/*=============================================

INICIALIZAR CHECKBOX DE VARIANTES CON iCheck

=============================================*/

/* 
// CONFLICTO CON PRODUCTO-DETALLE.JS - SE COMENTA PARA EVITAR DUPLICIDAD
$("#checkTieneVariantes").iCheck({
	checkboxClass: 'icheckbox_minimal-blue',
	radioClass: 'iradio_minimal-blue'
});

$("#checkTieneVariantes").on('ifChecked', function () {
	$("#contenedorVariantes").slideDown();
	cargarTiposVariantes();
	// Deshabilitar y resetear el campo de stock
	$("#nuevoStock").val(0);
	$("#nuevoStock").prop('readonly', true);
	$("#nuevoStock").prop('required', false);
	$("#nuevoStock").css('background-color', '#f4f4f4');
	// Mostrar mensaje informativo
	swal({
		type: "info",
		title: "Stock calculado automáticamente",
		text: "El stock del producto se calculará automáticamente como la suma de todas las variantes que crees.",
		confirmButtonText: "Entendido"
	});
	// Hacer campos de precio opcionales cuando hay variantes
	$("#nuevoPrecioCompra").prop('required', false);
	$("#nuevoPrecioVenta").prop('required', false);
	// Cambiar los mensajes de ayuda
	$("#helpStockProducto").hide();
	$("#helpStockVariantes").show();
});

$("#checkTieneVariantes").on('ifUnchecked', function () {
	$("#contenedorVariantes").slideUp();
	limpiarVariantes();
	// Rehabilitar el campo de stock
	$("#nuevoStock").val('');
	$("#nuevoStock").prop('readonly', false);
	$("#nuevoStock").prop('required', true);
	$("#nuevoStock").css('background-color', '');
	// Volver a hacer obligatorios los campos
	$("#nuevoPrecioCompra").prop('required', true);
	$("#nuevoPrecioVenta").prop('required', true);
	// Restaurar los mensajes de ayuda
	$("#helpStockProducto").show();
	$("#helpStockVariantes").hide();
});
*/


/*=============================================
CARGAR TIPOS DE VARIANTES DISPONIBLES
=============================================*/

function cargarTiposVariantes() {

	var datos = new FormData();

	datos.append("obtenerTiposVariantes", true);

	$.ajax({

		url: "ajax/productos.ajax.php",

		method: "POST",

		data: datos,

		cache: false,

		contentType: false,

		processData: false,

		dataType: "json",

		success: function (respuesta) {

			console.log("Tipos de variantes:", respuesta);

			if (respuesta && respuesta.length > 0) {

				var html = '';

				for (var i = 0; i < respuesta.length; i++) {


					if (respuesta[i].estado == 1) {

						html += '<div class="checkbox">';

						html += '<label>';

						html += '<input type="checkbox" class="minimal checkTipoVariante" data-idtipo="' + respuesta[i].id + '" data-nombretipo="' + respuesta[i].nombre + '">';

						html += respuesta[i].nombre;

						html += '</label>';

						html += '</div>';


						// Guardar datos del tipo

						datosVariantes['tipo_' + respuesta[i].id] = {

							id: respuesta[i].id,

							nombre: respuesta[i].nombre,

							opciones: []
						};

					}
				}


				$("#tiposVariantesContainer").html(html);


				// Inicializar iCheck para los checkboxes de tipos

				$('.checkTipoVariante').iCheck({

					checkboxClass: 'icheckbox_minimal-blue',

					radioClass: 'iradio_minimal-blue'

				});



				// Evento al seleccionar/deseleccionar tipo

				$('.checkTipoVariante').on('ifChecked', function () {

					var idTipo = $(this).attr("data-idtipo");

					var nombreTipo = $(this).attr("data-nombretipo");

					agregarTipoVariante(idTipo, nombreTipo);

				});



				$('.checkTipoVariante').on('ifUnchecked', function () {
					var idTipo = $(this).attr("data-idtipo");
					removerTipoVariante(idTipo);
				});



			} else {
				$("#tiposVariantesContainer").html('<p class="text-danger">No hay tipos de variantes disponibles. <a href="variantes">Crear tipo de variante</a></p>');

			}

		},

		error: function () {
			$("#tiposVariantesContainer").html('<p class="text-danger">Error al cargar tipos de variantes.</p>');
		}

	});

}



/*=============================================
AGREGAR TIPO DE VARIANTE SELECCIONADO
=============================================*/

function agregarTipoVariante(idTipo, nombreTipo) {

	if (tiposVariantesSeleccionados.indexOf(idTipo) === -1) {

		tiposVariantesSeleccionados.push(idTipo);

	}

	// Cargar opciones de este tipo

	cargarOpcionesVariante(idTipo, nombreTipo);

	console.log("Tipos seleccionados:", tiposVariantesSeleccionados);
}

/*=============================================
REMOVER TIPO DE VARIANTE
=============================================*/

function removerTipoVariante(idTipo) {

	var index = tiposVariantesSeleccionados.indexOf(idTipo);

	if (index > -1) {

		tiposVariantesSeleccionados.splice(index, 1);
	}

	// Remover contenedor de opciones de este tipo
	$("#opcionesTipo_" + idTipo).remove();

	// Limpiar opciones seleccionadas de este tipo
	delete opcionesVariantesSeleccionadas[idTipo];

	// Regenerar combinaciones
	if (tiposVariantesSeleccionados.length > 0) {
		generarCombinaciones();

	} else {

		$("#opcionesVariantesContainer").hide();
		$("#combinacionesContainer").hide();

	}

	console.log("Tipos seleccionados:", tiposVariantesSeleccionados);

}

/*=============================================
CARGAR OPCIONES DE UN TIPO DE VARIANTE
=============================================*/

function cargarOpcionesVariante(idTipo, nombreTipo) {

	var datos = new FormData();

	datos.append("obtenerOpcionesVariante", idTipo);

	$.ajax({

		url: "ajax/productos.ajax.php",

		method: "POST",

		data: datos,

		cache: false,

		contentType: false,

		processData: false,

		dataType: "json",

		success: function (respuesta) {

			console.log("Opciones de " + nombreTipo + ":", respuesta);

			if (respuesta && respuesta.length > 0) {

				// Mostrar contenedor de opciones si está oculto
				$("#opcionesVariantesContainer").show();


				var html = '<div id="opcionesTipo_' + idTipo + '" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; background-color: #fff;">';

				html += '<h5><strong>' + nombreTipo + ':</strong></h5>';

				for (var i = 0; i < respuesta.length; i++) {

					if (respuesta[i].estado == 1) {

						html += '<div class="checkbox" style="display:inline-block; margin-right: 15px;">';

						html += '<label>';

						html += '<input type="checkbox" class="minimal checkOpcionVariante" data-idtipo="' + idTipo + '" data-idopcion="' + respuesta[i].id + '" data-nombreopcion="' + respuesta[i].nombre + '">';

						html += respuesta[i].nombre;

						html += '</label>';

						html += '</div>';


						// Guardar datos de la opción

						datosVariantes['tipo_' + idTipo].opciones.push({

							id: respuesta[i].id,

							nombre: respuesta[i].nombre

						});

					}

				}

				html += '</div>';

				$("#opcionesVariantesContainer").append(html);

				// Inicializar iCheck para los checkboxes de opciones
				$('.checkOpcionVariante[data-idtipo="' + idTipo + '"]').iCheck({
					checkboxClass: 'icheckbox_minimal-blue',
					radioClass: 'iradio_minimal-blue'

				});

				// Evento al seleccionar/deseleccionar opción
				$('.checkOpcionVariante[data-idtipo="' + idTipo + '"]').on('ifChecked', function () {

					var idTipo = $(this).attr("data-idtipo");

					var idOpcion = $(this).attr("data-idopcion");

					var nombreOpcion = $(this).attr("data-nombreopcion");

					agregarOpcionVariante(idTipo, idOpcion, nombreOpcion);

				});

				$('.checkOpcionVariante[data-idtipo="' + idTipo + '"]').on('ifUnchecked', function () {

					var idTipo = $(this).attr("data-idtipo");

					var idOpcion = $(this).attr("data-idopcion");

					removerOpcionVariante(idTipo, idOpcion);

				});

			} else {

				var html = '<div id="opcionesTipo_' + idTipo + '" class="alert alert-warning">';

				html += 'No hay opciones disponibles para ' + nombreTipo + '. <a href="variantes">Crear opciones</a>';

				html += '</div>';

				$("#opcionesVariantesContainer").show();

				$("#opcionesVariantesContainer").append(html);

			}

		},

		error: function () {

			console.log("Error al cargar opciones de variante");

		}

	});

}


/*=============================================
AGREGAR OPCIÓN DE VARIANTE SELECCIONADA
=============================================*/

function agregarOpcionVariante(idTipo, idOpcion, nombreOpcion) {


	if (!opcionesVariantesSeleccionadas[idTipo]) {

		opcionesVariantesSeleccionadas[idTipo] = [];

	}


	var existe = opcionesVariantesSeleccionadas[idTipo].find(function (op) {

		return op.id === idOpcion;

	});


	if (!existe) {
		opcionesVariantesSeleccionadas[idTipo].push({

			id: idOpcion,

			nombre: nombreOpcion

		});

	}

	generarCombinaciones();

	console.log("Opciones seleccionadas:", opcionesVariantesSeleccionadas);

}

/*=============================================
REMOVER OPCIÓN DE VARIANTE
=============================================*/

function removerOpcionVariante(idTipo, idOpcion) {

	if (opcionesVariantesSeleccionadas[idTipo]) {

		opcionesVariantesSeleccionadas[idTipo] = opcionesVariantesSeleccionadas[idTipo].filter(function (op) {

			return op.id !== idOpcion;

		});


		if (opcionesVariantesSeleccionadas[idTipo].length === 0) {

			delete opcionesVariantesSeleccionadas[idTipo];

		}

	}

	generarCombinaciones();

	console.log("Opciones seleccionadas:", opcionesVariantesSeleccionadas);

}

/*=============================================
GENERAR COMBINACIONES DE VARIANTES
=============================================*/

function generarCombinaciones() {

	// Verificar que haya opciones seleccionadas

	var tiposConOpciones = Object.keys(opcionesVariantesSeleccionadas);

	if (tiposConOpciones.length === 0) {

		$("#combinacionesContainer").hide();

		return;

	}

	// Generar todas las combinaciones posibles

	var combinaciones = generarProductoCartesiano(opcionesVariantesSeleccionadas);

	console.log("Combinaciones generadas:", combinaciones);


	if (combinaciones.length === 0) {

		$("#combinacionesContainer").hide();

		return;

	}


	// Mostrar contenedor

	$("#combinacionesContainer").show();

	// Generar HTML para cada combinación

	var html = '<table class="table table-bordered table-condensed">';

	html += '<thead>';

	html += '<tr>';

	html += '<th width="50px"><input type="checkbox" id="checkTodasCombinaciones"></th>';

	html += '<th>Variante</th>';

	html += '<th width="150px">Precio Adicional (+/-)</th>';

	html += '<th width="100px">Stock</th>';

	html += '</tr>';

	html += '</thead>';

	html += '<tbody>';


	for (var i = 0; i < combinaciones.length; i++) {

		var nombreCombinacion = combinaciones[i].map(function (opt) { return opt.nombre; }).join(' - ');

		var idsCombinacion = combinaciones[i].map(function (opt) { return opt.id; }).join('_');


		html += '<tr class="filaCombinacion" data-index="' + i + '">';

		html += '<td><input type="checkbox" class="checkCombinacion" data-index="' + i + '" data-idscombinacion="' + idsCombinacion + '" checked></td>';

		html += '<td>' + nombreCombinacion + '</td>';

		html += '<td>';

		html += '<input type="number" class="form-control input-sm" name="precioAdicional_' + idsCombinacion + '" placeholder="0" step="0.01">';

		html += '<small class="text-muted">Se sumará al precio base</small>';

		html += '</td>';

		html += '<td>';

		html += '<input type="number" class="form-control input-sm" name="stockVariante_' + idsCombinacion + '" placeholder="Stock base" min="0">';

		html += '</td>';

		html += '</tr>';



		// Guardar información de la combinación (hidden inputs) - Se habilitarán/deshabilitarán según checkbox

		html += '<input type="hidden" name="combinacion_' + i + '_ids" value="' + idsCombinacion + '" class="hiddenCombinacion" data-index="' + i + '">';

		html += '<input type="hidden" name="combinacion_' + i + '_nombre" value="' + nombreCombinacion + '" class="hiddenCombinacion" data-index="' + i + '">';

	}

	html += '</tbody>';

	html += '</table>';

	html += '<input type="hidden" name="totalCombinaciones" value="' + combinaciones.length + '">';

	$("#listaCombinaciones").html(html);

	// Evento para seleccionar/deseleccionar todas las combinaciones

	$("#checkTodasCombinaciones").change(function () {

		var checked = $(this).prop('checked');

		$(".checkCombinacion").each(function () {

			$(this).prop('checked', checked);

			actualizarEstadoCombinacion($(this));

		});

	});


	// Evento para cada checkbox de combinación individual
	$(document).on('change', '.checkCombinacion', function () {
		actualizarEstadoCombinacion($(this));
	});
}

/*=============================================
ACTUALIZAR ESTADO DE COMBINACIÓN (HABILITAR/DESHABILITAR)
=============================================*/

function actualizarEstadoCombinacion(checkbox) {
	var index = checkbox.attr('data-index');
	var checked = checkbox.prop('checked');

	// Deshabilitar/habilitar los hidden inputs según el estado del checkbox
	if (!checked) {
		$('.hiddenCombinacion[data-index="' + index + '"]').prop('disabled', true);

	} else {
		$('.hiddenCombinacion[data-index="' + index + '"]').prop('disabled', false);

	}
}


/*============================================
GENERAR PRODUCTO CARTESIANO (COMBINACIONES)
=============================================*/

function generarProductoCartesiano(opciones) {

	var keys = Object.keys(opciones);

	if (keys.length === 0) return [];

	var result = [[]];

	for (var i = 0; i < keys.length; i++) {

		var key = keys[i];

		var opcionesDelTipo = opciones[key];

		var temp = [];


		for (var j = 0; j < result.length; j++) {

			for (var k = 0; k < opcionesDelTipo.length; k++) {

				temp.push(result[j].concat([opcionesDelTipo[k]]));

			}

		}

		result = temp;

	}

	return result;

}


/*=============================================
LIMPIAR DATOS DE VARIANTES
=============================================*/

function limpiarVariantes() {

	tiposVariantesSeleccionados = [];

	opcionesVariantesSeleccionadas = {};

	datosVariantes = {};

	$("#tiposVariantesContainer").html('');

	$("#opcionesVariantesContainer").html('').hide();

	$("#combinacionesContainer").html('').hide();

}


/*=============================================
EXPANDIR/COLAPSAR VARIANTES DE PRODUCTO
=============================================*/

// Función para formatear la tabla de variantes
function formatearTablaVariantes(variantes) {

	if (!variantes || variantes.length === 0) {
		return '<div class="alert alert-info">No hay variantes para este producto</div>';
	}

	// Función auxiliar para formatear precios (formato colombiano: $80.000)

	function formatearPrecio(numero) {
		return Math.round(numero).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
	}

	var html = '<div style="padding: 20px; background-color: #f9f9f9;">';

	html += '<h4><i class="fa fa-list"></i> Variantes del Producto</h4>';

	html += '<table class="table table-condensed table-bordered table-striped" style="background-color: white; margin-bottom: 0;">';

	html += '<thead>';

	html += '<tr>';

	html += '<th width="150px">SKU</th>';

	html += '<th>Variante</th>';

	html += '<th width="120px">Precio Adicional</th>';

	html += '<th width="120px">Precio Final</th>';

	html += '<th width="80px">Stock</th>';

	html += '<th width="80px">Estado</th>';

	html += '<th width="80px">Acciones</th>';

	html += '</tr>';

	html += '</thead>';

	html += '<tbody>';


	for (var i = 0; i < variantes.length; i++) {

		var variante = variantes[i];

		// Formato de precio adicional con signo
		var precioAdicionalStr = '';

		if (parseFloat(variante.precio_adicional) > 0) {
			precioAdicionalStr = '<span class="text-success">+$' + formatearPrecio(variante.precio_adicional) + '</span>';

		} else if (parseFloat(variante.precio_adicional) < 0) {
			precioAdicionalStr = '<span class="text-danger">-$' + formatearPrecio(Math.abs(variante.precio_adicional)) + '</span>';

		} else {
			precioAdicionalStr = '<span class="text-muted">$0</span>';
		}

		// Botones de Acciones
		var botonesAcciones = '';

		// Botón de editar
		if (typeof permisoEditarProductos !== 'undefined' && permisoEditarProductos) {
			botonesAcciones += '<button class="btn btn-warning btn-xs btnEditarVariante" idVariante="' + variante.id + '" precioAdicional="' + variante.precio_adicional + '" stock="' + variante.stock + '"><i class="fa fa-pencil"></i></button>';
		} else {
			botonesAcciones += '<button class="btn btn-warning btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para editar"><i class="fa fa-pencil"></i></button>';
		}

		// Botón de eliminar
		if (typeof permisoEliminarProductos !== 'undefined' && permisoEliminarProductos) {
			botonesAcciones += ' <button class="btn btn-danger btn-xs btnEliminarVariante" idVariante="' + variante.id + '" idProducto="' + variante.id_producto + '"><i class="fa fa-trash"></i></button>';
		} else {
			botonesAcciones += ' <button class="btn btn-danger btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para eliminar"><i class="fa fa-trash"></i></button>';
		}

		// Stock con colores
		var stockBadge = '';

		if (variante.stock <= 10) {
			stockBadge = '<span class="badge bg-red">' + variante.stock + '</span>';

		} else if (variante.stock <= 15) {
			stockBadge = '<span class="badge bg-yellow">' + variante.stock + '</span>';

		} else {
			stockBadge = '<span class="badge bg-green">' + variante.stock + '</span>';
		}

		// Botón de Estado de la variante
		var botonEstado = '';
		if (typeof permisoEditarProductos !== 'undefined' && permisoEditarProductos) {
			if (variante.estado != 0) {
				botonEstado = '<button class="btn btn-success btn-xs btnActivarVariante" idVariante="' + variante.id + '" estadoVariante="1"><i class="fa fa-check"></i> Activado</button>';
			} else {
				botonEstado = '<button class="btn btn-danger btn-xs btnActivarVariante" idVariante="' + variante.id + '" estadoVariante="0"><i class="fa fa-times"></i> Desactivado</button>';
			}
		} else {
			if (variante.estado != 0) {
				botonEstado = '<button class="btn btn-success btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para editar"><i class="fa fa-check"></i> Activado</button>';
			} else {
				botonEstado = '<button class="btn btn-danger btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para editar"><i class="fa fa-times"></i> Desactivado</button>';
			}
		}

		html += '<tr>';
		html += '<td><code>' + variante.sku + '</code></td>';
		html += '<td>' + variante.nombre + '</td>';
		html += '<td>' + precioAdicionalStr + '</td>';
		html += '<td><strong>$' + formatearPrecio(variante.precio_final) + '</strong></td>';
		html += '<td class="text-center">' + stockBadge + '</td>';
		html += '<td class="text-center">' + botonEstado + '</td>';
		html += '<td class="text-center">' + botonesAcciones + '</td>';
		html += '</tr>';
	}

	html += '</tbody>';
	html += '</table>';
	html += '</div>';

	return html;
}

// Evento click en botón de expandir variantes
$(document).on('click', '.btnExpandirVariantes', function () {

	console.log("Click en botón expandir variantes detectado");

	var boton = $(this);
	var tr = boton.closest('tr');
	var row = table.row(tr);
	var idProducto = boton.attr('data-id-producto');
	var icono = boton.find('i');

	console.log("ID Producto:", idProducto);
	console.log("Botón:", boton);
	console.log("Row:", row);

	// Si la fila ya está expandida, colapsarla
	if (row.child.isShown()) {

		row.child.hide();

		tr.removeClass('shown');

		icono.removeClass('fa-minus').addClass('fa-plus');

		boton.removeClass('btn-warning').addClass('btn-info');

		return;
	}

	// Si no está expandida, mostrar loading y cargar variantes
	boton.prop('disabled', true);
	icono.removeClass('fa-plus').addClass('fa-spinner fa-spin');


	// Cargar variantes vía AJAX
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

			console.log("Variantes cargadas:", variantes);


			// Formatear tabla de variantes
			var tablaVariantes = formatearTablaVariantes(variantes);


			// Mostrar fila expandida
			row.child(tablaVariantes).show();
			tr.addClass('shown');


			// Cambiar icono del botón
			icono.removeClass('fa-spinner fa-spin fa-plus').addClass('fa-minus');
			boton.removeClass('btn-info').addClass('btn-warning');
			boton.prop('disabled', false);
		},

		error: function (jqXHR, textStatus, errorThrown) {

			console.error("Error al cargar variantes:", textStatus, errorThrown);
			console.log("Response:", jqXHR.responseText);

			// Mostrar mensaje de error
			var mensajeError = '<div class="alert alert-danger">Error al cargar las variantes. Por favor, intenta nuevamente.</div>';

			row.child(mensajeError).show();

			tr.addClass('shown');


			// Restaurar botón
			icono.removeClass('fa-spinner fa-spin').addClass('fa-plus');
			boton.prop('disabled', false);

		}

	});


});


/*=============================================
ACTIVAR/DESACTIVAR PRODUCTO
=============================================*/

$(document).on('click', '.btnActivarProducto', function () {

	var idProducto = $(this).attr("idProducto");
	var estadoProducto = $(this).attr("estadoProducto");
	var boton = $(this);

	var datos = new FormData();
	datos.append("activarId", idProducto);
	datos.append("activarProducto", estadoProducto);

	$.ajax({

		url: "ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			if (respuesta == "ok") {

				// Cambiar visualmente el botón
				if (estadoProducto == 0) {
					boton.removeClass('btn-success').addClass('btn-danger');
					boton.html('Desactivado');
					boton.attr('estadoProducto', 1);
				} else {
					boton.removeClass('btn-danger').addClass('btn-success');
					boton.html('Activado');
					boton.attr('estadoProducto', 0);
				}

				swal({
					type: "success",
					title: "Estado actualizado correctamente",
					showConfirmButton: false,
					timer: 1500
				});

			} else {

				swal({
					type: "error",
					title: "Error al actualizar el estado",
					text: "Por favor, intenta nuevamente"
				});
			}

		},

		error: function (jqXHR, textStatus, errorThrown) {

			console.error("Error al activar/desactivar producto:", textStatus, errorThrown);

			swal({
				type: "error",
				title: "Error de servidor",
				text: "Por favor, intenta nuevamente"
			});

		}

	});

});


/*=============================================
ACTIVAR/DESACTIVAR VARIANTE
=============================================*/

$(document).on('click', '.btnActivarVariante', function () {

	var idVariante = $(this).attr("idVariante");
	var estadoActual = $(this).attr("estadoVariante");
	var nuevoEstado = (estadoActual == 1) ? 0 : 1;
	var boton = $(this);

	var datos = new FormData();
	datos.append("activarVariante", idVariante);
	datos.append("nuevoEstado", nuevoEstado);
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

			if (respuesta == "ok") {

				// Cambiar visualmente el botón
				if (nuevoEstado == 1) {
					boton.removeClass('btn-danger').addClass('btn-success');
					boton.html('<i class="fa fa-check"></i> Activado');
					boton.attr('estadoVariante', 1);
				} else {

					boton.removeClass('btn-success').addClass('btn-danger');
					boton.html('<i class="fa fa-times"></i> Desactivado');
					boton.attr('estadoVariante', 0);
				}

				swal({
					type: "success",
					title: "Estado actualizado correctamente",
					showConfirmButton: false,
					timer: 1500
				});

			} else {

				swal({
					type: "error",
					title: "Error al actualizar el estado",
					text: "Por favor, intenta nuevamente"
				});
			}

		},

		error: function (jqXHR, textStatus, errorThrown) {

			console.error("Error al activar/desactivar variante:", textStatus, errorThrown);

			swal({
				type: "error",
				title: "Error de conexión",
				text: "No se pudo conectar con el servidor"
			});

		}

	});

});


/*=============================================
EDITAR VARIANTE
=============================================*/

$(document).on('click', '.btnEditarVariante', function (e) {
	e.preventDefault();
	e.stopPropagation();

	var idVariante = $(this).attr("idVariante");
	var precioAdicional = $(this).attr("precioAdicional");
	var stock = $(this).attr("stock");

	console.log("Abriendo modal editar variante:", { idVariante, precioAdicional, stock });

	// Limpiar TODOS los backdrops y modales abiertos
	$('.modal').modal('hide');
	$('.modal-backdrop').remove();
	$('body').removeClass('modal-open');
	$('body').css('padding-right', '');

	// Pequeño delay para asegurar que todo se limpió
	setTimeout(function () {
		// Llenar el formulario del modal
		$("#idVariante").val(idVariante);
		$("#editarPrecioAdicionalVariante").val(precioAdicional);
		$("#editarStockVariante").val(stock);

		// Asegurar que el modal esté en el body
		if ($('#modalEditarVariante').parent()[0] !== document.body) {
			$('#modalEditarVariante').appendTo('body');
		}

		// Abrir el modal
		$("#modalEditarVariante").modal('show');

		console.log("Modal abierto");
	}, 100);

});


/*=============================================
GUARDAR CAMBIOS DE VARIANTE
=============================================*/

$("#formEditarVariante").on("submit", function (e) {

	e.preventDefault();

	var idVariante = $("#idVariante").val();
	var precioAdicional = $("#editarPrecioAdicionalVariante").val();
	var stock = $("#editarStockVariante").val();
	var datos = new FormData();

	datos.append("editarVariante", idVariante);
	datos.append("editarPrecioAdicionalVariante", precioAdicional);
	datos.append("editarStockVariante", stock);
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

			if (respuesta == "ok") {
				$("#modalEditarVariante").modal("hide");

				swal({
					type: "success",
					title: "¡La variante ha sido actualizada correctamente!",
					showConfirmButton: false,
					timer: 1500
				});

				// Recargar la fila de variantes si está expandida
				var botonExpandir = $(".btnExpandirVariantes[data-id-producto]").filter(function () {
					var row = table.row($(this).closest('tr'));
					return row.child.isShown();
				}).first();

				if (botonExpandir.length > 0) {
					// Colapsar y volver a expandir para refrescar datos
					botonExpandir.click();
					setTimeout(function () {
						botonExpandir.click();
					}, 500);
				}

			} else {
				swal({
					type: "error",
					title: "Error al actualizar la variante",
					text: "Por favor, intenta nuevamente"
				});
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {

			console.error("Error al guardar variante:", textStatus, errorThrown);

			swal({
				type: "error",
				title: "Error de conexión",
				text: "No se pudo conectar con el servidor"
			});

		}

	});


});

/*=============================================
ELIMINAR VARIANTE
=============================================*/
$(document).on("click", ".btnEliminarVariante", function(){

	var idVariante = $(this).attr("idVariante");
    var idProducto = $(this).attr("idProducto");
	
	swal({
		title: '¿Eliminar variante?',
		text: "Esta acción no se puede deshacer.",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		cancelButtonText: 'Cancelar',
		confirmButtonText: '¡Sí, eliminar!'
	}).then(function(result){

		if(result.value){

            var datos = new FormData();
            datos.append("idVarianteEliminar", idVariante);
            datos.append("idProductoVarianteEliminar", idProducto);

            $.ajax({
                url:"ajax/productos.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success:function(respuesta){
                    var esExito = false;
                    try {
                        var resObj = typeof respuesta === 'object' ? respuesta : JSON.parse(respuesta);
                        if (resObj.status === "ok") {
                            esExito = true;
                        }
                    } catch(e) {
                        if (respuesta == "ok" || respuesta.trim() == "ok") {
                            esExito = true;
                        }
                    }

                    if(esExito){
                        swal({
                            type: "success",
                            title: "¡La variante ha sido eliminada correctamente!",
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Recargar el DataTable principal para refrescar el stock global y las acciones
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        }

                    } else {
                        var msg = "No se pudo eliminar la variante.";
                        try {
                            var resObj = typeof respuesta === 'object' ? respuesta : JSON.parse(respuesta);
                            if (resObj.message) msg = resObj.message;
                        } catch(e) {}
                        swal({
                            type: "error",
                            title: "Oops...",
                            text: msg
                        });
                    }
                }
            });
		}
	})
});

/*=============================================
VARIABLES PARA VARIANTES EN EDITAR PRODUCTO
=============================================*/
var tiposVariantesSeleccionadosEditar = [];
var opcionesVariantesSeleccionadasEditar = {};
var datosVariantesEditar = {};

var idProductoEditando = null; // ID del producto que se está editando
var variantesExistentes = {}; // Variantes existentes del producto (key = opciones, value = datos)

/*=============================================
ACTIVAR ICHECK PARA CHECKBOX AGREGAR VARIANTES EN EDITAR
=============================================*/
// NOTA: La inicialización de iCheck se hace dinámicamente cuando se abre el modal de editar
// Ver líneas 411-416 en el handler de btnEditarProducto

/*=============================================
MOSTRAR/OCULTAR CONTENEDOR DE AGREGAR VARIANTES EN EDITAR
=============================================*/
// NOTA: Los eventos se registran dinámicamente cuando se abre el modal de editar
// Ver líneas 419-427 en el handler de btnEditarProducto

/*=============================================
CARGAR VARIANTES EXISTENTES DEL PRODUCTO
=============================================*/

function cargarVariantesExistentes(callback) {

	// Inicializar como objeto vacío por defecto
	variantesExistentes = {};

	if (!idProductoEditando) {
		console.log("No hay ID de producto para cargar variantes");

		if (callback) callback();
		return;
	}

	var datos = new FormData();
	datos.append("obtenerVariantesParaEditar", idProductoEditando);
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
			console.log("Variantes existentes del producto:", respuesta);

			// Guardar variantes existentes en un objeto con key = opciones
			if (respuesta && respuesta.length > 0) {

				for (var i = 0; i < respuesta.length; i++) {

					var variante = respuesta[i];
					variantesExistentes[variante.opciones] = {

						id: variante.id,
						precio_adicional: variante.precio_adicional,
						stock: variante.stock,
						sku: variante.sku
					};
				}

			}

			console.log("Variantes existentes indexadas:", variantesExistentes);
			if (callback) callback();

		},

		error: function (jqXHR, textStatus, errorThrown) {
			console.log("Error al cargar variantes existentes:", textStatus, errorThrown);
			if (callback) callback();
		}

	});
}


/*=============================================
CARGAR TIPOS DE VARIANTES PARA EDITAR
=============================================*/

function cargarTiposVariantesEditar() {

	// Primero cargar variantes existentes
	cargarVariantesExistentes();

	var datos = new FormData();
	datos.append("obtenerTiposVariantes", true);
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

			console.log("Tipos de variantes (Editar):", respuesta);

			if (respuesta && respuesta.length > 0) {

				var html = '';

				for (var i = 0; i < respuesta.length; i++) {

					if (respuesta[i].estado == 1) {

						html += '<div class="checkbox">';
						html += '<label>';
						html += '<input type="checkbox" class="minimal checkTipoVarianteEditar" data-idtipo="' + respuesta[i].id + '" data-nombretipo="' + respuesta[i].nombre + '">';
						html += respuesta[i].nombre;
						html += '</label>';
						html += '</div>';

						datosVariantesEditar['tipo_' + respuesta[i].id] = {
							id: respuesta[i].id,
							nombre: respuesta[i].nombre,
							opciones: []
						};
					}
				}

				$("#tiposVariantesEditarContainer").html(html);

				$('.checkTipoVarianteEditar').iCheck({
					checkboxClass: 'icheckbox_minimal-blue',
					radioClass: 'iradio_minimal-blue'
				});

				$('.checkTipoVarianteEditar').on('ifChecked', function () {
					var idTipo = $(this).attr("data-idtipo");
					var nombreTipo = $(this).attr("data-nombretipo");
					agregarTipoVarianteEditar(idTipo, nombreTipo);
				});

				$('.checkTipoVarianteEditar').on('ifUnchecked', function () {
					var idTipo = $(this).attr("data-idtipo");
					removerTipoVarianteEditar(idTipo);
				});

			} else {
				$("#tiposVariantesEditarContainer").html('<p class="text-danger">No hay tipos de variantes disponibles. <a href="variantes">Crear tipo de variante</a></p>');
			}
		},
		error: function () {
			$("#tiposVariantesEditarContainer").html('<p class="text-danger">Error al cargar tipos de variantes.</p>');
		}
	});
}

/*=============================================
AGREGAR TIPO DE VARIANTE EN EDITAR
=============================================*/
function agregarTipoVarianteEditar(idTipo, nombreTipo) {

	if (tiposVariantesSeleccionadosEditar.indexOf(idTipo) === -1) {
		tiposVariantesSeleccionadosEditar.push(idTipo);
	}

	cargarOpcionesVarianteEditar(idTipo, nombreTipo);

	console.log("Tipos seleccionados (Editar):", tiposVariantesSeleccionadosEditar);
}

/*=============================================
REMOVER TIPO DE VARIANTE EN EDITAR
=============================================*/
function removerTipoVarianteEditar(idTipo) {

	var index = tiposVariantesSeleccionadosEditar.indexOf(idTipo);

	if (index > -1) {
		tiposVariantesSeleccionadosEditar.splice(index, 1);
	}

	$("#opcionesTipoEditar_" + idTipo).remove();
	delete opcionesVariantesSeleccionadasEditar[idTipo];

	if (tiposVariantesSeleccionadosEditar.length > 0) {
		generarCombinacionesEditar();
	} else {
		$("#opcionesVariantesEditarContainer").hide();
		$("#combinacionesEditarContainer").hide();
	}

	console.log("Tipos seleccionados (Editar):", tiposVariantesSeleccionadosEditar);
}

/*=============================================
CARGAR OPCIONES DE VARIANTE EN EDITAR
=============================================*/
function cargarOpcionesVarianteEditar(idTipo, nombreTipo) {

	var datos = new FormData();
	datos.append("obtenerOpcionesVariante", idTipo);
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

			console.log("Opciones de " + nombreTipo + " (Editar):", respuesta);

			if (respuesta && respuesta.length > 0) {

				$("#opcionesVariantesEditarContainer").show();

				var html = '<div id="opcionesTipoEditar_' + idTipo + '" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; background-color: #fff;">';
				html += '<h5><strong>' + nombreTipo + ':</strong></h5>';

				for (var i = 0; i < respuesta.length; i++) {

					if (respuesta[i].estado == 1) {

						html += '<div class="checkbox" style="display:inline-block; margin-right: 15px;">';
						html += '<label>';
						html += '<input type="checkbox" class="minimal checkOpcionVarianteEditar" data-idtipo="' + idTipo + '" data-idopcion="' + respuesta[i].id + '" data-nombreopcion="' + respuesta[i].nombre + '">';
						html += respuesta[i].nombre;
						html += '</label>';
						html += '</div>';

						datosVariantesEditar['tipo_' + idTipo].opciones.push({
							id: respuesta[i].id,
							nombre: respuesta[i].nombre
						});
					}
				}

				html += '</div>';

				$("#opcionesVariantesEditarContainer").append(html);

				$('.checkOpcionVarianteEditar[data-idtipo="' + idTipo + '"]').iCheck({
					checkboxClass: 'icheckbox_minimal-blue',
					radioClass: 'iradio_minimal-blue'
				});

				$('.checkOpcionVarianteEditar[data-idtipo="' + idTipo + '"]').on('ifChecked', function () {
					var idTipo = $(this).attr("data-idtipo");
					var idOpcion = $(this).attr("data-idopcion");
					var nombreOpcion = $(this).attr("data-nombreopcion");
					agregarOpcionVarianteEditar(idTipo, idOpcion, nombreOpcion);
				});

				$('.checkOpcionVarianteEditar[data-idtipo="' + idTipo + '"]').on('ifUnchecked', function () {
					var idTipo = $(this).attr("data-idtipo");
					var idOpcion = $(this).attr("data-idopcion");
					removerOpcionVarianteEditar(idTipo, idOpcion);
				});

			} else {
				var html = '<div id="opcionesTipoEditar_' + idTipo + '" class="alert alert-warning">';
				html += 'No hay opciones disponibles para ' + nombreTipo + '. <a href="variantes">Crear opciones</a>';
				html += '</div>';

				$("#opcionesVariantesEditarContainer").show();
				$("#opcionesVariantesEditarContainer").append(html);
			}
		},
		error: function () {
			console.log("Error al cargar opciones de variante (Editar)");
		}
	});
}

/*=============================================
AGREGAR OPCIÓN DE VARIANTE EN EDITAR
=============================================*/
function agregarOpcionVarianteEditar(idTipo, idOpcion, nombreOpcion) {

	if (!opcionesVariantesSeleccionadasEditar[idTipo]) {
		opcionesVariantesSeleccionadasEditar[idTipo] = [];
	}

	var existe = opcionesVariantesSeleccionadasEditar[idTipo].find(function (op) {
		return op.id === idOpcion;
	});

	if (!existe) {
		opcionesVariantesSeleccionadasEditar[idTipo].push({
			id: idOpcion,
			nombre: nombreOpcion
		});
	}

	generarCombinacionesEditar();

	console.log("Opciones seleccionadas (Editar):", opcionesVariantesSeleccionadasEditar);
}

/*=============================================
REMOVER OPCIÓN DE VARIANTE EN EDITAR
=============================================*/
function removerOpcionVarianteEditar(idTipo, idOpcion) {

	if (opcionesVariantesSeleccionadasEditar[idTipo]) {

		opcionesVariantesSeleccionadasEditar[idTipo] = opcionesVariantesSeleccionadasEditar[idTipo].filter(function (op) {
			return op.id !== idOpcion;
		});

		if (opcionesVariantesSeleccionadasEditar[idTipo].length === 0) {
			delete opcionesVariantesSeleccionadasEditar[idTipo];
		}
	}

	generarCombinacionesEditar();

	console.log("Opciones seleccionadas (Editar):", opcionesVariantesSeleccionadasEditar);
}

/*=============================================
GENERAR COMBINACIONES EN EDITAR
=============================================*/
function generarCombinacionesEditar() {

	// Asegurar que variantesExistentes está inicializado
	if (typeof variantesExistentes === 'undefined' || variantesExistentes === null) {
		variantesExistentes = {};
	}

	var tiposConOpciones = Object.keys(opcionesVariantesSeleccionadasEditar);

	if (tiposConOpciones.length === 0) {
		$("#combinacionesEditarContainer").hide();
		return;
	}

	var combinaciones = generarProductoCartesiano(opcionesVariantesSeleccionadasEditar);

	console.log("Combinaciones generadas (Editar):", combinaciones);

	if (combinaciones.length === 0) {
		$("#combinacionesEditarContainer").hide();
		return;
	}

	$("#combinacionesEditarContainer").show();

	var html = '<table class="table table-bordered table-condensed">';
	html += '<thead>';
	html += '<tr>';
	html += '<th width="50px"><input type="checkbox" id="checkTodasCombinacionesEditar"></th>';
	html += '<th>Variante</th>';
	html += '<th width="150px">Precio Adicional (+/-)</th>';
	html += '<th width="100px">Stock</th>';
	html += '</tr>';
	html += '</thead>';
	html += '<tbody>';

	for (var i = 0; i < combinaciones.length; i++) {

		var nombreCombinacion = combinaciones[i].map(function (opt) {
			return opt.nombre;
		}).join(' - ');

		var idsCombinacion = combinaciones[i].map(function (opt) {
			return opt.id;
		}).join('_');

		// Verificar si esta variante ya existe
		var varianteExiste = variantesExistentes[idsCombinacion];
		var precioActual = varianteExiste ? varianteExiste.precio_adicional : '';
		var stockActual = varianteExiste ? varianteExiste.stock : '';
		var idVarianteExistente = varianteExiste ? varianteExiste.id : '';
		var estiloFila = varianteExiste ? ' style="background-color: #ffffcc;"' : '';
		var etiquetaExistente = varianteExiste ? ' <span class="label label-info">Existente</span>' : '';

		html += '<tr class="filaCombinacionEditar" data-index="' + i + '"' + estiloFila + '>';
		html += '<td><input type="checkbox" class="checkCombinacionEditar" data-index="' + i + '" data-idscombinacion="' + idsCombinacion + '" checked></td>';
		html += '<td>' + nombreCombinacion + etiquetaExistente + '</td>';
		html += '<td>';
		html += '<input type="number" class="form-control input-sm" name="precioAdicionalEditar_' + idsCombinacion + '" placeholder="0" step="0.01" value="' + precioActual + '">';
		html += '<small class="text-muted">Se sumará al precio base</small>';
		html += '</td>';
		html += '<td>';
		html += '<input type="number" class="form-control input-sm" name="stockVarianteEditar_' + idsCombinacion + '" placeholder="Stock base" min="0" value="' + stockActual + '">';
		html += '</td>';
		html += '</tr>';

		html += '<input type="hidden" name="combinacionEditar_' + i + '_ids" value="' + idsCombinacion + '" class="hiddenCombinacionEditar" data-index="' + i + '">';
		html += '<input type="hidden" name="combinacionEditar_' + i + '_nombre" value="' + nombreCombinacion + '" class="hiddenCombinacionEditar" data-index="' + i + '">';

		// Si la variante existe, agregar campo con su ID para hacer UPDATE
		if (idVarianteExistente) {
			html += '<input type="hidden" name="idVarianteExistente_' + idsCombinacion + '" value="' + idVarianteExistente + '">';
		}
	}

	html += '</tbody>';
	html += '</table>';
	html += '<input type="hidden" name="totalCombinacionesEditar" value="' + combinaciones.length + '">';

	$("#listaCombinacionesEditar").html(html);

	$("#checkTodasCombinacionesEditar").change(function () {
		var checked = $(this).prop('checked');
		$(".checkCombinacionEditar").each(function () {
			$(this).prop('checked', checked);
			actualizarEstadoCombinacionEditar($(this));
		});
	});

	$(document).on('change', '.checkCombinacionEditar', function () {
		actualizarEstadoCombinacionEditar($(this));
	});
}

/*=============================================
ACTUALIZAR ESTADO DE COMBINACIÓN EN EDITAR
=============================================*/
function actualizarEstadoCombinacionEditar(checkbox) {

	var index = checkbox.attr('data-index');
	var checked = checkbox.prop('checked');

	if (!checked) {
		$('.hiddenCombinacionEditar[data-index="' + index + '"]').prop('disabled', true);
	} else {
		$('.hiddenCombinacionEditar[data-index="' + index + '"]').prop('disabled', false);
	}
}

/*=============================================
LIMPIAR VARIANTES EN EDITAR
=============================================*/
function limpiarVariantesEditar() {
	tiposVariantesSeleccionadosEditar = [];
	opcionesVariantesSeleccionadasEditar = {};
	datosVariantesEditar = {};

	idProductoEditando = null;
	variantesExistentes = {};

	$("#tiposVariantesEditarContainer").html('');
	$("#opcionesVariantesEditarContainer").html('').hide();
	$("#combinacionesEditarContainer").html('').hide();
	$("#listaCombinacionesEditar").html('');
}

/*=============================================
LIMPIAR BACKDROP AL CERRAR MODAL DE EDICIÓN
=============================================*/
$('#modalEditarProducto').on('hidden.bs.modal', function () {
	// Limpiar cualquier backdrop atascado
	$('.modal-backdrop').remove();
	$('body').removeClass('modal-open');
	$('body').css('padding-right', '');
});

/*=============================================
LIMPIAR BACKDROP AL CERRAR MODAL DE EDITAR VARIANTE
=============================================*/
$('#modalEditarVariante').on('hidden.bs.modal', function () {
	console.log("Modal de editar variante cerrado");
	// Limpiar cualquier backdrop atascado
	$('.modal-backdrop').remove();
	$('body').removeClass('modal-open');
	$('body').css('padding-right', '');

	// Limpiar los campos del formulario
	$("#idVariante").val('');
	$("#editarPrecioAdicionalVariante").val('');
	$("#editarStockVariante").val('');
});
