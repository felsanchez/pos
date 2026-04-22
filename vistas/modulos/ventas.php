<style>
  .formulario-fechas-container {
    max-width: 300px;
    padding: 15px;
    border-radius: 10px;
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .formulario-fechas label {
    font-weight: 600;
    margin-top: 10px;
  }

  .formulario-fechas select,
  .formulario-fechas input[type="date"] {
    border-radius: 8px;
    margin-bottom: 10px;
  }

  .d-none {
    display: none !important;
  }

  /* Ocultar tabla de ventas hasta que DataTables termine de procesarla */
  #example:not(.datatable-ready) {
    visibility: hidden;
  }

  /* Mostrar un indicador de carga mientras se procesa */
  #example:not(.datatable-ready)+.dataTables_wrapper {
    position: relative;
  }

</style>


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
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar ventas</li>
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
            <button type="button" class="btn btn-default" id="daterange-btn">
              <span>
                <i class="fa fa-calendar"></i> Rango de fecha
              </span>
              <i class="fa fa-caret-down"></i>
            </button>

            <!-- Botón Buscar -->
            <button type="submit" class="btn btn-primary" title="Filtrar">
              <i class="fa fa-search"></i>
            </button>

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

        <div class="tabla-ventas table-responsive">
          <table id="tablaListaVentas" class="table table-bordered table-striped tablaVentasListado display nowrap" width="100%">

            <thead>
              <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Forma de pago</th>
                <th>Imagen</th>
                <th>Total</th>
                <th><i class="fa fa-magic"></i> Notas</th>
                <th><i class="fa fa-pencil-square"></i> Observación</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>

              <?php

              // Determinar filtros activos
              $fechaInicial = null;
              $fechaFinal = null;
              $clienteId = null;
              $usuarioId = null;
              $mensajeFiltro = "";

              // Filtro por fechas
              if (isset($_GET["fechaInicial"]) && !empty($_GET["fechaInicial"]) && isset($_GET["fechaFinal"]) && !empty($_GET["fechaFinal"])) {
                $fechaInicial = $_GET["fechaInicial"];
                $fechaFinal = $_GET["fechaFinal"];
                $mensajeFiltro .= "Filtrando desde $fechaInicial hasta $fechaFinal";
              }

              // Filtro por cliente
              if (isset($_GET["cliente"]) && !empty($_GET["cliente"])) {
                $clienteId = $_GET["cliente"];

                // Obtener nombre del cliente para mostrar
                $clienteInfo = ControladorClientes::ctrMostrarClientes("id", $clienteId);
                $nombreClienteFiltro = $clienteInfo["nombre"];

                if ($mensajeFiltro != "") {
                  $mensajeFiltro .= " | ";
                }
                $mensajeFiltro .= "Cliente: $nombreClienteFiltro";
              }

              // Filtro por usuario
              if (isset($_GET["usuario"]) && !empty($_GET["usuario"])) {
                $usuarioId = $_GET["usuario"];

                // Obtener nombre del usuario para mostrar
                $usuarioInfo = ControladorUsuarios::ctrMostrarUsuarios("id", $usuarioId);
                $nombreUsuarioFiltro = $usuarioInfo["nombre"];

                if ($mensajeFiltro != "") {
                  $mensajeFiltro .= " | ";
                }
                $mensajeFiltro .= "Usuario: $nombreUsuarioFiltro";
              }

              // Mostrar mensaje de filtros activos
              if ($mensajeFiltro != "") {
                echo "<p style='background: #d9edf7; padding: 10px; border-left: 4px solid #31708f; color: #31708f;'><i class='fa fa-filter'></i> $mensajeFiltro</p>";
              } else {
                echo "<p>Mostrando todas las ventas</p>";
              }

              // Obtener ventas según filtros
              $respuesta = ControladorVentas::ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, "venta");



              // Si hay filtro por cliente, filtrar el resultado
              if ($clienteId !== null) {
                $respuesta = array_filter($respuesta, function ($venta) use ($clienteId) {
                  return $venta["id_cliente"] == $clienteId;
                });
              }

              // Si hay filtro por usuario, filtrar el resultado
              if ($usuarioId !== null) {
                $respuesta = array_filter($respuesta, function ($venta) use ($usuarioId) {
                  return $venta["id_vendedor"] == $usuarioId;
                });
              }

              // Filtrar SIEMPRE para excluir facturas electrónicas (tienen numero_factura o resolucion_id)
              // Las FE tienen su propia vista dedicada
              $respuesta = array_filter($respuesta, function ($venta) {
                return empty($venta['numero_factura']) && empty($venta['resolucion_id']);
              });


              $contador = 1;
              foreach ($respuesta as $key => $value) {

                echo '<tr data-venta-id="' . $value['id'] . '">
                  <td>';

                if (!empty($value["numero_factura"])) {
                  // Si hay factura oficial, mostrarla GRANDE
                  echo '<span style="font-weight:bold; font-size:1.1em; color:#605ca8;">' . $value["numero_factura"] . '</span>';
                  // Y el código interno pequeño
                  echo '<br><span style="font-size:0.85em; color:#999;">Ref: ' . $formatoCodigoVenta . $value["codigo"] . '</span>';
                } else {
                  // Si no, mostrar código interno normal
                  echo $formatoCodigoVenta . $value["codigo"];
                }

                echo '</td>';

                // Usar nombres que ya vienen del JOIN en la consulta SQL
                $nombreCliente = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Cliente no encontrado";
                $nombreVendedor = !empty($value["nombre_vendedor"]) ? $value["nombre_vendedor"] : "Vendedor no encontrado";

                echo '<td>

                                  <span class="btnVerClienteDesdeVenta"
                                        data-toggle="modal"
                                        data-target="#modalEditarCliente"
                                        idCliente="' . $value["id_cliente"] . '"
                                        style="cursor: pointer; color: #337ab7; text-decoration: underline;">
                                      ' . $nombreCliente . '
                                  </span>
                              </td>';

                echo '<td>' . $nombreVendedor . '</td> 

                        <td>' . $moneda . ' ' . $value["metodo_pago"] . '</td>';

                // Validación de la foto
                if ($value["imagen"] != "") {
                  echo '<td><img src="' . $value["imagen"] . '" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="' . $value["imagen"] . '" data-idventa="' . $value["id"] . '"></td>';
                } else {
                  echo '<td><img src="vistas/img/ventas/default/sinventa.png" class="img-thumbnail img-ampliar-venta" width="40px" style="cursor: pointer;" data-imagen="vistas/img/ventas/default/sinventa.png" data-idventa="' . $value["id"] . '"></td>';
                }



                echo '<td>' . $moneda . ' ' . number_format($value["total"], 2) . '</td>';

                // Estado DIAN
                /*
                $estadoDian = isset($value["estado_dian"]) ? $value["estado_dian"] : 'pendiente';
                $badgeDian = '';
                if ($estadoDian == 'aceptada') {
                  $badgeDian = '<span class="badge bg-green">Aceptada</span>';
                } elseif ($estadoDian == 'rechazada') {
                  $badgeDian = '<span class="badge bg-red">Rechazada</span>';
                } elseif ($estadoDian == 'enviada') {
                  $badgeDian = '<span class="badge bg-yellow">Enviada</span>';
                } else {
                  $badgeDian = '<span class="badge bg-gray">Pendiente</span>';
                }
                echo '<td>' . $badgeDian . '</td>';
                */

                echo '<td>' . $value['notas'] . '</td>

                        <td contenteditable="true" class="celda-observacion" data-id="' . $value['id'] . '">' . $value['observacion'] . '</td>
                        
                        <td>' . $value["fecha"] . '</td>

                        <td>
                          <div class="btn-group col-acciones">';

                if (puedeAccion('ventas', 'editar')) {
                  echo '<button class="btn btn-warning btnEditarVenta" idVenta="' . $value["id"] . '" title="Ver Detalle de Venta" style="width: auto !important;">
                              <i class="fa fa-eye"></i>
                            </button>';
                }

                if (puedeAccion('ventas', 'eliminar')) {
                  echo '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '">
                                      <i class="fa fa-times"></i>
                                    </button>';
                }

                echo '</div>
                        </td>


                      </tr>';
              }
              ?>

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

        $("#tablaListaVentas").DataTable({
          "autoWidth": false,
          "initComplete": function(settings, json) {
             $(this.api().table().node()).addClass('datatable-ready');
             if (typeof quitarLoaderGlobal === 'function') {
                quitarLoaderGlobal();
             }
          },
          "order": [
            [8, "desc"]
          ], // Ordenar por Fecha por defecto
          "responsive": {
            "details": {
              "type": "inline",
              "renderer": function(api, rowIdx, columns) {
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

                  $.each(columns, function(i, col) {
                    // Solo mostrar columnas que DataTables realmente ocultó
                    if (!col.hidden) return;

                    hasHidden = true;
                    var colIdx = col.columnIndex;
                    var label = labels[colIdx] || col.title || ('Columna ' + colIdx);
                    var data  = col.data || '';

                    // Tratamiento especial para la columna de Observación (editable)
                    if (colIdx === 7) {
                      var obsTexto = $('<div>').html(data).text().trim();
                      finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
                      finalHtml += '<span class="text-bold" style="display:block;color:#555;margin-bottom:4px;"><i class="fa fa-pencil-square"></i> ' + label + ':</span>';
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