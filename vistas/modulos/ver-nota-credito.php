<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

// Obtener datos
if (!isset($_GET["idNota"])) {
    echo '<script>window.location = "notas-credito";</script>';
    return;
}

$idNota = $_GET["idNota"];
$notaCredito = ControladorFactus::ctrMostrarNotasCredito("id", $idNota);

if (!$notaCredito) {
    echo '<script>
    window.location = "notas-credito";
  </script>';
    return;
}

// Datos relacionados
$clienteId = !empty($notaCredito["id_cliente"]) ? $notaCredito["id_cliente"] : null;
$cliente = $clienteId ? ControladorClientes::ctrMostrarClientes("id", $clienteId) : [];
$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $notaCredito["id_usuario"]);
$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();
$configFactus = ControladorFactus::ctrObtenerConfiguracion();

// Venta original para obtener CUFE y datos extra
require_once "modelos/ventas.modelo.php";
$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $notaCredito["id_venta_original"]);

// Productos de la NC
$listaProducto = !empty($notaCredito["productos"]) ? json_decode($notaCredito["productos"], true) : [];

// Mapa de impuesto desde la venta original (para mostrar desglose)
$impuestoMap = [];
if ($venta && !empty($venta["productos"])) {
    $productosVenta = json_decode($venta["productos"], true);
    if (is_array($productosVenta)) {
        foreach ($productosVenta as $pv) {
            $impuestoMap[$pv["id"]] = isset($pv["impuesto"]) ? floatval($pv["impuesto"]) : 0;
        }
    }
}

// CUFE de la factura original
$cufeFactura = $venta["cufe"] ?? '';
if (empty($cufeFactura) && !empty($venta["qr_data"])) {
    $parts = parse_url($venta["qr_data"], PHP_URL_QUERY);
    if ($parts) {
        parse_str($parts, $query);
        if (isset($query['documentkey'])) {
            $cufeFactura = $query['documentkey'];
        }
    }
}


