console.log("✅ Archivo gastos.js cargado correctamente");

$(document).ready(function () {
    console.log("✅ jQuery está funcionando en gastos.js");

    // Inicializar Select2 para los filtros si están presentes
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%'
        });
    }

    // Función para inicializar DataTable en Gastos de forma global
    window.inicializarTablaGastos = function() {
        if ($('#tablaGastos').length > 0) {
            console.log("Inicializando tabla de gastos...");
            
            // Si ya existe, destruirla para evitar errores al re-filtrar
            if ($.fn.DataTable.isDataTable('#tablaGastos')) {
                $('#tablaGastos').DataTable().destroy();
            }
            return $('#tablaGastos').DataTable({
                "autoWidth": false,
                "order": [], // Respetar el orden del servidor (id DESC) para mostrar los más recientes arriba
                "columnDefs": [
                    { 
                        "targets": 0, 
                        "className": "dtr-control",
                        "responsivePriority": 1 
                    }, // Concepto (Siempre visible)
                    { "targets": 8, "responsivePriority": 2, "orderable": false }, // Acciones (Casi siempre visible)
                    { "targets": 1, "responsivePriority": 3 }, // Monto
                    { "targets": 6, "responsivePriority": 4 }, // Fecha
                    { "targets": 3, "responsivePriority": 5 }, // Estado
                    { "targets": 2, "responsivePriority": 6 }, // Categoría
                    { "targets": 4, "responsivePriority": 7 }, // Proveedor
                    { "targets": 5, "responsivePriority": 8 }, // Imagen
                    { "targets": 7, "responsivePriority": 9 }  // Notas (La primera en ocultarse)
                ],
                "responsive": {
                    "details": {
                        "type": "column",
                        "target": 0, // Click de expansión exclusivo sobre el Concepto
                        "renderer": function (api, rowIdx, columns) {
                            var finalHtml = '';
                            var hasHidden = false;

                            $.each(columns, function (i, col) {
                                if (!col.hidden) return;
                                hasHidden = true;

                                var label = col.title || ('Columna ' + col.columnIndex);
                                
                                finalHtml += '<div style="padding:8px 10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px; text-align:left;">';
                                finalHtml += '<span class="text-bold" style="color:#555; min-width:100px;">' + label + ':</span>';
                                
                                // Si es la columna de notas (ahora índice 7)
                                if (col.columnIndex === 7) {
                                    // Preservar la capacidad de edición incluso en la vista expandida
                                    var rowNode = api.row(rowIdx).node();
                                    var idGasto = $(rowNode).find('.celda-notas-gasto').data('id') || "";
                                    var notasText = $(rowNode).find('.celda-notas-gasto').text().trim();
                                    
                                    finalHtml += '<div contenteditable="true" class="celda-notas-gasto" data-id="' + idGasto + '" style="flex:1; outline:none; border:1px dashed #ccc; padding:6px; background:#fff9e6; margin-top:5px; width:100%;">' + notasText + '</div>';
                                } else {
                                    finalHtml += '<span style="color:#333;">' + col.data + '</span>';
                                }
                                finalHtml += '</div>';
                            });

                            if (!hasHidden) return false;
                            return $('<div style="padding:0; background:#fcfcfc; width:100%;">').append(finalHtml);
                        }
                    }
                },
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sSearch": "Buscar:",
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
                },
                "dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
                "preDrawCallback": function () {
                    if (!$(this).hasClass('datatable-ready')) {
                        $(this).css('visibility', 'hidden');
                    }
                },
                "initComplete": function () {
                    $(this).addClass('datatable-ready').css('visibility', 'visible');
                }
            });

        }
    };

    window.inicializarTablaGastos();

    // Inicializar DataTable para tabla de categorías de gastos
    if (!$.fn.DataTable.isDataTable('.tablaCategoriasGastos')) {
        $('.tablaCategoriasGastos').DataTable({
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

    /*=============================================
    FILTRAR GASTOS
    =============================================*/
    $("#btnFiltrarGastos").on("click", function () {
        var fechaInicio = $("#filtroFechaInicio").val();
        var fechaFin = $("#filtroFechaFin").val();
        var categoria = $("#filtroCategoria").val();
        var proveedor = $("#filtroProveedor").val();

        console.log("Filtros:", fechaInicio, fechaFin, categoria, proveedor);

        if ($.fn.DataTable.isDataTable('#tablaGastos')) {
            $('#tablaGastos').DataTable().destroy();
        }

        var datos = new FormData();
        datos.append("accion", "filtrarGastos");
        datos.append("fechaInicio", fechaInicio);
        datos.append("fechaFin", fechaFin);
        datos.append("categoria", categoria);
        datos.append("proveedor", proveedor);

        $.ajax({
            url: "ajax/gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                $("#tablaGastos tbody").empty();

                if (!respuesta || !Array.isArray(respuesta) || respuesta.length == 0) {
                    $("#tablaGastos tbody").html('<tr><td colspan="9" class="text-center">No se encontraron gastos con los filtros seleccionados</td></tr>');
                } else {
                    respuesta.forEach(function (gasto) {
                        var fecha = gasto.fecha ? new Date(gasto.fecha + 'T00:00:00') : null;
                        var fechaFormateada = fecha ? ("0" + fecha.getDate()).slice(-2) + "/" +
                            ("0" + (fecha.getMonth() + 1)).slice(-2) + "/" +
                            fecha.getFullYear() : '-';

                        var hoy = new Date();
                        var esHoy = fecha && fecha.toDateString() === hoy.toDateString();
                        var rowStyle = esHoy ? 'style="border-left: 6px solid #28a745 !important; background-color: #f0f9f4; box-shadow: inset 6px 0 0 #28a745;"' : '';

                        var categoriaBadge = gasto.categoria_nombre ? '<span class="badge" style="background-color: ' + gasto.categoria_color + '">' + gasto.categoria_nombre + '</span>' : '-';
                        
                        var estadoBadge = '';
                        if (gasto.estado == "aprobado") estadoBadge = '<button class="btn btn-success btn-xs">Aprobado</button>';
                        else if (gasto.estado == "pendiente") estadoBadge = '<button class="btn btn-warning btn-xs">Pendiente</button>';
                        else estadoBadge = '<button class="btn btn-danger btn-xs">Rechazado</button>';

                        var monto = gasto.monto ? '$' + parseFloat(gasto.monto).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';
                        var imagen = gasto.imagen_comprobante ? '<img src="' + gasto.imagen_comprobante + '" class="img-thumbnail img-comprobante-clickeable" width="40px" style="cursor: pointer;" data-imagen="' + gasto.imagen_comprobante + '" data-idgasto="' + gasto.id + '" data-concepto="' + gasto.concepto + '">' : '<img src="vistas/img/gastos/default/sin-imagen.png" class="img-thumbnail img-comprobante-clickeable" width="40px" style="cursor: pointer;" data-imagen="" data-idgasto="' + gasto.id + '" data-concepto="' + gasto.concepto + '">';

                        var fila = '<tr ' + rowStyle + '>';
                        fila += '<td class="dtr-control">' + (gasto.concepto || '-') + '</td>';
                        fila += '<td><strong>' + monto + '</strong></td>';
                        fila += '<td>' + categoriaBadge + '</td>';
                        fila += '<td>' + estadoBadge + '</td>';
                        fila += '<td>' + (gasto.proveedor_nombre || '-') + '</td>';
                        fila += '<td>' + imagen + '</td>';
                        fila += '<td>' + fechaFormateada + '</td>';
                        fila += '<td contenteditable="true" class="celda-notas-gasto" data-id="' + gasto.id + '">' + (gasto.notas || '') + '</td>';
                        
                        fila += '<td><div class="btn-group">';
                        fila += '<button class="btn btn-warning btnEditarGasto" idGasto="' + gasto.id + '" data-toggle="modal" data-target="#modalEditarGasto"><i class="fa fa-pencil"></i></button>';
                        fila += '<button class="btn btn-danger btnEliminarGasto" idGasto="' + gasto.id + '" conceptoGasto="' + (gasto.concepto || '') + '"><i class="fa fa-times"></i></button>';
                        fila += '</div></td></tr>';

                        $("#tablaGastos tbody").append(fila);
                    });
                }
                window.inicializarTablaGastos();
            }
        });
    });

    /*=============================================
    LIMPIAR FILTROS GASTOS
    =============================================*/
    $("#btnLimpiarGastos").on("click", function () {
        $("#filtroFechaInicio").val("");
        $("#filtroFechaFin").val("");
        $("#filtroCategoria").val("").trigger('change');
        $("#filtroProveedor").val("").trigger('change');
        $("#daterange-btn span").html('<i class="fa fa-calendar"></i> Rango de fecha');
        $("#btnFiltrarGastos").click();
    });

    /*=============================================
    RANGO DE FECHAS GASTOS
    =============================================*/
    if ($('#daterange-btn').length > 0) {
        $('#daterange-btn').daterangepicker(
            {
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),
                opens: 'left'
            },
            function (start, end) {
                $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                $("#filtroFechaInicio").val(start.format('YYYY-MM-DD'));
                $("#filtroFechaFin").val(end.format('YYYY-MM-DD'));
            }
        );

        // Inicializar los inputs ocultos con el valor por defecto al cargar
        var drp = $('#daterange-btn').data('daterangepicker');
        if (drp) {
            $("#filtroFechaInicio").val(drp.startDate.format('YYYY-MM-DD'));
            $("#filtroFechaFin").val(drp.endDate.format('YYYY-MM-DD'));
        }
    }

});

