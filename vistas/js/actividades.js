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
	// csrf_token removido - manejado por csrf-helper.js

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
			// csrf_token removido - manejado por csrf-helper.js

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
	// csrf_token removido - manejado por csrf-helper.js

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
			// csrf_token removido - manejado por csrf-helper.js

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
TABLA ACTIVIDADES CON CONFIGURACIÓN ESPECIAL
=============================================*/
$(document).ready(function () {

	// Mover modales al body para evitar conflictos de z-index con AdminLTE
	$('#modalAgregarActividad').appendTo("body");
	$('#modalEditarActividad').appendTo("body");
	$('#modalGestionarEstados').appendTo("body");
	$('#modalEditarEstadoActividad').appendTo("body");
	$('#modalGestionarTipos').appendTo("body");
	$('#modalEditarTipoActividad').appendTo("body"); // <- Added this

	// Evento para arreglar z-index de nested modals
	$('#modalEditarTipoActividad').off('shown.bs.modal').on('shown.bs.modal', function () {
		// El modal se pone por encima del segundo backdrop
		$(this).css('z-index', 1060);
		// Ajustar z-index de backdrops
		var backdrops = $('.modal-backdrop');
		if (backdrops.length >= 2) {
			$(backdrops[backdrops.length - 1]).css('z-index', 1055);
		}
	});

	// Inicializar Select2 para los filtros
	if (typeof $.fn.select2 !== 'undefined') {
		$("#filtroTipo").select2({
			placeholder: "Seleccionar tipo...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
		$("#filtroEstado").select2({
			placeholder: "Seleccionar estado...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
	}

	// Verificar si existe la tabla antes de inicializar
	if ($(".tablaActividades").length > 0) {
		// Destruir instancia previa si existe por alguna razón
		if ($.fn.DataTable.isDataTable('.tablaActividades')) {
			$('.tablaActividades').DataTable().destroy();
		}

		$(".tablaActividades").DataTable({
			"ajax": {
				"url": "ajax/actividades.ajax.php",
				"type": "POST",
				"data": function(d) {
					d.draw = d.draw || 1; // Usado por DataTables, el controlador lo lee.
					d.filtroTipo = $('#filtroTipo').val() || '';
					d.filtroEstado = $('#filtroEstado').val() || '';
					
					// Token CSRF obligatorio
					d.csrf_token = $('meta[name="csrf-token"]').attr('content') || '';
				}
			},
			"serverSide": true,
			"processing": true,
			"deferRender": true,
			"order": [[0, "asc"]], // Ordenar por Descripción (columna 0)
			"columnDefs": [
				{ "targets": 0, "responsivePriority": 1 }, // Descripción
				{ "targets": 7, "responsivePriority": 2, "orderable": false }, // Acciones
				{ "targets": 1, "responsivePriority": 3 }, // Tipo
				{ "targets": 2, "responsivePriority": 4 }, // Responsable
				{ "targets": 3, "responsivePriority": 5 }, // Fecha
				{ "targets": 4, "responsivePriority": 6 }, // Estado
				{ "targets": 5, "responsivePriority": 7 }, // Cliente
				{ "targets": 6, "responsivePriority": 8 }  // Notas / Observacion
			],
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

							if (col.columnIndex === 6) {
								// Reconstruimos la celda editable de observación
								var rowNode = api.row(rowIdx).node();
								var idActividad = $(rowNode).attr('data-actividad-id') || "";
								var observacionText = $(rowNode).find('.celda-observacion').text().trim();
								var placeholderAttr = (observacionText === "") ? ' data-placeholder="Escribe una nota.."' : "";
								
								finalHtml += '<div contenteditable="true" class="celda-observacion" data-id="' + idActividad + '"' + placeholderAttr + ' style="width:100%; outline:none; display:block; border:1px dashed #ccc; padding:8px; background:#fff9e6; margin-top:5px;">' + observacionText + '</div>';
							} else {
								// El resto pasa su HTML o texto tal cual
								finalHtml += '<span style="color:#333;">' + col.data + '</span>';
							}
							
							finalHtml += '</div>';
						});

						if (!hasHidden) return false;
						return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
					}
				}
			},
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
			"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
			"drawCallback": function (settings) {
				// Inicializar Tippy en cada redibujado de la tabla (paginación, búsqueda, etc.)
				if (typeof tippy === 'function') {
					tippy('.has-tooltip', {
						theme: 'pos-premium',
						allowHTML: true,
						placement: 'top',
						arrow: true,
						animation: 'shift-away',
						delay: [150, 0],
						interactive: true,
						appendTo: document.body,
						zIndex: 9999
					});
				}

				// Inicializar placeholders en las celdas de la tabla
				inicializarPlaceholders();
			},
			"initComplete": function () {
				// Asegurar visibilidad tras inicializar
				$(".tablaActividades").addClass("datatable-ready").css("visibility", "visible");
			}
		});

		var table = $('.tablaActividades').DataTable();

		// Filtro por Tipo
		$('#filtroTipo').on('change', function () {
			console.log('Filtrando por Tipo:', this.value);
			table.draw();
		});

		// Filtro por Estado
		$('#filtroEstado').on('change', function () {
			console.log('Filtrando por Estado:', this.value);
			table.draw();
		});
	}
});

