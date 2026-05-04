<?php

require_once __DIR__ . "/../controladores/ventas.controlador.php";
require_once __DIR__ . "/../modelos/ventas.modelo.php";
require_once __DIR__ . "/../controladores/clientes.controlador.php";
require_once __DIR__ . "/../modelos/clientes.modelo.php";
require_once __DIR__ . "/../controladores/usuarios.controlador.php";
require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../controladores/productos.controlador.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";
require_once __DIR__ . "/../controladores/configuracion.controlador.php";
require_once __DIR__ . "/../modelos/configuracion.modelo.php";
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/factus.controlador.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once __DIR__ . "/../controladores/correo.controlador.php";
require_once __DIR__ . "/../controladores/proveedores.controlador.php";
require_once __DIR__ . "/../modelos/proveedores.modelo.php";

class AjaxFacturacion
{
    public $idVenta;
    public $emailDestino;
    public $idNota;
    public $idDS;
    public $idNA;
    public $fechaInicial;
    public $fechaFinal;
    public $categoria;

    public function ajaxEnviarPDFCorreo()
    {
        $item = "id";
        $valor = $this->idVenta;
        $venta = ControladorVentas::ctrMostrarVentas($item, $valor);

        if (!$venta) {
            echo json_encode(["status" => "error", "mensaje" => "Venta no encontrada"]);
            return;
        }

        // 1. Generar el PDF usando la lógica de descargar-pdf-detalle.php
        // Pero capturándolo en un archivo en lugar de enviarlo al navegador.

        require_once(__DIR__ . '/../extensiones/tcpdf/tcpdf.php');

        // Incluimos la definición de la clase de impresión pero modificada para guardar en disco
        // Para evitar duplicar código, vamos a usar una técnica similar a la de descargar-pdf-detalle.php
        // pero inyectando el guardado.

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Factura Electrónica #' . $venta["codigo"]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // --- Datos para el PDF ---
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
        $cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();

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

        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';

        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = "../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:40px; vertical-align:middle; margin-bottom:5px;"> ';
            }
        }

        $html = $style . '<div class="invoice">';
        $html .= '<table style="width:100%; border-bottom: 2px solid #3c8dbc; padding-bottom:10px;">
            <tr>
                <td style="width:50%; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:bold; color:#444;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:50%; text-align:right; vertical-align:middle;">
                    <span style="font-size:16px; font-weight:bold; color:#3c8dbc;">FACTURA ELECTRÓNICA</span><br>
                    <span style="font-size:10px; color:#666;">Fecha: ' . $venta["fecha"] . '</span>
                </td>
            </tr>
        </table><br>';

        $html .= '<table class="table" cellpadding="6">
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
                    <strong>Factura Electrónica #' . ($venta["codigo"] ?? '') . '</strong><br><br>
                    <strong>Vendedor:</strong> ' . ($vendedor["nombre"] ?? '') . '<br>
                    <strong>Método de Pago:</strong> ' . ($venta["metodo_pago"] ?? '') . '
                </td>
            </tr>
        </table><br><br>';

        $html .= '<table class="table" cellpadding="5">
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
            $html .= '<tr>
                <td>' . $value["descripcion"] . '</td>
                <td style="text-align:center;">' . $value["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($respuestaProd["precio_venta"], 2) . '</td>
                <td style="text-align:center;">' . $nombreCorto . ' ' . $impRate . '%</td>
                <td style="text-align:right;">$' . number_format($value["total"], 2) . '</td>
            </tr>';
        }
        $html .= '</tbody></table><br><br>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $startY = $pdf->GetY();

        // --- PARTE 2: COLUMNA IZQUIERDA (Notas, Retenciones, QR, CUFE) ---
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

        // QR y CUFE
        $yDespuesDeNotas = $pdf->GetY() + 5;
        $pdf->SetY($yDespuesDeNotas);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(233, 236, 239);
        $pdf->Cell(110, 6, ' Código QR DIAN:', 'L', 1, 'L', true);

        $yQR = $pdf->GetY() + 2;
        if (!empty($venta["qr_data"])) {
            $styleQR = array('border' => 0, 'vpadding' => 'auto', 'hpadding' => 'auto', 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'module_width' => 1, 'module_height' => 1);
            $pdf->write2DBarcode(trim($venta["qr_data"]), 'QRCODE,H', 15, $yQR, 35, 35, $styleQR, 'N');

            $pdf->SetY($yQR + 36);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(119, 119, 119);
            $pdf->MultiCell(105, 0, trim($venta["qr_data"]), 0, 'L', false, 1, 12, null, true);

            $cufeDisp = $venta["cufe"];
            if (empty($cufeDisp) && !empty($venta["qr_data"])) {
                if (preg_match('/documentkey=([a-zA-Z0-9]+)/', $venta["qr_data"], $matches))
                    $cufeDisp = $matches[1];
            }
            if (!empty($cufeDisp)) {
                $pdf->Ln(8);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetTextColor(68, 68, 68);
                $pdf->Cell(105, 6, ' CUFE:', 'L', 1, 'L', true, '', 1);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->MultiCell(105, 0, $cufeDisp, 1, 'L', true, 1, 12, null, true);
            }
        }

        // --- PARTE 3: COLUMNA DERECHA (Totales detallados) ---
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

        $htmlTotal = $style . '<div class="invoice"><div class="lead">Totales</div>
                    <table class="total-table" cellpadding="6">
                        <tr><th>Subtotal:</th><td>$' . number_format($bruto, 2) . '</td></tr>';
        if (($venta["monto_descuento"] ?? 0) > 0) {
            $htmlTotal .= '<tr><th>Descuento:</th><td>$' . number_format($bruto - $neta, 2) . '</td></tr>';
        }
        $htmlTotal .= '<tr><th>Valor Bruto:</th><td>$' . number_format($neta, 2) . '</td></tr>
                        <tr><th>Impuesto:</th><td>$' . number_format($imp, 2) . '</td></tr>
                        <tr style="background-color:#eee;"><th>Total:</th><td style="font-weight:bold;">$' . number_format($totalVentaFinal, 2) . '</td></tr>
                        <tr style="background-color:#3c8dbc; color:white;"><th style="font-size:12px;">VALOR NETO:</th><td style="font-size:12px; font-weight:bold;">$' . number_format($vNeto, 2) . '</td></tr>
                    </table></div>';

        $pdf->writeHTMLCell(75, 0, 125, $startY, $htmlTotal, 0, 1, false, true, 'R', true);

        // --- Guardar temporalmente ---
        $tempDir = __DIR__ . '/../vistas/img/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $nombreArchivo = 'Factura-' . $venta["codigo"] . '-' . date('YmdHis') . '.pdf';
        $rutaCompleta = $tempDir . $nombreArchivo;

        $pdf->Output($rutaCompleta, 'F');

        // 2. Enviar Correo
        $asunto = "Factura Electronica #" . $venta["codigo"] . " - " . $nombreEmpresa;
        $mensaje = "<h3>Estimado(a) " . ($cliente["nombre"] ?? 'Cliente') . "</h3>";
        $mensaje .= "<p>Adjunto encontrará su factura electrónica #" . $venta["codigo"] . " en formato PDF.</p>";
        $mensaje .= "<p>Gracias por su preferencia.</p>";
        $mensaje .= "<br><hr><small>Este es un correo automático, por favor no responda a este mensaje.</small>";

        $enviar = ControladorCorreo::ctrEnviarCorreo($this->emailDestino, $asunto, $mensaje, $rutaCompleta);

        // 3. Limpiar temporal
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }

        if ($enviar == "ok") {
            echo json_encode(["status" => "success", "mensaje" => "Correo enviado correctamente a " . $this->emailDestino]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Error al enviar el correo: " . $enviar]);
        }
    }

    public function ajaxEnviarPDFCNCorreo()
    {
        $idNota = $this->idNota;
        $notaCredito = ModeloFactus::mdlMostrarNotasCredito("notas_credito", "id", $idNota);

        if (!$notaCredito) {
            echo json_encode(["status" => "error", "mensaje" => "Nota de crédito no encontrada"]);
            return;
        }

        require_once __DIR__ . "/../modelos/ventas.modelo.php";
        $venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $notaCredito["id_venta_original"]);

        $clienteId = !empty($notaCredito["id_cliente"]) ? $notaCredito["id_cliente"] : ($venta["id_cliente"] ?? null);
        $cliente = $clienteId ? ControladorClientes::ctrMostrarClientes("id", $clienteId) : [];
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $notaCredito["id_usuario"]);
        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();

        $impuestoMap = [];
        if ($venta && !empty($venta["productos"])) {
            $productosVenta = json_decode($venta["productos"], true);
            if (is_array($productosVenta)) {
                foreach ($productosVenta as $pv) {
                    $impuestoMap[$pv["id"]] = isset($pv["impuesto"]) ? floatval($pv["impuesto"]) : 0;
                }
            }
        }

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

        require_once(__DIR__ . '/../extensiones/tcpdf/tcpdf.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Nota Credito #' . $notaCredito["numero_nota_credito"]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

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

        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';

        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = __DIR__ . "/../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:40px; vertical-align:middle; margin-bottom:5px;"> ';
            }
        }

        $html = $style . '<div class="invoice">';
        $html .= '<table style="width:100%; border-bottom: 2px solid #3c8dbc; padding-bottom:10px;">
            <tr>
                <td style="width:50%; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:bold; color:#444;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:50%; text-align:right; vertical-align:middle;">
                    <span style="font-size:16px; font-weight:bold; color:#3c8dbc;">NOTA CRÉDITO</span><br>
                    <span style="font-size:10px; color:#666;">Fecha Emisión: ' . ($notaCredito["fecha_envio_dian"] ?? date('Y-m-d')) . '</span>
                </td>
            </tr>
        </table><br>';

        $html .= '<table class="table" cellpadding="6">
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

        $html .= '<table class="table" cellpadding="5">
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

            $html .= '<tr>
                <td>' . $prod["descripcion"] . '</td>
                <td style="text-align:center;">' . $prod["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($precioUnitarioConImpuesto, 2) . '</td>
                <td style="text-align:right;">$' . number_format($subtotalItem, 2) . '</td>
            </tr>';
        }
        $html .= '</tbody></table><br><br>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $startY = $pdf->GetY();

        $htmlLeft = $style . '<div class="invoice">';
        if (!empty($notaCredito["observacion"])) {
            $htmlLeft .= '<div class="lead">Observaciones:</div><div class="well">' . nl2br($notaCredito["observacion"]) . '</div><br>';
        }
        $htmlLeft .= '</div>';
        $pdf->writeHTMLCell(110, 0, 10, $startY, $htmlLeft, 0, 1, false, true, 'L', true);

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
            $pdf->MultiCell(105, 0, trim($notaCredito["qr_data_nc"]), 0, 'L', false, 1, 12, null, true);

            $pdf->Ln(5);
            $pdf->SetTextColor(68, 68, 68);

            if (!empty($cufeFactura)) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(105, 5, ' CUFE (Factura):', 'L', 1, 'L', true);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->MultiCell(105, 0, $cufeFactura, 1, 'L', true, 1, 12, null, true);
                $pdf->Ln(2);
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(105, 5, ' CUDE (Nota Crédito):', 'L', 1, 'L', true);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(105, 0, $notaCredito["cufe_nc"], 1, 'L', true, 1, 12, null, true);
        }

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

        $pdf->writeHTMLCell(75, 0, 125, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        $tempDir = __DIR__ . '/../vistas/img/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $nombreArchivo = 'NotaCredito-' . $notaCredito["numero_nota_credito"] . '-' . date('YmdHis') . '.pdf';
        $rutaCompleta = $tempDir . $nombreArchivo;

        $pdf->Output($rutaCompleta, 'F');

        $asunto = "Nota de Credito #" . $notaCredito["numero_nota_credito"] . " - " . $nombreEmpresa;
        $mensaje = "<h3>Estimado(a) " . ($cliente["nombre"] ?? 'Cliente') . "</h3>";
        $mensaje .= "<p>Adjunto encontrará su nota de crédito #" . $notaCredito["numero_nota_credito"] . " en formato PDF.</p>";
        $mensaje .= "<p>Gracias por su preferencia.</p>";
        $mensaje .= "<br><hr><small>Este es un correo automático, por favor no responda a este mensaje.</small>";

        $enviar = ControladorCorreo::ctrEnviarCorreo($this->emailDestino, $asunto, $mensaje, $rutaCompleta);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }

        if ($enviar == "ok") {
            echo json_encode(["status" => "success", "mensaje" => "Nota de crédito enviada correctamente a " . $this->emailDestino]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Error al enviar el correo: " . $enviar]);
        }
    }

    public function ajaxEnviarPDFDSCorreo()
    {
        $idDS = $this->idDS;
        $documentoSoporte = ControladorFactus::ctrMostrarDocumentosSoporte("id", $idDS);

        if (!$documentoSoporte) {
            echo json_encode(["status" => "error", "mensaje" => "Documento soporte no encontrado"]);
            return;
        }

        $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $documentoSoporte["id_proveedor"]);
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $documentoSoporte["id_usuario"]);
        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();
        $listaProducto = json_decode($documentoSoporte["productos"], true);

        require_once __DIR__ . '/../extensiones/tcpdf/tcpdf.php';
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Documento Soporte #' . $documentoSoporte["numero_ds"]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

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

        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';

        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = __DIR__ . "/../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:40px; vertical-align:middle; margin-bottom:5px;"> ';
            }
        }

        $htmlHeader = $style . '<div class="invoice">';
        $htmlHeader .= '<table style="width:100%; border-bottom: 2px solid #3c8dbc; padding-bottom:10px;">
            <tr>
                <td style="width:50%; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:bold; color:#444;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:50%; text-align:right; vertical-align:middle;">
                    <span style="font-size:16px; font-weight:bold; color:#3c8dbc;">DOCUMENTO SOPORTE</span><br>
                    <span style="font-size:10px; color:#666;">Fecha Emisión: ' . $documentoSoporte["fecha_emision"] . '</span>
                </td>
            </tr>
        </table><br>';

        $municipioProveedor = "No definido";
        if (!empty($proveedor["municipio_id"])) {
            $mun = ModeloFactus::mdlMostrarMunicipioPorId($proveedor["municipio_id"]);
            $municipioProveedor = $mun ? $mun["nombre"] : $proveedor["municipio_id"];
        }

        $htmlHeader .= '<table class="table" cellpadding="6">
            <tr>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Comprador (Emisor)</span><br><br>
                    <strong>' . $labelNombreEmisor . ':</strong> ' . $nombreEmpresa . '<br>
                    <strong>NIT:</strong> ' . $nitEmisor . '<br>
                    <strong>Dirección:</strong> ' . $direccionEmisor . '<br>
                    <strong>Teléfono:</strong> ' . $telefonoEmisor . '<br>
                    <strong>Email:</strong> ' . $emailEmisor . '
                </td>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Vendedor (Proveedor)</span><br><br>
                    <strong>Nombre:</strong> ' . ($proveedor["nombre"] ?? 'Proveedor') . '<br>
                    <strong>Documento:</strong> ' . ($proveedor["documento"] ?? '') . '<br>
                    <strong>Dirección:</strong> ' . ($proveedor["direccion"] ?? '') . '<br>
                    <strong>Municipio:</strong> ' . $municipioProveedor . '<br>
                    <strong>Teléfono:</strong> ' . ($proveedor["celular"] ?? '') . '<br>
                    <strong>Email:</strong> ' . ($proveedor["correo"] ?? '') . '
                </td>
                <td style="width:34%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Detalles DS</span><br><br>
                    <strong>Documento Soporte #' . $documentoSoporte["numero_ds"] . '</strong><br>
                    <strong>Método de Pago:</strong> ' . ($documentoSoporte["metodo_pago"] ?? 'N/A') . '<br>
                    <strong>Estado:</strong> ' . ucfirst($documentoSoporte["estado_dian"]) . '<br>
                    <strong>Vendedor:</strong> ' . ($vendedor["nombre"] ?? 'N/A') . '
                </td>
            </tr>
        </table><br><br>';

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

        $totalSubtotal = 0;
        foreach ($listaProducto as $prod) {
            $precio = floatval($prod["precio"]);
            $cantidad = floatval($prod["cantidad"]);
            $subtotal = $precio * $cantidad;
            $totalSubtotal += $subtotal;

            $htmlHeader .= '<tr>
                <td>' . $prod["descripcion"] . '</td>
                <td style="text-align:center;">' . $prod["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($precio, 2) . '</td>
                <td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>
            </tr>';
        }
        $htmlHeader .= '</tbody></table><br><br>';

        $pdf->writeHTML($htmlHeader, true, false, true, false, '');
        $startY = $pdf->GetY();

        $htmlLeft = $style . '<div class="invoice">';
        $retencionesArr = !empty($documentoSoporte["retenciones"]) ? json_decode($documentoSoporte["retenciones"], true) : [];
        $totalRetenciones = 0;
        if (!empty($retencionesArr)) {
            $htmlLeft .= '<div class="lead">Retenciones:</div><table class="table" cellpadding="3">';
            foreach ($retencionesArr as $ret) {
                $totalRetenciones += floatval($ret['monto']);
                $htmlLeft .= '<tr><td style="width:65%;"><strong>' . $ret['tipo'] . ' (' . $ret['porcentaje'] . '%):</strong></td><td style="text-align:right; width:35%;">$' . number_format($ret['monto'], 2) . '</td></tr>';
            }
            $htmlLeft .= '<tr><td style="width:65%;"><strong>Total Retenido:</strong></td><td style="text-align:right; width:35%; font-weight:bold;">$' . number_format($totalRetenciones, 2) . '</td></tr></table><br>';
        }

        if (!empty($documentoSoporte["mensaje_dian"])) {
            $htmlLeft .= '<div class="lead">Mensaje DIAN:</div><div class="well">' . nl2br($documentoSoporte["mensaje_dian"]) . '</div><br>';
        }

        $htmlLeft .= '</div>';
        $pdf->writeHTMLCell(110, 0, 10, $startY, $htmlLeft, 0, 1, false, true, 'L', true);

        $yDespuesDeNotas = $pdf->GetY() + 5;
        $pdf->SetY($yDespuesDeNotas);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(233, 236, 239);

        if (!empty($documentoSoporte["qr_data"])) {
            $pdf->Cell(110, 6, ' Código QR DIAN:', 'L', 1, 'L', true);
            $yQR = $pdf->GetY() + 2;
            $styleQR = array('border' => 0, 'vpadding' => 'auto', 'hpadding' => 'auto', 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'module_width' => 1, 'module_height' => 1);
            $pdf->write2DBarcode(trim($documentoSoporte["qr_data"]), 'QRCODE,H', 15, $yQR, 35, 35, $styleQR, 'N');

            $pdf->SetY($yQR + 36);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(119, 119, 119);
            $pdf->MultiCell(105, 0, trim($documentoSoporte["qr_data"]), 0, 'L', false, 1, 12, null, true);

            $pdf->Ln(5);
            $pdf->SetTextColor(68, 68, 68);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(105, 5, ' CUDS (Documento Soporte):', 'L', 1, 'L', true);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(105, 0, $documentoSoporte["cuds"], 1, 'L', true, 1, 12, null, true);
        }

        $tipoDescuento = $documentoSoporte["tipo_descuento"] ?? "porcentaje";
        $montoDescuento = floatval($documentoSoporte["monto_descuento"] ?? 0);
        $totalPagar = $totalSubtotal - $montoDescuento - $totalRetenciones;

        $htmlRight = $style . '<div class="invoice">';
        $htmlRight .= '<div class="lead">Resumen Financiero</div>
                    <table class="total-table" cellpadding="6">
                        <tr><th>Subtotal:</th><td>$' . number_format($totalSubtotal, 2) . '</td></tr>';

        if ($montoDescuento > 0) {
            $htmlRight .= '<tr><th>Descuento:</th><td>-$' . number_format($montoDescuento, 2) . '</td></tr>';
        }

        $htmlRight .= '<tr><th>Retenciones:</th><td>-$' . number_format($totalRetenciones, 2) . '</td></tr>
                        <tr style="background-color:#eee; font-size:12px;">
                            <th style="font-weight:bold;">TOTAL A PAGAR:</th>
                            <td style="font-weight:bold;">$' . number_format($totalPagar, 2) . '</td>
                        </tr>
                    </table></div>';

        $pdf->writeHTMLCell(75, 0, 125, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        $tempDir = __DIR__ . '/../vistas/img/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $nombreArchivo = 'DocumentoSoporte-' . $documentoSoporte["numero_ds"] . '-' . date('YmdHis') . '.pdf';
        $rutaCompleta = $tempDir . $nombreArchivo;

        $pdf->Output($rutaCompleta, 'F');

        $asunto = "Documento Soporte #" . $documentoSoporte["numero_ds"] . " - " . $nombreEmpresa;
        $mensaje = "<h3>Estimado(a) " . ($proveedor["nombre"] ?? 'Proveedor') . "</h3>";
        $mensaje .= "<p>Adjunto encontrará su documento soporte #" . $documentoSoporte["numero_ds"] . " en formato PDF.</p>";
        $mensaje .= "<p>Gracias por su atención.</p>";
        $mensaje .= "<br><hr><small>Este es un correo automático, por favor no responda a este mensaje.</small>";

        $enviar = ControladorCorreo::ctrEnviarCorreo($this->emailDestino, $asunto, $mensaje, $rutaCompleta);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }

        if ($enviar == "ok") {
            echo json_encode(["status" => "success", "mensaje" => "Documento soporte enviado correctamente a " . $this->emailDestino]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Error al enviar el correo: " . $enviar]);
        }
    }

    public function ajaxEnviarPDFNACorreo()
    {
        $idNA = $this->idNA;
        $nota = ControladorFactus::ctrMostrarNotasAjusteDS("id", $idNA);

        if (!$nota) {
            echo json_encode(["status" => "error", "mensaje" => "Nota de ajuste no encontrada"]);
            return;
        }

        $documentoSoporte = ControladorFactus::ctrMostrarDocumentosSoporte("id", $nota["id_ds_original"]);
        $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $nota["id_proveedor"]);
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $nota["id_usuario"]);
        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();
        $listaProducto = json_decode($nota["productos"], true);

        require_once __DIR__ . '/../extensiones/tcpdf/tcpdf.php';
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Nota de Ajuste DS #' . $nota["numero_nota_ajuste"]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

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

        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';

        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = __DIR__ . "/../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:40px; vertical-align:middle; margin-bottom:5px;"> ';
            }
        }

        $htmlHeader = $style . '<div class="invoice">';
        $htmlHeader .= '<table style="width:100%; border-bottom: 2px solid #3c8dbc; padding-bottom:10px;">
            <tr>
                <td style="width:50%; vertical-align:middle;">
                    <span style="font-size:18px; font-weight:bold; color:#444;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:50%; text-align:right; vertical-align:middle;">
                    <span style="font-size:16px; font-weight:bold; color:#3c8dbc;">NOTA DE AJUSTE DS</span><br>
                    <span style="font-size:10px; color:#666;">Fecha Emisión: ' . $nota["fecha_envio_dian"] . '</span>
                </td>
            </tr>
        </table><br>';

        $municipioProveedor = "No definido";
        if (!empty($proveedor["municipio_id"])) {
            $mun = ModeloFactus::mdlMostrarMunicipioPorId($proveedor["municipio_id"]);
            $municipioProveedor = $mun ? $mun["nombre"] : $proveedor["municipio_id"];
        }

        $conceptos = [
            "1" => "Devolución parcial de los bienes y/o no aceptación parcial del servicio",
            "2" => "Anulación de documento soporte",
            "3" => "Rebaja o descuento parcial o total",
            "4" => "Ajuste de precio",
            "5" => "Otros"
        ];
        $textoConcepto = $conceptos[$nota["tipo_nota"]] ?? "No definido";

        $htmlHeader .= '<table class="table" cellpadding="6">
            <tr>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Comprador (Emisor)</span><br><br>
                    <strong>' . $labelNombreEmisor . ':</strong> ' . $nombreEmpresa . '<br>
                    <strong>NIT:</strong> ' . $nitEmisor . '<br>
                    <strong>Dirección:</strong> ' . $direccionEmisor . '<br>
                    <strong>Teléfono:</strong> ' . $telefonoEmisor . '<br>
                    <strong>Email:</strong> ' . $emailEmisor . '
                </td>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Vendedor (Proveedor)</span><br><br>
                    <strong>Nombre:</strong> ' . ($proveedor["nombre"] ?? 'Proveedor') . '<br>
                    <strong>Documento:</strong> ' . ($proveedor["documento"] ?? '') . '<br>
                    <strong>Dirección:</strong> ' . ($proveedor["direccion"] ?? '') . '<br>
                    <strong>Municipio:</strong> ' . $municipioProveedor . '<br>
                    <strong>Teléfono:</strong> ' . ($proveedor["celular"] ?? '') . '<br>
                    <strong>Email:</strong> ' . ($proveedor["correo"] ?? '') . '
                </td>
                <td style="width:34%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Detalles Nota</span><br><br>
                    <strong>Nota Ajuste #' . $nota["numero_nota_ajuste"] . '</strong><br>
                    <strong>Doc. Original:</strong> ' . $nota["numero_ds_original"] . '<br>
                    <strong>Concepto:</strong> ' . $textoConcepto . '<br>
                    <strong>Estado:</strong> ' . ucfirst($nota["estado_dian"]) . '
                </td>
            </tr>
        </table><br><br>';

        $htmlHeader .= '<table class="table" cellpadding="5">
            <thead>
                <tr class="table-header">
                    <th style="width:45%;">Producto</th>
                    <th style="width:15%; text-align:center;">Cant</th>
                    <th style="width:20%; text-align:right;">Precio Unit.</th>
                    <th style="width:20%; text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($listaProducto as $prod) {
            $precio = floatval($prod["precio"]);
            $cantidad = floatval($prod["cantidad"]);
            $totalProd = $prod["total"] ?? ($precio * $cantidad);

            $htmlHeader .= '<tr>
                <td>' . $prod["descripcion"] . '</td>
                <td style="text-align:center;">' . $prod["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($precio, 2) . '</td>
                <td style="text-align:right;">$' . number_format($totalProd, 2) . '</td>
            </tr>';
        }
        $htmlHeader .= '</tbody></table><br><br>';

        $pdf->writeHTML($htmlHeader, true, false, true, false, '');
        $startY = $pdf->GetY();

        $htmlLeft = $style . '<div class="invoice">';
        $htmlLeft .= '<div class="lead">Observación / Motivo:</div><div class="well">' . nl2br($nota["motivo"] ?: ($nota["observacion"] ?: "Sin observaciones adicionales.")) . '</div><br>';
        $htmlLeft .= '</div>';
        $pdf->writeHTMLCell(110, 0, 10, $startY, $htmlLeft, 0, 1, false, true, 'L', true);

        $yDespuesDeNotas = $pdf->GetY() + 5;
        $pdf->SetY($yDespuesDeNotas);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(233, 236, 239);

        if (!empty($nota["qr_data"])) {
            $pdf->Cell(110, 6, ' Código QR DIAN:', 'L', 1, 'L', true);
            $yQR = $pdf->GetY() + 2;
            $styleQR = array('border' => 0, 'vpadding' => 'auto', 'hpadding' => 'auto', 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'module_width' => 1, 'module_height' => 1);
            $pdf->write2DBarcode(trim($nota["qr_data"]), 'QRCODE,H', 15, $yQR, 35, 35, $styleQR, 'N');

            $pdf->SetY($yQR + 36);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(119, 119, 119);
            $pdf->MultiCell(105, 0, trim($nota["qr_data"]), 0, 'L', false, 1, 12, null, true);

            $pdf->Ln(5);
            $pdf->SetTextColor(68, 68, 68);
            if (!empty($documentoSoporte["cuds"])) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(105, 5, ' CUDS (Original):', 'L', 1, 'L', true);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->MultiCell(105, 0, $documentoSoporte["cuds"], 1, 'L', true, 1, 12, null, true);
                $pdf->Ln(2);
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(105, 5, ' CUDS (Ajuste):', 'L', 1, 'L', true);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(105, 0, $nota["cuds_ajuste"], 1, 'L', true, 1, 12, null, true);
        }

        $htmlRight = $style . '<div class="invoice">';
        $htmlRight .= '<div class="lead">Resumen Financiero</div>
                    <table class="total-table" cellpadding="6">
                        <tr style="background-color:#eee; font-size:12px;">
                            <th style="font-weight:bold;">TOTAL AJUSTADO:</th>
                            <td style="font-weight:bold;">$' . number_format((float) ($nota["monto_total"] ?? 0), 2) . '</td>
                        </tr>
                    </table></div>';

        $pdf->writeHTMLCell(75, 0, 125, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        $tempDir = __DIR__ . '/../vistas/img/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $nombreArchivo = 'NotaAjusteDS-' . $nota["numero_nota_ajuste"] . '-' . date('YmdHis') . '.pdf';
        $rutaCompleta = $tempDir . $nombreArchivo;

        $pdf->Output($rutaCompleta, 'F');

        $asunto = "Nota de Ajuste DS #" . $nota["numero_nota_ajuste"] . " - " . $nombreEmpresa;
        $mensaje = "<h3>Estimado(a) " . ($proveedor["nombre"] ?? 'Proveedor') . "</h3>";
        $mensaje .= "<p>Adjunto encontrará su nota de ajuste del documento soporte #" . $nota["numero_nota_ajuste"] . " en formato PDF.</p>";
        $mensaje .= "<p>Gracias por su atención.</p>";
        $mensaje .= "<br><hr><small>Este es un correo automático, por favor no responda a este mensaje.</small>";

        $enviar = ControladorCorreo::ctrEnviarCorreo($this->emailDestino, $asunto, $mensaje, $rutaCompleta);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }

        if ($enviar == "ok") {
            echo json_encode(["status" => "success", "mensaje" => "Nota de ajuste enviada correctamente a " . $this->emailDestino]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Error al enviar el correo: " . $enviar]);
        }
    }

    /*=============================================
    OBTENER KPIs PARA REPORTES
    =============================================*/
    public $tercero;
    public $idUsuario;

    public function ajaxObtenerKPIsReporte()
    {
        $respuesta = ControladorFactus::ctrObtenerKPIsReporte($this->fechaInicial, $this->fechaFinal, $this->categoria, $this->tercero, $this->idUsuario);
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER DATOS PARA GRÁFICO
    =============================================*/
    public function ajaxObtenerVentasGrafico()
    {
        $respuesta = ControladorFactus::ctrObtenerVentasGrafico($this->fechaInicial, $this->fechaFinal, $this->categoria, $this->tercero, $this->idUsuario);
        echo json_encode($respuesta);
    }

    /*=============================================
    MOSTRAR REPORTE DETALLADO
    =============================================*/
    public function ajaxMostrarReporteDetallado()
    {
        $respuesta = ControladorFactus::ctrMostrarReporteDetallado($this->fechaInicial, $this->fechaFinal, $this->categoria, $this->tercero, $this->idUsuario);

        $datos = [];

        foreach ($respuesta as $key => $valor) {

            /*=============================================
              ACCIONES
              =============================================*/
            $botones = "<div class='btn-group'>";

            if ($valor["tipo"] == "Factura") {
                $rutaDocs = "index.php?ruta=detalle-factura&idVenta=" . $valor["id_doc"];
                $btnClass = "btn-info";
            } else if ($valor["tipo"] == "Nota Crédito") {
                $rutaDocs = "index.php?ruta=ver-nota-credito&idNota=" . $valor["id_doc"];
                $btnClass = "btn-warning";
            } else if ($valor["tipo"] == "Doc. Soporte") {
                $rutaDocs = "index.php?ruta=ver-documento-soporte&idDS=" . $valor["id_doc"];
                $btnClass = "btn-success";
            } else if ($valor["tipo"] == "Nota Ajuste DS") {
                $rutaDocs = "index.php?ruta=ver-nota-ajuste-ds&idNota=" . $valor["id_doc"];
                $btnClass = "btn-danger";
            } else {
                $rutaDocs = "#";
                $btnClass = "btn-default";
            }

            $botones .= "<a href='" . $rutaDocs . "' class='btn " . $btnClass . " btn-xs' title='Ver detalle del documento'><i class='fa fa-eye'></i></a>";

            $botones .= "</div>";

            $datos[] = [
                ($key + 1),
                $valor["tipo"],
                $valor["numero"],
                $valor["tercero"],
                $valor["vendedor"],
                $valor["fecha"],
                "$" . number_format((float) $valor["monto"], 2),
                $valor["estado"],
                $botones
            ];
        }

        echo json_encode(["data" => $datos]);
    }
}

