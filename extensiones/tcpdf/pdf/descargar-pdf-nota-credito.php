<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/configuracion.controlador.php";
require_once "../../../modelos/configuracion.modelo.php";

require_once "../../../controladores/factus.controlador.php";
require_once "../../../modelos/factus.modelo.php";

class imprimirNotaCredito
{
    public $idVenta;

    public function traerImpresionDetalle()
    {
        $idVenta = $this->idVenta;
        $notaCredito = ModeloFactus::mdlObtenerNotaCredito($idVenta);
        $venta = ControladorVentas::ctrMostrarVentas("id", $idVenta);

        if (!$notaCredito || !$venta) {
            die("Nota de crédito no encontrada.");
        }

        // Usar el cliente de la NC si está guardado, si no, el de la venta original
        $clienteId = !empty($notaCredito["id_cliente"]) ? $notaCredito["id_cliente"] : $venta["id_cliente"];
        $cliente = ControladorClientes::ctrMostrarClientes("id", $clienteId);
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();

        // Construir mapa de impuesto por ID de producto desde la venta original
        $productosVenta = json_decode($venta["productos"], true);
        $impuestoMap = [];
        if (is_array($productosVenta)) {
            foreach ($productosVenta as $pv) {
                $impuestoMap[$pv["id"]] = isset($pv["impuesto"]) ? floatval($pv["impuesto"]) : 0;
            }
        }

        // --- MOTIVO NC ---
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

        // --- CUFE FACTURA ---
        $cufeFactura = $venta["cufe"];
        if (empty($cufeFactura) && !empty($venta["qr_data"])) {
            $parts = parse_url($venta["qr_data"], PHP_URL_QUERY);
            if ($parts) {
                parse_str($parts, $query);
                if (isset($query['documentkey'])) {
                    $cufeFactura = $query['documentkey'];
                }
            }
        }

        // REQUERIMOS LA CLASE TCPDF
        require_once('tcpdf_include.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Nota Crédito #' . $notaCredito["numero_nota_credito"]);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Estilos CSS
        $style = '
        <style>
            .invoice { font-family: helvetica; color: #444; }
            .page-header { border-bottom: 2px solid #3c8dbc; padding-bottom: 10px; color: #444; }
            .invoice-info { background-color: #f8f9fa; padding: 10px; margin-bottom: 20px; border-left: 4px solid #3c8dbc; }
            .table { width: 100%; border-collapse: collapse; }
            .table-header { background-color: #3c8dbc; color: white; font-weight: bold; text-transform: uppercase; font-size: 10px; }
            .table td, .table th { border-bottom: 1px solid #eee; padding: 8px; font-size: 10px; }
            .lead { font-size: 12px; font-weight: bold; color: #444; background-color: #e9ecef; padding: 5px; border-left: 4px solid #d2d6de; margin-bottom: 10px; }
            .well { background-color: #f0f0f0; padding: 10px; border: 1px solid #ddd; font-size: 10px; color: #555; }
            .total-table { width: 100%; }
            .total-table th { text-align: left; font-weight: bold; padding: 5px; }
            .total-table td { text-align: right; padding: 5px; }
        </style>';

        // --- VARIABLES EMISOR ---
        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';

        // --- LOGO FACTUS ---
        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = "../../../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:40px; vertical-align:middle; margin-bottom:5px;"> ';
            }
        }

        // --- HEADER ---
        $htmlHeader = $style . '<div class="invoice">';
        $htmlHeader .= '<table style="width:100%; border-bottom: 2px solid #3c8dbc; padding-bottom:10px;">
            <tr>
                <td style="width:70%; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:bold; color:#444;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:30%; text-align:right; vertical-align:bottom;">
                    <span style="font-size:10px; color:#666;">Fecha Emisión: ' . ($notaCredito["fecha_envio_dian"] ?? date('Y-m-d')) . '</span>
                </td>
            </tr>
        </table><br>';

        $htmlHeader .= '<table class="table" cellpadding="6">
            <tr>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Emisor</span><br><br>
                    <strong>' . $labelNombreEmisor . ':</strong> ' . $nombreEmpresa . '<br>
                    <strong>NIT:</strong> ' . $nitEmisor . '<br>
                    <strong>Dirección:</strong> ' . $direccionEmisor . '<br>
                    <strong>Teléfono:</strong> ' . $telefonoEmisor . '<br>
                    <strong>Email:</strong> ' . $emailEmisor . '
                </td>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Cliente</span><br><br>
                    <strong>Cliente:</strong> ' . ($cliente["nombre"] ?? 'Consumidor Final') . '<br>
                    <strong>Documento:</strong> ' . ($cliente["documento"] ?? '') . '<br>
                    <strong>Dirección:</strong> ' . ($cliente["direccion"] ?? '') . '<br>
                    <strong>Ciudad:</strong> ' . ($cliente["ciudad"] ?? '') . '<br>
                    <strong>Teléfono:</strong> ' . ($cliente["telefono"] ?? '') . '<br>
                    <strong>Email:</strong> ' . ($cliente["email"] ?? '') . '
                </td>
                <td style="width:34%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Detalles NC</span><br><br>
                    <strong>Nota Crédito #' . $notaCredito["numero_nota_credito"] . '</strong><br>
                    <strong>Factura Relac:</strong> ' . $notaCredito["numero_factura_original"] . '<br>
                    <strong>Motivo:</strong> ' . $textoMotivo . '<br>
                    <strong>Estado:</strong> ' . ucfirst($notaCredito["estado_dian"]) . '
                </td>
            </tr>
        </table><br><br>';

        // --- PRODUCTOS ---
        $htmlHeader .= '<table class="table" cellpadding="5">
            <thead>
                <tr class="table-header">
                    <th style="width:45%;">Producto</th>
                    <th style="width:15%; text-align:center;">Cant</th>
                    <th style="width:20%; text-align:right;">Precio Unit.</th>
                    <th style="width:20%; text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>';

        $listaProducto = json_decode($notaCredito["productos"], true);
        $totalBase = 0;
        $totalImpuesto = 0;

        foreach ($listaProducto as $prod) {
            $precioUnitarioConImpuesto = floatval($prod["precio"]);
            $cantidad = floatval($prod["cantidad"]);
            $tasaImpuesto = isset($prod["impuesto"]) && $prod["impuesto"] !== "" ? floatval($prod["impuesto"]) : ($impuestoMap[$prod["id"]] ?? 0);

            $subtotalItem = $precioUnitarioConImpuesto * $cantidad;
            $baseItem = $subtotalItem / (1 + ($tasaImpuesto / 100));
            $impuestoItem = $subtotalItem - $baseItem;

            $totalBase += $baseItem;
            $totalImpuesto += $impuestoItem;

            $htmlHeader .= '<tr>
                <td>' . $prod["descripcion"] . '</td>
                <td style="text-align:center;">' . $prod["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($precioUnitarioConImpuesto, 2) . '</td>
                <td style="text-align:right;">$' . number_format($subtotalItem, 2) . '</td>
            </tr>';
        }
        $htmlHeader .= '</tbody></table><br><br>';

        $pdf->writeHTML($htmlHeader, true, false, true, false, '');
        $startY = $pdf->GetY();

        // --- COLUMNA IZQUIERDA (QR / CUFEs / Obs) ---
        $htmlLeft = $style . '<div class="invoice">';

        if (!empty($notaCredito["observacion"])) {
            $htmlLeft .= '<div class="lead">Observaciones:</div><div class="well">' . nl2br($notaCredito["observacion"]) . '</div><br>';
        }

        $htmlLeft .= '</div>';
        $pdf->writeHTMLCell(110, '', 10, $startY, $htmlLeft, 0, 1, false, true, 'L', true);

        // QR e Identificadores
        $yDespuesDeNotas = $pdf->GetY() + 5;
        $pdf->SetY($yDespuesDeNotas);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(233, 236, 239);

        if (!empty($notaCredito["qr_data_nc"])) {
            $pdf->Cell(110, 6, ' Código QR DIAN:', 'L', 1, 'L', true);
            $yQR = $pdf->GetY() + 2;
            $styleQR = array('border' => 0, 'vpadding' => 'auto', 'hpadding' => 'auto', 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'module_width' => 1, 'module_height' => 1);
            $pdf->write2DBarcode(trim($notaCredito["qr_data_nc"]), 'QRCODE,H', 15, $yQR, 35, 35, $styleQR, 'N');

            $pdf->SetY($yQR + 36);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(119, 119, 119);
            $pdf->MultiCell(105, 0, trim($notaCredito["qr_data_nc"]), 0, 'L', false, 1, 12, '', true);

            // CUFEs
            $pdf->Ln(5);
            $pdf->SetTextColor(68, 68, 68);

            if (!empty($cufeFactura)) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(105, 5, ' CUFE (Factura):', 'L', 1, 'L', true);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->MultiCell(105, 0, $cufeFactura, 1, 'L', true, 1, 12, '', true);
                $pdf->Ln(2);
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(105, 5, ' CUDE (Nota Crédito):', 'L', 1, 'L', true);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(105, 0, $notaCredito["cufe_nc"], 1, 'L', true, 1, 12, '', true);
        }

        // --- COLUMNA DERECHA (Totales) ---
        $htmlRight = $style . '<div class="invoice">';
        $htmlRight .= '<div class="lead">Resumen Financiero</div>
                    <table class="total-table" cellpadding="6">
                        <tr><th>Valor Bruto:</th><td>$' . number_format($totalBase, 2) . '</td></tr>
                        <tr><th>Subtotal:</th><td>$' . number_format($totalBase, 2) . '</td></tr>
                        <tr><th>Impuestos:</th><td>$' . number_format($totalImpuesto, 2) . '</td></tr>
                        <tr style="background-color:#eee; font-size:12px;">
                            <th style="font-weight:bold;">TOTAL DEVOLUCIÓN:</th>
                            <td style="font-weight:bold;">$' . number_format($totalBase + $totalImpuesto, 2) . '</td>
                        </tr>
                    </table></div>';

        $pdf->writeHTMLCell(75, '', 125, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        ob_end_clean();
        $pdf->Output('nota-credito-' . $notaCredito["numero_nota_credito"] . '.pdf', 'I');
    }
}

if (isset($_GET["idVenta"])) {
    $imprimir = new imprimirNotaCredito();
    $imprimir->idVenta = $_GET["idVenta"];
    $imprimir->traerImpresionDetalle();
}
