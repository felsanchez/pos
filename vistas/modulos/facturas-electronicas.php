<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">


<?php

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";


// Obtener prefijo de Factus para borradores
$rangoActivoFactus = ModeloFactus::mdlObtenerRangoActivo();
$prefijoDian = $rangoActivoFactus ? $rangoActivoFactus["prefijo"] : "";

$xml = ControladorVentas::ctrDescargarXML();

if ($xml) {

  rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");
  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
}
?>

<div class="content-wrapper">
  <section class="content-header">


    <h1>
      Facturación electrónica
      <small>Comprobantes fiscales</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Facturas electrónicas</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <?php if (puedeAccion('factura_electronica', 'crear')): ?>
          <?php if (ControladorCajas::ctrValidarCajaAbierta()): ?>
            <a href="crear-factura-electronica">
              <button class="btn btn-primary">
                <i class="fa fa-plus"></i> Crear Factura Electrónica
              </button>
            </a>
          <?php else: ?>
            <button class="btn btn-primary" onclick="alertaCajaCerradaVentas()">
              <i class="fa fa-plus"></i> Crear Factura Electrónica
            </button>
            <script>
            function alertaCajaCerradaVentas(){
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
          <?php endif; ?>
        <?php else: ?>
          <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear factura electrónica">
            <i class="fa fa-plus"></i> Crear Factura Electrónica
          </button>
        <?php endif; ?>

        <div class="pull-right" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
          
          <div style="display: flex; align-items: center; gap: 10px;">

            <!-- Filtro por Bodega (Administradores) -->
            <?php if (stripos($_SESSION["perfil"], "Admin") !== false): ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="hidden-xs"><b>Sucursal:</b></span>
                <div class="input-group" style="width: 180px;">
                  <span class="input-group-addon"><i class="fa fa-building text-primary"></i></span>
                  <select name="bodega" class="form-control select2 select-bodega"
                    data-default="<?php echo !empty($_SESSION['id_bodega']) ? e($_SESSION['id_bodega']) : 'todas'; ?>">
                    <?php
                    $bodegaSeleccionadaFE = '';
                    if (isset($_GET['bodega']) && $_GET['bodega'] !== '') {
                      $bodegaSeleccionadaFE = $_GET['bodega'];
                    } elseif (!empty($_SESSION['id_bodega'])) {
                      $bodegaSeleccionadaFE = $_SESSION['id_bodega'];
                    }
                    $selectedTodasFE = ($bodegaSeleccionadaFE === 'todas' || $bodegaSeleccionadaFE === '') ? 'selected' : '';
                    echo '<option value="todas" ' . $selectedTodasFE . '>Mostrar Todas</option>';
                    $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                    foreach ($bodegas as $key => $valueBodega) {
                      $selected = ($bodegaSeleccionadaFE == $valueBodega["id"]) ? 'selected' : '';
                      echo '<option value="' . e($valueBodega["id"]) . '" ' . $selected . '>' . e($valueBodega["nombre"]) . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            <!-- Filtro por cliente -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Cliente:</b></span>
              <div class="input-group" style="width: 200px;">
                <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                  <i class="fa fa-search text-primary"></i>
                </span>
                <select id="filtroClienteFacturas" class="form-control select2 select-cliente" style="width: 100%;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
                  foreach ($clientes as $key => $valueCliente) {
                    echo '<option value="' . e($valueCliente["id"]) . '">' . e($valueCliente["nombre"]) . '</option>';
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
                <select id="filtroUsuarioFacturas" class="form-control select2 select-usuario" style="width: 100%;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                  foreach ($usuarios as $key => $valueUsuario) {
                    echo '<option value="' . e($valueUsuario["id"]) . '">' . e($valueUsuario["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Botón Rango de Fecha -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Fecha:</b></span>
              <button type="button" class="btn btn-default" id="daterange-btn-factus">
                <span>
                  <i class="fa fa-calendar"></i> Rango de fecha
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
            </div>

            <!-- Botón Limpiar -->
            <button type="button" class="btn btn-default" id="btnLimpiarFacturas" title="Limpiar filtros">
              <i class="fa fa-refresh"></i>
            </button>

          </div>
        </div>



      </div>

      <div class="box-body">


        <div class="box-body">

          <div id="loader-table" class="loader-container">
            <i class="fa fa-refresh fa-spin"></i>
            <span>Cargando Facturas Electrónicas...</span>
          </div>

          <div class="tabla-facturas table-responsive" id="wrapperTablaFacturas" style="display:none;">
            <table id="tablaFacturasElectronicas"
              class="table table-bordered table-striped dt-responsive tablaFacturasListado display nowrap" width="100%">

              <thead>
                <tr>
                  <th>Código</th>
                  <th>Cliente</th>
                  <th>Vendedor</th>
                  <th>Imagen</th>

                  <th>Total</th>
                  <th>Estado DIAN</th>
                  <th><i class="fa fa-magic"></i> Notas del Cliente</th>
                  <th>Observación</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>

              <tbody>
              </tbody>

            </table>
          </div>
          <?php
          $eliminarVenta = new ControladorVentas();
          $eliminarVenta->ctrEliminarVenta();
          ?>



          <!-- Modal para ampliar/editar imagen de venta -->
          <div class="modal-custom" id="modalAmpliarImagenVenta">
            <div class="modal-custom-backdrop" data-dismiss="modal"></div>
            <div class="modal-custom-container">
              <div class="modal-custom-header">
                <h4 class="modal-title">Imagen de la Venta</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                  style="color: white; opacity: 0.8; margin-top: -2px;">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-custom-body text-center">
                <img id="imagenVentaAmpliada" src="" class="img-responsive"
                  style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">

                <hr>

                <div class="form-group text-left">
                  <label>Cambiar Imagen de la Venta</label>
                  <input type="file" class="form-control nuevaImagenVenta" accept="image/*">
                  <p class="help-block">Peso máximo de la imagen 2MB</p>
                </div>

                <input type="hidden" id="idVentaImagen">
              </div>
              <div class="modal-custom-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btnGuardarImagenVenta">Guardar Imagen</button>
              </div>
            </div>
          </div>




        </div>
      </div>
  </section>
</div>



<!--==========================================================================
MODAL GENERAR NOTA CRÉDITO
===========================================================================-->
<div class="modal-custom" id="modalNotaCredito">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container">
    <div class="modal-custom-header" style="background:#dd4b39;">
      <h4 class="modal-title"><i class="fa fa-undo"></i> Generar Nota Crédito</h4>
      <button type="button" class="close" data-dismiss="modal"
        style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
    </div>
    <div class="modal-custom-body" style="text-align: left;">
      <input type="hidden" id="ncIdVenta">

      <div class="alert alert-info">
        <p><strong>Factura:</strong> <span id="ncNumeroFactura"></span></p>
        <p><strong>Cliente:</strong> <span id="ncCliente"></span></p>
        <p><strong>Total:</strong> $<span id="ncTotal"></span></p>
      </div>

      <div class="form-group">
        <label>Tipo de Nota Crédito:</label>
        <select class="form-control" id="ncTipo">
          <option value="anulacion_total">Anulación Total</option>
          <option value="devolucion_parcial">Devolución Parcial</option>
          <option value="ajuste_precio">Ajuste de Precio</option>
          <option value="descuento_posterior">Descuento Posterior</option>
        </select>
      </div>

      <div class="form-group">
        <label>Motivo <span class="text-danger">*</span>:</label>
        <textarea class="form-control" id="ncMotivo" rows="3"
          placeholder="Ej: Error en digitación de precio, producto defectuoso, etc."></textarea>
      </div>

      <div class="alert alert-warning">
        <i class="fa fa-warning"></i> Esta acción generará una Nota Crédito oficial ante la DIAN y <strong>no puede
          revertirse</strong>.
      </div>
    </div>
    <div class="modal-custom-footer">
      <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
      <?php if (puedeAccion('factura_electronica', 'crear')): ?>
        <button type="button" class="btn btn-danger" id="btnConfirmarNC">
          <i class="fa fa-check"></i> Generar Nota Crédito
        </button>
      <?php else: ?>
        <button type="button" class="btn btn-danger" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para generar nota crédito">
          <i class="fa fa-check"></i> Generar Nota Crédito
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!--==========================================================================
MODAL EDITAR CLIENTE
===========================================================================-->

<!-- Modal -->
<div id="modalEditarCliente" class="modal-custom">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container">

    <form role="form" method="post" style="display: flex; flex-direction: column; height: 100%;">

      <?php CSRF::insertToken(); ?>

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-custom-header">
        <h4 class="modal-title">Ver cliente</h4>
        <button type="button" class="close" data-dismiss="modal"
          style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-custom-body" style="text-align: left;">
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
                  <textarea class="form-control input-lg" name="editarNota" id="editarNota" placeholder="Notas" readonly
                    style="height: 80px; resize: none;"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-custom-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
      </div>

    </form>
  </div>
</div>


<!--Ruta Clientes.js-->
<script src="vistas/js/ventas.js"></script>
<script src="vistas/js/notas-credito.js"></script>

<!-- DateRangePicker -->
<script src="vistas/bower_components/moment/min/moment.min.js"></script>
<script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>


<!-- Filtro de Fechas -->
<script>
  $(function () {
    // 1. Inicializar fechas
    var start = moment();
    var textoBoton = '<i class="fa fa-calendar"></i> Rango de fecha';

    $("#daterange-btn-factus span").html(textoBoton);

    // 2. Configurar DateRangePicker
    $('#daterange-btn-factus').daterangepicker(
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
        endDate: moment()
      },
      function (start, end) {
        // Actualizar texto del botón visualmente
        var textoRango = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
        $('#daterange-btn-factus span').html(textoRango);

        // Actualizar variables globales de filtro
        feFilterFechaInicial = start.startOf('day').format('YYYY-MM-DD');
        feFilterFechaFinal = end.endOf('day').format('YYYY-MM-DD');

        // Recargar tabla con el nuevo filtro de fecha
        reloadFETable();
      }
    );

    // 3. Manejar Cancelar/Limpiar Rango
    $('#daterange-btn-factus').on('cancel.daterangepicker', function (ev, picker) {
      $('#daterange-btn-factus span').html('<i class="fa fa-calendar"></i> Rango de fecha');
      feFilterFechaInicial = '';
      feFilterFechaFinal = '';
      reloadFETable();
    });
  });
</script>

<!--Guardar observaciones-->
<script>
  $(document).on('blur', '.celda-observacion', function () {
    // No guardar si la factura ya fue firmada (campo readonly)
    if ($(this).attr('data-readonly') === '1' || $(this).attr('contenteditable') === 'false') return;
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
              window.location = "facturas-electronicas";
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
  $(document).on("click", ".img-ampliar-venta", function () {
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
        $("#editarNota").val(data["notas"]);

        // AGREGAR ESTA LÍNEA para preseleccionar el estado
        $("#editarEstado").val(data["estatus"]);

        // Si tienes más campos, agrégalos aquí:
        // $("#editarDepartamento").val(data["departamento"]); // Eliminado visualmente
        $("#editarCiudad").val(data["ciudad"]);

        // Abrir el modal
        $('#modalEditarCliente').modal('show');
      }
    });
  });
</script>

<!--=====================================
MODAL VER NOTAS DE CRÉDITO
======================================-->
<div id="modalNotasCredito" class="modal-custom">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container">
    <div class="modal-custom-header" style="background:#f39c12;">
      <h4 class="modal-title"><i class="fa fa-list"></i> Notas Crédito Asociadas</h4>
      <button type="button" class="close" data-dismiss="modal"
        style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
    </div>
    <div class="modal-custom-body" style="text-align: left;">
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Código</th>
              <th>Fecha</th>
              <th>Monto</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyNotasCredito">
            <!-- Filas inyectadas por AJAX -->
          </tbody>
        </table>
      </div>
    </div>
    <div class="modal-custom-footer">
      <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
    </div>
  </div>
</div>

<!--=====================================
MODAL ENVIAR EMAIL
======================================-->

<div id="modalEnviarEmail" class="modal-custom">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container" style="max-width: 500px;">
    <form role="form" method="post" id="formEnviarEmail" style="display: flex; flex-direction: column; height: 100%;">
      <div class="modal-custom-header">
        <h4 class="modal-title">Enviar Factura por Correo</h4>
        <button type="button" class="close" data-dismiss="modal"
          style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
      </div>
      <div class="modal-custom-body" style="text-align: left;">
        <div class="box-body">
          <div class="form-group">
            <label>Cliente</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-user"></i></span>
              <input type="text" class="form-control input-lg" id="emailNombreCliente" readonly>
              <input type="hidden" id="emailIdVenta">
            </div>
          </div>
          <div class="form-group">
            <label>Correo Electrónico</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
              <input type="email" class="form-control input-lg" id="emailDestino" placeholder="Ingresar correo"
                required>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-custom-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
        <button type="submit" class="btn btn-primary">Enviar PDF</button>
      </div>
    </form>
  </div>
</div>

</div>
<!-- DataTables Personalizado para Facturas -->
<script>
  // Variables globales para los filtros de Facturas Electrónicas
  var feFilterFechaInicial = '';
  var feFilterFechaFinal = '';

  // Función para recargar la tabla de Facturas Electrónicas con los filtros activos
  function reloadFETable() {
    if (window.tablaFE) {
      window.tablaFE.ajax.reload(null, false);
    }
  }

  $(document).ready(function () {
    setTimeout(function () {
      if ($("#tablaFacturasElectronicas").length > 0) {
        if ($.fn.DataTable.isDataTable('#tablaFacturasElectronicas')) {
          $('#tablaFacturasElectronicas').DataTable().destroy();
        }

        window.tablaFE = $("#tablaFacturasElectronicas").DataTable({
          "processing": true,
          "serverSide": true,
          "responsive": {
            "details": {
              "type": "inline",
              "renderer": function (api, rowIdx, columns) {
                var labels = {
                  2: 'Vendedor', 3: 'Imagen', 4: 'Total',
                  5: 'Estado DIAN', 6: 'Notas del cliente', 7: 'Observación', 8: 'Fecha'
                };
                var idVenta = $(api.row(rowIdx).node()).find('.celda-observacion').attr('data-id') || '';
                var obsReadonly = $(api.row(rowIdx).node()).find('.celda-observacion').attr('data-readonly') || '0';
                var finalHtml = '';
                var hasHidden = false;

                $.each(columns, function (i, col) {
                  if (!col.hidden) return;
                  hasHidden = true;
                  var colIdx = col.columnIndex;
                  var label = labels[colIdx] || col.title || ('Columna ' + colIdx);
                  var data = col.data || '';

                  if (colIdx === 7) { // Observación
                    var obsTexto = $('<div>').html(data).text().trim();
                    var obsEditableAttr = obsReadonly === '1' ? 'false' : 'true';
                    var obsStyleStr = obsReadonly === '1'
                      ? 'background:#f5f5f5; color:#777; cursor:default; font-style:italic; min-height:24px;'
                      : 'min-height:24px;';
                    finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
                    finalHtml += '<span class="text-bold" style="display:block;color:#555;margin-bottom:4px;"> ' + label + ':</span>';
                    finalHtml += '<div class="celda-observacion" contenteditable="' + obsEditableAttr + '" data-id="' + idVenta + '" data-readonly="' + obsReadonly + '" style="' + obsStyleStr + '">' + obsTexto + '</div>';
                    finalHtml += '</div>';
                    return;
                  }

                  finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee;">';
                  finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span> ';
                  finalHtml += '<span>' + data + '</span>';
                  finalHtml += '</div>';
                });

                return hasHidden ? $('<div/>').append(finalHtml) : false;
              }
            }
          },
          "autoWidth": false,
          "ajax": {
            "url": "ajax/ventas.ajax.php",
            "type": "POST",
            "data": function (d) {
              d.drawFacturasElectronicas = 1;
              d.fechaInicial = feFilterFechaInicial;
              d.fechaFinal = feFilterFechaFinal;
              d.clienteId = $('#filtroClienteFacturas').val() || '';
              d.usuarioId = $('#filtroUsuarioFacturas').val() || '';
              d.bodegaId = $('.select-bodega').length ? ($('.select-bodega').val() || 'todas') : '';
            }
          },
          "initComplete": function (settings, json) {
            $(this.api().table().node()).addClass('datatable-ready');
            if (typeof quitarLoaderGlobal === 'function') {
              quitarLoaderGlobal();
            }
            // Mostrar tabla y filtros solo cuando DataTables terminó de inicializar
            // (esto elimina el layout-shift / parpadeo al cargar)
            $('#wrapperTablaFacturas').fadeIn(200);
            $('#contenedorFiltrosFacturas').removeClass('fe-ui-hidden').css('display', '');
          },
          "order": [[8, "desc"]], // Fecha
          "columnDefs": [
            { "targets": 0, "responsivePriority": 1 },
            { "targets": 9, "responsivePriority": 2, "orderable": false },
            { "targets": 1, "responsivePriority": 3 },
            { "targets": 2, "responsivePriority": 4 },
            { "targets": 3, "responsivePriority": 5, "orderable": false },
            { "targets": 4, "responsivePriority": 6 },
            { "targets": 5, "responsivePriority": 7, "orderable": false },
            { "targets": 6, "responsivePriority": 8 },
            { "targets": 7, "responsivePriority": 9, "orderable": false },
            { "targets": 8, "responsivePriority": 10 }
          ],
          "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sSearch": "Buscar:",
            "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
          }
        });

        // Inicializar Select2
        $('#filtroClienteFacturas, #filtroUsuarioFacturas, .select-bodega').select2({ allowClear: false, width: '100%' });

        // Filtrar automáticamente al cambiar cliente, usuario o bodega
        $('#filtroClienteFacturas, #filtroUsuarioFacturas, .select-bodega').on('change', function () {
          reloadFETable();
        });

        // Botón limpiar
        $('#btnLimpiarFacturas').on('click', function () {
          $('#filtroClienteFacturas').val('').trigger('change');
          $('#filtroUsuarioFacturas').val('').trigger('change');
          var defaultBodega = $('.select-bodega').data('default') || 'todas';
          $('.select-bodega').val(defaultBodega).trigger('change.select2');
          feFilterFechaInicial = '';
          feFilterFechaFinal = '';
          $('#daterange-btn-factus span').html('<i class="fa fa-calendar"></i> Rango de fecha');
          reloadFETable();
        });
      }
    }, 200);
  });
</script>