/*=============================================
EDITAR GASTO
=============================================*/
$(document).on("click", ".btnEditarGasto", function () {
    var idGasto = $(this).attr("idGasto");
    $('#modalEditarGasto input[name="idGasto"]').val(idGasto);

    var datos = new FormData();
    datos.append("idGasto", idGasto);

    $.ajax({
        url: "ajax/gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            $("#editarConceptoGasto").val(respuesta["concepto"]);
            $("#editarMontoGasto").val(respuesta["monto"]);
            $("#editarFechaGasto").val(respuesta["fecha"]);
            $("#editarCategoriaGasto").val(respuesta["id_categoria_gasto"]);
            $("#editarProveedorGasto").val(respuesta["id_proveedor"]);
            $("#editarMetodoPagoGasto").val(respuesta["metodo_pago"]);
            $("#editarNumeroComprobante").val(respuesta["numero_comprobante"]);
            $("#editarEstadoGasto").val(respuesta["estado"]);
            $("#editarNotasGasto").val(respuesta["notas"]);
            $("#imagenActual").val(respuesta["imagen_comprobante"]);

            if (respuesta["imagen_comprobante"] != "" && respuesta["imagen_comprobante"] != null) {
                $("#previsualizarImagen").html('<img src="' + respuesta["imagen_comprobante"] + '" class="img-thumbnail img-ampliar-gasto" style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">');
            } else {
                $("#previsualizarImagen").html('');
            }
        }
    });
});

