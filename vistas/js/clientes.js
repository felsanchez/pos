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

  // Inicializar Select2 para el filtro de estado
  if ($("#filtroEstatus1").length > 0 && typeof $.fn.select2 !== 'undefined') {
    $("#filtroEstatus1").select2({
      placeholder: "Seleccionar estado...",
      allowClear: true,
      minimumResultsForSearch: 0,
      width: '100%'
    });
  }

  if ($('.tablas1').length === 0) return;

  tabla1 = $('.tablas1').DataTable({
    "destroy": true,
    "stateSave": false,
    "order": [[0, 'asc']],
    "autoWidth": true,
    "pageLength": 25,
    "responsive": {
      "details": {
        "type": "inline",
        "renderer": function (api, rowIdx, columns) {
          var rowData = api.row(rowIdx).data();

          // Índices (0-based):
          // 0: Nombre, 1: Documento, 2: Email, 3: Teléfono, 4: Dirección,
          // 5: Estado (HTML), 6: Notas, 7: Última compra, 8: Acciones (HTML), 9: Ingreso al sistema

          // Extraer solo el texto del documento (evitando los botones solo-movil que están en la misma celda)
          var documento = $('<div>').html(rowData[1]).contents().filter(function () {
            return this.nodeType === 3; // Nodo de texto
          }).text().trim();

          var email = rowData[2];
          var direccion = rowData[4];
          var estado = rowData[5];
          var notas = rowData[6];
          var ultimaCompra = rowData[7];
          var ingreso = rowData[9];

          // Leer ID del cliente desde el atributo data-cliente-id del <tr>
          var idCliente = $(api.row(rowIdx).node()).attr('data-cliente-id') || "";

          var finalHtml = '';

          // Sección 1: Información Personal
          finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align:left;">';
          finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Información Personal</h5></div>';

          finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
          finalHtml += '<span class="text-bold" style="color:#555;">Documento: </span><span style="color:#333; text-align: right;">' + documento + '</span></div>';

          finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
          finalHtml += '<span class="text-bold" style="color:#555;">Correo: </span><span style="color:#333; text-align: right;">' + email + '</span></div>';

          finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
          finalHtml += '<span class="text-bold" style="color:#555;">Dirección: </span><span style="color:#333; text-align: right;">' + direccion + '</span></div>';

          finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
          finalHtml += '<span class="text-bold" style="color:#555;">Estado: </span><span style="color:#333; text-align: right;">' + estado + '</span></div>';

          // Sección 2: Actividad
          finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align:left;">';
          finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Actividad</h5></div>';

          finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
          finalHtml += '<span class="text-bold" style="color:#555;">Última compra: </span><span style="color:#333; text-align: right;">' + ultimaCompra + '</span></div>';

          finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">';
          finalHtml += '<span class="text-bold" style="color:#555;">Ingreso al sistema: </span><span style="color:#333; text-align: right;">' + ingreso + '</span></div>';

          // Notas editable con el mismo estilo que en escritorio (con placeholder dinámico)
          var placeholderAttr = (notas === "") ? ' data-placeholder="true"' : "";
          finalHtml += '<div class="col-xs-12" style="padding: 10px 0; border-bottom: 1px solid #eee;">';
          finalHtml += '<span class="text-bold" style="color:#555; display:block; margin-bottom:5px;">Notas: </span>';
          finalHtml += '<div class="celda-notas" contenteditable="true" data-id="' + idCliente + '"' + placeholderAttr + ' style="width: 100%;">' + notas + '</div></div>';

          return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #f8f9fa; margin: 0;">').append(finalHtml) : false;
        }
      }
    },
    "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
    "columnDefs": [
      {
        "targets": 0, // Nombre
        "responsivePriority": 1
      },
      {
        "targets": 3, // Teléfono
        "responsivePriority": 2
      },
      {
        "targets": 8, // Acciones
        "responsivePriority": 1,
        "orderable": false
      },
      {
        "targets": [1, 2, 4, 5, 6, 7, 9], // Otros
        "responsivePriority": 1000
      }
    ],
    "drawCallback": function (settings) {
      if (typeof inicializarPlaceholdersClientes === "function") {
        inicializarPlaceholdersClientes();
      }
    },
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
    // columna 5 (índice 5) = Estado — extraer texto del badge, ignorar HTML
    var rawHtml = data[5] || '';
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
  console.log('🔹 Blur detectado en Notas Cliente (ID: ' + $(this).attr('data-id') + ')');
  var elemento = $(this);
  var id = elemento.attr('data-id'); 
  var nuevaNota = elemento.text().trim();

  if (nuevaNota === '') {
    elemento.attr('data-placeholder', 'true');
  }

  if (!id) {
    console.warn('⚠️ celda-notas: no se encontró data-id en el elemento.');
    return;
  }

  // Obtener token CSRF (requerido por el servidor)
  var csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: 'ajax/clientes.ajax.php',
    method: 'POST',
    data: {
      id: id,
      notas: nuevaNota,
      accion: 'actualizarNota',
      csrf_token: csrfToken
    },
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta == "ok") {
        console.log('✅ Nota guardada correctamente (Cliente ID: ' + id + ')');
        // Feedback visual (destello verde)
        elemento.css('background-color', '#dff0d8');
        setTimeout(function () {
          elemento.css('background-color', '');
        }, 500);
      }
    },
    error: function () {
      alert('Error al actualizar la nota');
    }
  });
});

// Inicializar placeholder en celdas vacías al cargar
function inicializarPlaceholdersClientes() {
  $('.celda-notas').each(function () {
    if ($(this).text().trim() === '') {
      $(this).attr('data-placeholder', 'true');
    } else {
      $(this).removeAttr('data-placeholder');
    }
  });
}

$(document).ready(function () {
  inicializarPlaceholdersClientes();
});


/*=============================================
BOTÓN SIN VENTAS
=============================================*/
$(document).on("click", ".btnSinVentas", function (e) {
  e.preventDefault();
  swal({
    title: "Sin ventas",
    text: "Este cliente no tiene ventas registradas",
    type: "info",
    confirmButtonText: "Cerrar"
  });
});


/*=============================================
BOTÓN SIN FACTURAS ELECTRÓNICAS
=============================================*/
$(document).on("click", ".btnSinFacturas", function (e) {
  e.preventDefault();
  swal({
    title: "Sin Facturas electrónicas",
    text: "Este cliente no tiene facturas electrónicas registradas",
    type: "info",
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
    type: 'warning',
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
              type: "success",
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
              type: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene actividades asociadas.",
              confirmButtonText: "Cerrar"
            });
          } else if (respuesta === "error_ventas") {
            swal({
              type: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene ventas asociadas.",
              confirmButtonText: "Cerrar"
            });
          } else if (respuesta === "error_notas_credito") {
            swal({
              type: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene notas crédito asociadas.",
              confirmButtonText: "Cerrar"
            });
          } else {
            swal({
              type: "error",
              title: "Error",
              text: "No se pudo eliminar el cliente. " + respuesta,
              confirmButtonText: "Cerrar"
            });
          }
        },
        error: function (xhr, status, err) {
          swal({
            type: "error",
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