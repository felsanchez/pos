/*=============================================
CARGAR TABLA DINAMICA BODEGAS
=============================================*/
$(document).ready(function () {
	if (!$.fn.DataTable.isDataTable('.tablaBodegas')) {
		$(".tablaBodegas").DataTable({
			"autoWidth": false,
			"responsive": {
				"details": {
					"type": "inline",
					"renderer": function (api, rowIdx, columns) {
						var labels = {
							0: '#',
							1: 'Nombre',
							2: 'Dirección',
							3: 'Teléfono',
							4: 'Acciones'
						};

						var finalHtml = '';
						var hasHidden = false;

						$.each(columns, function (i, col) {
							if (!col.hidden) return;

							hasHidden = true;
							var colIdx = col.columnIndex;
							var label = labels[colIdx] || col.title || ('Columna ' + colIdx);
							var data = col.data || '';

							finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
							finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
							finalHtml += '<span style="color:#333;">' + data + '</span>';
							finalHtml += '</div>';
						});

						if (!hasHidden) return false;
						return $('<div style="padding:8px 12px; background:#f8f9fa;">').append(finalHtml);
					}
				}
			},
			"columnDefs": [
				{ "targets": 0, "responsivePriority": 1 },
				{ "targets": 4, "responsivePriority": 1, "orderable": false },
				{ "targets": 1, "responsivePriority": 2 },
				{ "targets": 2, "responsivePriority": 3 },
				{ "targets": 3, "responsivePriority": 4 }
			],
			"order": [[1, "asc"]],
			"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
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

/*=============================================
EDITAR BODEGA
=============================================*/
$(".tablaBodegas").on("click", ".btnEditarBodega", function(){

	var idBodega = $(this).attr("idBodega");

	var datos = new FormData();
	datos.append("idBodega", idBodega);

	$.ajax({
		url: "ajax/bodegas.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){

			$("#editarBodega").val(respuesta["nombre"]);
			$("#idBodega").val(respuesta["id"]);
			$("#editarDireccionBodega").val(respuesta["direccion"]);
			$("#editarTelefonoBodega").val(respuesta["telefono"]);

		}

	})

})

/*=============================================
ACTIVAR BODEGA
=============================================*/
$(".tablaBodegas").on("click", ".btnActivarBodega", function(){

	var idBodega = $(this).attr("idBodega");
	var estadoBodega = $(this).attr("estadoBodega");
	var boton = $(this);

	var datos = new FormData();
	datos.append("activarId", idBodega);
	datos.append("activarBodega", estadoBodega);

	$.ajax({
		url: "ajax/bodegas.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		success: function(respuesta){

			if (estadoBodega == 0) {
				boton.removeClass('btn-success').addClass('btn-danger');
				boton.html('Desactivado');
				boton.attr('estadoBodega', 1);
			} else {
				boton.removeClass('btn-danger').addClass('btn-success');
				boton.html('Activado');
				boton.attr('estadoBodega', 0);
			}
			
			swal({
				title: "¡Estado actualizado!",
				text: "El estado de la sucursal ha sido cambiado correctamente.",
				type: "success",
				confirmButtonText: "Cerrar"
			}).then(function(result) {
				if (result.value) {
					window.location = "bodegas";
				}
			});

		}
	})

})


/*=============================================
INGRESAR A SUCURSAL
=============================================*/
$(".tablaBodegas").on("click", ".btnIngresarBodega", function(){

	var idBodega = $(this).attr("idBodega");

	var datos = new FormData();
	datos.append("ingresarId", idBodega);

	$.ajax({
		url: "ajax/bodegas.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		success: function(respuesta){

			if(respuesta == "ok"){

				swal({
					title: "¡Ingreso exitoso!",
					text: "Ahora estás administrando esta sucursal.",
					type: "success",
					confirmButtonText: "¡Excelente!"
				}).then(function(result) {
					if (result.value) {
						window.location = "inicio";
					}
				});

			}
		}
	})

})
