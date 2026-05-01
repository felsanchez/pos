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

class imprimirDetalleOrden
{
    public $idVenta;

    public function traerImpresionDetalle()
    {
        $item = "id";
        $valor = $this->idVenta;
        $venta = ControladorVentas::ctrMostrarVentas($item, $valor);

        if (!$venta) {
            die("Orden no encontrada.");
        }

        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
        $cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();

        // Identificar tipo de documento
        $tipoDocumento = "ORDEN DE VENTA";
        $etiquetaDocumento = "Orden de Venta";
        $numeroDocumento = $venta["codigo"] ?? '';

        if (!empty($venta["numero_factura"])) {
            $tipoDocumento = "FACTURA ELECTRÓNICA";
            $etiquetaDocumento = "Factura Electrónica";
            $numeroDocumento = $venta["numero_factura"];
        } else if ($venta["estado"] == "venta") {
            $tipoDocumento = "FACTURA DE VENTA";
            $etiquetaDocumento = "Factura de Venta";
            $numeroDocumento = $venta["codigo"] ?? '';
        }

        // Extraer CUFE de la URL si está vacío en la base de datos
        $cufeExtraido = $venta["cufe"] ?? '';
        if (empty($cufeExtraido) && !empty($venta["qr_data"])) {
            $parts = parse_url($venta["qr_data"], PHP_URL_QUERY);
            if ($parts) {
                parse_str($parts, $query);
                if (isset($query['documentkey'])) {
                    $cufeExtraido = $query['documentkey'];
                }
            }
        }

        // REQUERIMOS LA CLASE TCPDF
        require_once('tcpdf_include.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle($etiquetaDocumento . ' #' . $numeroDocumento);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Estilos CSS para el PDF (Simplificados para TCPDF)
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

        // --- DEFINICIÓN DE VARIABLES (Ámbito principal) ---
        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';

        // --- LOGO FACTUS (Si existe) ---
        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = "../../../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                // Convertir a base64 para evitar problemas de ruta en TCPDF
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:40px; vertical-align:middle; margin-bottom:5px;"> ';
            }
        }

        // --- PARTE 1: HEADER Y TABLA DE PRODUCTOS (Ancho completo) ---
        $htmlHeader = $style . '<div class="invoice">';
        $htmlHeader .= '<table style="width:100%; border-bottom: 2px solid #3c8dbc; padding-bottom:10px;">
            <tr>
                <td style="width:50%; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:bold; color:#444;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:50%; text-align:right; vertical-align:middle;">
                    <span style="font-size:16px; font-weight:bold; color:#3c8dbc;">' . $tipoDocumento . '</span><br>
                    <span style="font-size:10px; color:#666;">Fecha: ' . $venta["fecha"] . '</span>
                </td>
            </tr>
        </table><br>';

        $htmlHeader .= '<table class="table" cellpadding="6">
            <tr>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Empresa</span><br><br>
                    <strong>' . $labelNombreEmisor . ':</strong> ' . $nombreEmpresa . '<br>
                    <strong>NIT:</strong> ' . $nitEmisor . '<br>
                    <strong>Dirección:</strong> ' . $direccionEmisor . '<br>
                    <strong>Teléfono:</strong> ' . $telefonoEmisor . '<br>
                    <strong>Email:</strong> ' . $emailEmisor . '
                </td>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Cliente</span><br><br>
                    <strong>Cliente:</strong> ' . ($cliente["nombre"] ?? '') . '<br>
                    <strong>Documento:</strong> ' . ($cliente["documento"] ?? '') . '<br>
                    <strong>Dirección:</strong> ' . ($cliente["direccion"] ?? '') . '<br>
                    <strong>Ciudad:</strong> ' . ($cliente["ciudad"] ?? '') . '<br>
                    <strong>Teléfono:</strong> ' . ($cliente["telefono"] ?? '') . '<br>
                    <strong>Email:</strong> ' . ($cliente["email"] ?? '') . '
                </td>
                <td style="width:34%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Detalles</span><br><br>
                    <strong>' . $etiquetaDocumento . ' #' . $numeroDocumento . '</strong><br><br>
                    <strong>Vendedor:</strong> ' . ($vendedor["nombre"] ?? '') . '<br>
                    <strong>Método de Pago:</strong> ' . ($venta["metodo_pago"] ?? '') . '
                </td>
            </tr>
        </table><br><br>';

