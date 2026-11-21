/*=============================================
CARGAR LA TABLA DINÁMICA DE CLIENTES
=============================================*/
$(document).ready(function() {
	// Mover todos los modales de clientes al body para evitar conflictos de z-index
	$('#modalAgregarCliente').appendTo('body');
	$('#modalEditarCliente').appendTo('body');
	$('#modalImportarClientes').appendTo('body');
	$('#modalGestionarEstados').appendTo('body');
	$('#modalEditarEstado').appendTo('body');
});

/*=============================================
EDITAR CLIENTE (cargar datos en modal)
=============================================*/
$(document).on("click", ".btnEditarCliente", function () {
  var idCliente = $(this).attr("idCliente");
  var datos = new FormData();
  datos.append("idClienteEditar", idCliente);

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      $("#editarCliente").val(respuesta.nombre);
      $("#editarDocumentoId").val(respuesta.documento);
      $("#editarEmail").val(respuesta.email);
      $("#editarTelefono").val(respuesta.telefono);
      $("#editarDepartamento").val(respuesta.departamento);
      $("#editarCiudad").val(respuesta.ciudad);
      $("#editarDireccion").val(respuesta.direccion);
      $("#editarFechaNacimiento").val(respuesta.fecha_nacimiento);
      $("#editarEstado").val(respuesta.estatus);
      $("#editarNota").val(respuesta.notas);
      $("#idCliente").val(respuesta.id);
    }
  });
});


/*=============================================
TABLA CLIENTES (DataTables sin plugin responsive)
=============================================*/
var tabla1;

function filterTable1() {
  if (tabla1) tabla1.draw();
}

$(document).ready(function () {

  // Inicializar Select2 para el filtro de estado y origen
  if ($("#filtroEstatus1").length > 0 && typeof $.fn.select2 !== 'undefined') {
    $("#filtroEstatus1").select2({
      placeholder: "Seleccionar estado...",
      allowClear: true,
      minimumResultsForSearch: 0,
      width: '100%'
    });
  }

  if ($("#filtroOrigenCliente").length > 0 && typeof $.fn.select2 !== 'undefined') {
    $("#filtroOrigenCliente").select2({
      placeholder: "Todos",
      allowClear: true,
      minimumResultsForSearch: -1,
      width: '100%'
    });
  }

  if ($('.tablas1').length === 0) return;

  tabla1 = $('.tablas1').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
      "url": "ajax/clientes.ajax.php",
      "type": "POST",
      "data": function ( d ) {
          d.csrf_token = $('meta[name="csrf-token"]').attr('content');
          d.filtroEstatus1 = $('#filtroEstatus1').val();
          d.filtroOrigenCliente = $('#filtroOrigenCliente').val();
      }
    },
    "destroy": true,
    "stateSave": false,
    "order": [[0, 'asc']],
    "autoWidth": true,
    "pageLength": 25,
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

            if (col.columnIndex === 4) {
                // Reconstruimos la celda de notas respetando si es editable o no
                var rowNode = api.row(rowIdx).node();
                var idCliente = $(rowNode).attr('data-cliente-id') || "";
                var celdaOriginal = $(rowNode).find('.celda-notas, .celda-notas-disabled');
                var esEditable = celdaOriginal.hasClass('celda-notas') && celdaOriginal.attr('contenteditable') === 'true';
                var notasText = celdaOriginal.text().trim();
                if (notasText === "Sin notas") notasText = "";
                
                if (esEditable) {
                    var placeholderAttr = (notasText === "") ? ' data-placeholder="Escribe una nota..."' : "";
                    finalHtml += '<div contenteditable="true" class="celda-notas" data-id="' + idCliente + '"' + placeholderAttr + ' style="width:100%; outline:none; display:block; border:1px dashed #ccc; padding:8px; background:#fff9e6; margin-top:5px;">' + notasText + '</div>';
                } else {
                    finalHtml += '<div contenteditable="false" class="celda-notas-disabled" data-id="' + idCliente + '" title="Permiso de edición requerido" style="width:100%; display:block; border:1px solid #e2e8f0; border-radius:4px; padding:8px; background:#f8fafc; color:#64748b; margin-top:5px; cursor:not-allowed; opacity:0.85;">' + (notasText || '<span class="text-muted" style="font-style:italic;">Sin notas</span>') + '</div>';
                }
            } else {
                // El resto pasa su HTML o texto tal cual
                finalHtml += '<span style="color:#333;">' + col.data + '</span>';
            }
            
            finalHtml += '</div>';
          });

          if (!hasHidden) return false;
          return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
        }
      }
    },
    "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
    "columnDefs": [
      { "targets": 0, "responsivePriority": 1 }, // Nombre
      { "targets": 5, "responsivePriority": 2, "orderable": false, "render": function (data, type, row) { return row[5]; } }, // Acciones
      { "targets": 1, "responsivePriority": 3 }, // Documento
      { "targets": 2, "responsivePriority": 4 }, // Teléfono
      { "targets": 3, "responsivePriority": 5 }, // Estado
      { "targets": 4, "responsivePriority": 6 }  // Notas
    ],
    "drawCallback": function (settings) {
      if (typeof inicializarPlaceholdersClientes === "function") {
        inicializarPlaceholdersClientes();
      }
    },
    "language": {
      url: "vistas/bower_components/datatables.net/Spanish.json",
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_ entradas",
      info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
      sLoadingRecords: "Cargando...",
      oPaginate: {
        sFirst: "Primero",
        sLast: "Último",
        sNext: "Siguiente",
        sPrevious: "Anterior"
      }
    }
  });

  $('#filtroEstatus1').on('change', function () {
    filterTable1();
  });

  $('#filtroOrigenCliente').on('change', function () {
    filterTable1();
  });

});


