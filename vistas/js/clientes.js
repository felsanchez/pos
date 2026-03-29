/*=============================================
EDITAR CLIENTE (cargar datos en modal)
=============================================*/
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
      $("#editarCliente").val(respuesta.nombre);
      $("#editarDocumentoId").val(respuesta.documento);
      $("#editarEmail").val(respuesta.email);
      $("#editarTelefono").val(respuesta.telefono);
      $("#editarDepartamento").val(respuesta.departamento);
      $("#editarCiudad").val(respuesta.ciudad);
      $("#editarDireccion").val(respuesta.direccion);
      $("#editarFechaNacimiento").val(respuesta.fecha_nacimiento);
      $("#editarEstado").val(respuesta.estatus);
      $("#editarNota").val(respuesta.notas);
      $("#idCliente").val(respuesta.id);
    }
  });
});


/*=============================================
TABLA CLIENTES (DataTables sin plugin responsive)
=============================================*/
var tabla1;

function filterTable1() {
  if (tabla1) tabla1.draw();
}

$(document).ready(function () {

  if ($('.tablas1').length === 0) return;

  tabla1 = $('.tablas1').DataTable({
    "destroy": true,
    "stateSave": false,
    "order": [[0, 'desc']],
    "autoWidth": true,
    "pageLength": 25,
    "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
    "columnDefs": [
      { "targets": 0, "orderable": true },
      { "targets": 1, "orderable": true },
      { "targets": 2, "orderable": true },
      { "targets": 3, "orderable": true },
      { "targets": 4, "orderable": true },
      { "targets": 5, "orderable": true },
      { "targets": 6, "orderable": true },
      { "targets": 7, "orderable": true },
      { "targets": 8, "orderable": true },
      { "targets": 9, "orderable": false },
      { "targets": 10, "orderable": true }
    ],
    "language": {
      url: "vistas/bower_components/datatables.net/Spanish.json",
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_ entradas",
      info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
      sLoadingRecords: "Cargando...",
      oPaginate: {
        sFirst: "Primero",
        sLast: "Último",
        sNext: "Siguiente",
        sPrevious: "Anterior"
      }
    }
  });

  // Filtro por estado: buscar en la celda de Estado por el texto del badge
  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    if (settings.nTable !== $('.tablas1')[0]) return true;
    var filtro = $('#filtroEstatus1').val();
    if (!filtro || filtro === '') return true;
    // columna 6 (índice 6) = Estado — extraer texto del badge, ignorar HTML
    var rawHtml = data[6] || '';
    var estadoCelda = rawHtml ? $('<div>').html(rawHtml).text().toLowerCase() : '';
    return estadoCelda.indexOf(filtro.toLowerCase()) !== -1;
  });

  $('#filtroEstatus1').on('change', function () {
    filterTable1();
  });

});


/*=============================================
EDITAR NOTAS (inline, contenteditable)
=============================================*/
$(document).on('focus', '.celda-notas', function () {
  $(this).removeAttr('data-placeholder');
});

$(document).on('blur', '.celda-notas', function () {
  var nuevaNota = $(this).text().trim();
  var id = $(this).data('id');

  if (nuevaNota === '') {
    $(this).attr('data-placeholder', 'true');
  }

  if (id) {
    $.ajax({
      url: 'ajax/clientes.ajax.php',
      method: 'POST',
      data: {
        id: id,
        notas: nuevaNota,
        accion: 'actualizarNota'
      },
      error: function () {
        alert('Error al actualizar la nota');
      }
    });
  }
});

// Inicializar placeholder en celdas vacías al cargar
$(document).ready(function () {
  $('.celda-notas').each(function () {
    if ($(this).text().trim() === '') {
      $(this).attr('data-placeholder', 'true');
    }
  });
});


/*=============================================
BOTÓN SIN VENTAS
=============================================*/
$(document).on("click", ".btnSinVentas", function (e) {
  e.preventDefault();
  swal({
    title: "Sin ventas",
    text: "Este cliente no tiene ventas registradas",
    icon: "info",
    confirmButtonText: "Cerrar"
  });
});


/*=============================================
ELIMINAR CLIENTE
=============================================*/
$(document).on("click", ".btnEliminarCliente", function () {
  var idCliente = $(this).attr("idCliente");

  swal({
    title: "¿Estás seguro de borrar el cliente?",
    text: "¡Si no lo estás puedes cancelar la acción!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Sí, borrar'
  }).then(function (result) {
    if (result.value) {
      var datos = new FormData();
      datos.append("idClienteEliminar", idCliente);
      datos.append("ruta", "clientes");

      $.ajax({
        url: "ajax/clientes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {
          respuesta = $.trim(respuesta);
          if (respuesta === "ok") {
            swal({
              icon: "success",
              title: "¡El cliente ha sido borrado correctamente!",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            }).then(function (r) {
              if (r.value) {
                window.location = "clientes";
              }
            });
          } else if (respuesta === "error_actividades") {
            swal({
              icon: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene actividades asociadas.",
              confirmButtonText: "Cerrar"
            });
          } else if (respuesta === "error_ventas") {
            swal({
              icon: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene ventas asociadas.",
              confirmButtonText: "Cerrar"
            });
          } else if (respuesta === "error_notas_credito") {
            swal({
              icon: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene notas crédito asociadas.",
              confirmButtonText: "Cerrar"
            });
          } else {
            swal({
              icon: "error",
              title: "Error",
              text: "No se pudo eliminar el cliente. " + respuesta,
              confirmButtonText: "Cerrar"
            });
          }
        },
        error: function (xhr, status, err) {
          swal({
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor.",
            confirmButtonText: "Cerrar"
          });
        }
      });
    }
  });
});


/*=============================================
VALIDAR CLIENTE DUPLICADO AL CREAR
=============================================*/
$(document).on("change", "#nuevoCliente", function () {
  $(".alert").remove();
  var nombre = $(this).val();
  var datos = new FormData();
  datos.append("validarCliente", nombre);

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (respuesta) {
      if (respuesta) {
        $("#nuevoCliente").parent().after('<div class="alert alert-warning">Este cliente ya existe en la base de datos!</div>');
      }
    }
  });
});