        $htmlHeader .= '<table class="table" cellpadding="5">
            <thead>
                <tr class="table-header">
                    <th style="width:40%;">Producto</th>
                    <th style="width:10%; text-align:center;">Cant</th>
                    <th style="width:20%; text-align:right;">Precio Unit.</th>
                    <th style="width:15%; text-align:center;">Impuesto</th>
                    <th style="width:15%; text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>';

        $listaProducto = json_decode($venta["productos"], true);
        foreach ($listaProducto as $value) {
            $respuestaProd = ControladorProductos::ctrMostrarProductos("id", $value["id"], "id");
            $nombreCorto = "Exento";
            $impRate = 0;
            if (isset($respuestaProd["tributo_id"]) && $respuestaProd["tributo_id"] != 0) {
                $tributo = ModeloFactus::mdlMostrarTributo($respuestaProd["tributo_id"]);
                if ($tributo) {
                    $impRate = $tributo["porcentaje_defecto"];
                    $nombreCorto = trim(preg_split('/[0-9]/', $tributo["nombre"])[0]);
                }
            }
            $htmlHeader .= '<tr>
                <td>' . $value["descripcion"] . '</td>
                <td style="text-align:center;">' . $value["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($respuestaProd["precio_venta"], 2) . '</td>
                <td style="text-align:center;">' . $nombreCorto . ' ' . $impRate . '%</td>
                <td style="text-align:right;">$' . number_format($value["total"], 2) . '</td>
            </tr>';
        }
        $htmlHeader .= '</tbody></table><br><br>';

        $pdf->writeHTML($htmlHeader, true, false, true, false, '');
        $startY = $pdf->GetY();

        // --- PARTE 2: COLUMNA IZQUIERDA (Notas, Observaciones) ---
        $htmlLeft = $style . '<div class="invoice">';

        // Retenciones
        $retencionesArr = !empty($venta["retenciones"]) ? json_decode($venta["retenciones"], true) : [];
        $totalRetencionesVenta = 0;
        if (!empty($retencionesArr)) {
            $htmlLeft .= '<div class="lead">Retenciones:</div><table class="table" cellpadding="3">';
            foreach ($retencionesArr as $ret) {
                $totalRetencionesVenta += $ret['monto'];
                $htmlLeft .= '<tr><td style="width:60%;"><strong>' . $ret['tipo'] . ' (' . $ret['porcentaje'] . '%):</strong></td><td style="text-align:right;">$' . number_format($ret['monto'], 2) . '</td></tr>';
            }
            $htmlLeft .= '<tr><td><strong>Total Retenido:</strong></td><td style="text-align:right; font-weight:bold;">$' . number_format($totalRetencionesVenta, 2) . '</td></tr></table><br>';
        }

        $htmlLeft .= '<div class="lead">Notas:</div><div class="well">' . ($venta["notas"] ?: 'Sin notas') . '</div>';
        if (!empty($venta["observacion"])) {
            $htmlLeft .= '<br><div class="lead">Observaciones:</div><div class="well">' . $venta["observacion"] . '</div>';
        }
        $htmlLeft .= '</div>';

        // Renderizamos columna izquierda
        $pdf->writeHTMLCell(110, 0, 10, $startY, $htmlLeft, 0, 1, false, true, 'L', true);

