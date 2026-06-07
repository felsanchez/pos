/*=============================================
TABLA SEGUIMIENTO LEADS - SERVER-SIDE
=============================================*/
$(document).ready(function () {

    // Verificar si ya existe una instancia para no re-inicializar
    if ($.fn.DataTable.isDataTable('.tablaSeguimiento')) {
        $('.tablaSeguimiento').DataTable().destroy();
    }

    var tablaSeguimiento = $('table.tablaSeguimiento').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/seguimiento.ajax.php",
            "type": "POST",
            "data": function (d) {
                d.drawSeguimientos = 1;
            }
        },
        "order": [[1, "desc"]], // Fecha descendente
        "columnDefs": [
            { "targets": 0, "orderable": false, "searchable": false, "className": "text-center", "responsivePriority": 1 },
            { "targets": 1, "responsivePriority": 2 },
            { "targets": 2, "responsivePriority": 3 },
            { "targets": 3, "responsivePriority": 4 },
            { "targets": 4, "responsivePriority": 5 },
            { "targets": 5, "responsivePriority": 6 },
            { "targets": 6, "orderable": false, "responsivePriority": 7 },
            { "targets": 7, "orderable": false, "responsivePriority": 8 },
            { "targets": 8, "orderable": false, "responsivePriority": 9 },
            { "targets": 9, "orderable": false, "responsivePriority": 10 }
        ],
        "language": {
            "sProcessing":   "Procesando...",
            "sLengthMenu":   "Mostrar _MENU_ registros",
            "sZeroRecords":  "No se encontraron resultados",
            "sEmptyTable":   "Ningún dato disponible en esta tabla",
            "sInfo":         "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty":    "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch":       "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
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
    $(document).on("change", "#checkAll", function () {
        var checked = $(this).prop("checked");
        $(".tablaSeguimiento tbody .checkItem").prop("checked", checked);
        actualizarBotonEliminar();
    });

    /*=============================================
    SELECCIONAR ITEM INDIVIDUAL
    =============================================*/
    $(document).on("change", ".tablaSeguimiento tbody .checkItem", function () {
        actualizarBotonEliminar();

        var totalChecks   = $(".tablaSeguimiento tbody .checkItem").length;
        var totalChecked  = $(".tablaSeguimiento tbody .checkItem:checked").length;
        $("#checkAll").prop("checked", totalChecks === totalChecked && totalChecks > 0);
    });

    /*=============================================
    ACTUALIZAR ESTADO BOTÓN ELIMINAR
    =============================================*/
    function actualizarBotonEliminar() {
        var seleccionados = $(".tablaSeguimiento tbody .checkItem:checked").length;
        $("#btnEliminarSeleccionados").prop("disabled", seleccionados === 0);
    }

    /*=============================================
    ELIMINAR SELECCIONADOS
    =============================================*/
    $("#btnEliminarSeleccionados").on("click", function () {
        var ids = [];
        $(".tablaSeguimiento tbody .checkItem:checked").each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        swal({
            title: '¿Está seguro de borrar los registros seleccionados?',
            text: "¡Si no lo está puede cancelar la acción!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, borrar'
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: "ajax/seguimiento.ajax.php",
                    method: "POST",
                    data: { idsEliminar: JSON.stringify(ids) },
                    dataType: "json",
                    success: function (respuesta) {
                        if (respuesta === "ok" || respuesta === true) {
                            swal({
                                type: "success",
                                title: "Los registros han sido borrados correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function (result) {
                                tablaSeguimiento.ajax.reload(null, false);
                                $("#checkAll").prop("checked", false);
                                actualizarBotonEliminar();
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "Error al borrar registros",
                                text: JSON.stringify(respuesta),
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
                });
            }
        });
    });

});
