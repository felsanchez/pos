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


</style>

<style>
  /* Solo muestra el botón en móvil */
  .solo-movil {
    display: none;
  }

  @media (max-width: 767px) {
    .solo-movil {
      display: inline-block !important;
    }
  }
</style>

<!--Agregar espacio entre los btones en móvil-->
<style>
  @media (max-width: 767px) {
    .solo-movil {
      margin-left: 3px !important;
    }
  }
</style>

<style>
  /* Botones de acción pequeños en móvil */
  @media (max-width: 767px) {
    .tablaOrdenes tbody td .btn {
      padding: 1px 5px !important;
      font-size: 12px !important;
      line-height: 1.5 !important;
    }
  }
</style>

<!-- Estilos para campo observación -->
<style>
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

<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">


<?php

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";
$mensajeRecibido = !empty($configuracion["mensaje_recibido"]) ? $configuracion["mensaje_recibido"] : "Su pedido ha sido recibido";
$mensajeProcesado = !empty($configuracion["mensaje_procesado"]) ? $configuracion["mensaje_procesado"] : "Su pedido ha sido procesado";
$mensajeConfirmado = !empty($configuracion["mensaje_confirmado"]) ? $configuracion["mensaje_confirmado"] : "Su pedido ha sido confirmado";


$xml = ControladorVentas::ctrDescargarXML();

