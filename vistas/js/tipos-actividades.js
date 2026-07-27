/*=============================================
DATATABLE TIPOS
=============================================*/
/*=============================================
DATATABLE TIPOS
=============================================*/
if (!$.fn.DataTable.isDataTable('.tablaTiposActividades')) {
	$("table.tablaTiposActividades").DataTable({

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
		}
	});
}


/*=============================================
EDITAR TIPO ACTIVIDAD
=============================================*/

$(".tablaTiposActividades").on("click", ".btnEditarTipoActividad", function () {

	var idTipo = $(this).attr("idTipo") || $(this).attr("data-id");
	var datos = new FormData();
	datos.append("idTipo", idTipo);
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

	$.ajax({
		url: "ajax/tipos-actividades.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			$("#idTipo").val(respuesta["id"]);
			$("#editarTipoNombre").val(respuesta["nombre"]);
			$("#editarTipoOrden").val(respuesta["orden"]);
		}
	});
});


/*=============================================
ELIMINAR TIPO ACTIVIDAD
=============================================*/

$(".tablaTiposActividades").on("click", ".btnEliminarTipoActividad", function () {

	var idTipo = $(this).attr("idTipo");
	var nombreTipo = $(this).attr("nombreTipo");

	swal({
		title: '¿Está seguro de eliminar el tipo?',
		text: "¡Puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, eliminar tipo'
	}).then(function (result) {

		if (result.value) {

			var datos = new FormData();
			datos.append("idTipoEliminar", idTipo);
			datos.append("nombreTipo", nombreTipo);
			datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

			$.ajax({
				url: "ajax/tipos-actividades.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							type: "success",
							title: "¡Eliminado!",
							text: "El tipo ha sido eliminado correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location.reload();
							}
						});
					} else if (respuesta == "error_en_uso") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "Este tipo está en uso por algunas actividades.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else {
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo eliminar el tipo. " + respuesta,
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					}
				}
			})
		}
	});

});

/*=============================================
GUARDAR CREAR TIPO DE ACTIVIDAD VÍA AJAX
=============================================*/
$(document).on("submit", "#formAgregarTipoActividad", function (e) {
	e.preventDefault();
	e.stopImmediatePropagation();

	var form = this;
	var boton = $(form).find("button[type='submit']");
	boton.prop('disabled', true);
	var htmlOriginal = boton.html();
	boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

	swal({
		title: 'Guardando tipo',
		text: 'Por favor espere mientras se procesa la información...',
		type: 'info',
		allowOutsideClick: false,
		showConfirmButton: false,
		onBeforeOpen: () => {
			swal.showLoading()
		}
	});

	var datos = new FormData(form);
	datos.append("guardarCrearTipoActividad", "ok");

	$.ajax({
		url: "ajax/tipos-actividades.ajax.php",
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
					window.location.href = window.location.pathname;
				});
			} else {
				swal({
					type: "error",
					title: "¡Error!",
					text: respuesta.mensaje || "No se pudo guardar el tipo.",
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
				text: "Ocurrió un problema de conexión al guardar el tipo.",
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			});
		}
	});
});

/*=============================================
GUARDAR EDITAR TIPO DE ACTIVIDAD VÍA AJAX
=============================================*/
$(document).on("submit", "#formEditarTipoActividad, #modalEditarTipoActividad form", function (e) {
	e.preventDefault();
	e.stopImmediatePropagation();

	var form = this;
	var boton = $(form).find("button[type='submit']");
	boton.prop('disabled', true);
	var htmlOriginal = boton.html();
	boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

	swal({
		title: 'Actualizando tipo',
		text: 'Por favor espere mientras se procesa la información...',
		type: 'info',
		allowOutsideClick: false,
		showConfirmButton: false,
		onBeforeOpen: () => {
			swal.showLoading()
		}
	});

	var datos = new FormData(form);
	datos.append("guardarEditarTipoActividad", "ok");

	$.ajax({
		url: "ajax/tipos-actividades.ajax.php",
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
					$("#modalEditarTipoActividad").modal("hide");
					window.location.href = window.location.pathname;
				});
			} else {
				swal({
					type: "error",
					title: "¡Error!",
					text: respuesta.mensaje || "No se pudo actualizar el tipo.",
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
				text: "Ocurrió un problema de conexión al actualizar el tipo.",
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			});
		}
	});
});