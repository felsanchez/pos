/*=============================================
EDITAR CLIENTE
=============================================*/
$(document).on("click", ".btnSinVentas", function (e) {
	e.preventDefault();
	swal({
		title: "Sin ventas",
		text: "Este cliente no tiene ventas registradas",
		type: "info",
		confirmButtonText: "Cerrar"
	});
});

$(document).on("click", ".btnEditarCliente", function () {

	var idCliente = $(this).attr("idCliente");

	var datos = new FormData();
	datos.append("idClienteEditar", idCliente);
	//datos.append("idCliente", idCliente);

	$.ajax({

		url: "ajax/clientes.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			console.log("Respuesta estatus:", respuesta["estatus"]);

			$("#idCliente").val(respuesta["id"]);
			$("#editarCliente").val(respuesta["nombre"]);
			$("#editarDocumentoId").val(respuesta["documento"]);
			$("#editarEmail").val(respuesta["email"]);
			$("#editarTelefono").val(respuesta["telefono"]);
			$("#editarDepartamento").val(respuesta["departamento"]);
			$("#editarCiudad").val(respuesta["ciudad"]);
			$("#editarDireccion").val(respuesta["direccion"]);
			$("#editarFechaNacimiento").val(respuesta["fecha_nacimiento"]);

			$("#editarEstado").val(respuesta["estatus"]);
			//$("#editarEstado").val(respuesta["estatus"].toLowerCase()).trigger("change");
		}
	})

})


/*=============================================
ELIMINAR CLIENTE
=============================================*/
$(document).on("click", ".btnEliminarCliente", function () {

	var idCliente = $(this).attr("idCliente");

	// Detectar si estamos en contactos o clientes
	var rutaActual = window.location.href;
	var ruta = "clientes";
	var mensaje = "¿Esta seguro de borrar el cliente?";

	if (rutaActual.indexOf("contactos") !== -1) {
		ruta = "contactos";
		mensaje = "¿Esta seguro de borrar el contacto?";
	}

	swal({

		title: mensaje,
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar!'
	}).then((result) => {

		if (result.value) {

			window.location = "index.php?ruta=" + ruta + "&idCliente=" + idCliente;
		}
	})
})



/*=============================================
HPM REVISAR SI EL CLIENTE YA ESTA REGISTRADO
=============================================*/

$("#nuevoCliente").change(function () {

	$(".alert").remove();

	var nombre = $(this).val();

	var datos = new FormData();
	datos.append("validarCliente", nombre);

	$.ajax({
		url: "ajax/clientes.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {

			if (respuesta) {

				$("#nuevoCliente").parent().after('<div class="alert alert-warning">Este cliente ya existe en la base de datos!</div>');

				//$("#nuevoCliente").val("");
			}

		}
	})
})


/*=============================================
INICIALIZAR DATATABLES PARA TABLAS DE CLIENTES
=============================================*/
$(document).ready(function () {
	// Inicializar tabla 1 (Clientes con ventas) solo si no está ya inicializada
	if ($('.tablas1').length > 0) {
		$('.tablas1').DataTable({
			"destroy": true,
			"stateSave": false,
			"responsive": false,
			"columnDefs": [
				{
					"targets": 0, // # (ID)
					"orderable": true
				},
				{
					"targets": 1, // Nombre
					"orderable": true
				},
				{
					"targets": 2, // Documento
					"orderable": true
				},
				{
					"targets": 3, // Email
					"orderable": true
				},
				{
					"targets": 4, // Teléfono
					"orderable": true
				},
				{
					"targets": 5, // Dirección
					"orderable": true
				},
				{
					"targets": 6, // Estado
					"orderable": true
				},
				{
					"targets": 7, // Notas
					"orderable": true
				},
				{
					"targets": 8, // Última compra
					"orderable": true
				},
				{
					"targets": 9, // Acciones
					"orderable": false
				},
				{
					"targets": 10, // Ingreso
					"orderable": true
				}
			],
			"order": [[0, 'desc']],
			"autoWidth": false,
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

	// Inicializar tabla 2 (Clientes sin ventas) solo si no está ya inicializada
	if ($('.tablas2').length > 0 && !$.fn.DataTable.isDataTable('.tablas2')) {
		$('.tablas2').DataTable({
			"responsive": true,
			"columnDefs": [
				{
					"responsivePriority": 1,
					"targets": 0 // #
				},
				{
					"responsivePriority": 1,
					"targets": 1 // Nombre
				},
				{
					"responsivePriority": 10,
					"targets": 2 // Documento
				},
				{
					"responsivePriority": 10,
					"targets": 3 // Email
				},
				{
					"responsivePriority": 10,
					"targets": 4 // Teléfono
				},
				{
					"responsivePriority": 10,
					"targets": 5 // Departamento
				},
				{
					"responsivePriority": 10,
					"targets": 6 // Ciudad
				},
				{
					"responsivePriority": 10,
					"targets": 7 // Dirección
				},
				{
					"responsivePriority": 2,
					"targets": 8 // Estado
				},
				{
					"responsivePriority": 10,
					"targets": 9 // Notas
				},
				{
					"responsivePriority": 10,
					"targets": 10 // Ingreso al sistema
				},
				{
					"responsivePriority": 1,
					"targets": 11 // Acciones
				}
			],
			"order": [[1, 'asc']],
			"autoWidth": false,
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