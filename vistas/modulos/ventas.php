<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">


<?php

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";

/*echo "<pre>";
var_dump($_GET);
echo "</pre>";
*/

$xml = ControladorVentas::ctrDescargarXML();

if ($xml) {

  rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");
  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
}
?>

<div class="content-wrapper">
  <section class="content-header">


    <h1>
      Administrar ventas
      <small>Control de facturación</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Ventas</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">


        <?php if (puedeAccion('ventas', 'crear')): ?>
          <a href="crear-venta">
            <button class="btn btn-primary">
              <i class="fa fa-plus"></i> Agregar venta
            </button>
          </a>
        <?php endif; ?>


        <div class="pull-right contenedor-filtros">

          <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

            <input type="hidden" name="ruta" value="ventas">
            <input type="hidden" name="fechaInicial" id="fechaInicial"
              value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
            <input type="hidden" name="fechaFinal" id="fechaFinal"
              value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

            <!-- Filtro por cliente -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Filtrar por Cliente:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select name="cliente" class="form-control select2 select-cliente" style="width: 100%;">
                  <option value="">Seleccionar cliente...</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

                  foreach ($clientes as $key => $valueCliente) {
                    $selected = (isset($_GET['cliente']) && $_GET['cliente'] == $valueCliente["id"]) ? 'selected' : '';
                    echo '<option value="' . e($valueCliente["id"]) . '" ' . $selected . '>' . e($valueCliente["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por usuario -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Filtrar por Vendedor:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select name="usuario" class="form-control select2 select-usuario" style="width: 100%;">
                  <option value="">Seleccionar usuario...</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                  foreach ($usuarios as $key => $valueUsuario) {
                    $selected = (isset($_GET['usuario']) && $_GET['usuario'] == $valueUsuario["id"]) ? 'selected' : '';
                    echo '<option value="' . e($valueUsuario["id"]) . '" ' . $selected . '>' . e($valueUsuario["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>


            <!-- Botón Rango de Fecha -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Filtrar por Fecha:</b></span>
              <button type="button" class="btn btn-default" id="daterange-btn">
                <span>
                  <i class="fa fa-calendar"></i> Rango de fecha
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
            </div>

            <!-- Botón Buscar -->


            <!-- Botón Limpiar -->
            <a href="index.php?ruta=ventas" class="btn btn-default" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </a>

          </form>

        </div>

        <style>
          @media (max-width: 767px) {
            .box-header .btn-primary:not([type="submit"]) {
              width: 100%;
              margin-bottom: 10px;
            }

            .pull-right.contenedor-filtros {
              float: none !important;
              width: 100%;
            }

            .pull-right.contenedor-filtros form {
              flex-direction: column;
              align-items: stretch !important;
              width: 100%;
              gap: 10px !important;
            }

            .pull-right.contenedor-filtros form .input-group,
            .pull-right.contenedor-filtros form div {
              width: 100% !important;
            }

            #daterange-btn,
            [type="submit"],
            .btn-default {
              width: 100% !important;
              margin-bottom: 5px;
            }
          }
        </style>

        <style>
          /* Botones de acción pequeños en móvil para ventas */
          @media (max-width: 767px) {
            .tabla-ventas .col-acciones .btn {
              padding: 1px 5px !important;
              font-size: 12px !important;
              line-height: 1.5 !important;
            }
          }
        </style>

        <style>
          /* Estilo campo observación — igual que en ordenes */
          .celda-observacion {
            background: #fff9e6;
            padding: 8px;
            border-radius: 3px;
            font-size: 12px;
            color: #666;
            border-left: 2px solid #f39c12;
            cursor: text;
            min-height: 30px;
          }

          .celda-observacion:empty:before {
            content: "Escribe una observación...";
            color: #999;
            font-style: italic;
          }

          .celda-observacion:focus {
            outline: 2px solid #f39c12;
            background: #fffef5;
          }
        </style>


      </div>

      <div class="box-body">

        <div class="tabla-ventas tablas tablaVentas table-responsive">
          <table id="tablaListaVentas" class="table table-bordered table-striped tablaVentasListado display nowrap"
            width="100%">

            <thead>
              <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Forma de pago</th>
                <th>Imagen</th>
                <th>Total</th>
                <th><i class="fa fa-magic"></i> Notas</th>
                <th>Observación</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <!-- Los datos se cargarán por DataTables Server-Side -->
            </tbody>

          </table>
        </div>




        <!-- Modal para ampliar/editar imagen de venta -->
        <div class="modal fade" id="modalAmpliarImagenVenta" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Imagen de la Venta</h4>
              </div>
              <div class="modal-body text-center">
                <img id="imagenVentaAmpliada" src="" class="img-responsive"
                  style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">

                <hr>

                <div class="form-group">
                  <label>Cambiar Imagen de la Venta</label>
                  <input type="file" class="form-control nuevaImagenVenta" accept="image/*">
                  <p class="help-block">Peso máximo de la imagen 2MB</p>
                </div>

                <input type="hidden" id="idVentaImagen">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btnGuardarImagenVenta">Guardar Imagen</button>
              </div>
            </div>
          </div>
        </div>


        <?php
        $eliminarVenta = new ControladorVentas();
        $eliminarVenta->ctrEliminarVenta();
        ?>

      </div>
    </div>
  </section>
</div>



<!--==========================================================================
MODAL EDITAR CLIENTE
===========================================================================-->

<!-- Modal -->
<div id="modalEditarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Ver cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">
          <div class="box-body">

            <!-- FILA 1: DATOS PERSONALES -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para nombre -->
                <div class="form-group">
                  <label>Nombre:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control input-lg" name="editarCliente" id="editarCliente" readonly>
                    <input type="hidden" id="idCliente" name="idCliente">
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-6">
                <!-- entrada para documento ID -->
                <div class="form-group">
                  <label>Documento:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId"
                      id="editarDocumentoId" placeholder="Documento" readonly>
                  </div>
                </div>
              </div>
            </div>

            <!-- FILA 2: CONTACTO -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para Email -->
                <div class="form-group">
                  <label>Email:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail"
                      placeholder="Correo Electrónico" readonly>
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-6">
                <!-- entrada para telefono -->
                <div class="form-group">
                  <label>Teléfono:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono"
                      data-inputmask="'mask':'(999) 999-9999'" data-mask placeholder="Celular" readonly>
                  </div>
                </div>
              </div>
            </div>

            <hr style="margin-top: 5px; margin-bottom: 15px;">

            <!-- FILA 3: UBICACIÓN Y ESTADO -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para la direccion -->
                <div class="form-group">
                  <label>Dirección:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-home"></i></span>
                    <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion"
                      placeholder="Dirección" required readonly>
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-6">
                <!-- entrada para la ciudad (Municipio) -->
                <div class="form-group">
                  <label>Municipio:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad"
                      placeholder="Municipio" readonly>
                  </div>
                </div>
              </div>
            </div>

            <!-- FILA 4: ESTADO Y NOTAS -->
            <div class="row">
              <div class="col-xs-12 col-md-6">
                <!-- entrada para estado -->
                <div class="form-group">
                  <label>Estado:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                    <input type="text" class="form-control input-lg" id="editarEstado" name="editarEstado" readonly
                      style="background-color: #f4f4f4; cursor: not-allowed;">
                  </div>
                </div>
              </div>

              <div class="col-xs-12 col-md-12">
                <!-- entrada para nota -->
                <div class="form-group">
                  <label>Notas:</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-sticky-note"></i></span>
                    <textarea class="form-control input-lg" name="editarNota" id="editarNota" placeholder="Notas"
                      readonly style="height: 80px; resize: none;"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <!--<button type="submit" class="btn btn-primary">Guardar cambios</button>-->
        </div>

      </form>
    </div>
  </div>
</div>


<!-- DateRangePicker -->
<script src="vistas/bower_components/moment/min/moment.min.js"></script>
<script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>


<!-- El script duplicado de Fecha ha sido removido y ahora opera centralizado en ventas.js -->

<!--Guardar observaciones-->
<script>
  $(document).on('blur', '.celda-observacion', function () {
    const idVenta = $(this).attr('data-id'); // .attr() para elementos dinámicos
    const nuevaObservacion = $(this).text().trim();
    console.log("Guardando observación:", nuevaObservacion, "para ID:", idVenta);
    $.ajax({
      url: "ajax/datatable-ventas.ajax.php",
      method: "POST",
      data: {
        csrf_token: $('meta[name="csrf-token"]').attr('content'),
        idVentaObservacion: idVenta,
        nuevaObservacion: nuevaObservacion
      },
      success: function (respuesta) {
        console.log("Respuesta del servidor:", respuesta);
      },
      error: function () {
        alert("Hubo un error al guardar la observación.");
      }
    });
  });
</script>

<!-- DataTables Personalizado para Ventas (Mobile First) -->
<script>
  $(document).ready(function () {
    // Retraso para asegurar que sobreescribimos cualquier inicialización global
    setTimeout(function () {
      if ($("#tablaListaVentas").length > 0) {
        // Destruir instancia previa si existe
        if ($.fn.DataTable.isDataTable('#tablaListaVentas')) {
          $('#tablaListaVentas').DataTable().destroy();
        }

        var table = $("#tablaListaVentas").DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": {
            "url": "ajax/ventas-listado.ajax.php",
            "type": "POST",
            "data": function (d) {
              d.csrf_token = $('meta[name="csrf-token"]').attr('content');
              d.fechaInicial = $("#fechaInicial").val();
              d.fechaFinal = $("#fechaFinal").val();
              d.clienteId = $("select[name='cliente']").val();
              d.usuarioId = $("select[name='usuario']").val();
            }
          },
          "createdRow": function (row, data, dataIndex) {
            // Añadir el atributo data-venta-id a cada fila
            if (data.DT_RowAttr && data.DT_RowAttr['data-venta-id']) {
              $(row).attr('data-venta-id', data.DT_RowAttr['data-venta-id']);
            }
          },

          // Escuchar cambios en los filtros para recargar la tabla
          "initComplete": function (settings, json) {
            $(this.api().table().node()).addClass('datatable-ready');
            if (typeof quitarLoaderGlobal === 'function') {
              quitarLoaderGlobal();
            }

            // Definir función global para recargar la tabla
            window.recargarTablaVentas = function() {
              table.ajax.reload();
            };

            // Recargar tabla al cambiar filtros
            $("select[name='cliente'], select[name='usuario']").on("change", function () {
              window.recargarTablaVentas();
            });

            // Recargar tabla al cambiar fechas (escuchando el cambio en los inputs ocultos)
            $("#fechaInicial, #fechaFinal").on("change", function () {
              window.recargarTablaVentas();
            });

            // Recargar tabla al hacer clic en el botón Buscar
            $(".btnBuscarFiltros").on("click", function () {
              window.recargarTablaVentas();
            });

            // Prevenir el submit del formulario si se presiona Enter
            $(".contenedor-filtros form").on("submit", function (e) {
              e.preventDefault();
              window.recargarTablaVentas();
            });

            // Inicializar DateRangePicker aquí mismo para tener acceso a la tabla
            if ($('#daterange-btn').length > 0 && typeof $.fn.daterangepicker !== 'undefined') {
              var urlParams = new URLSearchParams(window.location.search);
              var fechaInicialUrl = urlParams.get('fechaInicial');
              var fechaFinalUrl = urlParams.get('fechaFinal');

              $('#daterange-btn').daterangepicker({
                ranges: {
                  'Todos los documentos': [moment('2000-01-01'), moment()],
                  'Hoy': [moment(), moment()],
                  'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                  'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                  'Este mes': [moment().startOf('month'), moment().endOf('month')],
                  'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: fechaInicialUrl ? moment(fechaInicialUrl) : moment(),
                endDate: fechaFinalUrl ? moment(fechaFinalUrl) : moment()
              }, function (start, end) {
                $('#daterange-btn span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                $('#fechaInicial').val(start.format('YYYY-MM-DD'));
                $('#fechaFinal').val(end.format('YYYY-MM-DD'));
                window.recargarTablaVentas();
              });
            }
          },
          "order": [
            [8, "desc"]
          ], // Ordenar por Fecha por defecto
          "responsive": {
            "details": {
              "type": "inline",
              "renderer": function (api, rowIdx, columns) {
                // Mapeo de índice de columna → etiqueta y tipo especial
                var labels = {
                  2: 'Vendedor',
                  3: 'Forma de Pago',
                  4: 'Imagen',
                  5: 'Total',
                  6: 'Notas',
                  7: 'Observación',
                  8: 'Fecha'
                };

                var idVenta = $(api.row(rowIdx).node()).attr('data-venta-id') || '';
                var finalHtml = '';
                var hasHidden = false;

                $.each(columns, function (i, col) {
                  // Solo mostrar columnas que DataTables realmente ocultó
                  if (!col.hidden) return;

                  hasHidden = true;
                  var colIdx = col.columnIndex;
                  var label = labels[colIdx] || col.title || ('Columna ' + colIdx);
                  var data = col.data || '';

                  // Tratamiento especial para la columna de Observación (editable)
                  if (colIdx === 7) {
                    var obsTexto = $('<div>').html(data).text().trim();
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
                    finalHtml += '<span class="text-bold" style="block;color:#555;margin-bottom:4px;"> ' + label + ':</span>';
                    finalHtml += '<div class="celda-observacion" contenteditable="true" data-id="' + idVenta + '" style="min-height:24px;">' + obsTexto + '</div>';
                    finalHtml += '</div>';
                    return;
                  }

                  // Tratamiento especial para la columna de Notas (quitar HTML)
                  if (colIdx === 6) {
                    var notasTexto = $('<div>').html(data).text().trim();
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
                    finalHtml += '<span class="text-bold" style="color:#555;"><i class="fa fa-magic"></i> ' + label + ': </span>';
                    finalHtml += '<span style="color:#333;">' + (notasTexto || '<em style="color:#999;">Sin notas</em>') + '</span>';
                    finalHtml += '</div>';
                    return;
                  }

                  // Columnas genéricas
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
            { "targets": 0, "responsivePriority": 1 }, // Código
            { "targets": 9, "responsivePriority": 2, "orderable": false }, // Acciones
            { "targets": 1, "responsivePriority": 3 }, // Cliente
            { "targets": 2, "responsivePriority": 4 }, // Vendedor
            { "targets": 3, "responsivePriority": 5 }, // Forma de Pago
            { "targets": 4, "responsivePriority": 6 }, // Imagen
            { "targets": 5, "responsivePriority": 7 }, // Total
            { "targets": 6, "responsivePriority": 8 }, // Notas
            { "targets": 7, "responsivePriority": 9 }, // Observación
            { "targets": 8, "responsivePriority": 10 } // Fecha
          ],
          "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
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

<script>
  $(document).ready(function () {
    setTimeout(function () {
      if ($('#tablaVentasEspecial').length > 0) {
        $('#tablaVentasEspecial').DataTable({
          "order": [[0, "asc"]],
          "columnDefs": [
            { "targets": "_all", "responsivePriority": 1 },
            { "targets": 9, "orderable": false },
            { "targets": 0, "className": "text-center" }
          ],
          "responsive": {
            "details": {
              "type": "inline",
              "renderer": function (api, rowIdx, columns) {
                var rowData = api.row(rowIdx).data();
                var html = '<div class="row" style="padding: 10px; background-color: #f8f9fa; margin: 0; border: 1px solid #ddd;">';
                html += '<div class="col-xs-12" style="border-bottom: 2px solid #3c8dbc; margin-bottom:10px;"><h5 style="font-weight:bold; color:#3c8dbc; margin:5px 0;">Detalles de Venta</h5></div>';
                html += '<div class="col-xs-12" style="padding:5px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between;"><b>Cliente:</b> <span>' + (rowData[1] || 'N/A') + '</span></div>';
                html += '<div class="col-xs-12" style="padding:5px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between;"><b>Vendedor:</b> <span>' + (rowData[2] || 'N/A') + '</span></div>';
                html += '<div class="col-xs-12" style="padding:5px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between;"><b>Pago:</b> <span>' + (rowData[3] || 'N/A') + '</span></div>';
                html += '<div class="col-xs-12" style="padding:5px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between;"><b>Fecha:</b> <span>' + (rowData[8] || 'N/A') + '</span></div>';
                html += '<div class="col-xs-12" style="padding:8px 0; border-bottom:1px solid #eee;"><b>Notas:</b> <div style="color:#666; font-size:12px; margin-top:4px;">' + (rowData[6] || '<em>Sin notas</em>') + '</div></div>';
                html += '<div class="col-xs-12" style="padding:8px 0;"><b>Observación:</b> <div style="color:#666; font-size:12px; margin-top:4px;">' + (rowData[7] || '<em>Sin observación</em>') + '</div></div>';
                html += '</div>';
                return html;
              }
            }
          },
          "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sSearch": "Buscar:",
            "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
          },
          "dom": '<"row" <"col-sm-6" l><"col-sm-6" f>rt <"row" <"col-sm-6" i><"col-sm-6" p>>',
          "autoWidth": false
        });
      }
    }, 500);
  });
</script>


<!-- Ampliar foto -->
<script>
  // Previsualizar nueva imagen cuando se selecciona
  $(".nuevaImagenVenta").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaImagenVenta").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaImagenVenta").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen no debe pesar más de 2MB!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else {
        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function (event) {
          var rutaImagen = event.target.result;
          $("#imagenVentaAmpliada").attr("src", rutaImagen);
        });
      }
    }
  });

  // Guardar la nueva imagen de la venta
  $(document).on("click", ".btnGuardarImagenVenta", function () {

    var idVenta = $("#idVentaImagen").val();
    var imagen = $(".nuevaImagenVenta")[0].files[0];

    console.log("ID al guardar:", idVenta); // Para debug
    console.log("Imagen al guardar:", imagen); // Para debug

    if (!imagen) {
      swal({
        title: "Advertencia",
        text: "No has seleccionado ninguna imagen",
        type: "warning",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    if (!idVenta) {
      swal({
        title: "Error",
        text: "No se pudo obtener el ID de la venta",
        type: "error",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    var datos = new FormData();
    datos.append("idVentaImagen", idVenta);
    datos.append("nuevaImagenVenta", imagen);

    // Mostrar loading
    swal({
      title: 'Cargando...',
      allowOutsideClick: false,
      onBeforeOpen: () => {
        swal.showLoading()
      }
    });

    $.ajax({
      url: "ajax/ventas.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (respuesta) {
        console.log("Respuesta del servidor:", respuesta); // Para debug

        if (respuesta == "ok") {
          swal({
            type: "success",
            title: "¡La imagen ha sido actualizada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function (result) {
            if (result.value) {
              $("#modalAmpliarImagenVenta").modal("hide");
              window.location = "ventas";
            }
          });
        } else {
          swal({
            type: "error",
            title: "Error al actualizar la imagen",
            text: JSON.stringify(respuesta),
            confirmButtonText: "Cerrar"
          });
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log("Error AJAX:", textStatus, errorThrown); // Para debug
        console.log("Respuesta:", jqXHR.responseText); // Para debug

        swal({
          type: "error",
          title: "Error en la petición",
          text: "Por favor revisa la consola para más detalles",
          confirmButtonText: "Cerrar"
        });
      }
    });
  });


  // Ampliar imagen de venta al hacer clic
  $(document).on("click", ".img-ampliar-venta, .btnVerFotoVenta", function () {
    var rutaImagen = $(this).attr("data-imagen");
    var idVenta = $(this).attr("data-idventa");

    console.log("ID Venta:", idVenta); // Para debug
    console.log("Ruta Imagen:", rutaImagen); // Para debug

    $("#imagenVentaAmpliada").attr("src", rutaImagen);
    $("#idVentaImagen").val(idVenta);
    $(".nuevaImagenVenta").val("");
    $("#modalAmpliarImagenVenta").modal("show");
  });
</script>



<!-- Abrir modal de clientes desde ordenes -->
<script>
  $(document).on("click", ".btnVerClienteDesdeVenta", function () {

    var idCliente = $(this).attr("idCliente");

    var datos = new FormData();
    datos.append("idCliente", idCliente);

    $.ajax({
      url: "ajax/clientes.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "text",
      success: function (respuesta) {

        // Extraer solo el JSON
        var jsonStart = respuesta.indexOf('{');
        var jsonString = respuesta.substring(jsonStart);
        var data = JSON.parse(jsonString);

        // Llenar el modal
        $("#idCliente").val(data["id"]);
        $("#editarCliente").val(data["nombre"]);
        $("#editarDocumentoId").val(data["documento"]);
        $("#editarEmail").val(data["email"]);
        $("#editarTelefono").val(data["telefono"]);
        $("#editarDireccion").val(data["direccion"]);
        $("#editarNotas").val(data["notas"]);

        // AGREGAR ESTA LÍNEA para preseleccionar el estado
        $("#editarEstado").val(data["estatus"]);

        // Si tienes más campos, agrégalos aquí:
        $("#editarDepartamento").val(data["departamento"]);
        $("#editarCiudad").val(data["ciudad"]);

        // Abrir el modal
        $('#modalEditarCliente').modal('show');
      }
    });
  });
</script>