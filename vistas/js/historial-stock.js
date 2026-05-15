$(document).ready(function () {

    // 0. NO establecer fechas por defecto al cargar (para mostrar todo el historial)
    $("#fi_s").val("");
    $("#ff_s").val("");
    $("#span-rango-stock").html('<i class="fa fa-calendar"></i> Rango de fecha');

    // 1. Inicialización de la Tabla
    if ($(".tablaHistorialStock").length > 0) {
        $(".tablaHistorialStock").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/movimientos.ajax.php",
                "method": "POST",
                "data": function (d) {
                    d.id_producto = $("#cat_s").val() || "";
                    d.tipo_movimiento = $("#tipo_s").val() || "";
                    d.fecha_desde = $("#fi_s").val() || "";
                    d.fecha_hasta = $("#ff_s").val() || "";
                    d.usuario = $("#user_s").val() || "";
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                    
                    // Extraer id_bodega del filtro maestro
                    var idBodega = $('#sucursalReporteMaestro').val();
                    if(idBodega && idBodega !== 'todos'){
                        d.id_bodega = idBodega;
                    }
                }
            },
            "columns": [
                { "data": null, "defaultContent": "", "orderable": false, "className": "dtr-control" },
                { "data": 0 }, // Producto
                { "data": 1 }, // Tipo Movimiento
                { "data": 2 }, // Tipo
                {
                    "data": 3, // Fecha
                    "render": function (data, type, row) {
                        if (type === 'display' && data) {
                            var fecha = new Date(data);
                            return fecha.toLocaleString('es-ES', { year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit'});
                        }
                        return data;
                    }
                },
                { "data": 4 }, // Cantidad
                { "data": 5 }, // Stock Anterior
                { "data": 6 }, // Stock Nuevo
                { "data": 7 }, // Usuario
                { "data": 8 }, // Referencia
                {
                    "data": 9, // Notas
                    "render": function (data, type, row) {
                        var id = row.id || row[10] || row.DT_RowId;
                        return '<div contenteditable="true" class="celda-notas-movimiento" data-id="' + id + '">' + (data || "") + '</div>';
                    }
                }
            ],
            "order": [[4, "desc"]],
            "columnDefs": [
                { "targets": 0, "responsivePriority": 1 },
                { "targets": 1, "responsivePriority": 1 },
                { "targets": 2, "responsivePriority": 1 },
                { "targets": 5, "responsivePriority": 1 },
                { "targets": 4, "responsivePriority": 2 },
                { "targets": [3, 6, 7, 8, 9, 10], "responsivePriority": 3 }
            ],
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
            "responsive": {
                "details": {
                    "type": "column",
                    "target": 0
                }
            },
            "autoWidth": false,
            "initComplete": function() {
                cargarResumenStock(); 
            }
        });
    }

    // 2. Inicialización de Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ width: '100%' });
        
        // Forzar reset de sucursal al cargar para que siempre inicie en "Vista Global" (Solo administradores)
        if($("#sucursalReporteMaestro").is("select")){
            $("#sucursalReporteMaestro").val("todos").trigger("change.select2");
            // Limpiar cualquier rastro previo en localStorage
            localStorage.removeItem("sucursalReporteMaestro");
        }
    }

    // 3. Función de Recarga
    function reloadStockTable() {
        if ($.fn.DataTable.isDataTable('.tablaHistorialStock')) {
            $('.tablaHistorialStock').DataTable().ajax.reload();
        }
        cargarResumenStock();
    }

    // 4. Listeners de Filtros
    $(document).on("change", "#cat_s, #tipo_s, #user_s, #fi_s, #ff_s, #sucursalReporteMaestro", function () {
        reloadStockTable();
    });

    // 5. Botón Limpiar (Vuelve al estado inicial: Todo)
    $("#btnLimpiar").on("click", function () {
        $("#cat_s, #tipo_s, #user_s, #fi_s, #ff_s").val("").trigger("change");
        $("#span-rango-stock").html('<i class="fa fa-calendar"></i> Rango de fecha');
        
        if($("#sucursalReporteMaestro").is("select")){
            $("#sucursalReporteMaestro").val("todos").trigger("change.select2");
        }
        
        reloadStockTable();
    });

    // 6. Rango de Fechas (Pre-posicionado en HOY al abrir, pero sin filtrar al inicio)
    if ($('#btn-rango-stock').length > 0) {
        $('#btn-rango-stock').daterangepicker({
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')]
            },
            startDate: moment(), // Pre-seleccionado en Hoy al abrir
            endDate: moment()    // Pre-seleccionado en Hoy al abrir
        }, function (start, end) {
            $('#span-rango-stock').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $("#fi_s").val(start.format('YYYY-MM-DD'));
            $("#ff_s").val(end.format('YYYY-MM-DD'));
            reloadStockTable();
        });
    }

    function cargarResumenStock() {
        var filtros = {
            accion: "obtenerResumen",
            fecha_desde: $("#fi_s").val(),
            fecha_hasta: $("#ff_s").val(),
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Extraer id_bodega del filtro maestro
        var idBodega = $('#sucursalReporteMaestro').val();
        if(idBodega && idBodega !== 'todos'){
            filtros.id_bodega = idBodega;
        }

        $.ajax({
            url: "ajax/movimientos.ajax.php",
            method: "POST",
            data: filtros,
            dataType: "json",
            success: function (resumen) {
                $("#totalVentas, #totalCreaciones, #totalEdiciones, #totalMovimientos").text("0");
                var totalMovimientos = 0;
                var totalCreaciones = 0;
                resumen.forEach(function (item) {
                    totalMovimientos += parseInt(item.total_movimientos);
                    if (item.tipo_movimiento == "venta") $("#totalVentas").text(item.total_unidades);
                    if (item.tipo_movimiento == "creacion_producto" || item.tipo_movimiento == "creacion_variante") totalCreaciones += parseInt(item.total_unidades);
                    if (item.tipo_movimiento == "edicion_stock") $("#totalEdiciones").text(item.total_unidades);
                });
                $("#totalCreaciones").text(totalCreaciones);
                $("#totalMovimientos").text(totalMovimientos);
            }
        });
    }

    // 7. Notas Inline
    $(document).on('blur', '.celda-notas-movimiento', function () {
        const $celda = $(this);
        const id = $celda.data('id');
        const nuevaNota = $celda.text().trim();
        if (!id) return;
        $.ajax({
            url: 'ajax/movimientos.ajax.php',
            method: 'POST',
            data: { id: id, notas: nuevaNota, accion: 'actualizarNota', csrf_token: $('meta[name="csrf-token"]').attr('content') },
            success: function (respuesta) {
                if (respuesta === "ok" || respuesta === "\"ok\"" || respuesta === true) {
                    $celda.css({ 'background-color': '#d4edda' });
                    setTimeout(function () { $celda.css({ 'background-color': '' }); }, 800);
                }
            }
        });
    });

});
