$(document).ready(function () {

    // 1. Inicialización de Fechas (Rango Inicial Vacío para mostrar todo)
    $("#fechaInicialCaja").val("");
    $("#fechaFinalCaja").val("");
    $("#daterange-btn-cajas span").html('<i class="fa fa-calendar"></i> Rango de fecha');

    // 2. Inicialización de DataTable
    if ($(".tablaHistorialCajas").length > 0) {
        var tablaCajas = $(".tablaHistorialCajas").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/cajas.ajax.php",
                "method": "POST",
                "data": function (d) {
                    d.fechaInicio = $("#fechaInicialCaja").val() || "";
                    d.fechaFin = $("#fechaFinalCaja").val() || "";
                    d.bodegaId = $("#filtroBodegaCaja").val() || "";
                    d.usuarioId = $("#filtroUsuarioCaja").val() || "";
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                }
            },
            "columns": [
                { "data": 1 }, // Sucursal
                { "data": 2 }, // Cajero
                { "data": 3 }, // Apertura
                { "data": 4 }, // Cierre
                { "data": 5 }, // Monto Apertura
                { "data": 6 }, // Efectivo Esperado
                { "data": 7 }, // Efectivo Real
                { "data": 8 }, // Diferencia
                { "data": 9, "orderable": false } // Acciones
            ],
            "order": [[2, "desc"]],
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sSearch": "Buscar:",
                "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
            },
            "dom": '<"row" <"col-sm-6" l><"col-sm-6" f>>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
            "responsive": true,
            "autoWidth": false
        });

        // 3. Listeners de Filtros
        $(document).on("change", "#filtroBodegaCaja, #filtroUsuarioCaja, #fechaInicialCaja, #fechaFinalCaja", function () {
            tablaCajas.ajax.reload();
        });

        // 4. Botón Limpiar Filtros
        $("#btnLimpiarFiltrosCajas").on("click", function () {
            $("#filtroBodegaCaja, #filtroUsuarioCaja, #fechaInicialCaja, #fechaFinalCaja").val("").trigger("change");
            $("#daterange-btn-cajas span").html('<i class="fa fa-calendar"></i> Rango de fecha');
            tablaCajas.ajax.reload();
        });
    }

    // 5. Inicialización de Daterangepicker para Cajas
    if ($('#daterange-btn-cajas').length > 0) {
        $('#daterange-btn-cajas').daterangepicker({
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')]
            },
            startDate: moment(),
            endDate: moment()
        }, function (start, end) {
            $('#daterange-btn-cajas span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $("#fechaInicialCaja").val(start.format('YYYY-MM-DD'));
            $("#fechaFinalCaja").val(end.format('YYYY-MM-DD')).trigger('change');
        });
    }

    // 6. Ver Detalles del Cierre (Modal)
    $(document).on("click", ".btnVerDetalleCaja", function () {
        var idCierre = $(this).attr("idCaja");
        
        var datos = new FormData();
        datos.append("accion", "obtenerDetail"); // Para no colisionar en case
        datos.append("accion", "obtenerDetalle");
        datos.append("idCierre", idCierre);

        $.ajax({
            url: "ajax/cajas.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta) {
                    var c = respuesta.cierre;
                    var moneda = "$"; // Moneda local default, se puede obtener de la configuración

                    // Rellenar Información General
                    $("#detTurno").text(c.id);
                    $("#detSucursal").text(c.nombre_bodega);
                    $("#detCajero").text(c.nombre_usuario);
                    $("#detApertura").text(c.fecha_apertura);
                    $("#detCierre").text(c.fecha_cierre ? c.fecha_cierre : "Abierto");
                    $("#detEstado").html(c.estado == 'abierto' ? '<span class="label label-success">Abierto</span>' : '<span class="label label-danger">Cerrado</span>');

                    // Sumar movimientos manuales para desglose
                    var ingresosMan = 0;
                    var egresosMan = 0;
                    var gastosEfectivo = 0;

                    // Poblar tabla de movimientos de caja chica
                    $("#tablaDetMovimientos tbody").empty();
                    var tieneMovimientosManuales = false;
                    
                    if (respuesta.movimientos && respuesta.movimientos.length > 0) {
                        respuesta.movimientos.forEach(function (mov) {
                            var hora = mov.fecha.split(" ")[1];
                            var tipoLabel = mov.tipo === 'ingreso' ? '<span class="label label-success">Ingreso</span>' : '<span class="label label-danger">Egreso</span>';
                            var montoFormateado = moneda + " " + parseFloat(mov.monto).toLocaleString('es-ES', { minimumFractionDigits: 2 });
                            
                            if (mov.tipo === 'ingreso') {
                                if (mov.motivo.toLowerCase().indexOf("gasto") !== -1) {
                                    gastosEfectivo -= parseFloat(mov.monto);
                                } else {
                                    ingresosMan += parseFloat(mov.monto);
                                }
                            } else {
                                if (mov.motivo.toLowerCase().indexOf("gasto") !== -1) {
                                    gastosEfectivo += parseFloat(mov.monto);
                                } else {
                                    egresosMan += parseFloat(mov.monto);
                                }
                            }

                            // Omitir visualmente los gastos (incluye reversiones y ajustes) en la lista de movimientos
                            if (mov.motivo.toLowerCase().indexOf("gasto") !== -1) {
                                return;
                            }

                            tieneMovimientosManuales = true;

                            $("#tablaDetMovimientos tbody").append(
                                "<tr>" +
                                "<td>" + hora + "</td>" +
                                "<td>" + tipoLabel + "</td>" +
                                "<td class='text-right text-bold'>" + montoFormateado + "</td>" +
                                "<td>" + mov.motivo + "</td>" +
                                "</tr>"
                            );
                        });
                    }
                    
                    if (!tieneMovimientosManuales) {
                        $("#tablaDetMovimientos tbody").append("<tr><td colspan='4' class='text-center text-muted'>Sin movimientos manuales en este turno</td></tr>");
                    }

                    // Rellenar Auditoría de Efectivo
                    $("#detBase").text(moneda + " " + parseFloat(c.monto_apertura).toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                    $("#detVentasEfectivo").text(moneda + " " + parseFloat(c.ventas_efectivo).toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                    $("#detIngresosManuales").text(moneda + " " + ingresosMan.toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                    $("#detEgresosManuales").text(moneda + " " + egresosMan.toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                    $("#detGastosEfectivo").text(moneda + " " + gastosEfectivo.toLocaleString('es-ES', { minimumFractionDigits: 2 }));

                    var esperado = parseFloat(c.monto_cierre_teorico !== null ? c.monto_cierre_teorico : (parseFloat(c.monto_apertura) + parseFloat(c.ventas_efectivo) + ingresosMan - egresosMan - gastosEfectivo));
                    $("#detEsperado").text(moneda + " " + esperado.toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                    
                    var real = c.monto_cierre_real !== null ? parseFloat(c.monto_cierre_real) : esperado;
                    $("#detReal").text(moneda + " " + real.toLocaleString('es-ES', { minimumFractionDigits: 2 }));

                    var dif = c.diferencia !== null ? parseFloat(c.diferencia) : (real - esperado);
                    var difEl = $("#detDiferencia");
                    difEl.text(moneda + " " + dif.toLocaleString('es-ES', { minimumFractionDigits: 2 }));
                    difEl.removeClass("text-red text-green text-muted");
                    
                    if (dif === 0) {
                        difEl.addClass("text-muted");
                    } else if (dif > 0) {
                        difEl.addClass("text-green").text("+" + difEl.text() + " (Sobrante)");
                    } else {
                        difEl.addClass("text-red").text(difEl.text() + " (Faltante)");
                    }

                    // Rellenar Medios Electrónicos de forma dinámica
                    var totalMediosElectronicos = 0;
                    $("#tablaMediosElectronicos tbody").empty();

                    if (respuesta.desgloseVentas && respuesta.desgloseVentas.length > 0) {
                        respuesta.desgloseVentas.forEach(function (v) {
                            var metodo = v.metodo_pago;
                            var metodoLower = metodo.toLowerCase().trim();
                            var total = parseFloat(v.total);

                            // Omitir efectivo en esta tabla (va en la gaveta)
                            if (metodoLower.indexOf("efectivo") === -1) {
                                totalMediosElectronicos += total;
                                var totalFormateado = moneda + " " + total.toLocaleString('es-ES', { minimumFractionDigits: 2 });
                                $("#tablaMediosElectronicos tbody").append(
                                    "<tr>" +
                                    "<td class='text-bold'>" + metodo + "</td>" +
                                    "<td class='text-right text-bold text-green'>" + totalFormateado + "</td>" +
                                    "</tr>"
                                );
                            }
                        });
                    }

                    if (totalMediosElectronicos === 0) {
                        $("#tablaMediosElectronicos tbody").append("<tr><td colspan='2' class='text-center text-muted'>Sin ventas por medios electrónicos en este turno</td></tr>");
                    }

                    // Total Recaudado en Turno (Efectivo + Electrónicos)
                    var ventasEfectivo = parseFloat(c.ventas_efectivo) || 0;
                    var totalRecaudado = ventasEfectivo + totalMediosElectronicos;

                    $("#detTotalRecaudado").text(moneda + " " + totalRecaudado.toLocaleString('es-ES', { minimumFractionDigits: 2 }));

                    $("#detObservacionesApertura").text(c.observaciones_apertura ? c.observaciones_apertura : "Ninguna observación de apertura registrada.");
                    $("#detObservaciones").text(c.observaciones ? c.observaciones : "Ninguna observación registrada.");

                    // Mostrar Modal
                    $("#modalDetalleCaja").modal("show");
                }
            }
        });
    });
});
