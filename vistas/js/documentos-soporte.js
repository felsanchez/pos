
/*=============================================
CARGAR TABLA DINAMICA DE PRODUCTOS
=============================================*/
var tablaProductosDS = $(".tablaProductosDS").DataTable({
    "ajax": "ajax/datatable-ventas.ajax.php",
    "columnDefs": [
        {
            "targets": -5,
            "data": null,
            "defaultContent": '<img class="img-thumbnail imgTablaDS" width="40px">'
        },
        {
            "targets": -2,
            "data": null,
            "render": function (data, type, row) {
                if (row[4] <= 10) {
                    return '<button class="btn btn-danger">' + row[4] + '</button>';
                } else if (row[4] <= 15) {
                    return '<button class="btn btn-warning">' + row[4] + '</button>';
                } else {
                    return '<button class="btn btn-success">' + row[4] + '</button>';
                }
            }
        },
        {
            "targets": -1,
            "data": null,
            "render": function (data, type, row) {
                return '<div class="btn-group"><button class="btn btn-primary agregarProductoDS recuperarBoton" idProducto="' + row[5] + '">Agregar</button></div>';
            }
        }
    ],
    "language": {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
        "sSearch": "Buscar",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        }
    }
});

/*=============================================
CARGAR IMAGENES CUANDO LA TABLA SE DIBUJA
=============================================*/
tablaProductosDS.on('draw.dt', function () {
    var imgTabla = $(".imgTablaDS");
    for (var i = 0; i < imgTabla.length; i++) {
        var data = tablaProductosDS.row($(imgTabla[i]).parents("tr")).data();
        $(imgTabla[i]).attr("src", data[1]);
    }
});

/*=============================================
AGREGANDO PRODUCTOS AL DOCUMENTO SOPORTE
=============================================*/
$(".tablaProductosDS tbody").on("click", "button.agregarProductoDS", function () {
    var idProducto = $(this).attr("idProducto");
    $(this).removeClass("btn-primary agregarProductoDS").addClass("btn-default").prop("disabled", true);

    var datos = new FormData();
    datos.append("idProducto", idProducto);

    $.ajax({
        url: "ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            var descripcion = respuesta["descripcion"];
            var stock = respuesta["stock"];
            var precio = respuesta["precio_venta"];

            $(".nuevoProducto").append(
                '<div class="row" style="padding:5px 15px">' +
                '<div class="col-xs-6" style="padding-right:0px">' +
                '<div class="input-group">' +
                '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProductoDS" idProducto="' + idProducto + '"><i class="fa fa-times"></i></button></span>' +
                '<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProducto + '" value="' + descripcion + '" readonly required>' +
                '</div>' +
                '</div>' +
                '<div class="col-xs-3">' +
                '<input type="number" class="form-control nuevaCantidadProductoDS" min="1" value="1" stock="' + stock + '" required>' +
                '</div>' +
                '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
                '<div class="input-group">' +
                '<input type="number" class="form-control nuevoPrecioProductoDS" precioReal="' + precio + '" value="' + precio + '" min="1" required>' +
                '</div>' +
                '</div>' +
                '</div>'
            );

            sumarTotalPreciosDS();
            aplicarDescuentoDS();
            listarProductosDS();
            $(".nuevoPrecioProductoDS").number(true, 0);
        }
    });
});

/*=============================================
QUITAR PRODUCTO Y RECUPERAR BOTON
=============================================*/
$(".formularioDocumentoSoporte").on("click", "button.quitarProductoDS", function () {
    $(this).parent().parent().parent().parent().remove();
    var idProducto = $(this).attr("idProducto");
    $("button.recuperarBoton[idProducto='" + idProducto + "']").removeClass('btn-default').addClass('btn-primary agregarProductoDS').prop("disabled", false);

    if ($(".nuevoProducto").children().length == 0) {
        $("#nuevoSubtotalSinDescDS").val(0);
        $("#nuevoSubtotalDS").val(0);
        $("#nuevoTotalDS").val(0);
        $("#totalDS").val(0);
        $("#totalDS").attr("totalOriginal", 0);
    } else {
        sumarTotalPreciosDS();
        aplicarDescuentoDS();
        listarProductosDS();
    }
});

/*=============================================
CANTIDAD O PRECIO CAMBIADO
=============================================*/
$(".formularioDocumentoSoporte").on("change", "input.nuevaCantidadProductoDS, input.nuevoPrecioProductoDS", function () {
    sumarTotalPreciosDS();
    aplicarDescuentoDS();
    listarProductosDS();
});