if (isset($_POST["idVenta"])) {
    $enviar = new AjaxFacturacion();
    $enviar->idVenta = $_POST["idVenta"];
    $enviar->emailDestino = $_POST["emailDestino"];
    $enviar->ajaxEnviarPDFCorreo();
}

if (isset($_POST["idNota"])) {
    $enviar = new AjaxFacturacion();
    $enviar->idNota = $_POST["idNota"];
    $enviar->emailDestino = $_POST["emailDestino"];
    $enviar->ajaxEnviarPDFCNCorreo();
}

if (isset($_POST["idDS"])) {
    $enviar = new AjaxFacturacion();
    $enviar->idDS = $_POST["idDS"];
    $enviar->emailDestino = $_POST["emailDestino"];
    $enviar->ajaxEnviarPDFDSCorreo();
}

if (isset($_POST["idNA"])) {
    $enviar = new AjaxFacturacion();
    $enviar->idNA = $_POST["idNA"];
    $enviar->emailDestino = $_POST["emailDestino"];
    $enviar->ajaxEnviarPDFNACorreo();
}

/*=============================================
OBTENER KPIs PARA REPORTES
=============================================*/

if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerKPIsReporte") {
    $reporte = new AjaxFacturacion();
    $reporte->fechaInicial = $_POST["fechaInicial"];
    $reporte->fechaFinal = $_POST["fechaFinal"];
    $reporte->categoria = $_POST["categoria"] ?? "todos";
    $reporte->tercero = $_POST["tercero"] ?? "todos";
    $reporte->idUsuario = $_POST["idUsuario"] ?? "todos";
    $reporte->ajaxObtenerKPIsReporte();
}

if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerVentasGrafico") {
    $reporte = new AjaxFacturacion();
    $reporte->fechaInicial = $_POST["fechaInicial"];
    $reporte->fechaFinal = $_POST["fechaFinal"];
    $reporte->categoria = $_POST["categoria"] ?? "todos";
    $reporte->tercero = $_POST["tercero"] ?? "todos";
    $reporte->idUsuario = $_POST["idUsuario"] ?? "todos";
    $reporte->ajaxObtenerVentasGrafico();
}

if (isset($_POST["accion"]) && $_POST["accion"] == "mostrarReporteDetallado") {
    $reporte = new AjaxFacturacion();
    $reporte->fechaInicial = $_POST["fechaInicial"];
    $reporte->fechaFinal = $_POST["fechaFinal"];
    $reporte->categoria = $_POST["categoria"] ?? "todos";
    $reporte->tercero = $_POST["tercero"] ?? "todos";
    $reporte->idUsuario = $_POST["idUsuario"] ?? "todos";
    $reporte->ajaxMostrarReporteDetallado();
}
