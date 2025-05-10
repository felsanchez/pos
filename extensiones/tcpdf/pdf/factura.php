<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";
 

class imprimirFactura{

public $codigo;


public function traerImpresionFactura(){	

//TRAEMOS LA INFORMACIÓN DE LA VENTA


$itemVenta = "codigo";
$valorVenta = $this->codigo;

$respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

$fecha = substr($respuestaVenta["fecha"],0,-8);
$productos = json_decode($respuestaVenta["productos"], true);
$neto = number_format($respuestaVenta["neto"],2);
$impuesto = number_format($respuestaVenta["impuesto"],2);
$total = number_format($respuestaVenta["total"],2);

//TRAEMOS LA INFORMACIÓN DEL CLIENTE

$itemCliente = "id";
$valorCliente = $respuestaVenta["id_cliente"];

$respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

//TRAEMOS LA INFORMACIÓN DEL VENDEDOR

$itemVendedor = "id";
$valorVendedor = $respuestaVenta["id_vendedor"];

$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);


//REQUERIMOS LA CLASE TCPDF

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set font- añadido
$pdf->SetFont('helvetica', '', 11);

$pdf->startPageGroup();

$pdf->AddPage();

//---------------------------------------------------------

$bloque1 = <<<EOF

	<table>
	
		<tr>
		
			<td style="width:150px"><img src="images/logo-negro-bloque.png"></td>

			<td style="background-color:white; width:140px">

				<div style="font-size:8.5px; text-align:right; line-height:15px;">

					<br>
					NIT: 71.759.945-9

					<br>
					Dirección: Calle 14B 29-45

					<br>
					Teléfono: 301 314 99 82

					<br>
					inventorysystem@gmail.com

				</div>

			</td>


			<td>
				<div>
					<br>
					<br>
				</div>
			</td>


			<td style="background-color:white; width:110px; text-align:right; color:red"><br><br>FACTURA N.<br>$valorVenta</td>

		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');


//---------------------------------------------------------

$bloque2 = <<<EOF

<table>
<br><br><br><br><br><br>

<tr>

	<td style="width:540px"><img src="images/back.jpg"></td>

</tr>

</table>

<table style="font-size:10px; padding:5px 10px;">

<br><br><br>

	<tr>

		<td style="border:1px solid #666; background-color:white; width:540px">Fecha: $fecha</td>

	</tr>

	<tr>

		<td style="border:1px solid #666; background-color:white; width:540px">Vendedor: $respuestaVendedor[nombre]</td>

	</tr>
	<br>

	<tr>

		<td style="border:1px solid #666; background-color:white; width:390px">
		
			Cliente: $respuestaCliente[nombre]

		</td>

		<td style="border:1px solid #666; background-color:white; width:150px; text-align:left">
		
			Documento: $respuestaCliente[documento]
			
		</td>

	</tr>

	<tr>

		<td style="border:1px solid #666; background-color:white; width:180px; text-align:left">
		
			Departamento: $respuestaCliente[departamento]

		</td>

		<td style="border:1px solid #666; background-color:white; width:180px; text-align:left">
		
			Ciudad: $respuestaCliente[ciudad]
			
		</td>

		<td style="border:1px solid #666; background-color:white; width:180px; text-align:left">
		
			Dirección: $respuestaCliente[direccion]
			
		</td>

	</tr>

	<tr>

		<td style="border:1px solid #666; background-color:white; width:270px">
		
			E-mail: $respuestaCliente[email]

		</td>

		<td style="border:1px solid #666; background-color:white; width:270px">
		
			Celular: $respuestaCliente[telefono]
			
		</td>

	</tr>

	<tr>

		<td style="border-bottom: 1px solid #666; background-color:white; width:540px"></td>

