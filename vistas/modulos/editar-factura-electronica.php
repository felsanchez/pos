<?php
// Verificar que se haya proporcionado un ID de venta
if (!isset($_GET["idVenta"]) || empty($_GET["idVenta"])) {
  echo '<script>window.location = "facturas-electronicas";</script>';
  exit;
}

$idVenta = $_GET["idVenta"];

// Obtener datos de la venta
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

// Verificar que la venta existe y está en borrador
if (!$venta || !in_array($venta["estado_dian"], ['creada', 'pendiente', null])) {
  echo '<script>
        swal({
            type: "error",
            title: "Error",
            text: "Esta factura no puede ser editada",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        }).then(function(result){
            window.location = "facturas-electronicas";
        });
    </script>';
  exit;
}

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$impuestoDefecto = !empty($configuracion["impuesto_defecto"]) ? $configuracion["impuesto_defecto"] : 0;

// Obtener datos de Factus (Rango y Prefijo)
$rangoFactus = ModeloFactus::mdlObtenerRangoActivo();
$prefijoFactus = $rangoFactus ? $rangoFactus["prefijo"] : "FE";
$resolucionId = $rangoFactus ? $rangoFactus["id"] : 0;

$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");

// Decodificar productos de la venta
$productosVenta = json_decode($venta["productos"], true);
?>