if ($xml) {

  rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");
  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
}
?>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar orden de venta
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Ordenes de Venta</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">


        <?php if (puedeAccion('ordenes', 'crear')): ?>
          <a href="crear-orden" class="btn btn-primary" title="Agregar orden">
            <i class="fa fa-plus"></i> <span class="hidden-xs">Agregar orden</span>
          </a>
        <?php endif; ?>


        <div class="pull-right contenedor-filtros">

          <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

            <input type="hidden" name="ruta" value="ordenes">
            <input type="hidden" name="fechaInicial" id="fechaInicial"
              value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
            <input type="hidden" name="fechaFinal" id="fechaFinal"
              value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

            <?php CSRF::insertToken(); ?>

            <!-- Filtro por cliente -->
            <?php
            $clientesData = ControladorClientes::ctrMostrarClientes(null, null);
            $totalClientes = is_array($clientesData) ? count($clientesData) : 0;
            ?>
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Filtrar Cliente:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-user text-primary"></i></span>
                <select name="cliente" id="filtroClienteOrdenes"
                  style="width: 200px; border: 1px solid #ccc; height: 34px; padding: 6px 12px;"></select>
              </div>
            </div>

            <!-- Filtro por usuario -->
            <?php
            $usuariosData = ControladorUsuarios::ctrMostrarUsuarios(null, null);
            $totalUsuarios = is_array($usuariosData) ? count($usuariosData) : 0;
            ?>
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Vendedor:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select name="usuario" id="filtroUsuarioOrdenes"
                  style="width: 180px; border: 1px solid #ccc; height: 34px; padding: 6px 12px;"></select>
              </div>
            </div>

            <!-- Botón Rango de Fecha -->
            <div class="form-group" style="margin-bottom: 0;">
              <button type="button" class="btn btn-default" id="daterange-btn">
                <span>
                  <i class="fa fa-calendar"></i> Rango
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
            </div>

            <!-- Botones de Acción (Separados para mantener gap consistente con Ventas) -->
            <button type="submit" class="btn btn-primary" title="Filtrar">
              <i class="fa fa-search"></i>
            </button>

            <a href="index.php?ruta=ordenes" class="btn btn-default" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </a>



          </form>

        </div>

      </div>

      <div class="box-body">

        <div class="tabla-ordenes table-responsive">
          <table class="table table-bordered table-striped dt-responsive tablaOrdenes display nowrap" width="100%">

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
                <th>Seguimiento</th>
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
              if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"])) {
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
                echo "<p>Mostrando todas las órdenes</p>";
              }

              //$respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);
              $respuesta = ControladorVentas::ctrRangoFechasVentasPorEstado($fechaInicial, $fechaFinal, "orden");

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


              foreach ($respuesta as $key => $value) {

                echo '<tr data-orden-id="' . e($value['id']) . '">
                        <td>' . e($formatoCodigoVenta) . e($value["codigo"]) . '</td>';

                /*
                 $itemCliente = "id";
                 $valorCliente = $value["id_cliente"];
                 $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
                 echo'<td>'.$respuestaCliente["nombre"].'</td>';
                 */

                $itemCliente = "id";
                $valorCliente = $value["id_cliente"];
                $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

                echo '<td>

                                  <span class="btnVerClienteDesdeVenta"
                                        data-toggle="modal"
                                        data-target="#modalEditarCliente"
                                        idCliente="' . e($value["id_cliente"]) . '"
                                        style="cursor: pointer; color: #337ab7; text-decoration: underline;">
                                      ' . e($respuestaCliente["nombre"]) . '
                                  </span>
                              </td>';

                $itemUsuario = "id";
                $valorUsuario = $value["id_vendedor"];
                $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
                echo '<td>' . e($respuestaUsuario["nombre"]) . '</td>';

                echo '<td>' . e($moneda) . ' ' . e($value["metodo_pago"]) . '</td>';
                
                // Validación de la foto
                if ($value["imagen"] != "") {
                  echo '<td><img src="' . $value["imagen"] . '" class="img-thumbnail img-ampliar-orden" width="40px" style="cursor: pointer;" data-imagen="' . $value["imagen"] . '" data-idventa="' . $value["id"] . '"></td>';
                } else {
                  echo '<td><img src="vistas/img/ventas/default/sinventa.png" class="img-thumbnail img-ampliar-orden" width="40px" style="cursor: pointer;" data-imagen="vistas/img/ventas/default/sinventa.png" data-idventa="' . $value["id"] . '"></td>';
                }

                echo '<td>' . e($moneda) . ' ' . e(number_format($value["total"], 2)) . '</td>

                        <td class="celda-nota" data-id="' . e($value['id']) . '">' . e($value['notas']) . '</td>

                        <td contenteditable="true" class="celda-observacion" data-id="' . e($value['id']) . '">' . e($value['observacion']) . '</td>

                         <td>' . e($value["fecha"]) . '</td>';

                // Columna SEGUIMIENTO
                echo '<td style="white-space:nowrap; text-align:center;">';

                // Botón 1: Recibido
                if (isset($value["seguimiento_recibido"]) && $value["seguimiento_recibido"] == 1) {
                  echo '<span class="label label-success" style="margin-right:5px;">Enviado (R)</span>';
                } else {
                  // Check if keys exist to avoid warnings if columns missing
                  $recibido = isset($value["seguimiento_recibido"]) ? $value["seguimiento_recibido"] : 0;
                  if ($recibido == 1) {
                    echo '<span class="label label-success" style="margin-right:5px;">Enviado (R)</span>';
                  } else {
                    if (puedeAccion('ordenes', 'editar')) {
                      echo '<button class="btn btn-default btn-xs btnSeguimientoRecibido" 
                                  idOrden="' . e($value["id"]) . '" 
                                  codigoOrden="' . e($value["codigo"]) . '"
                                  cliente="' . e($respuestaCliente["nombre"]) . '"
                                  telefono="' . e($respuestaCliente["telefono"]) . '"
                                  data-mensaje-recibido="' . e(htmlspecialchars($mensajeRecibido)) . '"
                                  style="margin-right:5px; border: 1px solid #ccc; color: green; width: auto !important;" 
                                  title="Enviar mensaje: Pedido Recibido">
                                  1er mensaje
                              </button>';
                    }
                  }
                }

                // Botón 2: Procesado
                if (isset($value["seguimiento_procesado"]) && $value["seguimiento_procesado"] == 1) {
                  echo '<span class="label label-success" style="margin-right:5px;">Enviado (P)</span>';
                } else {
                  // Check if keys exist
                  $procesado = isset($value["seguimiento_procesado"]) ? $value["seguimiento_procesado"] : 0;
                  if ($procesado == 1) {
                    echo '<span class="label label-success" style="margin-right:5px;">Enviado (P)</span>';
                  } else {
                    if (puedeAccion('ordenes', 'editar')) {
                      echo '<button class="btn btn-default btn-xs btnSeguimientoProcesado" 
                                    idOrden="' . e($value["id"]) . '" 
                                    codigoOrden="' . e($value["codigo"]) . '"
                                    cliente="' . e($respuestaCliente["nombre"]) . '"
                                    telefono="' . e($respuestaCliente["telefono"]) . '"
                                    data-mensaje-procesado="' . e(htmlspecialchars($mensajeProcesado)) . '"
                                    style="margin-right:5px; border: 1px solid #ccc; color: blue; width: auto !important;" 
                                    title="Enviar mensaje: Pedido Procesado">
                                    2do mensaje
                                 </button>';
                    }
                  }
                }

                $alistado = isset($value["seguimiento_alistado"]) ? $value["seguimiento_alistado"] : 0;

                if (puedeAccion('ordenes', 'editar')) {
                  if ($alistado == 1) {
                    echo '<a href="index.php?ruta=editar-orden&idVenta=' . $value["id"] . '" class="btn btn-xs btn-success" title="Pedido Alistado / Editado" style="width: auto !important;">
                                Enviado (A) <i class="fa fa-line-chart"></i>
                              </a>';
                  } else {
                    echo '<a href="index.php?ruta=editar-orden&idVenta=' . $value["id"] . '" class="btn btn-xs btn-warning" title="Editar Orden" style="width: auto !important;">
                                Enviar a Ventas
                              </a>';
                  }
                }

                // Botón 4: Convertir a Factura Electrónica
                if (puedeAccion('ordenes', 'editar')) {
                  echo ' <a href="index.php?ruta=orden-a-factura-electronica&idVenta=' . $value["id"] . '" 
                              class="btn btn-xs btn-primary" 
                              title="Convertir a Factura Electrónica" 
                              style="width: auto !important; margin-left: 3px; background-color: #605ca8; border-color: #605ca8;">
                              <i class="fa fa-file-text-o"></i> Enviar a FE
                          </a>';
                }

                echo '</td>';

                echo '<td> 
                          <div class="btn-group">

                            <a class="btn btn-warning" href="index.php?ruta=ver-detalle-orden&idVenta=' . $value["id"] . '" title="Ver Detalle de Orden" style="width: auto !important;">
                              <i class="fa fa-eye"></i>
                            </a>';

                // Mostrar el botón solo si el usuario tiene permiso
                if (puedeAccion('ordenes', 'eliminar')) {
                  echo '<button class="btn btn-danger btnEliminarVenta" idVenta="' . $value["id"] . '" style="width: auto !important;">
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



        <?php

        $eliminarVenta = new ControladorVentas();
        $eliminarVenta->ctrEliminarVenta();

        ?>

      </div>

    </div>

  </section>

</div>



<!-- Modal para ampliar/editar imagen de orden de venta -->
<div class="modal fade" id="modalAmpliarImagenOrden" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Imagen de la Orden de Venta</h4>
      </div>
      <div class="modal-body text-center">
        <img id="imagenOrdenAmpliada" src="" class="img-responsive"
          style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">

        <hr>

        <div class="form-group">
          <label>Cambiar Imagen de la Orden</label>
          <input type="file" class="form-control nuevaImagenOrden" accept="image/*">
          <p class="help-block">Peso máximo de la imagen 2MB</p>
        </div>

        <input type="hidden" id="idOrdenImagen">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btnGuardarImagenOrden">Guardar Imagen</button>
      </div>
    </div>
  </div>
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


<!--Ruta Clientes.js-->
<script src="vistas/js/ventas.js"></script>

<!-- DateRangePicker -->
<script src="vistas/bower_components/moment/min/moment.min.js"></script>
<script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

<!-- Filtro de Fechas -->
<script>
  $('#daterange-btn').daterangepicker(
    {
      ranges: {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
        'Este mes': [moment().startOf('month'), moment().endOf('month')],
        'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment(),
      endDate: moment(),
      locale: {
        cancelLabel: 'Cancelar',
        applyLabel: 'Aplicar'
      },
      autoUpdateInput: false
    }
  );

  // Solo aplicar filtro cuando se hace clic en "Aplicar"
  $('#daterange-btn').on('apply.daterangepicker', function (ev, picker) {
    var fechaInicial = picker.startDate.format('YYYY-MM-DD');
    var fechaFinal = picker.endDate.format('YYYY-MM-DD');

    var nuevaURL = 'index.php?ruta=ordenes&fechaInicial=' + fechaInicial + '&fechaFinal=' + fechaFinal;
    window.location.href = nuevaURL;
  });

  // No hacer nada cuando se cancela
  $('#daterange-btn').on('cancel.daterangepicker', function (ev, picker) {
    // No redirigir, solo cerrar el picker
  });
</script>


<!--Guarddar notas-->
<script>
  $(document).on('blur', '.celda-nota', function () {
    const idVenta = $(this).data('id');
    const nuevaNota = $(this).text().trim();

    console.log("Guardando nota:", nuevaNota, "para ID:", idVenta); // <== prueba
    $.ajax({
      url: "ajax/datatable-ventas.ajax.php",
      method: "POST",
      data: {
        idVentaNota: idVenta,
        nuevaNota: nuevaNota
      },
      success: function (respuesta) {
        console.log("Respuesta del servidor:", respuesta);
      },
      error: function () {
        alert("Hubo un error al guardar la nota.");
      }
    });
  });
</script>

<!--Guardar observaciones-->
<script>
  $(document).on('blur', '.celda-observacion', function () {
    const idVenta = $(this).attr('data-id'); // .attr() para compatibilidad con elementos dinámicos
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

<!-- Ampliar foto al hacer clic -->
<script>
  $(document).on("click", ".img-ampliar-orden", function () {
    var rutaImagen = $(this).attr("data-imagen");
    var idVenta = $(this).attr("data-idventa");

    console.log("ID Orden:", idVenta);
    console.log("Ruta Imagen:", rutaImagen);

    $("#imagenOrdenAmpliada").attr("src", rutaImagen);
    $("#idOrdenImagen").val(idVenta);
    $(".nuevaImagenOrden").val("");
    $("#modalAmpliarImagenOrden").modal("show");
  });

  // Previsualizar nueva imagen cuando se selecciona
  $(".nuevaImagenOrden").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaImagenOrden").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaImagenOrden").val("");
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
          $("#imagenOrdenAmpliada").attr("src", rutaImagen);
        });
      }
    }
  });

  // Guardar la nueva imagen de la orden
  $(document).on("click", ".btnGuardarImagenOrden", function () {

    var idVenta = $("#idOrdenImagen").val();
    var imagen = $(".nuevaImagenOrden")[0].files[0];

    console.log("ID al guardar:", idVenta);
    console.log("Imagen al guardar:", imagen);

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
        text: "No se pudo obtener el ID de la orden",
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
        console.log("Respuesta del servidor:", respuesta);

        if (respuesta == "ok") {
          swal({
            type: "success",
            title: "¡La imagen ha sido actualizada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function (result) {
            if (result.value) {
              $("#modalAmpliarImagenOrden").modal("hide");
              window.location = "ordenes";
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
        console.log("Error AJAX:", textStatus, errorThrown);
        console.log("Respuesta:", jqXHR.responseText);

        swal({
          type: "error",
          title: "Error en la petición",
          text: "Por favor revisa la consola para más detalles",
          confirmButtonText: "Cerrar"
        });
      }
    });
  });
</script>


<!-- Script para botones de seguimiento -->
<script>
  $(document).ready(function () {

    // Función genérica para enviar webhook y luego actualizar BD
    function enviarSeguimiento(btn, urlWebhook, tipo) {
      var idOrden = btn.attr("idOrden");
      var codigoOrden = btn.attr("codigoOrden");
      var cliente = btn.attr("cliente");
      var telefono = btn.attr("telefono");

      // Leer mensaje dinámico desde los data attributes
      var textoPregunta = btn.data('mensaje-' + tipo);

      swal({
        title: '¿Desea enviar un mensaje al cliente?',
        html: '<p style="font-size: 18px; font-weight: 500; margin: 10px 0;">' + textoPregunta + '</p>',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.value) {

          // Preparar datos para fetch
          const datosWebhook = new URLSearchParams();
          datosWebhook.append('id_orden', idOrden);
          datosWebhook.append('codigo', codigoOrden);
          datosWebhook.append('cliente', cliente);
          datosWebhook.append('celular', telefono);
          datosWebhook.append('tipo', tipo);
          datosWebhook.append('mensaje', textoPregunta);

          // 1. Enviar Webhook
          fetch(urlWebhook, {
            method: 'POST',
            mode: 'no-cors',
            cache: 'no-cache',
            credentials: 'omit',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: datosWebhook
          })
            .then(response => {
              // 2. Si el fetch "funciona" (network ok), actualizamos la BD
              // Nota: no-cors devuelve respuesta opaca, no podemos saber si fue 200 o 500, asumimos éxito de red.

              var columna = (tipo === 'recibido') ? "seguimiento_recibido" : "seguimiento_procesado";

              var datos = new FormData();
              datos.append("idVentaSeguimiento", idOrden);
              datos.append("columna", columna);
              datos.append("valor", 1);

              $.ajax({
                url: "ajax/ventas.ajax.php",
                method: "POST",
                data: datos,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                  if (res == "ok") {
                    var label = (tipo === 'recibido') ? 'Enviado (R)' : 'Enviado (P)';
                    btn.replaceWith('<span class="label label-success" style="margin-right:5px;">' + label + '</span>');

                    swal({
                      type: "success",
                      title: "Enviado",
                      showConfirmButton: false,
                      timer: 1500
                    });
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  // Fallo al guardar en BD local
                  console.error(jqXHR.responseText);
                  swal({
                    type: "error",
                    title: "Error al guardar el estado en base de datos",
                    text: "Detalle: " + jqXHR.responseText.substring(0, 200),
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                  });
                }
              });

            })
            .catch(error => {
              console.error('Error Webhook:', error);
              swal({
                type: "error",
                title: "Error al conectar con el servidor de mensajes",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
              });
            });
        }
      });
    }

    // Botón Recibido
    $(".tablaOrdenes tbody").on("click", ".btnSeguimientoRecibido", function () {
      enviarSeguimiento($(this), "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/47b4eb4c-c238-4ab4-bebd-efcb09206cef", 'recibido');
    });

    // Botón Procesado
    $(".tablaOrdenes tbody").on("click", ".btnSeguimientoProcesado", function () {
      enviarSeguimiento($(this), "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/b9ebbdab-45f9-46ac-957e-30e080f773aa", 'procesado');
    });

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
<!-- Custom DataTable Initialization for Ordenes -->
<script>
  $(document).ready(function () {
    // PROTECCIÓN DE ÚLTIMA PALABRA (Delay para vencer scripts globales)
    setTimeout(function () {
      if (typeof $.fn.select2 !== 'undefined') {

        const listaClientes = <?php
        $clientesJS = [['id' => '', 'text' => 'Todos los clientes']];
        foreach ($clientesData as $c) {
          $clientesJS[] = ['id' => $c['id'], 'text' => $c['nombre']];
        }
        echo json_encode($clientesJS);
        ?>;

        const listaUsuarios = <?php
        $usuariosJS = [['id' => '', 'text' => 'Todos los usuarios']];
        foreach ($usuariosData as $u) {
          $usuariosJS[] = ['id' => $u['id'], 'text' => $u['nombre']];
        }
        echo json_encode($usuariosJS);
        ?>;

        $('#filtroClienteOrdenes').select2({
          data: listaClientes,
          placeholder: "Seleccionar cliente...",
          allowClear: true,
          width: '200px'
        }).addClass('form-control'); // Añadir clase después de la carga

        $('#filtroUsuarioOrdenes').select2({
          data: listaUsuarios,
          placeholder: "Seleccionar vendedor...",
          allowClear: true,
          width: '180px'
        }).addClass('form-control');

        // Restaurar valores si existen en la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('cliente')) $('#filtroClienteOrdenes').val(urlParams.get('cliente')).trigger('change.select2');
        if (urlParams.get('usuario')) $('#filtroUsuarioOrdenes').val(urlParams.get('usuario')).trigger('change.select2');
      }

      // INICIALIZACIÓN DEL RANGO DE FECHAS
      if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#daterange-btn').daterangepicker(
          {
            ranges: {
              'Hoy': [moment(), moment()],
              'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
              'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
              'Este mes': [moment().startOf('month'), moment().endOf('month')],
              'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment(),
            endDate: moment()
          },
          function (start, end) {
            $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            var fechaInicial = start.format('YYYY-MM-DD');
            var fechaFinal = end.format('YYYY-MM-DD');

            // Asignar a campos ocultos para el GET
            $('#fechaInicial').val(fechaInicial);
            $('#fechaFinal').val(fechaFinal);

            localStorage.setItem("capturarRangoOrdenes", start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
          }
        );

        // Cancelar rango (limpiar fechas)
        $('.daterangepicker.opensright .range_inputs .cancelBtn').on('click', function () {
          localStorage.removeItem("capturarRangoOrdenes");
          $('#fechaInicial').val("");
          $('#fechaFinal').val("");
        });
      }
    }, 200); // 200ms es suficiente para que la mayoría de scripts globales terminen

    // Verificar si existe la tabla antes de inicializar
    if ($(".tablaOrdenes").length > 0) {
      if ($.fn.DataTable.isDataTable('.tablaOrdenes')) {
        $('.tablaOrdenes').DataTable().destroy();
      }

      $(".tablaOrdenes").DataTable({
        "order": [[8, "desc"]], // Ahora fecha es la columna 8
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
                
                // Excepciones para no romper layout (Notas y Observacion)
                if(col.columnIndex === 6 || col.columnIndex === 7) {
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
                    finalHtml += '<span class="text-bold" style="display:block; color:#555; margin-bottom:5px;">' + label + ':</span>';
                } else {
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
                    finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
                }

                if (col.columnIndex === 7) {
                    // Celda de observación editable
                    var rowNode = api.row(rowIdx).node();
                    var idOrden = $(rowNode).attr('data-orden-id') || "";
                    var observacionText = $(rowNode).find('.celda-observacion').text().trim();
                    var placeholderAttr = (observacionText === "") ? ' data-placeholder="true"' : "";
                    
                    finalHtml += '<div contenteditable="true" class="celda-observacion" data-id="' + idOrden + '"' + placeholderAttr + ' style="width:100%; outline:none; display:block; border:1px dashed #ccc; padding:8px; background:#fff9e6; margin-top:5px;">' + observacionText + '</div>';
                } else {
                    // El resto pasa su HTML o texto tal cual
                    finalHtml += '<span style="color:#333;">' + col.data + '</span>';
                }
                
                finalHtml += '</div>';
              });

              if (!hasHidden) return false;
              return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
            }
          }
        },
        "columnDefs": [
          { "targets": 0, "responsivePriority": 1 }, // Código
          { "targets": 10, "responsivePriority": 2, "orderable": false }, // Acciones
          { "targets": 1, "responsivePriority": 3 }, // Cliente
          { "targets": 2, "responsivePriority": 4 }, // Vendedor
          { "targets": 3, "responsivePriority": 5 }, // Forma de pago
          { "targets": 4, "responsivePriority": 6 }, // Imagen
          { "targets": 5, "responsivePriority": 7 }, // Total
          { "targets": 6, "responsivePriority": 8 }, // Notas
          { "targets": 7, "responsivePriority": 9 }, // Observación
          { "targets": 8, "responsivePriority": 10 }, // Fecha
          { "targets": 9, "responsivePriority": 11 } // Seguimiento
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
  });
</script>