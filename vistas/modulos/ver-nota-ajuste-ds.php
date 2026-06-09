<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

$idNota = $_GET["idNota"];
$nota = ControladorFactus::ctrMostrarNotasAjusteDS("id", $idNota);

if (!$nota) {
    echo '<script>
    window.location = "notas-ajuste-ds";
  </script>';
    return;
}

$documentoSoporte = ControladorFactus::ctrMostrarDocumentosSoporte("id", $nota["id_ds_original"]);
$proveedor = ControladorProveedores::ctrMostrarProveedores("id", $nota["id_proveedor"]);
$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $nota["id_usuario"]);
$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();
$configFactus = ControladorFactus::ctrObtenerConfiguracion();

// Productos del Ajuste
$listaProducto = json_decode($nota["productos"], true);

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Ver Nota de Ajuste DS
            <small>Panel de Control</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="notas-ajuste-ds">Notas de Ajuste DS</a></li>
            <li class="active">Ver Nota de Ajuste</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-xs-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nota de Ajuste:
                            <?php echo $nota["numero_nota_ajuste"]; ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                            <a href="notas-ajuste-ds" class="btn btn-box-tool"><i class="fa fa-times"></i></a>
                        </div>
                    </div>

                    <div class="box-body">

                        <section class="invoice" style="margin: 0;">

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
                                        <small class="pull-right" style="margin-top: 20px;">Fecha Nota:
                                            <?php echo $nota["fecha_envio_dian"]; ?>
                                        </small>
                                    </h2>
                                </div>
                            </div>

                            <div class="row invoice-info">
                                <div class="col-sm-3 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #3c8dbc; display: block; margin-bottom: 10px; width: fit-content;">Emisor (Comprador)</span>
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

                                <div class="col-sm-3 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #3c8dbc; display: block; margin-bottom: 10px; width: fit-content;">Proveedor (Vendedor)</span>
                                    <address>
                                        <strong>Nombre:</strong> <?php echo $proveedor["nombre"] ?? 'Proveedor'; ?><br>
                                        <strong>NIT:</strong> <?php echo $proveedor["documento"] ?? ''; ?><br>
                                        <strong>Dirección:</strong> <?php echo $proveedor["direccion"] ?? ''; ?><br>
                                        <strong>Municipio:</strong> <?php
                                        if (!empty($proveedor["municipio_id"])) {
                                            $mun = ModeloFactus::mdlMostrarMunicipioPorId($proveedor["municipio_id"]);
                                            echo $mun ? ($mun["nombre"] . (!empty($mun["departamento"]) ? ' - ' . $mun["departamento"] : '')) : $proveedor["municipio_id"];
                                        } else {
                                            echo "No definido";
                                        }
                                        ?><br>
                                        <strong>Teléfono:</strong> <?php echo $proveedor["celular"] ?? ''; ?><br>
                                        <strong>Email:</strong> <?php echo $proveedor["correo"] ?? ''; ?>
                                    </address>
                                </div>

                                <div class="col-sm-3 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #3c8dbc; display: block; margin-bottom: 10px; width: fit-content;">Referencia Original</span>
                                    <b>Doc. Soporte Original:</b> <?php echo $nota["numero_ds_original"]; ?>
                                </div>

                                <div class="col-sm-3 invoice-col">
                                    <span
                                        style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #3c8dbc; display: block; margin-bottom: 10px; width: fit-content;">Detalles Nota</span>
                                    <b>Nota Ajuste #:</b> <?php echo $nota["numero_nota_ajuste"]; ?><br>
                                    <b>Concepto Ajuste:</b> <?php
                                    $conceptos = [
                                        "1" => "Devolución parcial de los bienes y/o no aceptación parcial del servicio",
                                        "2" => "Anulación de documento soporte",
                                        "3" => "Rebaja o descuento parcial o total",
                                        "4" => "Ajuste de precio",
                                        "5" => "Otros"
                                    ];
                                    echo $conceptos[$nota["tipo_nota"]] ?? "No definido";
                                    ?><br>
                                    <b>Medio de Pago:</b>
                                    <?php echo htmlspecialchars($nota["metodo_pago"] ?? "No definido"); ?>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 20px;">
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
                                                $totalProducto = $value["total"] ?? ($value["cantidad"] * $value["precio"]);
                                                echo '<tr>
                                                    <td>' . $value["descripcion"] . '</td>
                                                    <td>' . $value["cantidad"] . '</td>
                                                    <td>$' . number_format((float) ($value["precio"] ?? 0), 2) . '</td>
                                                    <td>$' . number_format((float) ($totalProducto), 2) . '</td>
                                                </tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-xs-6 col-xs-offset-6">
                                    <p class="lead">Resumen Financiero</p>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr style="font-size: 18px;">
                                                <th style="width:50%">Total Ajustado:</th>
                                                <td><strong>$
                                                        <?php echo number_format((float) ($nota["monto_total"] ?? 0), 2); ?></strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">


                                <p class="lead" style="margin-top: 20px;">Código QR DIAN:</p>
                                <?php if (!empty($nota["qr_data"])):
                                    $qrData = trim($nota["qr_data"]);
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
                                        <img src="data:image/svg+xml;base64,<?php echo $qrBase64; ?>" width="150" height="150"
                                            title="QR Nota de Ajuste" alt="QR Nota de Ajuste"
                                            style="display:block; margin-bottom:10px; border:1px solid #ddd;" />
                                    <?php else: ?>
                                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo rawurlencode($qrData); ?>"
                                            width="150" height="150" title="QR Nota de Ajuste (Fallback)"
                                            alt="QR Nota de Ajuste" style="display:block; margin-bottom:10px;" />
                                    <?php endif; ?>

                                    <small style="color: #666; font-size: 14px; word-break: break-all;">
                                        <a href="<?php echo $nota["qr_data"]; ?>" target="_blank">Ver validación DIAN</a>
                                    </small>
                                <?php else: ?>
                                    <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                        QR no disponible para esta nota.
                                    </p>
                                <?php endif; ?>
                            </div>


                            <div class="row">
                                <div class="col-xs-6">
                                <p class="lead">Observación:</p>
                                <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                    <?php echo $nota["motivo"] ?: ($nota["observacion"] ?: "Sin observaciones adicionales."); ?>
                                </p>

                                <!-- Botones externos de Factus -->
                                <div style="margin-top: 20px;">
                                    <?php if (!empty($nota["pdf_dian"])): ?>
                                        <a href="<?php echo $nota["pdf_dian"]; ?>" target="_blank"
                                            class="btn btn-danger btn-sm">
                                            <i class="fa fa-file-pdf-o"></i> Ver PDF Oficial DIAN
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($nota["xml_dian"])): ?>
                                        <a href="<?php echo $nota["xml_dian"]; ?>" target="_blank"
                                            class="btn btn-primary btn-sm">
                                            <i class="fa fa-file-code-o"></i> Ver XML Oficial DIAN
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- CUDS Informacion -->
                                <div class="well well-sm" style="margin-top: 20px; background-color: #f9fafc;">
                                    <b style="color: #3c8dbc;">CUDS Documento Soporte Original:</b><br>
                                    <span
                                        style="font-size: 11px; word-break: break-all;"><?php echo $documentoSoporte["cuds"]; ?></span><br><br>

                                    <b style="color: #3c8dbc;">CUDS Nota de Ajuste:</b><br>
                                    <span
                                        style="font-size: 11px; word-break: break-all;"><?php echo $nota["cuds_ajuste"]; ?></span>
                                </div>
                            </div>




                            <div class="row no-print" style="margin-top: 20px;">
                                <div class="col-xs-12">
                                    <!-- Descargar XML (Local Downloader - Con validación de estado) -->
                                    <?php if ($nota["estado_dian"] != "borrador" && !empty($nota["numero_nota_ajuste"])): ?>
                                        <a href="descargar-xml-na.php?id=<?php echo $idNota; ?>"
                                            target="_blank" class="btn pull-right"
                                            style="margin-right: 5px; background-color: #00c0ef; color: white; border-color: #00acd6;">
                                            <i class="fa fa-file-code-o"></i> Descargar XML
                                        </a>
                                    <?php endif; ?>

                                    <!-- Descargar PDF (TCPDF Local) -->
                                    <a href="extensiones/tcpdf/pdf/descargar-pdf-nota-ajuste-ds.php?idNota=<?php echo $idNota; ?>"
                                        target="_blank" class="btn btn-danger pull-right" style="margin-right: 5px;">
                                        <i class="fa fa-file-pdf-o"></i> Descargar PDF
                                    </a>

                                    <a href="notas-ajuste-ds" class="btn btn-default pull-right"
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