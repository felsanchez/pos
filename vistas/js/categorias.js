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
			$("#idCategoria").val(respuesta["id"]);

		}

	})


})


$(".tablas").on("click", ".btnEliminarCategoria", function () {

	var idCategoria = $(this).attr("idCategoria");

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
			// csrf_token removido - manejado por csrf-helper.js

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
							if (result.value) {
								window.location.reload();
							}
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
			})
		}

	})

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
				{ "targets": 2, "responsivePriority": 2, "orderable": false }, // Acciones
				{ "targets": 1, "responsivePriority": 3 } // Productos
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
});