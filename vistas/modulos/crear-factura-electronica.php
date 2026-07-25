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

          <div class="box-header with-border">
            <?php
              $feNombreEmpresa  = isset($configFactus['nombre_empresa'])   && !empty($configFactus['nombre_empresa'])   ? $configFactus['nombre_empresa']   : ($configuracion['nombre_empresa'] ?? '');
              $feNombreComercial = isset($configFactus['nombre_comercial']) && !empty($configFactus['nombre_comercial']) ? $configFactus['nombre_comercial'] : '';
              $feNitEmpresa     = isset($configFactus['nit_empresa'])      && !empty($configFactus['nit_empresa'])      ? $configFactus['nit_empresa']      : ($configuracion['nit'] ?? '');
              $feDvEmpresa      = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1' && isset($configFactus['dv']) && $configFactus['dv'] !== '') ? ' - ' . $configFactus['dv'] : '';
              $feNitConDv       = $feNitEmpresa . $feDvEmpresa;
            ?>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
              <?php if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa']) && file_exists($configFactus['logo_empresa'])): ?>
                <img src="<?php echo $configFactus['logo_empresa']; ?>" style="max-height:48px; border-radius:4px;">
              <?php endif; ?>
              <div>
                <?php if (!empty($feNombreEmpresa)): ?>
                  <strong style="font-size:15px;"><?php echo $feNombreEmpresa; ?></strong><br>
                <?php endif; ?>
                <?php if (!empty($feNombreComercial)): ?>
                  <span style="color:#555; font-size:13px;"><i class="fa fa-tag" style="margin-right:4px;"></i><?php echo $feNombreComercial; ?></span><br>
                <?php endif; ?>
                <?php if (!empty($feNitConDv)): ?>
                  <span style="color:#777; font-size:12px;"><strong>NIT:</strong> <?php echo $feNitConDv; ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <form role="form" method="post" class="formularioVenta">

            <?php CSRF::insertToken(); ?>

            <div class="box-body">

              <div class="box">

                <!--=====================================
                      ENTRADA DEL VENDEDOR
                      ======================================-->

                <!--=====================================
                      ENCABEZADO FE: VENDEDOR (fila propia)
                      ======================================-->

                <div class="row">
                  <div class="col-xs-12">
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
                </div>

                <!--=====================================
                      FORMATO Y CÓDIGO FE (misma fila)
                      ======================================-->

                <div class="row">
                  <div class="col-xs-6">
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

                  <div class="col-xs-6">
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
                <!--BTN SE MUESTRA EN CELULARES (xs) Y TABLETS (sm, md)-->
                <button type="button" class="btn btn-warning btn-block btnAgregarProducto visible-xs visible-sm visible-md" style="margin-top: 10px; margin-bottom: 15px; font-weight: bold;">
                  <i class="fa fa-plus"></i> Agregar producto
                </button>

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
                <input type="hidden" name="activarFacturaElectronica" value="1">
                <input type="hidden" name="guardarVentaFactus" value="1">

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
<div id="modalAgregarCliente" class="modal-custom">
  <div class="modal-custom-backdrop" data-dismiss="modal"></div>
  <div class="modal-custom-container">
    <form role="form" method="post" style="display: flex; flex-direction: column; height: 100%;">

      <?php CSRF::insertToken(); ?>

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->
      <div class="modal-custom-header">
        <h4 class="modal-title">Agregar cliente</h4>
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; margin-top: -2px;">&times;</button>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->
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
          <input type="hidden" name="origen" value="crear-factura-electronica">
          <input type="hidden" name="vistaOrigen" value="crear-factura-electronica">

        </div>
      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->
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

    <!--
        SUBMIT AJAX CON MODAL "GUARDANDO" ESTANDARIZADO
        Patrón idéntico al de notas-credito.js, documentos-soporte.js y notas-ajuste-ds.js
    -->
    <script>
      $(document).on("submit", ".formularioVenta", function (e) {
        e.preventDefault();
        var form = this;

        // 1. Validar productos
        var listaProductos = $("#listaProductos").val();
        if (!listaProductos || listaProductos.trim() == "" || listaProductos.trim() == "[]") {
          swal({
            type: "error",
            title: "La venta no se puede guardar porque no tiene productos",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          });
          return false;
        }

        // VALIDACIÓN DE RETEIVA PARA PRODUCTOS SIN IVA (DIAN/Factus)
        var inputRetenciones = $("#datosRetenciones").val();
        if (inputRetenciones && inputRetenciones.trim() !== "" && inputRetenciones.trim() !== "[]") {
          var retenciones = [];
          var productosObj = [];
          try {
            retenciones = JSON.parse(inputRetenciones);
            productosObj = JSON.parse(listaProductos);
          } catch(err) {}

          var tieneReteIva = retenciones.some(function(r) { return r.tipo === "ReteIVA"; });
          var todosProductosSinIva = false;
          if (tieneReteIva && productosObj && productosObj.length > 0) {
            var algunProductoGeneraIva = productosObj.some(function(p) {
              var tax = parseFloat(p.impuesto);
              var nombreTax = p.impuestoNombre ? p.impuestoNombre.toUpperCase() : "";
              return !isNaN(tax) && tax > 0 && nombreTax.indexOf("IVA") !== -1;
            });
            todosProductosSinIva = !algunProductoGeneraIva;
          }

          if (tieneReteIva && todosProductosSinIva) {
            swal({
              title: "Error de Retención",
              text: "No se puede aplicar ReteIVA si la factura contiene únicamente productos sin IVA (Exentos o Excluidos).",
              type: "error",
              confirmButtonText: "Cerrar"
            });
            return false;
          }
        }

        // 2. Validar cliente
        var idCliente = $("#seleccionarCliente").val();
        if (!idCliente || idCliente == "") {
          swal({
            type: "error",
            title: "Error",
            text: "Debe seleccionar un cliente antes de guardar.",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          });
          return false;
        }

        // 3. Validar fecha de vencimiento si es crédito
        if ($('#forma_pago_dian').val() === '2' && !$('#fecha_vencimiento_fe').val()) {
          swal({
            type: 'warning',
            title: 'Fecha de Vencimiento requerida',
            text: 'Para facturas a crédito debe ingresar la fecha de vencimiento.',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar'
          });
          return false;
        }

        // 4. Modal de confirmación (igual al patrón NC/DS/NA)
        swal({
          title: '¿Está seguro de guardar esta Factura Electrónica?',
          text: "Se guardará en el sistema como borrador y podrá firmarla y enviarla a la DIAN después.",
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancelar',
          confirmButtonText: 'Sí, guardar'
        }).then(function (result) {
          if (result.value) {

            // 5. Modal de carga (idéntico al de notas-credito.js)
            var boton = $(form).find("button[type='submit']");
            boton.prop('disabled', true);
            var htmlOriginal = boton.html();
            boton.html('<i class="fa fa-spinner fa-spin"></i>');

            swal({
              title: 'Firmando Factura Electrónica',
              text: 'Por favor espere mientras se procesa la información...',
              type: 'info',
              allowOutsideClick: false,
              showConfirmButton: false,
              onBeforeOpen: () => {
                swal.showLoading()
              }
            });

            // 6. Enviar por AJAX al endpoint puro (sin router ni layout HTML)
            var datos = new FormData(form);
            datos.append("accion", "crearFacturaElectronica");

            $.ajax({
              url: 'ajax/factus.ajax.php',
              method: "POST",
              data: datos,
              cache: false,
              contentType: false,
              processData: false,
              dataType: "json",
              success: function (respuesta) {
                if (respuesta.status === "success") {
                  swal({
                    type: "success",
                    title: respuesta.titulo || "¡Factura Electrónica guardada correctamente!",
                    text: respuesta.mensaje || "El documento ha sido registrado exitosamente en el sistema.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                  }).then(function (res) {
                    if (res.value) {
                      window.location = "facturas-electronicas";
                    }
                  });
                } else {
                  var msjError = respuesta.mensaje || "No se pudo guardar la factura.";
                  var esErrorToken = /token|expirado|autenticar/i.test(msjError);

                  if (esErrorToken) {
                    swal({
                      type: "warning",
                      title: "Token de Factus Expirado",
                      html: '<p style="font-size: 14px; color: #555; text-align: left; background: #fff8e1; padding: 10px; border-radius: 4px; border-left: 4px solid #ffc107;">' + msjError + '</p>' +
                            '<p style="margin-top: 15px; font-weight: bold; color: #3c8dbc;">¿Desea autenticar y renovar el token en este momento sin salir de la página?</p>',
                      showCancelButton: true,
                      confirmButtonColor: "#3c8dbc",
                      cancelButtonColor: "#d33",
                      confirmButtonText: '<i class="fa fa-key"></i> Autenticar y obtener tokens',
                      cancelButtonText: "Cerrar",
                      allowOutsideClick: false
                    }).then(function (resAuthModal) {
                      if (resAuthModal.value) {
                        swal({
                          title: 'Autenticando con Factus...',
                          text: 'Obteniendo nuevos tokens de acceso...',
                          type: 'info',
                          showConfirmButton: false,
                          allowOutsideClick: false,
                          onBeforeOpen: () => {
                            swal.showLoading()
                          }
                        });

                        $.ajax({
                          url: 'ajax/factus.ajax.php',
                          method: 'POST',
                          data: {
                            accion: 'autenticar',
                            csrf_token: $('meta[name="csrf-token"]').attr('content')
                          },
                          dataType: 'json',
                          success: function (resAuth) {
                            if (!resAuth.error) {
                              swal({
                                type: 'success',
                                title: '¡Autenticación Exitosa!',
                                text: 'Los tokens de Factus han sido renovados correctamente. Ya puede presionar nuevamente el botón para guardar la factura.',
                                showConfirmButton: true,
                                confirmButtonText: 'Entendido'
                              });
                            } else {
                              swal({
                                type: 'error',
                                title: 'Error al Autenticar',
                                text: resAuth.mensaje || 'No se pudieron renovar los tokens automáticamente. Verifique las credenciales en Configuración.',
                                showConfirmButton: true,
                                confirmButtonText: 'Cerrar'
                              });
                            }
                          },
                          error: function () {
                            swal({
                              type: 'error',
                              title: 'Error de Conexión',
                              text: 'Ocurrió un error al intentar comunicar con el servidor para renovar los tokens.',
                              showConfirmButton: true,
                              confirmButtonText: 'Cerrar'
                            });
                          }
                        });
                      }
                    });
                  } else {
                    swal({
                      type: "error",
                      title: respuesta.titulo || "Error al guardar la factura",
                      html: msjError,
                      showConfirmButton: true,
                      confirmButtonText: "Cerrar"
                    });
                  }
                  boton.prop('disabled', false);
                  boton.html(htmlOriginal);
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX FE:", jqXHR.responseText);
                swal({
                  type: "error",
                  title: "Error del Sistema",
                  text: "No se pudo comunicar con el servidor. Por favor intente nuevamente.",
                  showConfirmButton: true,
                  confirmButtonText: "Cerrar"
                });
                boton.prop('disabled', false);
                boton.html(htmlOriginal);
              }
            });
          }
        });
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

        // NOTA: La validación de fecha de vencimiento al submit
        // queda consolidada en el handler AJAX principal (arriba).

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
MODAL AGREGAR RETENCION NUEVO
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

<?php

$crearFactura = new ControladorVentas();
$crearFactura->ctrCrearVenta();

?>

<script>
/* =============================================
   VALIDAR DOCUMENTO DUPLICADO - MODAL AGREGAR CLIENTE
   (crear-factura-electronica)
   ============================================= */
$(document).on("submit", "#modalAgregarCliente form", function (e) {
  e.preventDefault();
  var form = this;
  var documento = $(form).find('[name="nuevoDocumentoId"]').val();

  if (!documento || documento.trim() === "") {
    return; // La validación HTML nativa de "required" se encargará
  }

  var csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: {
      validarDocumento: documento,
      csrf_token: csrfToken
    },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.existe) {
        swal({
          type: "warning",
          title: "Documento ya registrado",
          text: respuesta.mensaje,
          showConfirmButton: true,
          confirmButtonText: "Entendido"
        });
      } else {
        // No hay duplicado, enviar el formulario normalmente
        form.submit();
      }
    },
    error: function () {
      // Si hay error de conexión, permitir envío para no bloquear al usuario
      form.submit();
    }
  });
});
</script>