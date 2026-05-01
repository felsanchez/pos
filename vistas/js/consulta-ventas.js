$(document).ready(function () {

    if ($(".tablaConsultaVentas").length > 0) {

        if ($.fn.DataTable.isDataTable('.tablaConsultaVentas')) {
            $('.tablaConsultaVentas').DataTable().destroy();
        }

        var table = $(".tablaConsultaVentas").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/datatable-ventas-visita.ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                    d.fechaInicial = $("#fechaInicial").val();
                    d.fechaFinal = $("#fechaFinal").val();
                }
            },
            "order": [[4, "desc"]], // Ordenar por Fecha por defecto
            "responsive": {
                "details": {
                    "type": "inline",
                    "renderer": function (api, rowIdx, columns) {
                        var finalHtml = '';
                        var hasHidden = false;

                        $.each(columns, function (i, col) {
                            if (!col.hidden) return;

                            hasHidden = true;
                            var label = col.title || ('Columna ' + col.columnIndex);
                            var data = col.data || '';

                            finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
                            finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
                            finalHtml += '<span style="color:#333;">' + data + '</span>';
                            finalHtml += '</div>';
                        });

                        if (!hasHidden) return false;

                        return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
                    }
                }
            },
            "columnDefs": [
                { "targets": 0, "responsivePriority": 1 }, // Código
                { "targets": 5, "responsivePriority": 2, "orderable": false }, // Acciones
                { "targets": 1, "responsivePriority": 3 }, // Cliente
                { "targets": 3, "responsivePriority": 4 }, // Total
                { "targets": 4, "responsivePriority": 5 }  // Fecha
            ],
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                }
            }
        });

        // Inicializar DateRangePicker
        if ($('#daterange-btn').length > 0) {
            
            var fechaInicialUrl = new URLSearchParams(window.location.search).get('fechaInicial');
            var fechaFinalUrl = new URLSearchParams(window.location.search).get('fechaFinal');

            $('#daterange-btn').daterangepicker({
                ranges: {
                    'Todos los documentos': [moment('2000-01-01'), moment()],
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: fechaInicialUrl ? moment(fechaInicialUrl) : moment(),
                endDate: fechaFinalUrl ? moment(fechaFinalUrl) : moment(),
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Limpiar'
                }
            }, function (start, end) {
                $('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                $('#fechaInicial').val(start.format('YYYY-MM-DD'));
                $('#fechaFinal').val(end.format('YYYY-MM-DD'));
                
                table.ajax.reload();
            });

            // Limpiar filtros
            $('#daterange-btn').on('cancel.daterangepicker', function(ev, picker) {
                $('#daterange-btn span').html('<i class="fa fa-calendar"></i> Rango de fecha');
                $('#fechaInicial').val('');
                $('#fechaFinal').val('');
                table.ajax.reload();
            });
        }
    }
});
