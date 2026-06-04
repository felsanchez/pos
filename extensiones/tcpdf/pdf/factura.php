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


class imprimirFactura
{

	public $codigo;

	public function traerImpresionFactura()
	{

		//TRAEMOS LA INFORMACIÓN DE LA VENTA 

		$itemVenta = "codigo";
		$valorVenta = $this->codigo;

		$respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

		$fecha = substr($respuestaVenta["fecha"], 0, -8);
		$productos = json_decode($respuestaVenta["productos"], true);
		$neto = number_format($respuestaVenta["neto"], 2);
		$impuesto = number_format($respuestaVenta["impuesto"], 2);
		$total = number_format($respuestaVenta["total"], 2);

		//TRAEMOS LA INFORMACIÓN DEL CLIENTE

		$itemCliente = "id";
		$valorCliente = $respuestaVenta["id_cliente"];

		$respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

		//TRAEMOS LA INFORMACIÓN DEL VENDEDOR 

		$itemVendedor = "id";
		$valorVendedor = $respuestaVenta["id_vendedor"];
		$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

		//TRAEMOS LA CONFIGURACIÓN DEL SISTEMA 

		$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();

		// Datos de la empresa
		$nombreEmpresa = !empty($configuracion["nombre_empresa"]) ? $configuracion["nombre_empresa"] : "Grupo Fej";
		$nitEmpresa = !empty($configuracion["nit"]) ? $configuracion["nit"] : "";
		$direccionEmpresa = !empty($configuracion["direccion"]) ? $configuracion["direccion"] : "";
		$telefonoEmpresa = !empty($configuracion["telefono"]) ? $configuracion["telefono"] : "";
		$correoEmpresa = !empty($configuracion["correo"]) ? $configuracion["correo"] : "";

		// Logo de la empresa
		$logoEmpresa = "images/logo-negro-bloque.png"; // Logo por defecto
		if (!empty($configuracion["logo"])) {
			// Construir ruta absoluta desde la ubicación de este archivo
			$directorioBase = dirname(dirname(dirname(dirname(__FILE__)))); // Sube 4 niveles desde pdf/
			$rutaAbsoluta = $directorioBase . "/" . $configuracion["logo"];

			// Verificar que el archivo existe
			if (file_exists($rutaAbsoluta)) {
				$logoEmpresa = $rutaAbsoluta;
			}
		}


		// Colores de la factura
		$colorPrincipal = !empty($configuracion["color_principal"]) ? $configuracion["color_principal"] : "#667eea";
		$colorSecundario = !empty($configuracion["color_secundario"]) ? $configuracion["color_secundario"] : "#764ba2";

		// Mensaje de pie de ticket
		$mensajeTicket = !empty($configuracion["mensaje_ticket"]) ? $configuracion["mensaje_ticket"] : "¡Gracias por su compra!";

		//REQUERIMOS LA CLASE TCPDF
		require_once('tcpdf_include.php');

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($nombreEmpresa);

		// 🔹 LÓGICA DE VISUALIZACIÓN DE CONSECUTIVO 🔹
// Si la factura ya tiene número oficial DIAN (ej: FEFG67), usamos ese.
// Si no, usamos el código interno (ej: 68).
		$numeroMostrar = $valorVenta; // Por defecto el código interno
		$etiquetaNumero = "No.";

		if (!empty($respuestaVenta["numero_factura"])) {
			// Usar el número oficial
			// Opcional: Si quieres quitar el prefijo (FEFG) y dejar solo el número:
			// $numeroMostrar = preg_replace('/[^0-9]/', '', $respuestaVenta["numero_factura"]);

			// Pero mejor mostrarlo completo o como el usuario prefiera. 
			// El usuario dijo "folio 67", así que asumimos que quiere ver el número consecutivo.
			// Vamos a mostrar el número completo tal cual viene de Factus para ser exactos.
			$numeroMostrar = $respuestaVenta["numero_factura"];
		}

		$pdf->SetTitle('Factura #' . $numeroMostrar);

		// Removemos header y footer por defecto
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// Márgenes
		$pdf->SetMargins(15, 15, 15);
		$pdf->SetAutoPageBreak(TRUE, 15);

		// Fuente principal
		$pdf->SetFont('helvetica', '', 10);
		$pdf->AddPage();

		//---------------------------------------------------------
// BLOQUE 1 - HEADER MODERNO CON GRADIENTE
//--------------------------------------------------------- 

		$bloque1 = '<table cellpadding="10" style="background-color:' . $colorPrincipal . ';">';
		$bloque1 .= '<tr>';
		$bloque1 .= '<td style="width:20%; vertical-align:middle; background-color:' . $colorPrincipal . ';">';
		$bloque1 .= '<img src="' . $logoEmpresa . '" style="width:80px;">';
		$bloque1 .= '</td>';
		$bloque1 .= '<td style="width:45%; vertical-align:middle; background-color:' . $colorPrincipal . '; color:#ffffff;">';
		$bloque1 .= '<span style="font-size:22px; font-weight:bold; color:#ffffff;">' . $nombreEmpresa . '</span><br>';
		$bloque1 .= '<span style="font-size:9px; color:#ffffff;">NIT: ' . $nitEmpresa . '</span><br>';
		$bloque1 .= '<span style="font-size:9px; color:#ffffff;">Dirección: ' . $direccionEmpresa . '</span><br>';
		$bloque1 .= '<span style="font-size:9px; color:#ffffff;">Teléfono: ' . $telefonoEmpresa . '</span><br>';
		$bloque1 .= '<span style="font-size:9px; color:#ffffff;">' . $correoEmpresa . '</span>';
		$bloque1 .= '</td>';
		$bloque1 .= '<td style="width:35%; text-align:right; vertical-align:top; background-color:' . $colorPrincipal . '; color:#ffffff;">';
		$bloque1 .= '<span style="font-size:20px; font-weight:bold; color:#ffffff;">FACTURA</span><br>';
		$bloque1 .= '<span style="font-size:11px; color:#ffffff;">' . $numeroMostrar . '</span><br>';
		$bloque1 .= '<span style="font-size:9px; color:#ffffff;">' . $fecha . '</span>';
		$bloque1 .= '</td>';
		$bloque1 .= '</tr>';
		$bloque1 .= '</table>';

		$pdf->writeHTML($bloque1, false, false, false, false, '');

		//---------------------------------------------------------
// BLOQUE 2 - INFORMACIÓN DE CLIENTE Y VENDEDOR
//---------------------------------------------------------

		$pdf->Ln(8);
		$bloque2 = '<table cellpadding="6">';
		$bloque2 .= '<tr>';
		$bloque2 .= '<td style="width:100%; background:#f8f9fa; border-left:4px solid ' . $colorPrincipal . '; vertical-align:top;">';
		$bloque2 .= '<div style="color:' . $colorPrincipal . '; font-size:10px; font-weight:bold; margin-bottom:5px;">';
		$bloque2 .= 'INFORMACIÓN DEL CLIENTE';
		$bloque2 .= '</div>';
		$bloque2 .= '<div style="font-size:9px; line-height:16px; color:#555;">';
		$bloque2 .= '<strong>Cliente:</strong> ' . $respuestaCliente["nombre"] . '<br>';
		$bloque2 .= '<strong>Documento:</strong> ' . $respuestaCliente["documento"] . '<br>';
		$bloque2 .= '<strong>Dirección:</strong> ' . $respuestaCliente["direccion"] . '<br>';
		$municipio = !empty($respuestaCliente["ciudad"]) ? mb_convert_case($respuestaCliente["ciudad"], MB_CASE_TITLE, "UTF-8") : "";
		$departamento = !empty($respuestaCliente["departamento"]) ? mb_convert_case($respuestaCliente["departamento"], MB_CASE_TITLE, "UTF-8") : "";
		$ubicacion = $municipio;
		if (!empty($municipio) && !empty($departamento)) {
			$ubicacion .= ", " . $departamento;
		} elseif (empty($municipio)) {
			$ubicacion = $departamento;
		}

		$bloque2 .= '<strong>Municipio:</strong> ' . $ubicacion . '<br>';
		$bloque2 .= '<strong>Teléfono:</strong> ' . $respuestaCliente["telefono"] . '<br>';
		$bloque2 .= '<strong>Email:</strong> ' . $respuestaCliente["email"];
		$bloque2 .= '</div>';
		$bloque2 .= '</td>';
		$bloque2 .= '</tr>';
		$bloque2 .= '</table>';

		$pdf->writeHTML($bloque2, false, false, false, false, '');

		//---------------------------------------------------------
// BLOQUE 3 - TABLA DE PRODUCTOS (HEADER)
//---------------------------------------------------------

		$pdf->Ln(5);

		$bloque3 = <<<EOF

<table cellpadding="8" style="border-bottom:2px solid #667eea;">
	<tr style="background:#f8f9fa; color:#667eea; font-weight:bold;">
		<td style="width:50%; font-size:10px; text-transform:uppercase;">Producto</td>
		<td style="width:15%; font-size:10px; text-align:center; text-transform:uppercase;">Cant.</td>
		<td style="width:17%; font-size:10px; text-align:right; text-transform:uppercase;">Precio Unit.</td>
		<td style="width:18%; font-size:10px; text-align:right; text-transform:uppercase;">Total</td>
	</tr>
</table>

EOF;

		$pdf->writeHTML($bloque3, false, false, false, false, '');


		//---------------------------------------------------------
// BLOQUE 4 - PRODUCTOS (LOOP)
//---------------------------------------------------------

		foreach ($productos as $key => $item) {

			$itemProducto = "descripcion";
			$valorProducto = $item["descripcion"];
			$orden = null;

			$respuestaProducto = ControladorProductos::ctrMostrarProductos($itemProducto, $valorProducto, $orden);

			$valorUnitario = number_format($respuestaProducto["precio_venta"], 2);
			$precioTotal = number_format($item["total"], 2);

			// Alternar color de fondo para mejor legibilidad
			$bgColor = ($key % 2 == 0) ? '#ffffff' : '#f8f9fa';

			$bloque4 = <<<EOF

	<table cellpadding="6">
		<tr style="background:$bgColor;">
			<td style="width:50%; font-size:9px; color:#333; border-bottom:1px solid #e0e0e0;">
				$item[descripcion]
			</td>
			<td style="width:15%; font-size:9px; color:#667eea; text-align:center; font-weight:bold; border-bottom:1px solid #e0e0e0;">
				$item[cantidad]
			</td>
			<td style="width:17%; font-size:9px; color:#555; text-align:right; border-bottom:1px solid #e0e0e0;">
				$ $valorUnitario
			</td>
			<td style="width:18%; font-size:9px; color:#333; text-align:right; font-weight:bold; border-bottom:1px solid #e0e0e0;">
				$ $precioTotal
			</td>
		</tr>
	</table>

EOF;

			$pdf->writeHTML($bloque4, false, false, false, false, '');
		}


		//---------------------------------------------------------
		// BLOQUE 5 - TOTALES (Recálculo Exacto)
		//---------------------------------------------------------

		$valorBrutoRecalculado = 0; // Base Imponible Bruta (Antes de descuentos)
		$subtotalRecalculado = 0;   // Base Imponible Neta (Después de descuentos)
		$impuestoGeneral = 0;

		$tipoDescuento = $respuestaVenta["tipo_descuento"] ?? "";
		$valorDescuentoGlobal = $respuestaVenta["valor_descuento"] ?? 0;
		$montoDescuentoTotal = $respuestaVenta["monto_descuento"] ?? 0;
		$totalVentaOriginal = $respuestaVenta["total"];
		$totalOriginalEstimado = $totalVentaOriginal + $montoDescuentoTotal;

		foreach ($productos as $prod) {
			$totalProductoConImpuesto = floatval($prod["total"]);
			$impuestoPorcentaje = 0;

			if (isset($prod["impuesto"])) {
				$impuestoPorcentaje = floatval($prod["impuesto"]);
			} else {
				$infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
				$impuestoPorcentaje = isset($infoP["impuesto_porcentaje"]) ? floatval($infoP["impuesto_porcentaje"]) : 19;
			}

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
		}

		$totalVentaCalculado = $subtotalRecalculado + $impuestoGeneral;
		$descuentoBase = $valorBrutoRecalculado - $subtotalRecalculado;

		// Retenciones
		$totalRetenciones = 0;
		if (!empty($respuestaVenta["retenciones"])) {
			$retencionesArr = json_decode($respuestaVenta["retenciones"], true);
			foreach ($retencionesArr as $ret) {
				$totalRetenciones += $ret['monto'];
			}
		}
		$valorNetoPagar = $totalVentaCalculado - $totalRetenciones;

		$pdf->Ln(5);

		$htmlTotales = '
		<table cellpadding="5">
			<tr>
				<td style="width:65%;"></td>
				<td style="width:35%;">
					<table cellpadding="4">
						<tr>
							<td style="width:50%; font-size:9px; color:#555; text-align:left; border-bottom:1px solid #e0e0e0;">Subtotal:</td>
							<td style="width:50%; font-size:9px; color:#333; text-align:right; border-bottom:1px solid #e0e0e0;">$ ' . number_format($valorBrutoRecalculado, 2) . '</td>
						</tr>';

		$labelDescuento = "Descuento:";
		if ($tipoDescuento == "porcentaje") {
			$labelDescuento = 'Descuento (' . $valorDescuentoGlobal . '%):';
		} else if ($tipoDescuento == "fijo") {
			$labelDescuento = 'Descuento (Fijo ' . number_format((float)$valorDescuentoGlobal, 0, '', '.') . '):';
		}

		if ($montoDescuentoTotal > 0 || (float)$valorDescuentoGlobal > 0) {
			$htmlTotales .= '
						<tr>
							<td style="width:50%; font-size:9px; color:#555; text-align:left; border-bottom:1px solid #e0e0e0;">' . $labelDescuento . '</td>
							<td style="width:50%; font-size:9px; color:#333; text-align:right; border-bottom:1px solid #e0e0e0;">$ ' . number_format($descuentoBase, 2) . '</td>
						</tr>';
		}

		$htmlTotales .= '
						<tr>
							<td style="width:50%; font-size:9px; color:#555; text-align:left; border-bottom:1px solid #e0e0e0;">Valor Bruto:</td>
							<td style="width:50%; font-size:9px; color:#333; text-align:right; border-bottom:1px solid #e0e0e0;">$ ' . number_format($subtotalRecalculado, 2) . '</td>
						</tr>
						<tr>
							<td style="width:50%; font-size:9px; color:#555; text-align:left; border-bottom:1px solid #e0e0e0;">Impuesto:</td>
							<td style="width:50%; font-size:9px; color:#333; text-align:right; border-bottom:1px solid #e0e0e0;">$ ' . number_format($impuestoGeneral, 2) . '</td>
						</tr>
						<tr>
							<td style="width:50%; font-size:9px; color:#555; text-align:left; border-bottom:1px solid #e0e0e0;">Total:</td>
							<td style="width:50%; font-size:9px; color:#333; text-align:right; border-bottom:1px solid #e0e0e0;">$ ' . number_format($totalVentaCalculado, 2) . '</td>
						</tr>
						<tr style="background:#667eea; color:white;">
							<td style="width:50%; font-size:11px; font-weight:bold; text-align:left; padding:6px;">VALOR NETO:</td>
							<td style="width:50%; font-size:11px; font-weight:bold; text-align:right; padding:6px;">$ ' . number_format($valorNetoPagar, 2) . '</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';

		$pdf->writeHTML($htmlTotales, false, false, false, false, '');

		//---------------------------------------------------------
// BLOQUE 6 - CÓDIGO QR Y FOOTER
//---------------------------------------------------------

		// Preparar lista de productos para el QR (simplificada)
		$listaProductosQR = '';
		foreach ($productos as $key => $item) {
			$listaProductosQR .= '- ' . $item["descripcion"] . ' x' . $item["cantidad"] . "\n";
		}

		// Contenido del código QR simplificado y legible usando concatenación
		$contenidoQR = $nombreEmpresa . "\n";
		$contenidoQR .= "----------------------------\n";
		$contenidoQR .= "FACTURA No: " . $valorVenta . "\n";
		$contenidoQR .= "Fecha: " . $fecha . "\n";
		$contenidoQR .= "NIT: 71.759.945-9\n\n";
		$contenidoQR .= "CLIENTE\n";
		$contenidoQR .= "----------------------------\n";
		$contenidoQR .= "Nombre: " . $respuestaCliente["nombre"] . "\n";
		$contenidoQR .= "Doc: " . $respuestaCliente["documento"] . "\n";
		$contenidoQR .= "Tel: " . $respuestaCliente["telefono"] . "\n";
		$contenidoQR .= "Dir: " . $respuestaCliente["direccion"] . "\n";
		$contenidoQR .= "Municipio: " . $ubicacion . "\n\n";
		$contenidoQR .= "PRODUCTOS\n";
		$contenidoQR .= "----------------------------\n";
		$contenidoQR .= $listaProductosQR . "\n";
		$contenidoQR .= "Subtotal: $" . number_format($valorBrutoRecalculado, 2) . "\n";
		if ($montoDescuentoTotal > 0) {
			$contenidoQR .= "Descuento: $" . number_format($descuentoBase, 2) . "\n";
		}
		$contenidoQR .= "Valor Bruto: $" . number_format($subtotalRecalculado, 2) . "\n";
		$contenidoQR .= "Impuesto: $" . number_format($impuestoGeneral, 2) . "\n";
		$contenidoQR .= "TOTAL: $" . number_format($totalVentaCalculado, 2) . "\n";
		$contenidoQR .= "VALOR NETO: $" . number_format($valorNetoPagar, 2) . "\n";
		$contenidoQR .= "----------------------------";

		// Estilo para código QR
		$style = array(
			'border' => false,
			'vpadding' => 'auto',
			'hpadding' => 'auto',
			'fgcolor' => array(0, 0, 0),
			'bgcolor' => false,
			'module_width' => 1,
			'module_height' => 1
		);

		// Posición del código QR (ajustar según necesites)
		$pdf->write2DBarcode($contenidoQR, 'QRCODE,L', 15, 200, 45, 45, $style, 'N');

		// Footer informativo
		$pdf->SetY(-50);

		$footer = '<table cellpadding="8" style="background:#f8f9fa; border-top:3px solid ' . $colorPrincipal . '; margin-top:10px;">';
		$footer .= '<tr>';
		$footer .= '<td style="width:100%; text-align:center;">';
		$footer .= '<div style="font-size:10px; font-weight:bold; color:' . $colorPrincipal . '; margin-bottom:5px;">';
		$footer .= $mensajeTicket;
		$footer .= '</div>';
		$footer .= '<div style="font-size:8px; color:#777; line-height:12px;">';
		$footer .= 'Esta factura es un documento válido como soporte contable<br>';
		$footer .= 'Resolución DIAN No. 18764031234567 del 15/01/2025 - Prefijo 001<br>';
		$footer .= 'Autorizado del N° 001-2025-0001 al N° 001-2025-5000';
		$footer .= '</div>';
		$footer .= '</td>';
		$footer .= '</tr>';
		$footer .= '</table>';
		$pdf->writeHTML($footer, false, false, false, false, '');
		
		//---------------------------------------------------------
		// BLOQUE 7 - OBSERVACIONES
		//---------------------------------------------------------
		if (!empty($respuestaVenta["observacion"])) {
			$pdf->Ln(2);
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->Cell(0, 10, 'Observaciones:', 0, 1);
			$pdf->SetFont('helvetica', '', 9);
			$pdf->MultiCell(0, 5, $respuestaVenta["observacion"], 0, 'L');
		}

		// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

		ob_end_clean();
		$pdf->Output('factura.pdf', 'I');
	}

}

$factura = new imprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();

?>