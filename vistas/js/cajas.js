$(document).ready(function () {

    // 1. Verificación automática al cargar la vista
    if ($("#modalAperturaCaja").length > 0 && $('body').hasClass('inicio')) {
        $.ajax({
            url: "ajax/cajas.ajax.php",
            method: "POST",
            data: { accion: "verificarCaja" },
            dataType: "json",
            success: function (respuesta) {
                if (respuesta.controlCaja == 1 && !respuesta.cajaAbierta) {
                    $("#modalAperturaCaja").modal("show");
                }
            }
        });
    }

    // 2. Procesamiento de Apertura de Caja
    $("#formularioAperturaCaja").on("submit", function (e) {
        e.preventDefault();

        var montoApertura = $("#montoApertura").val();
        var observacionesApertura = $("#observacionesApertura").val();
        if (montoApertura === "" || parseFloat(montoApertura) < 0) {
            swal({
                title: "Valor inválido",
                text: "El monto de apertura no puede ser menor a cero.",
                icon: "error"
            });
            return;
        }

        var datos = new FormData();
        datos.append("accion", "abrirCaja");
        datos.append("montoApertura", montoApertura);
        datos.append("observacionesApertura", observacionesApertura);
        datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: "ajax/cajas.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta == "ok") {
                    swal({
                        title: "¡Éxito!",
                        text: "El turno de caja ha sido abierto correctamente.",
                        icon: "success",
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        location.reload();
                    });
                } else if (respuesta == "caja_ya_abierta") {
                    swal({
                        title: "Atención",
                        text: "Ya existe un turno de caja abierto para tu usuario en esta sucursal.",
                        icon: "warning"
                    });
                } else {
                    swal({
                        title: "Error",
                        text: "Ocurrió un error al abrir la caja. Por favor, reintente.",
                        icon: "error"
                    });
                }
            }
        });
    });

    // 3. Procesamiento de Movimientos de Caja Chica
    $("#formularioMovimientoCaja").on("submit", function (e) {
        e.preventDefault();

        var tipo = $("#tipoMovimiento").val();
        var monto = $("#montoMovimiento").val();
        var motivo = $("#motivoMovimiento").val();

        if (tipo === "" || monto === "" || parseFloat(monto) <= 0 || motivo.trim() === "") {
            swal({
                title: "Campos incorrectos",
                text: "Por favor, complete todos los campos con valores válidos.",
                icon: "warning"
            });
            return;
        }

        var datos = new FormData();
        datos.append("accion", "registrarMovimiento");
        datos.append("tipoMovimiento", tipo);
        datos.append("montoMovimiento", monto);
        datos.append("motivoMovimiento", motivo);
        datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: "ajax/cajas.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta == "ok") {
                    swal({
                        title: "Registrado",
                        text: "El movimiento ha sido asentado en el turno actual.",
                        icon: "success",
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Error",
                        text: "No se pudo registrar el movimiento. Verifique el estado del turno.",
                        icon: "error"
                    });
                }
            }
        });
    });

    // 4. Procesamiento de Cierre de Caja y Arqueo
    $("#formularioCerrarCaja").on("submit", function (e) {
        e.preventDefault();

        var montoCierreReal = $("#montoCierreReal").val();
        var observaciones = $("#observacionesCierre").val();

        if (montoCierreReal === "" || parseFloat(montoCierreReal) < 0) {
            swal({
                title: "Monto inválido",
                text: "Por favor, ingrese un monto de cierre válido.",
                icon: "error"
            });
            return;
        }

        swal({
            title: "¿Estás seguro de cerrar el turno?",
            text: "Esta acción dará por finalizada la jornada de caja actual y no se podrán registrar nuevas transacciones en este turno.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, cerrar turno",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.value) {
                var datos = new FormData();
                datos.append("accion", "cerrarCaja");
                datos.append("montoCierreReal", montoCierreReal);
                datos.append("observaciones", observaciones);
                datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

                $.ajax({
                    url: "ajax/cajas.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        if (respuesta == "ok") {
                            swal({
                                title: "¡Turno Cerrado!",
                                text: "La caja se ha cerrado y el arqueo ha sido procesado de forma exitosa.",
                                icon: "success",
                                confirmButtonText: "Entendido"
                            }).then(() => {
                                // Redirigir a inicio para reflejar el bloqueo
                                window.location = "inicio";
                            });
                        } else {
                            swal({
                                title: "Error",
                                text: "Ocurrió un error al intentar cerrar la caja.",
                                icon: "error"
                            });
                        }
                    }
                });
            }
        });
    });
});
