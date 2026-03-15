<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$impuestoDefecto = !empty($configuracion["impuesto_defecto"]) ? $configuracion["impuesto_defecto"] : 0;

// Obtener datos de Factus (Rango y Prefijo)
$rangoFactus = ModeloFactus::mdlObtenerRangoActivo();
$prefijoFactus = $rangoFactus ? $rangoFactus["prefijo"] : "FE"; // FE por defecto si no hay rango
$resolucionId = $rangoFactus ? $rangoFactus["id"] : 0;

$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");

// ---------------------------------------------------------
// VALIDACIÓN DE CONSECUTIVO (NO PERMITIR CREAR SI LA ANTERIOR NO ESTÁ FIRMADA)
// ---------------------------------------------------------
// Buscamos específicamente la última FACTURA ELECTRÓNICA (con resolución DIAN)
// Ignoramos ventas POS normales y órdenes.
$ultimaVenta = ControladorVentas::ctrMostrarUltimaFacturaElectronica();

if ($ultimaVenta) {
  // Si la última venta NO tiene número de factura o no está enviada/aceptada, bloquear.
  // Estados válidos: 'enviada' (recién firmada) o 'aceptada' (confirmada por DIAN)
  // Verificamos numero_factura en lugar de cufe porque es más confiable
  $estadosValidos = ['enviada', 'aceptada'];
  if (!in_array($ultimaVenta["estado_dian"], $estadosValidos) || empty($ultimaVenta["numero_factura"])) {

    echo '
		<script>
		  swal({
		    type: "warning",
		    title: "Bloqueo de Consecutivo",
		    text: "No se puede crear una nueva factura porque la anterior (' . $ultimaVenta["codigo"] . ') aún no ha sido FIRMADA y ENVIADA a la DIAN. Debe firmar las facturas en orden secuencial.",
		    showConfirmButton: true,
		    confirmButtonText: "Ir a Facturas Electrónicas"
		  }).then(function (result) {
		    if (result.value) {
		      window.location = "facturas-electronicas";
		    }
		  })
		</script>';
    // Detener la renderización del resto de la página
    return;
  }
}
?>

<style>
  @media (min-width: 769px) {
    .solo-movil {
      display: none !important;
    }
  }
</style>


<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Crear Factura Electrónica
    </h1>

    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Crear Factura Electrónica</li>
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
                        <input type="hidden" name="rutaOrigen" value="crear-factura-electronica">
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

                <!-- Forma de Pago DIAN -->
                <div class="row">
                  <div class="col-xs-12 col-md-6">
                    <div class="form-group">
                      <label><i class="fa fa-credit-card"></i> Forma de Pago</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-exchange"></i></span>
                        <select class="form-control" id="forma_pago_dian" name="forma_pago_dian">
                          <option value="1" selected>Contado (Pago inmediato)</option>
                          <option value="2">Crédito (Pago a plazo)</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-xs-12 col-md-6" id="campo_fecha_vencimiento" style="display:none;">
                    <div class="form-group">
                      <label><i class="fa fa-calendar"></i> Fecha de Vencimiento</label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="date" class="form-control" id="fecha_vencimiento_fe" name="fecha_vencimiento"
                          placeholder="YYYY-MM-DD">
                      </div>
                      <small class="text-muted">Requerido para pago a crédito</small>
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
                          <button type="button" class="btn btn-default btn-xs" data-toggle="modal"
                            data-target="#modalAgregarCliente" data-dismiss="modal">Agregar cliente</button>
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
                    <button type="button" class="btn btn-default" data-toggle="modal"
                      data-target="#modalAgregarRetencionNuevo">Retenciones</button>
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
              <button type="submit" name="guardarVentaFactus" class="btn btn-primary pull-right">Guardar</button>
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
<div id="modalAgregarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- Fila 1: Nombre y Documento -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre Completo *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="nuevoCliente" placeholder="Nombre del cliente"
                      required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Documento *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input type="number" min="0" class="form-control" name="nuevoDocumentoId"
                      placeholder="Número de documento" required>
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
                    <input type="text" class="form-control" name="nuevoTelefono" placeholder="(300) 123-4567"
                      data-inputmask="'mask':'(999) 999-9999'" data-mask required>
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
                    <input type="text" class="form-control" name="nuevaDireccion"
                      placeholder="Calle, carrera, número, etc." required>
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
                    <input type="text" class="form-control" name="nuevaNota"
                      placeholder="Información adicional (opcional)">
                  </div>
                </div>
              </div>
            </div>

            <!-- Campos ocultos -->
            <input type="hidden" name="activarFacturaElectronica" value="1">
            <input type="hidden" name="nuevoEstatus" value="nuevo">
            <input type="hidden" name="origen" value="crear-venta">
            <input type="hidden" name="vistaOrigen" value="crear-venta">


            <!-- entrada para la fecha naciminiento -->
            <!--
           <div class="form-group">         
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>
             </div>
           </div>
          -->


          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

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
        if (listaProductos == "" || listaProductos == "[]") {
          e.preventDefault();
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

    <!-- FORMA DE PAGO: mostrar/ocultar fecha vencimiento -->
    <script>
      $(document).ready(function () {

        // Toggle del campo fecha de vencimiento
        $('#forma_pago_dian').on('change', function () {
          if ($(this).val() === '2') {
            $('#campo_fecha_vencimiento').slideDown(200);
            $('#fecha_vencimiento_fe').attr('required', true);
          } else {
            $('#campo_fecha_vencimiento').slideUp(200);
            $('#fecha_vencimiento_fe').removeAttr('required').val('');
          }
        });

        // Validar al enviar: si es crédito, exigir fecha
        $(document).on('submit', '.formularioVenta', function (e) {
          if ($('#forma_pago_dian').val() === '2' && !$('#fecha_vencimiento_fe').val()) {
            e.preventDefault();
            swal({
              type: 'warning',
              title: 'Fecha de Vencimiento requerida',
              text: 'Para facturas a crédito debe ingresar la fecha de vencimiento.',
              showConfirmButton: true,
              confirmButtonText: 'Aceptar'
            });
            return false;
          }
        });

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
  <div id="modalAgregarRetencion" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form role="form" method="post" id="formularioRetencion">

          <!-- CABEZA DEL MODAL -->
          <div class="modal-header" style="background:#3c8dbc; color: white">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Agregar Retención</h4>
          </div>

          <!-- CUERPO DEL MODAL -->
          <div class="modal-body">
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

          <!-- PIE DEL MODAL -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
            <button type="button" class="btn btn-primary" id="guardarRetencion" data-dismiss="modal">Guardar</button>
          </div>

        </form>
      </div>
    </div>
  </div>

</div>

<!--=====================================
MODAL AGREGAR RETENCION
======================================-->
<div id="modalAgregarRetencionNuevo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formularioRetencionNuevo">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Retención</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">
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
                <select class="form-control input-lg" id="nuevoPorcentajeRetencionNuevo"
                  name="nuevoPorcentajeRetencion">
                  <option value="">Seleccionar porcentaje</option>
                </select>
              </div>
            </div>

          </div>
        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="button" class="btn btn-primary" id="guardarRetencionNuevo" data-dismiss="modal">Guardar</button>
        </div>

      </form>
    </div>
  </div>
</div>
</div>

<?php

$crearFactura = new ControladorVentas();
$crearFactura->ctrCrearVenta();

?>