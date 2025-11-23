//FILTRO TIPOS Y ESTADO******************
$(document).ready(function() {
    // Esperar un momento para asegurarnos de que plantilla.js ya inicializó la tabla
    setTimeout(function() {
        var tablaActividades;

        if ($.fn.DataTable.isDataTable('.tablas')) {
            // Ya está inicializado por plantilla.js
            tablaActividades = $('.tablas').DataTable();

            // Marcar como lista si aún no lo está
            if (!$('.tablas').hasClass('datatable-ready')) {
                $('.tablas').addClass('datatable-ready');
            }
        } else {
            // No está inicializado, crear nueva instancia
            tablaActividades = $('.tablas').DataTable({
                autoWidth: false,
                responsive: true,
                language: {
                    url: "vistas/bower_components/datatables.net/Spanish.json",
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                },
                initComplete: function() {
                    $('.tablas').addClass('datatable-ready');
                }
            });
        }

        // Extiende el filtro de DataTables para tipo y estado
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const filtroTipo = $('#filtroTipo').val().toLowerCase();
                const filtroEstado = $('#filtroEstado').val().toLowerCase();

                // Obtiene el texto directamente de las columnas
                const tipoTexto = $(tablaActividades.row(dataIndex).node())
                    .find('td:eq(2)')
                    .text().trim().toLowerCase();

                const estadoTexto = $(tablaActividades.row(dataIndex).node())
                    .find('td:eq(5) .badge')
                    .text().trim().toLowerCase();

                const coincideTipo = (filtroTipo === "" || tipoTexto === filtroTipo);
                const coincideEstado = (filtroEstado === "" || estadoTexto === filtroEstado);

                return coincideTipo && coincideEstado;
            }
        );

        // Dispara redibujado al cambiar los selects
        $('#filtroTipo').on('change', function() {
            tablaActividades.draw();
        });

        $('#filtroEstado').on('change', function() {
            tablaActividades.draw();
        });
    }, 100);
});



/*=============================================
Dar colores al campo Estado - DESACTIVADO
Ya no se usan selects, ahora son badges de solo lectura
=============================================*/
/*
// Código desactivado porque ya no se usan selects para Estado y Tipo
// Ahora son badges de solo lectura que se editan desde el modal
*/



// EDITAR Observacion
// Permitir edición directa en campo "Observacion"
function inicializarEdicionObs() {
    $('.celda-observacion').off('blur').on('blur', function () {
      const id = $(this).data('id');
      const nuevaObservacion = $(this).text().trim();
  
      $.ajax({
        url: 'ajax/actividades.ajax.php',
        method: 'POST',
        data: {
          id: id,
          observacion: nuevaObservacion,
          accion: 'actualizarObservacion'
        },
        success: function (respuesta) {
          console.log('Nota actualizada:', respuesta);
        },
        error: function () {
          alert('Error al actualizar la nota');
        }
      });
    });
  }
  // Ejecutar al cargar por primera vez
  inicializarEdicionObs();


// CALENDARIO
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es', // Establece el idioma a español
        initialView: 'dayGridMonth',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
          },
        events: 'ajax/eventos.php' // Ruta al archivo PHP que devuelve los eventos
    });

    calendar.render();
});