$(document).ready(function() {

    var idChatActivo = null;
    var telefonoActivo = null;
    var intervaloPolling = null;
    var ultimoConteoMensajes = 0;

    // =============================================
    // INICIALIZACIÓN
    // =============================================

    // Leer parametros CRM de la URL antes de cargar chats
    var urlParamsCRM = new URLSearchParams(window.location.search);
    var telFromCRM   = urlParamsCRM.get('tel') || null;
    var nomFromCRM   = urlParamsCRM.get('nom') || '';

    // Cargar chats; si venimos del CRM, auto-abrir la conversacion en el callback
    cargarChats('', function() {
        if (telFromCRM) {
            autoAbrirConversacionCRM(telFromCRM, nomFromCRM);
        }
    });

    // Iniciar sondeo en tiempo real cada 3.5 segundos
    intervaloPolling = setInterval(function() {
        refrescarEnTiempoReal();
    }, 3500);

    // =============================================
    // CARGAR LISTA DE CHATS
    // =============================================
    function cargarChats(busqueda, callback) {
        $.ajax({
            url: "ajax/whatsapp-chats.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerChats",
                busqueda: busqueda || ""
            },
            dataType: "json",
            success: function(chats) {
                var contenedor = $("#listaChatsWhatsApp");
                var total = chats ? chats.length : 0;
                $("#contadorChatsTotal").text(total + " conversación(es)");

                if (!chats || chats.length === 0) {
                    contenedor.html(
                        '<div class="text-center" style="padding: 40px 20px; color: #8696a0;">' +
                        '  <i class="fa fa-comments-o fa-2x" style="opacity: 0.5;"></i>' +
                        '  <p style="margin-top: 10px; font-size: 13px;">No hay conversaciones registradas.</p>' +
                        '  <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#modalNuevoChatWhatsApp" style="border-radius: 12px; margin-top: 5px;">+ Iniciar primer chat</button>' +
                        '</div>'
                    );
                    return;
                }

                var html = "";
                chats.forEach(function(c) {
                    var isActive = (idChatActivo && idChatActivo == c.id) ? " active" : "";
                    var iniciales = obtenerIniciales(c.nombre || c.telefono);
                    var horaFormateada = formatearHoraFecha(c.fecha_ultimo_mensaje || c.created_at);
                    var rawMsg = c.ultimo_mensaje || "Sin mensajes";
                    var snippetMsg = rawMsg;
                    var iconoPrefijo = "";

                    if (rawMsg.indexOf("audioMessage") !== -1 || rawMsg.indexOf("Mensaje de voz") !== -1 || rawMsg.indexOf("Audio") !== -1) {
                        iconoPrefijo = '<i class="fa fa-microphone text-success" style="margin-right: 3px;"></i> ';
                        snippetMsg = "Audio";
                    } else if (rawMsg.indexOf("imageMessage") !== -1 || rawMsg.indexOf("Foto") !== -1) {
                        iconoPrefijo = '<i class="fa fa-camera text-primary" style="margin-right: 3px;"></i> ';
                        snippetMsg = "Foto";
                    } else if (rawMsg.indexOf("videoMessage") !== -1 || rawMsg.indexOf("Video") !== -1) {
                        iconoPrefijo = '<i class="fa fa-video-camera text-info" style="margin-right: 3px;"></i> ';
                        snippetMsg = "Video";
                    } else if (rawMsg.indexOf("documentMessage") !== -1 || rawMsg.indexOf("Documento") !== -1) {
                        iconoPrefijo = '<i class="fa fa-file-text-o text-danger" style="margin-right: 3px;"></i> ';
                        snippetMsg = "Documento";
                    }

                    if (snippetMsg.length > 34) {
                        snippetMsg = snippetMsg.substring(0, 34) + "...";
                    }

                    var badgeUnread = "";
                    if (c.no_leidos && parseInt(c.no_leidos) > 0) {
                        badgeUnread = '<span class="whatsapp-badge-unread">' + c.no_leidos + '</span>';
                    }

                    // Si este chat está activo en pantalla, mantener su nombre actualizado si cambió o mejoró
                    if (idChatActivo && idChatActivo == c.id) {
                        var nombreNuevoHeader = c.nombre || ("+" + c.telefono);
                        if (nombreNuevoHeader && nombreNuevoHeader !== "Usuario Whatsapp") {
                            $("#headerChatNombre").text(nombreNuevoHeader);
                            $("#headerChatAvatar").text(iniciales);
                        }
                    }

                    html += 
                    '<div class="whatsapp-chat-item' + isActive + '" data-id="' + c.id + '" data-tel="' + c.telefono + '" data-nom="' + escapeHtml(c.nombre || c.telefono) + '">' +
                    '  <div class="whatsapp-item-avatar">' + iniciales + '</div>' +
                    '  <div class="whatsapp-item-info">' +
                    '    <div class="whatsapp-item-name">' +
                    '      <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">' + escapeHtml(c.nombre || '+' + c.telefono) + '</span>' +
                    '      <span class="whatsapp-item-time">' + horaFormateada + '</span>' +
                    '    </div>' +
                    '    <div class="whatsapp-item-lastmsg">' +
                    '      <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;">' + iconoPrefijo + escapeHtml(snippetMsg) + '</span>' +
                    '      ' + badgeUnread +
                    '    </div>' +
                    '  </div>' +
                    '</div>';
                });

                contenedor.html(html);

                if (typeof callback === "function") {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar chats:", error);
                var contenedor = $("#listaChatsWhatsApp");
                if (contenedor.find(".fa-spinner").length > 0) {
                    contenedor.html(
                        '<div class="text-center" style="padding: 40px 20px; color: #8696a0;">' +
                        '  <i class="fa fa-comments-o fa-2x" style="opacity: 0.5;"></i>' +
                        '  <p style="margin-top: 10px; font-size: 13px;">No se pudieron cargar las conversaciones.</p>' +
                        '  <button class="btn btn-xs btn-default" id="btnReintentarCargarChats" style="border-radius: 12px; margin-top: 5px;"><i class="fa fa-refresh"></i> Reintentar</button>' +
                        '</div>'
                    );
                }
            }
        });
    }

    $(document).on("click", "#btnReintentarCargarChats", function() {
        $("#listaChatsWhatsApp").html(
            '<div class="text-center" style="padding: 40px 20px; color: #8696a0;">' +
            '  <i class="fa fa-spinner fa-spin fa-2x"></i>' +
            '  <p style="margin-top: 10px; font-size: 13px;">Cargando conversaciones...</p>' +
            '</div>'
        );
        cargarChats();
    });

    // =============================================
    // SELECCIONAR Y ABRIR UN CHAT
    // =============================================
    $(document).on("click", ".whatsapp-chat-item", function() {
        var id = $(this).attr("data-id");
        var tel = $(this).attr("data-tel");
        var nom = $(this).attr("data-nom");

        abrirConversacion(id, tel, nom);
    });

    function abrirConversacion(id, tel, nom) {
        idChatActivo = id;
        telefonoActivo = tel;

        // Actualizar UI activa en sidebar
        $(".whatsapp-chat-item").removeClass("active");
        $('.whatsapp-chat-item[data-id="' + id + '"]').addClass("active");
        $('.whatsapp-chat-item[data-id="' + id + '"] .whatsapp-badge-unread').remove();

        // Actualizar campos ocultos
        $("#activoIdChat").val(id);
        $("#activoTelefonoChat").val(tel);

        // Sanitizar nombre para que nunca muestre 'Usuario Whatsapp'
        var nombreLimpio = nom;
        if (!nombreLimpio || nombreLimpio === "Usuario Whatsapp" || nombreLimpio === "null") {
            nombreLimpio = "+" + tel;
        }

        // Actualizar encabezado
        $("#headerChatAvatar").text(obtenerIniciales(nombreLimpio || tel));
        $("#headerChatNombre").text(nombreLimpio);
        $("#headerChatTelefono").text("+" + tel);

        // Mostrar panel activo y ocultar vacío
        $("#panelChatVacio").hide();
        $("#panelChatActivo").css("display", "flex");

        // Cargar mensajes con autoscroll
        cargarMensajes(id, true);

        // Enfocar input
        setTimeout(function() {
            $("#inputMensajeWhatsApp").focus();
        }, 150);
    }

    // =============================================
    // CARGAR MENSAJES DE LA CONVERSACIÓN ACTIVA
    // =============================================
    function cargarMensajes(idChat, autoScroll) {
        if (!idChat) return;

        $.ajax({
            url: "ajax/whatsapp-chats.ajax.php",
            method: "POST",
            data: {
                accion: "obtenerMensajes",
                idChat: idChat
            },
            dataType: "json",
            success: function(mensajes) {
                var contenedor = $("#contenedorMensajesChat");

                if (!mensajes || mensajes.length === 0) {
                    contenedor.html(
                        '<div class="text-center" style="padding: 40px 20px; color: #8696a0;">' +
                        '  <span class="whatsapp-date-divider">Inicio de la conversación</span>' +
                        '  <p style="margin-top: 15px; font-size: 13px;">No hay mensajes registrados aún en este chat.</p>' +
                        '</div>'
                    );
                    ultimoConteoMensajes = 0;
                    return;
                }

                // Evitar redibujar innecesariamente si no hubo cambios
                if (!autoScroll && mensajes.length === ultimoConteoMensajes) {
                    return;
                }

                ultimoConteoMensajes = mensajes.length;

                var html = "";
                var fechaAnterior = "";

                mensajes.forEach(function(m) {
                    // Separador de fecha
                    var fechaHoraStr = (m.fecha_hora && typeof m.fecha_hora === 'string') ? m.fecha_hora : '';
                    var fechaMsg = fechaHoraStr.indexOf(" ") !== -1 ? fechaHoraStr.split(" ")[0] : fechaHoraStr;
                    if (fechaMsg && fechaMsg !== fechaAnterior) {
                        html += '<div class="whatsapp-date-divider">' + formatearFechaCabecera(fechaMsg) + '</div>';
                        fechaAnterior = fechaMsg;
                    }

                    var isOutbound = (m.direccion === "outbound");
                    var bubbleClass = isOutbound ? "whatsapp-bubble-outbound" : "whatsapp-bubble-inbound";
                    var hora = formatearHora(fechaHoraStr);

                    var checks = isOutbound ? ' <i class="fa fa-check text-success" style="font-size: 10px; margin-left: 2px;"></i>' : '';
                    var remitenteEtiqueta = "";
                    if (!isOutbound && m.remitente) {
                        remitenteEtiqueta = '<div class="whatsapp-bubble-sender">' + escapeHtml(m.remitente) + '</div>';
                    } else if (isOutbound && m.remitente === "agente_ia") {
                        remitenteEtiqueta = '<div class="whatsapp-bubble-sender" style="color: #25D366;"><i class="fa fa-magic"></i> Agente IA</div>';
                    }

                    // Análisis y renderizado de contenido multimedia (Audio, Imagen, Video, Documento)
                    var mediaHtml = "";
                    var textoHtml = "";
                    var textoMsg = m.mensaje || "";
                    var tipoMsg = (m.tipo || "text").toLowerCase();
                    var mediaUrl = (m.media_url || "").trim();

                    // Detectar tipos de archivo
                    var esAudio = (tipoMsg === "audio" || tipoMsg === "audiomessage" || tipoMsg === "voice" || 
                                   textoMsg.indexOf("audioMessage") !== -1 || textoMsg.indexOf("Mensaje de voz") !== -1 || 
                                   (mediaUrl && mediaUrl.match(/\.(ogg|opus|mp3|wav|m4a|aac)($|\?)/i)) || 
                                   (mediaUrl && mediaUrl.indexOf("data:audio/") === 0));

                    var esImagen = (tipoMsg === "image" || tipoMsg === "imagemessage" || 
                                    textoMsg.indexOf("imageMessage") !== -1 || 
                                    (mediaUrl && mediaUrl.match(/\.(jpg|jpeg|png|gif|webp|bmp)($|\?)/i)) || 
                                    (mediaUrl && mediaUrl.indexOf("data:image/") === 0));

                    var esVideo = (tipoMsg === "video" || tipoMsg === "videomessage" || 
                                   textoMsg.indexOf("videoMessage") !== -1 || 
                                   (mediaUrl && mediaUrl.match(/\.(mp4|webm|avi|mov)($|\?)/i)) || 
                                   (mediaUrl && mediaUrl.indexOf("data:video/") === 0));

                    var esDocumento = (tipoMsg === "document" || tipoMsg === "documentmessage" || 
                                       textoMsg.indexOf("documentMessage") !== -1 || 
                                       (mediaUrl && mediaUrl.match(/\.(pdf|docx?|xlsx?|txt|zip)($|\?)/i)) || 
                                       (mediaUrl && mediaUrl.indexOf("data:application/") === 0));

                    // Función auxiliar para normalizar rutas de medios
                    var normalizarRutaMedia = function(url) {
                        if (!url) return "";
                        if (url.match(/^(https?:|\/\/|data:)/i)) return url;
                        return '/' + url.replace(/^\/+/, '');
                    };

                    if (esAudio) {
                        if (mediaUrl) {
                            var audioSrc = normalizarRutaMedia(mediaUrl);
                            mediaHtml = 
                            '<div class="whatsapp-audio-box" style="display: flex; align-items: center; gap: 10px; padding: 4px 2px; min-width: 250px; max-width: 320px;">' +
                            '  <div style="width: 36px; height: 36px; border-radius: 50%; background: #25D366; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">' +
                            '    <i class="fa fa-microphone"></i>' +
                            '  </div>' +
                            '  <div style="flex: 1;">' +
                            '    <audio controls preload="metadata" style="width: 100%; height: 32px; outline: none;">' +
                            '      <source src="' + escapeHtml(audioSrc) + '">' +
                            '      Tu navegador no soporta reproducción de audio.' +
                            '    </audio>' +
                            '  </div>' +
                            '</div>';
                        } else {
                            mediaHtml = 
                            '<div class="whatsapp-media-card" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: rgba(0,0,0,0.04); border-radius: 8px; border-left: 3px solid #25D366; min-width: 200px;">' +
                            '  <div style="width: 32px; height: 32px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 14px;">' +
                            '    <i class="fa fa-microphone"></i>' +
                            '  </div>' +
                            '  <div>' +
                            '    <div style="font-weight: 600; font-size: 13px; color: #1e293b;">Mensaje de voz</div>' +
                            '    <small style="color: #64748b; font-size: 11px;">Audio recibido vía WhatsApp</small>' +
                            '  </div>' +
                            '</div>';
                        }
                        textoHtml = '';
                    } else if (esImagen) {
                        if (mediaUrl) {
                            var imgSrc = normalizarRutaMedia(mediaUrl);
                            mediaHtml = 
                            '<div style="margin-bottom: 6px;">' +
                            '  <a href="' + escapeHtml(imgSrc) + '" target="_blank" title="Clic para ver en tamaño completo">' +
                            '    <img src="' + escapeHtml(imgSrc) + '" class="img-responsive img-rounded img-whatsapp-preview" style="max-height: 250px; max-width: 100%; border-radius: 8px; cursor: pointer; border: 1px solid rgba(0,0,0,0.08); object-fit: cover;" title="Clic para ampliar" onerror="this.onerror=null; this.parentElement.innerHTML=\'<div class=\\\'alert alert-warning btn-xs\\\'>Error al cargar imagen</div>\';">' +
                            '  </a>' +
                            '</div>';
                        } else {
                            mediaHtml = 
                            '<div class="whatsapp-media-card" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: rgba(0,0,0,0.04); border-radius: 8px; border-left: 3px solid #0284c7; min-width: 200px;">' +
                            '  <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px;">' +
                            '    <i class="fa fa-camera"></i>' +
                            '  </div>' +
                            '  <div>' +
                            '    <div style="font-weight: 600; font-size: 13px; color: #1e293b;">Foto / Imagen</div>' +
                            '    <small style="color: #64748b; font-size: 11px;">Recibida vía WhatsApp</small>' +
                            '  </div>' +
                            '</div>';
                        }
                        var textoLimpio = $.trim(textoMsg).toLowerCase();
                        var esCaptionGenerico = (!textoMsg || 
                            textoLimpio === "foto" || 
                            textoLimpio === "📷 foto" || 
                            textoLimpio === "???? foto" || 
                            textoLimpio.indexOf("????") !== -1 || 
                            textoLimpio.indexOf("imagemessage") !== -1 || 
                            textoLimpio.indexOf("archivo:") !== -1 || 
                            textoLimpio === "[foto]");

                        if (!esCaptionGenerico) {
                            textoHtml = '<div style="margin-top: 4px; font-size: 13px;">' + nl2br(escapeHtml(textoMsg)) + '</div>';
                        } else {
                            textoHtml = '';
                        }
                    } else if (esVideo) {
                        if (mediaUrl) {
                            var vidSrc = normalizarRutaMedia(mediaUrl);
                            mediaHtml = 
                            '<div style="margin-bottom: 6px; max-width: 320px;">' +
                            '  <video controls style="width: 100%; max-height: 240px; border-radius: 8px; background: #000;">' +
                            '    <source src="' + escapeHtml(vidSrc) + '">' +
                            '  </video>' +
                            '</div>';
                        } else {
                            mediaHtml = 
                            '<div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: rgba(0,0,0,0.04); border-radius: 8px; color: #54656f; font-size: 13px;">' +
                            '  <i class="fa fa-video-camera" style="font-size: 18px; color: #6366f1;"></i>' +
                            '  <span style="font-weight: 500;">Video</span>' +
                            '</div>';
                        }
                        if (textoMsg && textoMsg.indexOf("videoMessage") === -1) {
                            textoHtml = '<div>' + nl2br(escapeHtml(textoMsg)) + '</div>';
                        } else {
                            textoHtml = '';
                        }
                    } else if (esDocumento) {
                        if (mediaUrl) {
                            var docSrc = normalizarRutaMedia(mediaUrl);
                            mediaHtml = 
                            '<div style="margin-bottom: 6px;">' +
                            '  <a href="' + escapeHtml(docSrc) + '" target="_blank" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: rgba(0,0,0,0.05); border-radius: 8px; text-decoration: none; color: #111b21;">' +
                            '    <i class="fa fa-file-text-o" style="font-size: 22px; color: #ef4444;"></i>' +
                            '    <div>' +
                            '      <div style="font-weight: 600; font-size: 13px;">Archivo adjunto</div>' +
                            '      <small style="color: #64748b; font-size: 11px;">Clic para abrir o descargar</small>' +
                            '    </div>' +
                            '  </a>' +
                            '</div>';
                        } else {
                            mediaHtml = 
                            '<div style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: rgba(0,0,0,0.04); border-radius: 8px; color: #54656f; font-size: 13px;">' +
                            '  <i class="fa fa-file-text-o" style="font-size: 18px; color: #ef4444;"></i>' +
                            '  <span style="font-weight: 500;">Documento adjunto</span>' +
                            '</div>';
                        }
                        if (textoMsg && textoMsg.indexOf("documentMessage") === -1) {
                            textoHtml = '<div>' + nl2br(escapeHtml(textoMsg)) + '</div>';
                        } else {
                            textoHtml = '';
                        }
                    } else {
                        // Mensaje de texto estándar
                        if (mediaUrl) {
                            var attachSrc = normalizarRutaMedia(mediaUrl);
                            mediaHtml = 
                            '<div style="margin-bottom: 6px;">' +
                            '  <a href="' + escapeHtml(attachSrc) + '" target="_blank" class="btn btn-default btn-xs" style="border-radius: 12px;">' +
                            '    <i class="fa fa-paperclip"></i> Ver adjunto' +
                            '  </a>' +
                            '</div>';
                        }
                        textoHtml = '<div>' + nl2br(escapeHtml(textoMsg)) + '</div>';
                    }

                    html += 
                    '<div class="whatsapp-message-bubble ' + bubbleClass + '">' +
                    '  ' + remitenteEtiqueta +
                    '  ' + mediaHtml +
                    '  ' + textoHtml +
                    '  <div class="whatsapp-bubble-time">' + hora + checks + '</div>' +
                    '</div>';
                });

                contenedor.html(html);

                if (autoScroll) {
                    scrollAlFinal();
                }
            },
            error: function() {
                console.error("Error al obtener mensajes.");
            }
        });
    }

    // =============================================
    // ENVIAR MENSAJE
    // =============================================
    $("#btnEnviarMensajeWhatsApp").on("click", function() {
        enviarMensajeActual();
    });

    $("#inputMensajeWhatsApp").on("keydown", function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            enviarMensajeActual();
        }
    });

    function enviarMensajeActual() {
        var idChat = $("#activoIdChat").val();
        var tel = $("#activoTelefonoChat").val();
        var mensaje = $.trim($("#inputMensajeWhatsApp").val());

        if (!idChat || !tel || mensaje === "") {
            return;
        }

        // Limpiar input inmediatamente
        $("#inputMensajeWhatsApp").val("").css("height", "auto");

        // Añadir visualmente la burbuja optimista
        var ahora = new Date();
        var horaStr = ahora.getHours() + ":" + (ahora.getMinutes() < 10 ? '0' : '') + ahora.getMinutes();
        var bubbleTemp = 
        '<div class="whatsapp-message-bubble whatsapp-bubble-outbound temp-sending">' +
        '  <div>' + nl2br(escapeHtml(mensaje)) + '</div>' +
        '  <div class="whatsapp-bubble-time">' + horaStr + ' <i class="fa fa-clock-o text-muted" style="font-size: 10px;"></i></div>' +
        '</div>';
        $("#contenedorMensajesChat").append(bubbleTemp);
        scrollAlFinal();

        // Enviar vía AJAX al controlador y WaFloW
        $.ajax({
            url: "ajax/whatsapp-chats.ajax.php",
            method: "POST",
            data: {
                accion: "enviarMensaje",
                idChat: idChat,
                telefono: tel,
                mensaje: mensaje
            },
            dataType: "json",
            success: function(resp) {
                if (resp && resp.success) {
                    cargarMensajes(idChat, true);
                    cargarChats();
                } else {
                    swal({
                        title: "Aviso de Envío",
                        text: (resp && resp.error) ? resp.error : "El mensaje se registró localmente, pero verifica la conexión con WaFloW.",
                        type: "warning"
                    });
                    cargarMensajes(idChat, true);
                }
            },
            error: function() {
                swal("Error", "No se pudo comunicar con el servidor.", "error");
            }
        });
    }

    // =============================================
    // REFRESCAR EN TIEMPO REAL
    // =============================================
    function refrescarEnTiempoReal() {
        var busqueda = $.trim($("#buscarChatInput").val());
        // Solo refrescar lista si el usuario no está tecleando una búsqueda activa
        if (busqueda === "") {
            cargarChats();
        }

        // Si hay un chat abierto, consultar nuevos mensajes silenciosamente
        if (idChatActivo) {
            cargarMensajes(idChatActivo, false);
        }
    }

    $("#btnRefrescarMensajes").on("click", function() {
        if (idChatActivo) {
            cargarMensajes(idChatActivo, true);
        }
    });

    // =============================================
    // SINCRONIZAR LEADS DEL CRM
    // =============================================
    $("#btnSincronizarCRM").on("click", function() {
        var $btn = $(this);
        var $icon = $("#iconoSyncCRM");
        $btn.prop("disabled", true);
        $icon.addClass("fa-spin");

        $.ajax({
            url: "ajax/whatsapp-chats.ajax.php",
            method: "POST",
            data: { accion: "sincronizarCRM" },
            dataType: "json",
            success: function(resp) {
                $btn.prop("disabled", false);
                $icon.removeClass("fa-spin");
                cargarChats();
                if (resp && resp.success) {
                    swal({
                        title: "¡CRM Sincronizado!",
                        text: "Se procesaron los contactos del CRM. Nuevos chats agregados: " + (resp.insertados || 0),
                        type: "success",
                        timer: 2500,
                        showConfirmButton: false
                    });
                } else {
                    swal("Aviso", (resp && resp.error) ? resp.error : "Sincronización completada.", "info");
                }
            },
            error: function() {
                $btn.prop("disabled", false);
                $icon.removeClass("fa-spin");
                swal("Error", "No se pudo sincronizar con el CRM.", "error");
            }
        });
    });

    // =============================================
    // BÚSQUEDA DE CONTACTOS EN VIVO
    // =============================================
    var debounceBusqueda = null;
    $("#buscarChatInput").on("keyup", function() {
        clearTimeout(debounceBusqueda);
        var q = $(this).val();
        debounceBusqueda = setTimeout(function() {
            cargarChats(q);
        }, 300);
    });

    // =============================================
    // CREAR NUEVO CHAT MODAL
    // =============================================
    $("#btnCrearNuevoChat").on("click", function() {
        var tel = $.trim($("#nuevoChatTelefono").val());
        var nom = $.trim($("#nuevoChatNombre").val());
        var primerMsg = $.trim($("#nuevoChatPrimerMensaje").val());

        if (tel === "") {
            swal("Atención", "Por favor ingresa un número de teléfono.", "warning");
            return;
        }

        $.ajax({
            url: "ajax/whatsapp-chats.ajax.php",
            method: "POST",
            data: {
                accion: "crearChat",
                telefono: tel,
                nombre: nom
            },
            dataType: "json",
            success: function(chat) {
                $("#modalNuevoChatWhatsApp").modal("hide");
                $("#nuevoChatTelefono").val("");
                $("#nuevoChatNombre").val("");
                $("#nuevoChatPrimerMensaje").val("");

                if (chat && chat.id) {
                    cargarChats("", function() {
                        abrirConversacion(chat.id, chat.telefono, chat.nombre);
                        if (primerMsg !== "") {
                            $("#inputMensajeWhatsApp").val(primerMsg);
                            enviarMensajeActual();
                        }
                    });
                }
            },
            error: function() {
                swal("Error", "Ocurrió un error al crear la conversación.", "error");
            }
        });
    });

    // =============================================
    // VISTA PREVIA DE IMAGEN
    // =============================================
    $(document).on("click", ".img-whatsapp-preview", function() {
        var src = $(this).attr("src");
        $("#imgZoomWhatsApp").attr("src", src);
        $("#modalZoomImagenWhatsApp").modal("show");
    });

    // =============================================
    // HELPERS & FORMATEADORES
    // =============================================
    function scrollAlFinal() {
        var el = document.getElementById("contenedorMensajesChat");
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    }

    function obtenerIniciales(str) {
        if (!str || str === "Usuario Whatsapp") return "WA";
        var limpio = str.replace(/[^a-zA-Z0-9\s]/g, '').trim();
        var partes = limpio.split(/\s+/).filter(Boolean);
        if (partes.length >= 2) {
            return (partes[0].charAt(0) + partes[1].charAt(0)).toUpperCase();
        }
        if (partes.length === 1 && partes[0].length >= 2) {
            return partes[0].substring(0, 2).toUpperCase();
        }
        return (limpio.substring(0, 2) || "WA").toUpperCase();
    }

    function formatearHora(fechaStr) {
        if (!fechaStr || typeof fechaStr !== 'string') return "";
        var d = new Date(fechaStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return "";
        var h = d.getHours();
        var m = d.getMinutes();
        var ampm = h >= 12 ? 'pm' : 'am';
        h = h % 12;
        h = h ? h : 12;
        m = m < 10 ? '0' + m : m;
        return h + ':' + m + ' ' + ampm;
    }

    function formatearHoraFecha(fechaStr) {
        if (!fechaStr) return "";
        var d = new Date(fechaStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return "";
        var ahora = new Date();
        var diffDias = Math.floor((ahora - d) / (1000 * 60 * 60 * 24));

        if (diffDias === 0) {
            return formatearHora(fechaStr);
        } else if (diffDias === 1) {
            return "Ayer";
        } else {
            return d.getDate() + "/" + (d.getMonth() + 1);
        }
    }

    function formatearFechaCabecera(fechaSql) {
        if (!fechaSql) return "";
        var partes = fechaSql.split("-");
        if (partes.length !== 3) return fechaSql;

        var d = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
        var ahora = new Date();
        ahora.setHours(0,0,0,0);
        d.setHours(0,0,0,0);

        var diffTime = ahora.getTime() - d.getTime();
        var diffDias = Math.round(diffTime / (1000 * 3600 * 24));

        if (diffDias === 0) return "HOY";
        if (diffDias === 1) return "AYER";
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }

    function escapeHtml(text) {
        if (!text) return "";
        return $('<div>').text(text).html();
    }

    function nl2br(str) {
        if (!str) return "";
        return str.replace(/\n/g, '<br>');
    }

    // =============================================
    // AUTO-ABRIR CONVERSACION DESDE CRM
    // =============================================
    function autoAbrirConversacionCRM(tel, nom) {
        // Normalizar: solo digitos, comparar ultimos 10
        var telNorm = tel.replace(/\D/g, '');
        var last10  = telNorm.length >= 10 ? telNorm.slice(-10) : telNorm;

        var chatEncontrado = null;

        $('.whatsapp-chat-item').each(function() {
            var itemTel = $(this).attr('data-tel') || '';
            var itemTelNorm = itemTel.replace(/\D/g, '');
            var itemLast10  = itemTelNorm.length >= 10 ? itemTelNorm.slice(-10) : itemTelNorm;
            if (itemLast10 === last10) {
                chatEncontrado = $(this);
                return false; // break each
            }
        });

        if (chatEncontrado && chatEncontrado.length > 0) {
            var chatId  = chatEncontrado.attr('data-id');
            var chatTel = chatEncontrado.attr('data-tel');
            var chatNom = chatEncontrado.attr('data-nom');
            abrirConversacion(chatId, chatTel, chatNom);
        } else {
            // Intentar buscar/crear en BD por telefono
            $.ajax({
                url: 'ajax/whatsapp-chats.ajax.php',
                method: 'POST',
                data: { accion: 'crearChat', telefono: tel, nombre: nom },
                dataType: 'json',
                success: function(chat) {
                    if (chat && chat.id) {
                        cargarChats('', function() {
                            abrirConversacion(chat.id, chat.telefono, chat.nombre || nom);
                        });
                    } else {
                        // No existe ninguna conversacion: mostrar modal informativo
                        mostrarModalSinConversacion(tel, nom);
                    }
                },
                error: function() {
                    mostrarModalSinConversacion(tel, nom);
                }
            });
        }
    }

    function mostrarModalSinConversacion(tel, nom) {
        var nombreMostrar = nom || tel;
        var html =
            '<div class="modal fade" id="modalSinConversacionWA" tabindex="-1" role="dialog" aria-hidden="true">' +
            '  <div class="modal-dialog modal-sm" role="document">' +
            '    <div class="modal-content" style="border-radius:12px;overflow:hidden;">' +
            '      <div class="modal-header" style="background:#25D366;padding:14px 20px;border-bottom:none;">' +
            '        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color:#fff;opacity:1;"><span>&times;</span></button>' +
            '        <h4 class="modal-title" style="color:#fff;font-weight:700;"><i class="fa fa-whatsapp"></i> Sin conversacion</h4>' +
            '      </div>' +
            '      <div class="modal-body" style="text-align:center;padding:28px 20px;">' +
            '        <i class="fa fa-comment-o" style="font-size:48px;color:#ccc;margin-bottom:16px;"></i>' +
            '        <p style="font-size:15px;color:#444;margin-bottom:6px;">No se encontro ninguna conversacion de WhatsApp para:</p>' +
            '        <p style="font-weight:700;font-size:16px;color:#25D366;margin-bottom:4px;">' + escapeHtml(nombreMostrar) + '</p>' +
            '        <p style="color:#888;font-size:13px;">(' + escapeHtml(tel) + ')</p>' +
            '        <p style="font-size:13px;color:#666;margin-top:10px;">Este cliente aun no ha iniciado una conversacion por WhatsApp.</p>' +
            '      </div>' +
            '      <div class="modal-footer" style="text-align:center;border-top:1px solid #eee;padding:12px 20px;">' +
            '        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:8px;">Cerrar</button>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';

        $('#modalSinConversacionWA').remove();
        $('body').append(html);
        $('#modalSinConversacionWA').modal('show');
    }

});
