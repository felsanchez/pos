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
        }
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
VER OPCIONES DE UN TIPO DE VARIANTE
=============================================*/
$(document).on("click", ".btnVerOpciones", function () {

    var idTipo = $(this).attr("idTipo");
    var nombreTipo = $(this).attr("nombreTipo");
    var tablaId = "tablaOpciones_" + idTipo;
    var bodyId = "bodyOpciones_" + idTipo;

    console.log("Iniciando carga de opciones para:", nombreTipo, "(ID: " + idTipo + ")");

    $("#nombreTipoVariante").text(nombreTipo);
    $("#idTipoVarianteActual").val(idTipo);
    $("#idTipoVarianteOpcion").val(idTipo);

    // 1. Limpieza total y creación de tabla con ID ÚNICO
    $(".tabla-opciones").html(
        '<table id="' + tablaId + '" class="table table-bordered table-striped display nowrap" style="width: 100%;">' +
            '<thead>' +
                '<tr>' +
                    '<th>Nombre</th>' +
                    '<th>Productos</th>' +
                    '<th>Estado</th>' +
                    '<th>Acciones</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody id="' + bodyId + '">' +
                '<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>' +
            '</tbody>' +
        '</table>'
    );

    // Mostrar el box de opciones
    $("#boxOpciones").show();

    // 2. Cargar opciones con AJAX
    $.ajax({
        url: "ajax/variantes.ajax.php",
        method: "POST",
        data: {
            "idTipoVariante": idTipo,
            "csrf_token": $('meta[name="csrf-token"]').attr('content')
        },
        dataType: "json",
        success: function (respuesta) {

            console.log("Datos recibidos para " + idTipo + ":", respuesta);

            var html = "";

            if (respuesta && Array.isArray(respuesta) && respuesta.length > 0) {

                var puedeEditar = $("#puedeEditarVariante").val() == "1";
                var puedeEliminar = $("#puedeEliminarVariante").val() == "1";

                for (var i = 0; i < respuesta.length; i++) {

                    var estadoHTML = "";
                    if (puedeEditar) {
                        estadoHTML = (respuesta[i].estado == 1) 
                            ? '<button class="btn btn-success btn-xs btnActivarOpcion" idOpcion="' + respuesta[i].id + '" estadoOpcion="0">Activado</button>'
                            : '<button class="btn btn-danger btn-xs btnActivarOpcion" idOpcion="' + respuesta[i].id + '" estadoOpcion="1">Desactivado</button>';
                    } else {
                        estadoHTML = (respuesta[i].estado == 1) 
                            ? '<button class="btn btn-success btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para cambiar estado">Activado</button>'
                            : '<button class="btn btn-danger btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para cambiar estado">Desactivado</button>';
                    }

                    html += '<tr>' +
                            '<td>' + respuesta[i].nombre + '</td>' +
                            '<td><span class="badge bg-blue">' + (respuesta[i].productos_asociados || 0) + '</span></td>' +
                            '<td>' + estadoHTML + '</td>' +
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
                html = '<tr><td colspan="4" class="text-center">No hay opciones registradas</td></tr>';
            }

            // 3. Inyectar en el body específico y reinicializar tabla específica
            $("#" + bodyId).html(html);
            
            var localOptions = $.extend(true, {}, dtVariantesOptions);
            $("#" + tablaId).DataTable(localOptions);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $("#" + bodyId).html('<tr><td colspan="4" class="text-center text-danger">Error de conexión</td></tr>');
        }
    });

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

                    if (respuesta == "ok") {

                        swal({
                            type: "success",
                            title: "¡El tipo de variante ha sido eliminado correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function (result) {
                            if (result.value) {
                                window.location = "variantes";
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

});


/*=============================================
ELIMINAR OPCIÓN DE VARIANTE
=============================================*/

$(document).on("click", ".btnEliminarOpcion", function () {

    var idOpcion = $(this).attr("idOpcion");

    var nombreOpcion = $(this).attr("nombreOpcion");

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

                    if (respuesta == "ok") {
                        swal({
                            type: "success",
                            title: "¡La opción ha sido eliminada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function (result) {

                            if (result.value) {

                                // Recargar las opciones del tipo actual

                                var idTipo = $("#idTipoVarianteActual").val();

                                $(".btnVerOpciones[idTipo='" + idTipo + "']").click();

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


});