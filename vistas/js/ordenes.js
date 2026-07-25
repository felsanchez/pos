/*=============================================
TABLA ORDENES - SERVER SIDE
=============================================*/
$(document).ready(function () {
	// Retraso para asegurar que los scripts globales terminen
	setTimeout(function () {
		if (typeof $.fn.select2 !== 'undefined') {
			$('#filtroClienteOrdenes').select2({
				allowClear: false,
				width: '100%'
			});

			$('#filtroUsuarioOrdenes').select2({
				allowClear: false,
				width: '100%'
			});

			// Restaurar valores si existen en la URL
			const urlParams = new URLSearchParams(window.location.search);
			if (urlParams.get('cliente')) $('#filtroClienteOrdenes').val(urlParams.get('cliente')).trigger('change.select2');
			if (urlParams.get('usuario')) $('#filtroUsuarioOrdenes').val(urlParams.get('usuario')).trigger('change.select2');

			// EVENTOS AUTOMÁTICOS PARA FILTROS
			$('#filtroClienteOrdenes, #filtroUsuarioOrdenes, .select-bodega').on('change', function () {
				if (typeof window.recargarTablaOrdenes === 'function') {
					window.recargarTablaOrdenes();
				}
			});

			if($('.select-bodega').length > 0){
				$('.select-bodega').select2({
					allowClear: false,
					width: '100%'
				});
			}
		}

		// INICIALIZACIÓN DEL RANGO DE FECHAS
		if (typeof $.fn.daterangepicker !== 'undefined') {
			const fechaInicialVal = $('#fechaInicial').val();
			const fechaFinalVal = $('#fechaFinal').val();

			if (fechaInicialVal && fechaFinalVal) {
				$('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + moment(fechaInicialVal).format('MMMM D, YYYY') + ' - ' + moment(fechaFinalVal).format('MMMM D, YYYY'));
			} else {
				$('#daterange-btn span').html('<i class="fa fa-calendar"></i> Mostrar todas');
			}

			$('#daterange-btn').daterangepicker(
				{
					ranges: {
						'Mostrar todas': [moment('2000-01-01'), moment()],
						'Hoy': [moment(), moment()],
						'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
						'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
						'Este mes': [moment().startOf('month'), moment().endOf('month')],
						'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					},
					startDate: fechaInicialVal ? moment(fechaInicialVal) : moment(),
					endDate: fechaFinalVal ? moment(fechaFinalVal) : moment(),
					locale: {
						cancelLabel: 'Limpiar'
					}
				},
				function (start, end) {
					if (start.format('YYYY-MM-DD') === '2000-01-01') {
						$('#daterange-btn span').html('<i class="fa fa-calendar"></i> Mostrar todas');
						$('#fechaInicial').val('');
						$('#fechaFinal').val('');
					} else {
						$('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
						$('#fechaInicial').val(start.format('YYYY-MM-DD'));
						$('#fechaFinal').val(end.format('YYYY-MM-DD'));
					}

					if (typeof window.recargarTablaOrdenes === 'function') {
						window.recargarTablaOrdenes();
					}
				}
			);

			$('#daterange-btn').on('cancel.daterangepicker', function () {
				$(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar todas');
				$('#fechaInicial').val('');
				$('#fechaFinal').val('');
				if (typeof window.recargarTablaOrdenes === 'function') {
					window.recargarTablaOrdenes();
				}
			});
		}
	}, 200);

	if ($(".tablaOrdenes").length > 0) {
		if ($.fn.DataTable.isDataTable('.tablaOrdenes')) {
			$('.tablaOrdenes').DataTable().destroy();
		}

		const tableOrdenes = $(".tablaOrdenes").DataTable({
			"serverSide": true,
			"processing": true,
			"ajax": {
				"url": "ajax/ventas.ajax.php",
				"type": "POST",
				"data": function (d) {
					d.csrf_token = $('meta[name="csrf-token"]').attr('content');
					d.drawOrdenes = 1;
					d.fechaInicial = $('#fechaInicial').val();
					d.fechaFinal = $('#fechaFinal').val();
					d.clienteId = $('#filtroClienteOrdenes').val();
					d.usuarioId = $('#filtroUsuarioOrdenes').val();
					d.bodegaId = $('.select-bodega').length ? ($('.select-bodega').val() || '') : '';

					var urlParams = new URLSearchParams(window.location.search);
					if (!d.fechaInicial) d.fechaInicial = urlParams.get('fechaInicial');
					if (!d.fechaFinal) d.fechaFinal = urlParams.get('fechaFinal');
					if (!d.clienteId) d.clienteId = urlParams.get('cliente');
					if (!d.usuarioId) d.usuarioId = urlParams.get('usuario');
				}
			},
			"order": [[6, "desc"]],
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

							if (col.columnIndex === 6) {
								finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
								finalHtml += '<span class="text-bold" style="display:block; color:#555; margin-bottom:5px;">' + label + ':</span>';
							} else {
								finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
								finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
							}

							if (col.columnIndex === 6) {
								var rowNode = api.row(rowIdx).node();
								var idOrden = $(rowNode).attr('data-orden-id') || "";
								var observacionText = $(rowNode).find('.celda-observacion').text().trim();
								var placeholderAttr = ' data-placeholder="Escribe una observación..."';

								finalHtml += '<div contenteditable="true" class="celda-observacion" data-id="' + idOrden + '"' + placeholderAttr + ' style="width:100%; outline:none; display:block; border:1px dashed #ccc; padding:8px; background:#fff9e6; margin-top:5px;">' + observacionText + '</div>';
							} else {
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
				{ "targets": 0, "responsivePriority": 1 },
				{ "targets": 1, "responsivePriority": 2 },
				{ "targets": 8, "responsivePriority": 3, "orderable": false, "render": function (data, type, row) { return row[11]; } }, // Acciones
				{ "targets": 2, "responsivePriority": 4 },
				{ "targets": 3, "responsivePriority": 6, "render": function (data, type, row) { return row[4]; } }, // Imagen
				{ "targets": 4, "responsivePriority": 7, "render": function (data, type, row) { return row[5]; } }, // Total
				{ "targets": 5, "responsivePriority": 10, "render": function (data, type, row) { return row[8]; } }, // Fecha
				{ "targets": 6, "responsivePriority": 9, "render": function (data, type, row) { return row[7]; } }, // Observación
				{ "targets": 7, "responsivePriority": 12, "orderable": false, "render": function (data, type, row) { return row[10]; } } // Convertir
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

		window.recargarTablaOrdenes = function () {
			tableOrdenes.ajax.reload();
		};
	}
});

/*=============================================
GUARDAR NOTAS Y OBSERVACIONES
=============================================*/
$(document).on('blur', '.celda-nota', function () {
	const idVenta = $(this).data('id');
	const nuevaNota = $(this).text().trim();
	
	$.ajax({
		url: "ajax/datatable-ventas.ajax.php",
		method: "POST",
		data: {
			csrf_token: $('meta[name="csrf-token"]').attr('content'),
			idVentaNota: idVenta,
			nuevaNota: nuevaNota
		},
		success: function (respuesta) {
			console.log("Nota guardada");
		}
	});
});

$(document).on('blur', '.celda-observacion', function () {
	const elemento = $(this);
	const idVenta = elemento.attr('data-id');
	const nuevaObservacion = elemento.text().trim();
	
	if (!idVenta) return;

	$.ajax({
		url: "ajax/datatable-ventas.ajax.php",
		method: "POST",
		data: {
			csrf_token: $('meta[name="csrf-token"]').attr('content'),
			idVentaObservacion: idVenta,
			nuevaObservacion: nuevaObservacion
		},
		success: function (respuesta) {
			console.log("Observación guardada");
			// Feedback visual (destello verde)
			elemento.css('background-color', '#dff0d8');
			setTimeout(function () {
				elemento.css('background-color', '');
			}, 500);
		}
	});
});

/*=============================================
GESTIÓN DE IMÁGENES DE ORDEN
=============================================*/
$(document).on("click", ".img-ampliar-orden", function () {
	var rutaImagen = $(this).attr("data-imagen");
	var idVenta = $(this).attr("data-idventa");

	$("#imagenOrdenAmpliada").attr("src", rutaImagen);
	$("#idOrdenImagen").val(idVenta);
	$(".nuevaImagenOrden").val("");
	$("#modalAmpliarImagenOrden").modal("show");
});

$(".nuevaImagenOrden").change(function () {
	var imagen = this.files[0];
	if (imagen) {
		if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
			$(".nuevaImagenOrden").val("");
			swal({ title: "Error", text: "¡La imagen debe ser JPG o PNG!", type: "error" });
		} else if (imagen["size"] > 2000000) {
			$(".nuevaImagenOrden").val("");
			swal({ title: "Error", text: "¡La imagen no debe pesar más de 2MB!", type: "error" });
		} else {
			var datosImagen = new FileReader;
			datosImagen.readAsDataURL(imagen);
			$(datosImagen).on("load", function (event) {
				$("#imagenOrdenAmpliada").attr("src", event.target.result);
			});
		}
	}
});

$(document).on("click", ".btnGuardarImagenOrden", function () {
	var idVenta = $("#idOrdenImagen").val();
	var imagen = $(".nuevaImagenOrden")[0].files[0];

	if (!imagen) {
		swal({ title: "Advertencia", text: "Seleccione una imagen", type: "warning" });
		return;
	}

	var datos = new FormData();
	datos.append("idVentaImagen", idVenta);
	datos.append("nuevaImagenVenta", imagen);
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

	swal({ title: 'Cargando...', allowOutsideClick: false, onBeforeOpen: () => { swal.showLoading() } });

	$.ajax({
		url: "ajax/ventas.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			if (respuesta == "ok") {
				swal({ type: "success", title: "¡Actualizada!", showConfirmButton: true }).then(() => {
					$("#modalAmpliarImagenOrden").modal("hide");
					if(window.recargarTablaOrdenes) window.recargarTablaOrdenes();
					else window.location.reload();
				});
			}
		}
	});
});

/*=============================================
SEGUIMIENTO DE ORDEN (WEBHOOKS)
=============================================*/
function enviarSeguimiento(btn, urlWebhook, tipo) {
	var idOrden = btn.attr("idOrden");
	var codigoOrden = btn.attr("codigoOrden");
	var cliente = btn.attr("cliente");
	var telefono = btn.attr("telefono");
	var textoPregunta = btn.data('mensaje-' + tipo);

	swal({
		title: '¿Desea enviar un mensaje al cliente?',
		html: '<p style="font-size: 18px; font-weight: 500; margin: 10px 0;">' + textoPregunta + '</p>',
		type: 'question',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, enviar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.value) {
			const datosWebhook = new URLSearchParams();
			datosWebhook.append('id_orden', idOrden);
			datosWebhook.append('codigo', codigoOrden);
			datosWebhook.append('cliente', cliente);
			datosWebhook.append('celular', telefono);
			datosWebhook.append('tipo', tipo);
			datosWebhook.append('mensaje', textoPregunta);

			fetch(urlWebhook, {
				method: 'POST',
				mode: 'no-cors',
				cache: 'no-cache',
				credentials: 'omit',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: datosWebhook
			})
			.then(response => {
				const mapping = {
					'recibido': { col: 'seguimiento_recibido', lbl: 'Enviado (R)' },
					'procesado': { col: 'seguimiento_procesado', lbl: 'Enviado (P)' },
					'alistado': { col: 'seguimiento_alistado', lbl: 'Enviado (A)' }
				};
				var columna = mapping[tipo] ? mapping[tipo].col : "seguimiento_recibido";
				var datos = new FormData();
				datos.append("idVentaSeguimiento", idOrden);
				datos.append("columna", columna);
				datos.append("valor", 1);
				datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

				$.ajax({
					url: "ajax/ventas.ajax.php",
					method: "POST",
					data: datos,
					cache: false,
					contentType: false,
					processData: false,
					dataType: "json",
					success: function (res) {
						if (res == "ok") {
							var label = mapping[tipo] ? mapping[tipo].lbl : 'Enviado';
							btn.replaceWith('<span class="label label-success" style="margin-right:5px;">' + label + '</span>');
							swal({ type: "success", title: "Enviado", showConfirmButton: false, timer: 1500 });
						}
					}
				});
			})
			.catch(error => {
				swal({ type: "error", title: "Error al conectar con el servidor de mensajes" });
			});
		}
	});
}

$(".tablaOrdenes").on("click", ".btnSeguimientoRecibido", function () {
	enviarSeguimiento($(this), "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/47b4eb4c-c238-4ab4-bebd-efcb09206cef", 'recibido');
});

$(".tablaOrdenes").on("click", ".btnSeguimientoProcesado", function () {
	enviarSeguimiento($(this), "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/b9ebbdab-45f9-46ac-957e-30e080f773aa", 'procesado');
});

$(".tablaOrdenes").on("click", ".btnSeguimientoAlistado", function () {
	enviarSeguimiento($(this), "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/b6aad80c-aedf-4339-a701-89d040f44f47", 'alistado');
});

/*=============================================
VER CLIENTE DESDE ORDEN
=============================================*/
$(document).on("click", ".btnVerClienteDesdeVenta", function () {
	var idCliente = $(this).attr("idCliente");
	var datos = new FormData();
	datos.append("idCliente", idCliente);
	datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

	$.ajax({
		url: "ajax/clientes.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "text",
		success: function (respuesta) {
			var jsonStart = respuesta.indexOf('{');
			var jsonString = respuesta.substring(jsonStart);
			var data = JSON.parse(jsonString);

			$("#idCliente").val(data["id"]);
			$("#editarCliente").val(data["nombre"]);
			$("#editarDocumentoId").val(data["documento"]);
			$("#editarEmail").val(data["email"]);
			$("#editarTelefono").val(data["telefono"]);
			$("#editarDireccion").val(data["direccion"]);
			$("#editarNotas").val(data["notas"]);
			$("#editarEstado").val(data["estatus"]);
			$("#editarDepartamento").val(data["departamento"]);
			$("#editarCiudad").val(data["ciudad"]);

			$('#modalEditarCliente').modal('show');
		}
	});
});