/*=============================================
ELIMINAR GASTO
=============================================*/
$(document).on("click", ".btnEliminarGasto", function () {
	var idGasto = $(this).attr("idGasto");
	var conceptoGasto = $(this).attr("conceptoGasto");

	swal({
		title: '¿Está seguro de eliminar el gasto: "' + conceptoGasto + '"?',
		text: "¡Si no lo está puede cancelar la acción!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, eliminar gasto!'
	}).then((result) => {
		if (result.value) {
			var datos = new FormData();
			datos.append("idGastoEliminar", idGasto);
			$.ajax({
				url: "ajax/gastos.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({ icon: "success", title: "¡Eliminado!", text: "El gasto ha sido eliminado correctamente." }).then(() => { location.reload(); });
					} else {
						swal({ icon: "error", title: "Error", text: respuesta });
					}
				}
			})
		}
	})
});

/*=============================================
GESTIÓN DE CATEGORÍAS (EDITAR/ELIMINAR)
=============================================*/
$("#modalGestionarCategorias").on("click", ".btnEditarCategoriaGasto", function () {
    var idCategoria = $(this).attr("idCategoria");
    $('#modalEditarCategoria input[name="idCategoriaGasto"]').val(idCategoria);

    var datos = new FormData();
    datos.append("idCategoria", idCategoria);

    $.ajax({
        url: "ajax/categorias_gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            $("#editarNombreCategoriaGasto").val(respuesta["nombre"]);
            $("#editarColorCategoriaGasto").val(respuesta["color"]);
            $("#editarDescripcionCategoriaGasto").val(respuesta["descripcion"]);
        }
    });
});

