$(document).ready(function () {

  // Mover modal al body para evitar conflictos de z-index
  $('#modalEditarCategoria').appendTo("body");

  // Corrección de z-index y backdrops para modalEditarCategoria
  $('#modalEditarCategoria').off('show.bs.modal').on('show.bs.modal', function () {
    $(this).appendTo('body');
  });

  $('#modalEditarCategoria').off('shown.bs.modal').on('shown.bs.modal', function () {
    $(this).css('z-index', 1060);
    var backdrops = $('.modal-backdrop');
    if (backdrops.length >= 2) {
      $(backdrops[0]).css('z-index', 1040);
      $(backdrops[backdrops.length - 1]).css('z-index', 1055);
    }
  });

  $('#modalEditarCategoria').off('hidden.bs.modal').on('hidden.bs.modal', function () {
    if ($('#modalGestionarCategorias').hasClass('in')) {
      $('body').addClass('modal-open');
    }
  });



  /*=============================================
  CARGAR TABLA DE ARTICULOS (DATATABLES SERVER-SIDE)
  =============================================*/
  var tablaArticulos = $("#tablaArticulosConocimiento").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
      "url": "ajax/conocimiento.ajax.php",
      "type": "POST",
      "data": function (d) {
        d.categoriaFiltro = $("#filtroCategoriaArticulo").val();
      }
    },
    "autoWidth": false,
    "responsive": {
      "details": {
        "type": "inline",
        "renderer": function (api, rowIdx, columns) {
          var finalHtml = '';
          var hasHidden = false;

          $.each(columns, function (i, col) {
            if (!col.hidden) return;

            hasHidden = true;
            var label = col.title || ('Columna ' + col.columnIndex);
            var data = col.data || '';

            finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
            finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
            finalHtml += '<span style="color:#333;">' + data + '</span>';
            finalHtml += '</div>';
          });

          if (!hasHidden) return false;
          return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
        }
      }
    },
    "columnDefs": [
      { "targets": 0, "responsivePriority": 1 }, // Título
      { "targets": 1, "responsivePriority": 4 }, // Categoría
      { "targets": 2, "responsivePriority": 5 }, // Palabras Clave
      { "targets": 3, "responsivePriority": 6 }, // Fecha Creación
      { "targets": 4, "responsivePriority": 2, "orderable": false } // Acciones
    ],
    "language": {
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ningún dato disponible en esta tabla",
      "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sInfoPostFix": "",
      "sSearch": "Buscar:",
      "sUrl": "",
      "sInfoThousands": ",",
      "sLoadingRecords": "Cargando...",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
      },
      "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
      }
    }
  });

  /*=============================================
  FILTRAR POR CATEGORIA
  =============================================*/
  $("#filtroCategoriaArticulo").change(function () {
    tablaArticulos.ajax.reload();
  });

  /*=============================================
  VER ARTICULO
  =============================================*/
  $(document).on("click", ".btnVerArticulo", function () {
    var idArticulo = $(this).attr("idArticulo");
    
    var datos = new FormData();
    datos.append("idArticulo", idArticulo);

    $.ajax({
      url: "ajax/conocimiento.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (respuesta) {
        $("#verArticuloTitulo").text(respuesta.titulo);
        $("#verArticuloCategoria").text(respuesta.nombre_categoria);
        $("#verArticuloFecha").text(respuesta.created_at);
        $("#verArticuloContenido").html(respuesta.contenido);

        if (respuesta.palabras_clave) {
          $("#verArticuloKeywordsContainer").show();
          $("#verArticuloKeywords").text(respuesta.palabras_clave);
        } else {
          $("#verArticuloKeywordsContainer").hide();
        }
      }
    });
  });

  /*=============================================
  EDITAR ARTICULO
  =============================================*/
  $(document).on("click", ".btnEditarArticulo", function () {
    var idArticulo = $(this).attr("idArticulo");
    
    var datos = new FormData();
    datos.append("idArticulo", idArticulo);

    $.ajax({
      url: "ajax/conocimiento.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (respuesta) {
        $("#idArticulo").val(respuesta.id);
        $("#editarArticuloTitulo").val(respuesta.titulo);
        $("#editarArticuloCategoria").val(respuesta.id_categoria);
        $("#editarArticuloKeywords").val(respuesta.palabras_clave);

        $("#editarArticuloContenido").val(respuesta.contenido);
      }
    });
  });

  /*=============================================
  ACTIVAR ARTICULO
  =============================================*/
  $(document).on("click", ".btnActivarArticulo", function () {
    var idArticulo = $(this).attr("idArticulo");
    var estadoArticulo = $(this).attr("estadoArticulo");
    var boton = $(this);
    var fila = boton.closest('tr');

    fila.css('opacity', '0.5');
    boton.prop('disabled', true);
    boton.html('<i class="fa fa-spinner fa-spin"></i>');

    var datos = new FormData();
    datos.append("activarId", idArticulo);
    datos.append("activarArticulo", estadoArticulo);

    $.ajax({
      url: "ajax/conocimiento.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      success: function (respuesta) {
        setTimeout(function () {
          if (estadoArticulo == 0) {
            boton.removeClass('btn-success').addClass('btn-danger').html('Inactivo').attr('estadoArticulo', 1);
          } else {
            boton.removeClass('btn-danger').addClass('btn-success').html('Activo').attr('estadoArticulo', 0);
          }

          fila.css('background-color', '#d4edda');
          fila.animate({ opacity: 1 }, 300);

          setTimeout(function () {
            fila.css('background-color', '');
          }, 1000);

          boton.prop('disabled', false);
        }, 400);
      }
    });
  });

  /*=============================================
  ELIMINAR ARTICULO
  =============================================*/
  $(document).on("click", ".btnEliminarArticulo", function () {
    var idArticulo = $(this).attr("idArticulo");

    swal({
      title: '¿Está seguro de borrar el artículo?',
      text: "¡Si no lo está puede cancelar la acción!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar artículo'
    }).then((result) => {
      if (result.value) {
        var datos = new FormData();
        datos.append("idArticuloEliminar", idArticulo);

        $.ajax({
          url: "ajax/conocimiento.ajax.php",
          method: "POST",
          data: datos,
          cache: false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function (respuesta) {
            if (respuesta == "ok") {
              swal({
                type: "success",
                title: "¡Eliminado!",
                text: "El artículo ha sido eliminado correctamente.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
              }).then((result) => {
                if (result.value) {
                  tablaArticulos.ajax.reload();
                }
              });
            } else {
              swal({
                type: "error",
                title: "Error",
                text: "No se pudo eliminar el artículo.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
              });
            }
          }
        });
      }
    });
  });

  /*=============================================
  ACTIVAR CATEGORIA
  =============================================*/
  $(document).on("click", ".btnActivarCat", function () {
    var idCat = $(this).attr("idCat");
    var estadoCat = $(this).attr("estadoCat");
    var boton = $(this);
    var fila = boton.closest('tr');

    fila.css('opacity', '0.5');
    boton.prop('disabled', true);
    boton.html('<i class="fa fa-spinner fa-spin"></i>');

    var datos = new FormData();
    datos.append("activarCatId", idCat);
    datos.append("activarCategoria", estadoCat);

    $.ajax({
      url: "ajax/conocimiento.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      success: function (respuesta) {
        setTimeout(function () {
          if (estadoCat == 0) {
            boton.removeClass('btn-success').addClass('btn-danger').html('Inactivo').attr('estadoCat', 1);
          } else {
            boton.removeClass('btn-danger').addClass('btn-success').html('Activo').attr('estadoCat', 0);
          }

          fila.css('background-color', '#d4edda');
          fila.animate({ opacity: 1 }, 300);

          setTimeout(function () {
            fila.css('background-color', '');
          }, 1000);

          boton.prop('disabled', false);
        }, 400);
      }
    });
  });

  /*=============================================
  EDITAR CATEGORIA (MOSTRAR MODAL)
  =============================================*/
  $(document).on("click", ".btnEditarCat", function () {
    var idCat = $(this).attr("idCat");
    var nombreCat = $(this).attr("nombreCat");

    $("#modalEditarCategoria #idCategoria").val(idCat);
    $("#modalEditarCategoria #editarCategoriaNombre").val(nombreCat);
    $("#modalEditarCategoria").modal("show");
  });

  /*=============================================
  ELIMINAR CATEGORIA
  =============================================*/
  $(document).on("click", ".btnEliminarCat", function () {
    var idCat = $(this).attr("idCat");
    var fila = $(this).closest("tr");

    swal({
      title: '¿Está seguro de borrar la categoría?',
      text: "¡Si no lo está puede cancelar la acción!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, borrar categoría'
    }).then((result) => {
      if (result.value) {
        var datos = new FormData();
        datos.append("idCategoriaEliminar", idCat);

        $.ajax({
          url: "ajax/conocimiento.ajax.php",
          method: "POST",
          data: datos,
          cache: false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function (respuesta) {
            if (respuesta == "ok") {
              swal({
                type: "success",
                title: "¡Eliminada!",
                text: "La categoría ha sido eliminada correctamente.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
              }).then((result) => {
                if (result.value) {
                  fila.remove();
                }
              });
            } else if (respuesta == "tiene_articulos") {
              swal({
                type: "error",
                title: "¡No se puede eliminar!",
                text: "No se puede eliminar la categoría porque tiene artículos asociados.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
              });
            } else {
              swal({
                type: "error",
                title: "Error",
                text: "No se pudo eliminar la categoría.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
              });
            }
          }
        });
      }
    });
  });

});
