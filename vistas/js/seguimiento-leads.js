/*=============================================
TABLA SEGUIMIENTO LEADS
=============================================*/
$(".tablaSeguimiento").DataTable({
    "responsive": {
        details: {
            renderer: function (api, rowIdx, columns) {
                var data = $.map(columns, function (col, i) {
                    return col;
                });

                // Helper para obtener valor por índice de columna (independiente de si está oculta o no)
                // Usamos api.cell(rowIdx, colIdx).render('display') para obtener el valor formateado
                function getVal(idx) {
                    return api.cell(rowIdx, idx).render('display');
                }

                // NOTA: Se ajustaron los índices +1 debido a la nueva columna Checkbox en índice 0
                // 0: Checkbox, 1: #, 2: Fecha, 3: Nombre, 4: Celular, 5: Contexto, 6: Estado, 7: Seg1, 8: Seg2, 9: Seg3, 10: Pedido

                // Definir secciones
                var html = '<div class="dtr-details-custom">';

                // 1. Información del cliente (Fecha(2), Nombre(3), Celular(4))
                html += '<div class="box box-solid box-primary" style="margin-bottom:10px; border:1px solid #ddd; box-shadow:none;">';
                html += '<div class="box-header with-border"><h5 class="box-title" style="font-weight:bold; font-size:14px;">Información del cliente</h5></div>';
                html += '<div class="box-body" style="padding:10px;">';
                html += '<p style="margin-bottom:5px;"><strong>Último seguimiento:</strong> ' + getVal(2) + '</p>';
                html += '<p style="margin-bottom:5px;"><strong>Nombre:</strong> ' + getVal(3) + '</p>';
                html += '<p style="margin-bottom:5px;"><strong>Celular:</strong> ' + getVal(4) + '</p>';
                html += '</div></div>';

                // 2. Conversación (Contexto(5), Estado(6))
                html += '<div class="box box-solid box-info" style="margin-bottom:10px; border:1px solid #ddd; box-shadow:none;">';
                html += '<div class="box-header with-border"><h5 class="box-title" style="font-weight:bold; font-size:14px;">Conversación</h5></div>';
                html += '<div class="box-body" style="padding:10px;">';
                html += '<p style="margin-bottom:5px;"><strong>Contexto:</strong> ' + getVal(5) + '</p>';
                html += '<p style="margin-bottom:5px;"><strong>Estado:</strong> ' + getVal(6) + '</p>';
                html += '</div></div>';

                // 3. Seguimientos (Seg1(7), Seg2(8), Seg3(9))
                html += '<div class="box box-solid box-warning" style="margin-bottom:10px; border:1px solid #ddd; box-shadow:none;">';
                html += '<div class="box-header with-border"><h5 class="box-title" style="font-weight:bold; font-size:14px;">Seguimientos</h5></div>';
                html += '<div class="box-body" style="padding:10px;">';
                html += '<p style="margin-bottom:5px;"><strong>Seguimiento 1:</strong> ' + getVal(7) + '</p>';
                html += '<p style="margin-bottom:5px;"><strong>Seguimiento 2:</strong> ' + getVal(8) + '</p>';
                html += '<p style="margin-bottom:5px;"><strong>Seguimiento 3:</strong> ' + getVal(9) + '</p>';
                html += '</div></div>';

                // 4. Pedido (Hizo pedido(10))
                html += '<div class="box box-solid box-success" style="margin-bottom:0px; border:1px solid #ddd; box-shadow:none;">';
                html += '<div class="box-header with-border"><h5 class="box-title" style="font-weight:bold; font-size:14px;">Pedido</h5></div>';
                html += '<div class="box-body" style="padding:10px;">';
                html += '<p style="margin-bottom:5px;"><strong>¿Completó pedido?:</strong> ' + getVal(10) + '</p>';
                html += '</div></div>';

                html += '</div>';

                return html;
            }
        }
    },
    "columnDefs": [
        {
            "targets": 0, // La columna del checkbox
            "orderable": false,
            "className": "text-center"
        },
        {
            "targets": 1, // La columna #
            "className": "dtr-control" // Aquí aparecerá el botón (+) en móvil
        }
    ],
    "order": [[2, 'desc']], // Ordenar por fecha (índice 2) descendente por defecto
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
