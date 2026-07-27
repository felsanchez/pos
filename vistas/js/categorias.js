/*=============================================
EDITAR CATEGORIA
=============================================*/

$(".tablas").on("click", ".btnEditarCategoria", function(){

	var idCategoria = $(this).attr("idCategoria");

	var datos = new FormData();
	datos.append("idCategoria", idCategoria);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url:"ajax/categorias.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			$("#editarCategoria").val(respuesta["categoria"]);
			$("#editarPrefijo").val(respuesta["prefijo"]);
			$("#idCategoria").val(respuesta["id"]);

		}

	})


})


$(".tablas").on("click", ".btnEliminarCategoria", function () {

	var idCategoria = $(this).attr("idCategoria");

	// Primero verificar si tiene productos activos asociados
	var datosVerificacion = new FormData();
	datosVerificacion.append("idCategoriaVerificarRelaciones", idCategoria);

	$.ajax({
		url: "ajax/categorias.ajax.php",
		method: "POST",
		data: datosVerificacion,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			if (respuesta.status === "success" && respuesta.tieneProductosActivos) {
				if (respuesta.tipo === "otra_sucursal") {
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				} else {
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "La categoría tiene productos asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				}
				return;
			}

			// Proceder con la confirmación de borrado
			swal({
				title: '¿Esta seguro de borrar la categoría?',
				text: "¡Si no lo está puede cancelar la acción!",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancelar',
				confirmButtonText: 'Si, borrar categoría!'
			}).then((result) => {
				if (result.value) {
					var datos = new FormData();
					datos.append("idCategoriaEliminar", idCategoria);

					$.ajax({
						url: "ajax/categorias.ajax.php",
						method: "POST",
						data: datos,
						cache: false,
						contentType: false,
						processData: false,
						success: function (respuesta) {
							if (respuesta == "ok") {
								swal({
									type: "success",
									title: "¡Borrado correctamente!",
									text: "La categoría ha sido borrada correctamente.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								}).then((result) => {
									if ($.fn.DataTable.isDataTable('.tablaCategorias')) {
										$('.tablaCategorias').DataTable().ajax.reload(null, false);
									} else {
										window.location.reload();
									}
								});
							} else if (respuesta == "error_productos_asociados_otra_sucursal") {
								swal({
									type: "error",
									title: "¡No se puede eliminar!",
									text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								});
							} else if (respuesta == "error_productos_asociados") {
								swal({
									type: "error",
									title: "¡No se puede eliminar!",
									text: "La categoría tiene productos asociados.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
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
					});
				}
			});
		},
		error: function () {
			// Fallback si la verificación falla
			swal({
				title: '¿Esta seguro de borrar la categoría?',
				text: "¡Si no lo está puede cancelar la acción!",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancelar',
				confirmButtonText: 'Si, borrar categoría!'
			}).then((result) => {
				if (result.value) {
					var datos = new FormData();
					datos.append("idCategoriaEliminar", idCategoria);

					$.ajax({
						url: "ajax/categorias.ajax.php",
						method: "POST",
						data: datos,
						cache: false,
						contentType: false,
						processData: false,
						success: function (respuesta) {
							if (respuesta == "ok") {
								swal({
									type: "success",
									title: "¡Borrado correctamente!",
									text: "La categoría ha sido borrada correctamente.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								}).then((result) => {
									if ($.fn.DataTable.isDataTable('.tablaCategorias')) {
										$('.tablaCategorias').DataTable().ajax.reload(null, false);
									} else {
										window.location.reload();
									}
								});
							} else if (respuesta == "error_productos_asociados_otra_sucursal") {
								swal({
									type: "error",
									title: "¡No se puede eliminar!",
									text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								});
							} else if (respuesta == "error_productos_asociados") {
								swal({
									type: "error",
									title: "¡No se puede eliminar!",
									text: "La categoría tiene productos asociados.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
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
					});
				}
			});
		}
	});

})


/*=============================================
HPM REVISAR SI LA CATEGORIA YA ESTA REGISTRADA
=============================================*/

$("#nuevaCategoria").change(function(){

	$(".alert").remove();

	var categoria = $(this).val();

	var datos = new FormData();
	datos.append("validarCategoria", categoria);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({
		url:"ajax/categorias.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			if(respuesta){

				$("#nuevaCategoria").parent().after('<div class="alert alert-warning">Esta categoría ya existe en la base de datos!</div>');

				$("#nuevaCategoria").val("");
			}

		}
	})
})

/*=============================================
VALIDAR NO REPETIR PREFIJO (CREAR)
=============================================*/
$(document).on("change", "#nuevoPrefijo", function(){

	$(".alert").remove();

	var prefijo = $(this).val();

	if (prefijo === "") return;

	var datos = new FormData();
	datos.append("validarPrefijo", prefijo);

	$.ajax({
		url:"ajax/categorias.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			if(respuesta){

				$("#nuevoPrefijo").parent().after('<div class="alert alert-warning">Este prefijo ya está siendo usado por otra categoría!</div>');

				$("#nuevoPrefijo").val("");
			}

		}
	})
})

/*=============================================
VALIDAR NO REPETIR PREFIJO (EDITAR)
=============================================*/
$(document).on("change", "#editarPrefijo", function(){

	$(".alert").remove();

	var prefijo = $(this).val();
	var idCategoria = $("#idCategoria").val();

	if (prefijo === "") return;

	var datos = new FormData();
	datos.append("validarPrefijo", prefijo);
	datos.append("idCategoriaActual", idCategoria);

	$.ajax({
		url:"ajax/categorias.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			if(respuesta){

				$("#editarPrefijo").parent().after('<div class="alert alert-warning">Este prefijo ya está siendo usado por otra categoría!</div>');

				$("#editarPrefijo").val("");
			}

		}
	})
})

/*=============================================
TABLA CATEGORIAS - SERVER-SIDE
=============================================*/
$(document).ready(function () {
	if ($(".tablaCategorias").length > 0) {
		if ($.fn.DataTable.isDataTable('.tablaCategorias')) {
			$('.tablaCategorias').DataTable().destroy();
		}

		$(".tablaCategorias").DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax/categorias.ajax.php",
				"type": "POST",
				"data": function (d) {
					d.csrf_token = $('meta[name="csrf-token"]').attr('content');
				}
			},
			"order": [[0, 'asc']],
			"autoWidth": false,
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
			"columnDefs": [
				{ "targets": 0, "responsivePriority": 1 }, // Categoría
				{ "targets": 1, "responsivePriority": 4 }, // Prefijo
				{ "targets": 2, "responsivePriority": 3 }, // Productos
				{ "targets": 3, "responsivePriority": 2, "orderable": false } // Acciones
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
			}
		});
	}

	/*=============================================
	GUARDAR CREAR CATEGORÍA VÍA AJAX
	=============================================*/
	$(document).on("submit", "#formAgregarCategoria", function (e) {
		e.preventDefault();

		var form = this;
		var boton = $(form).find("button[type='submit']");
		boton.prop('disabled', true);
		var htmlOriginal = boton.html();
		boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

		swal({
			title: 'Guardando categoría',
			text: 'Por favor espere mientras se procesa la información...',
			type: 'info',
			allowOutsideClick: false,
			showConfirmButton: false,
			onBeforeOpen: () => {
				swal.showLoading()
			}
		});

		var datos = new FormData(form);
		datos.append("guardarCrearCategoria", "ok");

		$.ajax({
			url: "ajax/categorias.ajax.php",
			method: "POST",
			data: datos,
			cache: false,
			contentType: false,
			processData: false,
			dataType: "json",
			success: function (respuesta) {
				boton.prop('disabled', false).html(htmlOriginal);

				if (respuesta.status === "ok") {
					swal({
						type: "success",
						title: "¡Éxito!",
						text: respuesta.mensaje,
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						$("#modalAgregarCategoria").modal("hide");
						form.reset();
						if ($.fn.DataTable.isDataTable('.tablaCategorias')) {
							$('.tablaCategorias').DataTable().ajax.reload(null, false);
						} else {
							window.location.reload();
						}
					});
				} else {
					swal({
						type: "error",
						title: "¡Error!",
						text: respuesta.mensaje || "No se pudo guardar la categoría.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				}
			},
			error: function () {
				boton.prop('disabled', false).html(htmlOriginal);
				swal({
					type: "error",
					title: "¡Error!",
					text: "Ocurrió un problema de conexión al guardar la categoría.",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				});
			}
		});
	});

	/*=============================================
	GUARDAR EDITAR CATEGORÍA VÍA AJAX
	=============================================*/
	$(document).on("submit", "#formEditarCategoria", function (e) {
		e.preventDefault();

		var form = this;
		var boton = $(form).find("button[type='submit']");
		boton.prop('disabled', true);
		var htmlOriginal = boton.html();
		boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

		swal({
			title: 'Actualizando categoría',
			text: 'Por favor espere mientras se procesa la información...',
			type: 'info',
			allowOutsideClick: false,
			showConfirmButton: false,
			onBeforeOpen: () => {
				swal.showLoading()
			}
		});

		var datos = new FormData(form);
		datos.append("guardarEditarCategoria", "ok");

		$.ajax({
			url: "ajax/categorias.ajax.php",
			method: "POST",
			data: datos,
			cache: false,
			contentType: false,
			processData: false,
			dataType: "json",
			success: function (respuesta) {
				boton.prop('disabled', false).html(htmlOriginal);

				if (respuesta.status === "ok") {
					swal({
						type: "success",
						title: "¡Éxito!",
						text: respuesta.mensaje,
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						$("#modalEditarCategoria").modal("hide");
						if ($.fn.DataTable.isDataTable('.tablaCategorias')) {
							$('.tablaCategorias').DataTable().ajax.reload(null, false);
						} else {
							window.location.reload();
						}
					});
				} else {
					swal({
						type: "error",
						title: "¡Error!",
						text: respuesta.mensaje || "No se pudo actualizar la categoría.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				}
			},
			error: function () {
				boton.prop('disabled', false).html(htmlOriginal);
				swal({
					type: "error",
					title: "¡Error!",
					text: "Ocurrió un problema de conexión al actualizar la categoría.",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				});
			}
		});
	});
});