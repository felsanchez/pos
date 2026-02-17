/*=============================================
GENERAR NOTA CRÉDITO
=============================================*/

// Abrir modal NC al hacer click en el botón
$(".tablas").on("click", ".btnGenerarNC", function () {
    var idVenta = $(this).attr("idVenta");
    var numeroFactura = $(this).attr("numeroFactura");

    // Guardar datos en el modal
    $("#ncIdVenta").val(idVenta);
    $("#ncNumeroFactura").text(numeroFactura);

    // Obtener datos del DOM de la fila (si están disponibles)
    var fila = $(this).closest("tr");
    var cliente = fila.find("td:eq(2)").text().trim(); // Columna Cliente
    var total = fila.find("td:eq(6)").text().trim(); // Columna Total

    // Mostrar datos si están disponibles
    if (cliente) {
        $("#ncCliente").text(cliente);
    } else {
        $("#ncCliente").text("Ver factura para detalles");
    }

    if (total) {
        $("#ncTotal").text(total.replace("$", "").replace(",", ""));
    } else {
        $("#ncTotal").text("0");
    }

    // Mostrar modal directamente
    $("#modalNotaCredito").modal("show");
});

// Confirmar generación de NC
$("#btnConfirmarNC").click(function () {
    var motivo = $("#ncMotivo").val().trim();

    if (motivo == "") {
        swal({
            type: "error",
            title: "Debe especificar el motivo de la Nota Crédito",
            showConfirmButton: true
        });
        return;
    }

    var datos = new FormData();
    datos.append("accion", "generarNotaCredito");
    datos.append("idVenta", $("#ncIdVenta").val());
    datos.append("motivo", motivo);
    datos.append("tipo", $("#ncTipo").val());

    $.ajax({
        url: "ajax/notas-credito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        beforeSend: function () {
            $("#btnConfirmarNC").prop("disabled", true)
                .html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
        },
        success: function (respuesta) {
            if (!respuesta.error) {
                swal({
                    type: "success",
                    title: "Nota Crédito generada exitosamente",
                    text: "Número: " + respuesta.numero_nc,
                    showConfirmButton: true
                }).then(function () {
                    window.location.reload();
                });
            } else {
                swal({
                    type: "error",
                    title: "Error al generar Nota Crédito",
                    text: respuesta.mensaje,
                    showConfirmButton: true
                });
                $("#btnConfirmarNC").prop("disabled", false)
                    .html('<i class="fa fa-check"></i> Generar Nota Crédito');
            }
        },
        error: function () {
            swal({
                type: "error",
                title: "Error de conexión",
                text: "No se pudo comunicar con el servidor",
                showConfirmButton: true
            });
            $("#btnConfirmarNC").prop("disabled", false)
                .html('<i class="fa fa-check"></i> Generar Nota Crédito');
        }
    });
});

// Limpiar modal al cerrar
$('#modalNotaCredito').on('hidden.bs.modal', function () {
    $("#ncMotivo").val("");
    $("#ncTipo").val("anulacion_total");
    $("#btnConfirmarNC").prop("disabled", false)
        .html('<i class="fa fa-check"></i> Generar Nota Crédito');
});
