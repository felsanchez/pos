/*=============================================
EDITAR Actividades
=============================================*/

//console log
//console.log("Datos completos:", datos);

/*=============================================
EDITAR Actividades
=============================================*/
$(document).on("click", ".btnEditarActividad", function () {
	var idActividad = $(this).attr("idActividad");
	console.log("ID Actividad: " + idActividad);

	// Rellenar el input hidden
	$('#modalEditarActividad input[name="idActividad"]').val(idActividad);

	var datos = new FormData();
	datos.append("idActividad", idActividad);
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

	$.ajax({

		url: "ajax/actividades.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			//console.log("Respuesta AJAX:", respuesta);

			//$("#idActividad").val(respuesta["id"]);
			$("#editarActividad").val(respuesta["descripcion"]);
			$("#editarTipo").val(respuesta["tipo"]);
			$("#editarUsuario").val(respuesta["id_user"]);
			//$("#editarFecha").val(respuesta["fecha"]);

			if (respuesta.fecha) {
				$("#editarFecha").val(respuesta.fecha.substring(0, 10));
			} else {
				console.error("Fecha no válida:", respuesta.fecha);
			}

			$("#editarEstado").val(respuesta["estado"]);

			// Si no hay cliente, seleccionar "Sin cliente" (value="0")
			if (!respuesta["id_cliente"] || respuesta["id_cliente"] == 0 || respuesta["id_cliente"] == null || respuesta["id_cliente"] == "") {
				$("#editarCliente").val("0");
			} else {
				$("#editarCliente").val(respuesta["id_cliente"]);
			}

			$("#editarObservacion").val(respuesta["observacion"]);

			// ✅ mostrar el modal
			$('#modalEditarActividad').modal('show');


		},

		//error: function(xhr, status, error){
		//console.error("Error en AJAX:", xhr.responseText);
		//}

	})

});



/*=============================================
ELIMINAR Actividad
=============================================*/
$(document).on("click", ".btnEliminarActividad", function () {

	var idActividad = $(this).attr("idActividad");

	swal({

		title: '¿Esta seguro de borrar la actividad?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar actividad!'
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idActividadEliminar", idActividad);
			datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

			$.ajax({
				url: "ajax/actividades.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							type: "success",
							title: "¡Eliminada!",
							text: "La actividad ha sido eliminada correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location.reload();
							}
						});
					} else {
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo eliminar la actividad. " + respuesta,
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
Guardar Tipo - DESACTIVADO (campo ahora es solo lectura)
=============================================*/
/*
$(".tablas").on("change", ".cambiarTipo", function() {
	var idActividad = $(this).data("id");
	var nuevoTipo = $(this).val();

	console.log("Voy a enviar AJAX con id:", idActividad, "y nuevoTipo:", nuevoTipo);

	$.ajax({
		url: "ajax/actividades.ajax.php",
		method: "POST",
		data: { idActividad: idActividad, nuevoTipo: nuevoTipo },
		success: function(respuesta) {

			//console.log("Respuesta RAW:", respuesta);

			var datos = JSON.parse(respuesta);
			//console.log("Respuesta al cambiar tipo:", datos);
			if (datos.status === "error") {
				alert("Hubo un error al actualizar");
			} else {
				alert("Tipo actualizado correctamente a: " + datos.tipo);
				// Aquí puedes actualizar el valor mostrado en la tabla, si quieres
			}
		}

	});
});
*/


/*=============================================
Guardar Estado - DESACTIVADO (campo ahora es solo lectura)
=============================================*/
/*
$(".tablas").on("change", ".cambiarEstado", function() {
	var idActividad = $(this).data("id");
	var nuevoEstado = $(this).val();

	console.log("Voy a enviar AJAX con id:", idActividad, "y nuevoEstado:", nuevoEstado);

	$.ajax({
		url: "ajax/actividades.ajax.php",
		method: "POST",
		data: { idActividad: idActividad, nuevoEstado: nuevoEstado },
		success: function(respuesta) {

			//console.log("Respuesta RAW:", respuesta);

			var datos = JSON.parse(respuesta);
			//console.log("Respuesta al cambiar tipo:", datos);
			if (datos.status === "error") {
				alert("Hubo un error al actualizar");
			} else {
				alert("Estado actualizado correctamente a: " + datos.estado);
				// Aquí puedes actualizar el valor mostrado en la tabla, si quieres
			}
		}

	});
});
*/


/*=============================================
Editar Estado - MOVIDO A estados-actividades.js
=============================================*/
// Código comentado porque ahora se maneja en estados-actividades.js
// $("#modalGestionarEstados").on("click", ".btnEditarEstadoActividad", function(){
// 	var idEstado = $(this).attr("idEstado");
//     console.log("ID Estado: " + idEstado);
// 	$('#modalEditarEstadoActividad input[name="idEstado"]').val(idEstado);
// 	var datos = new FormData();
// 	datos.append("idEstado", idEstado);
// 	$.ajax({
// 		url:"ajax/estados-actividades.ajax.php",
// 		method: "POST",
// 		data: datos,
// 		cache: false,
// 		contentType: false,
// 		processData: false,
// 		dataType: "json",
// 		success: function(respuesta){
// 			console.log("Respuesta AJAX Estado:", respuesta);
// 			$("#editarEstadoNombre").val(respuesta["nombre"]);
// 			$("#editarEstadoColor").val(respuesta["color"]);
// 			$("#editarEstadoOrden").val(respuesta["orden"]);
// 		},
// 		error: function(xhr, status, error){
// 			console.error("Error AJAX:", error);
// 			swal({
// 				type: "error",
// 				title: "Error",
// 				text: "No se pudieron cargar los datos del estado"
// 			});
// 		}
// 	})
// })

// Restaurar scroll al cerrar modal - MOVIDO A estados-actividades.js
// $("#modalEditarEstadoActividad").on("hidden.bs.modal", function(){
// 	if($("#modalGestionarEstados").hasClass("in")){
// 		$("body").addClass("modal-open");
// 	}
// });

// Debug código - COMENTADO
// $(document).ready(function(){
// 	$(document).on("keydown keypress keyup input", "#editarEstadoNombre", function(e){
// 		console.log("Evento detectado en editarEstadoNombre:", e.type, "Key:", e.key);
// 		return true;
// 	});
// 	$(document).on("click", "#editarEstadoNombre", function(e){
// 		console.log("Click en editarEstadoNombre detectado");
// 	});
// });


/*=============================================
Eliminar Estado - MOVIDO A estados-actividades.js
=============================================*/
// Código comentado porque ahora se maneja en estados-actividades.js
// $(".btnEliminarEstadoActividad").click(function(){
// 	var idEstado = $(this).attr("idEstado");
// 	var nombreEstado = $(this).attr("nombreEstado");
// 	swal({
// 		title: '¿Está seguro de borrar el estado "'+nombreEstado+'"?',
// 		text: "¡Si no lo está puede cancelar la acción!",
// 		type: 'warning',
// 		showCancelButton: true,
// 		confirmButtonColor: '#3085d6',
// 		cancelButtonColor: '#d33',
// 		cancelButtonText: 'Cancelar',
// 		confirmButtonText: 'Sí, borrar estado!'
// 	}).then(function(result){
// 		if(result.value){
// 			window.location = "index.php?ruta=actividades&idEstado="+idEstado+"&nombreEstado="+nombreEstado+"&origen=actividades";
// 		}
// 	})
// })


/*=============================================
Editar Tipo desde Modal de Gestión
=============================================*/
$("#modalGestionarTipos").on("click", ".btnEditarTipoActividad", function () {

	var idTipo = $(this).attr("idTipo");
	console.log("ID Tipo: " + idTipo);

	// Rellenar el input hidden
	$('#modalEditarTipoActividad input[name="idTipo"]').val(idTipo);

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

			console.log("Respuesta AJAX Tipo:", respuesta);

			$("#editarTipoNombre").val(respuesta["nombre"]);
			$("#editarTipoOrden").val(respuesta["orden"]);

		},
		error: function (xhr, status, error) {
			console.error("Error AJAX:", error);
			console.error("Status:", status);
			console.error("Respuesta completa:", xhr.responseText);

			swal({
				type: "error",
				title: "Error al cargar el tipo",
				text: "No se pudieron cargar los datos del tipo"
			});
		}

	})

})