/*=============================================
EDITAR NOTAS (inline, contenteditable)
=============================================*/
$(document).on('focus', '.celda-notas', function () {
  // Ya no eliminamos el placeholder al enfocar
});

$(document).on('input', '.celda-notas', function () {
  if ($(this).text().trim() === '') {
    $(this).attr('data-placeholder', 'Escribe una nota...');
  } else {
    $(this).removeAttr('data-placeholder');
  }
});

$(document).on('blur', '.celda-notas', function () {
  console.log('🔹 Blur detectado en Notas Cliente (ID: ' + $(this).attr('data-id') + ')');
  var elemento = $(this);
  var id = elemento.attr('data-id'); 
  var nuevaNota = elemento.text().trim();

  if (nuevaNota === '') {
    elemento.attr('data-placeholder', 'Escribe una nota...');
  } else {
    elemento.removeAttr('data-placeholder');
  }

  if (!id) {
    console.warn('⚠️ celda-notas: no se encontró data-id en el elemento.');
    return;
  }

  // Obtener token CSRF (requerido por el servidor)
  var csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: 'ajax/clientes.ajax.php',
    method: 'POST',
    data: {
      id: id,
      notas: nuevaNota,
      accion: 'actualizarNota',
      csrf_token: csrfToken
    },
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta == "ok") {
        console.log('✅ Nota guardada correctamente (Cliente ID: ' + id + ')');
        // Feedback visual (destello verde)
        elemento.css('background-color', '#dff0d8');
        setTimeout(function () {
          elemento.css('background-color', '');
        }, 500);
      }
    },
    error: function () {
      alert('Error al actualizar la nota');
    }
  });
});

// Inicializar placeholder en celdas vacías al cargar
function inicializarPlaceholdersClientes() {
  $('.celda-notas').each(function () {
    if ($(this).text().trim() === '') {
      $(this).attr('data-placeholder', 'Escribe una nota...');
    } else {
      $(this).removeAttr('data-placeholder');
    }
  });
}

$(document).ready(function () {
  inicializarPlaceholdersClientes();
});


/*=============================================
BOTÓN SIN VENTAS
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


/*=============================================
BOTÓN SIN FACTURAS ELECTRÓNICAS
=============================================*/
$(document).on("click", ".btnSinFacturas", function (e) {
  e.preventDefault();
  swal({
    title: "Sin Facturas electrónicas",
    text: "Este cliente no tiene facturas electrónicas registradas",
    type: "info",
    confirmButtonText: "Cerrar"
  });
});


