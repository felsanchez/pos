/*=============================================
SISTEMA DE LOGS
=============================================*/

$(document).ready(function() {
    // Cargar logs al iniciar
    cargarLogs();
    cargarEstadisticas();

    // Botón filtrar
    $("#btn-filtrar").click(function() {
        cargarLogs();
        cargarEstadisticas();
    });

    // Botón refrescar
    $("#btn-refrescar").click(function() {
        cargarLogs();
        cargarEstadisticas();
    });

    // Checkbox seleccionar todos
    $(document).on("change", "#check-all", function() {
        var isChecked = $(this).prop("checked");
        $(".log-checkbox").prop("checked", isChecked);
        actualizarBotonEliminar();
    });

    // Checkboxes individuales
    $(document).on("change", ".log-checkbox", function() {
        var totalCheckboxes = $(".log-checkbox").length;
        var checkedCheckboxes = $(".log-checkbox:checked").length;
        $("#check-all").prop("checked", totalCheckboxes === checkedCheckboxes);
        actualizarBotonEliminar();
    });

    // Botón eliminar seleccionados
    $("#btn-eliminar-seleccionados").click(function() {
        var logsSeleccionados = [];
        $(".log-checkbox:checked").each(function() {
            logsSeleccionados.push({
                timestamp: $(this).data("timestamp"),
                fecha: $(this).data("fecha")
            });
        });

        if (logsSeleccionados.length === 0) {
            return;
        }

        swal({
            title: "¿Está seguro?",
            text: "Se eliminarán " + logsSeleccionados.length + " registro(s) de log",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sí, eliminar"
        }).then(function(result) {
            if (result.value) {
                eliminarLogsSeleccionados(logsSeleccionados);
            }
        });
    });

    // Botón limpiar logs antiguos
    $("#btn-limpiar").click(function() {
        swal({
            title: "¿Está seguro?",
            text: "Se eliminarán todos los logs con más de 30 días",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "Cancelar",
            confirmButtonText: "Sí, limpiar"
        }).then(function(result) {
            if (result.value) {
                limpiarLogsAntiguos();
            }
        });
    });

    // Auto-refresh cada 30 segundos
    setInterval(function() {
        cargarEstadisticas();
    }, 30000);
});

/*=============================================
CARGAR LOGS
=============================================*/
function cargarLogs() {
    var fecha = $("#filtro-fecha").val();
    var nivel = $("#filtro-nivel").val();
    var limite = $("#filtro-limite").val();

    $("#tbody-logs").html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando logs...</td></tr>');

    var datos = new FormData();
    datos.append("accion", "obtener_logs");
    datos.append("fecha", fecha);
    datos.append("nivel", nivel);
    datos.append("limite", limite);

    $.ajax({
        url: "ajax/logs.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.length === 0) {
                $("#tbody-logs").html('<tr><td colspan="7" class="text-center">No hay logs para mostrar</td></tr>');
                return;
            }

            var html = '';
            respuesta.forEach(function(log) {
                var nivelClass = getNivelClass(log.level);
                var nivelBadge = getNivelBadge(log.level);

                html += '<tr>';
                html += '<td><input type="checkbox" class="log-checkbox" data-timestamp="' + escapeHtml(log.timestamp) + '" data-fecha="' + fecha + '"></td>';
                html += '<td><small>' + log.timestamp + '</small></td>';
                html += '<td>' + nivelBadge + '</td>';
                html += '<td>' + escapeHtml(log.message) + '</td>';
                html += '<td><small>' + escapeHtml(log.user) + '</small></td>';
                html += '<td><small>' + escapeHtml(log.ip) + '</small></td>';
                html += '<td><button class="btn btn-info btn-xs btn-ver-detalle" data-log=\'' + JSON.stringify(log) + '\'><i class="fa fa-eye"></i></button></td>';
                html += '</tr>';
            });

            $("#tbody-logs").html(html);

            // Event listener para ver detalles
            $(".btn-ver-detalle").click(function() {
                var log = JSON.parse($(this).attr("data-log"));
                mostrarDetalleLog(log);
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar logs:", textStatus, errorThrown);
            $("#tbody-logs").html('<tr><td colspan="7" class="text-center text-danger">Error al cargar logs</td></tr>');
        }
    });
}

/*=============================================
ACTUALIZAR BOTÓN ELIMINAR
=============================================*/
function actualizarBotonEliminar() {
    var checkedCheckboxes = $(".log-checkbox:checked").length;
    if (checkedCheckboxes > 0) {
        $("#btn-eliminar-seleccionados").show();
    } else {
        $("#btn-eliminar-seleccionados").hide();
    }
}

