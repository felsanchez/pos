<?php

require_once "../../../controladores/cajas.controlador.php";
require_once "../../../modelos/cajas.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/configuracion.controlador.php";
require_once "../../../modelos/configuracion.modelo.php";

require_once "../../../controladores/factus.controlador.php";
require_once "../../../modelos/factus.modelo.php";

class ImprimirArqueoCaja
{
    public $idCaja;

    public function traerImpresionArqueo()
    {
        $idTurno = $this->idCaja;
        $detalles = ControladorCajas::ctrObtenerDetalleTurno($idTurno);

        if (!$detalles) {
            die("Reporte de arqueo no encontrado.");
        }

        $cierre = $detalles["cierre"];
        $movimientos = $detalles["movimientos"];
        $desgloseVentas = $detalles["desgloseVentas"];

        $configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
        $configFactus = ControladorFactus::ctrObtenerConfiguracion();

        $nombreEmpresa = isset($configFactus['nombre_empresa']) && !empty($configFactus['nombre_empresa']) ? $configFactus['nombre_empresa'] : ($configuracion["nombre_empresa"] ?? 'Empresa');
        $nitEmisor = isset($configFactus['nit_empresa']) && !empty($configFactus['nit_empresa']) ? $configFactus['nit_empresa'] : ($configuracion["nit"] ?? '');
        $direccionEmisor = isset($configFactus['direccion_empresa']) && !empty($configFactus['direccion_empresa']) ? $configFactus['direccion_empresa'] : ($configuracion["direccion"] ?? '');
        $telefonoEmisor = isset($configFactus['telefono_empresa']) && !empty($configFactus['telefono_empresa']) ? $configFactus['telefono_empresa'] : ($configuracion["telefono"] ?? '');
        $emailEmisor = isset($configFactus['email_empresa']) && !empty($configFactus['email_empresa']) ? $configFactus['email_empresa'] : ($configuracion["correo"] ?? '');
        $labelNombreEmisor = (isset($configFactus['tipo_persona']) && $configFactus['tipo_persona'] == '1') ? 'Razón Social' : 'Nombre Empresa';
        $moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";

        // REQUERIMOS LA CLASE TCPDF
        require_once('tcpdf_include.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema POS');
        $pdf->SetTitle('Reporte de Arqueo #' . $idTurno);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(TRUE, 12);
        $pdf->AddPage();

        // Estilos CSS para el PDF
        $style = '
        <style>
            .container { font-family: helvetica; color: #333; }
            .header-table { width: 100%; border-bottom: 2px solid #00a65a; padding-bottom: 8px; }
            .meta-table { width: 100%; margin-top: 10px; margin-bottom: 15px; }
            .meta-title { font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; color: #00a65a; }
            .meta-content { background-color: #f9f9f9; border-left: 3px solid #00a65a; padding: 6px; font-size: 9.5px; }
            .section-title { font-size: 11px; font-weight: bold; color: #00a65a; border-bottom: 1.5px solid #00a65a; padding-bottom: 3px; margin-top: 15px; margin-bottom: 8px; }
            .table { width: 100%; border-collapse: collapse; }
            .table-header { background-color: #00a65a; color: white; font-weight: bold; text-transform: uppercase; font-size: 9px; }
            .table td { border-bottom: 1px solid #eee; padding: 6px; font-size: 9px; }
            .table th { padding: 6px; font-size: 9px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .text-bold { font-weight: bold; }
            .text-red { color: #dd4b39; }
            .text-green { color: #00a65a; }
            .text-muted { color: #777; }
            .well { background-color: #f5f5f5; padding: 8px; border: 1px solid #e3e3e3; border-radius: 4px; font-size: 9px; color: #555; }
            .sign-table { width: 100%; margin-top: 40px; }
            .sign-line { border-top: 1px solid #999; text-align: center; font-size: 9px; padding-top: 5px; width: 40%; }
        </style>';

        // Logo
        $htmlLogo = '';
        if (isset($configFactus['logo_empresa']) && !empty($configFactus['logo_empresa'])) {
            $rutaLogoFactus = "../../../" . $configFactus['logo_empresa'];
            if (file_exists($rutaLogoFactus)) {
                $imgData = base64_encode(file_get_contents($rutaLogoFactus));
                $ext = pathinfo($rutaLogoFactus, PATHINFO_EXTENSION);
                $htmlLogo = '<img src="data:image/' . $ext . ';base64,' . $imgData . '" style="height:35px; vertical-align:middle; margin-bottom:4px;"> ';
            }
        }

        // Header HTML
        $html = $style . '<div class="container">';
        $html .= '<table class="header-table">
            <tr>
                <td style="width:60%; vertical-align:middle;">
                    <span style="font-size:16px; font-weight:bold; color:#333;">' . $htmlLogo . $nombreEmpresa . '</span>
                </td>
                <td style="width:40%; text-align:right; vertical-align:middle;">
                    <span style="font-size:14px; font-weight:bold; color:#00a65a;">ARQUEO DE CAJA CHICA</span><br>
                    <span style="font-size:9px; color:#666;">Turno #' . $idTurno . ' | Estado: ' . strtoupper($cierre["estado"]) . '</span>
                </td>
            </tr>
        </table><br>';

        // Info General Table
        $html .= '<table class="meta-table" cellpadding="4">
            <tr>
                <td style="width:50%;" valign="top">
                    <div class="meta-content">
                        <span class="meta-title">INFORMACIÓN DE LA EMPRESA</span><br><br>
                        <strong>' . $labelNombreEmisor . ':</strong> ' . $nombreEmpresa . '<br>
                        <strong>NIT:</strong> ' . $nitEmisor . '<br>
                        <strong>Dirección:</strong> ' . $direccionEmisor . '<br>
                        <strong>Teléfono:</strong> ' . $telefonoEmisor . '<br>
                        <strong>Email:</strong> ' . $emailEmisor . '
                    </div>
                </td>
                <td style="width:50%;" valign="top">
                    <div class="meta-content">
                        <span class="meta-title">INFORMACIÓN DEL TURNO</span><br><br>
                        <strong>Sucursal / Bodega:</strong> ' . ($cierre["nombre_bodega"] ?? 'N/A') . '<br>
                        <strong>Cajero Responsable:</strong> ' . ($cierre["nombre_usuario"] ?? 'N/A') . '<br>
                        <strong>Fecha Apertura:</strong> ' . $cierre["fecha_apertura"] . '<br>
                        <strong>Fecha Cierre:</strong> ' . ($cierre["fecha_cierre"] ? $cierre["fecha_cierre"] : 'Abierto') . '
                    </div>
                </td>
            </tr>
        </table><br>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $startY = $pdf->GetY();

        // --- COLUMNA IZQUIERDA: AUDITORÍA DE EFECTIVO ---
        $ingresosMan = 0;
        $egresosMan = 0;
        $gastosEfectivo = 0;
        if (is_array($movimientos)) {
            foreach ($movimientos as $mov) {
                if ($mov['tipo'] === 'ingreso') {
                    if (stripos($mov['motivo'], "gasto") !== false) {
                        $gastosEfectivo -= floatval($mov['monto']);
                    } else {
                        $ingresosMan += floatval($mov['monto']);
                    }
                } else {
                    if (stripos($mov['motivo'], "gasto") !== false) {
                        $gastosEfectivo += floatval($mov['monto']);
                    } else {
                        $egresosMan += floatval($mov['monto']);
                    }
                }
            }
        }

        $base = floatval($cierre["monto_apertura"]);
        $ventasEfectivo = floatval($cierre["ventas_efectivo"]);
        
        $esperado = $cierre["monto_cierre_teorico"] !== null ? floatval($cierre["monto_cierre_teorico"]) : ($base + $ventasEfectivo + $ingresosMan - $egresosMan - $gastosEfectivo);
        $real = $cierre["monto_cierre_real"] !== null ? floatval($cierre["monto_cierre_real"]) : $esperado;
        $dif = $cierre["diferencia"] !== null ? floatval($cierre["diferencia"]) : ($real - $esperado);

        $claseDif = "text-muted";
        $textoDifLabel = "Cuadrada";
        if ($dif > 0) {
            $claseDif = "text-green";
            $textoDifLabel = "Sobrante";
        } elseif ($dif < 0) {
            $claseDif = "text-red";
            $textoDifLabel = "Faltante";
        }

        $htmlLeft = $style . '
        <div class="section-title"><i class="fa fa-calculator"></i> AUDITORÍA DE EFECTIVO (GAVETA)</div>
        <table class="table" cellpadding="6">
            <tr>
                <td style="width:60%; font-weight:bold;">(+) Monto Base (Apertura)</td>
                <td style="width:40%;" class="text-right text-bold">' . $moneda . ' ' . number_format($base, 2) . '</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">(+) Ventas en Efectivo</td>
                <td class="text-right text-bold text-green">' . $moneda . ' ' . number_format($ventasEfectivo, 2) . '</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">(+) Ingresos Manuales</td>
                <td class="text-right text-bold text-green">' . $moneda . ' ' . number_format($ingresosMan, 2) . '</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">(-) Egresos Manuales</td>
                <td class="text-right text-bold text-red">' . $moneda . ' ' . number_format($egresosMan, 2) . '</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">(-) Gastos en Efectivo</td>
                <td class="text-right text-bold text-red">' . $moneda . ' ' . number_format($gastosEfectivo, 2) . '</td>
            </tr>
            <tr style="background-color: #f5f5f5;">
                <td class="text-bold">(=) Efectivo Esperado</td>
                <td class="text-right text-bold">' . $moneda . ' ' . number_format($esperado, 2) . '</td>
            </tr>
            <tr style="background-color: #fff9e6;">
                <td class="text-bold">(=) Efectivo Real Contado</td>
                <td class="text-right text-bold">' . $moneda . ' ' . number_format($real, 2) . '</td>
            </tr>
            <tr style="background-color: #f5f5f5;">
                <td class="text-bold">Diferencia</td>
                <td class="text-right text-bold ' . $claseDif . '">' . $moneda . ' ' . number_format($dif, 2) . ' (' . $textoDifLabel . ')</td>
            </tr>
        </table>';

        $pdf->writeHTMLCell(90, 0, 12, $startY, $htmlLeft, 0, 0, false, true, 'L', true);

        // --- COLUMNA DERECHA: VENTAS POR MEDIOS ELECTRÓNICOS ---
        $totalMediosElectronicos = 0;
        $rowsMediosHTML = '';

        if (is_array($desgloseVentas) && count($desgloseVentas) > 0) {
            foreach ($desgloseVentas as $v) {
                $metodo = $v["metodo_pago"];
                $total = floatval($v["total"]);

                if (stripos(strtolower(trim($metodo)), "efectivo") === false) {
                    $totalMediosElectronicos += $total;
                    $rowsMediosHTML .= '
                    <tr>
                        <td class="text-bold">' . $metodo . '</td>
                        <td class="text-right text-bold text-green">' . $moneda . ' ' . number_format($total, 2) . '</td>
                    </tr>';
                }
            }
        }

        if (empty($rowsMediosHTML)) {
            $rowsMediosHTML = '<tr><td colspan="2" class="text-center text-muted" style="font-style:italic; padding: 12px;">Sin ventas electrónicas en este turno</td></tr>';
        }

        $totalRecaudado = $ventasEfectivo + $totalMediosElectronicos;

        $htmlRight = $style . '
        <div class="section-title"><i class="fa fa-credit-card"></i> MEDIOS ELECTRÓNICOS (BANCOS/TARJETAS)</div>
        <table class="table" cellpadding="5">
            <thead>
                <tr class="table-header">
                    <th style="width:60%; font-weight:bold;">Medio Electrónico</th>
                    <th style="width:40%; font-weight:bold;" class="text-right">Monto Recaudado</th>
                </tr>
            </thead>
            <tbody>
                ' . $rowsMediosHTML . '
            </tbody>
        </table>
        <table class="table" cellpadding="6" style="margin-top: 10px;">
            <tr style="background-color:#eee; font-size:10px;">
                <td style="width:60%; font-weight:bold;">Total Recaudado (Ventas)</td>
                <td style="width:40%;" class="text-right text-bold">' . $moneda . ' ' . number_format($totalRecaudado, 2) . '</td>
            </tr>
        </table>';

        $pdf->writeHTMLCell(90, 0, 108, $startY, $htmlRight, 0, 1, false, true, 'R', true);

        // --- OBSERVACIONES Y DETALLE DE MOVIMIENTOS ---
        $pdf->SetY(max($pdf->GetY(), $startY + 75) + 5);

        $htmlFooter = $style . '<div class="container">';

        // Observaciones (Apertura y Cierre)
        $htmlFooter .= '<table style="width: 100%;" cellpadding="4">
            <tr>
                <td style="width: 50%;" valign="top">
                    <div class="section-title">Observaciones de Apertura</div>
                    <div class="well">' . (!empty($cierre["observaciones_apertura"]) ? htmlspecialchars($cierre["observaciones_apertura"]) : 'Ninguna observación de apertura registrada.') . '</div>
                </td>
                <td style="width: 50%;" valign="top">
                    <div class="section-title">Observaciones del Cierre</div>
                    <div class="well">' . (!empty($cierre["observaciones"]) ? htmlspecialchars($cierre["observaciones"]) : 'Ninguna observación de cierre registrada.') . '</div>
                </td>
            </tr>
        </table><br>';

        // Historial de Movimientos de Caja Chica
        $htmlFooter .= '<div class="section-title">DETALLE MOVIMIENTOS MANUALES DE CAJA CHICA</div>';
        $rowsMovimientos = '';

        if (is_array($movimientos) && count($movimientos) > 0) {
            foreach ($movimientos as $mov) {
                // Omitir visualmente los gastos hechos en la vista gastos (incluye reversiones y ajustes)
                if (stripos($mov["motivo"], "gasto") !== false) {
                    continue;
                }
                $hora = explode(" ", $mov["fecha"])[1];
                $tipo = $mov["tipo"] === 'ingreso' ? '<span class="text-green">Ingreso</span>' : '<span class="text-red">Egreso</span>';
                $monto = $moneda . ' ' . number_format(floatval($mov["monto"]), 2);
                $rowsMovimientos .= '
                <tr>
                    <td class="text-center">' . $hora . '</td>
                    <td class="text-center text-bold">' . $tipo . '</td>
                    <td class="text-right text-bold">' . $monto . '</td>
                    <td>' . htmlspecialchars($mov["motivo"]) . '</td>
                </tr>';
            }
        }
        
        if (empty($rowsMovimientos)) {
            $rowsMovimientos = '<tr><td colspan="4" class="text-center text-muted" style="font-style:italic; padding: 10px;">Sin movimientos manuales registrados en este turno</td></tr>';
        }

        $htmlFooter .= '<table class="table" cellpadding="4">
            <thead>
                <tr class="table-header">
                    <th style="width:15%; text-align:center; font-weight:bold;">Hora</th>
                    <th style="width:15%; text-align:center; font-weight:bold;">Tipo</th>
                    <th style="width:20%; text-align:right; font-weight:bold;">Monto</th>
                    <th style="width:50%; font-weight:bold;">Motivo / Concepto</th>
                </tr>
            </thead>
            <tbody>
                ' . $rowsMovimientos . '
            </tbody>
        </table>';

        // Sección de Firmas
        $htmlFooter .= '<br><br>
        <table class="sign-table">
            <tr>
                <td style="width: 10%;"></td>
                <td class="sign-line">
                    <br><br><br>
                    ____________________________________<br>
                    Firma Cajero Responsable<br>
                    C.C. ________________________
                </td>
                <td style="width: 20%;"></td>
                <td class="sign-line">
                    <br><br><br>
                    ____________________________________<br>
                    Firma Administrador / Auditor<br>
                    C.C. ________________________
                </td>
                <td style="width: 10%;"></td>
            </tr>
        </table>';

        $htmlFooter .= '</div>';

        $pdf->writeHTML($htmlFooter, true, false, true, false, '');

        ob_end_clean();
        $filename = 'arqueo-turno-' . $idTurno . '.pdf';
        $pdf->Output($filename, 'I');
    }
}

if (isset($_GET["idCaja"])) {
    $imprimir = new ImprimirArqueoCaja();
    $imprimir->idCaja = $_GET["idCaja"];
    $imprimir->traerImpresionArqueo();
}
