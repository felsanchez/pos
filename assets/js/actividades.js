//FILTRO TIPOS Y ESTADO******************
$(document).ready(function() {
    // Verificar si DataTable ya está inicializado, si no, usar la instancia existente de plantilla.js

    var tablaActividades;

 

    if ($.fn.DataTable.isDataTable('.tablas')) {

        // Ya está inicializado por plantilla.js, usar la instancia existente

        tablaActividades = $('.tablas').DataTable();

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
            preDrawCallback: function() {
                // Ocultar tabla antes de dibujarla por primera vez
                if (!$(this).hasClass('datatable-ready')) {
                    $(this).css('visibility', 'hidden');
                }
            },
            initComplete: function() {
                // Mostrar tabla solo cuando esté completamente inicializada
                $(this).addClass('datatable-ready').css('visibility', 'visible');
            }
        });
    }

    // Extiende el filtro de DataTables para tipo y estado
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            const filtroTipo = $('#filtroTipo').val().toLowerCase();
            const filtroEstado = $('#filtroEstado').val().toLowerCase();

            // Obtiene el texto directamente de las columnas (ya no son selects, sino texto plano)
            // Columna Tipo (índice 2)
            const tipoTexto = $(tablaActividades.row(dataIndex).node())
                .find('td:eq(2)')
                .text().trim().toLowerCase();

            // Columna Estado (índice 5) - dentro del badge
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

    // Ajustar columnas cuando se cierran los modales, sin efecto visual
    $('.modal').on('hidden.bs.modal', function() {
        // Esperar a que el modal se cierre completamente
        setTimeout(function() {
            if (tablaActividades) {
                tablaActividades.columns.adjust();
            }
        }, 200);
    });
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