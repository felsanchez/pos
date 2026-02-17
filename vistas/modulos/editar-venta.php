<style>
  /* Mejoras Visuales Factura */
  .invoice {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 30px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    background: white;
  }

  .invoice-info {
    background-color: #f8f9fa;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 4px;
    border-left: 4px solid #3c8dbc;
  }

  .invoice-col address {
    margin-bottom: 0;
    color: #555;
  }

  .invoice-col strong {
    color: #333;
    font-size: 1.1em;
  }

  .page-header {
    border-bottom: 2px solid #3c8dbc;
    color: #444;
    padding-bottom: 15px;
  }

  .table thead th {
    background-color: #3c8dbc;
    color: white;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    border: none;
  }

  .table-striped>tbody>tr:nth-of-type(odd) {
    background-color: #f4f6f9;
  }

  .lead {
    font-size: 16px;
    font-weight: bold;
    color: #444;
    background: #e9ecef;
    padding: 8px 15px;
    border-radius: 3px;
    margin-bottom: 15px;
    border-left: 4px solid #d2d6de;
  }

  /* Responsive para móvil */
  @media (max-width: 767px) {

    /* Campos de ancho completo */
    .formularioVenta .form-group .input-group.col-xs-12,
    .formularioVenta .form-group .col-xs-6 {
      width: 100% !important;
      max-width: 100%;
      padding: 0 !important;
    }

    /* Productos - diseño vertical en móvil */
    .nuevoProducto .row.col-xs-10 {
      width: 100% !important;
      margin: 0;
      padding: 5px 15px;
    }

    .nuevoProducto .row .col-xs-7,
    .nuevoProducto .row .col-xs-2,
    .nuevoProducto .row .col-xs-3 {
      width: 100% !important;
      padding: 0;
      margin-bottom: 10px;
    }

    .nuevoProducto .row .col-xs-7 {
      margin-bottom: 5px;
    }

    /* Tabla de impuesto y total - diseño vertical */
    .table-responsive {
      overflow-x: auto;
    }

    table thead th,
    table tbody td {
      font-size: 12px;
      padding: 5px !important;
    }

    table tbody td .input-group {
      min-width: 120px;
    }

    /* Descuento - ancho completo */
    .col-xs-10 {
      width: 100% !important;
    }

    /* Input-group en móvil */
    .input-group-addon {
      padding: 6px 8px;
      font-size: 12px;
    }
  }
</style>

<?php
$item = "id";
$valor = $_GET["idVenta"];
$venta = ControladorVentas::ctrMostrarVentas($item, $valor);
$itemUsuario = "id";
$valorUsuario = $venta["id_vendedor"];
$vendedor = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
$itemCliente = "id";
$valorCliente = $venta["id_cliente"];
$cliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