/*=============================================
Restaurar scroll al cerrar modal de edición de tipos
=============================================*/
$("#modalEditarTipoActividad").on("hidden.bs.modal", function () {
	// Si el modal de gestión sigue abierto, restaurar la clase modal-open
	if ($("#modalGestionarTipos").hasClass("in")) {
		$("body").addClass("modal-open");
	}
});

/*=============================================
Eliminar Tipo
=============================================*/
$(document).on("click", ".btnEliminarTipoActividad", function () {

	var idTipo = $(this).attr("idTipo");
	var nombreTipo = $(this).attr("nombreTipo");

	swal({
		title: '¿Está seguro de borrar el tipo "' + nombreTipo + '"?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, borrar tipo!'
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

	})

})

/*=============================================
FILTROS DE DATATABLES - TIPO Y ESTADO
=============================================*/
$(document).ready(function () {
	var filtroTipoActual = '';
	var filtroEstadoActual = '';

	// Crear filtro personalizado que busca en los atributos data-* del <tr>
	$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
		// Solo aplicar este filtro a la tabla de actividades
		if (!$(settings.nTable).hasClass('tablas')) {
			return true;
		}

		// Obtener la fila actual
		var row = $(settings.aoData[dataIndex].nTr);

		// Obtener los valores de los atributos data-*
		var rowTipo = row.attr('data-tipo') || '';
		var rowEstado = row.attr('data-estado') || '';

		// Aplicar filtros
		var pasaTipoFiltro = true;
		var pasaEstadoFiltro = true;

		if (filtroTipoActual !== '') {
			pasaTipoFiltro = (rowTipo.toLowerCase() === filtroTipoActual.toLowerCase());
		}

		if (filtroEstadoActual !== '') {
			pasaEstadoFiltro = (rowEstado.toLowerCase() === filtroEstadoActual.toLowerCase());
		}

		return pasaTipoFiltro && pasaEstadoFiltro;
	});

	// Esperar a que DataTable esté inicializado
	setTimeout(function () {
		var table = $('.tablas').DataTable();

		// Filtro por Tipo
		$('#filtroTipo').on('change', function () {
			filtroTipoActual = this.value;
			console.log('Filtrando por Tipo:', filtroTipoActual);
			table.draw();
		});

		// Filtro por Estado
		$('#filtroEstado').on('change', function () {
			filtroEstadoActual = this.value;
			console.log('Filtrando por Estado:', filtroEstadoActual);
			table.draw();
		});
	}, 500);
});