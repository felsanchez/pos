/*=============================================
TABLA SEGUIMIENTO LEADS
=============================================*/
$("table.tablaSeguimiento").DataTable({
    "responsive": {
        details: {
            type: "inline",
            renderer: function (api, rowIdx, columns) {
                var finalHtml = '';
                var hasHidden = false;

                $.each(columns, function (i, col) {
                    if (!col.hidden) return;
                    hasHidden = true;

                    // Si es el checkbox (índice 0), idealmente no queremos imprimirlo en el listado debajo en crudo
                    if (col.columnIndex === 0) return;

                    var label = col.title || ('Columna ' + col.columnIndex);
                    
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
                    finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
                    finalHtml += '<span style="color:#333;">' + col.data + '</span>';
                    finalHtml += '</div>';
                });

                if (!hasHidden) return false;
                return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
            }
        }
    },
    "columnDefs": [
        { "targets": 0, "orderable": false, "className": "text-center", "responsivePriority": 1 },
        { "targets": 1, "responsivePriority": 2 },
        { "targets": 2, "responsivePriority": 3 },
        { "targets": 3, "responsivePriority": 4 },
        { "targets": 4, "responsivePriority": 5 },
        { "targets": 5, "responsivePriority": 6 },
        { "targets": 6, "responsivePriority": 7 },
        { "targets": 7, "responsivePriority": 8 },
        { "targets": 8, "responsivePriority": 9 },
        { "targets": 9, "responsivePriority": 10 }
    ],
    "order": [[1, 'desc']], // Ordenar por fecha (índice 1) descendente por defecto
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

/*=============================================
PREVENIR EXPANSIÓN AL CLICKEAR CHECKBOX
=============================================*/
$(".tablaSeguimiento tbody").on("click", ".checkItem", function (e) {
    e.stopPropagation();
});

/*=============================================
SELECCIONAR TODOS
=============================================*/
$("#checkAll").on("change", function () {
    var checked = $(this).prop("checked");
    $(".checkItem").prop("checked", checked);
    actualizarBotonEliminar();
});

/*=============================================
SELECCIONAR ITEM INDIVIDUAL
=============================================*/
$(".tablaSeguimiento").on("change", ".checkItem", function () {
    actualizarBotonEliminar();

    // Si todos están seleccionados, marcar checkAll
    var totalChecks = $(".checkItem").length;
    var totalChecked = $(".checkItem:checked").length;

    if (totalChecks == totalChecked) {
        $("#checkAll").prop("checked", true);
    } else {
        $("#checkAll").prop("checked", false);
    }
});

/*=============================================
ACTUALIZAR ESTADO BOTÓN ELIMINAR
=============================================*/
function actualizarBotonEliminar() {
    var seleccionados = $(".checkItem:checked").length;
    if (seleccionados > 0) {
        $("#btnEliminarSeleccionados").prop("disabled", false);
    } else {
        $("#btnEliminarSeleccionados").prop("disabled", true);
    }
}

/*=============================================
ELIMINAR SELECCIONADOS
=============================================*/
$("#btnEliminarSeleccionados").on("click", function () {
    var ids = [];
    $(".checkItem:checked").each(function () {
        ids.push($(this).val());
    });

    if (ids.length == 0) return;

    swal({
        title: '¿Está seguro de borrar los registros seleccionados?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar!'
    }).then((result) => {
        if (result.value) {
            var datos = new FormData();
            datos.append("idsEliminar", JSON.stringify(ids));
            datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: "ajax/seguimiento.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    // La respuesta suele traer comillas si es string, limpiaremos por si acaso
                    var res = respuesta.replace(/['"]+/g, '');

                    if (res == "ok") {
                        swal({
                            type: "success",
                            title: "Los registros han sido borrados correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function (result) {
                            if (result.value) {
                                window.location = "seguimiento-leads";
                            }
                        })
                    } else {
                        swal({
                            type: "error",
                            title: "Error al borrar registros",
                            text: respuesta,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    swal({
                        type: "error",
                        title: "Error de conexión",
                        text: textStatus + " " + errorThrown,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            })
        }
    })
})
