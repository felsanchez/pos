//Campo estatus
$(document).on("click", ".btnEditarCliente", function () {
  var idCliente = $(this).attr("idCliente");

  var datos = new FormData();
  datos.append("idClienteEditar", idCliente);

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {

      $("#editarCliente").val(respuesta.nombre); // mantiene tu variable personalizada
      $("#editarDocumentoId").val(respuesta.documento); // mantiene tu variable personalizada
      $("#editarEmail").val(respuesta.email);
      $("#editarTelefono").val(respuesta.telefono);
      $("#editarDepartamento").val(respuesta.departamento);
      $("#editarCiudad").val(respuesta.ciudad);
      $("#editarDireccion").val(respuesta.direccion);
      $("#editarFechaNacimiento").val(respuesta.fecha_nacimiento);
      $("#editarEstatus").val(respuesta.estatus);
      $("#editarNota").val(respuesta.notas);

      // Input oculto para el ID del cliente
      $("#idCliente").val(respuesta.id);
    }
  });
});


// Función para aplicar el color del estatus al contenedor de Choices.js
function aplicarColorEstatus(select) {
    const value = select.value;
    const container = select.closest('.choices');

    if (!container) return;

    // Eliminar clases anteriores que empiecen con "estatus-"
    container.className = container.className
      .split(" ")
      .filter(cls => !cls.startsWith("estatus-"))
      .join(" ");

    // Agregar la nueva clase
    container.classList.add("estatus-" + value.replace(/ /g, "-").toLowerCase());
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cambiarEstatus').forEach(function (select) {
      const choices = new Choices(select, {
        searchEnabled: false,
        itemSelectText: ''
      });

      // Esperar a que Choices.js genere su HTML (usa setTimeout como fallback)
      setTimeout(() => {
        aplicarColorEstatus(select);
      }, 100);

      // Cambiar color dinámicamente al cambiar el valor
      select.addEventListener('change', function () {
        aplicarColorEstatus(select);
      });
    });
  });

  // Script para guardar el estatus por AJAX
  $(document).on("change", ".cambiarEstatus", function () {
    var idCliente = $(this).data("id");
    var nuevoEstatus = $(this).val();
    var select = $(this)[0]; // Para usarlo en aplicarColorEstatus

    $.ajax({
      url: "ajax/clientes.ajax.php",
      method: "POST",
      data: {
        idCliente: idCliente,
        nuevoEstatus: nuevoEstatus
      },
      success: function (respuesta) {
        console.log("RESPUESTA:", respuesta);
        if (respuesta === "ok") {
          aplicarColorEstatus(select); // Asegura aplicar color correcto después del cambio
        } else {
          alert("Error al guardar el estatus");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
      }
    });
  });



  // BUSCADOR ESTATUS
// Función para aplicar el filtro personalizado
function filterTable() {
  $('.tablas').DataTable().draw(); // Forzar re-filtrado
}

$(document).ready(function () {

  // Inicializar DataTable si no está ya inicializada
  if (!$.fn.DataTable.isDataTable('.tablas')) {
    $('.tablas').DataTable({
      responsive: true,
      language: {
        url: "vistas/bower_components/datatables.net/Spanish.json",

        search: "Palabra Clave:",
        lengthMenu: "Mostrar _MENU_ entradas",
        info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
        "sLoadingRecords": "Cargando...",
        "oPaginate":       {
        "sFirst":          "Primero",
        "sLast":           "Último",
        "sNext":           "Siguiente",
        "sPrevious":       "Anterior"
          },

      }
    });
  }

  // Agregamos el filtro personalizado por estatus
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    const filtroSeleccionado = $('#filtroEstatus').val().toLowerCase();

    if (!filtroSeleccionado) return true;

    // Buscar el texto visible del <select> en la columna 8
    const row = settings.oInstance.api().row(dataIndex).node();
    const select = $(row).find('td:eq(9) select option:selected').text().toLowerCase();

    return select === filtroSeleccionado;
  });

  // Disparar filtro al cambiar el dropdown
  $('#filtroEstatus').on('change', function () {
    filterTable();
  });

});


// EDITAR NOTAS
// Permitir edición directa en campo "notas"
function inicializarEdicionNotas() {
  $('.celda-notas').off('blur').on('blur', function () {
    const id = $(this).data('id');
    const nuevaNota = $(this).text().trim();

    $.ajax({
      url: 'ajax/clientes.ajax.php',
      method: 'POST',
      data: {
        id: id,
        notas: nuevaNota,
        accion: 'actualizarNota'
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
inicializarEdicionNotas();
