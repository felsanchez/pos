$(document).ready(function () {
    // Función para quitar el loader
    function quitarLoaderNA() {
        if ($("#loader-table-na").length > 0) {
            $("#loader-table-na").fadeOut(400, function () {
                $(this).remove();
            });
        }
    }

    // 1. Escuchar el evento de inicialización de DataTables (delegado para mayor fiabilidad)
    $(document).on('init.dt', '.tablas', function () {
        console.log("DataTables inicializado (evento delegado) para Nota Ajuste DS");
        quitarLoaderNA();
    });

    // 2. Respaldo: Si la tabla ya tiene la clase 'datatable-ready', quitar loader
    if ($('.tablas').hasClass('datatable-ready')) {
        quitarLoaderNA();
    }

    // 3. Respaldo adicional: Si por alguna razón pasan 4 segundos y sigue el spinner, quitarlo
    setTimeout(quitarLoaderNA, 4000);
    /*=============================================
    CALCULAR TOTALES DE LA NOTA DE AJUSTE
    =============================================*/
    function calcularTotalesAdjDS() {
        var total = 0;

        $(".checkProductoDS:checked").each(function () {
            var key = $(this).val();
            var inputCantidad = $("input[name='cantidad_" + key + "']");

            var cantidad = parseFloat(inputCantidad.val()) || 0;
            var precio = parseFloat(inputCantidad.data("precio")) || 0;
            var subtotal = cantidad * precio;

            total += subtotal;

            // Actualizar subtotal en la fila
            $(this).closest("tr").find(".subtotalFilaAdjDS").text("$" + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 }));
        });

        $("#nuevoTotalAdjDS").val(total.toFixed(2));
    }

    /*=============================================
    INICIALIZAR
    =============================================*/
    calcularTotalesAdjDS();

    /*=============================================
    CAMBIO EN CANTIDADES O PRODUCTOS
    =============================================*/
    $(document).on("change", ".cantidadAjusteDS, .checkProductoDS", function () {
        calcularTotalesAdjDS();
    });

    $(document).on("change", "#checkTodoDS", function () {
        $(".checkProductoDS").prop("checked", $(this).prop("checked"));
        calcularTotalesAdjDS();
    });

    /*=============================================
    ENVIAR FORMULARIO DE NOTA DE AJUSTE
    =============================================*/
    $(".formularioNotaAjusteDS").submit(function (e) {
        e.preventDefault();

        var idDS = $("#idDS").val();
        var tipoNota = $("#motivoNotaDS").val();
        var motivoDesc = $("#motivoDescDS").val();
        var totalDS = $("#nuevoTotalAdjDS").val();
        var idUsuario = $("#idUsuarioSesionDS").val();

        // Preparar lista de productos
        var listaProductos = [];
        var seleccionados = 0;

        $(".checkProductoDS:checked").each(function () {
            var key = $(this).val();
            var id = $("input[name='idProducto_" + key + "']").val();
            var descripcion = $("input[name='descripcion_" + key + "']").val();
            var cantidad = $("input[name='cantidad_" + key + "']").val();
            var precio = $("input[name='precio_" + key + "']").val();

            listaProductos.push({
                "id": id,
                "descripcion": descripcion,
                "cantidad": cantidad,
                "precio": precio,
                "total": (parseFloat(cantidad) * parseFloat(precio)).toFixed(2)
            });
            seleccionados++;
        });

        if (seleccionados == 0) {
            swal({
                type: "error",
                title: "¡Error!",
                text: "Debe seleccionar al menos un producto para ajustar",
                showConfirmButton: true
            });
            return;
        }

        swal({
            title: "¿Está seguro de guardar este borrador?",
            text: "La Nota de Ajuste se guardará localmente and podrá enviarla a la DIAN después.",
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sí, guardar borrador"
        }).then(function (result) {
            if (result.value) {

                // Mostrar cargando
                swal({
                    title: "Guardando...",
                    text: "Por favor espere mientras procesamos el documento",
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        swal.showLoading();
                    }
                });

                var datos = new FormData();
                datos.append("accion", "crearNotaAjusteDS");
                datos.append("idDS", idDS);
                datos.append("tipoNota", tipoNota);
                datos.append("motivoDesc", motivoDesc);
                datos.append("totalDS", totalDS);
                datos.append("idUsuario", idUsuario);
                datos.append("metodoPagoDS", $("#metodoPagoDS").val());
                datos.append("listaProductosDS", JSON.stringify(listaProductos));

                $.ajax({
                    url: "ajax/factus.ajax.php",
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
                                title: "¡Éxito!",
                                text: respuesta.mensaje,
                                showConfirmButton: true
                            }).then(function (result) {
                                window.location = "notas-ajuste-ds";
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "¡Error!",
                                text: respuesta.mensaje,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function () {
                        swal({
                            type: "error",
                            title: "¡Error de Sistema!",
                            text: "No se pudo comunicar con el servidor AJAX",
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });

    /*=============================================
    FIRMAR NOTA DE AJUSTE DS
    =============================================*/
    $(document).on("click", ".btnFirmarNotaAjusteDS", function () {
        var idNota = $(this).attr("idNota");

        swal({
            title: "¿Está seguro de firmar esta Nota de Ajuste?",
            text: "¡Esta acción no se puede deshacer y el documento será oficial!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f39c12",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sí, firmar y enviar"
        }).then(function (result) {
            if (result.value) {
                swal({
                    title: "Enviando",
                    text: "Por favor espere mientras se firma el documento",
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        swal.showLoading();
                    }
                });

                var datos = new FormData();
                datos.append("accion", "firmarNotaAjusteDS");
                datos.append("idNota", idNota);

                $.ajax({
                    url: "ajax/factus.ajax.php",
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
                                title: "Exito",
                                text: "Nota de ajuste firmada correctamente",
                                showConfirmButton: true
                            }).then(function (result) {
                                window.location = "notas-ajuste-ds";
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "Error al firmar",
                                text: respuesta.mensaje,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        swal({
                            type: "error",
                            title: "¡Error de comunicación!",
                            text: "No se pudo conectar con el servidor",
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });

    /*=============================================
    ELIMINAR NOTA DE AJUSTE DS
    =============================================*/
    $(document).on("click", ".btnEliminarNotaAjusteDS", function () {
        var idNota = $(this).attr("idNota");

        swal({
            title: "¿Está seguro de eliminar el borrador de esta Nota de Ajuste?",
            text: "¡Si no lo está, puede cancelar la acción!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sí, borrar nota"
        }).then(function (result) {
            if (result.value) {
                var datos = new FormData();
                datos.append("accion", "eliminarNotaAjusteDS");
                datos.append("idNota", idNota);

                $.ajax({
                    url: "ajax/factus.ajax.php",
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
                                title: "El Documento ha sido eliminado correctamente",
                                showConfirmButton: true
                            }).then(function (result) {
                                window.location = "notas-ajuste-ds";
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "Error al eliminar",
                                text: respuesta.mensaje,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        swal({
                            type: "error",
                            title: "¡Error de comunicación!",
                            text: "No se pudo conectar con el servidor",
                            showConfirmButton: true
                        });
                    }
                });
            }
        });
    });

    /*=============================================
    SELECCIONAR DOCUMENTO SOPORTE PARA NOTA DE AJUSTE
    =============================================*/
    $("#seleccionarDSReferencia").change(function () {
        var idDS = $(this).val();
        if (idDS) {
            window.location = "index.php?ruta=crear-nota-ajuste-ds&idDS=" + idDS;
        }
    });

    /*=============================================
    VER LISTA DE NOTAS DE AJUSTE POR DOCUMENTO SOPORTE (MODAL)
    =============================================*/
    $(".tablaDocumentosSoporte, .tarjetas").on("click", ".btnVerNotasAjusteDS", function () {
        var idDS = $(this).attr("idDS");
        var datos = new FormData();
        datos.append("accion", "obtenerNotasAjusteDS");
        datos.append("idDS", idDS);

        $.ajax({
            url: "ajax/factus.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {

                $("#tbodyNotasAjusteDS").empty(); // Limpiar contenido previo

                if (respuesta.length > 0) {
                    var filas = "";

                    respuesta.forEach(function (nota, index) {

                        var numero = index + 1;
                        var codigo = nota["numero_nota_ajuste"] ? nota["numero_nota_ajuste"] : "Borrador";

                        // Formatear monto
                        var montoFormateado = new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: 'COP'
                        }).format(nota["monto_total"]);

                        // Formatear Estado DIAN
                        var estadoBadge = "";
                        if (nota["estado_dian"] == "borrador") {
                            estadoBadge = '<button class="btn btn-warning btn-xs">Borrador</button>';
                        } else if (nota["estado_dian"] == "aceptada" || nota["estado_dian"] == "enviada") {
                            estadoBadge = '<button class="btn btn-success btn-xs">Exitosa</button>';
                        } else if (nota["estado_dian"] == "rechazada") {
                            estadoBadge = '<button class="btn btn-danger btn-xs">Rechazada</button>';
                        } else {
                            estadoBadge = '<button class="btn btn-danger btn-xs">Pendiente</button>';
                        }

                        // Botón para ver detalle de esta nota en específico
                        var botonVer = '<a href="index.php?ruta=ver-nota-ajuste-ds&idNota=' + nota["id"] + '" class="btn btn-info btn-sm" title="Ver Detalle"><i class="fa fa-eye"></i> Ver Nota</a>';

                        // Construir fila
                        filas += '<tr>' +
                            '<td>' + numero + '</td>' +
                            '<td>' + codigo + '</td>' +
                            '<td>' + nota["fecha_creacion"] + '</td>' +
                            '<td>' + montoFormateado + '</td>' +
                            '<td>' + estadoBadge + '</td>' +
                            '<td>' + botonVer + '</td>' +
                            '</tr>';
                    });

                    $("#tbodyNotasAjusteDS").append(filas);
                } else {
                    $("#tbodyNotasAjusteDS").append('<tr><td colspan="6" class="text-center">No se encontraron notas de ajuste para este documento soporte.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error("Error al obtener las notas: ", error);
            }
        });
    });

    /*=============================================
    ABRIR MODAL ENVIAR EMAIL NA
    =============================================*/
    $(document).on("click", ".btnEnviarEmailNA", function () {
        var idNA = $(this).attr("idNA");
        var emailProveedor = $(this).attr("emailProveedor");
        var nombreProveedor = $(this).attr("nombreProveedor");

        $("#idNA_Email").val(idNA);
        $("#emailDestinoNA").val(emailProveedor);
        $("#nombreProveedorEmailNA").val(nombreProveedor);
        $("#modalEnviarEmailNA").modal("show");
    });

    /*=============================================
    ENVIAR CORREO NA (CONFIRMADO)
    =============================================*/
    $(document).on("click", ".btnEnviarCorreoConfirmadoNA", function () {
        var idNA = $("#idNA_Email").val();
        var emailDestino = $("#emailDestinoNA").val();

        if (emailDestino == "") {
            swal({
                title: "Error",
                text: "Por favor ingrese un correo de destino",
                type: "error",
                confirmButtonText: "¡Cerrar!"
            });
            return;
        }

        // Mostrar loading
        swal({
            title: "Enviando...",
            text: "Por favor espere mientras se envía el correo",
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });

        var datos = new FormData();
        datos.append("idNA", idNA);
        datos.append("emailDestino", emailDestino);

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
                        title: "¡Enviado!",
                        text: respuesta.mensaje,
                        type: "success",
                        confirmButtonText: "¡Cerrar!"
                    });
                    $("#modalEnviarEmailNA").modal("hide");
                } else {
                    swal({
                        title: "Error",
                        text: respuesta.mensaje,
                        type: "error",
                        confirmButtonText: "¡Cerrar!"
                    });
                }
            },
            error: function () {
                swal({
                    title: "Error de comunicación",
                    text: "No se pudo conectar con el servidor para enviar el correo.",
                    type: "error",
                    confirmButtonText: "¡Cerrar!"
                });
            }
        });
    });
});