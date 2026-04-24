/*=============================================
CARGAR TABLA DINAMICA USUARIOS
=============================================*/

$(document).ready(function () {

	// Inicializar Select2 para el filtro de perfiles
	if ($("#seleccionarPerfilFiltro").length > 0 && typeof $.fn.select2 !== 'undefined') {
		$("#seleccionarPerfilFiltro").select2({
			placeholder: "Seleccionar perfil...",
			allowClear: true,
			minimumResultsForSearch: 0,
			width: '100%'
		});
	}

	if (!$.fn.DataTable.isDataTable('#tablaListaUsuarios')) {
		var tablaUsuarios = $("#tablaListaUsuarios").DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "ajax/usuarios.ajax.php",
				"type": "POST",
				"data": function(d) {
					d.perfilFiltro = $("#seleccionarPerfilFiltro").val();
				}
			},
			"responsive": {
				"details": {
					"type": "inline",
					"renderer": function (api, rowIdx, columns) {
						var labels = {
							0: 'Usuario',
							1: 'Nombre',
							2: 'Email',
							3: 'Imagen',
							4: 'Perfil',
							5: 'Estado',
							6: 'Último login',
							7: 'Acciones'
						};

						var finalHtml = '';
						var hasHidden = false;

						$.each(columns, function (i, col) {
							if (!col.hidden) return;

							hasHidden = true;
							var colIdx = col.columnIndex;
							var label  = labels[colIdx] || col.title || ('Columna ' + colIdx);
							var data   = col.data || '';

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
			"order": [[0, 'asc']],
			"columnDefs": [
				{
					"targets": 0,
					"responsivePriority": 1,
					"orderable": true
				},
				{ "targets": 7, "responsivePriority": 1, "orderable": false },
				{ "targets": 1, "responsivePriority": 2 },
				{ "targets": 3, "responsivePriority": 3, "orderable": false },
				{ "targets": 4, "responsivePriority": 4 },
				{ "targets": 2, "responsivePriority": 5 },
				{
					"targets": 5,
					"responsivePriority": 6,
					"visible": $("#puedeEditarUsuarios").val() == "1"
				},
				{ "targets": 6, "responsivePriority": 7 }
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

		/*=============================================
		FILTRAR POR PERFIL (NUEVO BUSCADOR)
		=============================================*/
		$("#seleccionarPerfilFiltro").on("change", function () {
			console.log("Recargando tabla por cambio de perfil...");
			tablaUsuarios.ajax.reload();
		});
	}
});

/*=============================================
VER FOTO DESDE VISTA EXPANDIDA MÓVIL
=============================================*/
$(".tablaUsuarios").on("click", ".btnVerFotoUsuario", function () {
	// Find the parent row (could be the main row or the child row)
	var childRow = $(this).closest('tr');
	var parentRow = childRow.prev('tr');

	// Try to find the image in the parent row
	var img = parentRow.find('.img-usuario-clickeable').first();

	if (img.length > 0) {
		img.click();
	}
});

/*=============================================
SUBIENDO FOTO DEL USUARIO
=============================================*/

$(".nuevaFoto").change(function () {

	var imagen = this.files[0];


	/*=============================================
	VALIDAMOS EL FORMATO DE LA IMAGEN QUE SEA JPG O PNG
	=============================================*/

	if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {

		$(".nuevaFoto").val("");

		swal({
			title: "Error al subir la imagen",
			text: "¡La imagen debe estar en formato jpg o png!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});
	}

	else if (imagen["size"] > 2000000) {

		$(".nuevaFoto").val("");

		swal({
			title: "Error al subir la imagen",
			text: "¡La imagen no debe pesar mas de 2MB!",
			type: "error",
			confirmButtonText: "¡Cerrar!"
		});

	}

	else {

		var datosImagen = new FileReader;
		datosImagen.readAsDataURL(imagen);

		$(datosImagen).on("load", function (event) {

			var rutaImagen = event.target.result;

			$(".previsualizar").attr("src", rutaImagen);
		})
	}


})


/*=============================================
EDITAR USUARIO
=============================================*/

$(".tablaUsuarios").on("click", ".btnEditarUsuario", function () {

	var idUsuario = $(this).attr("idUsuario");

	console.log("ID Usuario a editar:", idUsuario);

	var datos = new FormData();
	datos.append("idUsuario", idUsuario);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({

		url: "ajax/usuarios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			console.log("Respuesta del servidor:", respuesta);

			$("#editarNombre").val(respuesta["nombre"]);
			$("#editarUsuario").val(respuesta["usuario"]);
			$("#editarPerfil").html(respuesta["perfil"]);

			$("#editarPerfil").val(respuesta["perfil"]);
			$("#fotoActual").val(respuesta["foto"]);
			$("#passwordActual").val(respuesta["password"]);
			$("#editarEmail").val(respuesta["email"]);

			if (respuesta["foto"] != "") {

				$(".previsualizar").attr("src", respuesta["foto"]);
			} else {
				$(".previsualizar").attr("src", "vistas/img/usuarios/default/anonymous.png");
			}

			// Abrir el modal DESPUÉS de cargar los datos
			$("#modalEditarUsuario").modal("show");

		},
		error: function (xhr, status, error) {
			console.error("Error en AJAX:", status, error);
			console.error("Respuesta:", xhr.responseText);

			swal({
				type: "error",
				title: "Error al cargar los datos del usuario",
				text: "Por favor, intente nuevamente",
				confirmButtonText: "Cerrar"
			});
		}

	});

})



/*=============================================
ACTIVAR USUARIO CON EFECTO
=============================================*/
$(".tablaUsuarios").on("click", ".btnActivar", function () {

	var idUsuario = $(this).attr("idUsuario");
	var estadoUsuario = $(this).attr("estadoUsuario");
	var boton = $(this);
	var fila = boton.closest('tr');

	// Agregar efecto de fade
	fila.css('opacity', '0.5');

	// Deshabilitar botón temporalmente
	boton.prop('disabled', true);
	var textoOriginal = boton.html();
	boton.html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

	var datos = new FormData();
	datos.append("activarId", idUsuario);
	datos.append("activarUsuario", estadoUsuario);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({
		url: "ajax/usuarios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		success: function (respuesta) {

			// Pequeño delay para ver el efecto
			setTimeout(function () {

				// Cambiar el estado del botón con animación
				if (estadoUsuario == 0) {
					boton.removeClass('btn-success').addClass('btn-danger');
					boton.html('Desactivado');
					boton.attr('estadoUsuario', 1);
				} else {
					boton.removeClass('btn-danger').addClass('btn-success');
					boton.html('Activado');
					boton.attr('estadoUsuario', 0);
				}

				// Efecto de "parpadeo" para indicar cambio
				fila.css('background-color', '#d4edda');
				fila.animate({ opacity: 1 }, 300);

				setTimeout(function () {
					fila.css('background-color', '');
				}, 1000);

				boton.prop('disabled', false);

			}, 400); // Delay para ver el efecto

		},
		error: function () {
			boton.html(textoOriginal);
			boton.prop('disabled', false);
			fila.css('opacity', '1');

			swal({
				type: "error",
				title: "Error en la conexión",
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			});
		}
	})

})

console.warn("!!!! USUARIOS JAVASCRIPT CARGADO !!!!");

/*=============================================
REVISAR SI EL USUARIO YA ESTA REGISTRADO
=============================================*/

$("#nuevoUsuario").change(function () {

	$(".alert").remove();

	var usuario = $(this).val();

	var datos = new FormData();
	datos.append("validarUsuario", usuario);
	// csrf_token removido - manejado por csrf-helper.js

	$.ajax({
		url: "ajax/usuarios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			if (respuesta) {

				$("#nuevoUsuario").parent().after('<div class="alert alert-warning">Este usuario ya existe en la base de datos!</div>');

				$("#nuevoUsuario").val("");
			}

		}
	})
})

/*=============================================
ELIMINAR USUARIO
=============================================*/

$(".tablaUsuarios").on("click", ".btnEliminarUsuario", function () {

	var idUsuario = $(this).attr("idUsuario");
	var fotoUsuario = $(this).attr("fotoUsuario");
	var usuario = $(this).attr("usuario");

	swal({
		title: '¿Esta seguro de borrar el usuario?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar usuario!'
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idUsuarioEliminar", idUsuario);
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/usuarios.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							type: "success",
							title: "¡El usuario ha sido borrado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location = "usuarios";
							}
						});
					} else if (respuesta == "error_auto_eliminacion") {
						swal({
							type: "error",
							title: "¡No puedes eliminar tu propio usuario!",
							text: "Cierra la sesión e inicia con otro usuario para poder eliminar este.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else if (respuesta == "error_actividades") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El usuario tiene actividades asociadas.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else if (respuesta == "error_ventas") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El usuario tiene ventas asociadas.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else if (respuesta == "error_notas_credito") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El usuario tiene notas crédito asociadas.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else if (respuesta == "error_documentos_soporte") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El usuario tiene documentos soporte asociados.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else if (respuesta == "error_notas_ajuste") {
						swal({
							type: "error",
							title: "¡No se puede eliminar!",
							text: "El usuario tiene notas de ajuste asociadas.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else {
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo eliminar el usuario. " + respuesta,
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					}
				}
			})
		}


	})


})