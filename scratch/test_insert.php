<?php
require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";

$stmt = Conexion::conectar()->prepare("SELECT id FROM usuarios LIMIT 1");
$stmt->execute();
$id_vendedor = $stmt->fetchColumn();

$stmt = Conexion::conectar()->prepare("SELECT id FROM clientes LIMIT 1");
$stmt->execute();
$id_cliente = $stmt->fetchColumn();

$datos = array(
    "codigo" => 99999,
    "numero_factura" => null,
    "id_cliente" => $id_cliente,
    "id_vendedor" => $id_vendedor,
    "productos" => "[]",
    "impuesto" => 0,
    "neto" => 0,
    "total" => 0,
    "metodo_pago" => "Efectivo",
    "notas" => "Test",
    "estado" => "venta",
    "imagen" => "",
    "fecha" => date('Y-m-d H:i:s'),
    "tipo_descuento" => "",
    "valor_descuento" => 0,
    "monto_descuento" => 0,
    "recibe" => 0,
    "extra" => null,
    "retenciones" => null,
    "resolucion_id" => null,
    "fecha_vencimiento" => null,
    "orden_compra" => "10738", // The test value
    "forma_pago_dian" => "1",
    "metodo_pago_dian_id" => null,
    "estado_dian" => "pendiente",
    "cufe" => null,
    "qr_data" => null,
    "xml_dian" => null,
    "pdf_dian" => null,
    "mensaje_dian" => null,
    "fecha_envio_dian" => null
);

$id = ModeloVentas::mdlIngresarVenta("ventas", $datos);
echo "Inserted ID: " . $id . "\n";

$stmt = Conexion::conectar()->prepare("SELECT orden_compra FROM ventas WHERE id = :id");
$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch();
echo "orden_compra in DB: " . $result["orden_compra"] . "\n";

// cleanup
$stmt = Conexion::conectar()->prepare("DELETE FROM ventas WHERE id = :id");
$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();
?>