/*=============================================
AUTOGUARDADO DE OBSERVACIONES (DELEGADO)
=============================================*/

// Función para inicializar placeholders en celdas vacías
function inicializarPlaceholders() {
	$('.celda-observacion').each(function () {
		// Solo establecer si no tiene ya uno definido
		if (!$(this).attr('data-placeholder')) {
			var texto = (window.location.search.includes('ruta=actividades')) ? 'Escribe una nota..' : 'Escribe una observación...';
			$(this).attr('data-placeholder', texto);
		}
	});
}

// Inicializar al cargar el documento
$(document).ready(function () {
	inicializarPlaceholders();
});

$(document).on('focus', '.celda-observacion', function () {
	// Ya no eliminamos el placeholder al enfocar para evitar saltos de texto o cambios de fallback
});

$(document).on('blur', '.celda-observacion', function () {
	var elemento = $(this);
	var id = elemento.attr('data-id'); // Usamos .attr() para mayor compatibilidad con elementos dinámicos
	var nuevaObservacion = elemento.text().trim();

	// Ya no es necesario manipular el atributo data-placeholder en JS
	// El CSS (:empty:not(:focus)) se encarga de mostrarlo/ocultarlo correctamente

	// Obtener token CSRF del meta tag (doble chequeo)
	var csrfToken = $('meta[name="csrf-token"]').attr('content');

	// Guardar en la base de datos vía AJAX
	$.ajax({
		url: 'ajax/actividades.ajax.php',
		method: 'POST',
		data: {
			id: id,
			observacion: nuevaObservacion,
			accion: 'actualizarObservacion',
			csrf_token: csrfToken // Enviar explícitamente por si acaso ajaxSetup no lo capturó
		},
		dataType: 'json',
		success: function (respuesta) {
			if (respuesta == "ok") {
				console.log('✅ Nota guardada correctamente (ID: ' + id + ')');
				// Opcional: mostrar un pequeño feedback visual momentáneo
				elemento.css('background-color', '#dff0d8');
				setTimeout(function () {
					elemento.css('background-color', '');
				}, 500);
			} else {
				console.error('❌ Error al guardar:', respuesta);
			}
		},
		error: function (xhr, status, error) {
			console.error('❌ Error AJAX:', status, error);
			alert('No se pudo guardar la nota. Por favor, intenta de nuevo.');
		}
	});
});

/*=============================================
FILTROS PERSONALIZADOS DATATABLES
=============================================*/
// Los filtros ahora se manejan del lado del servidor (Server-Side) mediante ajax.data
// en la inicialización de la tabla.

/*=============================================
CALENDARIO DE ACTIVIDADES
=============================================*/
document.addEventListener('DOMContentLoaded', function () {
	var calendarEl = document.getElementById('calendar');
	if (calendarEl && typeof FullCalendar !== 'undefined') {
		var calendar = new FullCalendar.Calendar(calendarEl, {
			locale: 'es',
			initialView: 'dayGridMonth',
			eventTimeFormat: {
				hour: '2-digit',
				minute: '2-digit',
				hour12: false
			},
			events: 'ajax/eventos.php'
		});
		calendar.render();
	}
});