/*=============================================
SUMAR TODOS LOS PRECIOS (SIN DESCUENTO)
=============================================*/
function sumarTotalPreciosDS() {
    var precioItem = $(".nuevoPrecioProductoDS");
    var cantidadItem = $(".nuevaCantidadProductoDS");
    var sumaTotal = 0;

    for (var i = 0; i < precioItem.length; i++) {
        var subtotalItem = Number($(precioItem[i]).val()) * Number($(cantidadItem[i]).val());
        sumaTotal += subtotalItem;
    }

    $("#nuevoSubtotalSinDescDS").val(sumaTotal);
    $("#nuevoTotalDS").val(sumaTotal);
    $("#totalDS").val(sumaTotal);
    $("#nuevoTotalDS").attr("totalOriginal", sumaTotal);

    $("#nuevoSubtotalSinDescDS").number(true, 0);
    $("#nuevoTotalDS").number(true, 0);
}

/*=============================================
APLICAR DESCUENTO DS
=============================================*/
function aplicarDescuentoDS() {
    var tipoDescuento = $("#tipoDescuentoDS").val();
    var valorDescuento = Number($("#valorDescuentoDS").val());
    var totalOriginal = Number($("#nuevoTotalDS").attr("totalOriginal")) || 0;

    var montoDescuento = 0;
    var totalConDescuento = totalOriginal;

    if (tipoDescuento === "porcentaje") {
        montoDescuento = (totalOriginal * valorDescuento) / 100;
        totalConDescuento = totalOriginal - montoDescuento;
    } else if (tipoDescuento === "fijo") {
        montoDescuento = valorDescuento;
        totalConDescuento = totalOriginal - montoDescuento;
        if (totalConDescuento < 0) {
            totalConDescuento = 0;
            montoDescuento = totalOriginal;
        }
    }

    $("#montoDescuentoDS").val(montoDescuento);
    $("#nuevoSubtotalDS").val(totalConDescuento);
    $("#nuevoTotalDS").val(totalConDescuento);
    $("#totalDS").val(totalConDescuento);

    $("#nuevoSubtotalDS").number(true, 0);
    $("#nuevoTotalDS").number(true, 0);

    actualizarVisualizacionRetencionesDS();
}

/*=============================================
MANEJO DE RETENCIONES
=============================================*/
var retencionesAplicadasDS = [];

$("#guardarRetencionDS").click(function () {
    var tipo = $("#nuevoTipoRetencionDS").val();
    var porcentaje = $("#nuevoPorcentajeRetencionDS").val();

    if (!tipo || !porcentaje) return;

    var retencion = {
        tipo: tipo,
        porcentaje: porcentaje,
        base: 0,
        monto: 0
    };

    retencionesAplicadasDS.push(retencion);
    actualizarVisualizacionRetencionesDS();

    $("#nuevoTipoRetencionDS").val("");
    $("#nuevoPorcentajeRetencionDS").html('<option value="">Seleccionar porcentaje</option>');
});

function actualizarVisualizacionRetencionesDS() {
    var html = "";
    var totalRetenciones = 0;
    var subtotalConDesc = Number($("#nuevoSubtotalDS").val().replace(/,/g, "")) || 0;

    if (retencionesAplicadasDS.length > 0) {
        html += '<table class="table table-condensed">';
        html += '<thead><tr><th>Tipo</th><th>%</th><th>Base</th><th>Monto</th><th></th></tr></thead><tbody>';

        retencionesAplicadasDS.forEach(function (ret, index) {
            // Nota: El Doc. Soporte usualmente no tiene IVA discriminado como venta, 
            // pero si se requiere ReteIVA, se calcula sobre el "IVA teórico" o base.
            // Para DS simplificado, calculamos sobre el Subtotal (Neto).
            ret.base = subtotalConDesc;
            ret.monto = (ret.base * parseFloat(ret.porcentaje)) / 100;

            html += '<tr>';
            html += '<td>' + ret.tipo + '</td>';
            html += '<td>' + ret.porcentaje + '%</td>';
            html += '<td>$' + Number(ret.base).toFixed(0) + '</td>';
            html += '<td><strong>$' + Number(ret.monto).toFixed(0) + '</strong></td>';
            html += '<td><button type="button" class="btn btn-danger btn-xs eliminarRetencionDS" data-index="' + index + '"><i class="fa fa-trash"></i></button></td>';
            html += '</tr>';

            totalRetenciones += ret.monto;
        });

        var totalPagar = subtotalConDesc - totalRetenciones;
        html += '<tr class="info"><td><strong>Total Ret.</strong></td><td colspan="4"><strong>$' + Number(totalRetenciones).toFixed(0) + '</strong></td></tr>';
        html += '<tr class="success"><td><strong>Neto a Pagar</strong></td><td colspan="4"><strong>$' + Number(totalPagar).toFixed(0) + '</strong></td></tr>';
        html += '</tbody></table>';

        $("#listaRetencionesDS").html(html);
        $("#seccionRetencionesDS").show();
        $("#datosRetencionesDS").val(JSON.stringify(retencionesAplicadasDS));
    } else {
        $("#seccionRetencionesDS").hide();
        $("#datosRetencionesDS").val("");
    }
}

