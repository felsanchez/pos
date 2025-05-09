//FILTRO TIPOS Y ESTADO******************
$(document).ready(function() {
    // Inicializa la tabla DataTable
    var tablaActividades = $('.tablas').DataTable(
        {
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
            }
          }
    );

    // Extiende el filtro de DataTables para tipo y estado
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            const filtroTipo = $('#filtroTipo').val().toLowerCase();
            const filtroEstado = $('#filtroEstado').val().toLowerCase();

            // Obtiene el texto del <option selected> en la columna Tipo (índice 2)
            const tipoTexto = $(tablaActividades.row(dataIndex).node())
                .find('td:eq(2) select option:selected')
                .text().trim().toLowerCase();

            // Obtiene el texto del <option selected> en la columna Estado (índice 5)
            const estadoTexto = $(tablaActividades.row(dataIndex).node())
                .find('td:eq(5) select option:selected')
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
});



/*=============================================
Dar colores al campo Estado
=============================================*/
// Función para aplicar el color al select de acuerdo al estado
function aplicarColorEstado(select) {
    const value = select.value;
    const container = select.closest('.choices'); // Obtener el contenedor de Choices.js

    if (!container) return;

    // Eliminar clases anteriores que empiecen con "estado-"
    container.className = container.className
        .split(" ")
        .filter(cls => !cls.startsWith("estado-"))
        .join(" ");

    // Agregar la nueva clase según el valor del estado
    container.classList.add("estado-" + value.replace(/ /g, "-").toLowerCase());
}

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar Choices.js en cada select
    document.querySelectorAll('.cambiarEstado').forEach(function (select) {
        const choices = new Choices(select, {
            searchEnabled: false,
            itemSelectText: ''
        });

        // Aplicar el color al inicializar el select
        setTimeout(() => {
            aplicarColorEstado(select);
        }, 100);

        // Cambiar el color dinámicamente al cambiar el valor del select
        select.addEventListener('change', function () {
            aplicarColorEstado(select);
        });
    });
});


// Script para guardar el estado por AJAX
$(document).on("change", ".cambiarEstado", function () {
    var idActividad = $(this).data("id");
    var nuevoEstado = $(this).val();
    var select = $(this)[0]; // Para usarlo en aplicarColorEstado

    $.ajax({
        url: "ajax/actividades.ajax.php",  // Asegúrate de que esta URL esté correcta
        method: "POST",
        data: {
            idActividad: idActividad,
            nuevoEstado: nuevoEstado
        },
        success: function (respuesta) {
            console.log("Respuesta del servidor:", response);
            //console.log("Respuesta del servidor:", respuesta);

            if (respuesta.status === "ok") {
                aplicarColorEstado(select); // Asegura aplicar color correcto después del cambio
            } else {
                alert("Error al guardar el estado");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            console.log("XHR responseText:", xhr.responseText);
        }
    });
});



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
