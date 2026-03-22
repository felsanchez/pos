// Prevenir ejecución múltiple del script
if (typeof window.estadosClientesJsLoaded === 'undefined') {
	window.estadosClientesJsLoaded = true;

/*=============================================
DATATABLE ESTADOS
=============================================*/

// Solo inicializar si no está ya inicializado
if (!$.fn.DataTable.isDataTable('.tablaEstadosClientes')) {
	$(".tablaEstadosClientes").DataTable({

		"language": {
			"sProcessing":     "Procesando...",
			"sLengthMenu":     "Mostrar _MENU_ registros",
			"sZeroRecords":    "No se encontraron resultados",
			"sEmptyTable":     "Ningún dato disponible en esta tabla",
			"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
			"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
			"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
			"sInfoPostFix":    "",
			"sSearch":         "Buscar:",
			"sUrl":            "",
			"sInfoThousands":  ",",
			"sLoadingRecords": "Cargando...",
			"oPaginate": {
				"sFirst":    "Primero",
				"sLast":     "Último",
				"sNext":     "Siguiente",
				"sPrevious": "Anterior"
			},

			"oAria": {
				"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
				"sSortDescending": ": Activar para ordenar la columna de manera descendente"
			}
		}
	});
}
 

/*=============================================
EDITAR ESTADO - Capturar datos por click directo
=============================================*/

$(document).off("click", ".btnEditarEstado");
$(document).on("click", ".btnEditarEstado", function() {
	var id = $(this).attr('data-id');
	var nombre = $(this).attr('data-nombre');
	var color = $(this).attr('data-color');
	var orden = $(this).attr('data-orden');

	console.log("✓ Click en botón editar - ID:", id, "Nombre:", nombre, "Color:", color, "Orden:", orden);

	// Llenar el formulario del modal
	$('#idEstado').val(id);
	$('#editarEstadoNombre').val(nombre);
	$('#editarEstadoColor').val(color);
	$('#editarEstadoOrden').val(orden);
	
	// Abrir el modal manualmente por si acaso el data-toggle falla o para asegurar orden
	// $('#modalEditarEstado').modal('show');
});

// Evento cuando el modal se muestra completamente
$('#modalEditarEstado').off('shown.bs.modal').on('shown.bs.modal', function () {
	console.log("✅ Modal editar mostrado completamente");

	// Ajustar z-index de backdrops
	var backdrops = $('.modal-backdrop');
	console.log("Backdrops presentes:", backdrops.length);

	if (backdrops.length === 2) {
		$(backdrops[0]).css('z-index', 1040);
		$(backdrops[1]).css('z-index', 1055);
	}

	// Forzar focus en el campo de nombre
	$('#editarEstadoNombre').focus();
});

// Limpiar backdrops cuando se cierra el modal de Gestionar Estados
$('#modalGestionarEstados').off('hidden.bs.modal').on('hidden.bs.modal', function () {
	console.log("Modal Gestionar Estados cerrado - Limpiando backdrops");
	$('.modal-backdrop').remove();
	$('body').removeClass('modal-open');
	$('body').css('padding-right', '');
	$('body').css('overflow', '');
});

// Limpiar backdrops cuando se cierra el modal de Editar
$('#modalEditarEstado').off('hidden.bs.modal').on('hidden.bs.modal', function () {
	console.log("Modal Editar Estado cerrado");
	// Si el modal principal sigue abierto, restaurar modal-open
	if ($('#modalGestionarEstados').hasClass('in')) {
		$('body').addClass('modal-open');
	} else {
		$('.modal-backdrop').remove();
		$('body').removeClass('modal-open');
		$('body').css('padding-right', '');
		$('body').css('overflow', '');
	}
});


/*=============================================
ELIMINAR ESTADO
=============================================*/

// Remover evento anterior para evitar duplicados
$(document).off("click", ".btnEliminarEstado");

$(document).on("click", ".btnEliminarEstado", function(){

 	var idEstado = $(this).attr("idEstado");
	var nombreEstado = $(this).attr("nombreEstado");

	console.log("✓ Click en Eliminar Estado - ID:", idEstado, "Nombre:", nombreEstado);

	// Detectar si estamos en la página de clientes o contactos
	var rutaActual = window.location.href;
	var origen = "estados-clientes";

	if(rutaActual.indexOf("clientes") !== -1 && rutaActual.indexOf("estados-clientes") === -1) {
		origen = "clientes";
	} else if(rutaActual.indexOf("contactos") !== -1) {
		origen = "contactos";
	}

	// Mostrar SweetAlert directamente SIN cerrar modales primero
	swal({
		title: '¿Está seguro de eliminar el estado "' + nombreEstado + '"?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, eliminar estado!'
	}).then(function(result){

		if(result.value){
			console.log("Eliminando estado vía AJAX...");

			// Usar AJAX para eliminar en lugar de redireccionar
			var datos = new FormData();
			datos.append("idEstado", idEstado);
			datos.append("nombreEstado", nombreEstado);
			datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

			$.ajax({
				url: "ajax/estados-clientes-eliminar.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				dataType: "json",
				success: function(respuesta) {
					console.log("Respuesta eliminación:", respuesta);

					if(respuesta.status == "success") {
						// Solo cerrar modales si la eliminación fue exitosa
						$('.modal').modal('hide');
						$('body').removeClass('modal-open');
						$('.modal-backdrop').remove();
						$('body').css('padding-right', '');

						swal({
							type: "success",
							title: "¡Eliminado!",
							text: respuesta.message,
							confirmButtonText: "Cerrar"
						}).then(function() {
							// Recargar la página actual sin parámetros
							window.location = window.location.pathname + "?ruta=" + origen;
						});
					} else {
						// NO cerrar el modal, solo mostrar el error
						swal({
							type: "error",
							title: "No se puede eliminar",
							text: respuesta.message,
							confirmButtonText: "Cerrar"
						});
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error("Error AJAX:", textStatus, errorThrown);

					// NO cerrar el modal, solo mostrar el error
					swal({
						type: "error",
						title: "Error",
						text: "Ocurrió un error al eliminar el estado",
						confirmButtonText: "Cerrar"
					});
				}
			});
		}
		// Si cancela (result.value es false), no hacemos nada, el modal sigue abierto
	});

});

} // Fin de prevención de ejecución múltiple
