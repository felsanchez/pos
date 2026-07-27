/*=============================================
CONFIGURACION DATATABLES ESTANDARIZADA
=============================================*/
var dtVariantesOptions = {
    "autoWidth": false,
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
                    var data = col.data || '';
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
                    finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
                    finalHtml += '<span style="color:#333;">' + data + '</span>';
                    finalHtml += '</div>';
                });
                if (!hasHidden) return false;
                return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
            }
        }
    },
    // Priority: 1:Nombre, 2:Acciones, 3:Estado
    "columnDefs": [
        { "targets": 0, "responsivePriority": 1 },
        { "targets": 2, "responsivePriority": 2, "orderable": false },
        { "targets": 1, "responsivePriority": 3 }
    ],
    "language": {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible",
        "sInfo": "Registros del _START_ al _END_ de _TOTAL_",
        "sInfoEmpty": "Registros del 0 al 0 de 0",
        "sInfoFiltered": "(filtrado de _MAX_ registros)",
        "sSearch": "Buscar:",
        "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
    }
};

$(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#tablaTiposVariantes')) {
        $('#tablaTiposVariantes').DataTable().destroy();
    }
    
    // Configuración específica para Server-Side en Tipos de Variantes
    var dtTiposOptions = $.extend(true, {}, dtVariantesOptions, {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/variantes.ajax.php",
            "type": "POST"
        },
        "columnDefs": [
            { "targets": 0, "responsivePriority": 1 },
            { "targets": 1, "responsivePriority": 2, "orderable": false }
        ]
    });

    $("#tablaTiposVariantes").DataTable(dtTiposOptions);
});

/*=============================================
AUTOCOMPLETAR ORDEN AL ABRIR MODAL DE TIPO
=============================================*/
$(document).on("click", ".btnAbrirModalTipo", function () {

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: {
            obtenerSiguienteOrdenTipo: true
            // csrf_token removido - manejado por csrf-helper.js
        },
        dataType: "json",
        success: function (respuesta) {
            console.log("Siguiente orden tipo:", respuesta);
            $("#nuevoOrdenTipo").val(respuesta);
        },
        error: function () {
            $("#nuevoOrdenTipo").val(1);
        }
    });

});

/*=============================================
REVISAR SI EL TIPO DE VARIANTE YA ESTÁ REGISTRADO
=============================================*/
$(document).on("change", "#nuevoTipoVariante", function () {

    $(".alert").remove();

    var tipoVariante = $(this).val();

    if (tipoVariante.trim() === "") return;

    var datos = new FormData();
    datos.append("validarTipoVariante", tipoVariante);

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            if (respuesta) {
                $("#nuevoTipoVariante").parent().after('<div class="alert alert-warning">¡Este tipo de variante ya existe en la base de datos!</div>');
                $("#nuevoTipoVariante").val("");
            }
        }
    });
});


/*=============================================
AUTOCOMPLETAR ORDEN AL ABRIR MODAL DE OPCIÓN
=============================================*/
$(document).on("click", "[data-target='#modalAgregarOpcion']", function () {

    var idTipo = $("#idTipoVarianteActual").val();

    var datos = new FormData();
    datos.append("obtenerSiguienteOrdenOpcion", idTipo);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            console.log("Siguiente orden opción:", respuesta);
            $("#nuevoOrdenOpcion").val(respuesta);
        },
        error: function () {
            $("#nuevoOrdenOpcion").val(1);
        }
    });

});


