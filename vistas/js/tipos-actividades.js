/*=============================================
DATATABLE TIPOS
=============================================*/
/*=============================================
DATATABLE TIPOS
=============================================*/
if (!$.fn.DataTable.isDataTable('.tablaTiposActividades')) {
	$(".tablaTiposActividades").DataTable({

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

	var idTipo = $(this).attr("idTipo");
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