/*=============================================
ELIMINAR CLIENTE
=============================================*/
$(document).on("click", ".btnEliminarCliente", function () {
  var idCliente = $(this).attr("idCliente");

  // Primero verificar si tiene relaciones asociadas
  var datosVerificacion = new FormData();
  datosVerificacion.append("idClienteVerificarRelaciones", idCliente);

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: datosVerificacion,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      var warningText = "¡Si no lo estás puedes cancelar la acción!";
      
      if (respuesta.status === "success" && respuesta.relaciones.length > 0) {
        var listaRelaciones = "";
        if (respuesta.relaciones.length === 1) {
          listaRelaciones = respuesta.relaciones[0];
        } else {
          var ultima = respuesta.relaciones.pop();
          listaRelaciones = respuesta.relaciones.join(", ") + " y " + ultima;
        }
        warningText = "El cliente tiene " + listaRelaciones + " asociadas. ¿Estás seguro de borrar el cliente?\n¡Si no lo estás puedes cancelar la acción!";
      }

      swal({
        title: "¿Estás seguro de borrar el cliente?",
        text: warningText,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, borrar'
      }).then(function (result) {
        if (result.value) {
          var datos = new FormData();
          datos.append("idClienteEliminar", idCliente);
          datos.append("ruta", "clientes");

          $.ajax({
            url: "ajax/clientes.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
              respuesta = $.trim(respuesta);
              if (respuesta === "ok") {
                swal({
                  type: "success",
                  title: "¡El cliente ha sido borrado correctamente!",
                  showConfirmButton: true,
                  confirmButtonText: "Cerrar"
                }).then(function (r) {
                  if ($.fn.DataTable.isDataTable('.tablas1')) {
                    $('.tablas1').DataTable().ajax.reload(null, false);
                  } else {
                    window.location.reload();
                  }
                });
              } else {
                swal({
                  type: "error",
                  title: "Error",
                  text: "No se pudo eliminar el cliente. " + respuesta,
                  confirmButtonText: "Cerrar"
                });
              }
            },
            error: function (xhr, status, err) {
              swal({
                type: "error",
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor.",
                confirmButtonText: "Cerrar"
              });
            }
          });
        }
      });
    },
    error: function () {
      // Fallback si la comprobación falla
      swal({
        title: "¿Estás seguro de borrar el cliente?",
        text: "¡Si no lo estás puedes cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, borrar'
      }).then(function (result) {
        if (result.value) {
          var datos = new FormData();
          datos.append("idClienteEliminar", idCliente);
          datos.append("ruta", "clientes");

          $.ajax({
            url: "ajax/clientes.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
              respuesta = $.trim(respuesta);
              if (respuesta === "ok") {
                swal({
                  type: "success",
                  title: "¡El cliente ha sido borrado correctamente!",
                  showConfirmButton: true,
                  confirmButtonText: "Cerrar"
                }).then(function (r) {
                  if ($.fn.DataTable.isDataTable('.tablas1')) {
                    $('.tablas1').DataTable().ajax.reload(null, false);
                  } else {
                    window.location.reload();
                  }
                });
              } else {
                swal({
                  type: "error",
                  title: "Error",
                  text: "No se pudo eliminar el cliente. " + respuesta,
                  confirmButtonText: "Cerrar"
                });
              }
            }
          });
        }
      });
    }
  });
});


/*=============================================
VALIDAR CLIENTE DUPLICADO AL CREAR
=============================================*/
$(document).on("change", "#nuevoCliente", function () {
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
      }
    }
  });
});

/*=============================================
GUARDAR CREAR CLIENTE VÍA AJAX
=============================================*/
$(document).on("submit", "#formAgregarCliente", function (e) {
	e.preventDefault();

	var form = this;
	var boton = $(form).find("button[type='submit']");
	boton.prop('disabled', true);
	var htmlOriginal = boton.html();
	boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

	swal({
		title: 'Guardando cliente',
		text: 'Por favor espere mientras se procesa la información...',
		type: 'info',
		allowOutsideClick: false,
		showConfirmButton: false,
		onBeforeOpen: () => {
			swal.showLoading()
		}
	});

	var datos = new FormData(form);
	datos.append("guardarCrearCliente", "ok");

	$.ajax({
		url: "ajax/clientes.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			boton.prop('disabled', false).html(htmlOriginal);

			if (respuesta.status === "ok") {
				swal({
					type: "success",
					title: "¡Éxito!",
					text: respuesta.mensaje,
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				}).then((result) => {
					$("#modalAgregarCliente").modal("hide");
					form.reset();
					if ($.fn.DataTable.isDataTable('.tablas1')) {
						$('.tablas1').DataTable().ajax.reload(null, false);
					} else {
						window.location.reload();
					}
				});
			} else {
				swal({
					type: "error",
					title: "¡Error!",
					text: respuesta.mensaje || "No se pudo guardar el cliente.",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				});
			}
		},
		error: function () {
			boton.prop('disabled', false).html(htmlOriginal);
			swal({
				type: "error",
				title: "¡Error!",
				text: "Ocurrió un problema de conexión al guardar el cliente.",
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			});
		}
	});
});