        // --- SECCIÓN QR Y CUFE (SOLO PARA FACTURA ELECTRÓNICA) ---
        if (!empty($venta["numero_factura"]) && (!empty($venta["qr_data"]) || !empty($venta["cufe"]))) {
            $yActual = $pdf->GetY() + 5;
            $pdf->SetY($yActual);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(233, 236, 239);

            // Título QR
            if (!empty($venta["qr_data"])) {
                $pdf->Cell(110, 6, ' Código de Validación DIAN (QR):', 'L', 1, 'L', true);
                $yQR = $pdf->GetY() + 2;
                $styleQR = array(
                    'border' => 0,
                    'vpadding' => 'auto',
                    'hpadding' => 'auto',
                    'fgcolor' => array(0, 0, 0),
                    'bgcolor' => false,
                    'module_width' => 1,
                    'module_height' => 1
                );
                $pdf->write2DBarcode(trim($venta["qr_data"]), 'QRCODE,H', 15, $yQR, 35, 35, $styleQR, 'N');
                
                // Enlace debajo del QR
                $pdf->SetY($yQR + 36);
                $pdf->SetFont('helvetica', '', 6.5);
                $pdf->SetTextColor(0, 0, 255); // Azul para el link
                
                $urlDian = trim($venta["qr_data"]);
                $htmlLink = '<a href="'.$urlDian.'" style="text-decoration:none; color:blue;">'.$urlDian.'</a>';
                
                // Usamos writeHTMLCell para que maneje mejor el wrap de texto largo
                $pdf->writeHTMLCell(110, 0, 10, '', $htmlLink, 0, 1, false, true, 'L', true);
                $pdf->SetTextColor(68, 68, 68); // Volver al gris oscuro

                // CUFE (Diseño igual a Nota de Crédito)
                if (!empty($cufeExtraido)) {
                    $pdf->Ln(4);
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->Cell(110, 5, ' CUFE:', 'L', 1, 'L', true);
                    $pdf->SetFont('helvetica', '', 7);
                    $pdf->MultiCell(110, 0, $cufeExtraido, 1, 'L', true, 1, 10, '', true);
                }
            }
        }

        // --- PARTE 3: COLUMNA DERECHA (Totales) ---
        // Totales recalculation
        $bruto = 0;
        $neta = 0;
        $imp = 0;
        $descGlob = $venta["valor_descuento"] ?? 0;
        $totalOrig = $venta["total"] + ($venta["monto_descuento"] ?? 0);
        foreach ($listaProducto as $p) {
            $tp = floatval($p["total"]);
            $ip = isset($p["impuesto"]) ? floatval($p["impuesto"]) : 19;
            $bb = $tp / (1 + ($ip / 100));
            $bruto += $bb;
            $di = ($venta["tipo_descuento"] == "porcentaje") ? ($tp * ($descGlob / 100)) : (($venta["tipo_descuento"] == "fijo" && $totalOrig > 0) ? ($descGlob * ($tp / $totalOrig)) : 0);
            $pd = $tp - $di;
            $bn = $pd / (1 + ($ip / 100));
            $neta += $bn;
            $imp += ($pd - $bn);
        }
        $totalVentaFinal = $neta + $imp;
        $vNeto = $totalVentaFinal - $totalRetencionesVenta;

        $htmlRight = $style . '<div class="invoice">';
        $htmlRight .= '<div class="lead">Totales</div>
                    <table class="total-table" cellpadding="6">
                        <tr><th>Subtotal:</th><td>$' . number_format($bruto, 2) . '</td></tr>';
        if (($venta["monto_descuento"] ?? 0) > 0) {
            $htmlRight .= '<tr><th>Descuento:</th><td>$' . number_format($bruto - $neta, 2) . '</td></tr>';
        }
        $htmlRight .= '<tr><th>Valor Bruto:</th><td>$' . number_format($neta, 2) . '</td></tr>
                        <tr><th>Impuesto:</th><td>$' . number_format($imp, 2) . '</td></tr>
                        <tr style="background-color:#eee;"><th>Total:</th><td style="font-weight:bold;">$' . number_format($totalVentaFinal, 2) . '</td></tr>
                        <tr style="background-color:#3c8dbc; color:white;"><th style="font-size:12px;">VALOR NETO:</th><td style="font-size:12px; font-weight:bold;">$' . number_format($vNeto, 2) . '</td></tr>
                    </table></div>';

        $pdf->writeHTMLCell(75, 0, 125, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        ob_end_clean();
        $filename = strtolower(str_replace(' ', '-', $etiquetaDocumento)) . '-' . $numeroDocumento . '.pdf';
        $pdf->Output($filename, 'I');
    }
}

if (isset($_GET["idVenta"])) {
    $imprimir = new imprimirDetalleOrden();
    $imprimir->idVenta = $_GET["idVenta"];
    $imprimir->traerImpresionDetalle();
}
