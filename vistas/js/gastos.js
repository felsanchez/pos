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
            "order": [[1, "asc"]],
            "columnDefs": [
                { "targets": 0, "data": null, "defaultContent": "", "orderable": false, "className": "control dtr-control", "responsivePriority": 1 },
                { "targets": 1, "responsivePriority": 1 }, // Concepto
                { "targets": -1, "responsivePriority": 2, "orderable": false }, // Acciones
                { "targets": 2, "responsivePriority": 3 }, // Monto
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
                                var placeholderAttr = (notasText === "") ? ' data-placeholder="Escribe una nota..."' : "";
                                finalHtml += '<div contenteditable="true" class="celda-notas-gasto" data-id="' + idGasto + '"' + placeholderAttr + ' style="flex:1; outline:none; border:1px dashed #ccc; padding:6px; background:#fff9e6; margin-top:5px; width:100%;">' + notasText + '</div>';
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
            "drawCallback": function (settings) {
                inicializarPlaceholders();
            },
            "initComplete": function () { $(this).addClass('datatable-ready').css('visibility', 'visible'); }
        });
    }

    $('.select2').select2({ width: '100%' });

    // Forzar reset de sucursal al cargar para que siempre inicie en la sucursal por defecto
    if ($("#sucursal_g").is("select")) {
        var defaultSucursal = typeof defaultSucursalGastos !== 'undefined' ? defaultSucursalGastos : '';
        $("#sucursal_g").val(defaultSucursal).trigger("change.select2");
    }

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

    $("#btnLimpiarGastos").on("click", function () {
        var defaultSucursal = typeof defaultSucursalGastos !== 'undefined' ? defaultSucursalGastos : '';
        $("#filtroFechaInicio, #filtroFechaFin, #cat_g, #prov_g").val("").trigger('change');
        $("#sucursal_g").val(defaultSucursal).trigger('change');
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

    // 7. Edición rápida de notas (Blur & Input)
    $(document).on('input', '.celda-notas-gasto', function () {
        var elemento = $(this);
        if (elemento.text().trim() === '') {
            elemento.attr('data-placeholder', 'Escribe una nota...');
            elemento.empty(); // Limpiar residuos del DOM como <br>
        } else {
            elemento.removeAttr('data-placeholder');
        }
    });

    $(document).on('blur', '.celda-notas-gasto', function () {
        var elemento = $(this);
        var id = elemento.data('id');
        var nota = elemento.text().trim();

        if (nota === '') {
            elemento.attr('data-placeholder', 'Escribe una nota...');
            elemento.empty(); // Asegurar elemento vacío en el DOM
        } else {
            elemento.removeAttr('data-placeholder');
        }

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

    // Función para inicializar placeholders en celdas vacías
    function inicializarPlaceholders() {
        $('.celda-notas-gasto').each(function () {
            if ($(this).text().trim() === '') {
                $(this).attr('data-placeholder', 'Escribe una nota...');
            } else {
                $(this).removeAttr('data-placeholder');
            }
        });
    }

    inicializarPlaceholders();

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
                                if ($.fn.DataTable.isDataTable('#tablaGastos')) {
                                    $('#tablaGastos').DataTable().ajax.reload(null, false);
                                } else {
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
    $('#modalEditarCategoriaGasto').off('show.bs.modal').on('show.bs.modal', function (event) {
        $(this).appendTo('body');
    });

    $('#modalEditarCategoriaGasto').off('shown.bs.modal').on('shown.bs.modal', function () {
        $(this).css('z-index', 1060);
        var backdrops = $('.modal-backdrop');
        if (backdrops.length >= 2) {
            $(backdrops[0]).css('z-index', 1040);
            $(backdrops[backdrops.length - 1]).css('z-index', 1055);
        }
        $('#editarNombreCategoriaGasto').focus();
    });

    $('#modalEditarCategoriaGasto').off('hidden.bs.modal').on('hidden.bs.modal', function () {
        if ($('#modalGestionarCategorias').hasClass('in')) {
            $('body').addClass('modal-open');
        }
    });

    /*=============================================
    REVISAR SI LA CATEGORÍA DE GASTO YA EXISTE
    =============================================*/
    $(document).on("change", "#nombreCategoriaGasto, input[name='nombreCategoriaGasto']", function () {
        $(".alert").remove();
        var categoria = $(this).val();

        if (categoria.trim() === "") return;

        var datos = new FormData();
        datos.append("validarCategoriaGasto", categoria);

        $.ajax({
            url: "ajax/categorias_gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta) {
                    $("#nombreCategoriaGasto").parent().after('<div class="alert alert-warning">¡Esta categoría de gasto ya existe en la base de datos!</div>');
                    $("#nombreCategoriaGasto").val("");
                }
            }
        });
    });

    /*=============================================
    GUARDAR CREAR CATEGORÍA DE GASTO VÍA AJAX
    =============================================*/
    $(document).on("submit", "#formAgregarCategoriaGasto", function (e) {
        e.preventDefault();

        var form = this;
        var boton = $(form).find("button[type='submit']");
        boton.prop('disabled', true);
        var htmlOriginal = boton.html();
        boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        swal({
            title: 'Guardando categoría',
            text: 'Por favor espere mientras se procesa la información...',
            type: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                swal.showLoading()
            }
        });

        var datos = new FormData(form);
        datos.append("guardarCrearCategoriaGasto", "ok");

        $.ajax({
            url: "ajax/categorias_gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                boton.prop('disabled', false).html(htmlOriginal);

                if (respuesta.status === "ok") {
                    swal({
                        type: "success",
                        title: "¡Éxito!",
                        text: respuesta.mensaje,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        window.location.reload();
                    });
                } else {
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: respuesta.mensaje || "No se pudo guardar la categoría.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            },
            error: function () {
                boton.prop('disabled', false).html(htmlOriginal);
                swal({
                    type: "error",
                    title: "¡Error!",
                    text: "Ocurrió un problema de conexión al guardar la categoría.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });

    /*=============================================
    GUARDAR EDITAR CATEGORÍA DE GASTO VÍA AJAX
    =============================================*/
    $(document).on("submit", "#formEditarCategoriaGasto", function (e) {
        e.preventDefault();

        var form = this;
        var boton = $(form).find("button[type='submit']");
        boton.prop('disabled', true);
        var htmlOriginal = boton.html();
        boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        swal({
            title: 'Actualizando categoría',
            text: 'Por favor espere mientras se procesa la información...',
            type: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                swal.showLoading()
            }
        });

        var datos = new FormData(form);
        datos.append("guardarEditarCategoriaGasto", "ok");

        $.ajax({
            url: "ajax/categorias_gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                boton.prop('disabled', false).html(htmlOriginal);

                if (respuesta.status === "ok") {
                    swal({
                        type: "success",
                        title: "¡Éxito!",
                        text: respuesta.mensaje,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        $("#modalEditarCategoriaGasto").modal("hide");
                        window.location.reload();
                    });
                } else {
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: respuesta.mensaje || "No se pudo actualizar la categoría.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            },
            error: function () {
                boton.prop('disabled', false).html(htmlOriginal);
                swal({
                    type: "error",
                    title: "¡Error!",
                    text: "Ocurrió un problema de conexión al actualizar la categoría.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });

    /*=============================================
    GESTIÓN DE IMÁGENES DE COMPROBANTE DE GASTO
    =============================================*/
    $(document).on("click", ".img-comprobante-clickeable, .img-ampliar-gasto, .btnVerFotoGasto", function () {
        var rutaImagen = $(this).attr("data-imagen") || $(this).attr("src");
        var idGasto = $(this).attr("data-idgasto") || $(this).attr("data-id");

        if (!rutaImagen) return;

        $("#imagenComprobanteAmpliada").attr("src", rutaImagen);
        $("#idGastoImagen").val(idGasto);
        $(".nuevaImagenComprobante").val("");
        $("#modalAmpliarComprobanteGasto").modal("show");
    });

    $(document).on("change", ".nuevaImagenComprobante", function () {
        var imagen = this.files[0];
        if (imagen) {
            if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
                $(".nuevaImagenComprobante").val("");
                swal({ title: "Error", text: "¡La imagen debe ser JPG o PNG!", type: "error" });
            } else if (imagen["size"] > 2000000) {
                $(".nuevaImagenComprobante").val("");
                swal({ title: "Error", text: "¡La imagen no debe pesar más de 2MB!", type: "error" });
            } else {
                var datosImagen = new FileReader;
                datosImagen.readAsDataURL(imagen);
                $(datosImagen).on("load", function (event) {
                    $("#imagenComprobanteAmpliada").attr("src", event.target.result);
                });
            }
        }
    });

    $(document).on("click", ".btnGuardarImagenComprobante", function () {
        var idGasto = $("#idGastoImagen").val();
        var imagen = $(".nuevaImagenComprobante")[0].files[0];

        if (!imagen) {
            swal({ title: "Advertencia", text: "Seleccione una nueva imagen", type: "warning" });
            return;
        }

        var datos = new FormData();
        datos.append("idGastoImagen", idGasto);
        datos.append("nuevaImagenComprobante", imagen);
        datos.append("csrf_token", $('meta[name="csrf-token"]').attr('content'));

        swal({ title: 'Cargando...', allowOutsideClick: false, onBeforeOpen: () => { swal.showLoading() } });

        $.ajax({
            url: "ajax/gastos.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta == "ok" || (typeof respuesta === "object" && respuesta.status === "ok")) {
                    swal({ type: "success", title: "¡Imagen actualizada!", text: "La imagen del comprobante ha sido actualizada correctamente.", showConfirmButton: true }).then(() => {
                        $("#modalAmpliarComprobanteGasto").modal("hide");
                        if ($.fn.DataTable.isDataTable('#tablaGastos')) {
                            $('#tablaGastos').DataTable().ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    swal({ type: "error", title: "Error", text: (typeof respuesta === "object" && respuesta.mensaje) ? respuesta.mensaje : "No se pudo actualizar la imagen.", showConfirmButton: true });
                }
            },
            error: function () {
                swal({ type: "error", title: "Error", text: "Ocurrió un problema de conexión al guardar la imagen.", showConfirmButton: true });
            }
        });
    });

});