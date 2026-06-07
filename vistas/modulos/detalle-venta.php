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
?>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Detalle de Venta
      <small>#<?php echo $venta["codigo"] ?? ''; ?></small>
      <?php if(isset($venta["estado"]) && $venta["estado"] == "anulada"): ?>
        <span class="label label-danger" style="margin-left: 10px; font-size: 14px; vertical-align: middle;">ANULADA</span>
      <?php endif; ?>
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
          
          $municipioEmisor = '';
          if (isset($configFactus['municipio_id']) && !empty($configFactus['municipio_id'])) {
              require_once "modelos/factus.modelo.php";
              $muns = ModeloFactus::mdlObtenerMunicipios();
              foreach ($muns as $mun) {
                  if ($mun['id_factus'] == $configFactus['municipio_id']) {
                      $municipioEmisor = $mun['nombre'] . ' - ' . $mun['departamento'];
                      break;
                  }
              }
          }
          ?>
          <strong><?php echo $labelNombre; ?>:</strong><br>
          <?php echo $nombreEmisor; ?><br>
          <strong>NIT:</strong> <?php echo $nitEmisor; ?><br>
          <strong>Dirección:</strong> <?php echo $direccionEmisor; ?><br>
          <?php if(!empty($municipioEmisor)): ?>
          <strong>Municipio:</strong> <?php echo $municipioEmisor; ?><br>
          <?php endif; ?>
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
        <b>Venta #<?php echo $venta["codigo"] ?? ''; ?></b><br>
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
        <?php
        // PRE-CALCULO DE TOTALES PARA EVITAR ERRORES DE RENDERIZADO
        $listaProducto = json_decode($venta["productos"], true);
        $valorBrutoRecalculado = 0;
        $subtotalRecalculado = 0;
        $impuestoGeneral = 0;
        $totalRetenciones = 0;

        // Calcular retenciones primero para tener el valor disponible
        $retenciones = !empty($venta["retenciones"]) ? json_decode($venta["retenciones"], true) : [];
        if (is_array($retenciones)) {
          foreach ($retenciones as $ret) {
            $totalRetenciones += floatval($ret['monto']);
          }
        }

        $tipoDescuento = $venta["tipo_descuento"] ?? "";
        $valorDescuentoGlobal = $venta["valor_descuento"] ?? 0;
        $montoDescuentoTotal = $venta["monto_descuento"] ?? 0;
        $totalVentaOriginal = floatval($venta["total"]);
        $totalOriginalEstimado = $totalVentaOriginal + $montoDescuentoTotal;

        $itemsTabla = [];

        $productosProcesados = [];
        $totalConocido = 0;
        $itemsDesconocidos = [];
        $cantidadDesconocida = 0;

        if (is_array($listaProducto)) {
          foreach ($listaProducto as $key => $prod) {
            $infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
            
            $precio = null;
            if (isset($prod["precio"]) && floatval($prod["precio"]) > 0) {
              $precio = floatval($prod["precio"]);
            } else if ($infoP && isset($infoP["precio_venta"]) && floatval($infoP["precio_venta"]) > 0) {
              $precio = floatval($infoP["precio_venta"]);
            }

            $cantidad = isset($prod["cantidad"]) ? floatval($prod["cantidad"]) : 1;
            
            $total = null;
            if (isset($prod["total"]) && floatval($prod["total"]) > 0) {
              $total = floatval($prod["total"]);
            }

            if ($precio !== null && $total === null) {
              $total = $precio * $cantidad;
            }

            if ($total !== null && $precio === null) {
              $precio = $total / $cantidad;
            }

            $impuestoPorcentaje = 19; // Default
            if (isset($prod["impuesto"]) && $prod["impuesto"] !== "") {
              $impuestoPorcentaje = floatval($prod["impuesto"]);
            } else if ($infoP && isset($infoP["tasa_impuesto"])) {
              $impuestoPorcentaje = floatval($infoP["tasa_impuesto"]);
            }

            $impuestoNombre = "";
            if ($infoP && !empty($infoP["tributo_id"])) {
              if (!class_exists("ModeloFactus")) {
                require_once "modelos/factus.modelo.php";
              }
              $tributo = ModeloFactus::mdlMostrarTributo($infoP["tributo_id"]);
              if ($tributo) {
                $impuestoNombre = $tributo["nombre"];
              }
            }
            if (empty($impuestoNombre)) {
              $impuestoNombre = ($impuestoPorcentaje == 8) ? "INC" : "IVA";
            }

            $productosProcesados[$key] = [
              "id" => $prod["id"],
              "descripcion" => $prod["descripcion"] ?? ($infoP["descripcion"] ?? "Producto"),
              "cantidad" => $cantidad,
              "precio" => $precio,
              "total" => $total,
              "impuesto" => $impuestoPorcentaje,
              "impuesto_nombre" => $impuestoNombre
            ];

            if ($total !== null) {
              $totalConocido += $total;
            } else {
              $itemsDesconocidos[] = $key;
              $cantidadDesconocida += $cantidad;
            }
          }

          // Distribuir el total estimado de la venta original
          $totalVenta = isset($venta["total"]) ? floatval($venta["total"]) : 0;
          $totalRestante = max(0, $totalVenta - $totalConocido);

          if (count($itemsDesconocidos) > 0 && $totalRestante > 0) {
            if ($cantidadDesconocida > 0) {
              $precioPorUnidad = $totalRestante / $cantidadDesconocida;
              foreach ($itemsDesconocidos as $key) {
                $cantidad = $productosProcesados[$key]["cantidad"];
                $productosProcesados[$key]["precio"] = $precioPorUnidad;
                $productosProcesados[$key]["total"] = $precioPorUnidad * $cantidad;
              }
            } else {
              $montoPorItem = $totalRestante / count($itemsDesconocidos);
              foreach ($itemsDesconocidos as $key) {
                $productosProcesados[$key]["precio"] = $montoPorItem;
                $productosProcesados[$key]["total"] = $montoPorItem;
              }
            }
          } else if (count($itemsDesconocidos) > 0) {
            foreach ($itemsDesconocidos as $key) {
              $productosProcesados[$key]["precio"] = 0;
              $productosProcesados[$key]["total"] = 0;
            }
          }

          foreach ($productosProcesados as $prod) {
            $precioUnitario = $prod["precio"];
            $cantidad = $prod["cantidad"];
            $totalProductoConImpuesto = $prod["total"];
            $impuestoPorcentaje = $prod["impuesto"];
            $impuestoNombre = $prod["impuesto_nombre"] ?? "";

            // Cálculos
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

            // Guardar para la tabla
            $itemsTabla[] = [
              "descripcion" => $prod["descripcion"],
              "cantidad" => $cantidad,
              "precio" => $precioUnitario,
              "total" => $totalProductoConImpuesto,
              "impuesto_porc" => $impuestoPorcentaje,
              "impuesto_nombre" => $impuestoNombre
            ];
          }
        }

        $totalVentaCalculado = $subtotalRecalculado + $impuestoGeneral;
        $valorNetoPagar = $totalVentaCalculado - $totalRetenciones;
        $descuentoBase = $valorBrutoRecalculado - $subtotalRecalculado;
        ?>

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
            <?php foreach ($itemsTabla as $item): ?>
              <tr>
                <td><?php echo $item["descripcion"]; ?></td>
                <td><?php echo $item["cantidad"]; ?></td>
                <td>$<?php echo number_format($item["precio"], 2); ?></td>
                <td><?php echo (!empty($item["impuesto_nombre"]) ? $item["impuesto_nombre"] . " " : "") . $item["impuesto_porc"]; ?>%</td>
                <td>$<?php echo number_format($item["total"], 2); ?></td>
              </tr>
            <?php endforeach; ?>
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
                  <td>$<?php echo number_format((float)$retencion['monto'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
              <tr>
                <th>Total Retenido:</th>
                <td>$<?php echo number_format((float)$totalRetenciones, 2); ?></td>
              </tr>
            </table>
          </div>
          <br>
        <?php endif; ?>

        <!-- Notas del Cliente (solo si tiene contenido) -->
        <?php
        $notasCliente = $venta["notas"] ?? "";
        $notasCliente = trim(str_replace(array("[Notificado_n8n]", "[Notificado]"), "", $notasCliente));

        if (!empty($venta["extra"]) && empty($notasCliente)) {
            $notasCliente = "";
        }

        if (!empty($notasCliente)): 
        ?>
          <p class="lead">Notas del Cliente:</p>
          <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
            <?php echo $notasCliente; ?>
          </p>
        <?php endif; ?>

        <!-- Observaciones (Factura) -->
        <?php if (!empty($venta["observacion"])): ?>
          <p class="lead" style="margin-top: 20px;">Observaciones:</p>
          <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
            <?php echo $venta["observacion"]; ?>
          </p>
        <?php endif; ?>

        <!-- QR Code -->
        <?php if (!empty($venta["qr_data"])): ?>
          <p class="lead" style="margin-top: 20px;">Código QR DIAN:</p>
          <?php
          $qrData = trim($venta["qr_data"]);
          $qrBase64 = "";

          // Attempt to generate QR locally
          // Path relative to vistas/modulos/detalle-venta.php -> pos root
          $tcpdfPath = __DIR__ . "/../../extensiones/tcpdf/tcpdf_barcodes_2d.php";

          if (file_exists($tcpdfPath)) {
            require_once($tcpdfPath);
            if (class_exists('TCPDF2DBarcode')) {
              try {
                $barcodeobj = new TCPDF2DBarcode($qrData, 'QRCODE,H');
                $svgCode = $barcodeobj->getBarcodeSVGcode(5, 5, 'black');
                if (!empty($svgCode)) {
                  $qrBase64 = base64_encode($svgCode);
                }
              } catch (Exception $e) {
                // Silent fail
              }
            }
          }
          ?>

          <?php if (!empty($qrBase64)): ?>
            <img src="data:image/svg+xml;base64,<?php echo $qrBase64; ?>" width="150" height="150" title="QR Factura"
              alt="QR Factura" style="display:block; margin-bottom:10px; border:1px solid #ddd;" />
          <?php else: ?>
            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo rawurlencode($qrData); ?>"
              width="150" height="150" title="QR Factura (Fallback)" alt="QR Factura"
              style="display:block; margin-bottom:10px;" />
          <?php endif; ?>

          <small style="color: #666; font-size: 14px; word-break: break-all;">
            <a href="<?php echo $venta["qr_data"]; ?>" target="_blank">Ver validación DIAN</a>
          </small>
          <br>
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
            <tr>
              <th style="width:50%">Subtotal:</th>
              <td>$<?php echo number_format($valorBrutoRecalculado, 2); ?></td>
            </tr>
            <!-- Descuento logic -->
            <?php
            $labelDescuento = "Descuento:";
            if ($tipoDescuento == "porcentaje") {
              $labelDescuento = 'Descuento (' . $valorDescuentoGlobal . '%):';
            } else if ($tipoDescuento == "fijo") {
              $labelDescuento = 'Descuento (Fijo ' . number_format((float)$valorDescuentoGlobal, 0, '', '.') . '):';
            }
            if ($montoDescuentoTotal > 0 || (float)$valorDescuentoGlobal > 0):
            ?>
              <tr>
                <th><?php echo $labelDescuento; ?></th>
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
              <td style="font-size: 1.1em; font-weight: bold;">$<?php echo number_format((float) $valorNetoPagar, 2); ?></td>
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
        <!-- Boton XML -->
        <?php if (!empty($venta["numero_factura"])): ?>
          <a class="btn pull-right"
            style="margin-right: 5px; background-color: #00c0ef; color: white; border-color: #00c0ef;"
            href="descargar-xml.php?xml=<?php echo $venta["numero_factura"]; ?>" target="_blank">
            <i class="fa fa-file-code-o"></i> Descargar XML
          </a>
        <?php endif; ?>

        <!-- Boton PDF -->
        <a class="btn btn-danger pull-right" style="margin-right: 5px;"
          href="extensiones/tcpdf/pdf/descargar-pdf-detalle.php?idVenta=<?php echo $venta["id"]; ?>" target="_blank">
          <i class="fa fa-file-pdf-o"></i> Descargar PDF
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