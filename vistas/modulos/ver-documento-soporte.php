<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

// Obtener datos
$idDS = $_GET["idDS"];
$documentoSoporte = ControladorFactus::ctrMostrarDocumentosSoporte("id", $idDS);

if (!$documentoSoporte) {
    echo '<script>
    window.location = "documentos-soporte";
  </script>';
    return;
}

require_once "controladores/proveedores.controlador.php";
$proveedor = ControladorProveedores::ctrMostrarProveedores("id", $documentoSoporte["id_proveedor"]);
$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $documentoSoporte["id_usuario"]);
$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();
$configFactus = ControladorFactus::ctrObtenerConfiguracion();

// Productos del DS
$listaProducto = json_decode($documentoSoporte["productos"], true);

// Retenciones del DS
$retencionesDS = !empty($documentoSoporte["retenciones"]) ? json_decode($documentoSoporte["retenciones"], true) : [];

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Ver Documento Soporte
            <small>Panel de Control</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="documentos-soporte">Documentos Soporte</a></li>
            <li class="active">Ver Documento Soporte</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-xs-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Documento Soporte:
                            <?php echo $documentoSoporte["numero_ds"]; ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                            <a href="documentos-soporte" class="btn btn-box-tool"><i class="fa fa-times"></i></a>
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
                                            <?php echo $documentoSoporte["fecha_emision"]; ?>
                                        </small>
                                    </h2>
                                </div>
                            </div>

                            <!-- info row -->
                            <div class="row invoice-info">
                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Emisor
                                        (Comprador)</span>
                                    <address>
                                        <?php
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

                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Proveedor
                                        (Vendedor)</span>
                                    <address>
                                        <strong>Nombre:</strong>
                                        <?php echo $proveedor["nombre"] ?? 'Proveedor'; ?><br>
                                        <strong>Documento:</strong> <?php echo $proveedor["documento"] ?? ''; ?><br>
                                        <strong>Dirección:</strong> <?php echo $proveedor["direccion"] ?? ''; ?><br>
                                        <strong>Municipio:</strong> <?php
                                        if (!empty($proveedor["municipio_id"])) {
                                            $mun = ModeloFactus::mdlMostrarMunicipioPorId($proveedor["municipio_id"]);
                                            echo $mun ? $mun["nombre"] : $proveedor["municipio_id"];
                                        } else {
                                            echo "No definido";
                                        }
                                        ?><br>
                                        <strong>Teléfono:</strong> <?php echo $proveedor["celular"] ?? ''; ?><br>
                                        <strong>Email:</strong> <?php echo $proveedor["correo"] ?? ''; ?>
                                    </address>
                                </div>

                                <div class="col-sm-4 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #d2d6de; display: block; margin-bottom: 10px; width: fit-content;">Detalles
                                        DS</span>
                                    <b>Documento Soporte #
                                        <?php echo $documentoSoporte["numero_ds"]; ?>
                                    </b><br>
                                    <b>Método de Pago:</b>
                                    <?php
                                    $partesMetodo = explode("-", $documentoSoporte["metodo_pago"]);
                                    echo $partesMetodo[0];
                                    if (isset($partesMetodo[1]) && !empty($partesMetodo[1])) {
                                        echo ' (' . $partesMetodo[1] . ')';
                                    }
                                    ?><br>
                                    <b>Estado DIAN:</b>
                                    <?php echo ucfirst($documentoSoporte["estado_dian"]); ?><br>
                                    <b>Vendedor:</b>
                                    <?php echo $vendedor["nombre"] ?? 'N/A'; ?>
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
                                    <?php if (!empty($documentoSoporte["qr_data"])):
                                        $qrData = trim($documentoSoporte["qr_data"]);
                                        $qrBase64 = "";

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
                                                }
                                            }
                                        }
                                        ?>

                                        <?php if (!empty($qrBase64)): ?>
                                            <img src="data:image/svg+xml;base64,<?php echo $qrBase64; ?>" width="150"
                                                height="150" title="QR Documento Soporte" alt="QR Documento Soporte"
                                                style="display:block; margin-bottom:10px; border:1px solid #ddd;" />
                                        <?php else: ?>
                                            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo rawurlencode($qrData); ?>"
                                                width="150" height="150" title="QR Documento Soporte (Fallback)"
                                                alt="QR Documento Soporte" style="display:block; margin-bottom:10px;" />
                                        <?php endif; ?>
                                        <br>

                                        <small style="color: #666; font-size: 14px; word-break: break-all;">
                                            <a href="<?php echo $documentoSoporte["qr_data"]; ?>" target="_blank">Ver
                                                validación DIAN</a>
                                        </small>
                                        <br>

                                        <div
                                            style="margin-top: 10px; border: 1px solid #d2d6de; padding: 10px; border-radius: 5px; background-color: #f9fafc;">
                                            <div>
                                                <b style="color: #555;">CUDS (Documento Soporte):</b><br>
                                                <span
                                                    style="font-size: 11px; word-break: break-all; display: block; line-height: 1.2; color: #333;">
                                                    <?php echo $documentoSoporte["cuds"]; ?>
                                                </span>
                                            </div>
                                        </div>
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
                                                <th style="width:50%">Subtotal (Sin Desc):</th>
                                                <td>$ <?php
                                                $sumSubtotal = 0;
                                                foreach ($listaProducto as $p) {
                                                    $sumSubtotal += ($p["precio"] * $p["cantidad"]);
                                                }
                                                echo number_format($sumSubtotal, 2);
                                                ?></td>
                                            </tr>

                                            <?php if ($documentoSoporte["monto_descuento"] > 0): ?>
                                                <tr>
                                                    <th>Descuento
                                                        (<?php echo ($documentoSoporte["tipo_descuento"] == "porcentaje" ? $documentoSoporte["valor_descuento"] . "%" : "Valor Fijo"); ?>):
                                                    </th>
                                                    <td class="text-danger">-$
                                                        <?php echo number_format($documentoSoporte["monto_descuento"], 2); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Subtotal (Con Desc):</th>
                                                    <td>$
                                                        <?php echo number_format($sumSubtotal - $documentoSoporte["monto_descuento"], 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>

                                            <!-- Retenciones -->
                                            <?php if (!empty($retencionesDS)): ?>
                                                <?php
                                                $totalRetenciones = 0;
                                                foreach ($retencionesDS as $ret):
                                                    $totalRetenciones += $ret["monto"];
                                                    ?>
                                                    <tr>
                                                        <th><?php echo $ret["tipo"]; ?> (<?php echo $ret["porcentaje"]; ?>%):
                                                        </th>
                                                        <td class="text-orange">-$
                                                            <?php echo number_format($ret["monto"], 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="active">
                                                    <th>Total Retenciones:</th>
                                                    <td><strong>-$
                                                            <?php echo number_format($totalRetenciones, 2); ?></strong></td>
                                                </tr>
                                            <?php endif; ?>

                                            <tr style="font-size: 18px;">
                                                <th>Total a Pagar:</th>
                                                <td><strong>$
                                                        <?php echo number_format($documentoSoporte["monto_total"], 2); ?></strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-xs-12">
                                    <a href="descargar-xml-ds.php?xml=<?php echo $documentoSoporte["numero_ds"]; ?>"
                                        target="_blank" class="btn pull-right"
                                        style="margin-right: 5px; background-color: #00c0ef; color: white; border-color: #00acd6;">
                                        <i class="fa fa-file-code-o"></i> Descargar XML
                                    </a>

                                    <a href="extensiones/tcpdf/pdf/descargar-pdf-documento-soporte.php?idDS=<?php echo $idDS; ?>"
                                        target="_blank" class="btn btn-danger pull-right" style="margin-right: 5px;">
                                        <i class="fa fa-file-pdf-o"></i> Descargar PDF
                                    </a>


                                    <a href="documentos-soporte" class="btn btn-default pull-right"
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