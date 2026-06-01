<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/proveedores.controlador.php";
require_once "../../../modelos/proveedores.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/configuracion.controlador.php";
require_once "../../../modelos/configuracion.modelo.php";

require_once "../../../controladores/factus.controlador.php";
require_once "../../../modelos/factus.modelo.php";

class imprimirNotaAjusteDS
{
    public $idNota;

    public function traerImpresionDetalle()
    {
        $idNota = $this->idNota;
        
        // Obtener la Nota de Ajuste
        $nota = ControladorFactus::ctrMostrarNotasAjusteDS("id", $idNota);

        if (!$nota) {
            die("Nota de ajuste no encontrada.");
        }

        // Obtener el Documento Soporte original
        $documentoSoporteOriginal = ControladorFactus::ctrMostrarDocumentosSoporte("id", $nota["id_ds_original"]);

        // Datos del Proveedor y Usuario
        $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $nota["id_proveedor"]);
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $nota["id_usuario"]);
        
        // Configuraciones
        $configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();

        // Productos de la Nota de Ajuste
        $listaProducto = json_decode($nota["productos"], true);

        // REQUERIMOS LA CLASE TCPDF
        require_once('tcpdf_include.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Nota de Ajuste DS #' . $nota["numero_nota_ajuste"]);

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

        $municipioEmisor = '';
        if (isset($configFactus['municipio_id']) && !empty($configFactus['municipio_id'])) {
            $muns = ModeloFactus::mdlObtenerMunicipios();
            foreach ($muns as $mun) {
                if ($mun['id_factus'] == $configFactus['municipio_id']) {
                    $municipioEmisor = $mun['nombre'] . ' - ' . $mun['departamento'];
                    break;
                }
            }
        }

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

        // --- CONCEPTOS AJUSTE ---
        $conceptos = [
            "1" => "Devolución parcial de los bienes y/o no aceptación parcial del servicio",
            "2" => "Anulación de documento soporte",
            "3" => "Rebaja o descuento parcial o total",
            "4" => "Ajuste de precio",
            "5" => "Otros"
        ];
        $textoNota = $conceptos[$nota["tipo_nota"]] ?? "No definido";

        $htmlHeader .= '<table class="table" cellpadding="6">
            <tr>
                <td style="width:33%; background-color:#f8f9fa; border-left:4px solid #3c8dbc;">
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Comprador (Emisor)</span><br><br>
                    <strong>' . $labelNombreEmisor . ':</strong> ' . $nombreEmpresa . '<br>
                    <strong>NIT:</strong> ' . $nitEmisor . '<br>
                    <strong>Dirección:</strong> ' . $direccionEmisor . '<br>' .
                    (!empty($municipioEmisor) ? '                    <strong>Municipio:</strong> ' . $municipioEmisor . '<br>' : '') . '
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
                    <span style="font-weight:bold; font-size:11px; border-bottom:1px solid #ddd;">Detalles Ajuste</span><br><br>
                    <strong>Nota Ajuste #' . $nota["numero_nota_ajuste"] . '</strong><br>
                    <strong>Referencia DS:</strong> ' . $nota["numero_ds_original"] . '<br>
                    <strong>Concepto:</strong> ' . $textoNota . '<br>
                    <strong>Vendedor:</strong> ' . ($vendedor["nombre"] ?? 'N/A') . '
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

        $totalSubtotal = 0;
        foreach ($listaProducto as $prod) {
            $precio = floatval($prod["precio"] ?? 0);
            $cantidad = floatval($prod["cantidad"] ?? 0);
            $subtotal = floatval($prod["total"] ?? ($precio * $cantidad));
            $totalSubtotal += $subtotal;

            $htmlHeader .= '<tr>
                <td>' . ($prod["descripcion"] ?? 'SIN DESCRIPCIÓN') . '</td>
                <td style="text-align:center;">' . $prod["cantidad"] . '</td>
                <td style="text-align:right;">$' . number_format($precio, 2) . '</td>
                <td style="text-align:right;">$' . number_format($subtotal, 2) . '</td>
            </tr>';
        }
        $htmlHeader .= '</tbody></table><br><br>';

        $pdf->writeHTML($htmlHeader, true, false, true, false, '');
        $startY = $pdf->GetY();

        // --- COLUMNA IZQUIERDA (QR / CUDS / Obs) ---
        $htmlLeft = $style . '<div class="invoice">';

        // Retenciones (si existen en la nota)
        $retencionesArr = !empty($nota["retenciones"]) ? json_decode($nota["retenciones"], true) : [];
        $totalRetenciones = 0;
        if (!empty($retencionesArr)) {
            $htmlLeft .= '<div class="lead">Retenciones:</div><table class="table" cellpadding="3">';
            foreach ($retencionesArr as $ret) {
                $totalRetenciones += floatval($ret['monto']);
                $htmlLeft .= '<tr><td style="width:65%;"><strong>' . $ret['tipo'] . ' (' . $ret['porcentaje'] . '%):</strong></td><td style="text-align:right; width:35%;">$' . number_format($ret['monto'], 2) . '</td></tr>';
            }
            $htmlLeft .= '<tr><td style="width:65%;"><strong>Total Retenido:</strong></td><td style="text-align:right; width:35%; font-weight:bold;">$' . number_format($totalRetenciones, 2) . '</td></tr></table><br>';
        }

        if (!empty($nota["mensaje_dian"])) {
            $htmlLeft .= '<div class="lead">Mensaje DIAN:</div><div class="well">' . nl2br($nota["mensaje_dian"]) . '</div><br>';
        }

        if (!empty($nota["observacion"])) {
            $htmlLeft .= '<div class="lead">Observaciones:</div><div class="well">' . nl2br($nota["observacion"]) . '</div><br>';
        }

        $htmlLeft .= '</div>';
        $pdf->writeHTMLCell(110, '', 10, $startY, $htmlLeft, 0, 1, false, true, 'L', true);

        // QR e Identificadores (Basado en Nota Ajuste)
        $yDespuesDeNotas = $pdf->GetY() + 5;
        $pdf->SetY($yDespuesDeNotas);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(233, 236, 239);

        if (!empty($nota["qr_data"])) {
            $pdf->Cell(110, 6, ' Código de Validación DIAN (QR):', 'L', 1, 'L', true);
            $yQR = $pdf->GetY() + 2;
            $styleQR = array('border' => 0, 'vpadding' => 'auto', 'hpadding' => 'auto', 'fgcolor' => array(0, 0, 0), 'bgcolor' => false, 'module_width' => 1, 'module_height' => 1);
            $pdf->write2DBarcode(trim($nota["qr_data"]), 'QRCODE,H', 15, $yQR, 35, 35, $styleQR, 'N');

            // Enlace debajo del QR
            $pdf->SetY($yQR + 36);
            $pdf->SetFont('helvetica', '', 6.5);
            $pdf->SetTextColor(0, 0, 255); // Azul para el link
            
            $urlDian = trim($nota["qr_data"]);
            $htmlLink = '<a href="'.$urlDian.'" style="text-decoration:none; color:blue;">'.$urlDian.'</a>';
            
            // Usamos writeHTMLCell para que maneje mejor el wrap de texto largo
            $pdf->writeHTMLCell(110, 0, 10, '', $htmlLink, 0, 1, false, true, 'L', true);
            $pdf->SetTextColor(68, 68, 68); // Volver al gris oscuro

            // CUDS AJUSTE
            $pdf->Ln(5);
            $pdf->SetTextColor(68, 68, 68);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(105, 5, ' CUDS (Nota de Ajuste):', 'L', 1, 'L', true);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(105, 0, $nota["cuds_ajuste"], 1, 'L', true, 1, 12, '', true);
        }

        // --- COLUMNA DERECHA (Totales) ---
        $montoDescuento = floatval($nota["monto_descuento"] ?? 0);
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
                            <th style="font-weight:bold;">TOTAL NETO:</th>
                            <td style="font-weight:bold;">$' . number_format($totalPagar, 2) . '</td>
                        </tr>
                    </table></div>';

        $pdf->writeHTMLCell(75, '', 125, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        ob_end_clean();
        $pdf->Output('nota-ajuste-ds-' . $nota["numero_nota_ajuste"] . '.pdf', 'I');
    }
}

if (isset($_GET["idNota"])) {
    $imprimir = new imprimirNotaAjusteDS();
    $imprimir->idNota = $_GET["idNota"];
    $imprimir->traerImpresionDetalle();
}
