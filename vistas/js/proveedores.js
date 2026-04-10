console.log("✅ Archivo JS cargado correctamente");

$(document).ready(function () {
	console.log("✅ jQuery está funcionando");

	// Verificar si la tabla ya está inicializada
	if (!$.fn.DataTable.isDataTable('.tablaProveedores')) {
		$(".tablaProveedores").DataTable({
			"order": [[1, "asc"]],
			"responsive": {
				"details": {
					"type": "column",
					"target": 0,
					"renderer": function (api, rowIdx, columns) {
						var data = $.map(columns, function (col, i) {
							return col.hidden ?
								'<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
								'<td>' + col.title + ':' + '</td> ' +
								'<td>' + col.data + '</td>' +
								'</tr>' :
								'';
						}).join('');

						// Custom renderer logic
						var rowData = api.row(rowIdx).data();

						// Indices (0-based) after removing #:
						// 0: Control, 1: Nombre, 2: Marca, 3: Celular, 4: Correo, 
						// 5: Dirección, 6: Productos, 7: Notas, 8: Acciones

						var nombre = rowData[1];
						var marca = rowData[2];
						var celular = rowData[3];
						var correo = rowData[4];
						var direccion = rowData[5];
						var productos = rowData[6]; // HTML content (badge)
						var notas = rowData[7]; // HTML content (editable)
						var finalHtml = '';

						// Section 1: Contacto
						finalHtml += '<div style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align:left; padding-left: 15px;">';
						finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Contacto</h5></div>';

						finalHtml += '<div style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Celular: </span><span style="color:#333; text-align: right;">' + celular + '</span></div>';

						finalHtml += '<div style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Correo: </span><span style="color:#333; text-align: right;">' + correo + '</span></div>';

						finalHtml += '<div style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Dirección: </span><span style="color:#333; text-align: right;">' + direccion + '</span></div>';

						// Section 2: Información (Productos, Marca)
						finalHtml += '<div style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align:left; padding-left: 15px;">';
						finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Información</h5></div>';

						finalHtml += '<div style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Productos: </span><span style="color:#333; text-align: right;">' + productos + '</span></div>';

						// Section 3: Notas
						finalHtml += '<div style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align:left; padding-left: 15px;">';
						finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Notas</h5></div>';

						finalHtml += '<div style="padding: 8px 0;">';

						// Get the ID from the row node (the main table row)
						var rowNode = api.row(rowIdx).node();
						var notasCell = $(rowNode).find('.celda-notas-proveedor');
						var providerId = notasCell.attr('data-id');
						var notasText = notasCell.text().trim();

						finalHtml += '<div contenteditable="true" class="celda-notas-proveedor" data-id="' + providerId + '" style="width: 100%; outline: none; display: block; border: 1px solid #ddd;">' + notasText + '</div></div>';

						return finalHtml ? $('<div style="background-color: #f8f9fa; margin: -8px; padding: 10px;">').append(finalHtml) : false;
					}
				}
			},
			"columnDefs": [
				{
					"targets": 0, // Control column
					"className": 'control',
					"orderable": false,
					"responsivePriority": 1
				},
				{
					"targets": 1, // Nombre
					"responsivePriority": 1
				},
				{
					"targets": 8, // Acciones
					"responsivePriority": 2,
					"orderable": false
				},
				{
					"targets": [2, 3, 4, 5, 6, 7], // Other columns hidden on mobile
					"responsivePriority": 1000
				}
			],
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
	}

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


/*=============================================
EDITAR NOTAS PROVEEDOR - Edición en línea
=============================================*/

// Usar event delegation para que funcione con elementos dinámicos (móvil)
$(document).on('blur', '.celda-notas-proveedor', function () {
	const id = $(this).data('id');
	const nuevasNotas = $(this).text().trim();

	if (!id) {
		console.error('ERROR: No se encontró el ID del proveedor');
		return;
	}

	$.ajax({
		url: 'ajax/proveedores.ajax.php',
		method: 'POST',
		data: {
			id: id,
			notas: nuevasNotas,
			accion: 'actualizarNotas'
			// csrf_token removido - manejado por csrf-helper.js
		},

		success: function (respuesta) {
			console.log('Notas actualizadas exitosamente');
		},

		error: function (xhr, status, error) {
			console.error('Error al actualizar las notas:', error);
		}
	});
});