$(document).ready(function () {

    // Variables globales para mantener el estado de las fechas sin depender del texto del span
    var fechaInicial = moment().subtract(29, 'days').format('YYYY-MM-DD');
    var fechaFinal = moment().format('YYYY-MM-DD');

    // Inicializar Select2 en los nuevos filtros
    if ($.fn.select2) {
        $("#seleccionarClienteReporte").select2({ width: '100%' });
        $("#seleccionarProveedorReporte").select2({ width: '100%' });
        $("#seleccionarUsuarioReporte").select2({ width: '100%', placeholder: 'Todos los usuarios' });
    }

    /*=============================================
    VARIABLE LOCAL STORAGE PARA RANGOS
    =============================================*/
    if (localStorage.getItem("capturarRangoReportes") != null) {
        $("#daterange-btn-reportes span").html(localStorage.getItem("capturarRangoReportes"));

        // Intentar recuperar las fechas del texto guardado si es posible, 
        // pero es mejor inicializar con las fechas por defecto para evitar errores de parseo
        // ya que el span es localizado.
    } else {
        $("#daterange-btn-reportes span").html('<i class="fa fa-calendar"></i> Rango de fecha');
    }

    /*=============================================
    DATERANGE PICKER
    =============================================*/
    $('#daterange-btn-reportes').daterangepicker(
        {
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function (start, end) {
            $("#daterange-btn-reportes span").html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            fechaInicial = start.format('YYYY-MM-DD');
            fechaFinal = end.format('YYYY-MM-DD');
            var categoria = $("#seleccionarCategoriaReporte").val();

            var capturarRango = $("#daterange-btn-reportes span").html();
            localStorage.setItem("capturarRangoReportes", capturarRango);
        }
    );

    /*=============================================
    CAMBIO DE CATEGORÍA
    =============================================*/
    $("#seleccionarCategoriaReporte").change(function () {
        var categoria = $(this).val();

        // Mostrar select correspondiente al tercero
        if (categoria == "ds" || categoria == "na") {
            $("#seleccionarClienteReporte").hide();
            $("#seleccionarProveedorReporte").show();
        } else {
            $("#seleccionarProveedorReporte").hide();
            $("#seleccionarClienteReporte").show();
        }
    });

    /*=============================================
    BOTÓN FILTRAR
    =============================================*/
    $("#btnFiltrarReportes").click(function () {
        var categoria = $("#seleccionarCategoriaReporte").val();
        var tercero = "todos";
        var idUsuario = $("#seleccionarUsuarioReporte").val() || "todos";

        if (categoria == "ds" || categoria == "na") {
            tercero = $("#seleccionarProveedorReporte").val();
        } else {
            tercero = $("#seleccionarClienteReporte").val();
        }

        cargarDashboard(fechaInicial, fechaFinal, categoria, tercero, idUsuario);
    });

    $("#btnLimpiarFiltrosReportes").click(function () {
        // Restablecer fecha a los últimos 30 días
        fechaInicial = moment().subtract(29, 'days').format('YYYY-MM-DD');
        fechaFinal = moment().format('YYYY-MM-DD');
        $("#daterange-btn-reportes span").html('<i class="fa fa-calendar"></i> Rango de fecha');
        localStorage.removeItem("capturarRangoReportes");

        // Restablecer selects
        $("#seleccionarCategoriaReporte").val("todos").trigger("change");
        $("#seleccionarClienteReporte").val("todos").trigger("change");
        $("#seleccionarProveedorReporte").val("todos").trigger("change");
        if ($.fn.select2) {
            $("#seleccionarUsuarioReporte").val(null).trigger("change");
        } else {
            $("#seleccionarUsuarioReporte").val("todos");
        }

        // Recargar datos sin filtros
        cargarDashboard(fechaInicial, fechaFinal, "todos", "todos", "todos");
    });

    function cargarDashboard(fi, ff, cat, tercero, idUsuario) {
        idUsuario = idUsuario || "todos";
        cargarKPIs(fi, ff, cat, tercero, idUsuario);
        cargarGrafico(fi, ff, cat, tercero, idUsuario);
        initializeTable(fi, ff, cat, tercero, idUsuario);
    }

    /*=============================================
    CARGAR DATOS INICIALES
    =============================================*/
    var categoriaInicial = $("#seleccionarCategoriaReporte").val();
    cargarDashboard(fechaInicial, fechaFinal, categoriaInicial, "todos", "todos");

    function cargarKPIs(fechaInicial, fechaFinal, categoria, tercero, idUsuario) {
        $.ajax({
            url: "ajax/facturacion.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerKPIsReporte",
                fechaInicial: fechaInicial,
                fechaFinal: fechaFinal,
                categoria: categoria,
                tercero: tercero,
                idUsuario: idUsuario || "todos",
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: "json",
            success: function (respuesta) {
                $("#widget-total-ventas").html("$" + Number(respuesta.totalVentas).toLocaleString());
                $("#widget-total-iva").html("$" + Number(respuesta.totalIva).toLocaleString());
                $("#widget-total-ds").html("$" + Number(respuesta.totalDS).toLocaleString());
                $("#widget-total-docs").html(respuesta.totalDocs);
            }
        });
    }

    var lineChart = null;

    function cargarGrafico(fechaInicial, fechaFinal, categoria, tercero, idUsuario) {
        $.ajax({
            url: "ajax/facturacion.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerVentasGrafico",
                fechaInicial: fechaInicial,
                fechaFinal: fechaFinal,
                categoria: categoria,
                tercero: tercero,
                idUsuario: idUsuario || "todos",
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: "json",
            success: function (respuesta) {
                var labels = [];
                var data = [];

                if (respuesta && Array.isArray(respuesta)) {
                    respuesta.forEach(function (punto) {
                        labels.push(punto.dia);
                        data.push(punto.total);
                    });
                }

                var lineChartCanvas = $("#lineChartReports").get(0).getContext("2d");

                if (lineChart) {
                    lineChart.destroy();
                }

                var chartLabel = "Ventas Diarias";
                var chartColor = "rgba(0,166,90,0.3)";
                var chartStroke = "rgba(0,166,90,0.8)";
                var chartPoint = "#00a65a";

                if (categoria == "nc") {
                    chartLabel = "Notas Crédito Diarias";
                    chartColor = "rgba(243,156,18,0.3)";
                    chartStroke = "rgba(243,156,18,0.8)";
                    chartPoint = "#f39c12";
                } else if (categoria == "ds") {
                    chartLabel = "Docs Soporte Diarios";
                    chartColor = "rgba(60,141,188,0.3)";
                    chartStroke = "rgba(60,141,188,0.8)";
                    chartPoint = "#3c8dbc";
                } else if (categoria == "na") {
                    chartLabel = "Notas Ajuste Diarias";
                    chartColor = "rgba(221,75,57,0.3)";
                    chartStroke = "rgba(221,75,57,0.8)";
                    chartPoint = "#dd4b39";
                }

                var lineChartData = {
                    labels: labels,
                    datasets: [
                        {
                            label: chartLabel,
                            fillColor: chartColor,
                            strokeColor: chartStroke,
                            pointColor: chartPoint,
                            pointStrokeColor: "#fff",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(60,141,188,1)",
                            data: data
                        }
                    ]
                };

                var lineChartOptions = {
                    showScale: true,
                    scaleShowGridLines: false,
                    scaleGridLineColor: "rgba(0,0,0,.05)",
                    scaleGridLineWidth: 1,
                    scaleShowHorizontalLines: true,
                    scaleShowVerticalLines: true,
                    bezierCurve: true,
                    bezierCurveTension: 0.3,
                    pointDot: true,
                    pointDotRadius: 4,
                    pointDotStrokeWidth: 1,
                    pointHitDetectionRadius: 20,
                    datasetStroke: true,
                    datasetStrokeWidth: 2,
                    datasetFill: true,
                    maintainAspectRatio: false,
                    responsive: true
                };

                lineChart = new Chart(lineChartCanvas).Line(lineChartData, lineChartOptions);
            }
        });
    }

    var table = null;

    function initializeTable(fi, ff, cat, tercero, idUsuario) {

        if ($.fn.DataTable.isDataTable(".tablaReporteFacturacion")) {
            $(".tablaReporteFacturacion").DataTable().clear().destroy();
            // Asegurarse de que el body de la tabla esté limpio
            $(".tablaReporteFacturacion tbody").empty();
        }

        table = $(".tablaReporteFacturacion").DataTable({
            "ajax": {
                "url": "ajax/facturacion.ajax.php",
                "method": "POST",
                "data": {
                    "accion": "mostrarReporteDetallado",
                    "fechaInicial": fi,
                    "fechaFinal": ff,
                    "categoria": cat,
                    "tercero": tercero,
                    "idUsuario": idUsuario || "todos"
                    // csrf_token removido - manejado por csrf-helper.js
                }
            },
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

    /*=============================================
    BOTÓN VER FACTURA
    =============================================*/
    $(".tablaReporteFacturacion").on("click", ".btnVerFactura", function () {
        var idVenta = $(this).attr("idVenta");
        window.open("extensiones/tcpdf/pdf/descargar-pdf-detalle.php?codigo=" + idVenta, "_blank");
    });

    /*=============================================
    BOTÓN VER NOTA CRÉDITO
    =============================================*/
    $(".tablaReporteFacturacion").on("click", ".btnVerNotaCredito", function () {
        var idNota = $(this).attr("idNota");
        window.open("extensiones/tcpdf/pdf/descargar-pdf-nc.php?idNota=" + idNota, "_blank");
    });

    /*=============================================
    BOTÓN EXPORTAR EXCEL NATIVO GESTION
    =============================================*/
    $("#btnExportarExcelFacturacion").click(function () {
        var fi = fechaInicial;
        var ff = fechaFinal;
        var cat = $("#seleccionarCategoriaReporte").val();
        var tercero = "todos";
        var idUsuario = $("#seleccionarUsuarioReporte").val() || "todos";

        if (cat == "ds" || cat == "na") {
            tercero = $("#seleccionarProveedorReporte").val();
        } else {
            tercero = $("#seleccionarClienteReporte").val();
        }

        var url = "vistas/modulos/descargar-reporte-facturacion.php?reporte=reporte_facturacion";
        url += "&fechaInicial=" + fi + "&fechaFinal=" + ff;
        url += "&categoria=" + cat + "&tercero=" + tercero + "&idUsuario=" + idUsuario;

        window.open(url, '_blank');
    });

});