/*=============================================
GUARDAR EDITAR CLIENTE VÍA AJAX
=============================================*/
$(document).on("submit", "#formEditarCliente", function (e) {
	e.preventDefault();

	var form = this;
	var boton = $(form).find("button[type='submit']");
	boton.prop('disabled', true);
	var htmlOriginal = boton.html();
	boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

	swal({
		title: 'Actualizando cliente',
		text: 'Por favor espere mientras se procesa la información...',
		type: 'info',
		allowOutsideClick: false,
		showConfirmButton: false,
		onBeforeOpen: () => {
			swal.showLoading()
		}
	});

	var datos = new FormData(form);
	datos.append("guardarEditarCliente", "ok");

	$.ajax({
		url: "ajax/clientes.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function (respuesta) {
			boton.prop('disabled', false).html(htmlOriginal);

			if (respuesta.status === "ok") {
				swal({
					type: "success",
					title: "¡Éxito!",
					text: respuesta.mensaje,
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				}).then((result) => {
					$("#modalEditarCliente").modal("hide");
					if ($.fn.DataTable.isDataTable('.tablas1')) {
						$('.tablas1').DataTable().ajax.reload(null, false);
					} else {
						window.location.reload();
					}
				});
			} else {
				swal({
					type: "error",
					title: "¡Error!",
					text: respuesta.mensaje || "No se pudo actualizar el cliente.",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				});
			}
		},
		error: function () {
			boton.prop('disabled', false).html(htmlOriginal);
			swal({
				type: "error",
				title: "¡Error!",
				text: "Ocurrió un problema de conexión al actualizar el cliente.",
				showConfirmButton: true,
				confirmButtonText: "Cerrar"
			});
		}
	});
});

/*=============================================
TABLA DE ANÁLISIS DE COMPRAS E INACTIVIDAD DE CLIENTES
=============================================*/
var tablaInactividadClientes = null;

