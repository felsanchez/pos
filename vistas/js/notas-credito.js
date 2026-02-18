$(document).ready(function () {

    // 1. Inicializar - Calcular total al cargar
    if ($("#tablaProductosNC").length > 0) {
        recalcularTotalNC();
    }

    // 1.1 Evento: Cambio en Motivo (Mostrar/Ocultar input "Otro")
    $("#motivoNota").change(function () {
        var motivo = $(this).val();
        if (motivo == "6") { // 6 = Otros
            $("#divOtroMotivo").show();
            $("#motivoDescripcion").prop("required", true);
        } else {
            $("#divOtroMotivo").hide();
            $("#motivoDescripcion").prop("required", false);
            $("#motivoDescripcion").val(""); // Limpiar
        }
    });

    // 1.2 Evento: Checkbox "Seleccionar Todo"
    $("#checkTodo").on("change", function () {
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
    $(".checkProducto").on("change", function () {
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
    $(".cantidadDevolver").on("change keyup", function () {
        var cantidad = $(this).val();
        var max = $(this).attr("max");
        var precio = $(this).data("precio");
        var fila = $(this).closest("tr");

        // Validar rango
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

        // Calcular subtotal fila
        var subtotal = cantidad * precio;
        fila.find(".subtotalFila").text("$" + $.number(subtotal, 2));

        recalcularTotalNC();
    });

    // 4. Función: Recalcular Total Global
    function recalcularTotalNC() {
        var total = 0;

        $(".checkProducto:checked").each(function () {
            var fila = $(this).closest("tr");
            var cantidad = fila.find(".cantidadDevolver").val();
            var precio = fila.find(".cantidadDevolver").data("precio");

            total += (cantidad * precio);
        });

        $("#nuevoTotalNC").val($.number(total, 2));
    }

    // 5. Envío del Formulario (AJAX)
    $(".formularioNotaCredito").on("submit", function (e) {
        e.preventDefault();

        // Validar que haya al menos un producto seleccionado
        if ($(".checkProducto:checked").length == 0) {
            swal({
                type: "error",
                title: "Error",
                text: "Debes seleccionar al menos un producto para devolver."
            });
            return;
        }

        // Preparar lista de productos
        var listaProductos = [];
        $(".checkProducto:checked").each(function () {
            var key = $(this).val();
            var fila = $(this).closest("tr");
            var cantidad = fila.find(".cantidadDevolver").val();

            // Recopilar datos necesarios para el controlador
            listaProductos.push({
                id: $("input[name='idProducto_" + key + "']").val(),
                codigo: $("input[name='codigo_" + key + "']").val(),
                descripcion: $("input[name='descripcion_" + key + "']").val(),
                precio: $("input[name='precio_" + key + "']").val(), // Precio unitario
                cantidad: cantidad,
                total: cantidad * $("input[name='precio_" + key + "']").val() // Total recalculado
            });
        });

        // Debug
        // console.log("Productos a enviar impl:", listaProductos);

        var idVenta = $("input[name='idVenta']").val();
        var motivo = $("#motivoNota").val();
        var tipoNota = $("#tipoNota").val();
        var idCliente = $("#seleccionarCliente").val();
        var motivoDescripcion = $("#motivoDescripcion").val(); // Capture custom description

        // Validar si es "Otros" y no escribió nada
        if (motivo == "6" && motivoDescripcion.trim() == "") {
            swal({
                type: "error",
                title: "Error",
                text: "Por favor especifique la descripción del motivo."
            });
            return;
        }

        var motivoDescripcion = $("#motivoDescripcion").val(); // Capture custom description
        var metodoPago = $("#nuevoMetodoPago").val();
        var observacion = $("#observacion").val();

        if (metodoPago == "") {
            swal({
                type: "error",
                title: "Error",
                text: "Seleccione un método de pago."
            });
            return;
        }

        // Validar si es "Otros" y no escribió nada
        if (motivo == "6" && motivoDescripcion.trim() == "") {
            swal({
                type: "error",
                title: "Error",
                text: "Por favor especifique la descripción del motivo."
            });
            return;
        }

        swal({
            title: '¿Generar Nota Crédito?',
            text: "Se generará una nota por valor de $" + $("#nuevoTotalNC").val(),
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {

                // Mostrar loading
                swal({
                    title: 'Procesando...',
                    text: 'Generando Nota Crédito en DIAN/Factus',
                    onOpen: () => { swal.showLoading() }
                });

                var datos = new FormData();
                datos.append("accion", "generarNotaCredito"); // Updated to match AJAX check
                datos.append("idVenta", idVenta);
                datos.append("numeroFactura", numeroFactura);
                datos.append("motivo", motivo);
                datos.append("tipo", tipoNota);
                datos.append("idCliente", idCliente); // Send client ID
                datos.append("motivoDescripcion", motivoDescripcion);
                datos.append("metodoPago", metodoPago); // Send payment method
                datos.append("observacion", observacion); // Send observation
                datos.append("listaProductos", JSON.stringify(listaProductos));

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
                                title: "¡Nota Crédito Generada!",
                                text: "Nota #" + respuesta.numero_nc + " creada correctamente.",
                                showConfirmButton: true
                            }).then((result) => {
                                if (result.value) {
                                    window.location = "facturas-electronicas";
                                }
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "Error API Factus",
                                text: respuesta.mensaje
                            });
                        }
                    },
                    error: function (jqXHR, status, error) {
                        swal({
                            type: "error",
                            title: "Error del Sistema",
                            text: "Ocurrió un error en la solicitud AJAX: " + error
                        });
                    }
                });

            }
        });

    });

});