$("#modalGestionarCategorias").on("click", ".btnEliminarCategoriaGasto", function () {
	var idCategoria = $(this).attr("idCategoria");
	var nombreCategoria = $(this).attr("nombreCategoria");

	swal({
		title: '¿Está seguro de eliminar la categoría "' + nombreCategoria + '"?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Sí, eliminar!'
	}).then((result) => {
		if (result.value) {
			var datos = new FormData();
			datos.append("idCategoriaGastoEliminar", idCategoria);
			$.ajax({
				url: "ajax/categorias_gastos.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({ icon: "success", title: "¡Eliminada!" }).then(() => { location.reload(); });
					} else {
						swal({ icon: "error", title: "Error", text: respuesta });
					}
				}
			})
		}
	})
});

/*=============================================
IMAGEN COMPROBANTE (AMPLIAR/CAMBIAR)
=============================================*/
$(document).on("click", ".img-comprobante-clickeable, .img-ampliar-gasto", function () {
    var rutaImagen = $(this).attr("data-imagen") || $(this).attr("src");
    var idGasto = $(this).attr("data-idgasto");
    var concepto = $(this).attr("data-concepto");

    if (!rutaImagen || rutaImagen === "") rutaImagen = "vistas/img/gastos/default/sin-imagen.png";

    $("#imagenComprobanteAmpliada").attr("src", rutaImagen);
    $("#idGastoImagen").val(idGasto);
    $("#conceptoGasto").val(concepto);
    $(".nuevaImagenComprobante").val("");
    $("#modalAmpliarComprobanteGasto").modal("show");
});

$(document).on("change", ".nuevaImagenComprobante", function () {
    var imagen = this.files[0];
    if (imagen) {
        if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
            $(".nuevaImagenComprobante").val("");
            swal({ title: "Error", text: "Formato inválido", icon: "error" });
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(imagen);
            reader.onload = e => $("#imagenComprobanteAmpliada").attr("src", e.target.result);
        }
    }
});

$(document).on("click", ".btnGuardarImagenComprobante", function () {
    var idGasto = $("#idGastoImagen").val();
    var concepto = $("#conceptoGasto").val();
    var imagen = $(".nuevaImagenComprobante")[0].files[0];

    if (!imagen) return;

    var datos = new FormData();
    datos.append("idGastoImagen", idGasto);
    datos.append("conceptoGasto", concepto);
    datos.append("nuevaImagenComprobante", imagen);

    $.ajax({
        url: "ajax/gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            if (respuesta == "ok") {
                swal({ icon: "success", title: "Imagen actualizada" }).then(() => { location.reload(); });
            }
        }
    });
});

/*=============================================
EDITAR NOTAS (CONTENTEDITABLE)
=============================================*/
$(document).on('blur', '.celda-notas-gasto', function () {
    const id = $(this).data('id');
    const nuevasNotas = $(this).text().trim();
    const $celdaActual = $(this);

    if (!id) return;

    $celdaActual.addClass('guardando');

    $.ajax({
        url: 'ajax/gastos-actualizar-nota.ajax.php',
        method: 'POST',
        data: {
            idGasto: id,
            nota: nuevasNotas,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (respuesta) {
            $celdaActual.removeClass('guardando');
            $('.celda-notas-gasto[data-id="' + id + '"]').css('background-color', '#dff0d8');
            setTimeout(() => {
                $('.celda-notas-gasto[data-id="' + id + '"]').css('background-color', '');
            }, 500);
            $('.celda-notas-gasto[data-id="' + id + '"]').not($celdaActual).text(nuevasNotas);
        }
    });
});