<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Editar Factura Electrónica (Borrador)
    </h1>

    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Editar Factura Electrónica</li>
    </ol>
  </section>


  <section class="content">

    <div class="row">

      <!--=====================================
            EL FORMULARIO
            ======================================-->

      <div class="col-lg-5 col-xs-12">

        <div class="box box-success">

          <div class="box-header with-border"></div>

          <form role="form" method="post" class="formularioVenta">

            <?php CSRF::insertToken(); ?>

            <div class="box-body">

              <div class="box">

                <!--=====================================
                      ENTRADA DEL VENDEDOR
                      ======================================-->

                <!--=====================================
                      ENCABEZADO DE VENTA (VENDEDOR, CÓDIGO, CLIENTE)
                      ======================================-->

                <div class="row">
                  <!-- Vendedor -->
                  <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                      <label>Vendedor</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="nuevoVendedor" name="nuevoVendedor"
                          value="<?php echo $_SESSION["nombre"]; ?>" readonly>
                        <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">
                        <input type="hidden" name="rutaOrigen" value="facturas-electronicas">
                      </div>
                    </div>
                  </div>

                  <!-- Formato Código -->
                  <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                      <label>Formato</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                        <input type="text" class="form-control" id="formatoCodigoVenta" name="formatoCodigoVenta"
                          value="<?php echo $prefijoFactus; ?>" readonly placeholder="Prefijo DIAN">
                        <input type="hidden" name="resolucion_id" value="<?php echo $resolucionId; ?>">
                      </div>
                    </div>
                  </div>

                  <!-- Código Venta -->
                  <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                      <label>Código Venta</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                        <?php
                        // Obtener el siguiente consecutivo de Factus
                        $siguienteNumero = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus();
                        ?>
                        <input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta"
                          value="<?php echo $siguienteNumero; ?>" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Cliente -->
                <div class="row">
                  <div class="col-xs-12">
                    <div class="form-group">
                      <label>Cliente</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-users"></i></span>
                        <select class="form-control" id="seleccionarCliente" name="seleccionarCliente" required>
                          <option value="">Seleccionar cliente</option>
                          <?php
                          $item = null;
                          $valor = null;
                          $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
                          foreach ($clientes as $key => $value) {
                            echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                          }
                          ?>
                        </select>
                        <span class="input-group-addon">
                          <button type="button" class="btn btn-default btn-xs" onclick="$('#modalAgregarCliente').fadeIn();">Agregar cliente</button>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>


                <!--=====================================
                      ENTRADA PARA IMAGEN
                      ======================================-->

                <!--<input type="hidden" id="nuevaimagen" name="nuevaimagen">-->
                <!--<input type="hidden" name="nuevaimagen" value="vistas/img/ventas/default/sinventa.png">-->


                <!--=====================================
                      ENTRADA PARA AGREGAR PRODUTO
                      ======================================-->

                <div class="form-group row nuevoProducto">

                </div>

                <input type="hidden" id="listaProductos" name="listaProductos">

                <!--=====================================
                       BOTON PARA AGREGAR PRODUCTO
                       ======================================-->
                <!--BTN SE MUESTRA SOLO DESDE MOVIL-->
                <button type="button" class="btn btn-default  btnAgregarProducto solo-movil">Agregar producto</button>

                <hr>

                <div class="row">

                  <!--=====================================
                        ENTRADA VALOR BRUTO, SUBTOTAL, IMPUESTOS Y TOTAL
                        ======================================-->

                  <div class="col-xs-12 col-md-6 pull-right">
                    <table class="table table-condensed table-bordered" style="background:#f9f9f9;">
                      <tbody>
                        <!-- Valor Bruto -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold; width: 40%">Subtotal</td>
                          <td style="width: 60%">
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoValorBruto" name="nuevoValorBruto"
                                placeholder="0" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Subtotal -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold;">Valor Bruto</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoSubtotalVenta" name="nuevoSubtotalVenta"
                                placeholder="0" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Impuestos IVA -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold;">Impuestos IVA</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoImpuestoVenta" name="nuevoImpuestoVenta"
                                placeholder="0" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Impuestos INC -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold;">Impuestos INC</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoImpuestoINCVenta"
                                name="nuevoImpuestoINCVenta" placeholder="0" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Total -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold; font-size: 1.2em;">Total</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-lg" id="nuevoTotalVenta"
                                name="nuevoTotalVenta" total="" placeholder="0" readonly required
                                style="font-weight: bold; font-size: 1.2em;">
                              <input type="hidden" name="totalVenta" id="totalVenta">
                              <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" value="0"
                                required>
                              <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto" required>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- SECCIÓN DE RETENCIONES -->
                  <div class="col-xs-12 pull-left" id="seccionRetenciones" style="display: none; margin-top: 10px;">
                    <div class="alert alert-info">
                      <h4><i class="icon fa fa-info-circle"></i> Retenciones Aplicadas</h4>
                      <div id="listaRetenciones"></div>
                      <input type="hidden" id="datosRetenciones" name="datosRetenciones" value="">
                    </div>
                  </div>


                </div>
                <hr>

                <!--=====================================
                        SECCIÓN DE DESCUENTOS
                        ======================================-->

                <div class="row">
                  <div class="col-xs-12">

                    <!-- Checkboxes para tipo de descuento -->
                    <div class="form-group">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="checkDescuentoPorcentaje" name="checkDescuentoPorcentaje"
                          style="margin-right: 5px; transform: scale(1.2);">
                        Agregar descuento por %
                      </label>
                      &nbsp;&nbsp;&nbsp;

                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="checkDescuentoFijo" name="checkDescuentoFijo"
                          style="margin-right: 5px; transform: scale(1.2);">
                        Agregar descuento por valor fijo
                      </label>
                    </div>

                    <!-- Campo de entrada para descuento (oculto inicialmente) -->
                    <div class="form-group" id="campoDescuento" style="display: none;">
                      <div class="input-group">
                        <span class="input-group-addon" id="iconoDescuento"><i class="fa fa-percent"></i></span>
                        <input type="number" class="form-control input-lg" min="0" id="valorDescuento"
                          name="valorDescuento" placeholder="0" value="0">
                        <span class="input-group-addon" id="labelDescuento">Descuento</span>
                      </div>
                      <small class="text-muted" id="textoAyudaDescuento">Ingrese el porcentaje de descuento</small>
                    </div>

                    <!-- Campos ocultos para guardar información del descuento -->
                    <input type="hidden" id="tipoDescuento" name="tipoDescuento" value="">
                    <input type="hidden" id="montoDescuento" name="montoDescuento" value="0">
                  </div>

                </div>

                <div class="row">
                  <div class="col-xs-12">
                    <button type="button" class="btn btn-default" onclick="$('#modalAgregarRetencionNuevo').fadeIn();">Retenciones</button>
                  </div>
                </div>

                <hr>

                <!--=====================================
                        ENTRADA METODO DE PAGO
                        ======================================-->

                <div class="form-group row">

                  <div class="col-xs-6" style="padding-right:0px">

                    <div class="input-group">

                      <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago" required>

                        <option value="">Seleccione método de pago</option>
                        <?php
                        foreach ($mediosPago as $medio) {
                          $medio = trim($medio); // Eliminar espacios en blanco
                          echo '<option value="' . $medio . '">' . $medio . '</option>';
                        }
                        ?>

                      </select>

                    </div>

                  </div>

                  <div class="cajasMetodoPago"></div>

                  <input type="hidden" id="listaMetodoPago" name="listaMetodoPago">

                </div>

                <!--=====================================
                        ENTRADA ESTADO
                        ======================================-->
                <input type="hidden" name="estado" value="venta">

                <br>

              </div>

            </div>


            <!--<div class="box-footer">

                    <button type="submit" class="btn btn-primary pull-right">Guardar venta</button>
                    
                  </div>-->


            <div class="box-footer">
              <input type="hidden" name="editarVenta" value="<?php echo $venta["codigo"]; ?>">
              <input type="hidden" name="idVenta" value="<?php echo $venta["id"]; ?>">
              <input type="hidden" name="activarFacturaElectronica" value="1">
              <!-- Campo hidden garantiza que editarVentaFactus llega al servidor vía AJAX FormData -->
              <input type="hidden" name="editarVentaFactus" value="1">
              <button type="submit" class="btn btn-primary pull-right">Actualizar Borrador</button>
            </div>

          </form>



          <button class="btn btn-danger pull-left" onclick="location.href='facturas-electronicas'">Cancelar</button>

        </div>

      </div>

      <!--=====================================
            LA TABLA DE PRODUCTOS
            ======================================-->

      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

        <div class="box box-warning">

          <div class="box-header with-border"></div>

          <div class="box-body">

            <!--<table class="table table-bordered table-striped dt-responsive tablaVentas">-->
            <table class="table table-bordered table-striped tablaVentas">

              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Imagen</th>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Stock</th>
                  <th>Acciones</th>
                </tr>
              </thead>

            </table>

          </div>

        </div>

      </div>


    </div>

  </section>

