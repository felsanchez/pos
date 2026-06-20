$(document).ready(function() {

    // Token CSRF global
    var csrfTokenCRM = $('input[name="csrf_token"]').val() || '';

    // =============================================
    // INITIALIZATION & SETUP
    // =============================================
    
    // Mover modales al body para evitar solapamientos z-index con AdminLTE
    $('#modalAgregarLead').appendTo("body");
    $('#modalEditarLead').appendTo("body");
    $('#modalGestionarEtapas').appendTo("body");
    $('#modalEditarEtapaCRM').appendTo("body");

    // Inicializar Select2 en los selectores correspondientes
    if ($.fn.select2) {
        $('#filtroVendedorCRM').select2({
            width: '100%'
        });
    }

    // Ejecutar filtrado inicial
    filtrarTablero();

    // =============================================
    // DRAG AND DROP (KANBAN INTERACTION)
    // =============================================
    
    var dragCard = null;
    var originalParent = null;
    var originalNextSibling = null;
    var droppedSuccessfully = false;

    $(document).on('dragstart', '.crm-lead-card', function(e) {
        dragCard = this;
        originalParent = this.parentNode;
        originalNextSibling = this.nextSibling;
        droppedSuccessfully = false;
        $(this).addClass('dragging');
    });

    $(document).on('dragend', '.crm-lead-card', function(e) {
        $(this).removeClass('dragging');
        if (!droppedSuccessfully && dragCard && originalParent) {
            if (originalNextSibling) {
                originalParent.insertBefore(dragCard, originalNextSibling);
            } else {
                originalParent.appendChild(dragCard);
            }
            recálculoTotalesColumnas();
        }
        dragCard = null;
        originalParent = null;
        originalNextSibling = null;
        $('.crm-cards-container').removeClass('dragover');
    });

    $('.crm-cards-container').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');

        if (dragCard) {
            var afterElement = getDragAfterElement(this, e.originalEvent.clientY);
            if (afterElement == null) {
                $(this).append(dragCard);
            } else {
                $(dragCard).insertBefore(afterElement);
            }
        }
    });

    $('.crm-cards-container').on('dragleave', function(e) {
        $(this).removeClass('dragover');
    });

    $('.crm-cards-container').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');

        if (dragCard) {
            droppedSuccessfully = true;
            var cardId = $(dragCard).attr('data-id');
            var nuevaEtapa = $(this).attr('data-etapa');

            // Si se mueve a la columna Facturado o Perdido, marcar visualmente como archivado
            if (nuevaEtapa === "Facturado" || nuevaEtapa === "Perdido") {
                $(dragCard).addClass('crm-lead-archived');
            } else {
                $(dragCard).removeClass('crm-lead-archived');
            }

            // Recopilar el orden de todas las tarjetas en la etapa destino
            var ordenes = [];
            $(this).find('.crm-lead-card').each(function() {
                var id = $(this).attr('data-id');
                if (id) ordenes.push(id);
            });

            // Actualizar valor en BD vía AJAX
            $.ajax({
                url: 'ajax/crm.ajax.php',
                method: 'POST',
                data: {
                    accion: 'actualizarEtapa',
                    idLead: cardId,
                    nuevaEtapa: nuevaEtapa,
                    ordenes: ordenes,
                    csrf_token: csrfTokenCRM
                },
                dataType: 'json',
                success: function(respuesta) {
                    if (respuesta === "ok") {
                        // Refrescar totales de las columnas y métricas
                        recálculoTotalesColumnas();

                        // Alerta de satisfacción breve (1.2 segundos)
                        swal({
                            type: "success",
                            title: "¡Etapa actualizada!",
                            text: "La tarjeta se ha movido de etapa correctamente.",
                            timer: 1200,
                            showConfirmButton: false
                        });
                    } else if (respuesta === "error_csrf") {
                        swal({
                            type: "error",
                            title: "Error de seguridad",
                            text: "Token CSRF inválido. Por favor recarga la página.",
                            confirmButtonText: "Cerrar"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else if (respuesta === "error_permisos") {
                        swal({
                            type: "error",
                            title: "¡Error de permisos!",
                            text: "No tienes permisos para realizar esta acción.",
                            confirmButtonText: "Cerrar"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: "No se pudo actualizar la etapa en el servidor.",
                            confirmButtonText: "Cerrar"
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function() {
                    console.error("Error al actualizar la etapa.");
                    window.location.reload();
                }
            });
        }
    });

    // Helper para determinar la posición del cursor de arrastre relativo a las tarjetas existentes
    function getDragAfterElement(container, y) {
        var draggableElements = [...container.querySelectorAll('.crm-lead-card:not(.dragging)')];

        return draggableElements.reduce(function(closest, child) {
            var box = child.getBoundingClientRect();
            var offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // Recalcular el dinero y conteo acumulado de cada columna, y las métricas superiores
    function recálculoTotalesColumnas() {
        $('.crm-kanban-column').each(function() {
            var columna = this;
            var totalDinero = 0;
            var conteo = 0;

            $(columna).find('.crm-lead-card:visible').each(function() {
                var card = this;
                // Obtener valor limpio del footer o del atributo si lo tuviéramos
                var valorTexto = $(card).find('.crm-lead-value').text().replace(/[^0-9\.]/g, '');
                var valor = parseFloat(valorTexto) || 0;
                totalDinero += valor;
                conteo++;
            });

            // Actualizar cabecera de columna
            $(columna).find('.crm-column-count').text(conteo);
            $(columna).find('.crm-column-value-total strong').text('$ ' + totalDinero.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        });

        // Calcular y actualizar las 5 métricas superiores de forma dinámica
        var totalNegociacion = 0;
        var montoNegociacion = 0;
        var totalGanados = 0;
        var montoGanados = 0;
        var totalPerdidos = 0;
        var montoPerdidos = 0;

        var textoBusqueda = $('#buscadorCRM').val().toLowerCase();
        var vendedorSeleccionado = $('#filtroVendedorCRM').val();
        var prioridadSeleccionada = $('#filtroPrioridadCRM').val();

        $('.crm-lead-card').each(function() {
            var card = this;
            var cliente = $(card).attr('data-cliente').toLowerCase();
            var titulo = $(card).attr('data-titulo').toLowerCase();
            var vendedor = $(card).attr('data-vendedor');
            var prioridad = $(card).attr('data-prioridad');
            var etapa = $(card).parent().attr('data-etapa');

            // Filtrado del lead para métricas (excluyendo el filtro de archivados de la visualización)
            var coincideTexto = cliente.includes(textoBusqueda) || titulo.includes(textoBusqueda);
            var coincideVendedor = !vendedorSeleccionado || vendedor === vendedorSeleccionado;
            var coincidePrioridad = !prioridadSeleccionada || prioridad === prioridadSeleccionada;

            if (coincideTexto && coincideVendedor && coincidePrioridad) {
                var valorTexto = $(card).find('.crm-lead-value').text().replace(/[^0-9\.]/g, '');
                var valor = parseFloat(valorTexto) || 0;

                if (etapa === "Facturado") {
                    totalGanados++;
                    montoGanados += valor;
                } else if (etapa === "Perdido") {
                    totalPerdidos++;
                    montoPerdidos += valor;
                } else {
                    totalNegociacion++;
                    montoNegociacion += valor;
                }
            }
        });

        // Actualizar elementos en el DOM
        $('#metricTotalSeguimiento').text(totalNegociacion + ' Oportunidades');
        $('#metricMontoSeguimiento').text('$ ' + montoNegociacion.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#metricTotalFacturado').text(totalGanados + ' Ganados');
        $('#metricMontoFacturado').text('$ ' + montoGanados.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#metricTotalPerdidos').text(totalPerdidos + ' Perdidos');
        $('#metricMontoPerdidos').text('$ ' + montoPerdidos.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    // =============================================
    // FILTROS EN TIEMPO REAL
    // =============================================

    $('#buscadorCRM, #filtroVendedorCRM, #filtroPrioridadCRM, #toggleArchivadosCRM').on('input change', function() {
        filtrarTablero();
    });

    function filtrarTablero() {
        var textoBusqueda = $('#buscadorCRM').val().toLowerCase();
        var vendedorSeleccionado = $('#filtroVendedorCRM').val();
        var prioridadSeleccionada = $('#filtroPrioridadCRM').val();
        var mostrarArchivados = $('#toggleArchivadosCRM').is(':checked');

        $('.crm-lead-card').each(function() {
            var card = this;
            var cliente = $(card).attr('data-cliente').toLowerCase();
            var titulo = $(card).attr('data-titulo').toLowerCase();
            var vendedor = $(card).attr('data-vendedor');
            var prioridad = $(card).attr('data-prioridad');
            var estaArchivado = $(card).parent().attr('data-etapa') === "Facturado" || $(card).parent().attr('data-etapa') === "Perdido";

            // Condiciones
            var coincideTexto = cliente.includes(textoBusqueda) || titulo.includes(textoBusqueda);
            var coincideVendedor = !vendedorSeleccionado || vendedor === vendedorSeleccionado;
            var coincidePrioridad = !prioridadSeleccionada || prioridad === prioridadSeleccionada;
            var coincideArchivo = !estaArchivado || mostrarArchivados;

            if (coincideTexto && coincideVendedor && coincidePrioridad && coincideArchivo) {
                $(card).show();
            } else {
                $(card).hide();
            }
        });

        // Recalcular totales después de aplicar filtros
        recálculoTotalesColumnas();
    }

    // =============================================
    // CREACIÓN MANUAL DE LEADS
    // =============================================

    // Configurar etapa inicial según el botón "+" de la columna
    $(document).on('click', '.btnCrearLeadDesdeColumna', function() {
        var etapa = $(this).attr('data-etapa');
        $('#nuevoLeadEtapa').val(etapa);
        $('#modalAgregarLead').modal('show');
    });

    // Al hacer clic en el botón "Nuevo Lead" superior (que no tiene data-etapa), resetear select a la primera etapa
    $(document).on('click', '[data-target="#modalAgregarLead"]', function() {
        var primerValor = $('#nuevoLeadEtapa option:first').val();
        $('#nuevoLeadEtapa').val(primerValor);
    });

    // =============================================
    // EDICIÓN Y DETALLE DE LEADS (DOBLE CLICK EN ESCRITORIO, CLICK EN MÓVIL)
    // =============================================
 
    $(document).on('dblclick', '.crm-lead-card', function() {
        var idLead = $(this).attr('data-id');
        abrirModalEditarLead(idLead);
    });

    $(document).on('click', '.crm-lead-card', function() {
        if (window.matchMedia("(max-width: 768px)").matches) {
            var idLead = $(this).attr('data-id');
            abrirModalEditarLead(idLead);
        }
    });

    function abrirModalEditarLead(idLead) {
        $.ajax({
            url: 'ajax/crm.ajax.php',
            method: 'POST',
            data: {
                accion: 'obtenerLead',
                idLead: idLead
            },
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta) {
                    $('#editarLeadId').val(respuesta.id);
                    $('#editarLeadTitulo').val(respuesta.titulo);
                    $('#editarLeadValor').val(respuesta.valor_estimado);
                    $('#editarLeadPrioridad').val(respuesta.prioridad);
                    $('#editarLeadEtapa').val(respuesta.etapa);
                    $('#editarLeadVendedor').val(respuesta.id_vendedor);
                    $('#editarLeadFechaCierre').val(respuesta.fecha_cierre);
                    $('#editarLeadNotas').val(respuesta.notas);
                    
                    if ($.fn.select2) {
                        $('#editarLeadCliente').val(respuesta.id_cliente).trigger('change');
                    } else {
                        $('#editarLeadCliente').val(respuesta.id_cliente);
                    }

                    $('#modalEditarLead').modal('show');
                }
            },
            error: function() {
                console.error("Error al obtener datos del lead.");
            }
        });
    }

    // =============================================
    // ELIMINACIÓN DE LEADS
    // =============================================

    $(document).on('click', '.btnEliminarLeadCRM', function() {
        var idLead = $('#editarLeadId').val();

        swal({
            title: '¿Está seguro de eliminar esta oportunidad?',
            text: "¡No podrá revertir esta acción!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.value) {
                // Redirigir al controlador para procesar la eliminación
                window.location = "index.php?ruta=crm&idLeadEliminar=" + idLead;
            }
        });
    });

    // =============================================
    // GESTIÓN DE ETAPAS (COLUMNAS)
    // =============================================

    // Editar etapa
    $(document).on('click', '.btnEditarEtapaCRM', function() {
        var id = $(this).attr('data-id');
        var nombre = $(this).attr('data-nombre');
        var color = $(this).attr('data-color');
        var orden = $(this).attr('data-orden');

        $('#editarIdEtapa').val(id);
        $('#editarEtapaNombre').val(nombre);
        $('#editarEtapaColor').val(color);
        $('#editarEtapaOrden').val(orden);
        
        // Cerrar gestionar etapas temporalmente y abrir editar etapa
        $('#modalGestionarEtapas').modal('hide');
        setTimeout(function() {
            $('#modalEditarEtapaCRM').modal('show');
        }, 300);
    });

    // Regresar de Editar Etapa a Gestionar Etapas al salir
    $('#modalEditarEtapaCRM').on('hidden.bs.modal', function () {
        // Solo si no fue guardado por formulario
        setTimeout(function() {
            if (!$('#modalEditarLead').hasClass('in') && !$('#modalAgregarLead').hasClass('in')) {
                $('#modalGestionarEtapas').modal('show');
            }
        }, 100);
    });

    // Eliminar Etapa (Con validación Escenario A en Backend)
    $(document).on('click', '.btnEliminarEtapaCRM', function() {
        var idEtapa = $(this).attr('idEtapa');
        var nombreEtapa = $(this).attr('nombreEtapa');

        swal({
            title: '¿Está seguro de eliminar la etapa "' + nombreEtapa + '"?',
            text: "¡Solo se eliminará si no contiene leads activos!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.value) {
                // Redirigir al controlador
                window.location = "index.php?ruta=crm&idEtapaEliminar=" + idEtapa;
            }
        });
    });

});
