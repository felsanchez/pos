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
INICIALIZAR
=============================================*/
$(document).ready(function () {
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
    var idUsuario = $("input[name='idUsuario']").val();

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
<<<<<<< HEAD
        title: "¿Está seguro de generar esta Nota de Ajuste?",
        text: "¡Esta acción enviará la información a la DIAN!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f39c12",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, generar nota"
=======
        title: "¿Está seguro de guardar este borrador?",
        text: "La Nota de Ajuste se guardará localmente y podrá enviarla a la DIAN después.",
        type: "info",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, guardar borrador"
>>>>>>> 085e8812 (documentos soporte v8)
    }).then(function (result) {
        if (result.value) {

            // Mostrar cargando
            swal({
<<<<<<< HEAD
                title: "Enviando a la DIAN...",
=======
                title: "Guardando...",
>>>>>>> 085e8812 (documentos soporte v8)
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
<<<<<<< HEAD
                            window.location = "documentos-soporte";
=======
                            window.location = "notas-ajuste-ds";
>>>>>>> 085e8812 (documentos soporte v8)
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
<<<<<<< HEAD
=======

/*=============================================
FIRMAR NOTA DE AJUSTE DS
=============================================*/
$(document).on("click", ".btnFirmarNotaAjusteDS", function () {
    var idNota = $(this).attr("idNota");

    swal({
        title: "¿Está seguro de firmar y enviar esta Nota de Ajuste a la DIAN?",
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
                title: "Firmando y Enviando a la DIAN...",
                text: "Esto puede tardar unos segundos. Por favor espere.",
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
                            title: "¡Nota Firmada!",
                            text: respuesta.mensaje,
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
                            title: "¡Borrador Eliminado!",
                            text: respuesta.mensaje,
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
>>>>>>> 085e8812 (documentos soporte v8)