$(document).on("click", ".eliminarRetencionDS", function () {
    var index = $(this).data("index");
    retencionesAplicadasDS.splice(index, 1);
    actualizarVisualizacionRetencionesDS();
});

/*=============================================
LISTAR PRODUCTOS JSON
=============================================*/
function listarProductosDS() {
    var listaProductos = [];
    var descripcion = $(".nuevaDescripcionProducto");
    var cantidad = $(".nuevaCantidadProductoDS");
    var precio = $(".nuevoPrecioProductoDS");

    for (var i = 0; i < descripcion.length; i++) {
        listaProductos.push({
            "id": $(descripcion[i]).attr("idProducto"),
            "descripcion": $(descripcion[i]).val(),
            "cantidad": $(cantidad[i]).val(),
            "precio": $(precio[i]).val(),
            "total": Number($(precio[i]).val()) * Number($(cantidad[i]).val())
        });
    }
    $("#listaProductosDS").val(JSON.stringify(listaProductos));
}

/*=============================================
TABLA DOCUMENTOS SOPORTE (ADMINISTRACIÓN)
=============================================*/
if ($(".tablaDocumentosSoporte").length > 0) {
    $(".tablaDocumentosSoporte").DataTable({
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sSearch": "Buscar",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });
}
/*=============================================
VALIDAR PROVEEDOR (DEBE SER NIT) ANTES DE EMITIR
=============================================*/
$(".formularioDocumentoSoporte").submit(function (e) {
    var optionSelected = $("#seleccionarProveedor").find('option:selected');
    var nombreTipo = (optionSelected.attr("nombreTipo") || "").trim();

    if (nombreTipo != "NIT") {
        e.preventDefault();
        swal({
            type: "warning",
            title: "Validación de Proveedor",
            text: "El tipo de documento de identificación del proveedor debe ser NIT",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        });
        return false;
    }
});
/*=============================================
FIRMAR Y ENVIAR DOCUMENTO SOPORTE
=============================================*/
$(document).on("click", ".btnFirmarDS", function () {
    var idDS = $(this).attr("idDS");

    swal({
        title: "¿Está seguro de firmar y enviar este Documento Soporte?",
        text: "¡Esta acción enviará el documento a Factus y la DIAN!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3c8dbc",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, firmar y enviar"
    }).then(function (result) {
        if (result.value) {

            // Mostrar loading
            swal({
                title: "Enviando...",
                text: "Por favor espere mientras se firma el documento",
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    swal.showLoading()
                }
            });

            var datos = new FormData();
            datos.append("idDS", idDS);
            datos.append("accion", "firmarDS");

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
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function (result) {
                            if (result.value) {
                                window.location = "documentos-soporte";
                            }
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: respuesta.mensaje,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                }
            });
        }
    });
});
/*=============================================
ELIMINAR BORRADOR DE DOCUMENTO SOPORTE
=============================================*/
$(document).on("click", ".btnEliminarDS", function () {
    var idDS = $(this).attr("idDS");

    swal({
        title: "¿Está seguro de eliminar este borrador?",
        text: "¡Si no lo está, puede cancelar la acción!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3c8dbc",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar borrador"
    }).then(function (result) {
        if (result.value) {
            var datos = new FormData();
            datos.append("idDS", idDS);
            datos.append("accion", "eliminarDS");

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
                            text: "El borrador ha sido eliminado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function (result) {
                            if (result.value) {
                                window.location = "documentos-soporte";
                            }
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: respuesta.mensaje,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                }
            });
        }
    });
});
<<<<<<< HEAD
=======

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