$(document).ready(function () {

    if ($('#tablaAnalisisInactividad').length === 0) return;

    // Inicializar DataTable en la tabla analítica
    tablaInactividadClientes = $('#tablaAnalisisInactividad').DataTable({
        "order": [[5, 'asc']], // Ordenar por días transcurridos ascendente por defecto
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
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
        "dom": '<"row"<"col-sm-6"l><"col-sm-6 text-right">>t<"row"<"col-sm-6"i><"col-sm-6"p>>',
        "autoWidth": false
    });

    // Filtro personalizado por rango de inactividad
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'tablaAnalisisInactividad') {
                return true;
            }

            var filtro = $('#filtroRangoInactividad').val();
            if (!filtro || filtro === 'todos') {
                return true;
            }

            var rowNode = settings.aoData[dataIndex].nTr;
            var rangoFila = $(rowNode).attr('data-rango');

            if (filtro === 'activos') {
                return (rangoFila === 'menos_15' || rangoFila === '15_a_30');
            }

            return rangoFila === filtro;
        }
    );

    // Función para actualizar contador de clientes
    function actualizarContadorInactividad() {
        if (!tablaInactividadClientes) return;
        var info = tablaInactividadClientes.page.info();
        $('#contadorClientesInactividad').html('Mostrando <b>' + info.recordsDisplay + '</b> de <b>' + info.recordsTotal + '</b> clientes');
    }

    // Evento al cambiar selector de rango
    $('#filtroRangoInactividad').on('change', function () {
        var valor = $(this).val();
        
        // Sincronizar estado visual de las tarjetas KPI
        $('.card-kpi-inact').removeClass('active');
        var kpiMatch = $('.card-kpi-inact[data-filtro-kpi="' + valor + '"]');
        if (kpiMatch.length > 0) {
            kpiMatch.addClass('active');
        } else if (valor === 'menos_15' || valor === '15_a_30') {
            $('.card-kpi-inact[data-filtro-kpi="activos"]').addClass('active');
        }

        tablaInactividadClientes.draw();
        actualizarContadorInactividad();
    });

    // Evento de búsqueda rápida en tiempo real
    $('#buscarTablaInactividad').on('keyup input', function () {
        tablaInactividadClientes.search(this.value).draw();
        actualizarContadorInactividad();
    });

    // Clic en tarjetas KPI para filtrar directamente
    $('.card-kpi-inact').on('click', function () {
        var filtro = $(this).attr('data-filtro-kpi');
        $('.card-kpi-inact').removeClass('active');
        $(this).addClass('active');

        $('#filtroRangoInactividad').val(filtro);
        tablaInactividadClientes.draw();
        actualizarContadorInactividad();
    });

    // Botón para restablecer filtros
    $('#btnResetFiltrosInactividad').on('click', function () {
        $('#filtroRangoInactividad').val('todos');
        $('#buscarTablaInactividad').val('');
        $('.card-kpi-inact').removeClass('active');
        $('.card-kpi-inact[data-filtro-kpi="todos"]').addClass('active');

        tablaInactividadClientes.search('').draw();
        actualizarContadorInactividad();
    });

    // Reajustar columnas al alternar pestañas
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (tablaInactividadClientes) {
            tablaInactividadClientes.columns.adjust();
        }
        if (typeof tabla1 !== 'undefined' && tabla1) {
            tabla1.columns.adjust();
        }
    });

    /*=============================================
    EXPORTAR A EXCEL (CLIENTES FILTRADOS VISIBLES)
    =============================================*/
    $('#btnExportarExcelInactividad').on('click', function () {
        if (!tablaInactividadClientes) return;

        var filas = tablaInactividadClientes.rows({ search: 'applied' }).nodes();
        if (filas.length === 0) {
            swal({
                type: "warning",
                title: "Sin registros",
                text: "No hay clientes que coincidan con los filtros aplicados para exportar.",
                showConfirmButton: true,
                confirmButtonText: "Entendido"
            });
            return;
        }

        var fechaActual = new Date().toISOString().slice(0, 10);
        var nombreArchivo = "Analisis_Inactividad_Clientes_" + fechaActual + ".xls";

        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        html += '<head><meta charset="utf-8">';
        html += '<style>';
        html += 'table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; }';
        html += 'th { background-color: #3c8dbc; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #ddd; padding: 6px; }';
        html += 'td { border: 1px solid #e2e8f0; padding: 5px; vertical-align: middle; }';
        html += '.num-text { mso-number-format: "\\@"; }';
        html += '.center { text-align: center; }';
        html += '.right { text-align: right; }';
        html += '</style></head><body>';
        html += '<table>';
        html += '<thead>';
        html += '<tr>';
        html += '<th>#</th>';
        html += '<th>Nombre del Cliente</th>';
        html += '<th>Ciudad</th>';
        html += '<th>Documento</th>';
        html += '<th>Teléfono</th>';
        html += '<th>Correo Electrónico</th>';
        html += '<th>Última Compra</th>';
        html += '<th>Días Transcurridos</th>';
        html += '<th>Total Compras</th>';
        html += '<th>Total Gastado ($)</th>';
        html += '<th>Estado / Semáforo</th>';
        html += '</tr>';
        html += '</thead><tbody>';

        var numItem = 1;
        $(filas).each(function () {
            var $fila = $(this);
            var nombre = $fila.find('td:eq(1) div').text().trim();
            var ciudad = $fila.find('td:eq(1) small').text().replace(/[\n\r]+/g, '').trim();
            var doc = $fila.find('td:eq(2) span').text().trim();
            var tel = $fila.find('td:eq(3) div:first-child').text().replace(/[\n\r]+/g, '').trim();
            var email = $fila.find('td:eq(3) a').text().trim();
            var fecha = $fila.find('td:eq(4) span').text().trim();
            var dias = $fila.find('td:eq(5)').text().replace(/[\n\r]+/g, '').trim();
            var compras = $fila.find('td:eq(6) span').text().trim();
            var total = $fila.find('td:eq(7)').text().trim();
            var estado = $fila.find('td:eq(8) span').text().trim();

            if (!fecha || fecha === '') fecha = 'Sin registro';

            html += '<tr>';
            html += '<td class="center">' + (numItem++) + '</td>';
            html += '<td>' + nombre + '</td>';
            html += '<td>' + ciudad + '</td>';
            html += '<td class="num-text center">' + doc + '</td>';
            html += '<td class="num-text center">' + tel + '</td>';
            html += '<td>' + email + '</td>';
            html += '<td class="center">' + fecha + '</td>';
            html += '<td class="center">' + dias + '</td>';
            html += '<td class="center">' + compras + '</td>';
            html += '<td class="right">' + total + '</td>';
            html += '<td class="center">' + estado + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></body></html>';

        var blob = new Blob(["\ufeff" + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        var link = document.createElement("a");
        var url = URL.createObjectURL(blob);
        link.href = url;
        link.download = nombreArchivo;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        swal({
            type: "success",
            title: "¡Exportación exitosa!",
            text: "Se descargó el archivo Excel con " + (numItem - 1) + " clientes filtrados.",
            timer: 2500,
            showConfirmButton: false
        });
    });

});