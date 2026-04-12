$(document).ready(function () {
	/*=============================================
	SideBar Menu
	=============================================*/
	if (typeof $.fn.tree === 'function') {
		$('.sidebar-menu').tree();
	}

	/*=============================================
	Data Table Global (.tablas)
	=============================================*/
	$(".tablas").DataTable({
		"autoWidth": false,
		"responsive": true,
		"language": {
			"sProcessing": "Procesando...",
			"sLengthMenu": "Mostrar _MENU_ registros",
			"sZeroRecords": "No se encontraron resultados",
			"sEmptyTable": "Ningún dato disponible en esta tabla",
			"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
			"sSearch": "Buscar:",
			"oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
		},
		"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
		"initComplete": function(settings, json) {
			// CRITICO: El sistema oculta las tablas por CSS hasta que tienen esta clase.
			$(this).addClass('datatable-ready');
			console.log("DataTable Global: Clase datatable-ready añadida.");
		}
	});

	/*=============================================
	Otras Funciones
	=============================================*/
	if (typeof $.fn.iCheck === 'function') {
		$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
			checkboxClass: 'icheckbox_minimal-blue',
			radioClass: 'iradio_minimal-blue'
		});
	}

	if (typeof $.fn.inputmask === 'function') {
		$('[data-mask]').inputmask();
	}
});