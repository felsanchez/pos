<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$impuestoDefecto = !empty($configuracion["impuesto_defecto"]) ? $configuracion["impuesto_defecto"] : 0;
$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");

// Obtener datos de Factus (Rango y Prefijo)
$rangoFactus = ModeloFactus::mdlObtenerRangoActivo();
$prefijoFactus = $rangoFactus ? $rangoFactus["prefijo"] : "FE";
$resolucionId = $rangoFactus ? $rangoFactus["id"] : 0;

// ---------------------------------------------------------
// VALIDACIÓN DE CONSECUTIVO (NO PERMITIR CREAR SI LA ANTERIOR NO ESTÁ FIRMADA)
// ---------------------------------------------------------
$ultimaVenta = ControladorVentas::ctrMostrarUltimaFacturaElectronica();

if ($ultimaVenta) {
  $estadosValidos = ['enviada', 'aceptada'];
  if (!in_array($ultimaVenta["estado_dian"], $estadosValidos) || empty($ultimaVenta["numero_factura"])) {
    echo '
		<script>
		  swal({
		    type: "warning",
		    title: "Bloqueo de Consecutivo",
		    text: "No se puede convertir a factura porque la anterior (' . $ultimaVenta["codigo"] . ') aún no ha sido FIRMADA y ENVIADA a la DIAN. Debe firmar las facturas en orden secuencial.",
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

// Obtener la orden a convertir
$item = "id";
$valor = $_GET["idVenta"];
$venta = ControladorVentas::ctrMostrarVentas($item, $valor);

// Datos del vendedor y cliente
$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
$cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);

// Obtener el siguiente código de Factus
$siguienteNumero = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus();
// DEBUG
// echo "Siguiente: " . $siguienteNumero;

// Lista de productos de la orden
$listaProducto = json_decode($venta["productos"], true);
$totalProductos = 0;
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
            <i class="fa fa-file-text-o" style="color:#605ca8;"></i>
            Convertir Orden a Factura Electrónica
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="ordenes">Órdenes</a></li>
            <li class="active">Convertir a FE</li>
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
                        <h3 class="box-title">
                            <span class="label label-warning" style="font-size:13px;">Orden #
                                <?php echo $venta["codigo"]; ?>
                            </span>
                            &nbsp;→&nbsp;
                            <span class="label label-primary" style="font-size:13px; background:#605ca8;">Factura
                                Electrónica</span>
                        </h3>
                    </div>

                    <form role="form" method="post" class="formularioVenta">

        <?php CSRF::insertToken(); ?>
                        <div class="box-body">
                            <div class="box">

                                <!--=====================================
                      ENCABEZADO FE: VENDEDOR (fila propia)
                      ======================================-->

                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label>Vendedor</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                                <input type="text" class="form-control"
                                                    value="<?php echo $vendedor["nombre"]; ?>" readonly>
                                                <input type="hidden" name="idVendedor"
                                                    value="<?php echo $vendedor["id"]; ?>">
                                                <input type="hidden" name="rutaOrigen"
                                                    value="orden-a-factura-electronica">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--=====================================
                      PREFIJO DIAN Y CÓDIGO FE (misma fila)
                      ======================================-->

                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>Prefijo DIAN</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                                                <input type="text" class="form-control"
                                                    value="<?php echo $prefijoFactus; ?>" readonly>
                                                <input type="hidden" name="resolucion_id"
                                                    value="<?php echo $resolucionId; ?>">
                                                <input type="hidden" name="formatoCodigoVenta"
                                                    value="<?php echo $prefijoFactus; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label>Código FE</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                <input type="text" class="form-control" id="nuevaVenta"
                                                    name="nuevaVenta" value="<?php echo $siguienteNumero; ?>" readonly>
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
                                                <select class="form-control" id="forma_pago_dian"
                                                    name="forma_pago_dian">
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
                                                <input type="date" class="form-control" id="fecha_vencimiento_fe"
                                                    name="fecha_vencimiento" placeholder="YYYY-MM-DD">
                                            </div>
                                            <small class="text-muted">Requerido para pago a crédito</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cliente (pre-cargado de la orden) -->
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label>Cliente</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                                <select class="form-control" id="seleccionarCliente"
                                                    name="seleccionarCliente" required>
                                                    <option value="<?php echo $cliente["id"]; ?>" selected>
                                                        <?php echo $cliente["nombre"]; ?>
                                                    </option>
                                                    <?php
                                                    $clientes = ControladorClientes::ctrMostrarClientes(null, null);
                                                    foreach ($clientes as $c) {
                                                        if ($c["id"] != $cliente["id"]) {
                                                            echo '<option value="' . $c["id"] . '">' . $c["nombre"] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <span class="input-group-addon">
                                                    <button type="button" class="btn btn-default btn-xs"
                                                        data-toggle="modal" data-target="#modalAgregarCliente"
                                                        data-dismiss="modal">Agregar cliente</button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!--=====================================
                      PRODUCTOS PRE-CARGADOS DE LA ORDEN
                      ======================================-->

                                <div class="form-group nuevoProducto">

                                    <?php
                                    foreach ($listaProducto as $key => $val) {
                                        $totalProductos += $val["total"];
                                        $prod = ControladorProductos::ctrMostrarProductos("id", $val["id"], "id");
                                        $stockAntiguo = $prod["stock"] + $val["cantidad"];

                                        // Obtener información del tributo para Factus
                                        $impuestoPorcentaje = 0;
                                        $impuestoNombre = "Exento";
                                        $impuestoNombreCorto = "EXE";

                                        if (isset($prod["tributo_id"]) && $prod["tributo_id"] != 0) {
                                            $tributo = ModeloFactus::mdlMostrarTributo($prod["tributo_id"]);
                                            if ($tributo) {
                                                $impuestoPorcentaje = $tributo["porcentaje_defecto"];
                                                $impuestoNombre = $tributo["nombre"];
                                                // Extraer nombre corto (ej: IVA, INC)
                                                $partesNombre = explode(' ', $impuestoNombre);
                                                $impuestoNombreCorto = $partesNombre[0];
                                            }
                                        }

                                        $camposVariante = '';
                                        if (isset($val["esVariante"]) && $val["esVariante"] == "1") {
                                            $camposVariante = '<input type="hidden" class="esVariante" value="1">
                                         <input type="hidden" class="idVarianteProducto" value="' . $val["idVariante"] . '">
                                         <input type="hidden" class="skuVariante" value="' . $val["skuVariante"] . '">';
                                        }

                                        echo '<div class="row" style="padding:5px 15px">
                                            <!-- Descripción -->
                                            <div class="col-xs-5" style="padding-right:0px">
                                                <div class="input-group">

                                                    <span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="' . $val["id"] . '"><i class="fa fa-times"></i></button></span>
                                                    <input type="text" class="form-control nuevaDescripcionProducto" idProducto="' . $val["id"] . '" name="agregarProducto" value="' . $val["descripcion"] . '" readonly required>
                                                    ' . $camposVariante . '
                                                </div>
                                            </div>

                                            <!-- Impuesto -->
                                            <div class="col-xs-2 ingresoImpuesto">
                                                <input type="text" class="form-control nuevoImpuestoProducto" name="nuevoImpuestoProducto" value="' . $impuestoNombreCorto . ' ' . $impuestoPorcentaje . '%" porcentaje="' . $impuestoPorcentaje . '" impuestoNombre="' . $impuestoNombre . '" readonly required>
                                            </div>

                                            <!-- Cantidad -->
                                            <div class="col-xs-2">
                                                <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="' . $val["cantidad"] . '" stock="' . $stockAntiguo . '" nuevoStock="' . $val["stock"] . '" required>
                                            </div>

                                            <!-- Precio -->
                                            <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">
                                                <div class="input-group">
                                                    <input type="text" class="form-control nuevoPrecioProducto" precioReal="' . $prod["precio_venta"] . '" name="nuevoPrecioProducto" value="' . $val["total"] . '" readonly required>
                                                </div>
                                            </div>
                                        </div>';
                                    }
                                    ?>
                                </div>

                                <input type="hidden" id="listaProductos" name="listaProductos">

                                <!--BTN SE MUESTRA EN CELULARES (xs) Y TABLETS (sm, md)-->
                                <button type="button" class="btn btn-warning btn-block btnAgregarProducto visible-xs visible-sm visible-md" style="margin-top: 10px; margin-bottom: 15px; font-weight: bold;">
                                  <i class="fa fa-plus"></i> Agregar producto
                                </button>


                                <hr>

                                <!--=====================================
                      TOTALES (mismo diseño que crear-venta)
                      ======================================-->

                                <div class="row">
                                    <div class="col-xs-12 col-md-6 pull-right">
                                        <table class="table table-condensed table-bordered" style="background:#f9f9f9;">
                                            <tbody>
                                                <tr>
                                                    <td style="vertical-align:middle; font-weight:bold; width:40%">
                                                        Subtotal</td>
                                                    <td style="width:60%">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="ion ion-social-usd"></i></span>
                                                            <input type="text" class="form-control" id="nuevoValorBruto"
                                                                name="nuevoValorBruto" placeholder="0"
                                                                value="<?php echo $totalProductos; ?>" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align:middle; font-weight:bold;">Valor Bruto
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="ion ion-social-usd"></i></span>
                                                            <input type="text" class="form-control"
                                                                id="nuevoSubtotalVenta" name="nuevoSubtotalVenta"
                                                                placeholder="0" value="<?php echo $totalProductos; ?>"
                                                                readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align:middle; font-weight:bold;">Impuestos IVA
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="ion ion-social-usd"></i></span>
                                                            <input type="text" class="form-control"
                                                                id="nuevoImpuestoVenta" name="nuevoImpuestoVenta"
                                                                placeholder="0"
                                                                value="<?php echo $venta['impuesto']; ?>" readonly>
                                                            <input type="hidden" name="nuevoPrecioImpuesto"
                                                                id="nuevoPrecioImpuesto"
                                                                value="<?php echo $venta['impuesto']; ?>" required>
                                                            <input type="hidden" name="nuevoPrecioNeto"
                                                                id="nuevoPrecioNeto"
                                                                value="<?php echo $venta['neto']; ?>" required>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="vertical-align:middle; font-weight:bold; font-size:1.2em;">
                                                        Total</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="ion ion-social-usd"></i></span>
                                                            <input type="text" class="form-control input-lg"
                                                                id="nuevoTotalVenta" name="nuevoTotalVenta" total=""
                                                                value="<?php echo $venta['total']; ?>" readonly required
                                                                style="font-weight:bold; font-size:1.2em;">
                                                            <input type="hidden" name="totalVenta" id="totalVenta"
                                                                value="<?php echo $venta['total']; ?>">
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
                                          style="margin-right: 5px; transform: scale(1.2);"
                                          <?php echo ($venta["tipo_descuento"] == "porcentaje") ? "checked" : ""; ?>>
                                        Agregar descuento por %
                                      </label>
                                      &nbsp;&nbsp;&nbsp;

                                      <label style="font-weight: normal; cursor: pointer;">
                                        <input type="checkbox" id="checkDescuentoFijo" name="checkDescuentoFijo"
                                          style="margin-right: 5px; transform: scale(1.2);"
                                          <?php echo ($venta["tipo_descuento"] == "fijo") ? "checked" : ""; ?>>
                                        Agregar descuento por valor fijo
                                      </label>
                                    </div>

                                    <!-- Campo de entrada para descuento -->
                                    <div class="form-group" id="campoDescuento" style="<?php echo empty($venta["tipo_descuento"]) ? "display: none;" : ""; ?>">
                                      <div class="input-group">
                                        <span class="input-group-addon" id="iconoDescuento">
                                            <?php if ($venta["tipo_descuento"] == "fijo"): ?>
                                                <i class="fa fa-money"></i>
                                            <?php else: ?>
                                                <i class="fa fa-percent"></i>
                                            <?php endif; ?>
                                        </span>
                                        <input type="number" class="form-control input-lg" min="0" id="valorDescuento"
                                          name="valorDescuento" placeholder="0" value="<?php echo empty($venta["valor_descuento"]) ? 0 : $venta["valor_descuento"]; ?>">
                                        <span class="input-group-addon" id="labelDescuento">
                                            <?php echo ($venta["tipo_descuento"] == "fijo") ? "Valor Descuento" : "Descuento"; ?>
                                        </span>
                                      </div>
                                      <small class="text-muted" id="textoAyudaDescuento">
                                          <?php echo ($venta["tipo_descuento"] == "fijo") ? "Ingrese el valor fijo del descuento" : "Ingrese el porcentaje de descuento"; ?>
                                      </small>
                                    </div>

                                    <!-- Campos ocultos para guardar información del descuento -->
                                    <input type="hidden" id="tipoDescuento" name="tipoDescuento" value="<?php echo $venta["tipo_descuento"]; ?>">
                                    <input type="hidden" id="montoDescuento" name="montoDescuento" value="<?php echo empty($venta["monto_descuento"]) ? 0 : $venta["monto_descuento"]; ?>">
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
                      MÉTODO DE PAGO
                      ======================================-->
                                 <?php
                                    $metodoPagoActual = $venta["metodo_pago"];
                                    $codigoTransaccionActual = "";
                                    
                                    if(strpos($metodoPagoActual, "-") !== false){
                                        $partesMetodo = explode("-", $metodoPagoActual);
                                        $metodoPagoActual = $partesMetodo[0];
                                        $codigoTransaccionActual = $partesMetodo[1];
                                    }
                                 ?>

                                <div class="form-group row">
                                    <div class="col-xs-6" style="padding-right:0px">
                                        <div class="input-group">
                                            <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago"
                                                required>
                                                <option value="">Seleccione método de pago</option>
                                                <?php
                                                foreach ($mediosPago as $medio) {
                                                    $medio = trim($medio);
                                                    $sel = ($medio == $metodoPagoActual) ? 'selected' : '';
                                                    echo '<option value="' . $medio . '" ' . $sel . '>' . $medio . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="cajasMetodoPago"></div>
                                    <input type="hidden" id="listaMetodoPago" name="listaMetodoPago">
                                </div>



                                <!-- Identificador para actualizar la orden original -->
                                <input type="hidden" name="editarVenta" value="<?php echo $venta["codigo"]; ?>">
                                <!-- Trigger para ctrCrearVentaFactus -->
                                <input type="hidden" name="guardarVentaFactus" value="1">
                                <!-- Estado: siempre venta (FE borrador) -->
                                <input type="hidden" name="estado" value="venta">
                                <!-- Descuento heredado de la orden -->
                                <input type="hidden" name="tipoDescuento"
                                    value="<?php echo $venta['tipo_descuento']; ?>">
                                <input type="hidden" name="valorDescuento"
                                    value="<?php echo $venta['valor_descuento']; ?>">
                                <input type="hidden" name="montoDescuento"
                                    value="<?php echo $venta['monto_descuento']; ?>">

                                <br>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right"
                            >
                                <i class="fa fa-file-text-o"></i> Crear Factura Electrónica
                            </button>
                        </div>
                    </form>

                    <?php
                    $guardarFE = new ControladorVentas();
                    $guardarFE->ctrCrearVentaFactus();
                    ?>

                    <button class="btn btn-danger pull-left" onclick="location.href='ordenes'">Cancelar</button>
                </div>
            </div>

            <!--=====================================
            LA TABLA DE PRODUCTOS (CATÁLOGO)
            ======================================-->

            <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
                <div class="box box-warning">
                    <div class="box-header with-border"></div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped dt-responsive tablaVentas" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px">#</th>
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

        <?php CSRF::insertToken(); ?>

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
            <input type="hidden" name="nuevoEstatus" value="nuevo">
            <input type="hidden" name="origen" value="orden-a-factura-electronica">
            <input type="hidden" name="vistaOrigen" value="orden-a-factura-electronica">

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

  </div>
</div>

<!--=====================================
MODAL AGREGAR RETENCION
======================================-->
<div id="modalAgregarRetencionNuevo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formularioRetencionNuevo">

        <?php CSRF::insertToken(); ?>

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

<!-- Script para inicializar el descuento al cargar la página -->
<script>
  $(document).ready(function () {
    // Si hay un descuento aplicado, recalcular el total al cargar
    if ($("#tipoDescuento").val() !== "") {
      // Asegurarse de que el atributo 'total' esté configurado
      var subtotal = $("#nuevoSubtotalVenta").val();
      $("#nuevoTotalVenta").attr("total", subtotal);

      // Aplicar el descuento
      aplicarDescuento();
    }
  });

  $(document).ready(function () {
    // Función centralizada para manejar la selección de porcentaje
    function activarPorcentaje() {
        // Desmarcar el otro checkbox
        $('#checkDescuentoFijo').prop('checked', false);
        try { $('#checkDescuentoFijo').iCheck('uncheck'); } catch(e){}

        // Mostrar campo de descuento
        $('#campoDescuento').slideDown();

        // Cambiar icono y texto a porcentaje
        $('#iconoDescuento').html('<i class="fa fa-percent"></i>');
        $('#labelDescuento').text('% Descuento');
        $('#textoAyudaDescuento').text('Ingrese el porcentaje de descuento (0-100)');
        $('#valorDescuento').attr('max', '100');
        $('#valorDescuento').attr('placeholder', '0');
        // Si no había valor previo, setear a 0
        if($('#tipoDescuento').val() !== 'porcentaje') $('#valorDescuento').val('0');

        // Guardar tipo de descuento
        $('#tipoDescuento').val('porcentaje');
        
        aplicarDescuento();
    }

    function desactivarDescuento() {
        $('#campoDescuento').slideUp();
        $('#valorDescuento').val('0');
        $('#tipoDescuento').val('');
        $('#montoDescuento').val('0');

        // Recalcular total sin descuento
        sumarTotalPrecios();
        agregarImpuesto();
    }

    // Función centralizada para manejar la selección de fijo
    function activarFijo() {
        // Desmarcar el otro checkbox
        $('#checkDescuentoPorcentaje').prop('checked', false);
        try { $('#checkDescuentoPorcentaje').iCheck('uncheck'); } catch(e){}

        // Mostrar campo de descuento
        $('#campoDescuento').slideDown();

        // Cambiar icono y texto a valor fijo
        $('#iconoDescuento').html('<i class="fa fa-money"></i>');
        $('#labelDescuento').text('Valor Descuento');
        $('#textoAyudaDescuento').text('Ingrese el valor fijo del descuento');
        $('#valorDescuento').removeAttr('max');
        $('#valorDescuento').attr('placeholder', '0');
        if($('#tipoDescuento').val() !== 'fijo') $('#valorDescuento').val('0');

        // Guardar tipo de descuento
        $('#tipoDescuento').val('fijo');
        
        aplicarDescuento();
    }

    // Eventos normales y eventos iCheck
    $('#checkDescuentoPorcentaje').on('change', function () {
      if ($(this).is(':checked')) activarPorcentaje(); else desactivarDescuento();
    });
    $('#checkDescuentoPorcentaje').on('ifChecked', activarPorcentaje);
    $('#checkDescuentoPorcentaje').on('ifUnchecked', desactivarDescuento);

    $('#checkDescuentoFijo').on('change', function () {
      if ($(this).is(':checked')) activarFijo(); else desactivarDescuento();
    });
    $('#checkDescuentoFijo').on('ifChecked', activarFijo);
    $('#checkDescuentoFijo').on('ifUnchecked', desactivarDescuento);

    // Cuando cambia el valor del descuento, recalcular
    $('#valorDescuento').on('change keyup', function () {
      aplicarDescuento();
    });

  });
</script>

<!--=====================================
    SCRIPT: Forma de Pago toggle + validación
    ======================================-->
<script>
    $(document).ready(function () {

        // Toggle campo fecha de vencimiento
        $('#forma_pago_dian').on('change', function () {
            if ($(this).val() === '2') {
                $('#campo_fecha_vencimiento').slideDown(200);
                $('#fecha_vencimiento_fe').attr('required', true);
            } else {
                $('#campo_fecha_vencimiento').slideUp(200);
                $('#fecha_vencimiento_fe').removeAttr('required').val('');
            }
        });

        // Validar al enviar
        $(document).on('submit', '.formularioVenta', function (e) {
            var listaProductos = $('#listaProductos').val();
            if (!listaProductos || listaProductos === '[]') {
                e.preventDefault();
                swal({ type: 'error', title: 'Sin productos', text: 'Debe agregar al menos un producto.', showConfirmButton: true, confirmButtonText: 'Cerrar' });
                return false;
            }
            if ($('#forma_pago_dian').val() === '2' && !$('#fecha_vencimiento_fe').val()) {
                e.preventDefault();
                swal({ type: 'warning', title: 'Fecha de Vencimiento requerida', text: 'Para facturas a crédito debe ingresar la fecha de vencimiento.', showConfirmButton: true, confirmButtonText: 'Aceptar' });
                return false;
            }
        });

        // Disparar el cambio de método de pago para que se muestren los campos adicionales si ya hay uno seleccionado
        if ($("#nuevoMetodoPago").val() != "") {
            $("#nuevoMetodoPago").trigger("change");

            // Si hay un código de transacción previo, rellenarlo
            var codigoPrevio = "<?php echo $codigoTransaccionActual; ?>";
            if(codigoPrevio != ""){
                $("#nuevoCodigoTransaccion").val(codigoPrevio);
            }
        }

    });
</script>

<script>
/* =============================================
   VALIDAR DOCUMENTO DUPLICADO - MODAL AGREGAR CLIENTE
   (orden-a-factura-electronica)
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