</div>



<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->

<!-- Modal -->
<div id="modalAgregarCliente" class="modal-custom">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container">
    <form role="form" method="post" style="display: flex; flex-direction: column; height: 100%;">

      <!-- CABEZA DEL MODAL -->
      <div class="modal-custom-header">
        <h4 class="modal-title">Agregar cliente</h4>
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
      </div>

      <!-- CUERPO DEL MODAL -->
      <div class="modal-custom-body" style="text-align: left;">
        <div class="box-body">

          <!-- Fila 1: Nombre y Documento -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nombre Completo *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control" name="nuevoCliente" placeholder="Nombre del cliente" required>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Documento *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-key"></i></span>
                  <input type="number" min="0" class="form-control" name="nuevoDocumentoId" placeholder="Número de documento" required>
                </div>
              </div>
            </div>
          </div>

          <!-- Fila 2: Teléfono y Email -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Teléfono *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                  <input type="text" class="form-control" name="nuevoTelefono" placeholder="(300) 123-4567" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Email</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                  <input type="email" class="form-control" name="nuevoEmail" placeholder="correo@ejemplo.com">
                </div>
              </div>
            </div>
          </div>

          <!-- Fila 3: Municipio -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Municipio *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                  <select class="form-control" name="nuevoMunicipio" required>
                    <option value="">-- Seleccionar Municipio --</option>
                    <?php
                    require_once "modelos/factus.modelo.php";
                    $municipios = ModeloFactus::mdlObtenerMunicipios();
                    foreach ($municipios as $municipio) {
                      $textoMunicipio = $municipio['nombre'] . ' - ' . $municipio['departamento'];
                      echo "<option value='{$municipio['id_factus']}'>{$textoMunicipio}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Fila 4: Dirección -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Dirección *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-home"></i></span>
                  <input type="text" class="form-control" name="nuevaDireccion" placeholder="Calle, carrera, número, etc." required>
                </div>
              </div>
            </div>
          </div>

          <!-- Fila 5: Notas -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Notas</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                  <input type="text" class="form-control" name="nuevaNota" placeholder="Información adicional (opcional)">
                </div>
              </div>
            </div>
          </div>

          <!-- Campos ocultos -->
          <input type="hidden" name="activarFacturaElectronica" value="1">
          <input type="hidden" name="nuevoEstatus" value="nuevo">
          <input type="hidden" name="origen" value="crear-venta">
          <input type="hidden" name="vistaOrigen" value="crear-venta">

        </div>
      </div>

      <!-- PIE DEL MODAL -->
      <div class="modal-custom-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
        <button type="submit" class="btn btn-primary">Guardar cliente</button>
      </div>

    </form>

    <?php
    $crearCliente = new ControladorClientes();
    $crearCliente->ctrCrearCliente();
    ?>

  </div>

    <!--Verificar que tenga productos , antes de guardar la venta-->

    <script>
      $(document).on("submit", ".formularioVenta", function (e) {
        var listaProductos = $("#listaProductos").val();
        if (!listaProductos || listaProductos.trim() == "" || listaProductos.trim() == "[]") {
          e.preventDefault();
          e.stopImmediatePropagation();
          swal({
            type: "error",
            title: "La venta no se puede guardar porque no tiene productos",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          });
          return false;
        }
      });
    </script>


    <!--=====================================
        SCRIPT PARA MANEJAR DESCUENTOS
        ======================================-->
    <script>

      $(document).ready(function () {
        // Manejar checkbox de descuento por porcentaje

        $('#checkDescuentoPorcentaje').on('change', function () {
          if ($(this).is(':checked')) {
            // Desmarcar el otro checkbox
            $('#checkDescuentoFijo').prop('checked', false);

            // Mostrar campo de descuento
            $('#campoDescuento').slideDown();

            // Cambiar icono y texto a porcentaje
            $('#iconoDescuento').html('<i class="fa fa-percent"></i>');
            $('#labelDescuento').text('% Descuento');
            $('#textoAyudaDescuento').text('Ingrese el porcentaje de descuento (0-100)');
            $('#valorDescuento').attr('max', '100');
            $('#valorDescuento').attr('placeholder', '0');
            $('#valorDescuento').val('0');

            // Guardar tipo de descuento
            $('#tipoDescuento').val('porcentaje');
          } else {
            // Si se desmarca
            $('#campoDescuento').slideUp();
            $('#valorDescuento').val('0');
            $('#tipoDescuento').val('');
            $('#montoDescuento').val('0');

            // Recalcular total sin descuento
            sumarTotalPrecios();
            agregarImpuesto();
          }

        });

        // Manejar checkbox de descuento fijo
        $('#checkDescuentoFijo').on('change', function () {

          if ($(this).is(':checked')) {

            // Desmarcar el otro checkbox
            $('#checkDescuentoPorcentaje').prop('checked', false);

            // Mostrar campo de descuento
            $('#campoDescuento').slideDown();

            // Cambiar icono y texto a valor fijo
            $('#iconoDescuento').html('<i class="fa fa-money"></i>');
            $('#labelDescuento').text('Valor Descuento');
            $('#textoAyudaDescuento').text('Ingrese el valor fijo del descuento');
            $('#valorDescuento').removeAttr('max');
            $('#valorDescuento').attr('placeholder', '0');
            $('#valorDescuento').val('0');

            // Guardar tipo de descuento
            $('#tipoDescuento').val('fijo');

          } else {
            // Si se desmarca
            $('#campoDescuento').slideUp();
            $('#valorDescuento').val('0');
            $('#tipoDescuento').val('');
            $('#montoDescuento').val('0');

            // Recalcular total sin descuento
            sumarTotalPrecios();
            agregarImpuesto();
          }
        });

        // Manejar checkbox de descuento fijo
        $('#checkDescuentoFijo').on('ifChecked', function () {

          // Desmarcar el otro checkbox
          $('#checkDescuentoPorcentaje').iCheck('uncheck');
          checkboxActivo = 'fijo';

          // Mostrar campo de descuento
          $('#campoDescuento').slideDown();

          // Cambiar icono y texto a valor fijo
          $('#iconoDescuento').html('<i class="fa fa-money"></i>');
          $('#labelDescuento').text('Valor Descuento');
          $('#textoAyudaDescuento').text('Ingrese el valor fijo del descuento');
          $('#valorDescuento').removeAttr('max');
          $('#valorDescuento').attr('placeholder', '0');
          $('#valorDescuento').val('0');

          // Guardar tipo de descuento
          $('#tipoDescuento').val('fijo');
        });

        $('#checkDescuentoFijo').on('ifUnchecked', function () {

          checkboxActivo = null;
          $('#campoDescuento').slideUp();
          $('#valorDescuento').val('0');
          $('#tipoDescuento').val('');
          $('#montoDescuento').val('0');

          // Recalcular total sin descuento
          sumarTotalPrecios();
          agregarImpuesto();
        });

        // Cuando cambia el valor del descuento, recalcular
        $('#valorDescuento').on('change keyup', function () {
          aplicarDescuento();
        });

      });

    </script>


  </div>

  <!--=====================================
  MODAL AGREGAR RETENCION
  ======================================-->
  <div id="modalAgregarRetencion" class="modal-custom">
    <div class="modal-custom-backdrop" data-dismiss="modal"></div>
    <div class="modal-custom-container" style="max-width: 500px;">
      <form role="form" method="post" id="formularioRetencion" style="display: flex; flex-direction: column; height: 100%;">
        <div class="modal-custom-header">
          <h4 class="modal-title">Agregar Retención</h4>
          <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
        </div>
        <div class="modal-custom-body" style="text-align: left;">
          <div class="box-body">
            <!-- Tipo de retencion -->
            <div class="form-group">
              <label>Tipo Retención</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                <select class="form-control input-lg" id="nuevoTipoRetencion" name="nuevoTipoRetencion">
                  <option value="">Seleccionar tipo</option>
                  <option value="ReteIVA">ReteIVA</option>
                  <option value="ReteRenta">ReteRenta</option>
                </select>
              </div>
            </div>
            <!-- Porcentaje -->
            <div class="form-group">
              <label>Porcentaje</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                <select class="form-control input-lg" id="nuevoPorcentajeRetencion" name="nuevoPorcentajeRetencion">
                  <option value="">Seleccionar porcentaje</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-custom-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="button" class="btn btn-primary" id="guardarRetencion" data-dismiss="modal">Guardar</button>
        </div>
      </form>
    </div>
  </div>

</div>

<!--=====================================
MODAL AGREGAR RETENCION
======================================-->
<div id="modalAgregarRetencionNuevo" class="modal-custom">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container" style="max-width: 500px;">
    <form role="form" method="post" id="formularioRetencionNuevo" style="display: flex; flex-direction: column; height: 100%;">
      <div class="modal-custom-header">
        <h4 class="modal-title">Agregar Retención</h4>
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
      </div>
      <div class="modal-custom-body" style="text-align: left;">
        <div class="box-body">
          <!-- Tipo de retencion -->
          <div class="form-group">
            <label>Tipo Retención</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-th"></i></span>
              <select class="form-control input-lg" id="nuevoTipoRetencionNuevo" name="nuevoTipoRetencion">
                <option value="">Seleccionar tipo</option>
                <option value="ReteIVA">ReteIVA</option>
                <option value="ReteRenta">ReteRenta</option>
              </select>
            </div>
          </div>
          <!-- Porcentaje -->
          <div class="form-group">
            <label>Porcentaje</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-percent"></i></span>
              <select class="form-control input-lg" id="nuevoPorcentajeRetencionNuevo" name="nuevoPorcentajeRetencion">
                <option value="">Seleccionar porcentaje</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-custom-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
        <button type="button" class="btn btn-primary" id="guardarRetencionNuevo" data-dismiss="modal">Guardar</button>
      </div>
    </form>
  </div>
</div>
</div>

<script>
  // Pre-cargar datos de la factura existente
  $(document).ready(function () {
    // Seleccionar el cliente
    $("#seleccionarCliente").val("<?php echo $venta['id_cliente']; ?>");

    // Seleccionar el método de pago
    var metodoPago = "<?php echo $venta['metodo_pago']; ?>";
    var metodoEncontrado = false;
    var codigoTransaccion = "";
    
    // Normalizar valor BD
    var metodoPagoNorm = metodoPago.trim();

    // 1. Intentar coincidencia EXACTA primero
    $("#nuevoMetodoPago option").each(function() {
        if ($(this).text().trim() === metodoPagoNorm) {
            $(this).prop('selected', true);
            metodoEncontrado = true;
            return false;
        }
    });

    // 2. Si no, buscar si empieza con alguna opción + "-" (Formato: Metodo-Codigo)
    if (!metodoEncontrado) {
        $("#nuevoMetodoPago option").each(function() {
             var opcionTexto = $(this).text().trim();
             // Buscar "Metodo-" al inicio del valor de la BD
             // Usar un separador seguro, en ventas.js es "-"
             if (metodoPagoNorm.indexOf(opcionTexto + "-") === 0) {
                 $(this).prop('selected', true);
                 metodoEncontrado = true;
                 // Extraer el código (lo que está después del guión)
                 codigoTransaccion = metodoPagoNorm.substring(opcionTexto.length + 1);
                 return false;
             }
        });
    }

    // 3. Fallback: Si definitivamente no está en la lista, agregarla
    if (!metodoEncontrado && metodoPagoNorm) {
         console.warn("Metodo de pago histórico no encontrado, agregando opción:", metodoPago);
         var newOption = new Option(metodoPago, metodoPago, true, true);
         $("#nuevoMetodoPago").append(newOption);
    }
    
    // 4. Activar eventos y poner código si existe
    if (metodoEncontrado) {
        $("#nuevoMetodoPago").trigger('change');
        if (codigoTransaccion) {
            setTimeout(function() {
                $("#nuevoCodigoTransaccion").val(codigoTransaccion);
            }, 200); // Pequeño delay para asegurar que el input se haya renderizado
        }
    }

    // Cargar los productos existentes
    <?php if (!empty($productosVenta) && is_array($productosVenta)): ?>
      var productosExistentes = <?php echo json_encode($productosVenta); ?>;

      // Agregar cada producto al formulario
      productosExistentes.forEach(function (producto) {
        // Simular el clic en agregar producto con los datos existentes
        agregarProductoExistente(producto);
      });
    <?php endif; ?>

    // --- CARGAR DESCUENTOS ---
    var tipoDescuento = "<?php echo isset($venta['tipo_descuento']) ? $venta['tipo_descuento'] : ''; ?>";
    var valorDescuento = parseFloat("<?php echo isset($venta['valor_descuento']) ? $venta['valor_descuento'] : 0; ?>") || 0;

    if (tipoDescuento != "") {
      setTimeout(function() {
        if (tipoDescuento == "fijo") {
          $('#checkDescuentoFijo').iCheck('check');
          // Forzar manualmente estado por si evento falla
          $('#tipoDescuento').val('fijo');
          $('#valorDescuento').removeAttr('max');
        } else {
          $('#checkDescuentoPorcentaje').iCheck('check');
          // Forzar manualmente estado por si evento falla
          $('#tipoDescuento').val('porcentaje');
          $('#valorDescuento').attr('max', '100');
        }
        
        // Usar setTimeout anidado para asegurar que la asignación del valor ocurra 
        // DESPUÉS de que el evento 'ifChecked' (que resetea el valor a 0) se haya ejecutado.
        setTimeout(function() {
            var val = parseFloat(valorDescuento);
            if (val > 0) {
               $('#valorDescuento').val(val);
               $('#montoDescuento').val(val); // Por si acaso
               $('#campoDescuento').show(); // Forzar visualización
               aplicarDescuento();
            }
        }, 300);
        
      }, 500); // Retraso inicial para iCheck
    }

    // --- CARGAR RETENCIONES ---
    <?php
    $retencionesJson = isset($venta['retenciones']) ? $venta['retenciones'] : '[]';
    // Limpiar posibles caracteres escapados siel JSON viene como string sucio
    if (is_string($retencionesJson)) {
      $retencionesJson = html_entity_decode($retencionesJson);
    }
    if (empty($retencionesJson) || $retencionesJson == 'null')
      $retencionesJson = '[]';
    ?>

    var retencionesGuardadas = [];
    try {
       retencionesGuardadas = <?php echo $retencionesJson; ?>;
    } catch(e) {
       console.error("Error parseando retenciones JSON", e);
       retencionesGuardadas = [];
    }

    if (retencionesGuardadas && retencionesGuardadas.length > 0) {
      setTimeout(function() {
        // Asegurar que la variable global exista y asignarla
        window.retencionesAplicadas = retencionesGuardadas;
        
      // Actualizar visualización
        if (typeof actualizarVisualizacionRetenciones === 'function') {
          actualizarVisualizacionRetenciones();
        }
      }, 800); // Retraso para esperar a que ventas.js defina sus funciones
    }
  });

  // Función para agregar un producto existente al formulario
  function agregarProductoExistente(producto) {
    var datos = new FormData();
    datos.append("idProducto", producto.id);

    $.ajax({
      url: "ajax/productos.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(respuesta) {
        var descripcion = respuesta["descripcion"];
        var descripcion = respuesta["descripcion"];
        var stock = Number(respuesta["stock"]);
        var precioUnitario = Number(producto.precio);
        var cantidad = Number(producto.cantidad);
        var precioTotal = producto.total ? Number(producto.total) : (precioUnitario * cantidad);
        
        // Calcular impuesto del producto (Precio incluye impuesto)
        var impuestoPorcentaje = respuesta["impuesto_porcentaje"] ? Number(respuesta["impuesto_porcentaje"]) : 0;
        var impuestoNombre = respuesta["impuesto_nombre"] ? respuesta["impuesto_nombre"] : "Exento";
        
        // Limpiar nombre del impuesto
        var nombreCorto = impuestoNombre.split(/[0-9]/)[0].trim();
      
        // Agregar el producto a la interfaz
        $(".nuevoProducto").append(
          '<div class="row" style="padding:5px 15px">' +
          
            '<!--Descripcion del producto-->' +
            '<div class="col-xs-5" style="padding-right:0px">' +
              '<div class="input-group">' +
                '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="' + producto.id + '"><i class="fa fa-times"></i></button></span>' +
                '<input type="text" class="form-control nuevaDescripcionProducto" idProducto="' + producto.id + '" name="agregarProducto" value="' + descripcion + '" readonly required>' +
              '</div>' +
            '</div>' +

            '<!--Impuesto del producto (col-xs-2)-->' +
            '<div class="col-xs-2 ingresoImpuesto">' +
              '<input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' + nombreCorto + ' ' + impuestoPorcentaje + '%" porcentaje="' + impuestoPorcentaje + '" impuestoNombre="' + impuestoNombre + '" readonly required>' +
            '</div>' +

            '<!--Cantidad del producto-->' +
            '<div class="col-xs-2 ingresoCantidad">' +
              '<input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="' + cantidad + '" stock="' + stock + '" nuevoStock="' + (Number(stock) + Number(cantidad)) + '" required>' +
            '</div>' +
            
            '<!--Precio del producto (Debe mostrar el TOTAL de la fila)-->' +
            '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">' +
              '<div class="input-group">' +
                '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>' +
                '<input type="text" class="form-control nuevoPrecioProducto" precioReal="' + precioUnitario + '" name="nuevoPrecioProducto" value="' + precioTotal + '" readonly required>' +
              '</div>' +
            '</div>' +
            
          '</div>'
        );

        // Sumar el total de precios
        sumarTotalPrecios();
        
        // Agregar impuesto
        agregarImpuesto();
        
        // Sumar total impuestos si la función existe (de ventas.js)
        if (typeof sumarTotalImpuestos === 'function') {
            sumarTotalImpuestos();
        }

        // Agrupar productos en formato JSON
        listarProductos();
      }
    });
  }
</script>

<?php

$crearFactura = new ControladorVentas();
$crearFactura->ctrCrearVenta();

?>