/*=============================================
RECARGAR OPCIONES DE UN TIPO DE VARIANTE
=============================================*/
function recargarOpcionesVariante(idTipo, nombreTipo, silencioso) {
    if (!idTipo) return;

    if (nombreTipo) {
        $("#nombreTipoVariante").text(nombreTipo);
    }
    $("#idTipoVarianteActual").val(idTipo);
    $("#idTipoVarianteOpcion").val(idTipo);
    $("#boxOpciones").show();

    var tablaId = "tablaOpciones_" + idTipo;
    var bodyId = "bodyOpciones_" + idTipo;

    if (!silencioso || $("#" + tablaId).length === 0) {
        if ($.fn.DataTable.isDataTable("#" + tablaId)) {
            $("#" + tablaId).DataTable().destroy();
        }
        $(".tabla-opciones").html(
            '<table id="' + tablaId + '" class="table table-bordered table-striped display nowrap" style="width: 100%;">' +
                '<thead>' +
                    '<tr>' +
                        '<th>Nombre</th>' +
                        '<th>Productos</th>' +
                        '<th>Acciones</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody id="' + bodyId + '">' +
                    '<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>' +
                '</tbody>' +
            '</table>'
        );
    }

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: {
            "idTipoVariante": idTipo,
            "csrf_token": $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function (respuesta) {
            var html = "";

            if (respuesta && Array.isArray(respuesta) && respuesta.length > 0) {
                var puedeEditar = $("#puedeEditarVariante").val() == "1";
                var puedeEliminar = $("#puedeEliminarVariante").val() == "1";

                for (var i = 0; i < respuesta.length; i++) {
                    html += '<tr>' +
                            '<td>' + respuesta[i].nombre + '</td>' +
                            '<td><span class="badge bg-blue">' + (respuesta[i].productos_asociados || 0) + '</span></td>' +
                            '<td>' +
                                '<div class="btn-group">';

                    if (puedeEditar) {
                        html += '<button class="btn btn-warning btnEditarOpcion" idOpcion="' + respuesta[i].id + '" data-toggle="modal" data-target="#modalEditarOpcion" title="Editar opción"><i class="fa fa-pencil"></i></button>';
                    } else {
                        html += '<button class="btn btn-warning" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para editar"><i class="fa fa-pencil"></i></button>';
                    }
                    if (puedeEliminar) {
                        html += '<button class="btn btn-danger btnEliminarOpcion" idOpcion="' + respuesta[i].id + '" nombreOpcion="' + respuesta[i].nombre + '" title="Eliminar opción"><i class="fa fa-times"></i></button>';
                    } else {
                        html += '<button class="btn btn-danger" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para eliminar"><i class="fa fa-times"></i></button>';
                    }

                    html += '</div>' +
                            '</td>' +
                            '</tr>';
                }
            } else {
                html = '<tr><td colspan="3" class="text-center">No hay opciones registradas</td></tr>';
            }

            if ($.fn.DataTable.isDataTable("#" + tablaId)) {
                $("#" + tablaId).DataTable().destroy();
            }
            $("#" + bodyId).html(html);

            var localOptions = $.extend(true, {}, dtVariantesOptions);
            $("#" + tablaId).DataTable(localOptions);
        },
        error: function () {
            $("#" + bodyId).html('<tr><td colspan="3" class="text-center text-danger">Error de conexión</td></tr>');
        }
    });
}

$(document).on("click", ".btnVerOpciones", function () {
    var idTipo = $(this).attr("idTipo");
    var nombreTipo = $(this).attr("nombreTipo");
    recargarOpcionesVariante(idTipo, nombreTipo, false);
});



