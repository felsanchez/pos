$(document).ready(function () {
    /*=============================================
    INICIALIZAR SELECT2 PARA SUCURSAL
    =============================================*/
    $('.select2').select2();

    /*=============================================
    CARGAR TABLA DINAMICA DE PRODUCTOS
    =============================================*/
    var tablaProductosDS = $(".tablaProductosDS").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/datatable-ventas.ajax.php",
            "type": "POST",
            "data": function (d) {
                d.csrf_token = $('meta[name="csrf-token"]').attr('content');
            }
        },
        "columnDefs": [
            {
                "targets": 0, // #
            },
            {
                "targets": 1, // Imagen
                "render": function (data, type, row) {
                    return '<img class="img-thumbnail imgTablaDS" src="' + row[1] + '" width="40px">';
                }
            },
            {
                "targets": 2, // Código
            },
            {
                "targets": 3, // Descripción
            },
            {
                "targets": 4, // Stock
                "render": function (data, type, row) {
                    var stock = row[4];
                    var btnClass = "btn-success";
                    if (stock <= 10) {
                        btnClass = "btn-danger";
                    } else if (stock <= 15) {
                        btnClass = "btn-warning";
                    }
                    return '<button class="btn ' + btnClass + '">' + stock + '</button>';
                }
            },
            {
                "targets": 5, // Acciones
                "render": function (data, type, row) {
                    if (row[6] == "1") {
                        return '<div class="btn-group"><button class="btn btn-warning btnVariantesDS recuperarBoton" data-id-producto="' + row[5] + '"><i class="fa fa-list"></i> Variantes</button></div>';
                    } else {
                        return '<div class="btn-group"><button class="btn btn-primary agregarProductoDS recuperarBoton" idProducto="' + row[5] + '">Agregar</button></div>';
                    }
                }
            }
        ],
        "dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
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
    AGREGANDO PRODUCTOS AL DOCUMENTO SOPORTE
    =============================================*/
    $(".tablaProductosDS tbody").on("click", "button.agregarProductoDS", function () {
        var idProducto = $(this).attr("idProducto");
        $(this).removeClass("btn-primary agregarProductoDS").addClass("btn-default").prop("disabled", true);

        var datos = new FormData();
        datos.append("idProducto", idProducto);
        // csrf_token removido - manejado por csrf-helper.js

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

                var impuestoPorcentaje = respuesta["impuesto_porcentaje"] ? Number(respuesta["impuesto_porcentaje"]) : 0;
                var impuestoNombre = respuesta["impuesto_nombre"] ? respuesta["impuesto_nombre"] : "Exento";
                var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();

                $(".nuevoProducto").append(
                    '<div class="row" style="padding:5px 15px">' +
                    '<div class="col-xs-5" style="padding-right:0px">' +
                    '<div class="input-group">' +
                    '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProductoDS" idProducto="' + idProducto + '"><i class="fa fa-times"></i></button></span>' +
                    '<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProducto + '" value="' + descripcion + '" readonly required>' +
                    '</div>' +
                    '</div>' +
                    '<!--Impuesto del producto (col-xs-2)-->' +
                    '<div class="col-xs-2 ingresoImpuesto">' +
                    '<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' + nombreCorto + ' ' + impuestoPorcentaje + '%" porcentaje="' + impuestoPorcentaje + '" impuestoNombre="' + impuestoNombre + '" readonly required>' +
                    '</div>' +
                    '<div class="col-xs-2">' +
                    '<input type="number" class="form-control nuevaCantidadProductoDS" min="1" value="1" stock="' + stock + '" required>' +
                    '</div>' +
                    '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
                    '<div class="input-group">' +
                    '<input type="text" class="form-control nuevoPrecioProductoDS" precioReal="' + precio + '" value="' + precio + '" readonly required>' +
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
    EXPANDIR VARIANTES EN DOCUMENTO SOPORTE
    =============================================*/

    // Función para formatear la tabla de variantes en Documentos Soporte
    function formatearTablaVariantesDS(variantes) {

        if (!variantes || variantes.length === 0) {
            return '<div class="alert alert-info">No hay variantes para este producto</div>';
        }

        // Función auxiliar para formatear precios
        function formatearPrecio(numero) {
            return Math.round(numero).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        var html = '<table class="table table-condensed table-bordered table-striped" style="background-color: white; margin-bottom: 0;">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Variante</th>';
        html += '<th width="120px">Precio</th>';
        html += '<th width="80px">Stock</th>';
        html += '<th width="100px">Acción</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        for (var i = 0; i < variantes.length; i++) {
            var variante = variantes[i];

            if (variante.estado != 1) continue;

            // Verificar si esta variante ya está agregada en el documento
            var yaAgregado = false;
            $(".nuevaDescripcionProducto").each(function () {
                if ($(this).attr("idVariante") == variante.id) {
                    yaAgregado = true;
                }
            });

            var stockBadge = '';
            if (variante.stock <= 0) {
                stockBadge = '<span class="badge bg-red">' + variante.stock + '</span>';
            } else if (variante.stock <= 10) {
                stockBadge = '<span class="badge bg-yellow">' + variante.stock + '</span>';
            } else {
                stockBadge = '<span class="badge bg-green">' + variante.stock + '</span>';
            }

            var botonAgregar = '';

            if (variante.stock > 0) {
                if (yaAgregado) {
                    botonAgregar = '<button class="btn btn-default btn-xs agregarVarianteDS" ' +
                        'idVariante="' + variante.id + '" ' +
                        'idProductoBase="' + variante.id_producto + '" ' +
                        'nombreVariante="' + variante.nombre + '" ' +
                        'precioVariante="' + variante.precio_final + '" ' +
                        'stockVariante="' + variante.stock + '" ' +
                        'skuVariante="' + variante.sku + '" ' +
                        'impuestoPorcentaje="' + (variante.impuesto_porcentaje || 0) + '" ' +
                        'impuestoNombre="' + (variante.impuesto_nombre || 'Exento') + '" disabled>Agregar</button>';
                } else {
                    botonAgregar = '<button class="btn btn-primary btn-xs agregarVarianteDS" ' +
                        'idVariante="' + variante.id + '" ' +
                        'idProductoBase="' + variante.id_producto + '" ' +
                        'nombreVariante="' + variante.nombre + '" ' +
                        'precioVariante="' + variante.precio_final + '" ' +
                        'stockVariante="' + variante.stock + '" ' +
                        'skuVariante="' + variante.sku + '" ' +
                        'impuestoPorcentaje="' + (variante.impuesto_porcentaje || 0) + '" ' +
                        'impuestoNombre="' + (variante.impuesto_nombre || 'Exento') + '">Agregar</button>';
                }
            } else {
                botonAgregar = '<button class="btn btn-default btn-xs" disabled>Sin stock</button>';
            }

            html += '<tr>';
            html += '<td>' + variante.nombre + '</td>';
            html += '<td><strong>$' + formatearPrecio(variante.precio_final) + '</strong></td>';
            html += '<td class="text-center">' + stockBadge + '</td>';
            html += '<td class="text-center">' + botonAgregar + '</td>';
            html += '</tr>';
        }

        html += '</tbody>';
        html += '</table>';
        return html;
    }

    // Evento click en botón de expandir variantes
    $(document).on('click', '.btnVariantesDS', function (e) {

        e.stopPropagation();

        var boton = $(this);
        var tr = boton.closest('tr');

        // Si el botón está en una fila hija (responsive), obtenemos la fila padre (la anterior)
        if (tr.hasClass('child')) {
            tr = tr.prev();
        }

        var row = tablaProductosDS.row(tr);
        var idProducto = boton.attr('data-id-producto');
        var icono = boton.find('i');

        // Si la fila ya está expandida, colapsarla
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icono.removeClass('fa-minus').addClass('fa-list');
            boton.removeClass('btn-danger').addClass('btn-warning');
        } else {
            // Expandir la fila

            // Deshabilitar botón y mostrar loading
            boton.prop('disabled', true);
            icono.removeClass('fa-list').addClass('fa-spinner fa-spin');

            // Solicitar variantes por AJAX
            var datos = new FormData();
            datos.append("obtenerVariantesProducto", idProducto);

            $.ajax({
                url: "ajax/productos.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (variantes) {

                    // Formatear tabla de variantes
                    var tablaVariantes = formatearTablaVariantesDS(variantes);

                    // Mostrar fila expandida
                    row.child(tablaVariantes).show();
                    tr.addClass('shown');

                    // Cambiar icono del botón
                    icono.removeClass('fa-spinner fa-spin fa-list').addClass('fa-minus');
                    boton.removeClass('btn-warning').addClass('btn-danger');
                    boton.prop('disabled', false);
                },

                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("Error al cargar variantes:", textStatus, errorThrown);
                    swal({
                        type: "error",
                        title: "Error al cargar las variantes",
                        text: "Por favor, intenta nuevamente"
                    });
                    icono.removeClass('fa-spinner fa-spin').addClass('fa-list');
                    boton.prop('disabled', false);
                }
            });
        }

    });

    /*=============================================
    AGREGANDO VARIANTES AL DOCUMENTO SOPORTE
    =============================================*/
    $(document).on("click", ".agregarVarianteDS", function () {

        var idVariante = $(this).attr("idVariante");
        var idProductoBase = $(this).attr("idProductoBase");
        var nombreVariante = $(this).attr("nombreVariante");
        var precioVariante = $(this).attr("precioVariante");
        var stockVariante = $(this).attr("stockVariante");
        var skuVariante = $(this).attr("skuVariante");
        var impuestoPorcentaje = $(this).attr("impuestoPorcentaje") ? Number($(this).attr("impuestoPorcentaje")) : 0;
        var impuestoNombre = $(this).attr("impuestoNombre") ? $(this).attr("impuestoNombre") : "Exento";
        var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();

        // Cambiar apariencia del botón
        $(this).removeClass("btn-primary").addClass("btn-default").prop("disabled", true);

        // Agregar la variante al documento soporte
        $(".nuevoProducto").append(
            '<div class="row" style="padding:5px 15px">' +
            '<div class="col-xs-5" style="padding-right:0px">' +
            '<div class="input-group">' +
            '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProductoDS" idProducto="' + idProductoBase + '" idVariante="' + idVariante + '"><i class="fa fa-times"></i></button></span>' +
            '<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + idProductoBase + '" esVariante="1" idVariante="' + idVariante + '" skuVariante="' + skuVariante + '" value="' + nombreVariante + '" readonly required>' +
            '</div>' +
            '</div>' +
            '<!--Impuesto de la variante (col-xs-2)-->' +
            '<div class="col-xs-2 ingresoImpuesto">' +
            '<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' + nombreCorto + ' ' + impuestoPorcentaje + '%" porcentaje="' + impuestoPorcentaje + '" impuestoNombre="' + impuestoNombre + '" readonly required>' +
            '</div>' +
            '<div class="col-xs-2">' +
            '<input type="number" class="form-control nuevaCantidadProductoDS" min="1" value="1" stock="' + stockVariante + '" required>' +
            '</div>' +
            '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
            '<div class="input-group">' +
            '<input type="text" class="form-control nuevoPrecioProductoDS" precioReal="' + precioVariante + '" value="' + precioVariante + '" readonly required>' +
            '</div>' +
            '</div>' +
            '</div>'
        );

        // Cambiar apariencia del botón principal en la tabla
        $(".btnVariantesDS[data-id-producto='" + idProductoBase + "']").removeClass("btn-warning").addClass("btn-default");

        sumarTotalPreciosDS();
        aplicarDescuentoDS();
        listarProductosDS();
        $(".nuevoPrecioProductoDS").number(true, 0);
    });

    /*=============================================
    QUITAR PRODUCTO Y RECUPERAR BOTON
    =============================================*/
    $(".formularioDocumentoSoporte").on("click", "button.quitarProductoDS", function () {
        $(this).parent().parent().parent().parent().remove();
        var idProducto = $(this).attr("idProducto");
        var idVariante = $(this).attr("idVariante");

        if (idVariante) {
            // Habilitar nuevamente el botón de la variante específica si el detalle está expandido
            $("button.agregarVarianteDS[idVariante='" + idVariante + "']").removeClass('btn-default').addClass('btn-primary').prop("disabled", false);

            var hayMasVariantes = false;
            $(".nuevaDescripcionProducto").each(function () {
                var idProd = $(this).attr("idProducto");
                var esVariante = $(this).attr("esVariante");
                if (idProd == idProducto && esVariante == "1") {
                    hayMasVariantes = true;
                }
            });

            // Si no quedan más variantes de este producto en el documento, restaurar el botón principal de variantes
            if (!hayMasVariantes) {
                $(".btnVariantesDS[data-id-producto='" + idProducto + "']").removeClass("btn-default").addClass("btn-warning");
            }
        } else {
            $("button.recuperarBoton[idProducto='" + idProducto + "']").removeClass('btn-default').addClass('btn-primary agregarProductoDS').prop("disabled", false);
        }

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
    $(".formularioDocumentoSoporte").on("change", "input.nuevaCantidadProductoDS", function () {
        var row = $(this).closest(".row");
        var precioInput = row.find(".nuevoPrecioProductoDS");
        var precioReal = Number(precioInput.attr("precioReal")) || 0;
        var cantidad = Number($(this).val()) || 0;
        var precioFinal = cantidad * precioReal;

        precioInput.val(precioFinal);

        sumarTotalPreciosDS();
        aplicarDescuentoDS();
        listarProductosDS();
    });

    /*=============================================
    SUMAR TODOS LOS PRECIOS (SIN DESCUENTO)
    =============================================*/
    function sumarTotalPreciosDS() {
        var precioItem = $(".nuevoPrecioProductoDS");
        var sumaTotal = 0;

        for (var i = 0; i < precioItem.length; i++) {
            sumaTotal += Number($(precioItem[i]).val()) || 0;
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
            html += '<div class="table-responsive">';
            html += '<table class="table table-condensed">';
            html += '<thead><tr><th>Tipo</th><th>%</th><th>Base</th><th>Monto</th><th></th></tr></thead><tbody>';

            retencionesAplicadasDS.forEach(function (ret, index) {
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
            html += '</div>';

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
        var impuesto = $(".nuevoImpuestoProducto");

        for (var i = 0; i < descripcion.length; i++) {
            var item = {
                "id": $(descripcion[i]).attr("idProducto"),
                "descripcion": $(descripcion[i]).val(),
                "cantidad": $(cantidad[i]).val(),
                "precio": $(precio[i]).attr("precioReal") || 0,
                "total": $(precio[i]).val() || 0,
                "impuesto": $(impuesto[i]).attr("porcentaje") || 0
            };

            var esVariante = $(descripcion[i]).attr("esVariante") || "0";
            var idVariante = $(descripcion[i]).attr("idVariante");
            var skuVariante = $(descripcion[i]).attr("skuVariante");

            if (esVariante == "1" && idVariante && idVariante != "" && idVariante != "undefined") {
                item.esVariante = "1";
                item.idVariante = idVariante;
                item.skuVariante = skuVariante;
            }

            listaProductos.push(item);
        }
        $("#listaProductosDS").val(JSON.stringify(listaProductos));
    }

    // Función para quitar el loader
    function quitarLoaderDS() {
        if ($("#loader-table-ds").length > 0) {
            $("#loader-table-ds").fadeOut(400, function () {
                $(this).remove();
            });
        }
    }

    /*=============================================
    TABLA DOCUMENTOS SOPORTE (ADMINISTRACIÓN) - SERVER SIDE
    =============================================*/
    if ($("#tablaListadoDocumentoSoporte").length > 0) {
        if ($.fn.DataTable.isDataTable('#tablaListadoDocumentoSoporte')) {
            $('#tablaListadoDocumentoSoporte').DataTable().destroy();
        }

        $("#tablaListadoDocumentoSoporte").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/documentos-soporte.ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                    d.idBodega = $("#sucursal_ds").val();
                    d.fechaInicial = $("#fechaInicialDS").val();
                    d.fechaFinal = $("#fechaFinalDS").val();
                }
            },
            "autoWidth": false,
            "order": [[4, "desc"]], // Ordenar por Fecha por defecto
            "columnDefs": [
                { "targets": 0, "responsivePriority": 1, "className": "vertical-middle" }, // Código
                { "targets": 6, "responsivePriority": 2, "className": "text-left vertical-middle", "orderable": false }, // Acciones
                { "targets": 1, "responsivePriority": 3, "className": "vertical-middle" }, // Proveedor
                { "targets": 2, "responsivePriority": 4, "className": "vertical-middle" }, // Vendedor
                { "targets": 3, "responsivePriority": 5, "className": "vertical-middle" }, // Total
                { "targets": 4, "responsivePriority": 6, "className": "vertical-middle" }, // Fecha
                { "targets": 5, "responsivePriority": 7, "className": "text-center vertical-middle" } // Estado DIAN
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
            "drawCallback": function() {
                 $(".btn-group").addClass("vertical-middle");
            }
        });
    }

    /*=============================================
    VALIDAR PROVEEDOR (DEBE SER NIT) ANTES DE EMITIR
    =============================================*/
    $(".formularioDocumentoSoporte").submit(function (e) {
        var form = this;

        // Si el formulario ya ha sido confirmado, permitimos el envío
        if ($(form).data('confirmado')) {
            return true;
        }

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

        e.preventDefault();

        // Aseguramos que los productos estén listados en el campo oculto
        listarProductosDS();

        swal({
            title: '¿Está seguro de guardar este documento?',
            text: "Se guardará en el sistema y podrá enviarla a la DIAN después.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (result.value) {
                $(form).data('confirmado', true);

                swal({
                    title: 'Guardando Documento Soporte',
                    text: 'Por favor espere mientras se procesa la información...',
                    type: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        swal.showLoading()
                    }
                });

                // Enviar por AJAX
                var datos = new FormData(form);
                datos.append("accion", "crearDS");
                datos.append("ajax", true);

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
                                title: "¡Documento Soporte guardado correctamente!",
                                text: "El documento ha sido registrado exitosamente en el sistema.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function (result) {
                                window.location = "documentos-soporte";
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "¡Error!",
                                text: respuesta.mensaje,
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("Error AJAX:", jqXHR.responseText);
                        swal({
                            type: "error",
                            title: "Error de Sistema",
                            text: "No se pudo emitir el documento soporte vía AJAX. Revisa la consola."
                        });
                    }
                });
            }
        });
    });

    /*=============================================
    FIRMAR Y ENVIAR DOCUMENTO SOPORTE
    =============================================*/
    $(document).on("click", ".btnFirmarDS", function () {
        var idDS = $(this).attr("idDS");

        swal({
            title: '¿Está seguro de firmar y emitir este Documento Soporte?',
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
                    title: 'Firmando Documento Soporte',
                    text: 'Por favor espere mientras se procesa la información...',
                    type: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        swal.showLoading()
                    }
                });

                var datos = new FormData();
                datos.append("idDS", idDS);
                datos.append("accion", "firmarDS");
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
                        if (!respuesta.error) {
                            swal({
                                type: "success",
                                title: "¡Documento Soporte firmado y enviado correctamente!",
                                text: "El documento ha sido procesado por la DIAN exitosamente.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function (result) {
                                if (result.value) {
                                    window.location = "documentos-soporte";
                                }
                            });
                        } else {
                            swal({
                                type: 'error',
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
            title: '¿Está seguro de eliminar este Documento Soporte?',
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
                datos.append("idDS", idDS);
                datos.append("accion", "eliminarDS");
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
                        if (!respuesta.error) {
                            swal({
                                type: "success",
                                title: "¡Documento Soporte eliminado correctamente!",
                                text: "El documento ha sido borrado exitosamente del sistema.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function (result) {
                                if (result.value) {
                                    window.location = "documentos-soporte";
                                }
                            });
                        } else {
                            swal({
                                type: 'error',
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
    VER LISTA DE NOTAS DE AJUSTE POR DOCUMENTO SOPORTE (MODAL)
    =============================================*/
    $(".tablaDocumentosSoporte, .tarjetas").on("click", ".btnVerNotasAjusteDS", function () {
        var idDS = $(this).attr("idDS");
        var datos = new FormData();
        datos.append("accion", "obtenerNotasAjusteDS");
        datos.append("idDS", idDS);
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
                $("#tbodyNotasAjusteDS").empty();
                if (respuesta.length > 0) {
                    var filas = "";
                    respuesta.forEach(function (nota, index) {
                        var numero = index + 1;
                        var codigo = nota["numero_nota_ajuste"] ? nota["numero_nota_ajuste"] : "Borrador";
                        var montoFormateado = new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: 'COP'
                        }).format(nota["monto_total"]);
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
                        var botonVer = '<a href="index.php?ruta=ver-nota-ajuste-ds&idNota=' + nota["id"] + '" class="btn btn-info btn-sm" title="Ver Detalle"><i class="fa fa-eye"></i> Ver Nota</a>';
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
    ABRIR MODAL ENVIAR EMAIL DS
    =============================================*/
    $(document).on("click", ".btnEnviarEmailDS", function () {
        var idDS = $(this).attr("idDS");
        var nombreProveedor = $(this).attr("nombreProveedor");
        var emailProveedor = $(this).attr("emailProveedor");

        $("#idDSEmailDS").val(idDS);
        $("#nombreProveedorEmailDS").val(nombreProveedor);
        $("#emailDestinoDS").val(emailProveedor);

        $("#modalEnviarEmailDS").modal("show");
    });

    /*=============================================
    ENVIAR CORREO DS (CONFIRMADO)
    =============================================*/
    $(document).on("click", ".btnEnviarCorreoConfirmadoDS", function () {
        var idDS = $("#idDSEmailDS").val();
        var emailDestino = $("#emailDestinoDS").val();

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
        datos.append("idDS", idDS);
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
                            $("#modalEnviarEmailDS").modal("hide");
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

    /*=============================================
    FILTRO POR SUCURSAL
    =============================================*/
    $("#sucursal_ds").change(function () {
        if ($.fn.DataTable.isDataTable('#tablaListadoDocumentoSoporte')) {
            $('#tablaListadoDocumentoSoporte').DataTable().ajax.reload();
        }
    });

    /*=============================================
    RANGO DE FECHAS DOCUMENTO SOPORTE
    =============================================*/
    if ($('#daterange-btn-ds').length > 0 && typeof $.fn.daterangepicker !== 'undefined') {
        // Inicializar texto del span
        $('#daterange-btn-ds span').html('<i class="fa fa-calendar"></i> Mostrar todas');

        $('#daterange-btn-ds').daterangepicker(
            {
                ranges: {
                    'Mostrar todas': [moment('2000-01-01'), moment()],
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment(),
                endDate: moment()
            },
            function (start, end) {
                if (start.format('YYYY-MM-DD') === '2000-01-01') {
                    $('#daterange-btn-ds span').html('<i class="fa fa-calendar"></i> Mostrar todas');
                    $('#fechaInicialDS').val('');
                    $('#fechaFinalDS').val('');
                } else {
                    $('#daterange-btn-ds span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                    $('#fechaInicialDS').val(start.format('YYYY-MM-DD'));
                    $('#fechaFinalDS').val(end.format('YYYY-MM-DD'));
                }
                if ($.fn.DataTable.isDataTable('#tablaListadoDocumentoSoporte')) {
                    $('#tablaListadoDocumentoSoporte').DataTable().ajax.reload();
                }
            }
        );

        $('#daterange-btn-ds').on('cancel.daterangepicker', function () {
            $(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar todas');
            $('#fechaInicialDS').val('');
            $('#fechaFinalDS').val('');
            if ($.fn.DataTable.isDataTable('#tablaListadoDocumentoSoporte')) {
                $('#tablaListadoDocumentoSoporte').DataTable().ajax.reload();
            }
        });
    }

    /*=============================================
    LIMPIAR FILTROS DOCUMENTO SOPORTE
    =============================================*/
    $(document).on("click", "#btnLimpiarFiltrosDS", function() {
        if ($("#sucursal_ds").length > 0) {
            $("#sucursal_ds").val('').trigger("change.select2");
        }
        $("#fechaInicialDS").val("");
        $("#fechaFinalDS").val("");
        $('#daterange-btn-ds span').html('<span><i class="fa fa-calendar"></i> Mostrar todas</span>');
        if ($.fn.DataTable.isDataTable('#tablaListadoDocumentoSoporte')) {
            $('#tablaListadoDocumentoSoporte').DataTable().ajax.reload();
        }
    });

    /*=============================================
    AGREGAR PRODUCTO EN DISPOSITIVOS MÓVILES (DOCUMENTO SOPORTE)
    =============================================*/
    $(".btnAgregarProductoDS").click(function () {
        var datos = new FormData();
        datos.append("traerProductos", "ok");

        $.ajax({
            url: "ajax/productos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                var optionsHtml = '<option value="">Seleccione el producto</option>';
                if (respuesta && respuesta.length > 0) {
                    respuesta.forEach(function(item) {
                        var optionAttrs = 'idProducto="' + item.id + '"';
                        optionAttrs += ' esVariante="' + (item.es_variante || 0) + '"';
                        if (item.es_variante == 1) {
                            optionAttrs += ' idVariante="' + item.id_variante + '" skuVariante="' + item.sku + '"';
                        }
                        optionAttrs += ' stock="' + item.stock + '"';
                        optionAttrs += ' precio="' + item.precio_venta + '"';
                        optionAttrs += ' impuestoPorcentaje="' + (item.impuesto_porcentaje || 0) + '"';
                        optionAttrs += ' impuestoNombre="' + (item.impuesto_nombre || 'Exento') + '"';

                        var label = item.descripcion;
                        if (item.es_variante == 1) {
                            label = '&nbsp;&nbsp;&nbsp;&nbsp;└─ ' + item.descripcion;
                        }

                        var disabledAttr = (item.deshabilitar == 1) ? 'disabled' : '';

                        optionsHtml += '<option ' + optionAttrs + ' ' + disabledAttr + ' value="' + item.descripcion + '">' + label + '</option>';
                    });
                }

                $(".nuevoProducto").append(
                    '<div class="row" style="padding:5px 15px">' +
                    '<div class="col-xs-5" style="padding-right:0px">' +
                    '<input type="text" class="form-control buscarProductoMovil" placeholder="🔍 Buscar..." style="margin-bottom: 4px; padding: 4px 8px; height: 28px; font-size: 11px; border-radius: 4px; border: 1px solid #ccc; width: 100%;">' +
                    '<div class="input-group">' +
                    '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProductoDS" idProducto><i class="fa fa-times"></i></button></span>' +
                    '<select class="form-control nuevaDescripcionProducto" idProducto name="nuevaDescripcionProducto" required>' +
                    optionsHtml +
                    '</select>' +
                    '</div>' +
                    '</div>' +
                    '<!--Impuesto del producto (col-xs-2)-->' +
                    '<div class="col-xs-2 ingresoImpuesto">' +
                    '<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="" porcentaje="0" impuestoNombre="Exento" readonly required>' +
                    '</div>' +
                    '<div class="col-xs-2">' +
                    '<input type="number" class="form-control nuevaCantidadProductoDS" min="1" value="1" stock nuevoStock required>' +
                    '</div>' +
                    '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
                    '<div class="input-group">' +
                    '<input type="text" class="form-control nuevoPrecioProductoDS" precioReal="" value="" readonly required>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );

                $(".nuevoPrecioProductoDS").number(true, 0);
            }
        });
    });

    $(".formularioDocumentoSoporte").on("change", "select.nuevaDescripcionProducto", function () {
        var select = $(this);
        var optionSelected = select.find("option:selected");
        var row = select.closest(".row");

        if (!optionSelected.length || !optionSelected.attr("idProducto")) {
            select.removeAttr("idProducto");
            select.removeAttr("esVariante");
            select.removeAttr("idVariante");
            select.removeAttr("skuVariante");

            row.find(".quitarProductoDS").removeAttr("idProducto");
            row.find(".quitarProductoDS").removeAttr("idVariante");

            row.find(".nuevoPrecioProductoDS").val(0).attr("precioReal", 0);
            row.find(".nuevaCantidadProductoDS").val(1).attr("stock", 0).attr("nuevoStock", 0);
            row.find(".nuevoImpuestoProducto").val("Exento 0%").attr("porcentaje", 0).attr("impuestoNombre", "Exento");

            sumarTotalPreciosDS();
            aplicarDescuentoDS();
            listarProductosDS();
            return;
        }

        var idProducto = optionSelected.attr("idProducto");
        var esVariante = optionSelected.attr("esVariante") || "0";
        var idVariante = optionSelected.attr("idVariante") || "";
        var skuVariante = optionSelected.attr("skuVariante") || "";
        var stock = Number(optionSelected.attr("stock") || 0);
        var precio = Number(optionSelected.attr("precio") || 0);
        var impuestoPorcentaje = optionSelected.attr("impuestoPorcentaje") ? Number(optionSelected.attr("impuestoPorcentaje")) : 0;
        var impuestoNombre = optionSelected.attr("impuestoNombre") || "Exento";
        var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();

        select.attr("idProducto", idProducto);
        select.attr("esVariante", esVariante);
        select.attr("idVariante", idVariante);
        select.attr("skuVariante", skuVariante);

        row.find(".quitarProductoDS").attr("idProducto", idProducto);
        row.find(".quitarProductoDS").attr("idVariante", idVariante);

        var nuevoPrecioProducto = row.find(".nuevoPrecioProductoDS");
        var nuevaCantidadProducto = row.find(".nuevaCantidadProductoDS");
        var nuevoImpuestoProducto = row.find(".nuevoImpuestoProducto");

        // Actualizar Impuesto
        $(nuevoImpuestoProducto).val(nombreCorto + " " + impuestoPorcentaje + "%");
        $(nuevoImpuestoProducto).attr("porcentaje", impuestoPorcentaje);
        $(nuevoImpuestoProducto).attr("impuestoNombre", impuestoNombre);

        // Actualizar Stock
        $(nuevaCantidadProducto).attr("stock", stock);
        $(nuevaCantidadProducto).attr("nuevoStock", stock);
        $(nuevaCantidadProducto).val(1);

        // Actualizar Precio
        $(nuevoPrecioProducto).val(precio);
        $(nuevoPrecioProducto).attr("precioReal", precio);

        sumarTotalPreciosDS();
        aplicarDescuentoDS();
        listarProductosDS();
    });

    /*=============================================
    SELECCIONAR METODO DE PAGO DOCUMENTO SOPORTE
    =============================================*/
    $(document).on("change", "#nuevoMetodoPagoDS", function () {
        var metodo = $(this).val();

        if (metodo == "") {
            $(this).parent().parent().parent().children(".cajasMetodoPagoDS").html("");
            $("#listaMetodoPagoDS").val("");
        } else {
            $(this).parent().parent().parent().children(".cajasMetodoPagoDS").html(
                '<div class="col-xs-6" style="padding-left:0px">' +
                '<div class="input-group">' +
                '<input type="text" class="form-control" id="nuevoCodigoTransaccionDS" name="nuevoCodigoTransaccionDS" placeholder="Ingrese el valor o código de transacción">' +
                '<span class="input-group-addon"><i class="fa fa-lock"></i></span>' +
                '</div>' +
                '</div>'
            );
            listarMetodosDS();
        }
    });

    $(document).on("change keyup", "#nuevoCodigoTransaccionDS", function () {
        listarMetodosDS();
    });

    function listarMetodosDS() {
        var metodo = $("#nuevoMetodoPagoDS").val();
        var transaccion = $("#nuevoCodigoTransaccionDS").val();
        if (transaccion && transaccion.trim() !== "") {
            $("#listaMetodoPagoDS").val(metodo + "-" + transaccion);
        } else {
            $("#listaMetodoPagoDS").val(metodo);
        }
    }
});