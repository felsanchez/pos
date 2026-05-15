$(document).ready(function () {

    /*=============================================
    INICIALIZAR DATATABLES SERVER-SIDE - NOTAS CRÉDITO
    =============================================*/
    if ($("#tablaListadoNotasCredito").length > 0) {
        if ($.fn.DataTable.isDataTable('#tablaListadoNotasCredito')) {
            $('#tablaListadoNotasCredito').DataTable().destroy();
        }

        $("#tablaListadoNotasCredito").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/notas-credito.ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                    d.idBodega = $("#selectBodegaNC").val();
                    d.fechaInicial = $("#fechaInicialNC").val();
                    d.fechaFinal = $("#fechaFinalNC").val();
                }
            },
            "autoWidth": false,
            "order": [[4, "desc"]],
            "columnDefs": [
                { "targets": 0, "responsivePriority": 1, "className": "vertical-middle" }, // Código
                { "targets": 1, "responsivePriority": 2, "className": "vertical-middle" }, // Factura Original
                { "targets": 6, "responsivePriority": 3, "className": "text-center vertical-middle", "orderable": false }, // Acciones
                { "targets": 2, "responsivePriority": 4, "className": "vertical-middle" }, // Cliente
                { "targets": 3, "responsivePriority": 5, "className": "vertical-middle" }, // Total
                { "targets": 4, "responsivePriority": 6, "className": "vertical-middle" }, // Fecha
                { "targets": 5, "responsivePriority": 7, "className": "vertical-middle text-center" } // Estado DIAN
            ],
            "responsive": {
                "details": {
                    "type": "inline",
                    "renderer": function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.hidden ?
                                '<div style="padding:8px 12px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">' +
                                '<span style="font-weight:bold; color:#555;">' + col.title + ':</span> ' +
                                '<span style="color:#333;">' + col.data + '</span>' +
                                '</div>' :
                                '';
                        }).join('');

                        return data ?
                            $('<div style="background:#f9f9f9; border:1px solid #eee; margin:10px 0; border-radius:4px;"/>').append(data) :
                            false;
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
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            },
            "dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
            "drawCallback": function() {
                 $(".btn-group.col-acciones").parent().addClass("text-center").css("vertical-align", "middle");
            }
        });

        /*=============================================
        EVENTO AL CAMBIAR SUCURSAL
        =============================================*/
        $(document).on("change", "#selectBodegaNC", function() {
            $("#tablaListadoNotasCredito").DataTable().ajax.reload();
        });

        /*=============================================
        RANGO DE FECHAS
        =============================================*/
        if ($('#daterange-btn-nc').length > 0) {
            $('#daterange-btn-nc').daterangepicker(
                {
                    ranges: {
                        'Hoy': [moment(), moment()],
                        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                        'Este mes': [moment().startOf('month'), moment().endOf('month')],
                        'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                        'Todos los documentos': [moment('2000-01-01'), moment()]
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment()
                },
                function (start, end) {
                    $('#daterange-btn-nc span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    $('#fechaInicialNC').val(start.format('YYYY-MM-DD'));
                    $('#fechaFinalNC').val(end.format('YYYY-MM-DD'));
                    $("#tablaListadoNotasCredito").DataTable().ajax.reload();
                }
            );
        }

        /*=============================================
        LIMPIAR FILTROS
        =============================================*/
        $(document).on("click", "#btnLimpiarFiltrosNC", function() {
            $("#selectBodegaNC").val("").trigger("change");
            $("#fechaInicialNC").val("");
            $("#fechaFinalNC").val("");
            $('#daterange-btn-nc span').html('<span><i class="fa fa-calendar"></i> Rango de fecha</span>');
            $("#tablaListadoNotasCredito").DataTable().ajax.reload();
        });
    }

    // 1. Inicializar - Calcular total al cargar
    if ($("#tablaProductosNC").length > 0) {
        recalcularTotalNC();
    }

    // Intentar inicializar Select2 si la librería está disponible
    if (typeof $.fn.select2 !== 'undefined') {
        $(".select2").select2({
            placeholder: "Seleccione una opción",
            allowClear: true
        });
    }

    // 1.2 Evento: Checkbox "Seleccionar Todo"
    $(document).on("change", "#checkTodo", function () {
        var estado = $(this).is(":checked");
        $(".checkProducto").prop("checked", estado);

        if (estado) {
            $(".cantidadDevolver").prop("disabled", false);
            $(".checkProducto").closest("tr").removeClass("text-muted");
        } else {
            $(".cantidadDevolver").prop("disabled", true);
            $(".checkProducto").closest("tr").addClass("text-muted");
        }
        recalcularTotalNC();
    });

    // 2. Evento: Cambio en Checkbox (Seleccionar/Deseleccionar producto)
    $(document).on("change", ".checkProducto", function () {
        var fila = $(this).closest("tr");
        var inputCantidad = fila.find(".cantidadDevolver");

        if ($(this).is(":checked")) {
            inputCantidad.prop("disabled", false);
            fila.removeClass("text-muted");
        } else {
            inputCantidad.prop("disabled", true);
            fila.addClass("text-muted");
        }
        recalcularTotalNC();
    });

    // 3. Evento: Cambio en Cantidad
    $(document).on("change keyup", ".cantidadDevolver", function () {
        var cantidad = $(this).val();
        var max = $(this).attr("max");
        var precio = $(this).data("precio");
        var fila = $(this).closest("tr");

        if (Number(cantidad) <= 0) {
            cantidad = 1;
            $(this).val(1);
        }
        if (Number(cantidad) > Number(max)) {
            cantidad = max;
            $(this).val(max);
            swal({
                type: "warning",
                title: "Cantidad excedida",
                text: "No puedes devolver más de la cantidad original (" + max + ")",
                timer: 2000
            });
        }

        var subtotal = cantidad * precio;
        fila.find(".subtotalFila").text("$" + $.number(subtotal, 2));
        recalcularTotalNC();
    });

    // 4. Función: Recalcular Total Global
    function recalcularTotalNC() {
        var totalFinal = 0;
        var totalBase = 0;
        var totalImpuesto = 0;

        $(".checkProducto:checked").each(function () {
            var fila = $(this).closest("tr");
            var cantidad = fila.find(".cantidadDevolver").val();
            var precioUnitarioConImpuesto = fila.find(".cantidadDevolver").data("precio");
            var tasaImpuesto = fila.find(".cantidadDevolver").data("impuesto") || 0;

            var subtotalConImpuesto = cantidad * precioUnitarioConImpuesto;
            var baseItem = subtotalConImpuesto / (1 + (tasaImpuesto / 100));
            var impuestoItem = subtotalConImpuesto - baseItem;

            totalFinal += subtotalConImpuesto;
            totalBase += baseItem;
            totalImpuesto += impuestoItem;
        });

        $("#nuevoTotalBase").val($.number(totalBase, 2));
        $("#nuevoTotalSubtotal").val($.number(totalBase, 2));
        $("#nuevoTotalImpuesto").val($.number(totalImpuesto, 2));
        $("#nuevoTotalNC").val($.number(totalFinal, 2));
    }

    // 5. Envío del Formulario (AJAX)
    $(".formularioNotaCredito").on("submit", function (e) {
        e.preventDefault();

        if ($(".checkProducto:checked").length == 0) {
            swal({
                type: "error",
                title: "Error",
                text: "Debes seleccionar al menos un producto para devolver."
            });
            return;
        }

        var listaProductos = [];
        $(".checkProducto:checked").each(function () {
            var key = $(this).val();
            var fila = $(this).closest("tr");
            var cantidad = fila.find(".cantidadDevolver").val();

            listaProductos.push({
                id: $("input[name='idProducto_" + key + "']").val(),
                codigo: $("input[name='codigo_" + key + "']").val(),
                descripcion: $("input[name='descripcion_" + key + "']").val(),
                precio: $("input[name='precio_" + key + "']").val(),
                impuesto: fila.find(".cantidadDevolver").data("impuesto") || 0,
                cantidad: cantidad,
                total: cantidad * $("input[name='precio_" + key + "']").val()
            });
        });

        var idVenta = $("input[name='idVenta']").val();
        var numeroFactura = $("input[name='numeroFactura']").val();
        var motivo = $("#motivoNota").val();
        var idCliente = $("#seleccionarCliente").val();
        var metodoPago = $("#nuevoMetodoPago").val();
        var observacion = $("#observacion").val();

        if (metodoPago == "") {
            swal({ type: "error", title: "Error", text: "Seleccione un método de pago." });
            return;
        }

        swal({
            title: '¿Está seguro de guardar este documento?',
            text: "Se guardará en el sistema y podrá enviarla a la DIAN después.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (result.value) {
                swal({
                    title: 'Guardando Nota Crédito',
                    text: 'Por favor espere mientras se procesa la información...',
                    type: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        swal.showLoading()
                    }
                });

                var datos = new FormData();
                datos.append("accion", "generarNotaCredito");
                datos.append("idVenta", idVenta);
                datos.append("numeroFactura", numeroFactura);
                datos.append("motivo", motivo);
                datos.append("idCliente", idCliente);
                datos.append("metodoPago", metodoPago);
                datos.append("observacion", observacion);
                datos.append("listaProductos", JSON.stringify(listaProductos));
                datos.append("idUsuario", $("#idUsuarioSesion").val());
                // csrf_token removido - manejado por csrf-helper.js

                $.ajax({
                    url: "ajax/notas-credito.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        if (!respuesta.error) {
                            swal({
                                type: "success",
                                title: "¡Nota Crédito guardada correctamente!",
                                text: "El documento ha sido registrado exitosamente en el sistema.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                if (result.value) { window.location = "notas-credito"; }
                            });
                        } else {
                            swal({ type: 'error', title: "Error API Factus", text: respuesta.mensaje });
                        }
                    },
                    error: function (jqXHR, status, error) {
                        swal({ type: "error", title: "Error del Sistema", text: "Error AJAX: " + error });
                    }
                });
            }
        });
    });

    /*=============================================
    SELECCIONAR FACTURA ELECTRÓNICA PARA NOTA CRÉDITO
    =============================================*/
    // Usamos delegación de eventos y también select2:select por si acaso
    $(document).on("change", "#seleccionarFacturaReferencia", function () {
        var idVenta = $(this).val();
        if (idVenta) {
            window.location = "index.php?ruta=crear-nota-credito&idVenta=" + idVenta;
        }
    });

    // Evento específico de Select2
    $(document).on("select2:select", "#seleccionarFacturaReferencia", function (e) {
        var idVenta = e.params.data.id;
        if (idVenta) {
            window.location = "index.php?ruta=crear-nota-credito&idVenta=" + idVenta;
        }
    });

    /*=============================================
    FIRMAR NOTA CRÉDITO BORRADOR
    =============================================*/
    $(document).on("click", ".btnFirmarNotaCredito", function () {
        var idNota = $(this).attr("idNota");

        swal({
            title: '¿Está seguro de firmar y emitir esta Nota Crédito?',
            text: 'Este proceso enviará el documento a la DIAN y no se podrá revertir.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, firmar documento'
        }).then(function (result) {
            if (result.value) {
                swal({
                    title: 'Guardando Nota Crédito',
                    text: 'Por favor espere mientras se procesa la información...',
                    type: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        swal.showLoading()
                    }
                });
                var datos = new FormData();
                datos.append("idNota", idNota);
                datos.append("accion", "firmarNotaCredito");
                // csrf_token removido - manejado por csrf-helper.js

                $.ajax({
                    url: "ajax/notas-credito.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        if (!respuesta.error) {
                            swal({ 
                                type: "success", 
                                title: "¡Nota Crédito firmada y enviada correctamente!", 
                                text: "El documento ha sido procesado por la DIAN exitosamente.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                if (result.value) { window.location = "notas-credito"; }
                            })
                        } else {
                            swal({ type: 'error', title: "Error API Factus", text: respuesta.mensaje });
                        }
                    },
                    error: function () {
                        swal({ type: "error", title: "Error de Servidor" });
                    }
                })
            }
        })
    });

    /*=============================================
    ELIMINAR NOTA CRÉDITO BORRADOR
    =============================================*/
    $(document).on("click", ".btnEliminarNotaCredito", function () {
        var idNota = $(this).attr("idNota");
        swal({
        title: '¿Está seguro de eliminar esta Nota Crédito?',
        text: '¡Si no lo está puede cancelar la acción!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, eliminar documento'
    }).then(function (result) {

        if (result.value) {

            var datos = new FormData();
            datos.append("accion", "eliminarNotaCredito");
            datos.append("idNotaEliminar", idNota);
            // csrf_token removido - manejado por csrf-helper.js

            $.ajax({
                url: "ajax/factus.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                success: function (respuesta) {
                    if (respuesta == "ok") {
                        swal({
                            type: "success",
                            title: "¡Nota Crédito eliminada correctamente!",
                            text: "El documento ha sido borrado exitosamente del sistema.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    } else if (respuesta == "error_estado") {
                        swal({
                            type: "error",
                            title: "¡No se puede eliminar!",
                            text: "La nota ya fue enviada a la DIAN.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo eliminar la nota. " + respuesta,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                }
            })
        }
    })
    });

    /*=============================================
    VER LISTA DE NOTAS CRÉDITO POR FACTURA (MODAL)
    =============================================*/
    $(document).on("click", ".btnVerNotasCredito", function () {
        var idVenta = $(this).attr("idVenta");
        var datos = new FormData();
        datos.append("accion", "obtenerNotasCreditoVenta");
        datos.append("idVenta", idVenta);
        // csrf_token removido - manejado por csrf-helper.js

        $.ajax({
            url: "ajax/factus.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                $("#tbodyNotasCredito").empty();
                if (respuesta.length > 0) {
                    var filas = "";
                    respuesta.forEach(function (nota, index) {
                        var numero = index + 1;
                        var codigo = nota["numero_nota_credito"] ? nota["numero_nota_credito"] : "Borrador";
                        var montoFormateado = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(nota["monto_total"]);
                        var estadoBadge = "";
                        if (nota["estado_dian"] == "borrador") estadoBadge = '<button class="btn btn-warning btn-xs">Borrador</button>';
                        else if (nota["estado_dian"] == "aceptada" || nota["estado_dian"] == "enviada") estadoBadge = '<button class="btn btn-success btn-xs">Exitosa</button>';
                        else if (nota["estado_dian"] == "rechazada") estadoBadge = '<button class="btn btn-danger btn-xs">Rechazada</button>';
                        else estadoBadge = '<button class="btn btn-danger btn-xs">Pendiente</button>';

                        var botonVer = '<a href="index.php?ruta=ver-nota-credito&idNota=' + nota["id"] + '" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Ver Nota</a>';
                        filas += '<tr><td>' + numero + '</td><td>' + codigo + '</td><td>' + nota["fecha_creacion"] + '</td><td>' + montoFormateado + '</td><td>' + estadoBadge + '</td><td>' + botonVer + '</td></tr>';
                    });
                    $("#tbodyNotasCredito").append(filas);
                } else {
                    $("#tbodyNotasCredito").append('<tr><td colspan="6" class="text-center">No se encontraron notas crédito.</td></tr>');
                }
            }
        });
    });

    /*=============================================
    ABRIR MODAL ENVIAR EMAIL NC
    =============================================*/
    $(document).on("click", ".btnEnviarEmailNC", function () {
        var idNota = $(this).attr("idNota");
        var nombreCliente = $(this).attr("nombreCliente");
        var emailCliente = $(this).attr("emailCliente");

        $("#idNotaEmailNC").val(idNota);
        $("#nombreClienteEmailNC").val(nombreCliente);
        $("#emailDestinoNC").val(emailCliente);

        $("#modalEnviarEmailNC").modal("show");
    });

    /*=============================================
    ENVIAR CORREO NC (CONFIRMADO)
    =============================================*/
    $(document).on("click", ".btnEnviarCorreoConfirmadoNC", function () {
        var idNota = $("#idNotaEmailNC").val();
        var emailDestino = $("#emailDestinoNC").val();

        if (emailDestino == "") {
            swal({
                type: "error",
                title: "Error",
                text: "El correo electrónico es obligatorio"
            });
            return;
        }

        swal({
            title: 'Enviando correo...',
            text: 'Por favor espere mientras se genera el PDF y se envía el correo.',
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });

        var datos = new FormData();
        datos.append("idNota", idNota);
        datos.append("emailDestino", emailDestino);
        // csrf_token removido - manejado por csrf-helper.js

        $.ajax({
            url: "ajax/facturacion.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta.status == "success") {
                    swal({
                        type: "success",
                        title: "¡Enviado!",
                        text: respuesta.mensaje,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.value) {
                            $("#modalEnviarEmailNC").modal("hide");
                        }
                    });
                } else {
                    swal({
                        type: "error",
                        title: "Error",
                        text: respuesta.mensaje
                    });
                }
            },
            error: function () {
                swal({
                    type: "error",
                    title: "Error de comunicación",
                    text: "No se pudo conectar con el servidor para enviar el correo."
                });
            }
        });
    });
});
