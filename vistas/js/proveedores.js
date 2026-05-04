console.log("✅ Archivo JS cargado correctamente");

$(document).ready(function () {
	console.log("✅ jQuery está funcionando");

	// Forzar inicialización limpia para asegurar modo responsivo
	$("table.tablaProveedores").DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax/proveedores.ajax.php",
				"type": "POST",
				"data": function (d) {
					d.csrf_token = $('meta[name="csrf-token"]').attr('content');
				}
			},
			"destroy": true,
			"order": [[0, "asc"]],
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
							finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
							finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';

							if (col.columnIndex === 6) {
								// Reconstruimos la celda editable de notas si está escondida
								var rowNode = api.row(rowIdx).node();
								var providerId = $(rowNode).find('.btnEditarProveedor').attr('idProveedor');
								var notasText = $(rowNode).find('.celda-notas-proveedor').text().trim();
								var placeholderAttr = (notasText === "") ? ' data-placeholder="Escribe una nota..."' : "";
								
								finalHtml += '<div contenteditable="true" class="celda-notas-proveedor" data-id="' + providerId + '"' + placeholderAttr + ' style="width:100%; outline:none; display:block; border:1px dashed #ccc; padding:8px; background:#fff9e6; margin-top:5px;">' + (notasText || "") + '</div>';
							} else {
								// El resto pasa su HTML tal cual (badges de productos, etc)
								finalHtml += '<span style="color:#333;">' + col.data + '</span>';
							}
							
							finalHtml += '</div>';
						});

						if (!hasHidden) return false;
						return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
					}
				}
			},
			"columnDefs": [
				{ "targets": 0, "responsivePriority": 1 }, // Nombre
				{ "targets": 7, "responsivePriority": 2, "orderable": false }, // Acciones
				{ "targets": 1, "responsivePriority": 3 }, // Nombre comercial
				{ "targets": 2, "responsivePriority": 4 }, // Celular
				{ "targets": 3, "responsivePriority": 5 }, // Correo
				{ "targets": 4, "responsivePriority": 6 }, // Dirección
				{ "targets": 5, "responsivePriority": 7 }, // Productos
				{ "targets": 6, "responsivePriority": 8 }  // Notas
			],
			"drawCallback": function (settings) {
				if (typeof inicializarPlaceholdersProveedores === "function") {
					inicializarPlaceholdersProveedores();
				}
			},
			"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
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

	// Inicializar Select2 al abrir los modales
	$('#modalAgregarProveedor, #modalEditarProveedor').on('shown.bs.modal', function () {
		if ($(".select2").length > 0) {
			$(".select2").select2({
				placeholder: "Seleccionar opción",
				allowClear: true
			});
		}
	});
});

// Inicializar placeholder en celdas vacías al cargar
function inicializarPlaceholdersProveedores() {
	$('.celda-notas-proveedor').each(function () {
		if ($(this).text().trim() === '') {
			$(this).attr('data-placeholder', 'Escribe una nota...');
		} else {
			$(this).removeAttr('data-placeholder');
		}
	});
}

// Ejecutar inicialización al cargar el documento
$(document).ready(function () {
	inicializarPlaceholdersProveedores();
});


/*=============================================
EDITAR PROVEEDOR
=============================================*/

$(".tablaProveedores").on("click", ".btnEditarProveedor", function () {
	var idProveedor = $(this).attr("idProveedor");
	console.log("ID Proveedor: " + idProveedor);

	// Rellenar el input hidden
	$('#modalEditarProveedor input[name="idProveedor"]').val(idProveedor);

	var datos = new FormData();
	datos.append("idProveedor", idProveedor);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url: "ajax/proveedores.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			//console.log("Respuesta AJAX:", respuesta);

			$("#editarProveedor").val(respuesta["nombre"]);
			$("#editarDocumento").val(respuesta["documento"]);
			$("#editarTipoDocumento").val(respuesta["tipo_documento_id"]);
			$("#editarMarca").val(respuesta["marca"]);
			$("#editarCelular").val(respuesta["celular"]);
			$("#editarCorreo").val(respuesta["correo"]);
			$("#editarDireccion").val(respuesta["direccion"]);
			$("#editarMunicipio").val(respuesta["municipio_id"]).trigger('change');
			$("#editarOrganizacion").val(respuesta["organizacion_id"]);

			// ✅ mostrar el modal
			//$('#modalEditarProveedor').modal('show');

		},

	})

});



$(".tablaProveedores").on("click", ".btnEliminarProveedor", function () {

	var idProveedor = $(this).attr("idProveedor");

	swal({

		title: '¿Esta seguro de borrar el proveedor?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar proveedor!'
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idProveedorEliminar", idProveedor);
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/proveedores.ajax.php",
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
							text: "El proveedor ha sido borrado correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location.reload();
							}
						});
					} else if (respuesta == "error_documentos_soporte") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El proveedor tiene documentos soporte asociados. Elimine o reasigne los documentos soporte antes de continuar.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else if (respuesta == "error_productos_asociados") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El proveedor tiene productos asociados.",
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
			})
		}
	})
})


// La lógica de guardado de notas se ha movido directamente a proveedores.php 
// para asegurar la carga y evitar problemas de caché.