// Obtener datos de la empresa
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
?>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Detalle de Venta
      <small>#<?php echo $venta["codigo"] ?? ''; ?></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Ventas</li>
    </ol>
  </section>

  <div class="pad margin no-print">
    <div class="callout callout-info" style="margin-bottom: 0!important;">
      <h4><i class="fa fa-info"></i> Nota:</h4>
      Esta página es solo para ver detalles. No se pueden realizar cambios.
    </div>
  </div>

  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header">
          <i class="fa fa-globe"></i> <?php echo $configuracion["nombre_empresa"] ?? 'Empresa'; ?>
          <small class="pull-right">Fecha: <?php echo $venta["fecha"] ?? ''; ?></small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <div class="row invoice-info">
      <div class="col-sm-4 invoice-col">
        <span
          style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Empresa</span>
        <address>
          <strong><?php echo $configuracion["nombre_empresa"] ?? 'Nombre Empresa'; ?></strong><br>
          NIT: <?php echo $configuracion["nit"] ?? ''; ?><br>
          <?php echo $configuracion["direccion"] ?? ''; ?><br>
          Teléfono: <?php echo $configuracion["telefono"] ?? ''; ?><br>
          Email: <?php echo $configuracion["correo"] ?? ''; ?>
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <span
          style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Cliente</span>
        <address>
          <strong><?php echo $cliente["nombre"] ?? ''; ?></strong><br>
          <?php echo $cliente["documento"] ?? ''; ?><br>
          <?php echo $cliente["direccion"] ?? ''; ?><br>
          Tel: <?php echo $cliente["telefono"] ?? ''; ?><br>
          Email: <?php echo $cliente["email"] ?? ''; ?>
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <span
          style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Detalles</span>
        <b>Factura #<?php echo $venta["codigo"] ?? ''; ?></b><br>
        <br>
        <b>Vendedor:</b> <?php echo $vendedor["nombre"] ?? ''; ?><br>
        <b>Método de Pago:</b> <?php echo $venta["metodo_pago"] ?? ''; ?><br>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- Table row -->
    <div class="row">
      <div class="col-xs-12 table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cant</th>
              <th>Precio Unit.</th>
              <th>Impuesto</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $listaProducto = json_decode($venta["productos"], true);
            foreach ($listaProducto as $key => $value) {

              $item = "id";
              $valor = $value["id"];
              $orden = "id";
              $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

              $nombreCorto = "Exento";
              $impuestoPorcentaje = 0;

              if (isset($respuesta["tributo_id"]) && $respuesta["tributo_id"] != 0) {
                require_once "modelos/factus.modelo.php";
                $tributo = ModeloFactus::mdlMostrarTributo($respuesta["tributo_id"]);
                if ($tributo) {
                  $impuestoPorcentaje = $tributo["porcentaje_defecto"];
                  $impuestoNombre = $tributo["nombre"];
                  $nombreCorto = trim(preg_split('/[0-9]/', $impuestoNombre)[0]);
                }
              }

              echo '<tr>
                        <td>' . $value["descripcion"] . '</td>
                        <td>' . $value["cantidad"] . '</td>
                        <td>$' . number_format($respuesta["precio_venta"], 2) . '</td>
                        <td>' . $nombreCorto . ' ' . $impuestoPorcentaje . '%</td>
                        <td>$' . number_format($value["total"], 2) . '</td>
                      </tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <div class="row">
      <!-- accepted payments column -->
      <div class="col-xs-6">

        <!-- Retenciones (Primero) -->
        <?php
        $retenciones = [];
        $totalRetenciones = 0;
        if (!empty($venta["retenciones"])) {
          $retenciones = json_decode($venta["retenciones"], true);
        }
        if (!empty($retenciones) && is_array($retenciones)):
          ?>
          <p class="lead">Retenciones:</p>
          <div class="table-responsive">
            <table class="table">
              <?php
              $totalRetenciones = 0;
              foreach ($retenciones as $retencion):
                $totalRetenciones += $retencion['monto'];
                ?>
                <tr>
                  <th style="width:50%"><?php echo $retencion['tipo']; ?> (<?php echo $retencion['porcentaje']; ?>%):</th>
                  <td>$<?php echo number_format($retencion['monto'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
              <tr>
                <th>Total Retenido:</th>
                <td>$<?php echo number_format($totalRetenciones, 2); ?></td>
              </tr>
            </table>
          </div>
          <br>
        <?php endif; ?>

        <!-- Notas (Despues) -->
        <p class="lead">Notas:</p>
        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
          <?php echo $venta["notas"]; ?>
        </p>

        <!-- Observaciones (Factura) -->
        <?php if (!empty($venta["observacion"])): ?>
          <p class="lead" style="margin-top: 20px;">Observaciones:</p>
          <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
            <?php echo $venta["observacion"]; ?>
          </p>
        <?php endif; ?>

        <!-- CUFE (Facturación Electrónica signature) -->
        <?php
        $cufeDisplay = $venta["cufe"];

        // Fallback: Si no hay CUFE guardado pero hay QR, intentar extraerlo del link
        if (empty($cufeDisplay) && !empty($venta["qr_data"])) {
          // El formato es ...?documentkey=CUFE...
          if (preg_match('/documentkey=([a-zA-Z0-9]+)/', $venta["qr_data"], $matches)) {
            $cufeDisplay = $matches[1];
          }
        }

        if (!empty($cufeDisplay)): ?>
          <p class="lead" style="margin-top: 20px;">CUFE:</p>
          <div class="well well-sm no-shadow"
            style="background: #f0f0f0; word-break: break-all; font-family: monospace; font-size: 11px; color: #555;">
            <?php echo $cufeDisplay; ?>
          </div>
        <?php endif; ?>

      </div>
      <!-- /.col -->
      <div class="col-xs-6">
        <p class="lead">Totales</p>

        <div class="table-responsive">
          <table class="table">
            <?php
            // RECALCULO EXACTO DE TOTALES (Igual que en JS de crear venta)
            
            $listaProducto = json_decode($venta["productos"], true);
            $valorBrutoRecalculado = 0; // Base Imponible Bruta (Antes de descuentos)
            $subtotalRecalculado = 0;   // Base Imponible Neta (Despues de descuentos)
            $impuestoGeneral = 0;
            $impuestoINC = 0;

            // Datos de descuentos
            $tipoDescuento = $venta["tipo_descuento"] ?? ""; // porcentaje o fijo
            $valorDescuentoGlobal = $venta["valor_descuento"] ?? 0;
            $montoDescuentoTotal = $venta["monto_descuento"] ?? 0;
            $totalVentaOriginal = $venta["total"];

            // Si hay descuento fijo, necesitamos el total original para prorratear
            // Afortunadamente tenemos $venta["total"] que es el FINAL con impuestos y descuentos.
            // Pero para prorrateo necesitamos el total ANTES de descuento... 
            // ESTIMACION: TotalOrig = TotalFinal + MontoDescuento
            $totalOriginalEstimado = $totalVentaOriginal + $montoDescuentoTotal;

            foreach ($listaProducto as $prod) {
              // Obtener datos del producto (especialmente impuestos)
              // Nota: $prod contiene precio y total que YA INCLUYEN IMPUESTO
              $totalProductoConImpuesto = floatval($prod["total"]);

              // Buscar porcentaje de impuesto
              $impuestoPorcentaje = 0;
              $impuestoNombre = "";

              // Intentamos sacar el impuesto del propio JSON si se guardó (idealmente)
              if (isset($prod["impuesto"])) {
                $impuestoPorcentaje = floatval($prod["impuesto"]);
              } else {
                // Fallback: buscar en BD (menos preciso si cambiaron impuestos)
                $infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
                $impuestoPorcentaje = isset($infoP["impuesto_porcentaje"]) ? floatval($infoP["impuesto_porcentaje"]) : 19; // Default 19 si falla
              }

              // Buscar nombre impuesto en BD para saber si es INC
              // (Esto es una limitante si no se guardó en el JSON de venta, asumimos IVA general si falla)
              // Intentamos obtener el nombre del impuesto del propio JSON
              if (isset($prod["impuestoNombre"])) {
                $impuestoNombre = $prod["impuestoNombre"];
              } else {
                // Fallback DB
                //$infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
                //$impuestoNombre = $infoP["impuesto_nombre"] ?? ""; 
                // Simplificación: Si no tenemos el nombre, asumimos IVA para no romper, 
                // pero idealmente deberíamos haber guardado esto.
              }

              // 1. CALCULAR VALOR BRUTO (BASE BRUTA) DEL ITEM
              // Base = TotalConImpuesto / (1 + %/100)
              // PERO: Si hubo descuento, el TotalConImpuesto ya lo tiene restado?
              // NO. En el JSON de productos, "total" suele ser Cantidad * PrecioUnitario.
              // En `ventas.js`: "total": $(precio[i]).val() -> Precio con impuesto unitario * cantidad.
              // El descuento se aplica GLOBALMENTE al final en la logica JS, 
              // AUNQUE internamente calcula prorrateos.
              // VERIFICACION CRITICA: ¿El "total" del JSON productos tiene el descuento restado?
              // En `listarProductos()` de ventas.js: "total": $(precio[i]).val().
              // $(precio[i]) es el input. Ese input NO cambia con el descuento global visualmente 
              // en la tabla (el descuento se muestra abajo).
              // POR LO TANTO: $prod["total"] es PRECIO DE LISTA CON IMPUESTO (SIN DESCUENTO APLICADO).
            
              $baseItemBruta = $totalProductoConImpuesto / (1 + ($impuestoPorcentaje / 100));
              $valorBrutoRecalculado += $baseItemBruta;

              // 2. CALCULAR DESCUENTO PRORRATEADO PARA ESTE ITEM
              $descuentoItem = 0;

              if ($tipoDescuento == "porcentaje") {
                $descuentoItem = $totalProductoConImpuesto * ($valorDescuentoGlobal / 100);
              } else if ($tipoDescuento == "fijo" && $totalOriginalEstimado > 0) {
                // Prorrateo: Descuento * (Peso del Item en el Total)
                $descuentoItem = $valorDescuentoGlobal * ($totalProductoConImpuesto / $totalOriginalEstimado);
              }

              $precioConDescuento = $totalProductoConImpuesto - $descuentoItem;

              // 3. CALCULAR BASE NETA (SUBTOTAL)
              $baseItemNeta = $precioConDescuento / (1 + ($impuestoPorcentaje / 100));
              $impuestoItem = $precioConDescuento - $baseItemNeta;

              $subtotalRecalculado += $baseItemNeta;

              // Clasificar impuesto
              // Si detectamos INC en alguna parte (difícil sin el nombre exacto guardado)
              // Por ahora sumamos todo a General user request.
              $impuestoGeneral += $impuestoItem;
            }

            // Ajuste final por redondeos: Usar los totales guardados si la diferencia es mínima
            // pero mostrar los desglosados calculados.
            
            // Calculo Final del Valor Neto (Total a Pagar)
            // Total Venta = Subtotal + Impuestos
            $totalVentaCalculado = $subtotalRecalculado + $impuestoGeneral;
            // Valor Neto = Total Venta - Retenciones
            $valorNetoPagar = $totalVentaCalculado - $totalRetenciones;

            // 4. CALCULAR DESCUENTO SOBRE LA BASE (Para que cuadre visualmente: Subtotal - Descuento = Valor Bruto)
            // El $montoDescuentoTotal es sobre el total con IVA, pero aquí mostramos bases.
            $descuentoBase = $valorBrutoRecalculado - $subtotalRecalculado;

            ?>
            <tr>
              <th style="width:50%">Subtotal:</th>
              <td>$
                <?php echo number_format($valorBrutoRecalculado, 2); ?>
              </td>
            </tr>
            <!-- Descuento logic -->
            <?php if ($montoDescuentoTotal > 0): ?>
              <tr>
                <th>Descuento:</th>
                <td>$<?php echo number_format((float) $descuentoBase, 2); ?></td>
              </tr>
            <?php endif; ?>
            <tr>
              <th>Valor Bruto:</th>
              <td>$<?php echo number_format((float) $subtotalRecalculado, 2); ?></td>
            </tr>
            <tr>
              <th>Impuesto:</th>
              <td>$<?php echo number_format((float) $impuestoGeneral, 2); ?></td>
            </tr>
            <tr>
              <th>Total:</th>
              <td>$<?php echo number_format((float) $totalVentaCalculado, 2); ?></td>
            </tr>
            <tr>
              <th style="font-size: 1.1em; color: #333;">Valor Neto:</th>
              <td style="font-size: 1.1em; font-weight: bold;">$<?php echo number_format((float) $valorNetoPagar, 2); ?>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- this row will not appear when printing -->
    <div class="row no-print">
      <div class="col-xs-12">
        <button type="button" class="btn btn-primary pull-right" onclick="history.back()">
          <i class="fa fa-arrow-left"></i> Atras
        </button>

        <!-- Boton XML -->
        <?php if (!empty($venta["numero_factura"])): ?>
          <a class="btn btn-success pull-right" style="margin-right: 5px;"
            href="descargar-xml.php?xml=<?php echo $venta["numero_factura"]; ?>" target="_blank">
            <i class="fa fa-file-code-o"></i> Descargar XML
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <!-- /.content -->
  <div class="clearfix"></div>
</div>