/*=============================================
ELIMINAR LOGS SELECCIONADOS
=============================================*/
function eliminarLogsSeleccionados(logs) {
    var datos = new FormData();
    datos.append("accion", "eliminar_logs");
    datos.append("logs", JSON.stringify(logs));

    $.ajax({
        url: "ajax/logs.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                swal({
                    type: "success",
                    title: "Logs eliminados",
                    text: "Se eliminaron " + respuesta.deleted + " registro(s)",
                    showConfirmButton: true
                }).then(function() {
                    $("#check-all").prop("checked", false);
                    cargarLogs();
                    cargarEstadisticas();
                });
            } else {
                swal({
                    type: "error",
                    title: "Error",
                    text: respuesta.message || "No se pudieron eliminar los logs"
                });
            }
        },
        error: function() {
            swal({
                type: "error",
                title: "Error",
                text: "Error al comunicarse con el servidor"
            });
        }
    });
}

/*=============================================
CARGAR ESTADÍSTICAS
=============================================*/
function cargarEstadisticas() {
    var fecha = $("#filtro-fecha").val();

    var datos = new FormData();
    datos.append("accion", "obtener_estadisticas");
    datos.append("fecha", fecha);

    $.ajax({
        url: "ajax/logs.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            $("#stat-total").text(respuesta.total || 0);
            $("#stat-errors").text(respuesta.errors || 0);
            $("#stat-warnings").text(respuesta.warnings || 0);
            $("#stat-info").text(respuesta.info || 0);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar estadísticas:", textStatus, errorThrown);
        }
    });
}

/*=============================================
LIMPIAR LOGS ANTIGUOS
=============================================*/
function limpiarLogsAntiguos() {
    var datos = new FormData();
    datos.append("accion", "limpiar_logs");
    datos.append("dias", 30);

    $.ajax({
        url: "ajax/logs.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                swal({
                    type: "success",
                    title: "Logs limpiados",
                    text: "Se eliminaron " + respuesta.deleted + " archivo(s) de log",
                    showConfirmButton: true
                }).then(function() {
                    cargarLogs();
                    cargarEstadisticas();
                });
            } else {
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudieron limpiar los logs"
                });
            }
        },
        error: function() {
            swal({
                type: "error",
                title: "Error",
                text: "Error al comunicarse con el servidor"
            });
        }
    });
}

/*=============================================
MOSTRAR DETALLE DEL LOG
=============================================*/
function mostrarDetalleLog(log) {
    var html = '<div class="row">';

    // Información básica
    html += '<div class="col-md-6">';
    html += '<dl class="dl-horizontal">';
    html += '<dt>Fecha/Hora:</dt><dd>' + log.timestamp + '</dd>';
    html += '<dt>Nivel:</dt><dd>' + getNivelBadge(log.level) + '</dd>';
    html += '<dt>Usuario:</dt><dd>' + escapeHtml(log.user) + '</dd>';
    html += '<dt>IP:</dt><dd>' + escapeHtml(log.ip) + '</dd>';
    html += '<dt>URL:</dt><dd><small>' + escapeHtml(log.url) + '</small></dd>';
    html += '<dt>Método:</dt><dd>' + escapeHtml(log.method) + '</dd>';
    html += '</dl>';
    html += '</div>';

    html += '<div class="col-md-6">';
    html += '<h4>Mensaje:</h4>';
    html += '<p>' + escapeHtml(log.message) + '</p>';
    html += '</div>';

    html += '</div>';

    // Contexto
    if (log.context && Object.keys(log.context).length > 0) {
        html += '<hr>';
        html += '<h4>Contexto:</h4>';
        html += '<pre>' + JSON.stringify(log.context, null, 2) + '</pre>';
    }

    // Excepción
    if (log.exception) {
        html += '<hr>';
        html += '<h4 class="text-danger">Excepción:</h4>';
        html += '<dl class="dl-horizontal">';
        html += '<dt>Mensaje:</dt><dd>' + escapeHtml(log.exception.message) + '</dd>';
        html += '<dt>Código:</dt><dd>' + log.exception.code + '</dd>';
        html += '<dt>Archivo:</dt><dd><small>' + escapeHtml(log.exception.file) + '</small></dd>';
        html += '<dt>Línea:</dt><dd>' + log.exception.line + '</dd>';
        html += '</dl>';
        html += '<h5>Stack Trace:</h5>';
        html += '<pre style="max-height: 300px; overflow-y: auto;">' + escapeHtml(log.exception.trace) + '</pre>';
    }

    $("#modal-body-log").html(html);
    $("#modal-detalle-log").modal("show");
}

/*=============================================
OBTENER CLASE CSS SEGÚN NIVEL
=============================================*/
function getNivelClass(nivel) {
    switch(nivel) {
        case 'ERROR': return 'danger';
        case 'WARNING': return 'warning';
        case 'INFO': return 'info';
        case 'DEBUG': return 'default';
        default: return 'default';
    }
}

/*=============================================
OBTENER BADGE SEGÚN NIVEL
=============================================*/
function getNivelBadge(nivel) {
    var clase = getNivelClass(nivel);
    return '<span class="label label-' + clase + '">' + nivel + '</span>';
}

/*=============================================
ESCAPAR HTML
=============================================*/
function escapeHtml(text) {
    if (!text) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}