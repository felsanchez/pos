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

        <div class="pull-right" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
          
          <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="ruta" value="ordenes">

            <!-- Filtro por Bodega (Administradores) -->
            <?php if (stripos($_SESSION["perfil"], "Admin") !== false): ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="hidden-xs"><b>Sucursal:</b></span>
                <div class="input-group" style="width: 180px;">
                  <span class="input-group-addon"><i class="fa fa-building text-primary"></i></span>
                  <select name="bodega" class="form-control select2 select-bodega">
                    <?php
                    // Determinar qué bodega mostrar por defecto:
                    // 1. Si hay filtro explícito en GET, usarlo
                    // 2. Si el admin tiene bodega en sesión, preseleccionar esa bodega
                    // 3. Si no hay bodega en sesión (super-admin), mostrar "Mostrar Todas"
                    $bodegaSeleccionada = '';
                    if (isset($_GET['bodega']) && $_GET['bodega'] !== '') {
                      $bodegaSeleccionada = $_GET['bodega'];
                    } elseif (!empty($_SESSION['id_bodega'])) {
                      $bodegaSeleccionada = $_SESSION['id_bodega'];
                    }
                    $selectedTodas = ($bodegaSeleccionada === 'todas' || $bodegaSeleccionada === '') ? 'selected' : '';
                    echo '<option value="todas" ' . $selectedTodas . '>Mostrar Todas</option>';
                    $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                    foreach ($bodegas as $key => $valueBodega) {
                      $selected = ($bodegaSeleccionada == $valueBodega["id"]) ? 'selected' : '';
                      echo '<option value="' . e($valueBodega["id"]) . '" ' . $selected . '>' . e($valueBodega["nombre"]) . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>
            <input type="hidden" name="fechaInicial" id="fechaInicial"
              value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
            <input type="hidden" name="fechaFinal" id="fechaFinal"
              value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

            <?php CSRF::insertToken(); ?>

            <!-- Filtro por cliente -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Cliente:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select name="cliente" id="filtroClienteOrdenes" class="form-control select2 select-cliente"
                  style="width: 100%;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $clientes = ControladorClientes::ctrMostrarClientes(null, null);
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
              <span class="hidden-xs"><b>Vendedor:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select name="usuario" id="filtroUsuarioOrdenes" class="form-control select2 select-usuario"
                  style="width: 100%;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
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
              <span class="hidden-xs"><b>Fecha:</b></span>
              <div class="form-group" style="margin-bottom: 0;">
                <button type="button" class="btn btn-default" id="daterange-btn">
                  <span>
                    <i class="fa fa-calendar"></i> Rango de fecha
                  </span>
                  <i class="fa fa-caret-down"></i>
                </button>
              </div>
            </div>

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
                <th><i class="fa fa-magic"></i> Notas del Cliente</th>
                <th>Observación</th>
                <th>Fecha</th>
                <th>Seguimiento</th>
                <th>Convertir</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <!-- Datos cargados por DataTables Server-Side -->
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


<script>
function alertaCajaCerradaOrdenes(){
    swal({
        title: '¡Caja Cerrada!',
        text: 'Debe abrir caja antes de realizar esta operación.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3c8dbc',
        cancelButtonColor: '#6c757d',
        cancelButtonText: 'Entendido',
        confirmButtonText: 'Abrir caja'
    }).then(function(result){
        if (result.value) {
            $('#modalAperturaCaja').modal('show');
        }
    });
}
</script>

<!-- Scripts específicos del módulo -->
<script src="vistas/js/ordenes.js"></script>