/*=============================================
ACTIVAR/DESACTIVAR TIPO DE VARIANTE CON EFECTO
=============================================*/
$(document).on("click", ".btnActivarTipo", function () {

    var idTipo = $(this).attr("idTipo");
    var estadoTipo = $(this).attr("estadoTipo");
    var boton = $(this);
    var fila = boton.closest('tr');

    // Agregar efecto de fade
    fila.css('opacity', '0.5');

    // Deshabilitar botón temporalmente
    boton.prop('disabled', true);
    var textoOriginal = boton.html();
    boton.html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

    var datos = new FormData();
    datos.append("activarTipo", idTipo);
    datos.append("estadoTipo", estadoTipo);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {

            // Pequeño delay para ver el efecto
            setTimeout(function () {

                if (respuesta == "ok") {

                    // Cambiar el estado del botón con animación
                    if (estadoTipo == 0) {
                        boton.removeClass('btn-success').addClass('btn-danger');
                        boton.html('Desactivado');
                        boton.attr('estadoTipo', 1);
                    } else {
                        boton.removeClass('btn-danger').addClass('btn-success');
                        boton.html('Activado');
                        boton.attr('estadoTipo', 0);
                    }

                    // Efecto de "parpadeo" para indicar cambio
                    fila.css('background-color', '#d4edda');
                    fila.animate({ opacity: 1 }, 300);

                    setTimeout(function () {
                        fila.css('background-color', '');
                    }, 1000);

                    boton.prop('disabled', false);

                } else {
                    boton.html(textoOriginal);
                    boton.prop('disabled', false);
                    fila.css('opacity', '1');

                    swal({
                        type: "error",
                        title: "Error al actualizar el estado",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }

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
    });

});

/*=============================================
ACTIVAR/DESACTIVAR OPCIÓN DE VARIANTE CON EFECTO
=============================================*/
$(document).on("click", ".btnActivarOpcion", function () {

    var idOpcion = $(this).attr("idOpcion");
    var estadoOpcion = $(this).attr("estadoOpcion");
    var boton = $(this);
    var fila = boton.closest('tr');

    // Agregar efecto de fade
    fila.css('opacity', '0.5');

    // Deshabilitar botón temporalmente
    boton.prop('disabled', true);
    var textoOriginal = boton.html();
    boton.html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

    var datos = new FormData();
    datos.append("activarOpcion", idOpcion);
    datos.append("estadoOpcion", estadoOpcion);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {

            // Pequeño delay para ver el efecto
            setTimeout(function () {

                if (respuesta == "ok") {

                    // Cambiar el estado del botón con animación
                    if (estadoOpcion == 0) {
                        boton.removeClass('btn-success').addClass('btn-danger');
                        boton.html('Desactivado');
                        boton.attr('estadoOpcion', 1);
                    } else {
                        boton.removeClass('btn-danger').addClass('btn-success');
                        boton.html('Activado');
                        boton.attr('estadoOpcion', 0);
                    }

                    // Efecto de "parpadeo" para indicar cambio
                    fila.css('background-color', '#d4edda');
                    fila.animate({ opacity: 1 }, 300);

                    setTimeout(function () {
                        fila.css('background-color', '');
                    }, 1000);

                    boton.prop('disabled', false);

                } else {
                    boton.html(textoOriginal);
                    boton.prop('disabled', false);
                    fila.css('opacity', '1');

                    swal({
                        type: "error",
                        title: "Error al actualizar el estado",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }

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
    });

});



/*=============================================
EDITAR TIPO DE VARIANTE
=============================================*/
$(document).on("click", ".btnEditarTipoVariante", function () {

    var idTipo = $(this).attr("idTipo");

    var datos = new FormData();
    datos.append("idTipo", idTipo);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            $("#editarTipoVariante").val(respuesta["nombre"]);
            $("#editarOrdenTipo").val(respuesta["orden"]);
            $("#idTipo").val(respuesta["id"]);

        }
    });

});


/*=============================================
EDITAR OPCIÓN DE VARIANTE
=============================================*/
$(document).on("click", ".btnEditarOpcion", function () {

    var idOpcion = $(this).attr("idOpcion");

    var datos = new FormData();
    datos.append("idOpcionEditar", idOpcion);
    // csrf_token removido - manejado por csrf-helper.js

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {

            console.log("Datos de opción:", respuesta);

            $("#editarOpcion").val(respuesta["nombre"]);
            $("#editarOrdenOpcion").val(respuesta["orden"]);
            $("#idOpcion").val(respuesta["id"]);

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log("Error al cargar opción:", textStatus, errorThrown);
        }
    });

});


/*=============================================
ELIMINAR TIPO DE VARIANTE
=============================================*/

$(document).on("click", ".btnEliminarTipo", function () {

    var idTipo = $(this).attr("idTipo");

    var nombreTipo = $(this).attr("nombreTipo");

    // Primero verificar si tiene uso
    var datosVerificacion = new FormData();
    datosVerificacion.append("idTipoVerificarUso", idTipo);

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datosVerificacion,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            if (respuesta.status === "success" && respuesta.tieneUso) {
                swal({
                    type: "error",
                    title: "¡No se puede eliminar!",
                    text: "Este tipo de variante tiene opciones o está siendo usado en productos",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
                return;
            }

            // Proceder con la confirmación de borrado
            swal({
                title: '¿Está seguro de eliminar el tipo "' + nombreTipo + '"?',
                text: "¡Si no lo está puede cancelar la acción!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar tipo!'
            }).then(function (result) {
                if (result.value) {
                    var datos = new FormData();
                    datos.append("idEliminarTipo", idTipo);

                    $.ajax({
                        url: "ajax/variantes.ajax.php",
                        method: "POST",
                        data: datos,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        success: function (respuesta) {
                            if (respuesta == "ok") {
                                swal({
                                    type: "success",
                                    title: "¡El tipo de variante ha sido eliminado correctamente!",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function (result) {
                                    if ($.fn.DataTable.isDataTable('#tablaTiposVariantes')) {
                                        $('#tablaTiposVariantes').DataTable().ajax.reload(null, false);
                                    } else if ($.fn.DataTable.isDataTable('.tablaTiposVariantes')) {
                                        $('.tablaTiposVariantes').DataTable().ajax.reload(null, false);
                                    }
                                });
                            } else {
                                swal({
                                    type: "error",
                                    title: "¡No se puede eliminar!",
                                    text: "Este tipo de variante tiene opciones o está siendo usado en productos",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                });
                            }
                        }
                    });
                }
            });
        },
        error: function () {
            // Fallback si la verificación falla
            swal({
                title: '¿Está seguro de eliminar el tipo "' + nombreTipo + '"?',
                text: "¡Si no lo está puede cancelar la acción!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar tipo!'
            }).then(function (result) {
                if (result.value) {
                    var datos = new FormData();
                    datos.append("idEliminarTipo", idTipo);

                    $.ajax({
                        url: "ajax/variantes.ajax.php",
                        method: "POST",
                        data: datos,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        success: function (respuesta) {
                            if (respuesta == "ok") {
                                swal({
                                    type: "success",
                                    title: "¡El tipo de variante ha sido eliminado correctamente!",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function (result) {
                                    if ($.fn.DataTable.isDataTable('#tablaTiposVariantes')) {
                                        $('#tablaTiposVariantes').DataTable().ajax.reload(null, false);
                                    } else if ($.fn.DataTable.isDataTable('.tablaTiposVariantes')) {
                                        $('.tablaTiposVariantes').DataTable().ajax.reload(null, false);
                                    }
                                });
                            } else {
                                swal({
                                    type: "error",
                                    title: "¡No se puede eliminar!",
                                    text: "Este tipo de variante tiene opciones o está siendo usado en productos",
                                    showConfirmButton: true,
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
ELIMINAR OPCIÓN DE VARIANTE
=============================================*/

$(document).on("click", ".btnEliminarOpcion", function () {

    var idOpcion = $(this).attr("idOpcion");

    var nombreOpcion = $(this).attr("nombreOpcion");

    // Primero verificar si tiene uso
    var datosVerificacion = new FormData();
    datosVerificacion.append("idOpcionVerificarUso", idOpcion);

    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: datosVerificacion,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            if (respuesta.status === "success" && respuesta.tieneUso) {
                if (respuesta.tipo === "otra_sucursal") {
                    swal({
                        type: "error",
                        title: "¡No se puede eliminar!",
                        text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                } else {
                    swal({
                        type: "error",
                        title: "¡No se puede eliminar!",
                        text: "Esta opción está siendo usada en productos existentes",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
                return;
            }

            // Proceder con la confirmación de borrado
            swal({
                title: '¿Está seguro de eliminar la opción "' + nombreOpcion + '"?',
                text: "¡Si no lo está puede cancelar la acción!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar opción!'
            }).then(function (result) {
                if (result.value) {
                    var datos = new FormData();
                    datos.append("idEliminarOpcion", idOpcion);

                    $.ajax({
                        url: "ajax/variantes.ajax.php",
                        method: "POST",
                        data: datos,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        success: function (respuesta) {
                            if (respuesta == "ok") {
                                swal({
                                    type: "success",
                                    title: "¡La opción ha sido eliminada correctamente!",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function (result) {
                                    var idTipo = $("#idTipoVarianteActual").val();
                                    if (idTipo) {
                                        recargarOpcionesVariante(idTipo, null, true);
                                    }
                                });
                            } else if (respuesta == "error_productos_asociados_otra_sucursal") {
                                swal({
                                    type: "error",
                                    title: "¡No se puede eliminar!",
                                    text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                });
                            } else {
                                swal({
                                    type: "error",
                                    title: "¡No se puede eliminar!",
                                    text: "Esta opción está siendo usada en productos existentes",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                });
                            }
                        }
                    });
                }
            });
        },
        error: function () {
            // Fallback si la verificación falla
            swal({
                title: '¿Está seguro de eliminar la opción "' + nombreOpcion + '"?',
                text: "¡Si no lo está puede cancelar la acción!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar opción!'
            }).then(function (result) {
                if (result.value) {
                    var datos = new FormData();
                    datos.append("idEliminarOpcion", idOpcion);

                    $.ajax({
                        url: "ajax/variantes.ajax.php",
                        method: "POST",
                        data: datos,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        success: function (respuesta) {
                            if (respuesta == "ok") {
                                swal({
                                    type: "success",
                                    title: "¡La opción ha sido eliminada correctamente!",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function (result) {
                                    var idTipo = $("#idTipoVarianteActual").val();
                                    if (idTipo) {
                                        recargarOpcionesVariante(idTipo, null, true);
                                    }
                                });
                            } else if (respuesta == "error_productos_asociados_otra_sucursal") {
                                swal({
                                    type: "error",
                                    title: "¡No se puede eliminar!",
                                    text: "No se puede eliminar porque tiene productos asociados en otra sucursal.",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                });
                            } else {
                                swal({
                                    type: "error",
                                    title: "¡No se puede eliminar!",
                                    text: "Esta opción está siendo usada en productos existentes",
                                    showConfirmButton: true,
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
GUARDAR CREAR TIPO DE VARIANTE VÍA AJAX
=============================================*/
$(document).on("submit", "#formAgregarTipoVariante", function (e) {
    e.preventDefault();

    var form = this;
    var boton = $(form).find("button[type='submit']");
    boton.prop('disabled', true);
    var htmlOriginal = boton.html();
    boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    swal({
        title: 'Guardando tipo de variante',
        text: 'Por favor espere mientras se procesa la información...',
        type: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            swal.showLoading()
        }
    });

    var datos = new FormData(form);
    datos.append("guardarCrearTipoVariante", "ok");

    $.ajax({
        url: "ajax/variantes.ajax.php",
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
                    $("#modalAgregarTipoVariante").modal("hide");
                    form.reset();
                    if ($.fn.DataTable.isDataTable('#tablaTiposVariantes')) {
                        $('#tablaTiposVariantes').DataTable().ajax.reload(null, false);
                    } else if ($.fn.DataTable.isDataTable('.tablaTiposVariantes')) {
                        $('.tablaTiposVariantes').DataTable().ajax.reload(null, false);
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "¡Error!",
                    text: respuesta.mensaje || "No se pudo guardar el tipo de variante.",
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
                text: "Ocurrió un problema de conexión al guardar el tipo de variante.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});

/*=============================================
GUARDAR EDITAR TIPO DE VARIANTE VÍA AJAX
=============================================*/
$(document).on("submit", "#formEditarTipoVariante", function (e) {
    e.preventDefault();

    var form = this;
    var boton = $(form).find("button[type='submit']");
    boton.prop('disabled', true);
    var htmlOriginal = boton.html();
    boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    swal({
        title: 'Actualizando tipo de variante',
        text: 'Por favor espere mientras se procesa la información...',
        type: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            swal.showLoading()
        }
    });

    var datos = new FormData(form);
    datos.append("guardarEditarTipoVariante", "ok");

    $.ajax({
        url: "ajax/variantes.ajax.php",
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
                    $("#modalEditarTipoVariante").modal("hide");
                    if ($.fn.DataTable.isDataTable('#tablaTiposVariantes')) {
                        $('#tablaTiposVariantes').DataTable().ajax.reload(null, false);
                    } else if ($.fn.DataTable.isDataTable('.tablaTiposVariantes')) {
                        $('.tablaTiposVariantes').DataTable().ajax.reload(null, false);
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "¡Error!",
                    text: respuesta.mensaje || "No se pudo actualizar el tipo de variante.",
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
                text: "Ocurrió un problema de conexión al actualizar el tipo de variante.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});

/*=============================================
GUARDAR CREAR OPCIÓN VÍA AJAX
=============================================*/
$(document).on("submit", "#formAgregarOpcion", function (e) {
    e.preventDefault();

    var form = this;
    var boton = $(form).find("button[type='submit']");
    boton.prop('disabled', true);
    var htmlOriginal = boton.html();
    boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    swal({
        title: 'Guardando opción',
        text: 'Por favor espere mientras se procesa la información...',
        type: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            swal.showLoading()
        }
    });

    var datos = new FormData(form);
    datos.append("guardarCrearOpcion", "ok");

    $.ajax({
        url: "ajax/variantes.ajax.php",
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
                    $("#modalAgregarOpcion").modal("hide");
                    form.reset();
                    var idTipo = $("#idTipoVarianteActual").val();
                    if (idTipo) {
                        recargarOpcionesVariante(idTipo, null, true);
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "¡Error!",
                    text: respuesta.mensaje || "No se pudo guardar la opción.",
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
                text: "Ocurrió un problema de conexión al guardar la opción.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});

/*=============================================
GUARDAR EDITAR OPCIÓN VÍA AJAX
=============================================*/
$(document).on("submit", "#formEditarOpcion", function (e) {
    e.preventDefault();

    var form = this;
    var boton = $(form).find("button[type='submit']");
    boton.prop('disabled', true);
    var htmlOriginal = boton.html();
    boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

    swal({
        title: 'Actualizando opción',
        text: 'Por favor espere mientras se procesa la información...',
        type: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        onBeforeOpen: () => {
            swal.showLoading()
        }
    });

    var datos = new FormData(form);
    datos.append("guardarEditarOpcion", "ok");

    $.ajax({
        url: "ajax/variantes.ajax.php",
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
                    $("#modalEditarOpcion").modal("hide");
                    var idTipo = $("#idTipoVarianteActual").val();
                    if (idTipo) {
                        recargarOpcionesVariante(idTipo, null, true);
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "¡Error!",
                    text: respuesta.mensaje || "No se pudo actualizar la opción.",
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
                text: "Ocurrió un problema de conexión al actualizar la opción.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});