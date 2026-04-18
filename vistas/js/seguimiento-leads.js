/*=============================================
TABLA SEGUIMIENTO LEADS
=============================================*/
$(".tablaSeguimiento").DataTable({
    "responsive": {
        details: {
            type: "column",
            target: 1, // Fuerza a que el clic de expansión ocurra estrictamente en la columna 1 (Fecha)
            renderer: function (api, rowIdx, columns) {
                var data = $.map(columns, function (col, i) {
                    return col;
                });

                // Helper para obtener valor por índice de columna (independiente de si está oculta o no)
                // Usamos api.cell(rowIdx, colIdx).render('display') para obtener el valor formateado
                function getVal(idx) {
                    return api.cell(rowIdx, idx).render('display');
                }

                // Índices actualizados (Columna # eliminada)
                // 0: Checkbox, 1: Fecha, 2: Nombre, 3: Celular, 4: Contexto, 5: Estado, 6: Seg1, 7: Seg2, 8: Seg3, 9: Pedido
                var finalHtml = '';

                // SECCION 1: Información del cliente
                finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Información del cliente</h5></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Último seguimiento: </span><span class="pull-right">' + getVal(1) + '</span></div>';

                finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Nombre: </span><span class="pull-right">' + getVal(2) + '</span></div>';

                finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Celular: </span><span class="pull-right">' + getVal(3) + '</span></div>';

                // SECCION 2: Conversación
                finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Conversación</h5></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold" style="display:block; margin-bottom:4px;">Contexto: </span><span>' + getVal(4) + '</span></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Estado: </span><span class="pull-right">' + getVal(5) + '</span></div>';

                // SECCION 3: Seguimientos
                finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Seguimientos</h5></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Seguimiento 1: </span><span class="pull-right">' + getVal(6) + '</span></div>';
                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Seguimiento 2: </span><span class="pull-right">' + getVal(7) + '</span></div>';
                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">Seguimiento 3: </span><span class="pull-right">' + getVal(8) + '</span></div>';

                // SECCION 4: Pedido
                finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
                finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Pedido</h5></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
                finalHtml += '<span class="text-bold">¿Completó pedido?: </span><span class="pull-right">' + getVal(9) + '</span></div>';

                return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0; text-align: left;">').append(finalHtml) : false;
            }
        }
    },
    "columnDefs": [
        {
            "targets": 0, // La columna del checkbox
            "orderable": false,
            "className": "text-center",
            "responsivePriority": 1
        },
        {
            "targets": 1, // La columna Fecha (que tendra el boton de expansion)
            "className": "dtr-control",
            "responsivePriority": 1
        },
        {
            "targets": 2, // Nombre
            "responsivePriority": 2
        },
        {
            "targets": [3, 4, 5, 6, 7, 8, 9], // El resto se colapsa
            "responsivePriority": 3
        }
    ],
    "order": [[1, 'desc']], // Ordenar por fecha (índice 1 tras eliminar '#') descendente por defecto
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
