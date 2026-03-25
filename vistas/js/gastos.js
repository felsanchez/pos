console.log("✅ Archivo gastos.js cargado correctamente");

$(document).ready(function () {
    console.log("✅ jQuery está funcionando en gastos.js");

    // Inicializar DataTable para tabla de gastos (solo si existe en la página)
    if ($('#tablaGastos').length > 0) {
        console.log("Inicializando tabla de gastos con orden forzado por ID descendente");
        var tablaGastos = $('#tablaGastos').DataTable({
            "destroy": true,
            "stateSave": false,
            "order": [[0, "desc"]],
            "responsive": false,
            "columnDefs": [
                {
                    "targets": 0, // # column
                    "orderable": true,
                },
                {
                    "targets": 8, // Notas
                    "orderable": true
                },
                {
                    "targets": 9, // Acciones
                    "orderable": false
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

    // Inicializar DataTable para tabla de categorías de gastos
    if (!$.fn.DataTable.isDataTable('.tablaCategoriasGastos')) {
        $('.tablaCategoriasGastos').DataTable({
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

            console.log("Gastos filtrados:", respuesta);

            // Limpiar tabla y cards
            $(".tablas1 tbody").empty();
            $(".cards-gastos").empty();

            if (respuesta.length == 0) {
                $(".tablas1 tbody").html('<tr><td colspan="8" class="text-center">No se encontraron gastos con los filtros seleccionados</td></tr>');
                $(".cards-gastos").html('<div class="alert alert-info"><i class="fa fa-info-circle"></i> No se encontraron gastos con los filtros seleccionados</div>');
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

                    // Crear fila
                    var fila = '<tr ' + rowStyle + '>';

                    // Columna 1: Número
                    fila += '<td>' + (index + 1) + '</td>';

                    // Columna 2: Concepto
                    fila += '<td>' + gasto.concepto + '</td>';

                    // Columna 3: Fecha
                    fila += '<td>' + fechaFormateada + '</td>';

                    // Columna 4: Monto
                    fila += '<td><strong>' + monto + '</strong></td>';

                    // Columna 5: Categoría
                    fila += '<td>' + categoriaBadge + '</td>';

                    // Columna 6: Proveedor
                    fila += '<td>' + proveedor + '</td>';

                    // Columna 7: Imagen
                    fila += '<td>' + imagen + '</td>';

                    // Columna 8: Acciones
                    fila += '<td>';
                    fila += '<div class="btn-group">';
                    fila += '<button class="btn btn-warning btnEditarGasto" idGasto="' + gasto.id + '" data-toggle="modal" data-target="#modalEditarGasto"><i class="fa fa-pencil"></i></button>';
                    fila += '<button class="btn btn-danger btnEliminarGasto" idGasto="' + gasto.id + '" codigoGasto="' + gasto.codigo + '" conceptoGasto="' + gasto.concepto + '"><i class="fa fa-times"></i></button>';
                    fila += '</div>';
                    fila += '</td>';

                    fila += '</tr>';

                    $(".tablas1 tbody").append(fila);

                    // CREAR CARD PARA MÓVIL
                    var claseHoy = esHoy ? ' gasto-hoy' : '';
                    var categoriaBadgeCard = gasto.categoria_nombre ?
                        '<span class="badge" style="background-color: ' + gasto.categoria_color + '">' + gasto.categoria_nombre + '</span>' :
                        '<span class="text-muted">Sin categoría</span>';
                    var proveedorCard = gasto.proveedor_nombre ? gasto.proveedor_nombre : 'Sin proveedor';

                    var card = '<div class="card-gasto' + claseHoy + '">';

                    // Header con Concepto y Botones
                    card += '<div class="card-gasto-header">';

                    // Concepto
                    card += '<div class="card-gasto-concepto">';
                    card += gasto.concepto;
                    card += '</div>';

                    // Botones
                    card += '<div class="btn-group">';
                    card += '<button class="btn btn-warning btn-xs btnEditarGasto" idGasto="' + gasto.id + '" data-toggle="modal" data-target="#modalEditarGasto">';
                    card += '<i class="fa fa-pencil"></i>';
                    card += '</button>';
                    card += '<button class="btn btn-danger btn-xs btnEliminarGasto" idGasto="' + gasto.id + '" codigoGasto="' + gasto.codigo + '" conceptoGasto="' + gasto.concepto + '">';
                    card += '<i class="fa fa-times"></i>';
                    card += '</button>';
                    card += '</div>';

                    card += '</div>';

                    // Detalles
                    card += '<div class="card-gasto-detalles">';

                    // Fila 1: Monto y Categoría
                    card += '<div class="card-gasto-fila">';
                    card += '<div class="card-gasto-monto"><i class="fa fa-money"></i> ' + monto + '</div>';
                    card += '<div class="card-gasto-categoria">' + categoriaBadgeCard + '</div>';
                    card += '</div>';

                    // Fila 2: Fecha y Proveedor
                    card += '<div class="card-gasto-fila">';
                    card += '<div class="card-gasto-fecha"><i class="fa fa-calendar"></i> ' + fechaFormateada + '</div>';
                    card += '<div class="card-gasto-proveedor"><i class="fa fa-user"></i> ' + proveedorCard + '</div>';
                    card += '</div>';

                    card += '</div>';

                    // Imagen (Botón)
                    var imagenGasto = (gasto.imagen_comprobante && gasto.imagen_comprobante != '') ? gasto.imagen_comprobante : "";

                    card += '<div class="card-gasto-imagen-icono img-comprobante-clickeable" data-imagen="' + imagenGasto + '" data-idgasto="' + gasto.id + '" data-concepto="' + gasto.concepto + '">';
                    card += '<i class="fa fa-image"></i> Ver imagen';
                    card += '</div>';

                    card += '</div>';

                    $(".cards-gastos").append(card);
                });

            }

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error en AJAX:", textStatus, errorThrown);
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