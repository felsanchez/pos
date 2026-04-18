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
                "order": [[8, "desc"]], // Ordenar por fecha (columna 8) desc
                "columnDefs": [
                    {
                        "targets": [0], // Concepto
                        "className": "dtr-control",
                        "responsivePriority": 1
                    },
                    {
                        "targets": [8], // Acciones
                        "orderable": false,
                        "responsivePriority": 2
                    }
                ],
                "responsive": {
                    "details": {
                        "type": "column",
                        "target": 0, // Click de expansión exclusivo sobre el Concepto
                        "renderer": function (api, rowIdx, columns) {

                            function getVal(idx) {
                                return api.cell(rowIdx, idx).render('display');
                            }

                            var finalHtml = '';

                            // SECCIÓN 1: Detalles del Gasto (Concepto(0), Monto(1), Fecha(8))
                            finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                            finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Detalles del Gasto</h5></div>';

                            finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Concepto: </span><span class="pull-right">' + getVal(0) + '</span></div>';

                            finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Monto: </span><span class="pull-right">' + getVal(1) + '</span></div>';

                            finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Fecha: </span><span class="pull-right">' + getVal(7) + '</span></div>';

                            // SECCIÓN 2: Clasificación (Categoría(2), Estado(3))
                            finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                            finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Clasificación</h5></div>';

                            finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Categoría: </span><span class="pull-right">' + getVal(2) + '</span></div>';

                            finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Estado: </span><span class="pull-right">' + getVal(3) + '</span></div>';

                            // SECCIÓN 3: Información Adicional (Proveedor(4), Imagen(5), Notas(6))
                            finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                            finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Información Adicional</h5></div>';

                            finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Proveedor: </span><span class="pull-right">' + getVal(4) + '</span></div>';
                            
                            finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold">Comprobante: </span><span class="pull-right">' + getVal(5) + '</span></div>';

                            finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                            finalHtml += '<span class="text-bold" style="display:block; margin-bottom:4px;">Notas:</span>';
                            finalHtml += '<span>' + getVal(6) + '</span></div>';

                            return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0; text-align: left;">').append(finalHtml) : false;
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
});

/*=============================================
EDITAR GASTO
=============================================*/

$(document).on("click", ".btnEditarGasto", function () {

    var idGasto = $(this).attr("idGasto");
    console.log("ID Gasto: " + idGasto);

    // Rellenar el input hidden
    $('#modalEditarGasto input[name="idGasto"]').val(idGasto);

    var datos = new FormData();
    datos.append("idGasto", idGasto);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({

        url: "ajax/gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            console.log("Respuesta AJAX:", respuesta);

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

            // Mostrar preview de imagen si existe
            if (respuesta["imagen_comprobante"] != "" && respuesta["imagen_comprobante"] != null) {
                $("#previsualizarImagen").html('<img src="' + respuesta["imagen_comprobante"] + '" class="img-thumbnail img-ampliar-gasto" style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">');
            } else {
                $("#previsualizarImagen").html('');
            }

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX:", textStatus, errorThrown);
        }

    })

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
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/gastos.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							icon: "success",
							title: "¡Eliminado!",
							text: "El gasto ha sido eliminado correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location.reload();
							}
						});
					} else {
						swal({
							icon: "error",
							title: "Error",
							text: "No se pudo eliminar el gasto. " + respuesta,
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
EDITAR CATEGORÍA DE GASTO
=============================================*/

$("#modalGestionarCategorias").on("click", ".btnEditarCategoriaGasto", function () {

    var idCategoria = $(this).attr("idCategoria");
    console.log("ID Categoría: " + idCategoria);

    // Rellenar el input hidden
    $('#modalEditarCategoria input[name="idCategoriaGasto"]').val(idCategoria);

    var datos = new FormData();
    datos.append("idCategoria", idCategoria);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({

        url: "ajax/categorias_gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            console.log("Respuesta AJAX Categoría:", respuesta);

            $("#editarNombreCategoriaGasto").val(respuesta["nombre"]);
            $("#editarColorCategoriaGasto").val(respuesta["color"]);
            $("#editarDescripcionCategoriaGasto").val(respuesta["descripcion"]);

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX:", textStatus, errorThrown);
        }

    })

});

/*=============================================
ELIMINAR CATEGORÍA DE GASTO
=============================================*/

$("#modalGestionarCategorias").on("click", ".btnEliminarCategoriaGasto", function () {

	var idCategoria = $(this).attr("idCategoria");
	var nombreCategoria = $(this).attr("nombreCategoria");

	swal({

		title: '¿Está seguro de eliminar la categoría "' + nombreCategoria + '"?',
		text: "¡Si no lo está puede cancelar la acción!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Sí, eliminar categoría!'
	}).then((result) => {

		if (result.value) {

			var datos = new FormData();
			datos.append("idCategoriaGastoEliminar", idCategoria);
			// csrf_token removido - manejado por csrf-helper.js

			$.ajax({
				url: "ajax/categorias_gastos.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				success: function (respuesta) {
					if (respuesta == "ok") {
						swal({
							icon: "success",
							title: "¡Eliminada!",
							text: "La categoría ha sido eliminada correctamente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if (result.value) {
								window.location.reload();
							}
						});
					} else if (respuesta == "error_gastos_asociados") {
						swal({
							icon: "error",
							title: "¡No se puede eliminar!",
							text: "Esta categoría tiene gastos asociados.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					} else {
						swal({
							icon: "error",
							title: "Error",
							text: "No se pudo eliminar la categoría. " + respuesta,
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
AMPLIAR Y EDITAR IMAGEN COMPROBANTE
=============================================*/

// Ampliar imagen de comprobante al hacer clic desde la tabla
$(document).on("click", ".img-comprobante-clickeable", function () {
    var rutaImagen = $(this).attr("data-imagen");
    var idGasto = $(this).attr("data-idgasto");
    var concepto = $(this).attr("data-concepto");

    // Si no hay imagen, mostrar placeholder
    if (!rutaImagen || rutaImagen === "") {
        rutaImagen = "vistas/img/gastos/default/sin-imagen.png";
    }

    console.log("ID Gasto:", idGasto);
    console.log("Concepto:", concepto);
    console.log("Ruta Imagen:", rutaImagen);

    $("#imagenComprobanteAmpliada").attr("src", rutaImagen);
    $("#idGastoImagen").val(idGasto);
    $("#conceptoGasto").val(concepto);
    $(".nuevaImagenComprobante").val("");
    $("#modalAmpliarComprobanteGasto").modal("show");
});

// Ampliar imagen desde el modal de editar (thumbnail)
$(document).on("click", ".img-ampliar-gasto", function () {
    var rutaImagen = $(this).attr("src");
    $("#imagenComprobanteAmpliada").attr("src", rutaImagen);
    $("#modalAmpliarComprobanteGasto").modal("show");
});

// Previsualizar nueva imagen cuando se selecciona
$(document).on("change", ".nuevaImagenComprobante", function () {
    var imagen = this.files[0];

    if (imagen) {
        if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
            $(".nuevaImagenComprobante").val("");
            swal({
                title: "Error al subir la imagen",
                text: "¡La imagen debe estar en formato JPG o PNG!",
                icon: "error",
                confirmButtonText: "¡Cerrar!"
            });
        } else if (imagen["size"] > 2000000) {
            $(".nuevaImagenComprobante").val("");
            swal({
                title: "Error al subir la imagen",
                text: "¡La imagen no debe pesar más de 2MB!",
                icon: "error",
                confirmButtonText: "¡Cerrar!"
            });
        } else {
            var datosImagen = new FileReader;
            datosImagen.readAsDataURL(imagen);

            $(datosImagen).on("load", function (event) {
                var rutaImagen = event.target.result;
                $("#imagenComprobanteAmpliada").attr("src", rutaImagen);
            });
        }
    }
});

// Guardar la nueva imagen del comprobante
$(document).on("click", ".btnGuardarImagenComprobante", function () {

    var idGasto = $("#idGastoImagen").val();
    var concepto = $("#conceptoGasto").val();
    var imagen = $(".nuevaImagenComprobante")[0].files[0];

    console.log("ID al guardar:", idGasto);
    console.log("Concepto al guardar:", concepto);
    console.log("Imagen al guardar:", imagen);

    if (!imagen) {
        swal({
            title: "Advertencia",
            text: "No has seleccionado ninguna imagen",
            icon: "warning",
            confirmButtonText: "¡Cerrar!"
        });
        return;
    }

    if (!idGasto) {
        swal({
            title: "Error",
            text: "No se pudo obtener el ID del gasto",
            icon: "error",
            confirmButtonText: "¡Cerrar!"
        });
        return;
    }

    var datos = new FormData();
    datos.append("idGastoImagen", idGasto);
    datos.append("conceptoGasto", concepto);
    datos.append("nuevaImagenComprobante", imagen);
    // csrf_token removido - manejado por csrf-helper.js

    // Mostrar loading
    swal({
        title: 'Cargando...',
        allowOutsideClick: false,
        didOpen: () => {
            swal.showLoading()
        }
    });

    $.ajax({
        url: "ajax/gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            console.log("Respuesta del servidor:", respuesta);

            if (respuesta == "ok") {
                swal({
                    icon: "success",
                    title: "¡La imagen ha sido actualizada correctamente!",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function (result) {
                    if (result.value) {
                        window.location = "gastos";
                    }
                });
            } else {
                swal({
                    icon: "error",
                    title: "Error al actualizar la imagen",
                    text: respuesta,
                    confirmButtonText: "¡Cerrar!"
                });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX:", textStatus, errorThrown);
            console.error("Respuesta:", jqXHR.responseText);
            swal({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                confirmButtonText: "¡Cerrar!"
            });
        }
    });
});

/*=============================================
FILTRAR GASTOS
=============================================*/

$("#btnFiltrarGastos").on("click", function () {

    var fechaInicio = $("#filtroFechaInicio").val();
    var fechaFin = $("#filtroFechaFin").val();
    var categoria = $("#filtroCategoria").val();
    var proveedor = $("#filtroProveedor").val();

    console.log("Filtros:", fechaInicio, fechaFin, categoria, proveedor);

    // Destruir instancia previa de DataTable para poder reconstruir
    if ($.fn.DataTable.isDataTable('#tablaGastos')) {
        $('#tablaGastos').DataTable().destroy();
    }

    var datos = new FormData();
    datos.append("accion", "filtrarGastos");
    datos.append("fechaInicio", fechaInicio);
    datos.append("fechaFin", fechaFin);
    datos.append("categoria", categoria);
    datos.append("proveedor", proveedor);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({

        url: "ajax/gastos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            console.log("Gastos filtrados respuesta:", respuesta);

            // Limpiar tabla
            $("#tablaGastos tbody").empty();

            if (!respuesta || !Array.isArray(respuesta) || respuesta.length == 0) {
                $("#tablaGastos tbody").html('<tr><td colspan="9" class="text-center">No se encontraron gastos con los filtros seleccionados</td></tr>');
            } else {

                // Llenar tabla con resultados
                respuesta.forEach(function (gasto, index) {

                    // Formatear fecha
                    var fecha = gasto.fecha ? new Date(gasto.fecha + 'T00:00:00') : null;
                    var fechaFormateada = fecha ? ("0" + fecha.getDate()).slice(-2) + "/" +
                        ("0" + (fecha.getMonth() + 1)).slice(-2) + "/" +
                        fecha.getFullYear() : '-';

                    // Verificar si es hoy
                    var hoy = new Date();
                    var esHoy = fecha && fecha.toDateString() === hoy.toDateString();
                    var rowStyle = esHoy ? 'style="border-left: 6px solid #28a745 !important; background-color: #f0f9f4; box-shadow: inset 6px 0 0 #28a745;"' : '';

                    // Categoría badge
                    var categoriaBadge = '';
                    if (gasto.categoria_nombre) {
                        categoriaBadge = '<span class="badge" style="background-color: ' + gasto.categoria_color + '">' + gasto.categoria_nombre + '</span>';
                    } else {
                        categoriaBadge = '-';
                    }

                    // Estado badge
                    var estadoBadge = '';
                    if (gasto.estado == "aprobado") {
                        estadoBadge = '<button class="btn btn-success btn-xs">Aprobado</button>';
                    } else if (gasto.estado == "pendiente") {
                        estadoBadge = '<button class="btn btn-warning btn-xs">Pendiente</button>';
                    } else {
                        estadoBadge = '<button class="btn btn-danger btn-xs">Rechazado</button>';
                    }

                    // Formatear monto
                    var monto = gasto.monto ? '$' + parseFloat(gasto.monto).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-';

                    // Proveedor
                    var proveedor = gasto.proveedor_nombre ? gasto.proveedor_nombre : '-';

                    // Imagen
                    var imagen = '';
                    if (gasto.imagen_comprobante && gasto.imagen_comprobante != '') {
                        imagen = '<img src="' + gasto.imagen_comprobante + '" class="img-thumbnail img-comprobante-clickeable" width="40px" style="cursor: pointer;" data-imagen="' + gasto.imagen_comprobante + '" data-idgasto="' + gasto.id + '" data-concepto="' + gasto.concepto + '">';
                    } else {
                        imagen = '<img src="vistas/img/gastos/default/sin-imagen.png" class="img-thumbnail img-comprobante-clickeable" width="40px" style="cursor: pointer;" data-imagen="" data-idgasto="' + gasto.id + '" data-concepto="' + gasto.concepto + '">';
                    }

                    // Notas (editable)
                    var notas = gasto.notas ? gasto.notas : '';

                    // Crear fila (9 columnas)
                    var fila = '<tr ' + rowStyle + '>';
                    fila += '<td>' + (gasto.concepto || '-') + '</td>';
                    fila += '<td><strong>' + monto + '</strong></td>';

                    fila += '<td>' + categoriaBadge + '</td>';
                    fila += '<td>' + estadoBadge + '</td>';
                    fila += '<td>' + proveedor + '</td>';
                    fila += '<td>' + imagen + '</td>';
                    fila += '<td contenteditable="true" class="celda-notas-gasto" data-id="' + gasto.id + '">' + notas + '</td>';
                    fila += '<td>' + fechaFormateada + '</td>';
                    
                    fila += '<td>';
                    fila += '<div class="btn-group">';
                    fila += '<button class="btn btn-warning btnEditarGasto" idGasto="' + gasto.id + '" data-toggle="modal" data-target="#modalEditarGasto"><i class="fa fa-pencil"></i></button>';
                    fila += '<button class="btn btn-danger btnEliminarGasto" idGasto="' + gasto.id + '" conceptoGasto="' + (gasto.concepto || '') + '"><i class="fa fa-times"></i></button>';
                    fila += '</div>';
                    fila += '</td>';

                    fila += '</tr>';

                    $("#tablaGastos tbody").append(fila);
                });
            }

            // Reinicializar DataTable tras cargar los nuevos datos
            if (typeof window.inicializarTablaGastos === 'function') {
                window.inicializarTablaGastos();
            }

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("❌ Error en AJAX de filtrado:", textStatus, errorThrown);
            console.error("Respuesta cruda del servidor:", jqXHR.responseText);
        }

    })

});

/*=============================================
EDITAR NOTAS (CONTENTEDITABLE - DESKTOP Y MÓVIL)
=============================================*/

// Usar event delegation para que funcione con elementos dinámicos (desktop y móvil)
$(document).on('blur', '.celda-notas-gasto', function () {
    const id = $(this).data('id');
    const nuevasNotas = $(this).text().trim();
    const $celdaActual = $(this);

    if (!id) {
        console.error('ERROR: No se encontró el ID del gasto');
        return;
    }

    // Añadir clase de guardando
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
            console.log('Notas actualizadas exitosamente');
            $celdaActual.removeClass('guardando');

            // Sincronizar con todas las celdas del mismo gasto (desktop y móvil)
            $('.celda-notas-gasto[data-id="' + id + '"]').not($celdaActual).each(function () {
                $(this).text(nuevasNotas);
            });
        },

        error: function (xhr, status, error) {
            console.error('Error al actualizar las notas:', error);
            $celdaActual.removeClass('guardando');

            swal({
                icon: "error",
                title: "Error al guardar la nota",
                text: "No se pudo guardar la nota. Por favor, intente nuevamente.",
                confirmButtonText: "Cerrar"
            });
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

    // Resetear texto del botón de rango
    $("#daterange-btn span").html('<i class="fa fa-calendar"></i> Rango de fecha');

    // Disparar el filtrado con campos vacíos (esto recargará todo)
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
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function (start, end) {
            $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

            var fechaInicial = start.format('YYYY-MM-DD');
            var fechaFinal = end.format('YYYY-MM-DD');

            // Actualizar inputs ocultos
            $("#filtroFechaInicio").val(fechaInicial);
            $("#filtroFechaFin").val(fechaFinal);
        }
    );
}