	</tr>

</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');


//---------------------------------------------------------

$bloque3 = <<<EOF

<table style="font-size:10px; padding:5px 10px;">

<tr>

<td style="border: 1px solid #666; background-color:white; width:260px; text-align:center">Productos</td>
<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Cantidad</td>
<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Valor Unit.</td>
<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Valor Total</td>

</tr>

</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


//---------------------------------------------------------
foreach ($productos as $key => $item) {

$itemProducto = "descripcion";
$valorProducto = $item["descripcion"];
$orden = null;

$respuestaProducto = ControladorProductos::ctrMostrarProductos($itemProducto, $valorProducto, $orden);

$valorUnitario = number_format($respuestaProducto["precio_venta"], 2);

$precioTotal = number_format($item["total"], 2);
	
$bloque4 = <<<EOF

<table style="font-size:10px; padding:5px 10px;">

<tr>

	<td style="border: 1px solid #666; color:#333; background-color:white; width:260px; text-align:center">$item[descripcion]
	</td>

	<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">$item[cantidad]
	</td>

	<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">$ $valorUnitario
	</td>

	<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">$ $precioTotal
	</td>

</tr>

</table>


EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}


//---------------------------------------------------------

$bloque5 = <<<EOF

<table style="font-size:10px; padding:5px 10px;">

	<tr>

		<td style="color:#333; background-color:white; width:340px; text-align:center"></td>

		<td style="border-bottom: 1px solid #666; background-color:white; width:100px; text-align:center"></td>

		<td style="border-bottom: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center"></td>

	</tr>

	<tr>

		<td style="border-right: 1px solid #666; color:#333; background-color:white; width:340px; text-align:center"></td>

		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">
			Neto:
		</td>

		<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
			$ $neto
		</td>

	</tr>

	<tr>

		<td style="border-right: 1px solid #666; color:#333; background-color:white; width:340px; text-align:center"></td>

		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">
			Impuesto:
		</td>

		<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
			$ $impuesto
		</td>

	</tr>

	<tr>

		<td style="border-right: 1px solid #666; color:#333; background-color:white; width:340px; text-align:center"></td>

		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">
			Total:
		</td>

		<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
			$ $total
		</td>

	</tr>

</table>


EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');

// ---------------------------------------------------------

// HPM- codigo QR- estilo para código de barras- https://tcpdf.org/examples/example_050/
/*
$style = array(
    'border' => false,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255)
    'module_width' => 1, // width of a single module in points
    'module_height' => 1 // height of a single module in points
);
// QRCODE
$pdf->write2DBarcode(('NumFac:'.$valorVenta.'        
FecFac:'.$fecha.'        
NitFac:8040047106'.'        
DocAdq:'.$respuestaCliente["documento"].'	       
ValFac:'.$neto.'        ValIva:'.$impuesto.'        ValOtroIm:0'.'        ValFacIm:'.$total), 'QRCODE,Q', 142, 6, 30, 45, $style, 'N');
$pdf->Text(20, 145, ''); 
*/

// Estilo para código QR
$style = array(
    'border' => false,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);

// Contenido del código QR (incluye nombre del cliente)
$contenidoQR = 
'Inventory System
NumFac:'.$valorVenta.'        
FecFac:'.$fecha.'        
NitFac:8040047106        
NomAdq:'.$respuestaCliente["nombre"].'   	DocAdq:'.$respuestaCliente["documento"].'
DepAdq:'.$respuestaCliente["departamento"].'   	CiudadAdq:'.$respuestaCliente["ciudad"].'		DirAdq:'.$respuestaCliente["direccion"].' 
ValFac:'.$neto.'        
ValIva:'.$impuesto.'             
ValFacIm:'.$total;

// Código QR con mayor tamaño y nivel de corrección más bajo
$pdf->write2DBarcode($contenidoQR, 'QRCODE,L', 140, 22, 60, 60, $style, 'N');


// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

ob_end_clean();
$pdf->Output('factura.pdf');

}

}

$factura = new imprimirFactura();
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionFactura();

/*$bloque1 = <<<EOF
	<table>	
		<tr>		
			<td style="width:150px"><img src="images/logo-negro-bloque.png"></td>
			<td style="background-color:white; width:140px">
				<div style="font-size:8.5px; text-align:right; line-height:15px;">
					<br>
					NIT: 71.759.963-9
					<br>
					Dirección: Calle 13B 19-15
				</div>
			</td>

			<td style="background-color:white; width:140px">
				<div style="font-size:8.5px; text-align:right; line-height:15px;">
					<br>
					Teléfono: 301 314 28 99
					<br>
					digitentcol@gmail.com
				</div>
			</td>
			<td style="background-color:white; width:110px; text-align:right; color:red"><br><br>FACTURA N.<br>$valorVenta</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloque1, false, false, false, false, '');*/

?>

