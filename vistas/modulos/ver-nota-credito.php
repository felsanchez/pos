<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

// Obtener datos
$idVenta = $_GET["idVenta"];
$notaCredito = ModeloFactus::mdlObtenerNotaCredito($idVenta);
$venta = ControladorVentas::ctrMostrarVentas("id", $idVenta);

if (!$notaCredito || !$venta) {
    echo '<script>
    window.location = "facturas-electronicas";
  </script>';
    return;
}

// Usar el cliente de la NC si está guardado, si no, el de la venta original
$clienteId = !empty($notaCredito["id_cliente"]) ? $notaCredito["id_cliente"] : $venta["id_cliente"];
$cliente = ControladorClientes::ctrMostrarClientes("id", $clienteId);
$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();

// Productos de la NC (normalmente los mismos de la venta, o subconjunto)
$listaProducto = json_decode($notaCredito["productos"], true);

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Ver Nota Crédito
            <small>Panel de Control</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="facturas-electronicas">Facturas Electrónicas</a></li>
            <li class="active">Ver Nota Crédito</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-xs-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nota Crédito:
                            <?php echo $notaCredito["numero_nota_credito"]; ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                            <a href="facturas-electronicas" class="btn btn-box-tool"><i class="fa fa-times"></i></a>
                        </div>
                    </div>

                    <div class="box-body">

                        <!-- Invoice Style -->
                        <section class="invoice" style="margin: 0;">
                            <!-- title row -->
                            <div class="row">
                                <div class="col-xs-12">
                                    <h2 class="page-header">
                                        <i class="fa fa-globe"></i>
                                        <?php echo $configuracion["nombre_empresa"] ?? 'Empresa'; ?>
                                        <small class="pull-right">Fecha Emisión:
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
                                        <strong>
                                            <?php echo $configuracion["nombre_empresa"] ?? 'Nombre Empresa'; ?>
                                        </strong><br>
                                        NIT:
                                        <?php echo $configuracion["nit"] ?? ''; ?><br>
                                        <?php echo $configuracion["direccion"] ?? ''; ?><br>
                                        Teléfono:
                                        <?php echo $configuracion["telefono"] ?? ''; ?><br>
                                        Email:
                                        <?php echo $configuracion["correo"] ?? ''; ?>
                                    </address>
                                </div>

                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Cliente</span>
                                    <address>
                                        <strong>
                                            <?php echo $cliente["nombre"] ?? 'Consumidor Final'; ?>
                                        </strong><br>
                                        Documento:
                                        <?php echo $cliente["documento"] ?? ''; ?><br>
                                        Dirección:
                                        <?php echo $cliente["direccion"] ?? ''; ?><br>
                                        Tel:
                                        <?php echo $cliente["telefono"] ?? ''; ?><br>
                                        Email:
                                        <?php echo $cliente["email"] ?? ''; ?>
                                    </address>
                                </div>

                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Detalles
                                        NC</span>
                                    <b>Nota Crédito #
                                        <?php echo $notaCredito["numero_nota_credito"]; ?>
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
                                    <b>CUDE:</b> <span style="font-size: 10px;">
                                        <?php echo $notaCredito["cufe_nc"]; ?>
                                    </span><br>
                                    <b>Estado DIAN:</b>
                                    <?php echo ucfirst($notaCredito["estado_dian"]); ?>
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
                                    <?php if (!empty($notaCredito["qr_data_nc"])): ?>
                                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo urlencode($notaCredito["qr_data_nc"]); ?>&choe=UTF-8"
                                            title="QR Factura Electrónica" />
                                        <br>
                                        <small style="color: #666; font-size: 10px; word-break: break-all;">
                                            <a href="<?php echo $notaCredito["qr_data_nc"]; ?>" target="_blank">Ver
                                                validación DIAN</a>
                                        </small>
                                    <?php else: ?>
                                        <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                            QR no disponible.
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Totals Column -->
                                <div class="col-xs-6">
                                    <p class="lead">Resumen Financiero</p>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th style="width:50%">Total Devolución:</th>
                                                <td>$
                                                    <?php echo number_format($notaCredito["monto_total"], 2); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-xs-12">
                                    <?php if ($notaCredito["pdf_dian_nc"]): ?>
                                        <a href="<?php echo $notaCredito["pdf_dian_nc"]; ?>" target="_blank" class="btn
                                        btn-primary pull-right" style="margin-right: 5px;">
                                            <i class="fa fa-download"></i> Descargar PDF Oficial
                                        </a>
                                    <?php endif; ?>

                                    <a href="facturas-electronicas" class="btn btn-default pull-right"
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