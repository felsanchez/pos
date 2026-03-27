console.log("DEBUG: vistas/js/clientes.js cargado correctamente");

//Campo estatus
$(document).on("click", ".btnEditarCliente", function () {
  console.log("DEBUG: Clic en .btnEditarCliente detectado");
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
      $("#editarEstado").val(respuesta.estatus);
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

  console.log("=== DEBUG aplicarColorEstatus ===");
  console.log("Select value:", value);
  console.log("Container found:", container);

  if (!container) {
    console.log("ERROR: No se encontró contenedor .choices");
    return;
  }

  // Eliminar clases anteriores que empiecen con "estatus-"
  container.className = container.className
    .split(" ")
    .filter(cls => !cls.startsWith("estatus-"))
    .join(" ");

  // Agregar la nueva clase
  const nuevaClase = "estatus-" + value.replace(/ /g, "-").toLowerCase();
  console.log("Agregando clase:", nuevaClase);
  container.classList.add(nuevaClase);
  console.log("Clases finales del contenedor:", container.className);

  // Aplicar estilos inline directamente usando el mapa de colores
  const valueLower = value.toLowerCase();
  const color = window.estadosColores ? window.estadosColores[valueLower] : null;

  console.log("Color encontrado para '" + valueLower + "':", color);

  if (color) {
    const choicesInner = container.querySelector('.choices__inner');
    if (choicesInner) {

      // Aplicar con setProperty para poder usar !important
      choicesInner.style.setProperty('background-color', color, 'important');
      choicesInner.style.setProperty('border-color', color, 'important');
      choicesInner.style.setProperty('color', '#fff', 'important');
      console.log("Estilos inline aplicados al .choices__inner con !important");

      console.log("Estilo actual:", choicesInner.style.cssText);

    } else {
      console.log("ERROR: No se encontró .choices__inner dentro del contenedor");
    }

  } else {
    console.log("ERROR: No se encontró color para el estado '" + valueLower + "'");
    console.log("Colores disponibles:", window.estadosColores);
  }
}

// Función para inicializar Choices.js en los selects de estatus
function inicializarChoicesEstatus() {
  console.log("=== inicializarChoicesEstatus LLAMADA ===");
  const selects = document.querySelectorAll('.cambiarEstatus');
  console.log("Total de selects .cambiarEstatus encontrados:", selects.length);

  selects.forEach(function (select, index) {
    console.log("Procesando select #" + index, select);

    // Evitar reinicializar si ya tiene Choices.js
    if (select.classList.contains('choices__input')) {
      console.log("Select #" + index + " ya tiene Choices.js, saltando...");
      return;
    }

    // Guardar las clases de estatus del select original

    const clasesEstatus = Array.from(select.classList).filter(cls => cls.startsWith('estatus-'));

    console.log("Clases estatus encontradas en select #" + index + ":", clasesEstatus);

    const choices = new Choices(select, {
      searchEnabled: false,
      itemSelectText: '',

      shouldSort: false,  // Mantener el orden original del HTML (ordenado por columna "orden")
      position: 'auto',   // Posicionar automáticamente el dropdown (arriba o abajo según espacio disponible)
      shouldSortItems: false
    });

    console.log("Choices.js inicializado para select #" + index);

    // Aplicar color inicial después de que Choices.js cree el contenedor
    setTimeout(() => {
      console.log("Timeout ejecutándose para select #" + index);
      const container = select.closest('.choices');
      console.log("Container encontrado:", container);

      if (container && clasesEstatus.length > 0) {
        // Agregar las clases de estatus al contenedor de Choices.js
        clasesEstatus.forEach(clase => {
          container.classList.add(clase);
          console.log("Clase agregada al contenedor:", clase);
        });

      }

      aplicarColorEstatus(select);

    }, 100);

    // Cambiar color dinámicamente al cambiar el valor

    select.addEventListener('change', function () {

      console.log("Select cambió, aplicando color...");

      aplicarColorEstatus(select);

    });

  });

}

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
var tabla1;

function filterTable1() {
  if(tabla1) tabla1.draw(); 
}

$(document).ready(function () {


  tabla1 = $('.tablas1').DataTable({
    "destroy": true,
    "stateSave": false,
    "responsive": {
      "details": {
        "renderer": function (api, rowIdx, columns) {
          var grupo1 = ""; // Estado, Teléfono
          var grupo2 = ""; // Ubicación
          var grupo3 = ""; // Otros
          var otros = "";  // Fallback

          $.each(columns, function (i, col) {
            if (col.hidden && col.title !== "Acciones" && col.title !== "#") {
              var title = col.title.toLowerCase();
              var dataContent = col.data;

              // Si es la columna de notas, reconstruir el div editable
              if (title.includes("notas")) {
                var cellNode = api.cell(rowIdx, col.columnIndex).node();
                var dataId = $(cellNode).data('id');
                if (dataId) {
                  dataContent = '<div contenteditable="true" class="celda-notas" data-id="' + dataId + '">' + (col.data || '') + '</div>';
                }
              }

              var itemHtml = '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">' +
                '<span class="text-bold" style="color:#555;">' + col.title + ': </span>' +
                '<span class="pull-right" style="color:#333; width: 100%;">' + dataContent + '</span>' +
                '</div>';

              if (title.includes("estado") || title.includes("teléfono") || title.includes("email") || title.includes("documento")) {
                grupo1 += itemHtml;
              } else if (title.includes("departamento") || title.includes("ciudad") || title.includes("dirección")) {
                grupo2 += itemHtml;
              } else if (title.includes("notas") || title.includes("compra") || title.includes("ingreso")) {
                grupo3 += itemHtml;
              } else {
                otros += itemHtml;
              }
            }
          });

          var finalHtml = "";
          if (grupo1) finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Estado y Contacto</h5></div>' + grupo1;
          if (grupo2) finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Ubicación</h5></div>' + grupo2;
          if (grupo3) finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Otros Detalles</h5></div>' + grupo3;
          if (otros) finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Más Información</h5></div>' + otros;

          return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #f8f9fa; margin: 0;">').append(finalHtml) : false;
        }
      }
    },
    "columnDefs": [
      {
        "targets": 0, // # (ID)
        "orderable": true
      },
      {
        "targets": 1, // Nombre
        "orderable": true
      },
      {
        "targets": 2, // Documento
        "orderable": true
      },
      {
        "targets": 3, // Email
        "orderable": true
      },
      {
        "targets": 4, // Teléfono
        "orderable": true
      },
      {
        "targets": 5, // Dirección
        "orderable": true
      },
      {
        "targets": 6, // Estado
        "orderable": true
      },
      {
        "targets": 7, // Notas
        "orderable": true
      },
      {
        "targets": 8, // Última compra
        "orderable": true
      },
      {
        "targets": 9, // Acciones
        "orderable": false
      },
      {
        "targets": 10, // Ingreso
        "orderable": true
      }
    ],
    "order": [[0, 'desc']],
    "autoWidth": false,
    "language": {
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
      }
    }

  });

  // Inicializar Choices.js después de que la tabla cargue
  inicializarChoicesEstatus();

  // Filtro individual SOLO para tabla1
  tabla1.on('draw', function () {
    const filtroSeleccionado = $('#filtroEstatus1').val() ? $('#filtroEstatus1').val().toLowerCase() : "";

    tabla1.rows().every(function () {
      const row = $(this.node());
      const estatus = row.find('td:eq(8) select option:selected').text().toLowerCase();

      if (filtroSeleccionado === "" || estatus === filtroSeleccionado) {
        row.show();

      } else {
        row.hide();
      }

    });

    // Reinicializar Choices.js después de cada redibujado
    inicializarChoicesEstatus();
  });

  // Al cambiar el filtro, disparar
  $('#filtroEstatus1').on('change', function () {
    filterTable1();
  });

});


