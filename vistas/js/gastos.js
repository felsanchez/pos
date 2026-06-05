$(document).ready(function () {
    
    // 1. Inicialización de la Tabla con todas las funciones Premium
    if ($('#tablaGastos').length > 0) {
        $('#tablaGastos').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/gastos.ajax.php",
                "type": "POST",
                "data": function(d) {
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                    d.fechaInicio = $("#filtroFechaInicio").val();
                    d.fechaFin = $("#filtroFechaFin").val();
                    d.categoriaId = $("#cat_g").val();
                    d.proveedorId = $("#prov_g").val();
                    d.bodegaId = $("#sucursal_g").val();
                }
            },
            "createdRow": function(row, data, dataIndex) {
                if (data.DT_RowAttr && data.DT_RowAttr.style) {
                    $(row).attr('style', data.DT_RowAttr.style);
                }
            },
            "order": [[0, "asc"]],
            "columnDefs": [
                { "targets": 0, "className": "dtr-control", "responsivePriority": 1 },
                { "targets": -1, "responsivePriority": 2, "orderable": false }, // Acciones
                { "targets": 1, "responsivePriority": 3 }, // Monto
                { "targets": -3, "responsivePriority": 4 }, // Fecha (3rd from last)
                { "targets": -2, "responsivePriority": 5 }  // Notas (2nd from last)
            ],
            "responsive": {
                "details": {
                    "type": "column",
                    "target": 0,
                    "renderer": function (api, rowIdx, columns) {
                        var finalHtml = '';
                        var hasHidden = false;
                        $.each(columns, function (i, col) {
                            if (!col.hidden) return;
                            hasHidden = true;
                            var label = col.title || ('Columna ' + col.columnIndex);
                            finalHtml += '<div style="padding:8px 10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px; text-align:left;">';
                            finalHtml += '<span class="text-bold" style="color:#555; min-width:100px;">' + label + ':</span>';
                            if (label === 'Notas') {
                                var rowNode = api.row(rowIdx).node();
                                var idGasto = $(rowNode).find('.celda-notas-gasto').data('id') || "";
                                var notasText = $(rowNode).find('.celda-notas-gasto').text().trim();
                                finalHtml += '<div contenteditable="true" class="celda-notas-gasto" data-id="' + idGasto + '" style="flex:1; outline:none; border:1px dashed #ccc; padding:6px; background:#fff9e6; margin-top:5px; width:100%;">' + notasText + '</div>';
                            } else {
                                finalHtml += '<span style="color:#333;">' + col.data + '</span>';
                            }
                            finalHtml += '</div>';
                        });
                        return hasHidden ? $('<div style="padding:0; background:#fcfcfc; width:100%;">').append(finalHtml) : false;
                    }
                }
            },
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
            "preDrawCallback": function () { if (!$(this).hasClass('datatable-ready')) $(this).css('visibility', 'hidden'); },
            "initComplete": function () { $(this).addClass('datatable-ready').css('visibility', 'visible'); }
        });
    }

    // 2. Inicialización de Select2
    $('.select2').select2({ width: '100%' });

    // 3. Función de Recarga (La que arregló el problema)
    function reloadTable() {
        if ($.fn.DataTable.isDataTable('#tablaGastos')) {
            $('#tablaGastos').DataTable().ajax.reload();
        }
    }

    // 4. Listeners de Filtros (Nuevos IDs fijos)
    $(document).on("change", "#cat_g, #prov_g, #sucursal_g, #filtroFechaInicio, #filtroFechaFin", function () {
        reloadTable();
    });

    // 5. Botón Limpiar
    $("#btnLimpiarGastos").on("click", function () {
        $("#filtroFechaInicio, #filtroFechaFin, #cat_g, #prov_g, #sucursal_g").val("").trigger('change');
        $("#daterange-btn span").html('<i class="fa fa-calendar"></i> Rango de fecha');
        reloadTable();
    });

    // 6. Rango de Fechas
    if ($('#daterange-btn').length > 0) {
        $('#daterange-btn').daterangepicker({
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')]
            },
            startDate: moment(),
            endDate: moment()
        }, function (start, end) {
            $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $("#filtroFechaInicio").val(start.format('YYYY-MM-DD'));
            $("#filtroFechaFin").val(end.format('YYYY-MM-DD')).trigger('change');
        });
    }

    // 7. Edición rápida de notas (Blur)
    $(document).on('blur', '.celda-notas-gasto', function () {
        var elemento = $(this);
        var id = elemento.data('id');
        var nota = elemento.text().trim();
        if (!id) return;
        $.ajax({
            url: 'ajax/gastos-actualizar-nota.ajax.php',
            method: 'POST',
            data: { idGasto: id, nota: nota, csrf_token: $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function (respuesta) {
                if (respuesta == "ok") {
                    console.log("Nota de gasto actualizada");
                    // Feedback visual (destello verde)
                    elemento.css('background-color', '#dff0d8');
                    setTimeout(function () {
                        elemento.css('background-color', '');
                    }, 500);
                }
            }
        });
    });

    // --- ACCIONES DE MODALES ---
    $(document).on("click", ".btnEditarGasto", function () {
        var idGasto = $(this).attr("idGasto");
        var datos = new FormData();
        datos.append("idGasto", idGasto);
        $.ajax({
            url: "ajax/gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                $("#editarConceptoGasto").val(respuesta["concepto"]);
                $("#editarMontoGasto").val(respuesta["monto"]);
                $("#editarFechaGasto").val(respuesta["fecha"]);
                $("#editarCategoriaGasto").val(respuesta["id_categoria_gasto"]);
                $("#editarProveedorGasto").val(respuesta["id_proveedor"]);
                $("#editarMetodoPagoGasto").val(respuesta["metodo_pago"]);
                $("#editarNumeroComprobante").val(respuesta["numero_comprobante"]);
                $("#editarEstadoGasto").val(respuesta["estado"]);
                $("#editarNotasGasto").val(respuesta["notas"]);
                $("#imagenActual").val(respuesta["imagen_comprobante"]);
                if (respuesta["imagen_comprobante"]) {
                    $("#previsualizarImagen").html('<img src="' + respuesta["imagen_comprobante"] + '" class="img-thumbnail" width="100px">');
                } else {
                    $("#previsualizarImagen").empty();
                }
                $('#modalEditarGasto input[name="idGasto"]').val(idGasto);
            }
        });
    });

    $(document).on("click", ".btnEliminarGasto", function () {
        var idGasto = $(this).attr("idGasto");
        swal({
            title: '¿Eliminar gasto?',
            text: "Esta acción no se puede deshacer.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.value) {
                var datos = new FormData();
                datos.append("idGastoEliminar", idGasto);
                $.ajax({
                    url: "ajax/gastos.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (respuesta) {
                        if (respuesta == "ok") {
                            swal({
                                type: "success",
                                title: "¡Eliminado!",
                                text: "El gasto ha sido eliminado correctamente.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".btnEditarCategoriaGasto", function(){

        var idCategoria = $(this).attr("idCategoria");
    
        var datos = new FormData();
        datos.append("idCategoria", idCategoria);
    
        $.ajax({
            url: "ajax/categorias_gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType:"json",
            success: function(respuesta){
    
                $("#editarNombreCategoriaGasto").val(respuesta["nombre"]);
                $("#idCategoriaGasto").val(respuesta["id"]);
                $("#editarColorCategoriaGasto").val(respuesta["color"]);
                $("#editarDescripcionCategoriaGasto").val(respuesta["descripcion"]);
    
            }
    
        })
    
    });
    
    $(document).on("click", ".btnEliminarCategoriaGasto", function(){
    
        var idCategoria = $(this).attr("idCategoria");
        var nombreCategoria = $(this).attr("nombreCategoria");
    
        swal({
            title: '¿Está seguro de borrar la categoría?',
            text: "¡Si no lo está puede cancelar la acción!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Si, borrar categoría!'
        }).then(function(result){
    
            if(result.value){
    
                var datos = new FormData();
                datos.append("idCategoriaGastoEliminar", idCategoria);
    
                $.ajax({
                    url: "ajax/categorias_gastos.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(respuesta){
    
                        if(respuesta == "ok"){
    
                            swal({
                                type: "success",
                                title: "La categoría ha sido borrada correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function(result){
                                if (result.value) {
                                    window.location = "gastos";
                                }
                            })
    
                        } else if (respuesta == "error_gastos_asociados") {
                            swal({
                                type: "error",
                                title: "¡No se puede eliminar la categoría porque tiene gastos asociados!",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            })
                        } else if (respuesta == "error_csrf") {
                            swal({
                                type: "error",
                                title: "Error de seguridad CSRF",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            })
                        }
                    }
    
                })
    
            }
    
        })
    
    });

    // Fijar z-index del modal Editar Categoría para que no se superponga incorrectamente con Gestionar Categorías
    $('#modalEditarCategoria').off('show.bs.modal').on('show.bs.modal', function (event) {
        $(this).appendTo('body');
    });

    $('#modalEditarCategoria').off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).css('z-index', 1060);
        var backdrops = $('.modal-backdrop');
        if (backdrops.length >= 2) {
            $(backdrops[0]).css('z-index', 1040);
            $(backdrops[backdrops.length - 1]).css('z-index', 1055);
        }
        $('#editarNombreCategoriaGasto').focus();
    });

    $('#modalEditarCategoria').off('hidden.bs.modal').on('hidden.bs.modal', function () {
        if ($('#modalGestionarCategorias').hasClass('in')) {
            $('body').addClass('modal-open');
        }
    });

});