?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Ver Nota Crédito
            <small>Panel de Control</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="notas-credito">Notas Crédito</a></li>
            <li class="active">Ver Nota Crédito</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-xs-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nota Crédito:
                            <span
                                class="<?php echo ($notaCredito["estado_dian"] == "borrador" ? 'text-yellow' : ''); ?>">
                                <?php echo $notaCredito["numero_nota_credito"]; ?>
                            </span>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                            <a href="notas-credito" class="btn btn-box-tool"><i class="fa fa-times"></i></a>
                        </div>
                    </div>

                    <div class="box-body">

                        <!-- Invoice Style -->
                        <section class="invoice" style="margin: 0;">
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
                                        <small class="pull-right" style="margin-top: 20px;">Fecha Emisión:
                                            <?php echo $notaCredito["fecha_envio_dian"] ?? date('Y-m-d'); ?>
                                        </small>
                                    </h2>
                                </div>
                            </div>

                            <!-- info row -->
                            <div class="row invoice-info">
                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Emisor</span>
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

                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Cliente</span>
                                    <address>
                                        <strong>Cliente:</strong>
                                        <?php echo $cliente["nombre"] ?? 'Consumidor Final'; ?><br>
                                        <strong>Documento:</strong> <?php echo $cliente["documento"] ?? ''; ?><br>
                                        <strong>Dirección:</strong> <?php echo $cliente["direccion"] ?? ''; ?><br>
                                        <strong>Ciudad:</strong> <?php echo $cliente["ciudad"] ?? ''; ?><br>
                                        <strong>Teléfono:</strong> <?php echo $cliente["telefono"] ?? ''; ?><br>
                                        <strong>Email:</strong> <?php echo $cliente["email"] ?? ''; ?>
                                    </address>
                                </div>

                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Detalles
                                        NC</span>
                                    <b>Nota Crédito #
                                        <span
                                            class="<?php echo ($notaCredito["estado_dian"] == "borrador" ? 'text-yellow' : ''); ?>">
                                            <?php echo $notaCredito["numero_nota_credito"]; ?>
                                        </span>
                                    </b><br>
                                    <b>Factura Relacionada:</b>
                                    <?php echo $notaCredito["numero_factura_original"]; ?><br>
                                    <b>Motivo:</b>
                                    <?php
                                    $motivoNC = $notaCredito["motivo"];
                                    $textoMotivo = "Otro";
                                    switch ($motivoNC) {
                                        case "1":
                                            $textoMotivo = "Devolución parcial de los bienes y/o no aceptación parcial del servicio";
                                            break;
                                        case "2":
                                            $textoMotivo = "Anulación de factura electrónica";
                                            break;
                                        case "3":
                                            $textoMotivo = "Rebaja o descuento parcial o total";
                                            break;
                                        case "4":
                                            $textoMotivo = "Ajuste de precio";
                                            break;
                                        case "5":
                                            $textoMotivo = "Descuento comercial por pronto pago";
                                            break;
                                        case "6":
                                            $textoMotivo = "Descuento comercial por volumen de ventas";
                                            break;
                                    }
                                    echo $textoMotivo;
                                    ?><br>

                                    <b>Estado DIAN:</b>
                                    <?php echo ucfirst($notaCredito["estado_dian"]); ?><br>

                                    <?php if (!empty($notaCredito["metodo_pago"])): ?>
                                        <b>Método de Pago:</b>
                                        <?php
                                        $metodoPagoNombres = [
                                            "Efectivo" => "Efectivo",
                                            "TC" => "Tarjeta Crédito",
                                            "TD" => "Tarjeta Débito",
                                            "Transf" => "Transferencia",
                                            "Cheque" => "Cheque",
                                            "Consignacion" => "Consignación",
                                            "Bonos" => "Bonos",
                                            "Vales" => "Vales",
                                            "Otros" => "Otros",
                                            "No Definido" => "No Definido",
                                        ];
                                        $codigoMP = $notaCredito["metodo_pago"];
                                        echo htmlspecialchars($metodoPagoNombres[$codigoMP] ?? $codigoMP);
                                        ?><br>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Table row -->
                            <div class="row">
                                <div class="col-xs-12 table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cant</th>
                                                <th>Precio Unit.</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($listaProducto as $key => $value) {
                                                echo '<tr>
                                        <td>' . $value["descripcion"] . '</td>
                                        <td>' . $value["cantidad"] . '</td>
                                        <td>$' . number_format($value["precio"], 2) . '</td>
                                        <td>$' . number_format($value["total"], 2) . '</td>
                                    </tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <!-- QR Column -->
                                <div class="col-xs-6">
                                    <p class="lead">Código QR DIAN:</p>
                                    <?php if (!empty($notaCredito["qr_data_nc"])):
                                        $qrData = trim($notaCredito["qr_data_nc"]);
                                        $qrBase64 = "";

                                        // Attempt to generate QR locally
                                        // Path relative to vistas/modulos/ver-nota-credito.php -> pos root
                                        $tcpdfPath = __DIR__ . "/../../extensiones/tcpdf/tcpdf_barcodes_2d.php";

                                        if (file_exists($tcpdfPath)) {
                                            require_once($tcpdfPath);
                                            // Check class exists to be safe
                                            if (class_exists('TCPDF2DBarcode')) {
                                                // Use SVG which doesn't require GD library
                                                try {
                                                    $barcodeobj = new TCPDF2DBarcode($qrData, 'QRCODE,H');
                                                    // Get SVG code
                                                    $svgCode = $barcodeobj->getBarcodeSVGcode(5, 5, 'black');
                                                    if (!empty($svgCode)) {
                                                        $qrBase64 = base64_encode($svgCode);
                                                    }
                                                } catch (Exception $e) {
                                                    // Silent fail to fallback
                                                }
                                            }
                                        }
                                        ?>

                                        <?php if (!empty($qrBase64)): ?>
                                            <img src="data:image/svg+xml;base64,<?php echo $qrBase64; ?>" width="150"
                                                height="150" title="QR Nota Crédito" alt="QR Nota Crédito"
                                                style="display:block; margin-bottom:10px; border:1px solid #ddd;" />
                                        <?php else: ?>
                                            <!-- Fallback to Google Charts -->
                                            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo rawurlencode($qrData); ?>"
                                                width="150" height="150" title="QR Nota Crédito (Fallback)"
                                                alt="QR Nota Crédito" style="display:block; margin-bottom:10px;" />
                                        <?php endif; ?>
                                        <br>

                                        <small style="color: #666; font-size: 14px; word-break: break-all;">
                                            <a href="<?php echo $notaCredito["qr_data_nc"]; ?>" target="_blank">Ver
                                                validación DIAN</a>
                                        </small>
                                        <br>

                                        <!-- Box for CUFE (Invoice) & CUDE (Credit Note) -->
                                        <div
                                            style="margin-top: 10px; border: 1px solid #d2d6de; padding: 10px; border-radius: 5px; background-color: #f9fafc;">

                                            <!-- CUFE Factura -->
                                            <?php if (!empty($cufeFactura)): ?>
                                                <div style="margin-bottom: 5px;">
                                                    <b style="color: #555;">CUFE (Factura):</b><br>
                                                    <span
                                                        style="font-size: 11px; word-break: break-all; display: block; line-height: 1.2; color: #333;">
                                                        <?php echo $cufeFactura; ?>
                                                    </span>
                                                </div>
                                                <hr style="margin: 5px 0; border: 0; border-top: 1px solid #ddd;">
                                            <?php endif; ?>

                                            <!-- CUDE Nota Crédito -->
                                            <div>
                                                <b style="color: #555;">CUDE (Nota Crédito):</b><br>
                                                <span
                                                    style="font-size: 11px; word-break: break-all; display: block; line-height: 1.2; color: #333;">
                                                    <?php echo $notaCredito["cufe_nc"]; ?>
                                                </span>
                                            </div>

                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                            QR no disponible.
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($notaCredito["observacion"])): ?>
                                        <p class="lead" style="margin-top: 20px; font-size: 16px;">Observaciones:</p>
                                        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                            <?php echo nl2br($notaCredito["observacion"]); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Totals Column -->
                                <div class="col-xs-6">
                                    <p class="lead">Resumen Financiero</p>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th style="width:50%">Valor Bruto:</th>
                                                <td>$
                                                    <?php
                                                    $totalBase = 0;
                                                    $totalImpuesto = 0;

                                                    foreach ($listaProducto as $prod) {
                                                        // Lógica alineada con Factus: El precio en BD es Intra-Impuesto
                                                        $precioUnitarioConImpuesto = floatval($prod["precio"]);
                                                        $cantidad = floatval($prod["cantidad"]);
                                                        $tasaImpuesto = isset($prod["impuesto"]) && $prod["impuesto"] !== ""
                                                            ? floatval($prod["impuesto"])
                                                            : ($impuestoMap[$prod["id"]] ?? 0);

                                                        // Subtotal es cantidad * precio (incluye impuesto)
                                                        $subtotalItem = $precioUnitarioConImpuesto * $cantidad;

                                                        // Back-out tax
                                                        $baseItem = $subtotalItem / (1 + ($tasaImpuesto / 100));
                                                        $impuestoItem = $subtotalItem - $baseItem;

                                                        $totalBase += $baseItem;
                                                        $totalImpuesto += $impuestoItem;
                                                    }

                                                    echo number_format($totalBase, 2);
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Subtotal:</th>
                                                <td>$ <?php echo number_format($totalBase, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Impuestos:</th>
                                                <td>$ <?php echo number_format($totalImpuesto, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Devolución:</th>
                                                <td>$ <?php echo number_format($totalBase + $totalImpuesto, 2); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-xs-12">

                                    <!-- Descargar PDF (vista interna - TCPDF) -->
                                    <a href="extensiones/tcpdf/pdf/descargar-pdf-nota-credito.php?idNota=<?php echo $idNota; ?>"
                                        target="_blank" class="btn btn-success pull-right" style="margin-right: 5px;">
                                        <i class="fa fa-file-pdf-o"></i> Descargar PDF
                                    </a>

                                    <!-- Descargar XML (desde Factus API usando número de la NC) -->
                                    <?php if (!empty($notaCredito["numero_nota_credito"])): ?>
                                        <a href="descargar-xml-nc.php?xml=<?php echo urlencode($notaCredito["numero_nota_credito"]); ?>"
                                            target="_blank" class="btn pull-right"
                                            style="margin-right: 5px; background-color: #00c0ef; color: white; border-color: #00acd6;">
                                            <i class="fa fa-file-code-o"></i> Descargar XML
                                        </a>
                                    <?php endif; ?>

                                    <a href="notas-credito" class="btn btn-default pull-right"
                                        style="margin-right: 5px;">
                                        <i class="fa fa-arrow-left"></i> Volver
                                    </a>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>