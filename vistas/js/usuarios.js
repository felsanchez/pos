/*=============================================
CARGAR TABLA DINAMICA USUARIOS
=============================================*/

$(document).ready(function () {
	if (!$.fn.DataTable.isDataTable('.tablaUsuarios')) {
		$(".tablaUsuarios").DataTable({
			"responsive": {
				"details": {
					"renderer": function (api, rowIdx, columns) {
						// Custom renderer logic
						var rowData = api.row(rowIdx).data();

						// Indices (0-based):
						// 0: Control, 1: Nombre, 2: Usuario, 3: Foto, 4: Perfil, 
						// 5: Estado, 6: Ultimo login, 7: Acciones

						var nombre = rowData[1];
						var foto = rowData[3]; // HTML content (img tag)
						var estado = rowData[5]; // HTML content (button)
						var ultimoLogin = rowData[6];

						var finalHtml = '';

						// Section 1: Información Personal
						finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;">';
						finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Información Personal</h5></div>';

						finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Nombre: </span><span class="pull-right" style="color:#333;">' + nombre + '</span></div>';

						finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Foto: </span><span class="pull-right" style="color:#333;"><button class="btn btn-info btn-xs btnVerFotoUsuario">Ver foto</button></span></div>';

						finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Estado: </span><span class="pull-right" style="color:#333;">' + estado + '</span></div>';

						// Section 2: Actividad
						finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;">';
						finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Actividad</h5></div>';

						finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
						finalHtml += '<span class="text-bold" style="color:#555;">Último login: </span><span class="pull-right" style="color:#333;">' + ultimoLogin + '</span></div>';

						return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #f8f9fa; margin: 0;">').append(finalHtml) : false;
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
					"targets": 2, // Usuario
					"responsivePriority": 1
				},
				{
					"targets": 4, // Perfil
					"responsivePriority": 1
				},
				{
					"targets": 7, // Acciones
					"responsivePriority": 2,
					"orderable": false
				},
				{
					"targets": [1, 3, 5, 6], // Nombre, Foto, Estado, Ultimo login (hidden on mobile)
					"responsivePriority": 1000
				}
			],
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
			title: "Error al subir la imagenn",
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
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

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
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

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


/*=============================================
REVISAR SI EL USUARIO YA ESTA REGISTRADO
=============================================*/

$("#nuevoUsuario").change(function () {

	$(".alert").remove();

	var usuario = $(this).val();

	var datos = new FormData();
	datos.append("validarUsuario", usuario);
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

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
			datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

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