// BUSCADOR ESTATUS 2da Tabla
// Función para aplicar el filtro personalizado
var tabla2;

function filterTable2() {
  if(tabla2) tabla2.draw(); 
}

$(document).ready(function () {



  tabla2 = $('.tablas2').DataTable({
    responsive: {
      details: {
        renderer: function (api, rowIdx, columns) {
          var grupo1 = ""; // Estado, Teléfono
          var grupo2 = ""; // Ubicación
          var grupo3 = ""; // Otros
          var otros = "";  // Fallback

          $.each(columns, function (i, col) {
            if (col.hidden && col.title !== "Acciones") {
              var title = col.title.toLowerCase();
              var dataContent = col.data;

              // Si es la columna de notas, reconstruir el div editable
              if (title.includes("notas")) {
                // Obtener el nodo original de la celda para extraer el data-id
                var cellNode = api.cell(rowIdx, col.columnIndex).node();
                var dataId = $(cellNode).data('id');

                // Si encontramos el ID, renderizamos el div editable
                if (dataId) {
                  dataContent = '<div contenteditable="true" class="celda-notas" data-id="' + dataId + '">' + (col.data || '') + '</div>';
                }
              }

              var itemHtml = '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">' +
                '<span class="text-bold" style="color:#555;">' + col.title + ': </span>' +
                '<span class="pull-right" style="color:#333; width: 100%;">' + dataContent + '</span>' +
                '</div>';

              if (title.includes("estado") || title.includes("teléfono") || title.includes("email") || title.includes("documento")) {
                grupo1 += itemHtml;
              } else if (title.includes("departamento") || title.includes("ciudad") || title.includes("dirección")) {
                grupo2 += itemHtml;
              } else if (title.includes("notas") || title.includes("compra") || title.includes("ingreso")) {
                grupo3 += itemHtml;
              } else {
                otros += itemHtml;
              }
            }
          });

          var finalHtml = "";

          if (grupo1) {
            finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Estado y Contacto</h5></div>' + grupo1;
          }
          if (grupo2) {
            finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Ubicación</h5></div>' + grupo2;
          }
          if (grupo3) {
            finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Otros Detalles</h5></div>' + grupo3;
          }
          if (otros) {
            finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;"><h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Más Información</h5></div>' + otros;
          }

          return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #f8f9fa; margin: 0;">').append(finalHtml) : false;
        }
      }
    },

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

  });

  // Inicializar Choices.js después de que la tabla cargue
  inicializarChoicesEstatus();

  // Filtro individual SOLO para tabla2
  tabla2.on('draw', function () {
    const filtroSeleccionado = $('#filtroEstatus2').val() ? $('#filtroEstatus2').val().toLowerCase() : "";

    tabla2.rows().every(function () {
      const row = $(this.node());
      const estatus = row.find('td:eq(8) select option:selected').text().toLowerCase();

      if (filtroSeleccionado === "" || estatus === filtroSeleccionado) {
        row.show();

      } else {
        row.hide();
      }

    });

    // Reinicializar Choices.js después de cada redibujado
    inicializarChoicesEstatus();
  });

  // Al cambiar el filtro, disparar
  $('#filtroEstatus2').on('change', function () {
    filterTable2();
  });

});



