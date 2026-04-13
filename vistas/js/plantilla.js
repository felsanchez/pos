$(document).ready(function () {
	/*=============================================
	SideBar Menu
	=============================================*/
	if (typeof $.fn.tree === 'function') {
		$('.sidebar-menu').tree();
	}

	/*=============================================
	Lógica de Loader Global
	=============================================*/
	function quitarLoaderGlobal() {
		if ($("#loader-table").length > 0) {
			$("#loader-table").fadeOut(400, function () {
				$(this).remove();
			});
		}
	}

	// Escuchar cuando cualquier DataTable con clase .tablas se inicialice
	$(document).on('init.dt', '.tablas', function () {
		console.log("DataTable inicializado. Quitando loader...");
		$(this).addClass('datatable-ready');
		quitarLoaderGlobal();
	});

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
			"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
			"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
			"sSearch": "Buscar:",
			"oPaginate": {
				"sFirst": "Primero",
				"sLast": "Último",
				"sNext": "Siguiente",
				"sPrevious": "Anterior"
			}
		},
		"dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
		"initComplete": function(settings, json) {
			// Respaldo por si el evento init.dt no se dispara a tiempo
			$(this).addClass('datatable-ready');
			quitarLoaderGlobal();
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