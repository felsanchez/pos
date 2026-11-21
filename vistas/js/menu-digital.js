/*=============================================
GESTIÓN DE MENÚ DIGITAL & CÓDIGOS QR (ADMIN)
=============================================*/
$(document).ready(function () {

    // Inicializar DataTable en la tabla del menú digital
    if ($('.tablaMenuDigital').length > 0) {
        $('.tablaMenuDigital').DataTable({
            "order": [[2, 'asc']], // Ordenar por nombre de producto
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar producto:",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            },
            "autoWidth": false
        });
    }

    // 1. CAMBIAR ESTADO (MOSTRAR EN MENÚ DIGITAL ON/OFF)
    $(document).on("change", ".toggleEstadoMenu", function () {
        var idProducto = $(this).attr("data-id");
        var estado = $(this).is(":checked") ? 1 : 0;
        var label = $(".label-estado-menu-" + idProducto);

        var datos = new FormData();
        datos.append("accion", "cambiar_estado");
        datos.append("id_producto", idProducto);
        datos.append("estado", estado);

        $.ajax({
            url: "ajax/menu-digital.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.status === "ok") {
                    if (estado === 1) {
                        label.text("Visible").css("color", "#00a65a");
                    } else {
                        label.text("Oculto").css("color", "#94a3b8");
                    }
                } else {
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudo actualizar el estado del producto.",
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                swal({
                    type: "error",
                    title: "Error de conexión",
                    text: "Ocurrió un error al intentar actualizar.",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // 2. EDICIÓN DIRECTA / INLINE DE CARACTERÍSTICAS (ESTILO NOTAS DE CLIENTES)
    $(document).on("input", ".celda-caracteristicas", function () {
        if ($(this).text().trim() === "") {
            $(this).attr("data-placeholder", "Escribe características o ingredientes...");
        } else {
            $(this).removeAttr("data-placeholder");
        }
    });

    $(document).on("blur", ".celda-caracteristicas", function () {
        var elemento = $(this);
        var id = elemento.attr("data-id");
        var nuevoTexto = elemento.text().trim();

        if (nuevoTexto === "") {
            elemento.attr("data-placeholder", "Escribe características o ingredientes...");
        } else {
            elemento.removeAttr("data-placeholder");
        }

        if (!id) {
            return;
        }

        var datos = new FormData();
        datos.append("accion", "guardar_caracteristicas");
        datos.append("id_producto", id);
        datos.append("caracteristicas", nuevoTexto);

        $.ajax({
            url: "ajax/menu-digital.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res && res.status === "ok") {
                    // Feedback visual exitoso (destello verde como en notas de clientes)
                    elemento.css("background-color", "#dff0d8");
                    setTimeout(function () {
                        elemento.css("background-color", "");
                    }, 500);
                } else {
                    elemento.css("background-color", "#f2dede");
                    setTimeout(function () {
                        elemento.css("background-color", "");
                    }, 1000);
                }
            },
            error: function () {
                elemento.css("background-color", "#f2dede");
                setTimeout(function () {
                    elemento.css("background-color", "");
                }, 1000);
            }
        });
    });

    // Inicializar placeholders de características vacías al cargar
    function inicializarPlaceholdersCaracteristicas() {
        $(".celda-caracteristicas").each(function () {
            if ($(this).text().trim() === "") {
                $(this).attr("data-placeholder", "Escribe características o ingredientes...");
            } else {
                $(this).removeAttr("data-placeholder");
            }
        });
    }

    inicializarPlaceholdersCaracteristicas();

    if ($('.tablaMenuDigital').length > 0) {
        $('.tablaMenuDigital').on('draw.dt', function () {
            inicializarPlaceholdersCaracteristicas();
        });
    }

    // 4. COPIAR ENLACE AL PORTAPAPELES
    $("#btnCopiarEnlaceMenu").on("click", function () {
        var input = document.getElementById("inputUrlMenuPublico");
        input.select();
        input.setSelectionRange(0, 99999); // Para móviles

        try {
            document.execCommand("copy");
            swal({
                type: "success",
                title: "¡Enlace copiado!",
                text: "El enlace público ha sido copiado a tu portapapeles.",
                timer: 2000,
                showConfirmButton: false
            });
        } catch (err) {
            swal({
                type: "info",
                title: "Copia el enlace manualmente",
                text: input.value,
                confirmButtonText: "Aceptar"
            });
        }
    });

    // 5. GENERAR QR ESPECÍFICO POR MESA (100% LOCAL Y OFFLINE)
    $("#btnGenerarQRMesa").on("click", function () {
        var base = $("#inputUrlMenuPublico").val().split("&mesa=")[0];
        var mesa = $("#inputNumeroMesa").val().trim();

        var finalUrl = base;
        if (mesa !== "") {
            finalUrl += "&mesa=" + encodeURIComponent(mesa);
            $("#subtituloQRMesa").html("Mesa / Puesto: <b style='color:#f39c12; font-size:14px;'>" + mesa + "</b>");
        } else {
            $("#subtituloQRMesa").html("Explora nuestro menú digital y pide desde tu celular");
        }

        // Obtener código QR en base64 desde nuestro generador local
        $.getJSON("ajax/generar-qr.php?formato=base64&data=" + encodeURIComponent(finalUrl), function (res) {
            if (res && res.base64) {
                $("#imagenCodigoQR").attr("src", res.base64);
            } else {
                $("#imagenCodigoQR").attr("src", "ajax/generar-qr.php?data=" + encodeURIComponent(finalUrl));
            }
        }).fail(function () {
            $("#imagenCodigoQR").attr("src", "ajax/generar-qr.php?data=" + encodeURIComponent(finalUrl));
        });

        swal({
            type: "success",
            title: "Código QR Actualizado",
            text: mesa ? ("El código QR ahora incluye automáticamente: " + mesa) : "Código QR general restablecido.",
            timer: 2000,
            showConfirmButton: false
        });
    });

    // 6. DESCARGAR QR
    $("#btnDescargarQR").on("click", function () {
        var qrSrc = $("#imagenCodigoQR").attr("src");
        var mesa = $("#inputNumeroMesa").val().trim();
        var nombreArchivo = mesa ? ("QR_Menu_" + mesa.replace(/\s+/g, '_') + ".png") : "QR_Menu_Digital.png";

        var a = document.createElement("a");
        a.href = qrSrc;
        a.download = nombreArchivo;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    // 7. IMPRIMIR TARJETA QR
    $("#btnImprimirQR").on("click", function () {
        var printContents = document.getElementById("seccionImprimibleQR").innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Imprimir Código QR</title>');
        printWindow.document.write('<style>body { font-family: Arial, sans-serif; text-align: center; padding: 40px; } button { display: none !important; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () {
            printWindow.print();
            printWindow.close();
        }, 500);
    });

});