// EDITAR NOTAS
// Permitir edición directa en campo "notas"
function inicializarEdicionNotas() {
  // Inicializar placeholder en celdas vacías (solo para elementos existentes al carga)
  // Para elementos dinámicos (móvil), el CSS :empty manejará el placeholder visualmente si está vacío
  $('.celda-notas').each(function () {
    const texto = $(this).text().trim();
    if (texto === '') {
      $(this).attr('data-placeholder', 'true');
    }
  });

  // Event delegation para focus (limpiar placeholder)
  $(document).on('focus', '.celda-notas', function () {
    $(this).removeAttr('data-placeholder');
  });

  // Event delegation para blur (guardar y restaurar placeholder)
  $(document).on('blur', '.celda-notas', function () {
    const nuevaNota = $(this).text().trim();
    const id = $(this).data('id');

    if (nuevaNota === '') {
      $(this).attr('data-placeholder', 'true');
    } else {
      $(this).removeAttr('data-placeholder');
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
        success: function (respuesta) {
          console.log('Nota actualizada:', respuesta);
        },
        error: function () {
          alert('Error al actualizar la nota');
        }
      });
    } else {
      console.error("No se encontró ID para guardar la nota");
    }
  });
}

// Ejecutar al cargar por primera vez
inicializarEdicionNotas();

/*=============================================
ACCIONES ADICIONALES (Restauradas)
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
  console.log("DEBUG: Clic en .btnEliminarCliente detectado");

  var idCliente = $(this).attr("idCliente");
  console.log("DEBUG: idCliente capturado:", idCliente);


  // Detectar si estamos en contactos o clientes
  var rutaActual = window.location.href;
  var ruta = "clientes";
  var mensaje = "¿Esta seguro de borrar el cliente?";

  if (rutaActual.indexOf("contactos") !== -1) {
    ruta = "contactos";
    mensaje = "¿Esta seguro de borrar el contacto?";
  }

  swal({
    title: mensaje,
    text: "¡Si no lo está puede cancelar la acción!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    cancelButtonText: 'Cancelar',
    confirmButtonText: 'Si, borrar!'
  }).then((result) => {
    console.log("DEBUG: Resultado de swal:", result);

    if (result.value) {
      console.log("DEBUG: Confirmación recibida, iniciando AJAX...");

      var datos = new FormData();
      datos.append("idClienteEliminar", idCliente);
      datos.append("ruta", ruta);

      $.ajax({
        url: "ajax/clientes.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {
          console.log("DEBUG: Respuesta del servidor AJAX:", respuesta);
          if (respuesta == "ok") {
            swal({
              icon: "success",
              title: "¡El cliente ha sido borrado correctamente!",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            }).then((result) => {
              if (result.value) {
                window.location = ruta;
              }
            });
          } else if (respuesta == "error_actividades") {
            swal({
              icon: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene actividades asociadas.",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            });
          } else if (respuesta == "error_ventas") {
            swal({
              icon: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene ventas asociadas.",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            });
          } else if (respuesta == "error_notas_credito") {
            swal({
              icon: "error",
              title: "¡No se puede eliminar!",
              text: "El cliente tiene notas crédito asociadas.",
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            });
          } else {
            swal({
              icon: "error",
              title: "Error",
              text: "No se pudo eliminar el cliente. " + respuesta,
              showConfirmButton: true,
              confirmButtonText: "Cerrar"
            });
          }
        }
      })
    }
  })
})

/*=============================================
REVISAR SI EL CLIENTE YA ESTA REGISTRADO
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
  })
})