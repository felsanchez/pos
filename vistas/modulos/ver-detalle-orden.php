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
// Obtener configuración de Factus para datos del emisor
$configFactus = ControladorFactus::ctrObtenerConfiguracion();

// Identificar tipo de documento
$tipoDocumento = "Orden de Venta";
$etiquetaDocumento = "Orden";

if (!empty($venta["numero_factura"])) {
  $tipoDocumento = "Factura Electrónica";
  $etiquetaDocumento = "Factura";
} else if ($venta["estado"] == "venta") {
  $tipoDocumento = "Detalle de Venta";
  $etiquetaDocumento = "Venta";
}
?>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <?php echo $tipoDocumento; ?>
      <small>#<?php echo (!empty($venta["numero_factura"]) ? $venta["numero_factura"] : ($venta["codigo"] ?? '')); ?></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active"><?php echo $etiquetaDocumento; ?></li>
    </ol>
  </section>

  <div class="pad margin no-print">
    <div class="callout callout-info" style="margin-bottom: 0!important;">
      <h4><i class="fa fa-info"></i> Nota:</h4>
      Esta página es solo para ver detalles de la <?php echo strtolower($etiquetaDocumento); ?>. No se pueden realizar
      cambios.
    </div>
  </div>

  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12">
        <h2 class="page-header">
          <?php if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa']) && file_exists($configFactus['logo_empresa'])): ?>
            <img src="<?php echo $configFactus['logo_empresa']; ?>"
              style="max-height: 60px; margin-right: 15px; vertical-align: middle; margin-top: -10px;">
          <?php else: ?>
            <i class="fa fa-globe"></i>
          <?php endif; ?>
          <?php
          echo isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
          ?>
          <small class="pull-right" style="margin-top: 20px;">Fecha: <?php echo $venta["fecha"] ?? ''; ?></small>
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
          <?php
          // Lógica para etiqueta de Nombre/Razón Social
          $labelNombre = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';
          $nombreEmisor = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Nombre Empresa');
          $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
          $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
          $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
          $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
          ?>
          <strong><?php echo $labelNombre; ?>:</strong><br>
          <?php echo $nombreEmisor; ?><br>
          <strong>NIT:</strong> <?php echo $nitEmisor; ?><br>
          <strong>Dirección:</strong> <?php echo $direccionEmisor; ?><br>
          <strong>Teléfono:</strong> <?php echo $telefonoEmisor; ?><br>
          <strong>Email:</strong> <?php echo $emailEmisor; ?>
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <span
          style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Cliente</span>
        <address>
          <strong>Cliente:</strong> <?php echo $cliente["nombre"] ?? ''; ?><br>
          <strong>Documento:</strong> <?php echo $cliente["documento"] ?? ''; ?><br>
          <strong>Dirección:</strong> <?php echo $cliente["direccion"] ?? ''; ?><br>
          <strong>Ciudad:</strong> <?php echo $cliente["ciudad"] ?? ''; ?><br>
          <strong>Teléfono:</strong> <?php echo $cliente["telefono"] ?? ''; ?><br>
          <strong>Email:</strong> <?php echo $cliente["email"] ?? ''; ?>
        </address>
      </div>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <span
          style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Detalles</span>
        <b><?php echo $etiquetaDocumento; ?>
          #<?php echo (!empty($venta["numero_factura"]) ? $venta["numero_factura"] : ($venta["codigo"] ?? '')); ?></b><br>
        <br>
        <b>Vendedor:</b> <?php echo $vendedor["nombre"] ?? ''; ?><br>
        <b>Método de Pago:</b> <?php echo $venta["metodo_pago"] ?? ''; ?><br>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->

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
            // ESTIMACION: TotalOrig = TotalFinal + MontoDescuento
            $totalOriginalEstimado = $totalVentaOriginal + $montoDescuentoTotal;

            foreach ($listaProducto as $prod) {
              $totalProductoConImpuesto = floatval($prod["total"]);
              $impuestoPorcentaje = 0;

              if (isset($prod["impuesto"])) {
                $impuestoPorcentaje = floatval($prod["impuesto"]);
              } else {
                $infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
                $impuestoPorcentaje = isset($infoP["impuesto_porcentaje"]) ? floatval($infoP["impuesto_porcentaje"]) : 19;
              }

              $baseItemBruta = $totalProductoConImpuesto / (1 + ($impuestoPorcentaje / 100));
              $valorBrutoRecalculado += $baseItemBruta;

              $descuentoItem = 0;
              if ($tipoDescuento == "porcentaje") {
                $descuentoItem = $totalProductoConImpuesto * ($valorDescuentoGlobal / 100);
              } else if ($tipoDescuento == "fijo" && $totalOriginalEstimado > 0) {
                $descuentoItem = $valorDescuentoGlobal * ($totalProductoConImpuesto / $totalOriginalEstimado);
              }

              $precioConDescuento = $totalProductoConImpuesto - $descuentoItem;
              $baseItemNeta = $precioConDescuento / (1 + ($impuestoPorcentaje / 100));
              $impuestoItem = $precioConDescuento - $baseItemNeta;

              $subtotalRecalculado += $baseItemNeta;
              $impuestoGeneral += $impuestoItem;
            }

            $totalVentaCalculado = $subtotalRecalculado + $impuestoGeneral;
            $valorNetoPagar = $totalVentaCalculado - $totalRetenciones;
            $descuentoBase = $valorBrutoRecalculado - $subtotalRecalculado;
            ?>
            <tr>
              <th style="width:50%">Subtotal:</th>
              <td>$<?php echo number_format($valorBrutoRecalculado, 2); ?></td>
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
        <!-- Boton PDF -->
        <a class="btn btn-danger pull-right" style="margin-right: 5px;"
          href="extensiones/tcpdf/pdf/descargar-pdf-orden.php?idVenta=<?php echo $venta["id"]; ?>" target="_blank">
          <i class="fa fa-file-pdf-o"></i> Descargar PDF de <?php echo $etiquetaDocumento; ?>
        </a>

        <button type="button" class="btn btn-default pull-right" onclick="history.back()" style="margin-right: 5px;">
          <i class="fa fa-arrow-left"></i> Volver
        </button>
      </div>
    </div>
  </section>
  <!-- /.content -->
  <div class="clearfix"></div>
</div>