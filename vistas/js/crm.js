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
                var valorTexto = $(card).attr('data-valor') || $(card).find('.crm-lead-value').text().replace(/[^0-9\.]/g, '');
                var valor = parseFloat(valorTexto) || 0;
                totalDinero += valor;
                conteo++;
            });

            // Actualizar cabecera de columna
            $(columna).find('.crm-column-count').text(conteo);
        });

        // Calcular y actualizar las métricas superiores de forma dinámica
        var totalNegociacion = 0;
        var montoNegociacion = 0;
        var totalGanados = 0;
        var montoGanados = 0;
        var totalPerdidos = 0;
        var montoPerdidos = 0;
        var totalCalientes = 0;

        var textoBusqueda = $('#buscadorCRM').val().toLowerCase().trim();
        var textoBusquedaNumeros = textoBusqueda.replace(/\D/g, '');
        var origenSeleccionado = $('#filtroOrigenCRM').val();
        var prioridadSeleccionada = $('#filtroPrioridadCRM').val();

        $('.crm-lead-card').each(function() {
            var card = this;
            var cliente = ($(card).attr('data-cliente') || '').toLowerCase();
            var titulo = ($(card).attr('data-titulo') || '').toLowerCase();
            var telefonoRaw = ($(card).attr('data-telefono') || '').toLowerCase();
            var telefonoNumeros = telefonoRaw.replace(/\D/g, '');
            var prioridad = $(card).attr('data-prioridad');
            var origen = ($(card).attr('data-origen') || '').toLowerCase();
            var etapa = $(card).parent().attr('data-etapa');

            var idLead = ($(card).attr('data-id') || '').toString();
            var codigoLead = '#' + idLead;
            var busquedaSinEspacios = textoBusqueda.replace(/\s+/g, '');

            var coincideCodigo = false;
            if (textoBusqueda !== '') {
                if (textoBusqueda === idLead || codigoLead === busquedaSinEspacios) {
                    coincideCodigo = true;
                } else if (textoBusqueda.startsWith('#')) {
                    var numBuscado = textoBusqueda.replace(/[^0-9]/g, '');
                    if (numBuscado !== '' && (idLead === numBuscado || idLead.includes(numBuscado))) {
                        coincideCodigo = true;
                    }
                } else if (busquedaSinEspacios.startsWith('lead')) {
                    var numLead = busquedaSinEspacios.replace(/[^0-9]/g, '');
                    if (numLead !== '' && (idLead === numLead || idLead.includes(numLead))) {
                        coincideCodigo = true;
                    }
                } else {
                    coincideCodigo = idLead === textoBusqueda || idLead.includes(textoBusqueda);
                }
            }

            // Filtrado del lead para métricas (excluyendo el filtro de archivados de la visualización)
            var coincideTexto = coincideCodigo ||
                                cliente.includes(textoBusqueda) || 
                                titulo.includes(textoBusqueda) || 
                                telefonoRaw.includes(textoBusqueda) ||
                                (textoBusquedaNumeros !== '' && telefonoNumeros.includes(textoBusquedaNumeros));
            var coincidePrioridad = !prioridadSeleccionada || prioridad === prioridadSeleccionada;
            
            var esWhatsApp = origen.includes('whatsapp');
            var coincideOrigen = true;
            if (origenSeleccionado === 'whatsapp') {
                coincideOrigen = esWhatsApp;
            } else if (origenSeleccionado === 'manual') {
                coincideOrigen = !esWhatsApp;
            }

            if (coincideTexto && coincidePrioridad && coincideOrigen) {
                var valorTexto = $(card).attr('data-valor') || $(card).find('.crm-lead-value').text().replace(/[^0-9\.]/g, '');
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
                    if (prioridad === "caliente") {
                        totalCalientes++;
                    }
                }
            }
        });

        // Actualizar elementos en el DOM
        $('#metricTotalSeguimiento').text(totalNegociacion + ' Oportunidades');
        $('#metricTotalFacturado').text(totalGanados + ' Ganados');
        $('#metricTotalCalientes').text('🔥 ' + totalCalientes + ' Calientes');
        $('#metricTotalPerdidos').text(totalPerdidos + ' Perdidos');
        $('#metricMontoPerdidos').text('$ ' + montoPerdidos.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    // =============================================
    // FILTROS EN TIEMPO REAL
    // =============================================

    $('#buscadorCRM, #filtroOrigenCRM, #filtroPrioridadCRM, #toggleArchivadosCRM').on('input change', function() {
        filtrarTablero();
    });

    function filtrarTablero() {
        var textoBusqueda = $('#buscadorCRM').val().toLowerCase().trim();
        var textoBusquedaNumeros = textoBusqueda.replace(/\D/g, '');
        var origenSeleccionado = $('#filtroOrigenCRM').val();
        var prioridadSeleccionada = $('#filtroPrioridadCRM').val();
        var mostrarArchivados = $('#toggleArchivadosCRM').is(':checked');

        $('.crm-lead-card').each(function() {
            var card = this;
            var cliente = ($(card).attr('data-cliente') || '').toLowerCase();
            var titulo = ($(card).attr('data-titulo') || '').toLowerCase();
            var telefonoRaw = ($(card).attr('data-telefono') || '').toLowerCase();
            var telefonoNumeros = telefonoRaw.replace(/\D/g, '');
            var prioridad = $(card).attr('data-prioridad');
            var origen = ($(card).attr('data-origen') || '').toLowerCase();
            var estaArchivado = $(card).parent().attr('data-etapa') === "Facturado" || $(card).parent().attr('data-etapa') === "Perdido";

            var idLead = ($(card).attr('data-id') || '').toString();
            var codigoLead = '#' + idLead;
            var busquedaSinEspacios = textoBusqueda.replace(/\s+/g, '');

            var coincideCodigo = false;
            if (textoBusqueda !== '') {
                if (textoBusqueda === idLead || codigoLead === busquedaSinEspacios) {
                    coincideCodigo = true;
                } else if (textoBusqueda.startsWith('#')) {
                    var numBuscado = textoBusqueda.replace(/[^0-9]/g, '');
                    if (numBuscado !== '' && (idLead === numBuscado || idLead.includes(numBuscado))) {
                        coincideCodigo = true;
                    }
                } else if (busquedaSinEspacios.startsWith('lead')) {
                    var numLead = busquedaSinEspacios.replace(/[^0-9]/g, '');
                    if (numLead !== '' && (idLead === numLead || idLead.includes(numLead))) {
                        coincideCodigo = true;
                    }
                } else {
                    coincideCodigo = idLead === textoBusqueda || idLead.includes(textoBusqueda);
                }
            }

            // Condiciones
            var coincideTexto = coincideCodigo ||
                                cliente.includes(textoBusqueda) || 
                                titulo.includes(textoBusqueda) || 
                                telefonoRaw.includes(textoBusqueda) ||
                                (textoBusquedaNumeros !== '' && telefonoNumeros.includes(textoBusquedaNumeros));
            var coincidePrioridad = !prioridadSeleccionada || prioridad === prioridadSeleccionada;
            var coincideArchivo = !estaArchivado || mostrarArchivados;

            var esWhatsApp = origen.includes('whatsapp');
            var coincideOrigen = true;
            if (origenSeleccionado === 'whatsapp') {
                coincideOrigen = esWhatsApp;
            } else if (origenSeleccionado === 'manual') {
                coincideOrigen = !esWhatsApp;
            }

            if (coincideTexto && coincidePrioridad && coincideOrigen && coincideArchivo) {
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
                    var codigoLead = '#' + respuesta.id;
                    $('#editarLeadCodigo').val(codigoLead);
                    $('#badgeEditarLeadCodigo').text(codigoLead).show();
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

                    // Condición: Verificar si el campo origen contiene 'WhatsApp' (case-insensitive)
                    var origenTexto = respuesta.origen || '';
                    var esWhatsApp = /whatsapp/i.test(origenTexto);

                    if (esWhatsApp) {
                        $('#editarLeadOrigen').val(origenTexto);
                        
                        
                        
                        var resumenIA = respuesta.resumen_ia || '';

                        // Eliminar enlaces de imágenes enviados por WhatsApp
                        resumenIA = resumenIA.replace(
                            /\s*Imagen enviada:\s*https?:\/\/\S+/gi,
                            ''
                        ).trim();
                        
                        $('#editarLeadResumenIA').val(resumenIA);
                        
                        
                        // =============================================
                        // MOSTRAR IMÁGENES EN MINIATURA
                        // =============================================
                        
                        var contenedorImagenes = $('#listaImagenesLead');
                        
                        contenedorImagenes.empty();
                        
                        if (respuesta.imagenes && respuesta.imagenes.length > 0) {
                        
                            respuesta.imagenes.forEach(function(imagen, index) {
                        
                                // -----------------------------------------
                                // FORMATEAR FECHA Y HORA
                                // -----------------------------------------
                        
                                var fechaFormateada = '';
                        
                                if (imagen.fecha_creacion) {
                        
                                    var fecha = new Date(
                                        imagen.fecha_creacion.replace(' ', 'T')
                                    );
                        
                                    if (!isNaN(fecha.getTime())) {
                        
                                        var dia = String(
                                            fecha.getDate()
                                        ).padStart(2, '0');
                        
                                        var mes = String(
                                            fecha.getMonth() + 1
                                        ).padStart(2, '0');
                        
                                        var año = fecha.getFullYear();
                        
                                        var horas = String(
                                            fecha.getHours()
                                        ).padStart(2, '0');
                        
                                        var minutos = String(
                                            fecha.getMinutes()
                                        ).padStart(2, '0');
                        
                                        var segundos = String(
                                            fecha.getSeconds()
                                        ).padStart(2, '0');
                        
                                        fechaFormateada =
                                            dia + '/' +
                                            mes + '/' +
                                            año + ' ' +
                                            horas + ':' +
                                            minutos + ':' +
                                            segundos;
                        
                                    } else {
                        
                                        fechaFormateada =
                                            imagen.fecha_creacion;
                        
                                    }
                        
                                }
                        
                                // -----------------------------------------
                                // CONTENEDOR DE LA MINIATURA
                                // -----------------------------------------
                        
                                var tarjetaImagen = $('<div>')
                                    .addClass('imagen-lead-miniatura')
                                    .attr('data-indice', index)
                                    .css({
                                        'display': 'inline-block',
                                        'width': '120px',
                                        'margin-right': '10px',
                                        'margin-bottom': '10px',
                                        'vertical-align': 'top',
                                        'cursor': 'pointer',
                                        'text-align': 'center'
                                    });
                        
                                // -----------------------------------------
                                // MINIATURA
                                // -----------------------------------------
                        
                                var miniatura = $('<img>')
                                .attr('alt', 'Imagen ' + (index + 1))
                                .attr('referrerpolicy', 'no-referrer')
                                .css({
                                    'width': '120px',
                                    'height': '90px',
                                    'object-fit': 'cover',
                                    'border-radius': '6px',
                                    'border': '2px solid #c8e6c9',
                                    'display': 'block',
                                    'background-color': '#ffffff'
                                });
                            
                            miniatura.on('load', function() {
                            
                                console.log(
                                    'Imagen cargada correctamente:',
                                    imagen.url_imagen
                                );
                            
                            });
                            
                            miniatura.on('error', function() {
                            
                                console.error(
                                    'ERROR cargando imagen:',
                                    imagen.url_imagen
                                );
                            
                            });
                            
                            miniatura.attr('src', imagen.url_imagen);
                            
                            
                            // =============================================
                            // ABRIR GALERÍA AL HACER CLICK
                            // =============================================
                            
                            tarjetaImagen.on('click', function(e) {
                            
                                e.stopPropagation();
                            
                                abrirGaleriaImagenes(
                                    respuesta.imagenes,
                                    index
                                );
                            
                            });
                        
                                // -----------------------------------------
                                // NÚMERO DE IMAGEN
                                // -----------------------------------------
                        
                                var numeroImagen = $('<div>')
                                    .text('Imagen ' + (index + 1))
                                    .css({
                                        'font-size': '12px',
                                        'font-weight': '600',
                                        'color': '#2e7d32',
                                        'margin-top': '4px'
                                    });
                        
                                // -----------------------------------------
                                // FECHA Y HORA
                                // -----------------------------------------
                        
                                var fechaImagen = $('<div>')
                                    .text(fechaFormateada)
                                    .css({
                                        'font-size': '10px',
                                        'color': '#666',
                                        'margin-top': '2px',
                                        'line-height': '1.2'
                                    });
                        
                                // -----------------------------------------
                                // ARMAR MINIATURA
                                // -----------------------------------------
                        
                                tarjetaImagen.append(miniatura);
                                tarjetaImagen.append(numeroImagen);
                                tarjetaImagen.append(fechaImagen);
                        
                                contenedorImagenes.append(tarjetaImagen);
                        
                            });
                        
                        } else {
                        
                            contenedorImagenes.html(
                                '<span style="color:#777;">No hay imágenes enviadas.</span>'
                            );
                        
                        }


                        $('#editarLeadProductosInteres').val(respuesta.productos_interes || '');
                        $('#editarLeadFechaUltimaInteraccion').val(respuesta.fecha_ultima_interaccion || '');
                        $('#secWhatsAppLeadIA').slideDown(200);
                    } else {
                        $('#secWhatsAppLeadIA').hide();
                        $('#editarLeadOrigen').val(origenTexto);
                        $('#editarLeadResumenIA').val('');
                        $('#editarLeadProductosInteres').val('');
                        $('#editarLeadFechaUltimaInteraccion').val('');
                    }

                    $('#editarLeadTelefonoCliente').val(respuesta.telefono_cliente || '');
                    $('#editarLeadNombreCliente').val(respuesta.nombre_cliente || '');
                    $('#modalEditarLead').modal('show');
                }
            },
            error: function() {
                console.error("Error al obtener datos del lead.");
            }
        });
    }

    $('#modalEditarLead').on('hidden.bs.modal', function () {
        $('#badgeEditarLeadCodigo').text('').hide();
        $('#editarLeadCodigo').val('');
    });

    // Ver conversacion WhatsApp desde CRM
    $(document).on('click', '#btnVerConversacionWhatsAppLead', function() {
        var tel = $('#editarLeadTelefonoCliente').val();
        var nom = $('#editarLeadNombreCliente').val();
        if (!tel) {
            swal('Sin tel\u00e9fono', 'Este lead no tiene n\u00famero celular registrado.', 'warning');
            return;
        }
        $('#modalEditarLead').modal('hide');
        setTimeout(function() {
            window.location.href = '?ruta=whatsapp-chats&tel=' + encodeURIComponent(tel) + '&nom=' + encodeURIComponent(nom);
        }, 300);
    });

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
                swal({
                    title: 'Eliminando oportunidad',
                    text: 'Por favor espere mientras se procesa la información...',
                    type: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onBeforeOpen: () => {
                        swal.showLoading()
                    }
                });

                var datos = new FormData();
                datos.append("guardarEliminarLead", "ok");
                datos.append("idLeadEliminar", idLead);

                $.ajax({
                    url: "ajax/crm.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (respuesta) {
                        if (respuesta.status === "ok") {
                            swal({
                                type: "success",
                                title: "¡Éxito!",
                                text: respuesta.mensaje,
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                $("#modalEditarLead").modal("hide");
                                window.location.reload();
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "¡Error!",
                                text: respuesta.mensaje || "No se pudo eliminar la oportunidad.",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            });
                        }
                    },
                    error: function () {
                        swal({
                            type: "error",
                            title: "¡Error!",
                            text: "Ocurrió un problema de conexión al eliminar la oportunidad.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                });
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

    /*=============================================
    GUARDAR CREAR LEAD VÍA AJAX
    =============================================*/
    $(document).on("submit", "#formAgregarLead", function (e) {
        e.preventDefault();

        var form = this;
        var boton = $(form).find("button[type='submit']");
        boton.prop('disabled', true);
        var htmlOriginal = boton.html();
        boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        swal({
            title: 'Guardando oportunidad',
            text: 'Por favor espere mientras se procesa la información...',
            type: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                swal.showLoading()
            }
        });

        var datos = new FormData(form);
        datos.append("guardarCrearLead", "ok");

        $.ajax({
            url: "ajax/crm.ajax.php",
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
                        $("#modalAgregarLead").modal("hide");
                        form.reset();
                        window.location.reload();
                    });
                } else {
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: respuesta.mensaje || "No se pudo guardar la oportunidad.",
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
                    text: "Ocurrió un problema de conexión al guardar la oportunidad.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });

    /*=============================================
    GUARDAR EDITAR LEAD VÍA AJAX
    =============================================*/
    $(document).on("submit", "#formEditarLead", function (e) {
        e.preventDefault();

        var form = this;
        var boton = $(form).find("button[type='submit']");
        boton.prop('disabled', true);
        var htmlOriginal = boton.html();
        boton.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        swal({
            title: 'Actualizando oportunidad',
            text: 'Por favor espere mientras se procesa la información...',
            type: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                swal.showLoading()
            }
        });

        var datos = new FormData(form);
        datos.append("guardarEditarLead", "ok");

        $.ajax({
            url: "ajax/crm.ajax.php",
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
                        $("#modalEditarLead").modal("hide");
                        window.location.reload();
                    });
                } else {
                    swal({
                        type: "error",
                        title: "¡Error!",
                        text: respuesta.mensaje || "No se pudo actualizar la oportunidad.",
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
                    text: "Ocurrió un problema de conexión al actualizar la oportunidad.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });
    
    
    
    
    
    
    
    
    
    
    // =============================================
// GALERÍA DE IMÁGENES DEL LEAD
// =============================================

function abrirGaleriaImagenes(imagenes, indiceInicial) {

    var indiceActual = indiceInicial;

    // Eliminar modal anterior si existe
    $('#modalGaleriaImagenes').remove();

    // Crear modal
    var modal = $(`
        <div class="modal fade" id="modalGaleriaImagenes" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            <i class="fa fa-image"></i>
                            Imagen del cliente
                        </h5>

                        <button type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Cerrar">

                            <span aria-hidden="true">&times;</span>

                        </button>

                    </div>

                    <div class="modal-body text-center">

                        <div id="galeriaImagenContenedor"
                             style="position:relative;">

                            <img id="galeriaImagenPrincipal"
                                 src=""
                                 alt="Imagen"
                                 style="
                                    max-width:100%;
                                    max-height:65vh;
                                    object-fit:contain;
                                    border-radius:6px;
                                 ">

                        </div>

                        <div id="galeriaNombreImagen"
                             style="
                                margin-top:10px;
                                font-weight:600;
                                color:#2e7d32;
                             ">
                        </div>

                        <div id="galeriaFechaImagen"
                             style="
                                font-size:12px;
                                color:#777;
                                margin-top:3px;
                             ">
                        </div>

                    </div>

                    <div class="modal-footer"
                         style="justify-content:center;">

                        <button type="button"
                                class="btn btn-default"
                                id="galeriaAnterior">

                            <i class="fa fa-chevron-left"></i>
                            Anterior

                        </button>

                        <span id="galeriaContador"
                              style="
                                min-width:80px;
                                text-align:center;
                                padding-top:7px;
                              ">
                        </span>

                        <button type="button"
                                class="btn btn-default"
                                id="galeriaSiguiente">

                            Siguiente
                            <i class="fa fa-chevron-right"></i>

                        </button>

                    </div>

                </div>

            </div>
        </div>
    `);

    $('body').append(modal);


    // =============================================
    // MOSTRAR IMAGEN ACTUAL
    // =============================================

    function mostrarImagen() {

        var imagen = imagenes[indiceActual];

        $('#galeriaImagenPrincipal')
            .attr('src', imagen.url_imagen);

        $('#galeriaNombreImagen')
            .text(
                'Imagen ' +
                (indiceActual + 1)
            );

        var fechaImagen = '';

        if (imagen.fecha_creacion) {

            var fecha = new Date(
                imagen.fecha_creacion.replace(' ', 'T')
            );

            if (!isNaN(fecha.getTime())) {

                fechaImagen =
                    String(fecha.getDate()).padStart(2, '0') + '/' +
                    String(fecha.getMonth() + 1).padStart(2, '0') + '/' +
                    fecha.getFullYear() + ' ' +
                    String(fecha.getHours()).padStart(2, '0') + ':' +
                    String(fecha.getMinutes()).padStart(2, '0') + ':' +
                    String(fecha.getSeconds()).padStart(2, '0');

            } else {

                fechaImagen = imagen.fecha_creacion;

            }

        }

        $('#galeriaFechaImagen')
            .text(fechaImagen);

        $('#galeriaContador')
            .text(
                (indiceActual + 1) +
                ' / ' +
                imagenes.length
            );

        // Desactivar botones cuando corresponda
        $('#galeriaAnterior')
            .prop(
                'disabled',
                indiceActual === 0
            );

        $('#galeriaSiguiente')
            .prop(
                'disabled',
                indiceActual === imagenes.length - 1
            );
    }


    // =============================================
    // BOTÓN ANTERIOR
    // =============================================

    $('#galeriaAnterior').on('click', function() {

        if (indiceActual > 0) {

            indiceActual--;

            mostrarImagen();

        }

    });


    // =============================================
    // BOTÓN SIGUIENTE
    // =============================================

    $('#galeriaSiguiente').on('click', function() {

        if (indiceActual < imagenes.length - 1) {

            indiceActual++;

            mostrarImagen();

        }

    });


    // =============================================
    // TECLADO
    // =============================================

    $(document).off(
        'keydown.galeriaImagenes'
    );

    $(document).on(
        'keydown.galeriaImagenes',
        function(e) {

            if (!$('#modalGaleriaImagenes').hasClass('show')) {
                return;
            }

            if (e.key === 'ArrowLeft') {

                $('#galeriaAnterior').click();

            }

            if (e.key === 'ArrowRight') {

                $('#galeriaSiguiente').click();

            }

        }
    );


    // =============================================
    // LIMPIAR EVENTO AL CERRAR
    // =============================================

    $('#modalGaleriaImagenes').on(
        'hidden.bs.modal',
        function() {

            $(document).off(
                'keydown.galeriaImagenes'
            );

            $(this).remove();

        }
    );


    // Mostrar primera imagen seleccionada
    mostrarImagen();

    // Abrir modal
    $('#modalGaleriaImagenes').modal('show');

}

    // =============================================
    // BOTÓN EDITAR CONTACTO (DESDE MODAL LEAD)
    // =============================================
    $(document).on("click", "#btnEditarContactoLead, .btn-editar-contacto-lead", function() {
        var origen = $("#editarLeadOrigen").val() || "";
        if (!origen.trim()) {
            swal({
                type: "warning",
                title: "Cliente no encontrado",
                text: "No se encontró un número de teléfono registrado en el origen del lead.",
                confirmButtonText: "Cerrar"
            });
            return;
        }

        $.ajax({
            url: "ajax/clientes.ajax.php",
            method: "POST",
            data: {
                accion: "buscarClientePorTelefono",
                telefono: origen
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta && respuesta.status === "ok" && respuesta.idCliente) {
                    window.location = "index.php?ruta=cliente-detalle&id=" + respuesta.idCliente;
                } else {
                    swal({
                        type: "warning",
                        title: "Cliente no encontrado",
                        text: "El cliente no ha sido encontrado",
                        confirmButtonText: "Cerrar"
                    });
                }
            },
            error: function() {
                swal({
                    type: "error",
                    title: "Error",
                    text: "Ocurrió un error al buscar el cliente.",
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });

});
