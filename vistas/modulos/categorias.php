<style>
  /* 
  . LÓGICA DESKTOP-FIRST: Ocultar botón de expansión por defecto en Categorías */ .tablaCategorias td.dtr-control:before, .tablaCategorias th.dtr-control:before { display: none !important; content: "" !important; } .tablaCategorias td.dtr-control, .tablaCategorias th.dtr-control { padding-left: 8px !important; cursor: default !important; } /* 2. ACTIVACIÓN EXCLUSIVA PARA MÓVIL (Menos de 767px) */ @media (max-width: 767px) { .tablaCategorias td.dtr-control { position: relative !important; padding-left: 30px !important; cursor: pointer !important; } .tablaCategorias td.dtr-control:before { top: 50% !important; left: 5px !important; height: 18px !important; width: 18px !important; margin-top: -9px !important; display: block !important; position: absolute !important; color: white !important; border: 2px solid white !important; border-radius: 14px !important; box-shadow: 0 0 3px #444 !important; box-sizing: content-box !important; text-align: center !important; text-indent: 0 !important; font-family: 'Courier New', Courier, monospace !important; font-weight: bold !important; line-height: 18px !important; content: '+' !important; background-color: #3c8dbc !important; /* Azul al estar contraído (+) */ } .tablaCategorias tr.parent td.dtr-control:before { content: '-' !important; background-color: #dd4b39 !important; /* Rojo al estar expandido (-) */ } .tablaCategorias .btn-group .btn { padding: 1px 5px; font-size: 12px; line-height: 1.5; border-radius: 3px; } }
  */

  /* 1. LÓGICA DESKTOP-FIRST: Ocultar botón de expansión por defecto en Categorías */
  .tablaCategorias td.dtr-control:before,
  .tablaCategorias th.dtr-control:before {
    display: none !important;
    content: "" !important;
  }

  .tablaCategorias td.dtr-control,
  .tablaCategorias th.dtr-control {
    padding-left: 8px !important;
    cursor: default !important;
  }

  /* 2. ACTIVACIÓN EXCLUSIVA PARA MÓVIL (Menos de 767px) */
  @media (max-width: 767px) {
    .tablaCategorias td.dtr-control {
      position: relative !important;
      padding-left: 30px !important;
      cursor: pointer !important;
    }

    .tablaCategorias td.dtr-control:before {
      top: 50% !important;
      left: 5px !important;
      height: 18px !important;
      width: 18px !important;
      margin-top: -9px !important;
      display: block !important;
      position: absolute !important;
      color: white !important;
      border: 2px solid white !important;
      border-radius: 14px !important;
      box-shadow: 0 0 3px #444 !important;
      box-sizing: content-box !important;
      text-align: center !important;
      text-indent: 0 !important;
      font-family: 'Courier New', Courier, monospace !important;
      font-weight: bold !important;
      line-height: 18px !important;
      content: '+' !important;
      background-color: #3c8dbc !important; /* Azul al estar contraído (+) */
    }

    .tablaCategorias tr.parent td.dtr-control:before {
      content: '-' !important;
      background-color: #dd4b39 !important; /* Rojo al estar expandido (-) */
    }

    .tablaCategorias .btn-group .btn {
      padding: 1px 5px;
      font-size: 12px;
      line-height: 1.5;
      border-radius: 3px;
    }
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Administrar categorías</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar categorías</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <?php if (puedeAccion('categorias', 'crear')): ?>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCategoria">
            <i class="fa fa-plus"></i> Agregar categoría
          </button>
        <?php endif; ?>
      </div>

      <div class="box-body table-responsive">
        <table id="tablaCategoriasListado" class="table table-bordered table-striped tablaCategorias display nowrap" width="100%">
          <thead>
              <th>Categoría</th>
              <th>Productos</th>
              <th style="width: 100px">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $item = null;
            $valor = null;
            $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

            foreach ($categorias as $key => $value) {
              $totalProductos = ModeloCategorias::mdlContarProductosPorCategoria($value["id"]);

              echo '<tr>
                      <td class="text-uppercase">' . $value["categoria"] . '</td> 
                      <td><span class="badge bg-blue">' . $totalProductos . '</span></td> 
                      <td>
                        <div class="btn-group">';
              
              if (puedeAccion('categorias', 'editar')) {
                echo '<button class="btn btn-warning btnEditarCategoria" idCategoria="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarCategoria"><i class="fa fa-pencil"></i></button>';
              }

              if (puedeAccion('categorias', 'eliminar')) {
                echo '<button class="btn btn-danger btnEliminarCategoria" idCategoria="' . $value["id"] . '"><i class="fa fa-times"></i></button>';
              }

              echo '    </div>
                      </td> 
                    </tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>


<!--=====================================
MODAL AGREGAR CATEGORIA
======================================-->

<!-- Modal -->
<div id="modalAgregarCategoria" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar categoría</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaCategoria" id="nuevaCategoria"
                  placeholder="Ingresar categoría" required>

              </div>

            </div>



          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar categoría</button>

        </div>


        <?php

        $crearCategoria = new ControladorCategorias();
        $crearCategoria->ctrCrearCategoria();

        ?>

      </form>

    </div>


  </div>

</div>


<!--==========================================================================
MODAL EDITAR CATEGORIA
===========================================================================-->

<!-- Modal -->
<div id="modalEditarCategoria" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar categoría</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>

                <input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>

                <input type="hidden" name="idCategoria" id="idCategoria" required>

              </div>

            </div>



          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>


        <?php

        $editarCategoria = new ControladorCategorias();
        $editarCategoria->ctrEditarCategoria();

        ?>

      </form>

    </div>


  </div>

</div>


<?php

$borrarCategoria = new ControladorCategorias();
$borrarCategoria->ctrBorrarCategoria();

?>

<!-- Script de inicialización de Categorías -->
<script>
$(document).ready(function () {
  setTimeout(function () {
    if ($("#tablaCategoriasListado").length > 0) {
      if ($.fn.DataTable.isDataTable('#tablaCategoriasListado')) {
        $('#tablaCategoriasListado').DataTable().destroy();
      }

      $("#tablaCategoriasListado").DataTable({
        "autoWidth": false,
        "responsive": {
          "details": {
            "type": "column",
            "target": 0, // En la primera columna (Categoría)
            "renderer": function (api, rowIdx, columns) {
              if ($(window).width() >= 768) return false;

              var nombre = columns[0].data || '';
              var productos = columns[1].data || '';
              var finalHtml = '';

              // SECCION 1: Información Categoría
              finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #dd4b39;">';
              finalHtml += '<h5 style="font-weight:bold; color:#dd4b39; margin:0;">Información Categoría</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold">Nombre: </span><span class="pull-right">' + nombre + '</span></div>';

              // SECCION 2: Información Productos
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Información Productos</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold">Total productos: </span><span class="pull-right">' + productos + '</span></div>';

              return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0;">').append(finalHtml) : false;
            }
          }
        },
        "columnDefs": [
          { "targets": 0, "className": 'dtr-control', "responsivePriority": 1 },
          { "targets": 2, "responsivePriority": 1, "orderable": false },
          { "targets": 1, "responsivePriority": 2 }
        ],
        "language": {
          "sProcessing": "Procesando...",
          "sLengthMenu": "Mostrar _MENU_ registros",
          "sZeroRecords": "No se encontraron resultados",
          "sEmptyTable": "Ningún dato disponible en esta tabla",
          "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
          "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
          "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
          "sSearch": "Buscar:",
          "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
          }
        }
      });
    }
  }, 200);
});
</script>