$(document).ready(function () {

    // 1. Inicializar - Calcular total al cargar
    if ($("#tablaProductosNC").length > 0) {
        recalcularTotalNC();
    }



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
        var totalFinal = 0;
        var totalBase = 0;
        var totalImpuesto = 0;

        $(".checkProducto:checked").each(function () {
            var fila = $(this).closest("tr");
            var cantidad = fila.find(".cantidadDevolver").val();
            var precioUnitarioConImpuesto = fila.find(".cantidadDevolver").data("precio"); // Precio es Intra-Impuesto
            var tasaImpuesto = fila.find(".cantidadDevolver").data("impuesto") || 0;

            // Subtotal es cantidad * precio (incluye impuesto)
            var subtotalConImpuesto = cantidad * precioUnitarioConImpuesto;

            // Calcular base y monto de impuesto (Back-out tax)
            // Formula: Base = Total / (1 + Tasa/100)
            var baseItem = subtotalConImpuesto / (1 + (tasaImpuesto / 100));
            var impuestoItem = subtotalConImpuesto - baseItem;

            totalFinal += subtotalConImpuesto;
            totalBase += baseItem;
            totalImpuesto += impuestoItem;
        });

        $("#nuevoTotalBase").val($.number(totalBase, 2));
        $("#nuevoTotalSubtotal").val($.number(totalBase, 2)); // Subtotal = Base
        $("#nuevoTotalImpuesto").val($.number(totalImpuesto, 2));
        $("#nuevoTotalNC").val($.number(totalFinal, 2));
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
                precio: $("input[name='precio_" + key + "']").val(),
                impuesto: fila.find(".cantidadDevolver").data("impuesto") || 0, // Tasa de impuesto
                cantidad: cantidad,
                total: cantidad * $("input[name='precio_" + key + "']").val()
            });
        });

        // Debug
        // console.log("Productos a enviar impl:", listaProductos);

        var idVenta = $("input[name='idVenta']").val();
        var numeroFactura = $("input[name='numeroFactura']").val();
        var motivo = $("#motivoNota").val();
        var idCliente = $("#seleccionarCliente").val();

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
                datos.append("idCliente", idCliente);
                datos.append("metodoPago", metodoPago);
                datos.append("observacion", observacion);
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
